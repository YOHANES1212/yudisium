<?php

namespace App\Http\Controllers;

use App\Models\Peserta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PesertaController extends Controller
{
    /**
     * Menampilkan data peserta.
     * Mengambil data pendaftar dari Google Sheets (via SheetDB)
     * dan data absensi dari database lokal.
     */
    public function index()
    {
        // 1. Mengambil data pendaftar dari Google Sheets teman Anda
        $sheetdbUrl = config('services.sheetdb.url', env('SHEETDB_URL', 'https://sheetdb.io/api/v1/71445zve8u6f7'));
        if (str_starts_with($sheetdbUrl, '//')) {
            $sheetdbUrl = 'https:' . $sheetdbUrl;
        } elseif (!str_starts_with($sheetdbUrl, 'http://') && !str_starts_with($sheetdbUrl, 'https://')) {
            $sheetdbUrl = 'https://' . $sheetdbUrl;
        }
        $response = Http::withoutVerifying()->get($sheetdbUrl);
        $pesertaFromForm = $response->successful() ? $response->json() : [];

        // 2. Mengambil data absensi dari database lokal (jika ada)
        // Kita gunakan ini untuk mencocokkan siapa saja yang sudah scan/hadir
        $pesertaHadir = Peserta::pluck('nim')->toArray();

        // 3. Menggabungkan data (kita tandai status kehadiran berdasarkan database lokal)
        $pesertaList = collect($pesertaFromForm)->map(function ($item) use ($pesertaHadir) {
            $item['status_kehadiran'] = in_array($item['NIM'], $pesertaHadir) ? 'Hadir' : 'Terdaftar';
            return $item;
        });

        return view('admin.peserta', compact('pesertaList'));
    }

    /**
     * Memproses Absensi (Saat admin melakukan scan QR Code)
     * Tetap menyimpan ke database lokal agar sistem absensi Anda tetap berjalan.
     */
    public function scan(Request $request)
    {
        $request->validate([
            'nim' => 'required',
        ]);

        // Cek apakah sudah pernah scan
        $exists = Peserta::where('nim', $request->nim)->exists();

        if ($exists) {
            return response()->json(['status' => 'already', 'message' => 'Peserta sudah hadir!']);
        }

        // Simpan ke database lokal sebagai penanda sudah hadir
        Peserta::create([
            'nim' => $request->nim,
            'nama' => $request->nama ?? 'Peserta Yudisium', // Bisa ambil dari request atau dikosongkan
            'prodi' => $request->prodi ?? '-',
        ]);

        return response()->json(['status' => 'success', 'message' => 'Kehadiran dicatat!']);
    }
}