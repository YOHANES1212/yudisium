<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Client\Pool;
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
        @set_time_limit(120);
        $url = config('services.sheetdb.url', env('SHEETDB_URL', 'https://sheetdb.io/api/v1/71445zve8u6f7'));
        if (str_starts_with($url, '//')) {
            $url = 'https:' . $url;
        } elseif (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            $url = 'https://' . $url;
        }
        $this->sheetdbUrl = $url;
    }

    /**
     * Ambil semua data dari SheetDB (di-cache 60 detik agar tidak spam API).
     */
    private function fetchAll(): array
    {
        return Cache::remember('sheetdb_peserta', 60, function () {
            $response = Http::timeout(20)
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

                $key = $this->getPesertaKey($clean);
                $clean['_key'] = $key;

                if ($key !== '' && isset($localVerifications[$key])) {
                    $local = $localVerifications[$key];
                    if (!empty($local->status_pembayaran)) {
                        $clean['Status Pembayaran'] = $local->status_pembayaran;
                    }
                    if (!empty($local->nomor_kursi)) {
                        $clean['Nomor Kursi'] = $local->nomor_kursi;
                    }
                    if (!empty($local->validated_by)) {
                        $clean['Validated By'] = $local->validated_by;
                    }
                    if (!empty($local->validated_at)) {
                        $clean['Validated At'] = $local->validated_at->format('d/m/Y H:i');
                    }
                    if (!empty($local->waktu_kehadiran)) {
                        $clean['Waktu Kehadiran'] = $local->waktu_kehadiran;
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
                } else {
                    $clean['Nomor Kursi'] = $this->normalizeKursi($clean['Nomor Kursi']);
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
     * Hapus cache data peserta agar request berikutnya mengambil data terbaru.
     */
    private function fetchFresh(): void
    {
        Cache::forget('sheetdb_peserta');
    }

    /**
     * Endpoint tombol Refresh Data di admin panel.
     */
    public function refresh()
    {
        Cache::forget('sheetdb_peserta');
        $this->fetchAll();
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
     * Helper: Generate a unique fallback key for any participant row.
     */
    private function getPesertaKey(array $p): string
    {
        $nim   = trim($p['NIM'] ?? '');
        $email = strtolower(trim($p['Email Address'] ?? $p['Email'] ?? ''));
        $nama  = strtolower(trim($p['Nama Lengkap'] ?? $p['nama'] ?? ''));

        if ($nim !== '' && $nim !== '-') {
            return $nim;
        }
        if ($email !== '' && $email !== '-') {
            return 'EMAIL:' . $email;
        }
        if ($nama !== '' && $nama !== '-') {
            return 'NAMA:' . $nama;
        }
        return '';
    }

    /**
     * Helper: Find a participant in list by key, nim, email, or name.
     */
    private function findPesertaInList(Request $request, array $pesertaList): ?array
    {
        $reqNim   = trim($request->input('nim', ''));
        $reqEmail = strtolower(trim($request->input('email', '')));
        $reqNama  = strtolower(trim($request->input('nama', '')));
        $reqKey   = trim($request->input('key', ''));

        foreach ($pesertaList as $p) {
            $pNim   = trim($p['NIM'] ?? '');
            $pEmail = strtolower(trim($p['Email Address'] ?? $p['Email'] ?? ''));
            $pNama  = strtolower(trim($p['Nama Lengkap'] ?? $p['nama'] ?? ''));
            $pKey   = $this->getPesertaKey($p);

            if ($reqKey !== '' && $pKey === $reqKey) {
                return $p;
            }
            if ($reqNim !== '' && $reqNim !== '-' && $pNim === $reqNim) {
                return $p;
            }
            if ($reqEmail !== '' && $reqEmail !== '-' && $pEmail === $reqEmail) {
                return $p;
            }
            if ($reqNama !== '' && $reqNama !== '-' && $pNama === $reqNama) {
                return $p;
            }
        }
        return null;
    }

    /**
     * Tandai peserta hadir — update kolom "Waktu Kehadiran" via Apps Script & database lokal.
     */
    public function tandaiHadir(Request $request)
    {
        $pesertaList = $this->fetchAll();
        $pesertaItem = $this->findPesertaInList($request, $pesertaList);

        if (!$pesertaItem) {
            return back()->with('error', 'Data peserta tidak ditemukan.');
        }

        $key   = $this->getPesertaKey($pesertaItem);
        $waktu = now()->format('d/m/Y H:i:s');
        $displayName = $pesertaItem['Nama Lengkap'] ?? $pesertaItem['nama'] ?? 'Peserta';

        PaymentVerification::updateOrCreate(
            ['nim' => $key],
            ['waktu_kehadiran' => $waktu]
        );

        try {
            $pNim   = trim($pesertaItem['NIM'] ?? '');
            $pEmail = trim($pesertaItem['Email Address'] ?? $pesertaItem['Email'] ?? '');
            $pNama  = trim($pesertaItem['Nama Lengkap'] ?? $pesertaItem['nama'] ?? '');

            $searchPayload = [];
            if ($pNim !== '' && $pNim !== '-') {
                $searchPayload['nim'] = $pNim;
            } elseif ($pEmail !== '' && $pEmail !== '-') {
                $searchPayload['Email Address'] = $pEmail;
            } else {
                $searchPayload['Nama Lengkap'] = $pNama;
            }

            Http::timeout(5)->withoutVerifying()->post($this->sheetdbUrl, array_merge($searchPayload, [
                'updates' => ['Waktu Kehadiran' => $waktu],
            ]));
        } catch (\Throwable $e) {}

        $this->fetchFresh();

        return back()->with('success', "Peserta {$displayName} berhasil ditandai hadir pada {$waktu}.");
    }

    /**
     * Batalkan status kehadiran peserta.
     */
    public function batalHadir(Request $request)
    {
        $pesertaList = $this->fetchAll();
        $pesertaItem = $this->findPesertaInList($request, $pesertaList);

        if (!$pesertaItem) {
            return back()->with('error', 'Data peserta tidak ditemukan.');
        }

        $key = $this->getPesertaKey($pesertaItem);
        $displayName = $pesertaItem['Nama Lengkap'] ?? $pesertaItem['nama'] ?? 'Peserta';

        PaymentVerification::where('nim', $key)->update(['waktu_kehadiran' => null]);

        try {
            $pNim   = trim($pesertaItem['NIM'] ?? '');
            $pEmail = trim($pesertaItem['Email Address'] ?? $pesertaItem['Email'] ?? '');
            $pNama  = trim($pesertaItem['Nama Lengkap'] ?? $pesertaItem['nama'] ?? '');

            $searchPayload = [];
            if ($pNim !== '' && $pNim !== '-') {
                $searchPayload['nim'] = $pNim;
            } elseif ($pEmail !== '' && $pEmail !== '-') {
                $searchPayload['Email Address'] = $pEmail;
            } else {
                $searchPayload['Nama Lengkap'] = $pNama;
            }

            Http::timeout(5)->withoutVerifying()->post($this->sheetdbUrl, array_merge($searchPayload, [
                'updates' => ['Waktu Kehadiran' => ''],
            ]));
        } catch (\Throwable $e) {}

        $this->fetchFresh();

        return back()->with('success', "Status kehadiran peserta {$displayName} berhasil dibatalkan.");
    }

    /**
     * Dapatkan prefix kode bangku berdasarkan Program Studi.
     * M -> Magister (M1, M2...)
     * S -> Sistem Informasi (S1, S2...)
     * T -> Teknik Informatika / Lainnya (T1, T2...)
     */
    private function getProdiPrefix(string $prodiName, string $nim = ''): string
    {
        $clean = strtolower(trim($prodiName));
        $nimClean = trim($nim);

        if (str_contains($clean, 'magister') || str_contains($clean, 'mik') || str_contains($clean, 's2') || str_contains($nimClean, '0804')) {
            return 'M';
        }
        if (str_contains($clean, 'sistem') || str_contains($clean, 'si') || str_contains($nimClean, '0803')) {
            return 'S';
        }
        return 'T';
    }

    /**
     * Helper: Parse NIM into numeric components (Year, Code, Sequence) to handle unpadded NIMs gracefully.
     */
    private function parseNimComponents(string $nimStr): array
    {
        $digits = preg_replace('/[^0-9]/', '', $nimStr);
        if (strlen($digits) < 4) {
            return ['year' => 0, 'code' => '', 'seq' => 0, 'raw' => $digits];
        }
        $year = (int) substr($digits, 0, 4);
        if (strlen($digits) >= 8) {
            $code = substr($digits, 4, 4);
            $seq  = (int) substr($digits, 8);
        } else {
            $code = substr($digits, 4);
            $seq  = 0;
        }
        return ['year' => $year, 'code' => $code, 'seq' => $seq, 'raw' => $digits];
    }

    /**
     * Auto-Floating: Hitung nomor bangku berikutnya untuk M (M1..), S (S1..), atau T (T1..).
     */
    private function generateNextSeatForProdi(string $prodiName, array $pesertaList, string $nim = ''): string
    {
        $prefix = $this->getProdiPrefix($prodiName, $nim);
        $maxNum = 0;

        // Cek data lokal database
        $dbSeats = PaymentVerification::whereNotNull('nomor_kursi')->pluck('nomor_kursi');
        foreach ($dbSeats as $seat) {
            $seatUpper = strtoupper(trim($seat));
            // Cross compatibility for MIK / M, SI- / S, TI- / T
            $normalizedSeat = str_replace(['MIK-', 'MIK'], 'M', str_replace(['SI-', 'SI'], 'S', str_replace(['TI-', 'TI'], 'T', $seatUpper)));
            if (str_starts_with($normalizedSeat, $prefix)) {
                $num = (int) preg_replace('/[^0-9]/', '', substr($normalizedSeat, strlen($prefix)));
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        // Cek data dari sheet
        foreach ($pesertaList as $p) {
            $seatUpper = strtoupper(trim($p['Nomor Kursi'] ?? ''));
            $normalizedSeat = str_replace(['MIK-', 'MIK'], 'M', str_replace(['SI-', 'SI'], 'S', str_replace(['TI-', 'TI'], 'T', $seatUpper)));
            if (str_starts_with($normalizedSeat, $prefix)) {
                $num = (int) preg_replace('/[^0-9]/', '', substr($normalizedSeat, strlen($prefix)));
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        $nextNum = $maxNum + 1;
        return $prefix . $nextNum;
    }


    /**
     * Hapus / kosongkan alokasi nomor kursi peserta.
     */
    public function hapusKursi(Request $request)
    {
        $pesertaList = $this->fetchAll();
        $pesertaItem = $this->findPesertaInList($request, $pesertaList);

        if (!$pesertaItem) {
            return back()->with('error', 'Data peserta tidak ditemukan.');
        }

        $key = $this->getPesertaKey($pesertaItem);
        $displayName = $pesertaItem['Nama Lengkap'] ?? $pesertaItem['nama'] ?? 'Peserta';

        PaymentVerification::where('nim', $key)->update(['nomor_kursi' => null]);

        try {
            $pNim   = trim($pesertaItem['NIM'] ?? '');
            $pEmail = trim($pesertaItem['Email Address'] ?? $pesertaItem['Email'] ?? '');
            $pNama  = trim($pesertaItem['Nama Lengkap'] ?? $pesertaItem['nama'] ?? '');

            $searchPayload = [];
            if ($pNim !== '' && $pNim !== '-') {
                $searchPayload['nim'] = $pNim;
            } elseif ($pEmail !== '' && $pEmail !== '-') {
                $searchPayload['Email Address'] = $pEmail;
            } else {
                $searchPayload['Nama Lengkap'] = $pNama;
            }

            Http::timeout(10)->withoutVerifying()->post($this->sheetdbUrl, array_merge($searchPayload, [
                'updates' => [
                    'Nomor Kursi'    => '-',
                    'Plotting Kursi' => '-',
                ],
            ]));
        } catch (\Throwable $e) {}

        $this->fetchFresh();

        return back()->with('success', "Alokasi nomor kursi peserta {$displayName} berhasil dikosongkan.");
    }

    /**
     * Update status pembayaran peserta di database lokal & sheet.
     * Sekaligus memicu Auto-Floating Bangku jika status berubah jadi 'valid'.
     */
    public function updatePembayaran(Request $request)
    {
        $rawStatus = trim($request->input('status', ''));
        if (!$rawStatus) {
            return back()->with('error', 'Status pembayaran wajib diisi.');
        }

        $pesertaList = $this->fetchAll();
        $pesertaItem = $this->findPesertaInList($request, $pesertaList);

        if (!$pesertaItem) {
            return back()->with('error', 'Data peserta tidak ditemukan.');
        }

        $key = $this->getPesertaKey($pesertaItem);
        if (!$key) {
            return back()->with('error', 'Identitas peserta tidak dapat dikenali.');
        }

        $status = in_array(strtolower($rawStatus), ['valid', 'validkan']) ? 'valid' : $rawStatus;
        $prodi  = $pesertaItem['Program Studi'] ?? 'Umum';
        $displayName = $pesertaItem['Nama Lengkap'] ?? $pesertaItem['nama'] ?? 'Peserta';

        $kursiAuto = null;
        $currentSeat = trim($pesertaItem['Nomor Kursi'] ?? '-');
        if (in_array(strtolower($status), ['valid', 'validkan']) && ($currentSeat === '' || $currentSeat === '-')) {
            $kursiAuto = $this->generateNextSeatForProdi($prodi, $pesertaList);
        }

        $validatorName = Auth::user()?->name ?? 'Panitia';
        $updateData = [
            'status_pembayaran' => $status,
            'validated_by'      => $validatorName,
            'validated_at'      => now(),
        ];
        if ($kursiAuto) {
            $updateData['nomor_kursi'] = $kursiAuto;
        }

        PaymentVerification::updateOrCreate(['nim' => $key], $updateData);

        try {
            $pNim   = trim($pesertaItem['NIM'] ?? '');
            $pEmail = trim($pesertaItem['Email Address'] ?? $pesertaItem['Email'] ?? '');
            $pNama  = trim($pesertaItem['Nama Lengkap'] ?? $pesertaItem['nama'] ?? '');

            $updates = [
                'Status Pembayaran'  => $status,
                'Status Pembayaran ' => $status,
            ];
            if ($kursiAuto) {
                $updates['Nomor Kursi']    = $kursiAuto;
                $updates['Plotting Kursi'] = $kursiAuto;
            }

            $postPayload = [
                'status'             => $status,
                'Status Pembayaran'  => $status,
                'Status Pembayaran ' => $status,
                'updates'            => $updates,
            ];
            if ($pNim !== '' && $pNim !== '-') {
                $postPayload['nim'] = $pNim;
                $postPayload['NIM'] = $pNim;
            }
            if ($pEmail !== '' && $pEmail !== '-') {
                $postPayload['email']          = $pEmail;
                $postPayload['Email']          = $pEmail;
                $postPayload['Email ']         = $pEmail;
                $postPayload['Email Address']  = $pEmail;
                $postPayload['Email Address '] = $pEmail;
            }
            if ($pNama !== '' && $pNama !== '-') {
                $postPayload['nama']          = $pNama;
                $postPayload['Nama']          = $pNama;
                $postPayload['Nama Lengkap']  = $pNama;
                $postPayload['Nama Lengkap '] = $pNama;
            }
            if ($kursiAuto) {
                $postPayload['nomor_kursi']    = $kursiAuto;
                $postPayload['Nomor Kursi']    = $kursiAuto;
                $postPayload['Plotting Kursi'] = $kursiAuto;
            }

            Http::timeout(30)->withoutVerifying()->post($this->sheetdbUrl, $postPayload);

        } catch (\Throwable $e) {}

        $this->fetchFresh();

        $msg = in_array(strtolower($status), ['valid', 'validkan'])
            ? "Pembayaran peserta {$displayName} disetujui ('valid'). " . ($kursiAuto ? "🎯 Bangku otomatis teralokasi ke '{$kursiAuto}' (Blok {$prodi})!" : "")
            : "Status pembayaran peserta {$displayName} diubah menjadi '{$status}'.";

        return back()->with('success', $msg);
    }

    /**
     * Update nomor kursi peserta.
     */
    public function updateKursi(Request $request)
    {
        $request->validate([
            'nomor_kursi' => 'required|string|max:20',
        ]);

        $pesertaList = $this->fetchAll();
        $pesertaItem = $this->findPesertaInList($request, $pesertaList);

        if (!$pesertaItem) {
            return back()->with('error', 'Data peserta tidak ditemukan.');
        }

        $key   = $this->getPesertaKey($pesertaItem);
        $kursi = strtoupper(trim($request->nomor_kursi));
        $displayName = $pesertaItem['Nama Lengkap'] ?? $pesertaItem['nama'] ?? 'Peserta';

        PaymentVerification::updateOrCreate(
            ['nim' => $key],
            ['nomor_kursi' => $kursi]
        );

        try {
            $pNim   = trim($pesertaItem['NIM'] ?? '');
            $pEmail = trim($pesertaItem['Email Address'] ?? $pesertaItem['Email'] ?? '');
            $pNama  = trim($pesertaItem['Nama Lengkap'] ?? $pesertaItem['nama'] ?? '');

            $postPayload = [
                'nomor_kursi'    => $kursi,
                'Nomor Kursi'    => $kursi,
                'Plotting Kursi' => $kursi,
            ];
            if ($pNim !== '' && $pNim !== '-') {
                $postPayload['nim'] = $pNim;
                $postPayload['NIM'] = $pNim;
            }
            if ($pEmail !== '' && $pEmail !== '-') {
                $postPayload['email']          = $pEmail;
                $postPayload['Email']          = $pEmail;
                $postPayload['Email ']         = $pEmail;
                $postPayload['Email Address']  = $pEmail;
                $postPayload['Email Address '] = $pEmail;
            }
            if ($pNama !== '' && $pNama !== '-') {
                $postPayload['nama']          = $pNama;
                $postPayload['Nama']          = $pNama;
                $postPayload['Nama Lengkap']  = $pNama;
                $postPayload['Nama Lengkap '] = $pNama;
            }

            Http::timeout(10)->withoutVerifying()->post($this->sheetdbUrl, $postPayload);
        } catch (\Throwable $e) {}

        $this->fetchFresh();

        return back()->with('success', "Nomor kursi peserta {$displayName} berhasil diatur ke '{$kursi}'.");
    }

    /**
     * Plotting otomatis kursi peserta berdasarkan NIM (Ascending).
     */
    public function autoPlotting(Request $request)
    {
        $pesertaList = $this->fetchAll();

        if (empty($pesertaList)) {
            return back()->with('error', 'Tidak ada data peserta yang ditemukan.');
        }

        // Kelompokkan peserta berdasarkan prefix Prodi (M, S, T)
        $mGroup = [];
        $sGroup = [];
        $tGroup = [];

        foreach ($pesertaList as $p) {
            $prodi  = trim($p['Program Studi'] ?? '');
            $nim    = trim($p['NIM'] ?? '');
            $prefix = $this->getProdiPrefix($prodi, $nim);

            if ($prefix === 'M') {
                $mGroup[] = $p;
            } elseif ($prefix === 'S') {
                $sGroup[] = $p;
            } else {
                $tGroup[] = $p;
            }
        }

        // Pengurutan berdasarkan NIM (ascending / dari terkecil ke terbesar)
        // Menggunakan parsing cerdas komponen NIM (Angkatan -> Kode Prodi -> Nomor Urut Peserta)
        $sortByNim = function ($a, $b) {
            $nimA = $a['NIM'] ?? '';
            $nimB = $b['NIM'] ?? '';

            $cA = $this->parseNimComponents($nimA);
            $cB = $this->parseNimComponents($nimB);

            if ($cA['raw'] !== '' && $cB['raw'] !== '') {
                if ($cA['year'] !== $cB['year']) {
                    return $cA['year'] <=> $cB['year'];
                }
                if ($cA['code'] !== $cB['code']) {
                    return strcmp($cA['code'], $cB['code']);
                }
                if ($cA['seq'] !== $cB['seq']) {
                    return $cA['seq'] <=> $cB['seq'];
                }
                return strcmp($cA['raw'], $cB['raw']);
            }
            if ($cA['raw'] !== '' && $cB['raw'] === '') return -1;
            if ($cA['raw'] === '' && $cB['raw'] !== '') return 1;
            return strcmp($a['Nama Lengkap'] ?? $a['nama'] ?? '', $b['Nama Lengkap'] ?? $b['nama'] ?? '');
        };

        usort($mGroup, $sortByNim);
        usort($sGroup, $sortByNim);
        usort($tGroup, $sortByNim);

        $allGroups = [
            'M' => $mGroup,
            'S' => $sGroup,
            'T' => $tGroup,
        ];

        $bulkUpdates = [];
        $count = 0;

        foreach ($allGroups as $prefix => $pList) {
            $usedSeats = [];
            $unassignedParticipants = [];

            // Pass 1: Alokasikan bangku LANGSUNG sesuai nomor digit belakang NIM (misal NIM belakang 1 -> S1/T1/M1)
            foreach ($pList as $p) {
                $key = $this->getPesertaKey($p);
                $nim = $p['NIM'] ?? '';
                $parsed = $this->parseNimComponents($nim);
                $targetSeq = $parsed['seq'];

                if ($key !== '' && $targetSeq > 0) {
                    $targetSeat = $prefix . $targetSeq;
                    if (!isset($usedSeats[$targetSeat])) {
                        $usedSeats[$targetSeat] = true;
                        $bulkUpdates[$key] = $targetSeat;
                        $count++;
                        continue;
                    }
                }
                $unassignedParticipants[] = $p;
            }

            // Pass 2: Untuk peserta bentrok atau tanpa NIM valid, alokasikan ke nomor bangku kosong berikutnya
            $fallbackNum = 1;
            foreach ($unassignedParticipants as $p) {
                $key = $this->getPesertaKey($p);
                if ($key === '') continue;

                while (isset($usedSeats[$prefix . $fallbackNum])) {
                    $fallbackNum++;
                }

                $seatCode = $prefix . $fallbackNum;
                $usedSeats[$seatCode] = true;
                $bulkUpdates[$key] = $seatCode;
                $count++;
            }
        }

        // Update database lokal secara massal dalam 1 transaksi DB: reset dulu lalu isi baru
        DB::transaction(function () use ($bulkUpdates) {
            PaymentVerification::query()->update(['nomor_kursi' => null]);
            foreach ($bulkUpdates as $key => $seatCode) {
                PaymentVerification::updateOrCreate(
                    ['nim' => $key],
                    ['nomor_kursi' => $seatCode]
                );
            }
        });

        // Bersihkan cache agar data terbaru langsung dibaca dari DB lokal
        $this->fetchFresh();

        return back()->with('success', "⚡ Resepsionis Pinter: Berhasil mengurutkan seluruh peserta berdasarkan NIM (Ascending) & mengalokasikan bangku untuk {$count} peserta secara instan!");
    }

    /**
     * Normalisasi format kursi ke format standar tampilan.
     * Input:  SI-001, SI001, S1, S-1, TI-001, T1, MIK-001, M1
     * Output: SI-001, TI-001, MIK-001 (format tampilan konsisten)
     */
    private function normalizeKursi(string $raw): string
    {
        $val = strtoupper(trim($raw));
        if ($val === '' || $val === '-') return '-';

        // Sudah dalam format yang benar (SI-001, TI-001, MIK-001) → return as-is
        if (preg_match('/^(MIK|SI|TI)-\d+$/i', $val)) {
            // Pastikan nomor 3 digit
            if (preg_match('/^(MIK|SI|TI)-(\d+)$/i', $val, $m)) {
                return strtoupper($m[1]) . '-' . str_pad((int)$m[2], 3, '0', STR_PAD_LEFT);
            }
            return $val;
        }

        // Format S1, S-1, SI1 → SI-001
        if (preg_match('/^(MIK|SI|S|TI|T|M)-?(\d+)$/i', $val, $m)) {
            $prefix = strtoupper($m[1]);
            $num    = (int) $m[2];
            $map    = ['M' => 'MIK', 'S' => 'SI', 'T' => 'TI', 'MIK' => 'MIK', 'SI' => 'SI', 'TI' => 'TI'];
            $std    = $map[$prefix] ?? $prefix;
            return $std . '-' . str_pad($num, 3, '0', STR_PAD_LEFT);
        }

        return $val;
    }

    /**
     * Import nomor kursi dari file CSV.
     * Format CSV: kolom 1 = Nomor Kursi, kolom 2 = NIM
     * (atau sebaliknya — sistem deteksi otomatis)
     */
    public function importKursi(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ], [
            'csv_file.required' => 'File CSV wajib dipilih.',
            'csv_file.mimes'    => 'Format file harus CSV (.csv atau .txt).',
            'csv_file.max'      => 'Ukuran file maksimal 2MB.',
        ]);

        $file    = $request->file('csv_file');
        $handle  = fopen($file->getPathname(), 'r');
        $count   = 0;
        $skipped = 0;
        $errors  = [];
        $isFirst = true;

        // Deteksi BOM UTF-8
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            // Coba separator titik koma jika koma tidak menghasilkan 2 kolom
            if (count($row) < 2) {
                rewind($handle);
                $bom2 = fread($handle, 3);
                if ($bom2 !== "\xEF\xBB\xBF") rewind($handle);
                while (($row = fgetcsv($handle, 1000, ';')) !== false) {
                    // Re-process with semicolon
                    if ($isFirst) {
                        $isFirst = false;
                        $h0 = strtolower(trim($row[0] ?? ''));
                        $h1 = strtolower(trim($row[1] ?? ''));
                        if (str_contains($h0, 'kursi') || str_contains($h0, 'nomor') || str_contains($h1, 'nim')) {
                            continue; // skip header
                        }
                    }
                    $this->processImportRow($row, $count, $skipped, $errors);
                }
                break;
            }

            // Skip header baris pertama
            if ($isFirst) {
                $isFirst = false;
                $h0 = strtolower(trim($row[0] ?? ''));
                $h1 = strtolower(trim($row[1] ?? ''));
                if (str_contains($h0, 'kursi') || str_contains($h0, 'nomor') || str_contains($h1, 'nim')) {
                    continue;
                }
            }

            $this->processImportRow($row, $count, $skipped, $errors);
        }

        fclose($handle);
        $this->fetchFresh();

        $msg = "✅ Import selesai! {$count} kursi berhasil diimport.";
        if ($skipped > 0) $msg .= " {$skipped} baris dilewati (NIM kosong/tidak valid).";
        if (!empty($errors)) $msg .= " Peringatan: " . implode(', ', array_slice($errors, 0, 3));

        return back()->with('success', $msg);
    }

    /**
     * Proses satu baris CSV import kursi.
     */
    private function processImportRow(array $row, int &$count, int &$skipped, array &$errors): void
    {
        $col0 = trim($row[0] ?? '');
        $col1 = trim($row[1] ?? '');

        if ($col0 === '' && $col1 === '') {
            $skipped++;
            return;
        }

        // Deteksi kolom mana yang NIM dan mana kursi
        // NIM = angka panjang (8+ digit), kursi = alphanumeric pendek
        $isCol0Nim = preg_match('/^\d{8,}$/', $col0);
        $nim   = $isCol0Nim ? $col0 : $col1;
        $kursi = $isCol0Nim ? $col1 : $col0;

        $nim   = trim($nim);
        $kursi = trim($kursi);

        if ($nim === '' || $kursi === '' || $kursi === '-') {
            $skipped++;
            return;
        }

        // Normalisasi format kursi
        $kursiNorm = $this->normalizeKursi($kursi);

        PaymentVerification::updateOrCreate(
            ['nim' => $nim],
            ['nomor_kursi' => $kursiNorm]
        );

        $count++;
    }

    /**
     * Download template CSV untuk import kursi.
     */
    public function importTemplate()
    {
        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // BOM UTF-8
            fputcsv($handle, ['Nomor Kursi', 'NIM']);
            // Contoh data
            $contoh = [
                ['M1', '20240804001'],
                ['M2', '20240804002'],
                ['S1', '20220803001'],
                ['S2', '20220803002'],
                ['T1', '20220801001'],
                ['T2', '20220801002'],
            ];
            foreach ($contoh as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return response()->streamDownload($callback, 'template_import_kursi.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Reset seluruh alokasi nomor kursi peserta.
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
                $upper = strtoupper($kursi);
                $assigned[$kursi] = $p;
                $assigned[$upper] = $p;

                // Normalize MIK-01 -> M1, SI-01 -> S1, TI-01 -> T1
                $norm = preg_replace('/^MIK-?/i', 'M', preg_replace('/^SI-?/i', 'S', preg_replace('/^TI-?/i', 'T', $upper)));
                $normNoPad = preg_replace_callback('/([MST])0*([1-9][0-9]*)/', fn($m) => $m[1].$m[2], $norm);

                $assigned[$norm] = $p;
                $assigned[$normNoPad] = $p;
            } else {
                $unassigned[] = $p;
            }
        }

        $totalCapacity = 322;
        $totalAssigned = count(array_filter($pesertaList, fn($p) => !empty($p['Nomor Kursi']) && $p['Nomor Kursi'] !== '-'));
        $totalHadir    = count(array_filter($pesertaList, fn($p) => !empty($p['Waktu Kehadiran'])));

        $totalMik = count(array_filter($pesertaList, fn($p) => preg_match('/^(M|MIK)/i', trim($p['Nomor Kursi'] ?? ''))));
        $totalSi  = count(array_filter($pesertaList, fn($p) => preg_match('/^(S|SI)/i', trim($p['Nomor Kursi'] ?? ''))));
        $totalTi  = count(array_filter($pesertaList, fn($p) => preg_match('/^(T|TI)/i', trim($p['Nomor Kursi'] ?? ''))));

        return view('admin.plotting', compact(
            'pesertaList',
            'assigned',
            'unassigned',
            'totalCapacity',
            'totalAssigned',
            'totalHadir',
            'totalMik',
            'totalSi',
            'totalTi'
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
        $pesertaIndex = null;
        $searchLower = strtolower($searchValue);

        foreach ($allPeserta as $idx => $p) {
            $idUnik = strtolower(trim($p['ID Unik'] ?? ''));
            $nim    = strtolower(trim($p['NIM'] ?? ''));
            $email  = strtolower(trim($p['Email Address'] ?? $p['Email'] ?? ''));

            if ($searchLower !== '' && ($idUnik === $searchLower || $nim === $searchLower || $email === $searchLower)) {
                $peserta = $p;
                $pesertaIndex = $idx;
                break;
            }
        }

        if (! $peserta && str_starts_with(strtoupper($searchValue), 'YDS-')) {
            $parts = explode('-', strtoupper($searchValue));
            if (count($parts) >= 2 && is_numeric($parts[1])) {
                $rowIndex = ((int)$parts[1]) - 2;
                if (isset($allPeserta[$rowIndex])) {
                    $peserta = $allPeserta[$rowIndex];
                    $pesertaIndex = $rowIndex;
                }
            }
        }

        $user = Auth::user();
        $operatorName = $user?->name ?? 'Panitia';
        $operatorPin  = $user?->pin  ?? '123456';
        $displayCode  = $searchValue !== '' ? $searchValue : $scanned;

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

        // Ambil nomor kursi dari data peserta atau DB lokal
        $kursiPeserta = trim($peserta['Nomor Kursi'] ?? '');
        if ($kursiPeserta === '' || $kursiPeserta === '-') {
            $localSeat = PaymentVerification::where('nim', $nimPeserta)->value('nomor_kursi');
            if ($localSeat) {
                $kursiPeserta = $localSeat;
            }
        }
        if ($kursiPeserta === '') {
            $kursiPeserta = '-';
        }

        // Tentukan blok tempat duduk berdasarkan prefix kursi (M, S, T)
        $prefixKursi = strtoupper(substr($kursiPeserta, 0, 1));
        if ($prefixKursi === 'M') {
            $blokKursi = 'Blok Magister (Depan Kiri)';
        } elseif ($prefixKursi === 'S') {
            $blokKursi = 'Blok Sistem Informasi (Tengah Kiri)';
        } elseif ($prefixKursi === 'T') {
            $blokKursi = 'Blok Teknik Informatika (Tengah & Kanan)';
        } else {
            $blokKursi = 'Denah Utama Yudisium';
        }

        $pesertaData = array_merge($peserta, [
            'Nomor Kursi' => $kursiPeserta,
            'nomor_kursi' => $kursiPeserta,
            'Blok Kursi'  => $blokKursi,
        ]);

        // Sudah hadir?
        if (! empty($peserta['Waktu Kehadiran'])) {
            ScanLog::create([
                'user_id'      => $user?->id,
                'panitia_name' => $operatorName,
                'panitia_pin'  => $operatorPin,
                'peserta_nim'  => $nimPeserta,
                'peserta_nama' => $namaPeserta,
                'peserta_prodi'=> $prodiPeserta,
                'peserta_kursi'=> $kursiPeserta,
                'status'       => 'already',
                'message'      => "{$namaPeserta} (Bangku {$kursiPeserta}) sudah hadir pada {$peserta['Waktu Kehadiran']}.",
                'scanned_at'   => now(),
            ]);

            return response()->json([
                'status'  => 'already',
                'message' => "{$namaPeserta} sudah hadir pada {$peserta['Waktu Kehadiran']}.",
                'peserta' => $pesertaData,
            ]);
        }

        // Tandai hadir
        $waktu = now()->format('d/m/Y H:i:s');

        // Simpan ke database lokal agar tidak hilang saat refresh data
        PaymentVerification::updateOrCreate(
            ['nim' => $nimPeserta],
            ['waktu_kehadiran' => $waktu]
        );

        // Fast update cache in-memory so subsequent scans don't miss cache
        if ($pesertaIndex !== null && isset($allPeserta[$pesertaIndex])) {
            $allPeserta[$pesertaIndex]['Waktu Kehadiran'] = $waktu;
            Cache::put('sheetdb_peserta', $allPeserta, 300);
        }

        ScanLog::create([
            'user_id'      => $user?->id,
            'panitia_name' => $operatorName,
            'panitia_pin'  => $operatorPin,
            'peserta_nim'  => $nimPeserta,
            'peserta_nama' => $namaPeserta,
            'peserta_prodi'=> $prodiPeserta,
            'peserta_kursi'=> $kursiPeserta,
            'status'       => 'success',
            'message'      => "Kehadiran {$namaPeserta} (Bangku {$kursiPeserta}) berhasil dicatat.",
            'scanned_at'   => now(),
        ]);

        // Fast non-blocking update ke Apps Script
        try {
            Http::timeout(5)
                ->withoutVerifying()
                ->post($this->sheetdbUrl, [
                    'nim'     => $nimPeserta,
                    'updates' => ['Waktu Kehadiran' => $waktu],
                ]);
        } catch (\Throwable $e) {}

        return response()->json([
            'status'  => 'success',
            'message' => "Kehadiran {$namaPeserta} berhasil dicatat. Silakan menuju Bangku {$kursiPeserta}.",
            'peserta' => array_merge($pesertaData, ['Waktu Kehadiran' => $waktu]),
        ]);
    }

    /**
     * Bersihkan seluruh log scan absensi secara permanen dari database.
     */
    public function clearLogs()
    {
        ScanLog::query()->delete();
        return response()->json([
            'status'  => 'success',
            'message' => 'Seluruh riwayat log scan absensi berhasil dibersihkan secara permanen.',
        ]);
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
                'Tanggal Transfer', 'Status Pembayaran', 'Divalidasi Oleh', 'Waktu Validasi',
                'ID Unik', 'Status Email', 'Waktu Kehadiran',
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
                    $p['Validated By']             ?? '-',
                    $p['Validated At']             ?? '-',
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

        // Data Panitia & Status Login Terakhir
        $panitiaList = \App\Models\User::whereIn('role', ['panitia', 'admin'])
            ->orderByRaw('last_login_at IS NULL, last_login_at DESC')
            ->get();

        $lastLoginUser = $panitiaList->first();

        // Data Log Validasi Pembayaran
        $validationLogs = PaymentVerification::whereNotNull('validated_by')
            ->orderBy('validated_at', 'desc')
            ->take(15)
            ->get();

        return view('admin.logs', compact('logs', 'panitiaList', 'lastLoginUser', 'validationLogs'));
    }
}