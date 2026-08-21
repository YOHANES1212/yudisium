<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use App\Models\ScanLog;
use App\Models\PaymentVerification;

class AdminController extends Controller
{
    /**
     * Base URL SheetDB API.
     */
    private string $sheetdbUrl;

    public function __construct()
    {
        $this->sheetdbUrl = config('services.sheetdb.url', env('SHEETDB_URL', 'https://sheetdb.io/api/v1/71445zve8u6f7'));
    }

    /**
     * Ambil semua data dari SheetDB (di-cache 60 detik agar tidak spam API).
     */
    private function fetchAll(): array
    {
        return Cache::remember('sheetdb_peserta', 60, function () {
            $response = Http::timeout(10)
                ->withoutVerifying()   // nonaktifkan SSL verify (aman untuk local dev)
                ->get($this->sheetdbUrl);

            $raw = $response->successful() ? $response->json() : [];
            if (! is_array($raw)) {
                $raw = [];
            }

            // Ambil data verifikasi pembayaran & nomor kursi dari database lokal
            $localVerifications = PaymentVerification::all()->keyBy('nim');

            $result = [];
            foreach ($raw as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $clean = [];
                foreach ($item as $key => $val) {
                    $cleanKey = trim($key);
                    $clean[$cleanKey] = is_string($val) ? trim($val) : $val;
                }

                // Standardize aliases for robustness
                if (isset($clean['Nama Lengkap']) && !isset($clean['nama'])) {
                    $clean['nama'] = $clean['Nama Lengkap'];
                }
                if (isset($clean['Email']) && !isset($clean['Email Address'])) {
                    $clean['Email Address'] = $clean['Email'];
                }
                if (isset($clean['Email Address']) && !isset($clean['Email'])) {
                    $clean['Email'] = $clean['Email Address'];
                }
                if (isset($clean['Upload Foto (Formal)']) && !isset($clean['Upload Foto Formal'])) {
                    $clean['Upload Foto Formal'] = $clean['Upload Foto (Formal)'];
                }

                $nim  = trim($clean['NIM'] ?? '');
                $nama = trim($clean['Nama Lengkap'] ?? $clean['nama'] ?? '');

                // Filter out empty rows (where both NIM and Nama are blank in Google Sheet)
                if ($nim === '' && $nama === '') {
                    continue;
                }

                if ($nim && isset($localVerifications[$nim])) {
                    $local = $localVerifications[$nim];
                    if (!empty($local->status_pembayaran)) {
                        $clean['Status Pembayaran'] = $local->status_pembayaran;
                    }
                    if (!empty($local->nomor_kursi)) {
                        $clean['Nomor Kursi'] = $local->nomor_kursi;
                    }
                }

                if (empty($clean['Status Pembayaran']) || $clean['Status Pembayaran'] === '-') {
                    $clean['Status Pembayaran'] = 'Pending';
                }

                if (!isset($clean['Nomor Kursi']) && isset($clean['Plotting Kursi'])) {
                    $clean['Nomor Kursi'] = $clean['Plotting Kursi'];
                }
                if (!isset($clean['Nomor Kursi']) && isset($clean['Kursi'])) {
                    $clean['Nomor Kursi'] = $clean['Kursi'];
                }
                if (!isset($clean['Nomor Kursi']) || trim($clean['Nomor Kursi']) === '') {
                    $clean['Nomor Kursi'] = '-';
                }

                // Automatic Auto-Seat Assignment: If participant is VALID but has no seat assigned
                $statusClean = strtolower(trim($clean['Status Pembayaran'] ?? ''));
                $currentSeat = trim($clean['Nomor Kursi'] ?? '-');

                if (in_array($statusClean, ['valid', 'validkan']) && ($currentSeat === '' || $currentSeat === '-')) {
                    $prodi = $clean['Program Studi'] ?? 'Umum';
                    $newSeat = $this->generateNextSeatForProdi($prodi, $result);
                    $clean['Nomor Kursi']    = $newSeat;
                    $clean['Plotting Kursi'] = $newSeat;

                    // Persist to local DB immediately
                    if ($nim) {
                        PaymentVerification::updateOrCreate(
                            ['nim' => $nim],
                            ['status_pembayaran' => 'valid', 'nomor_kursi' => $newSeat]
                        );
                    }
                }

                $result[] = $clean;
            }

            return $result;
        });
    }

    /**
     * Paksa refresh cache lalu ambil ulang.
     */
    private function fetchFresh(): array
    {
        Cache::forget('sheetdb_peserta');
        return $this->fetchAll();
    }

    /**
     * Endpoint tombol Refresh Data di admin panel.
     */
    public function refresh()
    {
        $this->fetchFresh();
        return back()->with('success', 'Data berhasil diperbarui dari Google Sheets.');
    }

    /**
     * Halaman dashboard admin.
     */
    public function dashboard()
    {
        $data = $this->fetchAll();

        $totalPeserta     = count($data);
        $totalHadir       = count(array_filter($data, fn($p) => !empty($p['Waktu Kehadiran'])));
        $totalBelumHadir  = $totalPeserta - $totalHadir;
        $totalValid       = count(array_filter($data, fn($p) => in_array(strtolower($p['Status Pembayaran'] ?? ''), ['valid', 'validkan'])));
        $totalPlotting    = count(array_filter($data, fn($p) => !empty($p['Nomor Kursi']) && $p['Nomor Kursi'] !== '-'));

        // 5 pendaftar terbaru
        $recentPeserta = array_slice(array_reverse($data), 0, 5);

        return view('admin.dashboard', compact(
            'totalPeserta',
            'totalHadir',
            'totalBelumHadir',
            'totalValid',
            'totalPlotting',
            'recentPeserta'
        ));
    }

    /**
     * Halaman daftar peserta dengan search & filter.
     */
    public function peserta(Request $request)
    {
        $data = $this->fetchAll();

        // Search
        if ($request->filled('search')) {
            $s = strtolower($request->search);
            $data = array_filter($data, function ($p) use ($s) {
                return str_contains(strtolower($p['Nama Lengkap']    ?? ''), $s)
                    || str_contains(strtolower($p['NIM']             ?? ''), $s)
                    || str_contains(strtolower($p['Program Studi']   ?? ''), $s)
                    || str_contains(strtolower($p['Nomor Kursi']     ?? ''), $s)
                    || str_contains(strtolower($p['Email Address']   ?? ''), $s);
            });
        }

        // Filter program studi
        if ($request->filled('prodi')) {
            $prodi = strtolower($request->prodi);
            $data = array_filter($data, function ($p) use ($prodi) {
                return str_contains(strtolower($p['Program Studi'] ?? ''), $prodi);
            });
        }

        // Filter status kehadiran
        if ($request->has('status') && $request->status !== '') {
            $data = array_filter($data, function ($p) use ($request) {
                $sudahHadir = !empty($p['Waktu Kehadiran']);
                return $request->status === '1' ? $sudahHadir : !$sudahHadir;
            });
        }

        // Filter status pembayaran
        if ($request->filled('payment')) {
            $pay = strtolower($request->payment);
            $data = array_filter($data, function ($p) use ($pay) {
                $status = strtolower($p['Status Pembayaran'] ?? '');
                if ($pay === 'valid' || $pay === 'validkan') {
                    return in_array($status, ['valid', 'validkan']);
                }
                return $status === $pay;
            });
        }

        // Manual pagination
        $data        = array_values($data);
        $perPage     = 15;
        $currentPage = max(1, (int) $request->get('page', 1));
        $total       = count($data);
        $sliced      = array_slice($data, ($currentPage - 1) * $perPage, $perPage);

        $pagination = [
            'data'         => $sliced,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $currentPage,
            'last_page'    => (int) ceil($total / $perPage),
            'from'         => $total > 0 ? ($currentPage - 1) * $perPage + 1 : 0,
            'to'           => min($currentPage * $perPage, $total),
        ];

        return view('admin.peserta', compact('pagination'));
    }

    /**
     * Tandai peserta hadir — update kolom "Waktu Kehadiran" di SheetDB.
     */
    public function tandaiHadir(Request $request)
    {
        $request->validate(['nim' => 'required|string']);

        $nim      = $request->nim;
        $waktu    = now()->format('d/m/Y H:i:s');

        $response = Http::timeout(10)
            ->withoutVerifying()
            ->patch("{$this->sheetdbUrl}/NIM/{$nim}", [
                'data' => ['Waktu Kehadiran' => $waktu],
            ]);

        $this->fetchFresh();

        if ($response->successful()) {
            return back()->with('success', "Peserta NIM {$nim} berhasil ditandai hadir pada {$waktu}.");
        }

        return back()->with('error', 'Gagal memperbarui data di SheetDB. Coba lagi.');
    }

    /**
     * Dapatkan prefix kode bangku berdasarkan Program Studi.
     * SI -> Sistem Informasi (SI-01, SI-02...)
     * TI -> Teknik Informatika / Lainnya (TI-01, TI-02...)
     */
    private function getProdiPrefix(string $prodiName): string
    {
        $clean = strtolower(trim($prodiName));
        if (str_contains($clean, 'sistem') || str_contains($clean, 'si')) {
            return 'SI';
        }
        return 'TI';
    }

    /**
     * Auto-Floating: Hitung nomor bangku berikutnya untuk SI (SI-01..) atau TI (TI-01..).
     */
    private function generateNextSeatForProdi(string $prodiName, array $pesertaList): string
    {
        $prefix = $this->getProdiPrefix($prodiName);
        $maxNum = 0;

        // Cek data lokal database
        $dbSeats = PaymentVerification::whereNotNull('nomor_kursi')->pluck('nomor_kursi');
        foreach ($dbSeats as $seat) {
            $seatUpper = strtoupper(trim($seat));
            if (str_starts_with($seatUpper, $prefix . '-')) {
                $num = (int) str_replace($prefix . '-', '', $seatUpper);
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        // Cek data dari sheet
        foreach ($pesertaList as $p) {
            $seatUpper = strtoupper(trim($p['Nomor Kursi'] ?? ''));
            if (str_starts_with($seatUpper, $prefix . '-')) {
                $num = (int) str_replace($prefix . '-', '', $seatUpper);
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        $nextNum = $maxNum + 1;
        return $prefix . '-' . str_pad($nextNum, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Hapus / kosongkan alokasi nomor kursi peserta.
     */
    public function hapusKursi(Request $request)
    {
        $request->validate([
            'nim' => 'required|string',
        ]);

        $nim = trim($request->nim);

        PaymentVerification::where('nim', $nim)->update(['nomor_kursi' => null]);

        try {
            Http::timeout(10)
                ->withoutVerifying()
                ->patch("{$this->sheetdbUrl}/NIM/{$nim}", [
                    'data' => [
                        'Nomor Kursi'    => '-',
                        'Plotting Kursi' => '-',
                    ],
                ]);
        } catch (\Throwable $e) {}

        $this->fetchFresh();

        return back()->with('success', "Alokasi nomor kursi peserta NIM {$nim} berhasil dikosongkan.");
    }

    /**
     * Update status pembayaran peserta di database lokal & SheetDB.
     * Sekaligus memicu Auto-Floating Bangku jika status berubah jadi 'valid'.
     */
    public function updatePembayaran(Request $request)
    {
        $request->validate([
            'nim'    => 'required|string',
            'status' => 'required|string',
        ]);

        $nim       = trim($request->nim);
        $rawStatus = trim($request->status);

        if (in_array(strtolower($rawStatus), ['valid', 'validkan'])) {
            $status = 'valid';
        } else {
            $status = $rawStatus;
        }

        $pesertaList = $this->fetchAll();
        $pesertaItem = null;
        foreach ($pesertaList as $p) {
            if (trim($p['NIM'] ?? '') === $nim) {
                $pesertaItem = $p;
                break;
            }
        }

        $kursiAuto = null;
        $prodi     = $pesertaItem['Program Studi'] ?? 'Umum';

        // Auto-floating seat assignment when status becomes 'valid' and participant doesn't have a seat yet
        $currentSeat = trim($pesertaItem['Nomor Kursi'] ?? '-');
        if (in_array(strtolower($status), ['valid', 'validkan']) && ($currentSeat === '' || $currentSeat === '-')) {
            $kursiAuto = $this->generateNextSeatForProdi($prodi, $pesertaList);
        }

        $updateData = ['status_pembayaran' => $status];
        if ($kursiAuto) {
            $updateData['nomor_kursi'] = $kursiAuto;
        }

        PaymentVerification::updateOrCreate(['nim' => $nim], $updateData);

        try {
            $patchData = [
                'Status Pembayaran'  => $status,
                'Status Pembayaran ' => $status,
            ];
            if ($kursiAuto) {
                $patchData['Nomor Kursi']    = $kursiAuto;
                $patchData['Plotting Kursi'] = $kursiAuto;
            }

            Http::timeout(10)
                ->withoutVerifying()
                ->patch("{$this->sheetdbUrl}/NIM/{$nim}", [
                    'data' => $patchData,
                ]);

            $webAppUrl = env('APPS_SCRIPT_WEBAPP_URL');
            if ($webAppUrl) {
                $payload = ['nim' => $nim, 'status' => $status];
                if ($kursiAuto) $payload['nomor_kursi'] = $kursiAuto;
                Http::timeout(5)->withoutVerifying()->post($webAppUrl, $payload);
            }
        } catch (\Throwable $e) {}

        $this->fetchFresh();

        $msg = in_array(strtolower($status), ['valid', 'validkan'])
            ? "Pembayaran peserta NIM {$nim} ({$prodi}) disetujui ('valid'). " . ($kursiAuto ? "🎯 Bangku otomatis teralokasi ke '{$kursiAuto}' (Blok {$prodi})!" : "")
            : "Status pembayaran peserta NIM {$nim} diubah menjadi '{$status}'.";

        return back()->with('success', $msg);
    }

    /**
     * Update nomor kursi peserta.
     */
    public function updateKursi(Request $request)
    {
        $request->validate([
            'nim'         => 'required|string',
            'nomor_kursi' => 'required|string|max:20',
        ]);

        $nim   = trim($request->nim);
        $kursi = strtoupper(trim($request->nomor_kursi));

        PaymentVerification::updateOrCreate(
            ['nim' => $nim],
            ['nomor_kursi' => $kursi]
        );

        try {
            Http::timeout(10)
                ->withoutVerifying()
                ->patch("{$this->sheetdbUrl}/NIM/{$nim}", [
                    'data' => [
                        'Nomor Kursi'    => $kursi,
                        'Plotting Kursi' => $kursi,
                    ],
                ]);
        } catch (\Throwable $e) {}

        $this->fetchFresh();

        return back()->with('success', "Nomor kursi peserta NIM {$nim} berhasil diatur ke '{$kursi}'.");
    }

    /**
     * Plotting otomatis kursi peserta.
     */
    public function autoPlotting(Request $request)
    {
        $formatMode = $request->input('format', 'prodi_prefix'); // 'prodi_prefix' (TI-01) or 'grid' (A01)
        $mode       = $request->input('mode', 'unassigned'); // 'unassigned' or 'reset_all'

        $pesertaList = $this->fetchAll();

        if ($mode === 'reset_all') {
            PaymentVerification::query()->update(['nomor_kursi' => null]);
            $targets = $pesertaList;
        } else {
            $targets = array_values(array_filter($pesertaList, function ($p) {
                $kursi = trim($p['Nomor Kursi'] ?? '-');
                return $kursi === '' || $kursi === '-';
            }));
        }

        if (empty($targets)) {
            return back()->with('error', 'Tidak ada peserta yang perlu di-plotting.');
        }

        $count = 0;

        if ($formatMode === 'prodi_prefix') {
            // Group by Prodi
            $grouped = [];
            foreach ($targets as $p) {
                $pr = trim($p['Program Studi'] ?? 'Umum');
                $grouped[$pr][] = $p;
            }

            foreach ($grouped as $prodiName => $pList) {
                foreach ($pList as $p) {
                    $nim = trim($p['NIM'] ?? '');
                    if (!$nim) continue;

                    $currentList = $this->fetchAll();
                    $seatCode = $this->generateNextSeatForProdi($prodiName, $currentList);

                    PaymentVerification::updateOrCreate(
                        ['nim' => $nim],
                        ['nomor_kursi' => $seatCode]
                    );

                    try {
                        Http::timeout(5)->withoutVerifying()->patch("{$this->sheetdbUrl}/NIM/{$nim}", [
                            'data' => [
                                'Nomor Kursi'    => $seatCode,
                                'Plotting Kursi' => $seatCode,
                            ],
                        ]);
                    } catch (\Throwable $e) {}

                    $count++;
                }
            }
        } else {
            $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
            $cols = range(1, 12);
            $availableSeats = [];

            $existingSeats = [];
            if ($mode !== 'reset_all') {
                foreach ($pesertaList as $p) {
                    $k = trim($p['Nomor Kursi'] ?? '-');
                    if ($k !== '' && $k !== '-') {
                        $existingSeats[$k] = true;
                    }
                }
            }

            foreach ($rows as $r) {
                foreach ($cols as $c) {
                    $seatCode = $r . str_pad($c, 2, '0', STR_PAD_LEFT);
                    if (!isset($existingSeats[$seatCode])) {
                        $availableSeats[] = $seatCode;
                    }
                }
            }

            foreach ($targets as $p) {
                if (empty($availableSeats)) break;

                $nim      = trim($p['NIM'] ?? '');
                $seatCode = array_shift($availableSeats);

                if ($nim) {
                    PaymentVerification::updateOrCreate(
                        ['nim' => $nim],
                        ['nomor_kursi' => $seatCode]
                    );
                    $count++;
                }
            }
        }

        $this->fetchFresh();

        return back()->with('success', "⚡ Resepsionis Pinter: Berhasil mengalokasikan bangku otomatis untuk {$count} peserta!");
    }

    /**
     * Reset seluruh plotting kursi peserta.
     */
    public function resetPlotting()
    {
        PaymentVerification::query()->update(['nomor_kursi' => null]);
        $this->fetchFresh();

        return back()->with('success', 'Seluruh alokasi nomor kursi peserta berhasil di-reset.');
    }

    /**
     * Halaman Plotting & Denah Kursi.
     */
    public function plotting(Request $request)
    {
        $pesertaList = $this->fetchAll();

        $assigned   = [];
        $unassigned = [];

        foreach ($pesertaList as $p) {
            $kursi = trim($p['Nomor Kursi'] ?? '-');
            if ($kursi !== '' && $kursi !== '-') {
                $assigned[$kursi] = $p;
                $assigned[strtoupper($kursi)] = $p;
                $cleanCode = str_replace(['-', ' '], '', strtoupper($kursi));
                $assigned[$cleanCode] = $p;
            } else {
                $unassigned[] = $p;
            }
        }

        // 10 Baris (a..j) x 15 Kolom per Sayap (150 SI + 150 TI/MIK = 300 Kursi Total)
        $rows = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j'];
        $cols = range(1, 15);

        $totalCapacity = 300;
        $totalAssigned = count(array_filter($pesertaList, fn($p) => !empty($p['Nomor Kursi']) && $p['Nomor Kursi'] !== '-'));
        $totalHadir    = count(array_filter($pesertaList, fn($p) => !empty($p['Waktu Kehadiran'])));

        return view('admin.plotting', compact(
            'pesertaList',
            'assigned',
            'unassigned',
            'rows',
            'cols',
            'totalCapacity',
            'totalAssigned',
            'totalHadir'
        ));
    }

    /**
     * Halaman scan absensi.
     */
    public function absensi()
    {
        return view('admin.absensi');
    }

    /**
     * Endpoint API untuk mengambil 15 scan log terbaru.
     */
    public function recentLogs()
    {
        $logs = ScanLog::latest('scanned_at')->take(15)->get();
        return response()->json($logs);
    }

    /**
     * Proses scan QR — support ID Unik langsung, URL dengan ?id=, NIM, atau Email.
     */
    public function scanQr(Request $request)
    {
        $request->validate(['nim' => 'required|string']);
        $scanned = trim($request->nim);

        $searchValue = $scanned;
        if (filter_var($scanned, FILTER_VALIDATE_URL) || str_contains($scanned, 'script.google.com')) {
            $parsed = parse_url($scanned);
            if (isset($parsed['query'])) {
                parse_str($parsed['query'], $params);
                if (isset($params['id'])) {
                    $searchValue = trim($params['id']);
                }
            }
        }

        $allPeserta = $this->fetchAll();
        $peserta    = null;

        foreach ($allPeserta as $p) {
            $idUnik = trim($p['ID Unik'] ?? '');
            $nim    = trim($p['NIM'] ?? '');
            $email  = trim($p['Email Address'] ?? $p['Email'] ?? '');

            if ($searchValue !== '' && ($idUnik === $searchValue || $nim === $searchValue || strtolower($email) === strtolower($searchValue))) {
                $peserta = $p;
                break;
            }
        }

        if (! $peserta && str_starts_with($searchValue, 'YDS-')) {
            $parts = explode('-', $searchValue);
            if (count($parts) >= 2 && is_numeric($parts[1])) {
                $rowIndex = ((int)$parts[1]) - 2;
                if (isset($allPeserta[$rowIndex])) {
                    $peserta = $allPeserta[$rowIndex];
                }
            }
        }

        if (! $peserta) {
            $peserta = $this->searchSheet('ID Unik', $searchValue)
                    ?? $this->searchSheet('NIM', $searchValue)
                    ?? $this->searchSheet('Email Address', $searchValue);
        }

        $user = Auth::user();
        $operatorName = $user?->name ?? 'Panitia';
        $operatorPin  = $user?->pin  ?? '123456';

        $displayCode = $searchValue !== '' ? $searchValue : $scanned;

        if (! $peserta) {
            ScanLog::create([
                'user_id'      => $user?->id,
                'panitia_name' => $operatorName,
                'panitia_pin'  => $operatorPin,
                'peserta_nim'  => $displayCode,
                'status'       => 'not_found',
                'message'      => "Kode '{$displayCode}' tidak ditemukan di database.",
                'scanned_at'   => now(),
            ]);

            return response()->json([
                'status'  => 'not_found',
                'message' => "Peserta dengan ID/NIM '{$displayCode}' tidak ditemukan.",
                'debug'   => "Dicari: {$displayCode}",
            ], 404);
        }

        $namaPeserta  = $peserta['Nama Lengkap'] ?? $peserta['nama'] ?? 'Peserta';
        $nimPeserta   = $peserta['NIM'] ?? '-';
        $prodiPeserta = $peserta['Program Studi'] ?? '-';

        // Sudah hadir?
        if (! empty($peserta['Waktu Kehadiran'])) {
            ScanLog::create([
                'user_id'      => $user?->id,
                'panitia_name' => $operatorName,
                'panitia_pin'  => $operatorPin,
                'peserta_nim'  => $nimPeserta,
                'peserta_nama' => $namaPeserta,
                'peserta_prodi'=> $prodiPeserta,
                'status'       => 'already',
                'message'      => "{$namaPeserta} sudah hadir pada {$peserta['Waktu Kehadiran']}.",
                'scanned_at'   => now(),
            ]);

            return response()->json([
                'status'  => 'already',
                'message' => "{$namaPeserta} sudah hadir pada {$peserta['Waktu Kehadiran']}.",
                'peserta' => $peserta,
            ]);
        }

        // Tandai hadir
        $waktu = now()->format('d/m/Y H:i:s');
        $patch = Http::timeout(10)
            ->withoutVerifying()
            ->patch("{$this->sheetdbUrl}/NIM/{$nimPeserta}", [
                'data' => ['Waktu Kehadiran' => $waktu],
            ]);

        Cache::forget('sheetdb_peserta');

        if (! $patch->successful()) {
            ScanLog::create([
                'user_id'      => $user?->id,
                'panitia_name' => $operatorName,
                'panitia_pin'  => $operatorPin,
                'peserta_nim'  => $nimPeserta,
                'peserta_nama' => $namaPeserta,
                'peserta_prodi'=> $prodiPeserta,
                'status'       => 'error',
                'message'      => 'Gagal menyimpan kehadiran ke SheetDB.',
                'scanned_at'   => now(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan kehadiran. Coba scan ulang.',
            ], 500);
        }

        ScanLog::create([
            'user_id'      => $user?->id,
            'panitia_name' => $operatorName,
            'panitia_pin'  => $operatorPin,
            'peserta_nim'  => $nimPeserta,
            'peserta_nama' => $namaPeserta,
            'peserta_prodi'=> $prodiPeserta,
            'status'       => 'success',
            'message'      => "Kehadiran {$namaPeserta} berhasil dicatat.",
            'scanned_at'   => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => "Kehadiran {$namaPeserta} berhasil dicatat pukul {$waktu}.",
            'peserta' => array_merge($peserta, ['Waktu Kehadiran' => $waktu]),
        ]);
    }

    /**
     * Helper: cari satu baris di SheetDB berdasarkan kolom & nilai tertentu.
     */
    private function searchSheet(string $column, string $value): ?array
    {
        $response = Http::timeout(10)
            ->withoutVerifying()
            ->get("{$this->sheetdbUrl}/search", [
                $column => $value,
            ]);

        if ($response->successful() && ! empty($response->json())) {
            $item = $response->json()[0];
            if (is_array($item)) {
                $clean = [];
                foreach ($item as $key => $val) {
                    $cleanKey = trim($key);
                    $clean[$cleanKey] = is_string($val) ? trim($val) : $val;
                }
                if (isset($clean['Nama Lengkap']) && !isset($clean['nama'])) {
                    $clean['nama'] = $clean['Nama Lengkap'];
                }
                if (isset($clean['Email']) && !isset($clean['Email Address'])) {
                    $clean['Email Address'] = $clean['Email'];
                }
                if (isset($clean['Email Address']) && !isset($clean['Email'])) {
                    $clean['Email'] = $clean['Email Address'];
                }
                return $clean;
            }
            return $item;
        }

        return null;
    }

    /**
     * Export semua data ke CSV.
     */
    public function export(Request $request)
    {
        $data = $this->fetchAll();

        // Search
        if ($request->filled('search')) {
            $s = strtolower($request->search);
            $data = array_filter($data, function ($p) use ($s) {
                return str_contains(strtolower($p['Nama Lengkap']    ?? ''), $s)
                    || str_contains(strtolower($p['NIM']             ?? ''), $s)
                    || str_contains(strtolower($p['Program Studi']   ?? ''), $s)
                    || str_contains(strtolower($p['Nomor Kursi']     ?? ''), $s)
                    || str_contains(strtolower($p['Email Address']   ?? ''), $s);
            });
        }

        // Filter program studi
        if ($request->filled('prodi')) {
            $prodi = strtolower($request->prodi);
            $data = array_filter($data, function ($p) use ($prodi) {
                return str_contains(strtolower($p['Program Studi'] ?? ''), $prodi);
            });
        }

        // Filter status kehadiran
        if ($request->has('status') && $request->status !== '') {
            $data = array_filter($data, function ($p) use ($request) {
                $sudahHadir = !empty($p['Waktu Kehadiran']);
                return $request->status === '1' ? $sudahHadir : !$sudahHadir;
            });
        }

        // Filter status pembayaran (case insensitive)
        if ($request->filled('payment')) {
            $pay = strtolower($request->payment);
            $data = array_filter($data, function ($p) use ($pay) {
                $status = strtolower($p['Status Pembayaran'] ?? '');
                if ($pay === 'valid' || $pay === 'validkan') {
                    return in_array($status, ['valid', 'validkan']);
                }
                return $status === $pay;
            });
        }

        $data = array_values($data);

        $filename = 'peserta_yudisium_' . now()->format('Ymd_His') . '.csv';

        $callback = function () use ($data) {
            if (ob_get_level()) {
                @ob_end_clean();
            }

            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // BOM UTF-8 untuk Excel

            fputcsv($handle, [
                'No', 'Timestamp', 'NIM', 'Nama Lengkap', 'Email', 'Program Studi',
                'No. HP (WA)', 'Nomor Kursi', 'Bank Asal', 'Nama Pemilik Rekening', 'Nomor Rekening',
                'Tanggal Transfer', 'Status Pembayaran', 'ID Unik',
                'Status Email', 'Waktu Kehadiran',
            ]);

            foreach ($data as $i => $p) {
                fputcsv($handle, [
                    $i + 1,
                    $p['Timestamp']                ?? '',
                    $p['NIM']                      ?? '',
                    $p['Nama Lengkap']             ?? '',
                    $p['Email Address']            ?? '',
                    $p['Program Studi']            ?? '',
                    $p['No. Handphone (WA)']       ?? '',
                    $p['Nomor Kursi']              ?? '-',
                    $p['Bank Asal']                ?? '',
                    $p['Nama Pemilik Rekening']    ?? '',
                    $p['Nomor Rekening']           ?? '',
                    $p['Tanggal Transfer']         ?? '',
                    $p['Status Pembayaran']        ?? '',
                    $p['ID Unik']                  ?? '',
                    $p['Status Email']             ?? '',
                    $p['Waktu Kehadiran']          ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Expires'             => '0',
            'Pragma'              => 'public',
        ]);
    }

    /**
     * Halaman Log Aktivitas Scan Panitia.
     */
    public function logs(Request $request)
    {
        $query = ScanLog::with('user')->latest('scanned_at');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('panitia_name', 'like', "%{$s}%")
                  ->orWhere('panitia_pin', 'like', "%{$s}%")
                  ->orWhere('peserta_nama', 'like', "%{$s}%")
                  ->orWhere('peserta_nim', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $logs = $query->paginate(20)->withQueryString();

        return view('admin.logs', compact('logs'));
    }
}
