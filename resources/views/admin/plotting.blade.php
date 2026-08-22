@extends('layouts.admin')

@section('title', 'Plotting Kursi')
@section('page-title', 'Denah & Auto-Floating Bangku Yudisium (320 Kursi)')
@section('breadcrumb', 'Plotting Kursi')

@section('content')

{{-- ── Alerts ──────────────────────────────────────────────── --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert"
         style="border-radius:10px; font-size:14px; background:#D1FAE5; color:#065F46; border:1px solid #A7F3D0;">
        <i class="bi bi-lightning-charge-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert"
         style="border-radius:10px; font-size:14px;">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ── Banner Auto-Floating ────────────────────────────────── --}}
<div style="background:linear-gradient(135deg, #1E1B4B, #4F46E5);color:#fff;border-radius:16px;padding:20px 24px;margin-bottom:24px;box-shadow:0 8px 20px rgba(79, 70, 229, 0.2);">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div style="max-width:680px;">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge" style="background:rgba(255,255,255,0.2);color:#fff;font-size:11px;font-weight:600;padding:4px 10px;border-radius:20px;letter-spacing:0.5px;">
                    👑 VIP MAGISTER (MIK-01..20) | SI (SI-01..150) | TI (TI-01..150)
                </span>
                <span style="font-size:12px;opacity:0.8;">• Total Kapasitas: 320 Bangku (20 MIK | 150 SI | 150 TI)</span>
            </div>
            <h5 class="fw-bold mb-1" style="font-size:18px;">Denah Tempat Duduk Yudisium</h5>
            <p class="mb-0" style="font-size:13px;opacity:0.9;line-height:1.5;">
                <strong style="color:#FDE047;">👑 Magister (Baris VIP Terdepan)</strong>: Kode <strong>MIK-01 s/d MIK-20</strong>.<br>
                <strong style="color:#A7F3D0;">Sistem Informasi (Sayap Kiri)</strong>: Kode <strong>SI-01 s/d SI-150</strong>.<br>
                <strong style="color:#BAE6FD;">Teknik Informatika (Sayap Kanan)</strong>: Kode <strong>TI-01 s/d TI-150</strong>.<br>
                <small class="opacity-75">💡 Terdapat <strong>Jalur Tengah / Gang Utama</strong> di antara Sayap SI dan TI. Klik bangku terisi untuk edit/hapus.</small>
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-light" onclick="openAutoPlotModal()" style="font-size:13px;font-weight:700;color:var(--color-primary);border-radius:10px;padding:10px 18px;">
                <i class="bi bi-lightning-fill text-warning me-1"></i> Plotting Otomatis
            </button>
            <button type="button" class="btn btn-outline-light" onclick="confirmResetPlotting()" style="font-size:13px;border-radius:10px;padding:10px 14px;opacity:0.85;">
                <i class="bi bi-arrow-counterclockwise"></i> Reset All
            </button>
        </div>
    </div>
</div>

{{-- ── Stat Cards Plotting ─────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="bi bi-layout-three-columns"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $totalAssigned }}</div>
                <div class="stat-label">Kursi Terisi / Terplot</div>
                <div class="stat-change up"><i class="bi bi-check2"></i> Dari {{ count($pesertaList) }} peserta</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon success"><i class="bi bi-person-check-fill"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $totalHadir }}</div>
                <div class="stat-label">Hadir di Bangku</div>
                <div class="stat-change up"><i class="bi bi-qr-code"></i> Kehadiran Terverifikasi</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon warning"><i class="bi bi-exclamation-circle-fill"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ count($unassigned) }}</div>
                <div class="stat-label">Belum Punya Kursi</div>
                <div class="stat-change down"><i class="bi bi-clock"></i> Auto saat Valid</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon info"><i class="bi bi-grid-3x3-gap-fill"></i></div>
            <div class="stat-info">
                <div class="stat-value">320</div>
                <div class="stat-label">Kapasitas Gedung</div>
                <div class="stat-change up"><i class="bi bi-building"></i> {{ $totalMik ?? 0 }} MIK · {{ $totalSi ?? 0 }} SI · {{ $totalTi ?? 0 }} TI</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- ── Denah Presisi 3 Sektor (VIP MIK, Sayap SI, Sayap TI) ───── --}}
    <div class="col-12 col-lg-8">
        <div class="data-card">
            <div class="data-card-header">
                <div class="data-card-title">
                    <i class="bi bi-grid-3x3-gap me-2 text-primary"></i>
                    Denah Tempat Duduk Yudisium
                </div>
                <div style="display:flex;gap:12px;font-size:12px;align-items:center;flex-wrap:wrap;">
                    <span style="display:inline-flex;align-items:center;gap:4px;">
                        <span style="width:12px;height:12px;border-radius:4px;background:#F59E0B;display:inline-block;"></span> VIP Magister
                    </span>
                    <span style="display:inline-flex;align-items:center;gap:4px;">
                        <span style="width:12px;height:12px;border-radius:4px;background:#10B981;display:inline-block;"></span> Hadir (Occupied)
                    </span>
                    <span style="display:inline-flex;align-items:center;gap:4px;">
                        <span style="width:12px;height:12px;border-radius:4px;background:#4F46E5;display:inline-block;"></span> Terplot (Belum Hadir)
                    </span>
                    <span style="display:inline-flex;align-items:center;gap:4px;">
                        <span style="width:12px;height:12px;border-radius:4px;background:#E5E7EB;display:inline-block;"></span> Kosong
                    </span>
                </div>
            </div>

            <div style="padding:20px;overflow-x:auto;">

                {{-- Banner Panggung Utama --}}
                <div style="background:linear-gradient(135deg, #0F172A, #1E293B);color:#fff;text-align:center;padding:14px;border-radius:14px;font-weight:800;font-size:14px;letter-spacing:1px;margin-bottom:20px;box-shadow:0 4px 10px rgba(0,0,0,0.15);">
                    🎭 PANGGUNG UTAMA YUDISIUM 🎭
                </div>

                {{-- 👑 SEKTOR VIP MAGISTER (BARIS TERDEPAN PANGGUNG) --}}
                <div class="mb-4 style-vip-container" style="background:#FFFBEB;border:2px dashed #F59E0B;border-radius:14px;padding:16px;min-width:840px;">
                    <div style="text-align:center;font-weight:700;font-size:13px;color:#B45309;margin-bottom:12px;letter-spacing:0.5px;">
                        👑 SEKTOR VIP MAGISTER ILMU KOMPUTER (BARIS TERDEPAN: MIK-01 s/d MIK-20)
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(20, 1fr);gap:4px;">
                        @foreach($mikCols as $mc)
                        @php
                            $displayCode = "MIK-" . str_pad($mc, 2, '0', STR_PAD_LEFT);
                            $p = $assigned[$displayCode] ?? ($assigned["MIK-{$mc}"] ?? null);
                            $isOccupied = $p && !empty($p['Waktu Kehadiran']);
                            $isAssigned = $p !== null;

                            $bg = $isOccupied ? '#10B981' : ($isAssigned ? '#F59E0B' : '#FEF3C7');
                            $color = $isAssigned ? '#fff' : '#92400E';
                            $border = $isAssigned ? 'none' : '1px solid #FDE68A';
                        @endphp
                        <button type="button"
                                onclick="inspectSeat('{{ $displayCode }}', '{{ $p ? addslashes($p['Nama Lengkap'] ?? $p['nama'] ?? '') : '' }}', '{{ $p ? ($p['NIM'] ?? '') : '' }}', 'Magister Ilmu Komputer', '{{ $p ? (!empty($p['Waktu Kehadiran']) ? 'Hadir' : 'Belum Hadir') : 'Kosong' }}')"
                                style="height:38px;border-radius:6px;background:{{ $bg }};color:{{ $color }};border:{{ $border }};font-weight:700;font-size:9px;display:flex;flex-direction:column;align-items:center;justify-content:center;cursor:pointer;padding:1px;"
                                title="{{ $displayCode }}: {{ $p ? ($p['Nama Lengkap'] ?? $p['nama'] ?? '') : 'Kosong (Klik untuk atur)' }}">
                            <span>{{ $displayCode }}</span>
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- Header 2 Sayap (SI & TI) dengan Gang Tengah --}}
                <div style="display:flex;align-items:center;gap:20px;margin-bottom:12px;min-width:840px;">
                    <div style="flex:1;background:#EEF2FF;color:#4F46E5;padding:10px;border-radius:10px;font-weight:700;font-size:13px;border:1.5px solid #C7D2FE;text-align:center;">
                        SISTEM INFORMASI (SI) — 150 Kursi (SI-01 s/d SI-150)
                    </div>
                    <div style="width:40px;text-align:center;font-size:11px;font-weight:800;color:#9CA3AF;letter-spacing:1px;">
                        GANG
                    </div>
                    <div style="flex:1;background:#ECFDF5;color:#059669;padding:10px;border-radius:10px;font-weight:700;font-size:13px;border:1.5px solid #A7F3D0;text-align:center;">
                        TEKNIK INFORMATIKA (TI) — 150 Kursi (TI-01 s/d TI-150)
                    </div>
                </div>

                {{-- Grid Dual Wings SI & TI dengan JALUR TENGAH (GANG UTAMA) --}}
                <div style="min-width:840px;">
                    @foreach($rows as $rowIndex => $r)
                    <div style="display:flex;align-items:center;gap:20px;margin-bottom:6px;">

                        {{-- SAYAP KIRI (SI - 15 Kolom per Baris) --}}
                        <div style="flex:1;">
                            <div style="display:grid;grid-template-columns:repeat(15, 1fr);gap:4px;">
                                @foreach($cols as $c)
                                @php
                                    $siNum = ($rowIndex * 15) + $c;
                                    $siFormatted = str_pad($siNum, 2, '0', STR_PAD_LEFT);
                                    $displayCode = "SI-{$siFormatted}";
                                    $codeGrid = "SI-{$r}" . str_pad($c, 2, '0', STR_PAD_LEFT);
                                    $codeShort = "{$r}-" . str_pad($c, 2, '0', STR_PAD_LEFT);

                                    $p = $assigned[$displayCode] ?? ($assigned["SI-{$siNum}"] ?? ($assigned[$codeGrid] ?? ($assigned[$codeShort] ?? null)));

                                    $isOccupied = $p && !empty($p['Waktu Kehadiran']);
                                    $isAssigned = $p !== null;

                                    $bg = $isOccupied ? '#10B981' : ($isAssigned ? '#4F46E5' : '#F3F4F6');
                                    $color = $isAssigned ? '#fff' : '#4B5563';
                                    $border = $isAssigned ? 'none' : '1px solid #E5E7EB';
                                @endphp

                                <button type="button"
                                        onclick="inspectSeat('{{ $displayCode }}', '{{ $p ? addslashes($p['Nama Lengkap'] ?? $p['nama'] ?? '') : '' }}', '{{ $p ? ($p['NIM'] ?? '') : '' }}', 'Sistem Informasi', '{{ $p ? (!empty($p['Waktu Kehadiran']) ? 'Hadir' : 'Belum Hadir') : 'Kosong' }}')"
                                        style="height:36px;border-radius:6px;background:{{ $bg }};color:{{ $color }};border:{{ $border }};font-weight:700;font-size:9px;display:flex;flex-direction:column;align-items:center;justify-content:center;cursor:pointer;padding:1px;"
                                        title="{{ $displayCode }}: {{ $p ? ($p['Nama Lengkap'] ?? $p['nama'] ?? '') : 'Kosong (Klik untuk atur)' }}">
                                    <span>{{ $displayCode }}</span>
                                </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- 🚶 JALUR TENGAH / GANG UTAMA 🚶 --}}
                        <div style="width:40px;background:#F1F5F9;border:1px dashed #CBD5E1;border-radius:6px;height:36px;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#64748B;">
                            🚶
                        </div>

                        {{-- SAYAP KANAN (TI - 15 Kolom per Baris) --}}
                        <div style="flex:1;">
                            <div style="display:grid;grid-template-columns:repeat(15, 1fr);gap:4px;">
                                @foreach($cols as $c)
                                @php
                                    $tiNum = ($rowIndex * 15) + $c;
                                    $tiFormatted = str_pad($tiNum, 2, '0', STR_PAD_LEFT);
                                    $displayCode = "TI-{$tiFormatted}";
                                    $codeGrid = "TI-{$r}" . str_pad($c, 2, '0', STR_PAD_LEFT);
                                    $codeShort = "{$r}-" . str_pad($c, 2, '0', STR_PAD_LEFT);

                                    $p = $assigned[$displayCode] ?? ($assigned["TI-{$tiNum}"] ?? ($assigned[$codeGrid] ?? ($assigned[$codeShort] ?? null)));

                                    $isOccupied = $p && !empty($p['Waktu Kehadiran']);
                                    $isAssigned = $p !== null;

                                    $bg = $isOccupied ? '#10B981' : ($isAssigned ? '#4F46E5' : '#F3F4F6');
                                    $color = $isAssigned ? '#fff' : '#4B5563';
                                    $border = $isAssigned ? 'none' : '1px solid #E5E7EB';
                                @endphp

                                <button type="button"
                                        onclick="inspectSeat('{{ $displayCode }}', '{{ $p ? addslashes($p['Nama Lengkap'] ?? $p['nama'] ?? '') : '' }}', '{{ $p ? ($p['NIM'] ?? '') : '' }}', 'Teknik Informatika', '{{ $p ? (!empty($p['Waktu Kehadiran']) ? 'Hadir' : 'Belum Hadir') : 'Kosong' }}')"
                                        style="height:36px;border-radius:6px;background:{{ $bg }};color:{{ $color }};border:{{ $border }};font-weight:700;font-size:9px;display:flex;flex-direction:column;align-items:center;justify-content:center;cursor:pointer;padding:1px;"
                                        title="{{ $displayCode }}: {{ $p ? ($p['Nama Lengkap'] ?? $p['nama'] ?? '') : 'Kosong (Klik untuk atur)' }}">
                                    <span>{{ $displayCode }}</span>
                                </button>
                                @endforeach
                            </div>
                        </div>

                    </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>

    {{-- ── Quick Manual Assign Form / Unassigned List ───────────────── --}}
    <div class="col-12 col-lg-4">
        <div class="data-card">
            <div class="data-card-header">
                <div class="data-card-title" style="font-size:14px;">
                    <i class="bi bi-sliders me-1 text-primary"></i> Atur Kursi Manual / Override
                </div>
            </div>
            <div style="padding:18px;">
                <form action="{{ route('admin.peserta.kursi') }}" method="POST" class="mb-4">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" style="font-size:12px;font-weight:600;">Pilih Peserta</label>
                        <select name="nim" id="quickSelectNim" class="form-select" style="font-size:13px;" required>
                            <option value="">-- Pilih Peserta --</option>
                            @foreach($pesertaList as $p)
                                <option value="{{ $p['NIM'] ?? '' }}">
                                    {{ $p['Nama Lengkap'] ?? $p['nama'] ?? '' }} ({{ $p['Program Studi'] ?? 'Umum' }}) {{ !empty($p['Nomor Kursi']) && $p['Nomor Kursi'] !== '-' ? '['.$p['Nomor Kursi'].']' : '[Belum ada]' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-size:12px;font-weight:600;">Nomor Bangku (misal: MIK-01, SI-01, TI-01)</label>
                        <input type="text" name="nomor_kursi" id="quickNomorKursi" class="form-control" placeholder="Contoh: MIK-01, SI-01 atau TI-05" style="font-size:13px;" required uppercase>
                        <small class="text-muted" style="font-size:11px;">Format: `MIK-01..20` (VIP Magister), `SI-01..150` atau `TI-01..150`.</small>
                    </div>

                    <button type="submit" class="btn-primary-sm w-100 justify-content-center py-2">
                        <i class="bi bi-check-circle-fill me-1"></i> Simpan Bangku Manual
                    </button>
                </form>

                <div style="font-size:13px;font-weight:600;color:var(--color-gray-800);margin-bottom:10px;display:flex;justify-content:space-between;align-items:center;">
                    <span>Belum Punya Bangku</span>
                    <span class="badge bg-warning text-dark">{{ count($unassigned) }}</span>
                </div>

                <div style="max-height:300px;overflow-y:auto;border:1px solid var(--color-gray-200);border-radius:10px;">
                    @if(empty($unassigned))
                        <div class="text-center py-4 text-muted" style="font-size:12px;">
                            🎉 Semua peserta telah mendapatkan kursi!
                        </div>
                    @else
                        @foreach($unassigned as $u)
                            <div style="padding:10px 12px;border-bottom:1px solid var(--color-gray-100);display:flex;align-items:center;justify-content:space-between;gap:8px;">
                                <div style="min-width:0;">
                                    <div style="font-weight:600;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        {{ $u['Nama Lengkap'] ?? $u['nama'] ?? '-' }}
                                    </div>
                                    <div style="font-size:11px;color:var(--color-gray-500);">
                                        {{ $u['NIM'] ?? '-' }} · <span class="badge bg-light text-dark border">{{ $u['Program Studi'] ?? '-' }}</span>
                                    </div>
                                </div>
                                <button type="button" class="btn-outline-sm py-1 px-2" style="font-size:11px;"
                                        onclick="setQuickAssign('{{ $u['NIM'] ?? '' }}')">
                                    Pilih
                                </button>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Plotting Otomatis (Auto Plotting) ──────────────── --}}
<div class="modal fade" id="autoPlotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:18px;border:none;">
            <div class="modal-header" style="border-bottom:1px solid var(--color-gray-200);padding:16px 20px;">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:36px;height:36px;border-radius:50%;background:var(--color-primary-light);color:var(--color-primary);display:flex;align-items:center;justify-content:center;font-size:18px;">
                        <i class="bi bi-lightning-charge-fill text-warning"></i>
                    </div>
                    <div>
                        <h6 class="modal-title mb-0" style="font-weight:700;">Resepsionis Pinter Auto-Floating (320 Kursi)</h6>
                        <small class="text-muted">Plotting massal otomatis sesuai Bangku MIK, SI & TI</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('admin.plotting.auto') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding:20px;">
                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px;font-weight:600;">Format Kode Bangku</label>
                        <select name="format" class="form-select" style="font-size:13px;">
                            <option value="prodi_prefix" selected>🎯 Format Prodi: VIP MIK-01..20, SI-01..150 & TI-01..150</option>
                        </select>
                        <small class="text-muted" style="font-size:11px;">Magister otomatis mendapat `MIK-01` di depan, anak SI `SI-01`, anak TI `TI-01`.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px;font-weight:600;">Metode Alokasi</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="mode" id="modeUnassigned" value="unassigned" checked>
                            <label class="form-check-label" for="modeUnassigned" style="font-size:13px;">
                                <strong>Hanya Peserta Tanpa Kursi</strong> (Pertahankan bangku yang sudah terplot)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="mode" id="modeReset" value="reset_all">
                            <label class="form-check-label" for="modeReset" style="font-size:13px;">
                                <strong>Reset & Floating Ulang Seluruh Peserta</strong> (Re-alokasi total dari 01)
                            </label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer" style="border-top:1px solid var(--color-gray-200);padding:14px 20px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="font-size:13px;font-weight:600;">Batal</button>
                    <button type="submit" class="btn-primary-sm" style="padding:8px 18px;">
                        <i class="bi bi-play-circle-fill me-1"></i> Jalankan Auto-Floating Massal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Form Reset Plotting ──────────────────────────────────── --}}
<form id="resetPlottingForm" action="{{ route('admin.plotting.reset') }}" method="POST" style="display:none;">
    @csrf
</form>

{{-- ── Modal Inspect Kursi (dengan Fitur Hapus Alokasi) ───── --}}
<div class="modal fade" id="seatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="border-bottom:1px solid var(--color-gray-200);padding:14px 18px;">
                <h6 class="modal-title mb-0" id="seatModalTitle" style="font-weight:700;">Detail Bangku</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" style="padding:20px;">
                <div id="seatBadge" style="width:78px;height:54px;border-radius:12px;background:var(--color-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;margin:0 auto 12px;padding:0 8px;">
                    SI-01
                </div>
                <h6 id="seatNama" style="font-weight:700;font-size:15px;margin-bottom:2px;">-</h6>
                <div id="seatNim" style="font-size:12px;color:var(--color-gray-500);margin-bottom:8px;">-</div>
                <div id="seatStatus" style="font-size:12px;margin-bottom:16px;">-</div>

                {{-- Form Hapus Alokasi Kursi --}}
                <div id="formHapusKursi" class="d-none">
                    <form id="deleteSeatFormPlotting" action="{{ route('admin.peserta.kursi.hapus') }}" method="POST">
                        @csrf
                        <input type="hidden" name="nim" id="deleteSeatNim">
                        <button type="button" class="btn btn-outline-danger w-100" style="border-radius:10px;font-size:12px;font-weight:600;padding:8px;" onclick="openCustomDeleteSeatModalPlotting()">
                            <i class="bi bi-trash3-fill me-1"></i> Hapus Alokasi Bangku Ini
                        </button>
                    </form>
                </div>
            </div>
            <div class="modal-footer justify-content-center" style="border-top:none;padding:0 18px 18px;">
                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal" style="border-radius:8px;font-size:13px;">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Konfirmasi Hapus Bangku ───────────────────────── --}}
<div class="modal fade" id="deleteSeatConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered modal-sm" style="max-width:360px;">
        <div class="modal-content" style="border-radius:20px;border:none;box-shadow:0 20px 40px rgba(0,0,0,0.25);overflow:hidden;">
            <div class="modal-body text-center" style="padding:28px 20px 16px;">
                <div style="width:60px;height:60px;border-radius:50%;background:#FEE2E2;color:#EF4444;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:26px;">
                    <i class="bi bi-trash3-fill"></i>
                </div>
                <h6 style="font-weight:700;font-size:16px;color:#111827;margin-bottom:6px;">Kosongkan Bangku Ini?</h6>
                <div style="font-size:13px;color:#6B7280;margin-bottom:0;" id="deleteSeatModalMsg">
                    Apakah Anda yakin ingin mengosongkan alokasi bangku peserta ini?
                </div>
            </div>
            <div class="modal-footer" style="border-top:none;padding:12px 20px 24px;display:flex;gap:10px;justify-content:center;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="flex:1;border-radius:10px;font-weight:600;font-size:13px;padding:10px;color:#4B5563;background:#F3F4F6;border:none;">
                    Batal
                </button>
                <button type="button" onclick="document.getElementById('deleteSeatFormPlotting').submit()" class="btn btn-danger" style="flex:1;border-radius:10px;font-weight:600;font-size:13px;padding:10px;">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Konfirmasi Reset All Plotting ───────────────────── --}}
<div class="modal fade" id="resetPlotConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered modal-sm" style="max-width:360px;">
        <div class="modal-content" style="border-radius:20px;border:none;box-shadow:0 20px 40px rgba(0,0,0,0.25);overflow:hidden;">
            <div class="modal-body text-center" style="padding:28px 20px 16px;">
                <div style="width:60px;height:60px;border-radius:50%;background:#FEF3C7;color:#D97706;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:26px;">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </div>
                <h6 style="font-weight:700;font-size:16px;color:#111827;margin-bottom:6px;">Reset Seluruh Bangku?</h6>
                <div style="font-size:13px;color:#6B7280;margin-bottom:0;">
                    Tindakan ini akan mengosongkan seluruh alokasi bangku peserta.
                </div>
            </div>
            <div class="modal-footer" style="border-top:none;padding:12px 20px 24px;display:flex;gap:10px;justify-content:center;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="flex:1;border-radius:10px;font-weight:600;font-size:13px;padding:10px;color:#4B5563;background:#F3F4F6;border:none;">
                    Batal
                </button>
                <button type="button" onclick="document.getElementById('resetPlottingForm').submit()" class="btn btn-warning text-dark fw-bold" style="flex:1;border-radius:10px;font-size:13px;padding:10px;">
                    Ya, Reset All
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentSelectedNim = '';
let currentSelectedNama = '';

function openAutoPlotModal() {
    new bootstrap.Modal(document.getElementById('autoPlotModal')).show();
}

function confirmResetPlotting() {
    new bootstrap.Modal(document.getElementById('resetPlotConfirmModal')).show();
}

function openCustomDeleteSeatModalPlotting() {
    if (currentSelectedNim) {
        document.getElementById('deleteSeatModalMsg').innerHTML = `Apakah Anda yakin ingin mengosongkan bangku untuk <strong>${currentSelectedNama}</strong> (NIM: ${currentSelectedNim})?`;
        new bootstrap.Modal(document.getElementById('deleteSeatConfirmModal')).show();
    }
}

function inspectSeat(code, nama, nim, prodi, status) {
    currentSelectedNim  = nim;
    currentSelectedNama = nama;

    document.getElementById('seatModalTitle').textContent = 'Detail Bangku ' + code;
    document.getElementById('seatBadge').textContent = code;
    document.getElementById('seatNama').textContent  = nama || 'Kosong / belum terisi';
    document.getElementById('seatNim').textContent   = nim ? (nim + (prodi ? ' · ' + prodi : '')) : 'Bangku belum dialokasikan';

    const statusEl = document.getElementById('seatStatus');
    if (status === 'Hadir') {
        statusEl.innerHTML = '<span class="badge bg-success">🟢 Hadir di Gedung</span>';
    } else if (status === 'Belum Hadir') {
        statusEl.innerHTML = '<span class="badge bg-primary">🔵 Terplot (Belum Hadir)</span>';
    } else {
        statusEl.innerHTML = '<span class="badge bg-secondary">⚪ Bangku Kosong</span>';
    }

    const formHapus = document.getElementById('formHapusKursi');
    const deleteNimInput = document.getElementById('deleteSeatNim');
    if (nim) {
        deleteNimInput.value = nim;
        formHapus.classList.remove('d-none');
    } else {
        deleteNimInput.value = '';
        formHapus.classList.add('d-none');

        const nomorInput = document.getElementById('quickNomorKursi');
        if (nomorInput) {
            nomorInput.value = code;
        }
    }

    new bootstrap.Modal(document.getElementById('seatModal')).show();
}

function setQuickAssign(nim) {
    const select = document.getElementById('quickSelectNim');
    if (select) {
        select.value = nim;
        document.getElementById('quickNomorKursi').focus();
    }
}
</script>
@endpush

@endsection
