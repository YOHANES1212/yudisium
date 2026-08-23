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
                       placeholder="Cari nama / NIM / prodi / kursi..."
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

            <a href="{{ route('admin.export', request()->query()) }}" class="btn-outline-sm" style="margin-left:auto;" download onclick="notifyExport(event)">
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
                if (!function_exists('driveFileId')) {
                    function driveFileId($url) {
                        if (preg_match('/id=([\w-]+)/', $url, $m)) return $m[1];
                        if (preg_match('/\/d\/([\w-]+)/', $url, $m)) return $m[1];
                        return '';
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
                        <th>Kursi</th>
                        <th>Pembayaran</th>
                        <th>Bukti</th>
                        <th>Status Hadir</th>
                        <th>Waktu Hadir</th>
                        <th style="width:90px;">Aksi</th>
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

                        {{-- Nomor Kursi --}}
                        <td>
                            @php $kursi = $p['Nomor Kursi'] ?? '-'; @endphp
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span class="badge {{ $kursi !== '-' ? 'bg-primary' : 'bg-light text-muted border' }}" style="font-size:12px;font-weight:600;padding:4px 8px;border-radius:6px;">
                                    🪑 {{ $kursi }}
                                </span>
                                <button type="button" class="btn btn-sm p-0 text-primary" title="Atur / Edit Kursi" onclick="openEditKursiModal('{{ $p['NIM'] ?? '' }}', '{{ addslashes($namaPeserta) }}', '{{ $kursi !== '-' ? $kursi : '' }}')">
                                    <i class="bi bi-pencil-square" style="font-size:13px;"></i>
                                </button>
                            </div>
                        </td>

                        <td>
                            @php $pay = $p['Status Pembayaran'] ?? '-'; @endphp
                            @if(in_array(strtolower($pay), ['valid', 'validkan']))
                                <div class="d-flex flex-column gap-1">
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
                                    @if(!empty($p['Validated By']))
                                        <div style="font-size:11px;color:var(--color-gray-500);line-height:1.2;">
                                            <i class="bi bi-person-check-fill text-success"></i> {{ $p['Validated By'] }}
                                            @if(!empty($p['Validated At']))
                                                <span style="font-size:10px;color:var(--color-gray-400);display:block;">{{ $p['Validated At'] }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="d-flex flex-column gap-1">
                                    <span class="badge-status badge-belum">
                                        {{ $pay ?: 'Pending' }}
                                    </span>
                                    @if(!empty($p['Validated By']))
                                        <div style="font-size:11px;color:var(--color-gray-500);line-height:1.2;">
                                            <i class="bi bi-person-gear"></i> {{ $p['Validated By'] }}
                                            @if(!empty($p['Validated At']))
                                                <span style="font-size:10px;color:var(--color-gray-400);display:block;">{{ $p['Validated At'] }}</span>
                                            @endif
                                        </div>
                                    @endif
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
                                    $idBukti = driveFileId($buktiBayar);
                                    $idFoto  = driveFileId($fotoFormal);
                                @endphp

                                @if($buktiBayar)
                                    <button type="button"
                                            class="action-btn"
                                            title="Lihat Bukti Transfer"
                                            onclick="showBukti('{{ $idBukti }}', '{{ $buktiBayar }}', '{{ addslashes($namaPeserta) }}', 'Bukti Transfer', '{{ $p['NIM'] ?? '' }}')">
                                        <i class="bi bi-receipt"></i>
                                    </button>
                                @endif

                                @if($fotoFormal)
                                    <button type="button"
                                            class="action-btn"
                                            title="Lihat Foto Formal"
                                            onclick="showBukti('{{ $idFoto }}', '{{ $fotoFormal }}', '{{ addslashes($namaPeserta) }}', 'Foto Formal', '{{ $p['NIM'] ?? '' }}')">
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
                                <form action="{{ route('admin.peserta.batal-hadir') }}" method="POST" class="d-inline"
                                      onsubmit="confirmAction(event, {
                                          title: 'Batalkan Kehadiran',
                                          message: 'Batalkan status kehadiran <strong>{{ addslashes($namaPeserta) }}</strong>?',
                                          icon: 'bi-x-circle-fill',
                                          iconBg: '#FEE2E2',
                                          iconColor: '#DC2626',
                                          btnText: 'Ya, Batal Hadir',
                                          btnClass: 'btn-danger'
                                      })">
                                    @csrf
                                    <input type="hidden" name="nim" value="{{ $p['NIM'] ?? '' }}">
                                    <button type="submit" class="action-btn text-danger" title="Batalkan Kehadiran Peserta">
                                        <i class="bi bi-person-x-fill"></i>
                                    </button>
                                </form>
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

                @for($pg = 1; $pg <= $pagination['last_page']; $pg++)
                    <a href="{{ request()->fullUrlWithQuery(['page' => $pg]) }}"
                       class="page-btn {{ $pagination['current_page'] === $pg ? 'active' : '' }}">
                        {{ $pg }}
                    </a>
                @endfor

                @if($pagination['current_page'] >= $pagination['last_page'])
                    <span class="page-btn disabled">
                        <i class="bi bi-chevron-right" style="font-size:11px;"></i>
                    </span>
                @else
                    <a href="{{ request()->fullUrlWithQuery(['page' => $pg + 1]) }}"
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

{{-- ── Modal Bukti Pembayaran & Foto Formal (Direct Stream) ───── --}}
<div class="modal fade" id="buktiModal" tabindex="-1" aria-labelledby="buktiModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden;">

            <div class="modal-header" style="padding:16px 20px;border-bottom:1px solid var(--color-gray-200);">
                <div>
                    <h6 class="modal-title mb-0" id="buktiModalTitle" style="font-weight:600;font-size:15px;"></h6>
                    <small id="buktiModalNama" class="text-muted"></small>
                </div>
                <div style="display:flex;gap:8px;align-items:center;">
                    <a id="buktiDirectLink" href="#" target="_blank" class="btn-outline-sm" style="font-size:12px;" title="Buka gambar di tab baru">
                        <i class="bi bi-box-arrow-up-right"></i> Buka Langsung
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            </div>

            <div class="modal-body" style="padding:20px;background:#0F172A;min-height:420px;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                {{-- Direct Image Preview --}}
                <img id="buktiImg" src="" style="max-width:100%;max-height:460px;border-radius:10px;object-fit:contain;display:none;box-shadow:0 10px 25px rgba(0,0,0,0.5);" alt="Preview">

                {{-- Iframe Fallback --}}
                <iframe id="buktiFrame" src="" style="width:100%;height:460px;border:none;display:none;border-radius:10px;"></iframe>

                {{-- Loading --}}
                <div id="buktiLoading" style="text-align:center;color:#fff;padding:40px;">
                    <div class="spinner-border text-light mb-3" role="status"></div>
                    <div style="font-size:13px;opacity:0.8;">Memuat gambar langsung dari Drive...</div>
                </div>

                {{-- Access Hint --}}
                <div class="alert alert-info border-0 mt-3 mb-0 w-100" style="font-size:12px;background:rgba(255,255,255,0.1);color:#93C5FD;border-radius:10px;">
                    <i class="bi bi-info-circle me-1"></i> <strong>Tips Akses Google Drive:</strong> Jika gambar belum muncul, buka folder Google Form Anda sekali dan set akses ke <em>"Anyone with the link can view"</em> agar terbuka otomatis untuk seluruh peserta.
                </div>
            </div>

            <div class="modal-footer" style="padding:14px 20px;border-top:1px solid var(--color-gray-200);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                <div style="display:flex;gap:8px;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="switchViewMode('img')" style="font-size:12px;">
                        🖼️ Mode Gambar Direct
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="switchViewMode('iframe')" style="font-size:12px;">
                        📄 Mode Viewer Drive
                    </button>
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

{{-- ── Modal Edit Nomor Kursi ───────────────────────────────── --}}
<div class="modal fade" id="editKursiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="border-bottom:1px solid var(--color-gray-200);padding:14px 18px;">
                <h6 class="modal-title mb-0" style="font-weight:700;">Atur Nomor Kursi</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.peserta.kursi') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding:18px;">
                    <div style="font-size:13px;font-weight:600;color:var(--color-gray-800);margin-bottom:4px;" id="editKursiNama">-</div>
                    <div style="font-size:12px;color:var(--color-gray-500);margin-bottom:14px;" id="editKursiNim">-</div>

                    <input type="hidden" name="nim" id="editKursiNimInput" value="">
                    <div class="mb-2">
                        <label class="form-label" style="font-size:12px;font-weight:600;">Nomor Kursi (misal: SI-01, TI-01)</label>
                        <input type="text" name="nomor_kursi" id="editKursiValInput" class="form-control" placeholder="Contoh: SI-01 atau TI-05" style="font-size:13px;" required uppercase>
                    </div>
                </div>
                <div class="modal-footer d-flex flex-column gap-2" style="border-top:none;padding:0 18px 18px;">
                    <button type="submit" class="btn-primary-sm w-100 justify-content-center py-2">
                        <i class="bi bi-check-circle-fill me-1"></i> Simpan Nomor Kursi
                    </button>
                    <button type="button" id="deleteKursiBtnInModal" class="btn btn-outline-danger w-100" style="border-radius:8px;font-size:12px;font-weight:600;padding:8px;" onclick="submitDeleteKursiFromPesertaModal()">
                        <i class="bi bi-trash3-fill me-1"></i> Hapus / Kosongkan Kursi Ini
                    </button>
                </div>
            </form>
            <form id="deleteKursiFormFromPeserta" action="{{ route('admin.peserta.kursi.hapus') }}" method="POST" style="display:none;">
                @csrf
                <input type="hidden" name="nim" id="deleteKursiFormNim">
            </form>
        </div>
    </div>
</div>

{{-- ── Modal Konfirmasi Hapus Bangku (Pengganti Pop-up Browser Native) ── --}}
<div class="modal fade" id="deleteSeatConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered modal-sm" style="max-width:360px;">
        <div class="modal-content" style="border-radius:20px;border:none;box-shadow:0 20px 40px rgba(0,0,0,0.25);overflow:hidden;">
            <div class="modal-body text-center" style="padding:28px 20px 16px;">
                <div style="width:60px;height:60px;border-radius:50%;background:#FEE2E2;color:#EF4444;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:26px;">
                    <i class="bi bi-trash3-fill"></i>
                </div>
                <h6 style="font-weight:700;font-size:16px;color:#111827;margin-bottom:6px;">Kosongkan Bangku Ini?</h6>
                <div style="font-size:13px;color:#6B7280;margin-bottom:0;" id="deleteSeatModalMsg">
                    Apakah Anda yakin ingin mengosongkan bangku peserta ini?
                </div>
            </div>
            <div class="modal-footer" style="border-top:none;padding:12px 20px 24px;display:flex;gap:10px;justify-content:center;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="flex:1;border-radius:10px;font-weight:600;font-size:13px;padding:10px;color:#4B5563;background:#F3F4F6;border:none;">
                    Batal
                </button>
                <button type="button" onclick="executeDeleteSeatFromModal()" class="btn btn-danger" style="flex:1;border-radius:10px;font-weight:600;font-size:13px;padding:10px;">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Custom Confirmation Modal ────────────────────────────── --}}
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
let currentFileId = '';
let currentRawUrl  = '';

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

function openEditKursiModal(nim, nama, kursi) {
    document.getElementById('editKursiNama').textContent = nama;
    document.getElementById('editKursiNim').textContent  = 'NIM: ' + nim;
    document.getElementById('editKursiNimInput').value  = nim;
    document.getElementById('editKursiValInput').value  = (kursi === '-' ? '' : kursi);

    const delBtn = document.getElementById('deleteKursiBtnInModal');
    if (delBtn) {
        delBtn.style.display = (kursi && kursi !== '-') ? 'block' : 'none';
    }

    new bootstrap.Modal(document.getElementById('editKursiModal')).show();
}

function submitDeleteKursiFromPesertaModal() {
    const nim  = document.getElementById('editKursiNimInput').value;
    const nama = document.getElementById('editKursiNama').textContent;
    if (nim) {
        document.getElementById('deleteSeatModalMsg').innerHTML = `Apakah Anda yakin ingin mengosongkan bangku untuk <strong>${nama}</strong> (NIM: ${nim})?`;
        document.getElementById('deleteKursiFormNim').value = nim;
        new bootstrap.Modal(document.getElementById('deleteSeatConfirmModal')).show();
    }
}

function executeDeleteSeatFromModal() {
    const modalEl = document.getElementById('deleteSeatConfirmModal');
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    if (modalInstance) modalInstance.hide();
    document.getElementById('deleteKursiFormFromPeserta').submit();
}

function showBukti(fileId, rawUrl, nama, judul, nim = '') {
    currentFileId = fileId;
    currentRawUrl  = rawUrl;

    document.getElementById('buktiModalTitle').textContent = judul;
    document.getElementById('buktiModalNama').textContent  = nama + (nim ? ' (' + nim + ')' : '');

    document.getElementById('modalNimValid').value  = nim;
    document.getElementById('modalNimReject').value = nim;

    const actions = document.getElementById('modalBuktiActions');
    actions.style.display = (judul === 'Bukti Transfer' && nim) ? 'flex' : 'none';

    const directImg = fileId ? `https://lh3.googleusercontent.com/d/${fileId}` : rawUrl;
    document.getElementById('buktiDirectLink').href = fileId ? `https://drive.google.com/file/d/${fileId}/view` : rawUrl;

    switchViewMode('img');
    new bootstrap.Modal(document.getElementById('buktiModal')).show();
}

function switchViewMode(mode) {
    const img     = document.getElementById('buktiImg');
    const frame   = document.getElementById('buktiFrame');
    const loading = document.getElementById('buktiLoading');

    img.style.display     = 'none';
    frame.style.display   = 'none';
    loading.style.display = 'block';

    if (mode === 'img' && currentFileId) {
        const directImg = `https://lh3.googleusercontent.com/d/${currentFileId}`;
        img.src = directImg;
        img.onload = function() {
            loading.style.display = 'none';
            img.style.display     = 'block';
        };
        img.onerror = function() {
            switchViewMode('iframe');
        };
    } else {
        const previewUrl = currentFileId ? `https://drive.google.com/file/d/${currentFileId}/preview` : currentRawUrl;
        frame.src = previewUrl;
        frame.onload = function() {
            loading.style.display = 'none';
            frame.style.display   = 'block';
        };
    }
}
</script>
@endpush
