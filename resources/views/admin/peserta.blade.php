@extends('layouts.admin')

@section('title', 'Data Peserta')
@section('page-title', 'Data Peserta')
@section('breadcrumb', 'Data Peserta')

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

{{-- ── Data Table Card ─────────────────────────────────────── --}}
<div class="data-card">

    <div class="data-card-header">
        <div class="data-card-title">
            <i class="bi bi-people-fill me-2 text-primary"></i>
            Daftar Peserta
            <span style="background:var(--color-primary-light);color:var(--color-primary);
                         font-size:11px;font-weight:600;padding:2px 8px;border-radius:20px;
                         margin-left:8px;">
                {{ $pagination['total'] }}
            </span>
        </div>

        <form method="GET" action="{{ route('admin.peserta') }}"
              class="d-flex align-items-center gap-2" style="flex-wrap:wrap;">
            <div class="search-input-wrap">
                <i class="bi bi-search"></i>
                <input type="text" name="search" class="search-input"
                       placeholder="Cari nama / NIM / prodi..."
                       value="{{ request('search') }}">
            </div>

            <select name="prodi" class="search-input" style="width:auto;padding-left:10px;"
                    onchange="this.form.submit()">
                <option value="">Semua Program Studi</option>
                <option value="Teknik Informatika"     {{ request('prodi') === 'Teknik Informatika'     ? 'selected' : '' }}>Teknik Informatika</option>
                <option value="Sistem Informasi"       {{ request('prodi') === 'Sistem Informasi'       ? 'selected' : '' }}>Sistem Informasi</option>
                <option value="Magister Ilmu Komputer" {{ request('prodi') === 'Magister Ilmu Komputer' ? 'selected' : '' }}>Magister Ilmu Komputer</option>
            </select>

            <select name="status" class="search-input" style="width:auto;padding-left:10px;"
                    onchange="this.form.submit()">
                <option value="">Semua Status Hadir</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Sudah Hadir</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Belum Hadir</option>
            </select>

            <select name="payment" class="search-input" style="width:auto;padding-left:10px;"
                    onchange="this.form.submit()">
                <option value="">Semua Pembayaran</option>
                <option value="Valid"   {{ request('payment') === 'Valid'   ? 'selected' : '' }}>Valid</option>
                <option value="Pending" {{ request('payment') === 'Pending' ? 'selected' : '' }}>Pending</option>
            </select>

            <button type="submit" class="btn-primary-sm">
                <i class="bi bi-search"></i> Cari
            </button>

            @if(request('search') || request('prodi') || (request('status') !== null && request('status') !== '') || request('payment'))
                <a href="{{ route('admin.peserta') }}" class="btn-outline-sm">
                    <i class="bi bi-x"></i> Reset
                </a>
            @endif

            <a href="{{ route('admin.export', request()->query()) }}" class="btn-outline-sm" style="margin-left:auto;">
                <i class="bi bi-file-earmark-excel"></i> Export CSV
            </a>
        </form>
    </div>

    @if(empty($pagination['data']))
        <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-people"></i></div>
            <h6>Tidak ada peserta ditemukan</h6>
            <p>Coba ubah kata kunci pencarian atau filter.</p>
        </div>
    @else
        <div style="overflow-x:auto;">
            @php
                // Konversi Google Drive "open" link ke "preview" link — dideklarasikan sekali di sini
                if (!function_exists('drivePreview')) {
                    function drivePreview($url) {
                        if (preg_match('/id=([\w-]+)/', $url, $m)) {
                            return 'https://drive.google.com/file/d/' . $m[1] . '/preview';
                        }
                        return $url;
                    }
                }
            @endphp
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Peserta</th>
                        <th>Email</th>
                        <th>Program Studi</th>
                        <th>No. HP (WA)</th>
                        <th>Bank / Rek.</th>
                        <th>Pembayaran</th>
                        <th>Bukti</th>
                        <th>Status Hadir</th>
                        <th>Waktu Hadir</th>
                        <th style="width:80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pagination['data'] as $i => $p)
                    <tr>
                        <td style="color:var(--color-gray-400);font-size:12px;">
                            {{ $pagination['from'] + $i }}
                        </td>

                        <td>
                            <div class="table-name-cell">
                                @php
                                    $namaPeserta = $p['Nama Lengkap'] ?? $p['Nama Lengkap '] ?? $p['Nama'] ?? $p['nama'] ?? '-';
                                @endphp
                                <div class="table-avatar">
                                    {{ strtoupper(substr($namaPeserta !== '-' ? $namaPeserta : 'X', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="table-name">{{ $namaPeserta }}</div>
                                    <div class="table-nim">{{ $p['NIM'] ?? '-' }}</div>
                                </div>
                            </div>
                        </td>

                        <td style="color:var(--color-gray-600); font-size:12px;">
                            {{ $p['Email Address'] ?? $p['Email Address '] ?? $p['Email'] ?? $p['Email '] ?? '-' }}
                        </td>

                        <td>{{ $p['Program Studi'] ?? '-' }}</td>

                        <td style="color:var(--color-gray-600);">
                            {{ $p['No. Handphone (WA)'] ?? $p['No. Handphone (WA) '] ?? $p['No. HP (WA)'] ?? '-' }}
                        </td>

                        <td style="font-size:12px;">
                            <div style="font-weight:500;">{{ $p['Bank Asal'] ?? '-' }}</div>
                            <div style="color:var(--color-gray-500);">{{ $p['Nomor Rekening'] ?? '-' }}</div>
                        </td>

                        <td>
                            @php $pay = $p['Status Pembayaran'] ?? '-'; @endphp
                            @if(in_array(strtolower($pay), ['valid', 'validkan']))
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge-status badge-hadir">valid</span>
                                    <form action="{{ route('admin.peserta.pembayaran') }}" method="POST" class="d-inline"
                                          onsubmit="confirmAction(event, {
                                              title: 'Batalkan Status Valid',
                                              message: 'Batalkan status valid pembayaran untuk <strong>{{ addslashes($namaPeserta) }}</strong>?',
                                              icon: 'bi-exclamation-triangle-fill',
                                              iconBg: '#FEF3C7',
                                              iconColor: '#D97706',
                                              btnText: 'Ya, Ubah Status',
                                              btnClass: 'btn-warning text-white'
                                          })">
                                        @csrf
                                        <input type="hidden" name="nim" value="{{ $p['NIM'] ?? '' }}">
                                        <input type="hidden" name="status" value="Tidak Valid">
                                        <button type="submit" class="btn btn-sm p-0 text-muted" title="Ubah status ke Tidak Valid" style="font-size:12px;border:none;background:none;">
                                            <i class="bi bi-x-circle-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="d-flex flex-column gap-1">
                                    <span class="badge-status badge-belum">
                                        {{ $pay ?: 'Pending' }}
                                    </span>
                                    <div class="d-flex align-items-center gap-1 mt-1">
                                        <form action="{{ route('admin.peserta.pembayaran') }}" method="POST" class="d-inline"
                                              onsubmit="confirmAction(event, {
                                                  title: 'Validasi Pembayaran',
                                                  message: 'Setujui dan ubah status pembayaran <strong>{{ addslashes($namaPeserta) }}</strong> menjadi <strong class=\'text-success\'>valid</strong>?',
                                                  icon: 'bi-shield-check',
                                                  iconBg: '#D1FAE5',
                                                  iconColor: '#059669',
                                                  btnText: 'Ya, Validkan',
                                                  btnClass: 'btn-success'
                                              })">
                                            @csrf
                                            <input type="hidden" name="nim" value="{{ $p['NIM'] ?? '' }}">
                                            <input type="hidden" name="status" value="valid">
                                            <button type="submit" class="btn btn-sm btn-success py-0 px-2" style="font-size:11px;border-radius:6px;" title="Verifikasi Pembayaran Valid">
                                                <i class="bi bi-check-lg"></i> Validkan
                                            </button>
                                        </form>
                                        @if($pay !== 'Tidak Valid')
                                        <form action="{{ route('admin.peserta.pembayaran') }}" method="POST" class="d-inline"
                                              onsubmit="confirmAction(event, {
                                                  title: 'Tolak Pembayaran',
                                                  message: 'Tolak bukti pembayaran <strong>{{ addslashes($namaPeserta) }}</strong>?',
                                                  icon: 'bi-x-circle-fill',
                                                  iconBg: '#FEE2E2',
                                                  iconColor: '#DC2626',
                                                  btnText: 'Ya, Tolak',
                                                  btnClass: 'btn-danger'
                                              })">
                                            @csrf
                                            <input type="hidden" name="nim" value="{{ $p['NIM'] ?? '' }}">
                                            <input type="hidden" name="status" value="Tidak Valid">
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1" style="font-size:11px;border-radius:6px;" title="Tolak Pembayaran">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </td>

                        {{-- Bukti Transfer & Foto Formal --}}
                        <td>
                            <div style="display:flex;gap:4px;flex-wrap:wrap;">
                                @php
                                    $buktiBayar = trim($p['Bukti Transfer'] ?? $p['Bukti Transfer '] ?? '');
                                    $fotoFormal = trim($p['Upload Foto (Formal)'] ?? $p['Upload Foto Formal'] ?? $p['Upload Foto (Formal) '] ?? '');
                                @endphp

                                @if($buktiBayar)
                                    <button type="button"
                                            class="action-btn"
                                            title="Lihat Bukti Transfer"
                                            onclick="showBukti('{{ drivePreview($buktiBayar) }}', '{{ addslashes($namaPeserta) }}', 'Bukti Transfer', '{{ $p['NIM'] ?? '' }}', '{{ addslashes($pay) }}')">
                                        <i class="bi bi-receipt"></i>
                                    </button>
                                @endif

                                @if($fotoFormal)
                                    <button type="button"
                                            class="action-btn"
                                            title="Lihat Foto Formal"
                                            onclick="showBukti('{{ drivePreview($fotoFormal) }}', '{{ addslashes($namaPeserta) }}', 'Foto Formal', '{{ $p['NIM'] ?? '' }}', '{{ addslashes($pay) }}')">
                                        <i class="bi bi-person-badge"></i>
                                    </button>
                                @endif

                                @if(!$buktiBayar && !$fotoFormal)
                                    <span style="font-size:12px;color:var(--color-gray-400);">—</span>
                                @endif
                            </div>
                        </td>

                        <td>
                            @if(!empty($p['Waktu Kehadiran']))
                                <span class="badge-status badge-hadir">Hadir</span>
                            @else
                                <span class="badge-status badge-belum">Belum Hadir</span>
                            @endif
                        </td>

                        <td style="font-size:12px; color:var(--color-gray-500);">
                            {{ $p['Waktu Kehadiran'] ?? '—' }}
                        </td>

                        <td>
                            @if(empty($p['Waktu Kehadiran']))
                                <form action="{{ route('admin.peserta.hadir') }}" method="POST"
                                      onsubmit="confirmAction(event, {
                                          title: 'Konfirmasi Kehadiran',
                                          message: 'Tandai <strong>{{ addslashes($namaPeserta) }}</strong> sebagai <strong class=\'text-success\'>HADIR</strong>?',
                                          icon: 'bi-person-check-fill',
                                          iconBg: '#D1FAE5',
                                          iconColor: '#059669',
                                          btnText: 'Ya, Tandai Hadir',
                                          btnClass: 'btn-success'
                                      })">
                                    @csrf
                                    <input type="hidden" name="nim" value="{{ $p['NIM'] ?? '' }}">
                                    <button type="submit" class="action-btn" title="Tandai Hadir">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                            @else
                                <span style="font-size:12px; color:var(--color-gray-400);">✓</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($pagination['last_page'] > 1)
        <div class="table-footer">
            <div class="table-footer-info">
                Menampilkan {{ $pagination['from'] }}–{{ $pagination['to'] }}
                dari {{ $pagination['total'] }} peserta
            </div>
            <div class="pagination-wrap">
                {{-- Prev --}}
                @if($pagination['current_page'] <= 1)
                    <span class="page-btn disabled">
                        <i class="bi bi-chevron-left" style="font-size:11px;"></i>
                    </span>
                @else
                    <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['current_page'] - 1]) }}"
                       class="page-btn">
                        <i class="bi bi-chevron-left" style="font-size:11px;"></i>
                    </a>
                @endif

                {{-- Pages --}}
                @for($pg = 1; $pg <= $pagination['last_page']; $pg++)
                    <a href="{{ request()->fullUrlWithQuery(['page' => $pg]) }}"
                       class="page-btn {{ $pagination['current_page'] === $pg ? 'active' : '' }}">
                        {{ $pg }}
                    </a>
                @endfor

                {{-- Next --}}
                @if($pagination['current_page'] >= $pagination['last_page'])
                    <span class="page-btn disabled">
                        <i class="bi bi-chevron-right" style="font-size:11px;"></i>
                    </span>
                @else
                    <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['current_page'] + 1]) }}"
                       class="page-btn">
                        <i class="bi bi-chevron-right" style="font-size:11px;"></i>
                    </a>
                @endif
            </div>
        </div>
        @endif
    @endif

</div>

@endsection

{{-- ── Modal Bukti Pembayaran & Foto Formal ────────────────── --}}
<div class="modal fade" id="buktiModal" tabindex="-1" aria-labelledby="buktiModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden;">

            <div class="modal-header" style="padding:16px 20px;border-bottom:1px solid var(--color-gray-200);">
                <div>
                    <h6 class="modal-title mb-0" id="buktiModalTitle"
                        style="font-weight:600;font-size:15px;"></h6>
                    <small id="buktiModalNama" class="text-muted"></small>
                </div>
                <div style="display:flex;gap:8px;align-items:center;">
                    <a id="buktiOpenLink" href="#" target="_blank" class="btn-outline-sm" style="font-size:12px;">
                        <i class="bi bi-box-arrow-up-right"></i> Buka di Drive
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            </div>

            <div class="modal-body" style="padding:0;background:#1a1a2e;min-height:500px;
                                           display:flex;align-items:center;justify-content:center;">
                {{-- Loading spinner --}}
                <div id="buktiLoading"
                     style="text-align:center;color:#fff;padding:40px;">
                    <div class="spinner-border text-light mb-3" role="status"></div>
                    <div style="font-size:13px;opacity:0.7;">Memuat file...</div>
                </div>

                {{-- Iframe Google Drive Preview --}}
                <iframe id="buktiFrame"
                        src=""
                        style="width:100%;height:560px;border:none;display:none;"
                        allow="autoplay"
                        onload="frameLoaded()">
                </iframe>
            </div>

            <div class="modal-footer"
                 style="padding:12px 20px;border-top:1px solid var(--color-gray-200);
                        display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                <div style="font-size:12px;color:var(--color-gray-500);">
                    <i class="bi bi-info-circle me-1"></i>
                    Verifikasi kelayakan bukti pembayaran peserta.
                </div>
                <div id="modalBuktiActions" style="display:flex;gap:8px;align-items:center;">
                    <form action="{{ route('admin.peserta.pembayaran') }}" method="POST" class="d-inline"
                          onsubmit="confirmAction(event, {
                              title: 'Setujui Pembayaran',
                              message: 'Verifikasi & setujui bukti pembayaran ini menjadi <strong class=\'text-success\'>VALID</strong>?',
                              icon: 'bi-shield-check',
                              iconBg: '#D1FAE5',
                              iconColor: '#059669',
                              btnText: 'Ya, Setujui',
                              btnClass: 'btn-success'
                          })">
                        @csrf
                        <input type="hidden" name="nim" id="modalNimValid" value="">
                        <input type="hidden" name="status" value="valid">
                        <button type="submit" class="btn btn-sm btn-success" style="font-weight:600;font-size:12px;border-radius:8px;padding:6px 14px;">
                            <i class="bi bi-check-circle-fill me-1"></i> Setujui (Tandai VALID)
                        </button>
                    </form>
                    <form action="{{ route('admin.peserta.pembayaran') }}" method="POST" class="d-inline"
                          onsubmit="confirmAction(event, {
                              title: 'Tolak Pembayaran',
                              message: 'Tolak bukti pembayaran peserta ini?',
                              icon: 'bi-x-circle-fill',
                              iconBg: '#FEE2E2',
                              iconColor: '#DC2626',
                              btnText: 'Ya, Tolak',
                              btnClass: 'btn-danger'
                          })">
                        @csrf
                        <input type="hidden" name="nim" id="modalNimReject" value="">
                        <input type="hidden" name="status" value="Tidak Valid">
                        <button type="submit" class="btn btn-sm btn-outline-danger" style="font-weight:600;font-size:12px;border-radius:8px;padding:6px 14px;">
                            <i class="bi bi-x-circle-fill me-1"></i> Tolak Pembayaran
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ── Custom Modern Confirmation Modal ─────────────────────── --}}
<div class="modal fade" id="customConfirmModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-sm" style="max-width:380px;">
        <div class="modal-content" style="border-radius:20px;border:none;box-shadow:0 20px 40px rgba(0,0,0,0.25);overflow:hidden;">
            <div class="modal-body text-center" style="padding:28px 24px 16px;">
                <div id="confirmModalIconWrap" class="mb-3" style="width:64px;height:64px;border-radius:50%;background:#EEF2FF;color:#4F46E5;display:flex;align-items:center;justify-content:center;margin:0 auto;font-size:28px;">
                    <i id="confirmModalIcon" class="bi bi-question-circle-fill"></i>
                </div>
                <h6 id="confirmModalTitle" style="font-weight:700;font-size:17px;color:#111827;margin-bottom:8px;">
                    Konfirmasi
                </h6>
                <div id="confirmModalMessage" style="font-size:13.5px;color:#6B7280;line-height:1.5;margin-bottom:0;">
                    Apakah Anda yakin ingin melakukan tindakan ini?
                </div>
            </div>
            <div class="modal-footer" style="padding:16px 24px 24px;border-top:none;display:flex;gap:10px;justify-content:center;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="flex:1;border-radius:10px;font-weight:600;font-size:13.5px;padding:10px;color:#4B5563;background:#F3F4F6;border:none;">
                    Batal
                </button>
                <button type="button" id="confirmModalSubmitBtn" class="btn btn-primary" style="flex:1;border-radius:10px;font-weight:600;font-size:13.5px;padding:10px;">
                    Ya, Lanjutkan
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let pendingConfirmForm = null;

function confirmAction(e, options = {}) {
    e.preventDefault();
    pendingConfirmForm = e.target.closest('form');

    const title     = options.title     || 'Konfirmasi';
    const message   = options.message   || 'Apakah Anda yakin?';
    const icon      = options.icon      || 'bi-question-circle-fill';
    const iconBg    = options.iconBg    || '#EEF2FF';
    const iconColor = options.iconColor || '#4F46E5';
    const btnText   = options.btnText   || 'Ya, Lanjutkan';
    const btnClass  = options.btnClass  || 'btn-primary';

    document.getElementById('confirmModalTitle').textContent = title;
    document.getElementById('confirmModalMessage').innerHTML = message;

    const iconWrap = document.getElementById('confirmModalIconWrap');
    iconWrap.style.background = iconBg;
    iconWrap.style.color      = iconColor;
    document.getElementById('confirmModalIcon').className = 'bi ' + icon;

    const submitBtn = document.getElementById('confirmModalSubmitBtn');
    submitBtn.textContent = btnText;
    submitBtn.className   = 'btn ' + btnClass;

    const confirmModal = new bootstrap.Modal(document.getElementById('customConfirmModal'));
    confirmModal.show();
}

document.addEventListener('DOMContentLoaded', function() {
    const submitBtn = document.getElementById('confirmModalSubmitBtn');
    if (submitBtn) {
        submitBtn.addEventListener('click', function() {
            if (pendingConfirmForm) {
                const modalEl = document.getElementById('customConfirmModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
                pendingConfirmForm.submit();
            }
        });
    }
});

function showBukti(previewUrl, nama, judul, nim = '', payStatus = '') {
    document.getElementById('buktiModalTitle').textContent = judul;
    document.getElementById('buktiModalNama').textContent  = nama;

    // Set NIM inputs in modal actions
    document.getElementById('modalNimValid').value  = nim;
    document.getElementById('modalNimReject').value = nim;

    // Show/hide validation buttons depending on whether it's transfer proof
    const actions = document.getElementById('modalBuktiActions');
    if (judul === 'Bukti Transfer' && nim) {
        actions.style.display = 'flex';
    } else {
        actions.style.display = 'none';
    }

    // Set link "Buka di Drive" — konversi preview URL balik ke view URL
    const viewUrl = previewUrl.replace('/preview', '/view');
    document.getElementById('buktiOpenLink').href = viewUrl;

    // Reset frame
    const frame   = document.getElementById('buktiFrame');
    const loading = document.getElementById('buktiLoading');
    frame.style.display   = 'none';
    loading.style.display = 'block';
    frame.src = previewUrl;

    new bootstrap.Modal(document.getElementById('buktiModal')).show();
}

function frameLoaded() {
    document.getElementById('buktiFrame').style.display   = 'block';
    document.getElementById('buktiLoading').style.display = 'none';
}
</script>
@endpush
