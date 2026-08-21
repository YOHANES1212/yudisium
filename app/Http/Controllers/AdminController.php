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

            // Fetch local payment verifications overrides
            $localPayments = PaymentVerification::pluck('status_pembayaran', 'nim')->toArray();

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

                if ($nim && isset($localPayments[$nim])) {
                    $clean['Status Pembayaran'] = $localPayments[$nim];
                } elseif (empty($clean['Status Pembayaran']) || $clean['Status Pembayaran'] === '-') {
                    $clean['Status Pembayaran'] = 'Pending';
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

        // 5 pendaftar terbaru
        $recentPeserta = array_slice(array_reverse($data), 0, 5);

        return view('admin.dashboard', compact(
            'totalPeserta',
            'totalHadir',
            'totalBelumHadir',
            'totalValid',
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
     * Update status pembayaran peserta di database lokal & SheetDB (validkan / Tidak Valid / Pending).
     */
    public function updatePembayaran(Request $request)
    {
        $request->validate([
            'nim'    => 'required|string',
            'status' => 'required|string',
        ]);

        $nim       = trim($request->nim);
        $rawStatus = trim($request->status);

        // Jika disetujui, kirim 'valid' (huruf kecil) ke Google Sheet
        if (in_array(strtolower($rawStatus), ['valid', 'validkan'])) {
            $status = 'valid';
        } else {
            $status = $rawStatus;
        }

        // 1. Simpan ke database lokal agar website selalu akurat
        PaymentVerification::updateOrCreate(
            ['nim' => $nim],
            ['status_pembayaran' => $status]
        );

        // 2. Coba update ke Google Sheets (SheetDB) jika kolomnya tersedia di Google Sheet
        try {
            Http::timeout(10)
                ->withoutVerifying()
                ->patch("{$this->sheetdbUrl}/NIM/{$nim}", [
                    'data' => [
                        'Status Pembayaran'  => $status,
                        'Status Pembayaran ' => $status,
                    ],
                ]);

            // Panggil WebApp Apps Script jika terkonfigurasi di env
            $webAppUrl = env('APPS_SCRIPT_WEBAPP_URL');
            if ($webAppUrl) {
                Http::timeout(5)->withoutVerifying()->post($webAppUrl, ['nim' => $nim, 'status' => $status]);
            }
        } catch (\Throwable $e) {
            // Biarkan lewat jika kolom belum dibuat di Google Sheet
        }

        $this->fetchFresh();

        $msg = in_array(strtolower($status), ['valid', 'validkan'])
            ? "Pembayaran peserta NIM {$nim} berhasil disetujui ('valid')."
            : "Status pembayaran peserta NIM {$nim} diubah menjadi '{$status}'.";

        return back()->with('success', $msg);
    }

    /**
     * Halaman scan absensi.
     */
    public function absensi()
    {
        return view('admin.absensi');
    }

    /**
     * Proses scan QR — support ID Unik langsung, URL dengan ?id=, NIM, atau Email.
     */
    /**
     * Proses scan QR — support ID Unik langsung, URL dengan ?id=, NIM, atau Email.
     */
    public function scanQr(Request $request)
    {
        $request->validate(['nim' => 'required|string']);
        $scanned = trim($request->nim);

        // Ekstrak parameter ?id= dari URL (misal hasil QR Code dari Apps Script)
        // Contoh: https://script.google.com/macros/s/xxx/exec?id=YDS-3-3083
        $searchValue = $scanned;
        if (filter_var($scanned, FILTER_VALIDATE_URL) || str_contains($scanned, 'script.google.com')) {
            $parsed = parse_url($scanned);
            if (isset($parsed['query'])) {
                parse_str($parsed['query'], $params);
                if (isset($params['id'])) {
                    $searchValue = trim($params['id']); // ambil nilai ?id= (misal YDS-3-3083)
                }
            }
        }

        // 1. Ambil seluruh data peserta dari Google Sheet (paling cepat & akurat)
        $allPeserta = $this->fetchAll();
        $peserta    = null;

        // Search 1: Match persis ID Unik, NIM, atau Email
        foreach ($allPeserta as $p) {
            $idUnik = trim($p['ID Unik'] ?? '');
            $nim    = trim($p['NIM'] ?? '');
            $email  = trim($p['Email Address'] ?? $p['Email'] ?? '');

            if ($searchValue !== '' && ($idUnik === $searchValue || $nim === $searchValue || strtolower($email) === strtolower($searchValue))) {
                $peserta = $p;
                break;
            }
        }

        // Search 2: Jika berformat YDS-ROW-XXXX (misal YDS-3-4916), cari berdasarkan baris ke-3 di Google Sheet
        if (! $peserta && str_starts_with($searchValue, 'YDS-')) {
            $parts = explode('-', $searchValue);
            if (count($parts) >= 2 && is_numeric($parts[1])) {
                $rowIndex = ((int)$parts[1]) - 2; // Row 3 di Sheet = Index 1 di data array (setelah potong header)
                if (isset($allPeserta[$rowIndex])) {
                    $peserta = $allPeserta[$rowIndex];
                }
            }
        }

        // Search 3: Fallback ke SheetDB search API jika tidak ditemukan di memori
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

        // Cek ulang langsung dari SheetDB (bypass cache) — hindari race condition
        $fresh = $this->searchSheet('NIM', $peserta['NIM']);
        if ($fresh) $peserta = $fresh;

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
            $pay = $request->payment;
            $data = array_filter($data, fn($p) => ($p['Status Pembayaran'] ?? '') === $pay);
        }

        $data = array_values($data);

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="peserta_yudisium_' . now()->format('Ymd_His') . '.csv"',
        ];

        $callback = function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // BOM UTF-8

            fputcsv($handle, [
                'No', 'Timestamp', 'NIM', 'Nama Lengkap', 'Email', 'Program Studi',
                'No. HP (WA)', 'Bank Asal', 'Nama Pemilik Rekening', 'Nomor Rekening',
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

        return response()->stream($callback, 200, $headers);
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
