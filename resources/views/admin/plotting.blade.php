@extends('layouts.admin')

@section('title', 'Plotting Kursi')
@section('page-title', 'Denah & Auto-Floating Bangku Yudisium (322 Kursi)')
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
<div style="background:linear-gradient(135deg, #0F172A, #1E1B4B, #312E81);color:#fff;border-radius:16px;padding:20px 24px;margin-bottom:24px;box-shadow:0 8px 20px rgba(15, 23, 42, 0.4);">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div style="max-width:720px;">
            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                <span class="badge" style="background:#00C853;color:#fff;font-size:11px;font-weight:700;padding:4px 10px;border-radius:20px;">
                    🟢 MAGISTER (M1 - M12)
                </span>
                <span class="badge" style="background:#FF9100;color:#fff;font-size:11px;font-weight:700;padding:4px 10px;border-radius:20px;">
                    🟠 SISTEM INFORMASI (S1 - S71)
                </span>
                <span class="badge" style="background:#0026CA;color:#fff;font-size:11px;font-weight:700;padding:4px 10px;border-radius:20px;">
                    🔵 TEKNIK INFORMATIKA (T1 - T239)
                </span>
            </div>
            <h5 class="fw-bold mb-1" style="font-size:18px;">Denah Tempat Duduk Yudisium (Kapasitas: 322 Bangku)</h5>
            <p class="mb-0" style="font-size:13px;opacity:0.9;line-height:1.5;">
                ⚡ <strong>Auto-Floating Pinter</strong>: Mengurutkan peserta secara otomatis berdasarkan <strong>NIM (Ascending)</strong> per Program Studi.<br>
                <small class="opacity-75">Contoh: NIM TI (<code>20220801001</code>) &rarr; <strong>T1</strong> | NIM SI (<code>20220803001</code>) &rarr; <strong>S1</strong> | NIM Magister (<code>20240804001</code>) &rarr; <strong>M1</strong>.</small>
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-warning text-dark fw-bold" onclick="openAutoPlotModal()" style="font-size:13px;border-radius:10px;padding:10px 18px;box-shadow:0 4px 12px rgba(245,158,11,0.3);">
                <i class="bi bi-lightning-fill me-1"></i> Plotting Urut NIM
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
                <div class="stat-label">Bangku Terisi / Terplot</div>
                <div class="stat-change up"><i class="bi bi-check2"></i> Dari {{ count($pesertaList) }} Peserta</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon success"><i class="bi bi-person-check-fill"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $totalHadir }}</div>
                <div class="stat-label">Hadir di Bangku</div>
                <div class="stat-change up"><i class="bi bi-qr-code"></i> Presensi Valid</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon warning"><i class="bi bi-exclamation-circle-fill"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ count($unassigned) }}</div>
                <div class="stat-label">Belum Ada Bangku</div>
                <div class="stat-change down"><i class="bi bi-clock"></i> Belum Di-plot</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon info"><i class="bi bi-grid-3x3-gap-fill"></i></div>
            <div class="stat-info">
                <div class="stat-value">322</div>
                <div class="stat-label">Total Kapasitas Gedung</div>
                <div class="stat-change up"><i class="bi bi-building"></i> 12 M · 71 S · 239 T</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- ── Canvas Denah Presisi Layout Visual (Dark Theme) ───────── --}}
    <div class="col-12 col-xl-8">
        <div class="data-card" style="background:#0B0F19;border:1px solid #1E293B;color:#fff;">
            <div class="data-card-header" style="border-bottom:1px solid #1E293B;">
                <div class="data-card-title text-white">
                    <i class="bi bi-grid-3x3-gap me-2 text-warning"></i>
                    Denah Tempat Duduk Yudisium
                </div>
                <div style="display:flex;gap:14px;font-size:12px;align-items:center;flex-wrap:wrap;">
                    <span style="display:inline-flex;align-items:center;gap:5px;">
                        <span style="width:14px;height:14px;border-radius:3px;background:#00C853;display:inline-block;"></span> Magister (M)
                    </span>
                    <span style="display:inline-flex;align-items:center;gap:5px;">
                        <span style="width:14px;height:14px;border-radius:3px;background:#FF9100;display:inline-block;"></span> Sistem Informasi (S)
                    </span>
                    <span style="display:inline-flex;align-items:center;gap:5px;">
                        <span style="width:14px;height:14px;border-radius:3px;background:#0026CA;display:inline-block;"></span> Teknik Informatika (T)
                    </span>
                </div>
            </div>

            <div style="padding:24px;overflow-x:auto;background:#05070D;">

                {{-- 🎭 PANGGUNG UTAMA (Top Centered Pill Banner) --}}
                <div class="d-flex justify-content-center mb-4">
                    <div style="background:#0047BA;color:#fff;width:340px;text-align:center;padding:10px 0;border-radius:30px;font-weight:800;font-size:14px;letter-spacing:3px;box-shadow:0 4px 18px rgba(0,71,186,0.6);border:1px solid rgba(255,255,255,0.2);">
                        PANGGUNG
                    </div>
                </div>

                {{-- 🟡 VIP ROUND TABLES (6 Meja Bundar VIP Kuning + Kursi) --}}
                <div class="d-flex justify-content-center gap-5 mb-5 flex-wrap">
                    {{-- Cluster Meja Kiri (3 Meja) --}}
                    <div class="d-flex gap-3 align-items-center">
                        <div style="position:relative;width:54px;height:54px;display:flex;align-items:center;justify-content:center;">
                            <div style="width:40px;height:40px;border-radius:50%;background:#FFD600;box-shadow:0 0 10px rgba(255,214,0,0.5);"></div>
                            <span style="position:absolute;top:-4px;width:10px;height:10px;background:#94A3B8;border-radius:2px;"></span>
                            <span style="position:absolute;bottom:-4px;width:10px;height:10px;background:#94A3B8;border-radius:2px;"></span>
                            <span style="position:absolute;left:-4px;width:10px;height:10px;background:#94A3B8;border-radius:2px;"></span>
                            <span style="position:absolute;right:-4px;width:10px;height:10px;background:#94A3B8;border-radius:2px;"></span>
                        </div>
                        <div style="position:relative;width:54px;height:54px;display:flex;align-items:center;justify-content:center;">
                            <div style="width:40px;height:40px;border-radius:50%;background:#FFD600;box-shadow:0 0 10px rgba(255,214,0,0.5);"></div>
                            <span style="position:absolute;top:-4px;width:10px;height:10px;background:#94A3B8;border-radius:2px;"></span>
                            <span style="position:absolute;bottom:-4px;width:10px;height:10px;background:#94A3B8;border-radius:2px;"></span>
                            <span style="position:absolute;left:-4px;width:10px;height:10px;background:#94A3B8;border-radius:2px;"></span>
                            <span style="position:absolute;right:-4px;width:10px;height:10px;background:#94A3B8;border-radius:2px;"></span>
                        </div>
                        <div style="position:relative;width:54px;height:54px;display:flex;align-items:center;justify-content:center;">
                            <div style="width:40px;height:40px;border-radius:50%;background:#FFD600;box-shadow:0 0 10px rgba(255,214,0,0.5);"></div>
                            <span style="position:absolute;top:-4px;width:10px;height:10px;background:#94A3B8;border-radius:2px;"></span>
                            <span style="position:absolute;bottom:-4px;width:10px;height:10px;background:#94A3B8;border-radius:2px;"></span>
                            <span style="position:absolute;left:-4px;width:10px;height:10px;background:#94A3B8;border-radius:2px;"></span>
                            <span style="position:absolute;right:-4px;width:10px;height:10px;background:#94A3B8;border-radius:2px;"></span>
                        </div>
                    </div>

                    {{-- Cluster Meja Kanan (3 Meja) --}}
                    <div class="d-flex gap-3 align-items-center">
                        <div style="position:relative;width:54px;height:54px;display:flex;align-items:center;justify-content:center;">
                            <div style="width:40px;height:40px;border-radius:50%;background:#FFD600;box-shadow:0 0 10px rgba(255,214,0,0.5);"></div>
                            <span style="position:absolute;top:-4px;width:10px;height:10px;background:#94A3B8;border-radius:2px;"></span>
                            <span style="position:absolute;bottom:-4px;width:10px;height:10px;background:#94A3B8;border-radius:2px;"></span>
                            <span style="position:absolute;left:-4px;width:10px;height:10px;background:#94A3B8;border-radius:2px;"></span>
                            <span style="position:absolute;right:-4px;width:10px;height:10px;background:#94A3B8;border-radius:2px;"></span>
                        </div>
                        <div style="position:relative;width:54px;height:54px;display:flex;align-items:center;justify-content:center;">
                            <div style="width:40px;height:40px;border-radius:50%;background:#FFD600;box-shadow:0 0 10px rgba(255,214,0,0.5);"></div>
                            <span style="position:absolute;top:-4px;width:10px;height:10px;background:#94A3B8;border-radius:2px;"></span>
                            <span style="position:absolute;bottom:-4px;width:10px;height:10px;background:#94A3B8;border-radius:2px;"></span>
                            <span style="position:absolute;left:-4px;width:10px;height:10px;background:#94A3B8;border-radius:2px;"></span>
                            <span style="position:absolute;right:-4px;width:10px;height:10px;background:#94A3B8;border-radius:2px;"></span>
                        </div>
                        <div style="position:relative;width:54px;height:54px;display:flex;align-items:center;justify-content:center;">
                            <div style="width:40px;height:40px;border-radius:50%;background:#FFD600;box-shadow:0 0 10px rgba(255,214,0,0.5);"></div>
                            <span style="position:absolute;top:-4px;width:10px;height:10px;background:#94A3B8;border-radius:2px;"></span>
                            <span style="position:absolute;bottom:-4px;width:10px;height:10px;background:#94A3B8;border-radius:2px;"></span>
                            <span style="position:absolute;left:-4px;width:10px;height:10px;background:#94A3B8;border-radius:2px;"></span>
                            <span style="position:absolute;right:-4px;width:10px;height:10px;background:#94A3B8;border-radius:2px;"></span>
                        </div>
                    </div>
                </div>

                {{-- 🪑 3 BLOK UTAMA TEMPAT DUDUK (BLOK KIRI, TENGAH, KANAN) --}}
                <div style="display:flex;gap:20px;min-width:960px;justify-content:center;">

                    {{-- BLOK 1: BLOK KIRI (Magister M1-M12 + SI S1-S71 + TI T1-T25) --}}
                    <div style="flex:1;max-width:340px;">
                        <div style="text-align:center;font-size:11px;font-weight:700;color:#94A3B8;margin-bottom:8px;letter-spacing:1px;">
                            BLOK KIRI
                        </div>
                        
                        {{-- Row Magister (M1 - M12) Hijau --}}
                        <div class="mb-2" style="background:rgba(0,200,83,0.1);border:1px dashed #00C853;border-radius:6px;padding:4px;">
                            <div style="font-size:9px;font-weight:700;color:#00C853;text-align:center;margin-bottom:3px;">
                                MAGISTER (M1 - M12)
                            </div>
                            <div style="display:grid;grid-template-columns:repeat(11, 1fr);gap:2px;">
                                @for($i = 1; $i <= 12; $i++)
                                    @php
                                        $code = "M{$i}";
                                        $p = $assigned[$code] ?? ($assigned["MIK-{$i}"] ?? ($assigned["MIK-".str_pad($i, 2, '0', STR_PAD_LEFT)] ?? null));
                                        $isOccupied = $p && !empty($p['Waktu Kehadiran']);
                                        $isAssigned = $p !== null;

                                        $bg = $isAssigned ? '#00C853' : '#0F291E';
                                        $color = $isAssigned ? '#ffffff' : '#4ADE80';
                                        $border = $isOccupied ? '2px solid #00E676' : ($isAssigned ? 'none' : '1px solid #15803D');
                                    @endphp
                                    <button type="button"
                                            onclick="inspectSeat('{{ $code }}', '{{ $p ? addslashes($p['Nama Lengkap'] ?? $p['nama'] ?? '') : '' }}', '{{ $p ? ($p['NIM'] ?? '') : '' }}', 'Magister Ilmu Komputer', '{{ $p ? (!empty($p['Waktu Kehadiran']) ? 'Hadir' : 'Belum Hadir') : 'Kosong' }}')"
                                            style="height:20px;border-radius:2px;background:{{ $bg }};color:{{ $color }};border:{{ $border }};font-weight:700;font-size:8px;display:flex;align-items:center;justify-content:center;cursor:pointer;padding:0;"
                                            title="{{ $code }}: {{ $p ? ($p['Nama Lengkap'] ?? $p['nama'] ?? '') : 'Kosong' }}">
                                        {{ $code }}
                                    </button>
                                @endfor
                            </div>
                        </div>

                        {{-- Grid Sistem Informasi (S1 - S71) Orange --}}
                        <div class="mb-2" style="background:rgba(255,145,0,0.1);border:1px dashed #FF9100;border-radius:6px;padding:4px;">
                            <div style="font-size:9px;font-weight:700;color:#FF9100;text-align:center;margin-bottom:3px;">
                                SISTEM INFORMASI (S1 - S71)
                            </div>
                            <div style="display:grid;grid-template-columns:repeat(11, 1fr);gap:2px;">
                                @for($i = 1; $i <= 71; $i++)
                                    @php
                                        $code = "S{$i}";
                                        $p = $assigned[$code] ?? ($assigned["SI-{$i}"] ?? ($assigned["SI-".str_pad($i, 2, '0', STR_PAD_LEFT)] ?? null));
                                        $isOccupied = $p && !empty($p['Waktu Kehadiran']);
                                        $isAssigned = $p !== null;

                                        $bg = $isAssigned ? '#FF9100' : '#2A1D0F';
                                        $color = $isAssigned ? '#ffffff' : '#FB923C';
                                        $border = $isOccupied ? '2px solid #FFAB40' : ($isAssigned ? 'none' : '1px solid #C2410C');
                                    @endphp
                                    <button type="button"
                                            onclick="inspectSeat('{{ $code }}', '{{ $p ? addslashes($p['Nama Lengkap'] ?? $p['nama'] ?? '') : '' }}', '{{ $p ? ($p['NIM'] ?? '') : '' }}', 'Sistem Informasi', '{{ $p ? (!empty($p['Waktu Kehadiran']) ? 'Hadir' : 'Belum Hadir') : 'Kosong' }}')"
                                            style="height:20px;border-radius:2px;background:{{ $bg }};color:{{ $color }};border:{{ $border }};font-weight:700;font-size:8px;display:flex;align-items:center;justify-content:center;cursor:pointer;padding:0;"
                                            title="{{ $code }}: {{ $p ? ($p['Nama Lengkap'] ?? $p['nama'] ?? '') : 'Kosong' }}">
                                        {{ $code }}
                                    </button>
                                @endfor
                            </div>
                        </div>

                        {{-- Section TI Kiri (T1 - T25) Biru --}}
                        <div style="background:rgba(0,38,202,0.1);border:1px dashed #0026CA;border-radius:6px;padding:4px;">
                            <div style="font-size:9px;font-weight:700;color:#2979FF;text-align:center;margin-bottom:3px;">
                                TEKNIK INFORMATIKA (T1 - T25)
                            </div>
                            <div style="display:grid;grid-template-columns:repeat(11, 1fr);gap:2px;">
                                @for($i = 1; $i <= 25; $i++)
                                    @php
                                        $code = "T{$i}";
                                        $p = $assigned[$code] ?? ($assigned["TI-{$i}"] ?? ($assigned["TI-".str_pad($i, 2, '0', STR_PAD_LEFT)] ?? null));
                                        $isOccupied = $p && !empty($p['Waktu Kehadiran']);
                                        $isAssigned = $p !== null;

                                        $bg = $isAssigned ? '#0026CA' : '#0B163B';
                                        $color = $isAssigned ? '#ffffff' : '#60A5FA';
                                        $border = $isOccupied ? '2px solid #2979FF' : ($isAssigned ? 'none' : '1px solid #1D4ED8');
                                    @endphp
                                    <button type="button"
                                            onclick="inspectSeat('{{ $code }}', '{{ $p ? addslashes($p['Nama Lengkap'] ?? $p['nama'] ?? '') : '' }}', '{{ $p ? ($p['NIM'] ?? '') : '' }}', 'Teknik Informatika', '{{ $p ? (!empty($p['Waktu Kehadiran']) ? 'Hadir' : 'Belum Hadir') : 'Kosong' }}')"
                                            style="height:20px;border-radius:2px;background:{{ $bg }};color:{{ $color }};border:{{ $border }};font-weight:700;font-size:8px;display:flex;align-items:center;justify-content:center;cursor:pointer;padding:0;"
                                            title="{{ $code }}: {{ $p ? ($p['Nama Lengkap'] ?? $p['nama'] ?? '') : 'Kosong' }}">
                                        {{ $code }}
                                    </button>
                                @endfor
                            </div>
                        </div>
                    </div>

                    {{-- BLOK 2: BLOK TENGAH (TI T26 - T132) --}}
                    <div style="flex:1;max-width:340px;">
                        <div style="text-align:center;font-size:11px;font-weight:700;color:#94A3B8;margin-bottom:8px;letter-spacing:1px;">
                            BLOK TENGAH
                        </div>
                        <div style="background:rgba(0,38,202,0.1);border:1px dashed #0026CA;border-radius:6px;padding:4px;">
                            <div style="font-size:9px;font-weight:700;color:#2979FF;text-align:center;margin-bottom:3px;">
                                TEKNIK INFORMATIKA (T26 - T132)
                            </div>
                            <div style="display:grid;grid-template-columns:repeat(11, 1fr);gap:2px;">
                                @for($i = 26; $i <= 132; $i++)
                                    @php
                                        $code = "T{$i}";
                                        $p = $assigned[$code] ?? ($assigned["TI-{$i}"] ?? ($assigned["TI-".str_pad($i, 2, '0', STR_PAD_LEFT)] ?? null));
                                        $isOccupied = $p && !empty($p['Waktu Kehadiran']);
                                        $isAssigned = $p !== null;

                                        $bg = $isAssigned ? '#0026CA' : '#0B163B';
                                        $color = $isAssigned ? '#ffffff' : '#60A5FA';
                                        $border = $isOccupied ? '2px solid #2979FF' : ($isAssigned ? 'none' : '1px solid #1D4ED8');
                                    @endphp
                                    <button type="button"
                                            onclick="inspectSeat('{{ $code }}', '{{ $p ? addslashes($p['Nama Lengkap'] ?? $p['nama'] ?? '') : '' }}', '{{ $p ? ($p['NIM'] ?? '') : '' }}', 'Teknik Informatika', '{{ $p ? (!empty($p['Waktu Kehadiran']) ? 'Hadir' : 'Belum Hadir') : 'Kosong' }}')"
                                            style="height:20px;border-radius:2px;background:{{ $bg }};color:{{ $color }};border:{{ $border }};font-weight:700;font-size:8px;display:flex;align-items:center;justify-content:center;cursor:pointer;padding:0;"
                                            title="{{ $code }}: {{ $p ? ($p['Nama Lengkap'] ?? $p['nama'] ?? '') : 'Kosong' }}">
                                        {{ $code }}
                                    </button>
                                @endfor
                            </div>
                        </div>
                    </div>

                    {{-- BLOK 3: BLOK KANAN (TI T133 - T239) --}}
                    <div style="flex:1;max-width:340px;">
                        <div style="text-align:center;font-size:11px;font-weight:700;color:#94A3B8;margin-bottom:8px;letter-spacing:1px;">
                            BLOK KANAN
                        </div>
                        <div style="background:rgba(0,38,202,0.1);border:1px dashed #0026CA;border-radius:6px;padding:4px;">
                            <div style="font-size:9px;font-weight:700;color:#2979FF;text-align:center;margin-bottom:3px;">
                                TEKNIK INFORMATIKA (T133 - T239)
                            </div>
                            <div style="display:grid;grid-template-columns:repeat(11, 1fr);gap:2px;">
                                @for($i = 133; $i <= 239; $i++)
                                    @php
                                        $code = "T{$i}";
                                        $p = $assigned[$code] ?? ($assigned["TI-{$i}"] ?? ($assigned["TI-".str_pad($i, 2, '0', STR_PAD_LEFT)] ?? null));
                                        $isOccupied = $p && !empty($p['Waktu Kehadiran']);
                                        $isAssigned = $p !== null;

                                        $bg = $isAssigned ? '#0026CA' : '#0B163B';
                                        $color = $isAssigned ? '#ffffff' : '#60A5FA';
                                        $border = $isOccupied ? '2px solid #2979FF' : ($isAssigned ? 'none' : '1px solid #1D4ED8');
                                    @endphp
                                    <button type="button"
                                            onclick="inspectSeat('{{ $code }}', '{{ $p ? addslashes($p['Nama Lengkap'] ?? $p['nama'] ?? '') : '' }}', '{{ $p ? ($p['NIM'] ?? '') : '' }}', 'Teknik Informatika', '{{ $p ? (!empty($p['Waktu Kehadiran']) ? 'Hadir' : 'Belum Hadir') : 'Kosong' }}')"
                                            style="height:20px;border-radius:2px;background:{{ $bg }};color:{{ $color }};border:{{ $border }};font-weight:700;font-size:8px;display:flex;align-items:center;justify-content:center;cursor:pointer;padding:0;"
                                            title="{{ $code }}: {{ $p ? ($p['Nama Lengkap'] ?? $p['nama'] ?? '') : 'Kosong' }}">
                                        {{ $code }}
                                    </button>
                                @endfor
                            </div>
                        </div>
                    </div>

                </div>


            </div>
        </div>
    </div>

    {{-- ── Manual Assign Form / Unassigned List ───────────────── --}}
    <div class="col-12 col-xl-4">
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
                        <label class="form-label" style="font-size:12px;font-weight:600;">Nomor Bangku (misal: M1, S1, T1)</label>
                        <input type="text" name="nomor_kursi" id="quickNomorKursi" class="form-control" placeholder="Contoh: M1, S5 atau T10" style="font-size:13px;" required uppercase>
                        <small class="text-muted" style="font-size:11px;">Format: `M1..M12` (Magister), `S1..S71` (SI), `T1..T239` (TI).</small>
                    </div>

                    <button type="submit" class="btn-primary-sm w-100 justify-content-center py-2">
                        <i class="bi bi-check-circle-fill me-1"></i> Simpan Bangku Manual
                    </button>
                </form>

                <div style="font-size:13px;font-weight:600;color:var(--color-gray-800);margin-bottom:10px;display:flex;justify-content:space-between;align-items:center;">
                    <span>Belum Punya Bangku</span>
                    <span class="badge bg-warning text-dark">{{ count($unassigned) }}</span>
                </div>

                <div style="max-height:380px;overflow-y:auto;border:1px solid var(--color-gray-200);border-radius:10px;">
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

{{-- ── Modal Plotting Otomatis Urut NIM ───────────────────── --}}
<div class="modal fade" id="autoPlotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:18px;border:none;">
            <div class="modal-header" style="border-bottom:1px solid var(--color-gray-200);padding:16px 20px;">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:36px;height:36px;border-radius:50%;background:var(--color-primary-light);color:var(--color-primary);display:flex;align-items:center;justify-content:center;font-size:18px;">
                        <i class="bi bi-lightning-charge-fill text-warning"></i>
                    </div>
                    <div>
                        <h6 class="modal-title mb-0" style="font-weight:700;">Plotting Urut NIM Otomatis</h6>
                        <small class="text-muted">Mengalokasikan M1-M12, S1-S71, T1-T239 berdasarkan NIM (Ascending)</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('admin.plotting.auto') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding:20px;">
                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px;font-weight:600;">Metode Alokasi</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="mode" id="modeReset" value="reset_all" checked>
                            <label class="form-check-label" for="modeReset" style="font-size:13px;">
                                <strong>Reset & Plotting Ulang Seluruh Peserta</strong> (Urutkan dari NIM terkecil ke terbesar)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="mode" id="modeUnassigned" value="unassigned">
                            <label class="form-check-label" for="modeUnassigned" style="font-size:13px;">
                                <strong>Hanya Peserta Tanpa Bangku</strong> (Pertahankan bangku yang sudah terplot)
                            </label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer" style="border-top:1px solid var(--color-gray-200);padding:14px 20px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="font-size:13px;font-weight:600;">Batal</button>
                    <button type="submit" class="btn-primary-sm" style="padding:8px 18px;">
                        <i class="bi bi-play-circle-fill me-1"></i> Jalankan Plotting Urut NIM
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

{{-- ── Modal Inspect Kursi ─────────────────────────────────── --}}
<div class="modal fade" id="seatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="border-bottom:1px solid var(--color-gray-200);padding:14px 18px;">
                <h6 class="modal-title mb-0" id="seatModalTitle" style="font-weight:700;">Detail Bangku</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" style="padding:20px;">
                <div id="seatBadge" style="width:78px;height:54px;border-radius:12px;background:var(--color-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;margin:0 auto 12px;padding:0 8px;">
                    -
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

@endsection

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
    
    const badgeEl = document.getElementById('seatBadge');
    badgeEl.textContent = code;
    if (code.startsWith('M')) {
        badgeEl.style.background = '#00C853';
    } else if (code.startsWith('S')) {
        badgeEl.style.background = '#FF9100';
    } else {
        badgeEl.style.background = '#0026CA';
    }

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
