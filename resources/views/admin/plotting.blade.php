@extends('layouts.admin')

@section('title', 'Plotting Kursi')
@section('page-title', 'Plotting & Denah Kursi Yudisium')
@section('breadcrumb', 'Plotting Kursi')

@section('content')

{{-- ── Alerts ──────────────────────────────────────────────── --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert"
         style="border-radius:10px; font-size:14px;">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
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

{{-- ── Action Toolbar (Otomatis & Manual) ──────────────────── --}}
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1" style="font-size:17px;color:var(--color-gray-900);">
            <i class="bi bi-layout-three-columns text-primary me-2"></i> Pengelolaan Bangku Kursi Peserta
        </h5>
        <div class="text-muted" style="font-size:13px;">
            Dukung pengalokasian kursi secara <strong>Otomatis (Auto-Plotting)</strong> atau <strong>Manual</strong> per peserta.
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn-primary-sm" onclick="openAutoPlotModal()" style="padding:9px 16px;">
            <i class="bi bi-lightning-charge-fill me-1"></i> Plotting Otomatis
        </button>
        <button type="button" class="btn-outline-sm text-danger border-danger-subtle" onclick="confirmResetPlotting()" style="padding:8px 14px;">
            <i class="bi bi-trash3-fill me-1"></i> Reset Plotting
        </button>
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
                <div class="stat-label">Hadir di Kursi</div>
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
                <div class="stat-change down"><i class="bi bi-clock"></i> Perlu Alokasi</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon info"><i class="bi bi-grid-3x3-gap-fill"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $totalCapacity }}</div>
                <div class="stat-label">Kapasitas Kursi Gedung</div>
                <div class="stat-change up"><i class="bi bi-building"></i> Denah 8 Baris x 12 Kolom</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- ── Grid Layout Denah Kursi ───────────────────────────── --}}
    <div class="col-12 col-lg-8">
        <div class="data-card">
            <div class="data-card-header">
                <div class="data-card-title">
                    <i class="bi bi-grid-3x3-gap me-2 text-primary"></i>
                    Denah Visual Kursi Peserta (Panggung Depan)
                </div>
                <div style="display:flex;gap:12px;font-size:12px;align-items:center;flex-wrap:wrap;">
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
                <div style="background:linear-gradient(135deg, #1E1B4B, #4F46E5);color:#fff;text-align:center;padding:10px;border-radius:10px;font-weight:700;font-size:13px;letter-spacing:1px;margin-bottom:24px;box-shadow:0 4px 10px rgba(0,0,0,0.1);">
                    🎬 PANGGUNG UTAMA YUDISIUM 🎭
                </div>

                {{-- Grid Seats --}}
                <div style="display:flex;flex-direction:column;gap:12px;min-width:600px;">
                    @foreach($rows as $row)
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:32px;font-weight:700;font-size:14px;color:var(--color-primary);text-align:center;">
                            {{ $row }}
                        </div>
                        <div style="flex:1;display:grid;grid-template-columns:repeat(12, 1fr);gap:8px;">
                            @foreach($cols as $col)
                            @php
                                $code = $row . str_pad($col, 2, '0', STR_PAD_LEFT);
                                $codeShort = $row . $col;
                                $p = $assigned[$code] ?? ($assigned[$codeShort] ?? null);
                                $isOccupied = $p && !empty($p['Waktu Kehadiran']);
                                $isAssigned = $p !== null;

                                $bg = $isOccupied ? '#10B981' : ($isAssigned ? '#4F46E5' : '#F3F4F6');
                                $color = $isAssigned ? '#fff' : '#6B7280';
                                $border = $isAssigned ? 'none' : '1px solid #E5E7EB';
                            @endphp

                            <button type="button"
                                    onclick="inspectSeat('{{ $code }}', '{{ $p ? addslashes($p['Nama Lengkap'] ?? $p['nama'] ?? '') : '' }}', '{{ $p ? ($p['NIM'] ?? '') : '' }}', '{{ $p ? ($p['Program Studi'] ?? '') : '' }}', '{{ $p ? (!empty($p['Waktu Kehadiran']) ? 'Hadir' : 'Belum Hadir') : 'Kosong' }}')"
                                    style="height:44px;border-radius:8px;background:{{ $bg }};color:{{ $color }};border:{{ $border }};font-weight:700;font-size:11px;display:flex;flex-direction:column;align-items:center;justify-content:center;cursor:pointer;transition:all 0.2s;"
                                    title="{{ $code }}: {{ $p ? ($p['Nama Lengkap'] ?? $p['nama'] ?? '') : 'Kosong' }}">
                                <span>{{ $code }}</span>
                                @if($isAssigned)
                                    <span style="font-size:9px;opacity:0.85;white-space:nowrap;max-width:38px;overflow:hidden;text-overflow:ellipsis;">
                                        {{ explode(' ', $p['Nama Lengkap'] ?? $p['nama'] ?? '')[0] }}
                                    </span>
                                @endif
                            </button>
                            @endforeach
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
                    <i class="bi bi-pencil-square me-1 text-primary"></i> Atur Kursi Manual
                </div>
            </div>
            <div style="padding:18px;">
                <form action="{{ route('admin.peserta.kursi') }}" method="POST" class="mb-4">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" style="font-size:12px;font-weight:600;">Pilih Peserta</label>
                        <select name="nim" class="form-select" style="font-size:13px;" required>
                            <option value="">-- Pilih Peserta --</option>
                            @foreach($pesertaList as $p)
                                <option value="{{ $p['NIM'] ?? '' }}">
                                    {{ $p['Nama Lengkap'] ?? $p['nama'] ?? '' }} (NIM: {{ $p['NIM'] ?? '' }}) {{ !empty($p['Nomor Kursi']) && $p['Nomor Kursi'] !== '-' ? '['.$p['Nomor Kursi'].']' : '[Belum ada]' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-size:12px;font-weight:600;">Nomor Kursi (misal: A01, A02, B05)</label>
                        <input type="text" name="nomor_kursi" class="form-control" placeholder="Contoh: A01" style="font-size:13px;" required uppercase>
                    </div>

                    <button type="submit" class="btn-primary-sm w-100 justify-content-center py-2">
                        <i class="bi bi-check-circle-fill me-1"></i> Simpan Kursi Manual
                    </button>
                </form>

                <div style="font-size:13px;font-weight:600;color:var(--color-gray-800);margin-bottom:10px;display:flex;justify-content:space-between;align-items:center;">
                    <span>Belum Punya Kursi</span>
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
                                        {{ $u['NIM'] ?? '-' }} · {{ $u['Program Studi'] ?? '-' }}
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
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                    <div>
                        <h6 class="modal-title mb-0" style="font-weight:700;">Plotting Kursi Otomatis</h6>
                        <small class="text-muted">Alokasikan nomor kursi sekaligus secara cepat</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('admin.plotting.auto') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding:20px;">
                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px;font-weight:600;">Urutkan Peserta Berdasarkan</label>
                        <select name="sort_by" class="form-select" style="font-size:13px;">
                            <option value="prodi">Program Studi lalu Nama (Direkomendasikan)</option>
                            <option value="nim">NIM Peserta</option>
                            <option value="nama">Nama Lengkap (A-Z)</option>
                        </select>
                        <small class="text-muted" style="font-size:11px;">Peserta akan dikelompokkan dan didudukkan berurutan mulai dari Baris A01, A02, A03...</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px;font-weight:600;">Metode Alokasi</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="mode" id="modeUnassigned" value="unassigned" checked>
                            <label class="form-check-label" for="modeUnassigned" style="font-size:13px;">
                                <strong>Hanya Peserta Tanpa Kursi</strong> (Pertahankan kursi yang sudah terplot)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="mode" id="modeReset" value="reset_all">
                            <label class="form-check-label" for="modeReset" style="font-size:13px;">
                                <strong>Reset & Plot Ulang Seluruh Peserta</strong> (Re-alokasi total dari A01)
                            </label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer" style="border-top:1px solid var(--color-gray-200);padding:14px 20px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="font-size:13px;font-weight:600;">Batal</button>
                    <button type="submit" class="btn-primary-sm" style="padding:8px 18px;">
                        <i class="bi bi-play-circle-fill me-1"></i> Jalankan Plotting Otomatis
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

{{-- ── Modal Inspect Kursi ────────────────────────────────── --}}
<div class="modal fade" id="seatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="border-bottom:1px solid var(--color-gray-200);padding:14px 18px;">
                <h6 class="modal-title mb-0" id="seatModalTitle" style="font-weight:700;">Detail Kursi</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" style="padding:20px;">
                <div id="seatBadge" style="width:54px;height:54px;border-radius:12px;background:var(--color-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;margin:0 auto 12px;">
                    A01
                </div>
                <h6 id="seatNama" style="font-weight:700;font-size:15px;margin-bottom:2px;">-</h6>
                <div id="seatNim" style="font-size:12px;color:var(--color-gray-500);margin-bottom:8px;">-</div>
                <div id="seatStatus" style="font-size:12px;margin-bottom:12px;">-</div>
            </div>
            <div class="modal-footer justify-content-center" style="border-top:none;padding:0 18px 18px;">
                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal" style="border-radius:8px;font-size:13px;">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openAutoPlotModal() {
    new bootstrap.Modal(document.getElementById('autoPlotModal')).show();
}

function confirmResetPlotting() {
    if (confirm('Apakah Anda yakin ingin MENGHAPUS / RESET SELURUH nomor kursi peserta? Tindakan ini akan mengosongkan alokasi kursi.')) {
        document.getElementById('resetPlottingForm').submit();
    }
}

function inspectSeat(code, nama, nim, prodi, status) {
    document.getElementById('seatModalTitle').textContent = 'Detail Kursi ' + code;
    document.getElementById('seatBadge').textContent = code;
    document.getElementById('seatNama').textContent  = nama || 'Kosong / belum terisi';
    document.getElementById('seatNim').textContent   = nim ? (nim + (prodi ? ' · ' + prodi : '')) : 'Kursi belum dialokasikan';

    const statusEl = document.getElementById('seatStatus');
    if (status === 'Hadir') {
        statusEl.innerHTML = '<span class="badge bg-success">🟢 Hadir di Gedung</span>';
    } else if (status === 'Belum Hadir') {
        statusEl.innerHTML = '<span class="badge bg-primary">🔵 Terplot (Belum Hadir)</span>';
    } else {
        statusEl.innerHTML = '<span class="badge bg-secondary">⚪ Kursi Kosong</span>';
    }

    new bootstrap.Modal(document.getElementById('seatModal')).show();
}

function setQuickAssign(nim) {
    const select = document.querySelector('select[name="nim"]');
    if (select) {
        select.value = nim;
        document.querySelector('input[name="nomor_kursi"]').focus();
    }
}
</script>
@endpush

@endsection
