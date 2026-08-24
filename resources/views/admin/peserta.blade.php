@extends('layouts.admin')

@section('title', 'Data Peserta')
@section('page-title', 'Data Peserta')
@section('breadcrumb', 'Data Peserta')

@section('content')

{{-- ── Alerts ──────────────────────────────────────────────── --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert"
         style="border-radius:12px; font-size:14px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15); border: none; background: #D1FAE5; color: #065F46;">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert"
         style="border-radius:12px; font-size:14px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15); border: none; background: #FEE2E2; color: #991B1B;">
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
                         font-size:12px;font-weight:700;padding:3px 10px;border-radius:20px;
                         margin-left:8px;">
                {{ $pagination['total'] }}
            </span>
        </div>

        <form method="GET" action="{{ route('admin.peserta') }}"
              class="d-flex align-items-center gap-2" style="flex-wrap:wrap; width: 100%; max-width: 100%;">
            <div class="search-input-wrap" style="flex: 1; min-width: 200px;">
                <i class="bi bi-search"></i>
                <input type="text" name="search" class="search-input" style="width: 100%;"
                       placeholder="Cari nama / NIM / prodi / kursi..."
                       value="{{ request('search') }}">
            </div>

            <select name="prodi" class="search-input" style="width:auto;padding-left:10px; flex: 1; min-width: 150px;"
                    onchange="this.form.submit()">
                <option value="">Semua Program Studi</option>
                <option value="Teknik Informatika"     {{ request('prodi') === 'Teknik Informatika'     ? 'selected' : '' }}>Teknik Informatika</option>
                <option value="Sistem Informasi"       {{ request('prodi') === 'Sistem Informasi'       ? 'selected' : '' }}>Sistem Informasi</option>
                <option value="Magister Ilmu Komputer" {{ request('prodi') === 'Magister Ilmu Komputer' ? 'selected' : '' }}>Magister Ilmu Komputer</option>
            </select>

            <select name="status" class="search-input" style="width:auto;padding-left:10px; flex: 1; min-width: 140px;"
                    onchange="this.form.submit()">
                <option value="">Semua Status Hadir</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Sudah Hadir</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Belum Hadir</option>
            </select>

            <select name="payment" class="search-input" style="width:auto;padding-left:10px; flex: 1; min-width: 140px;"
                    onchange="this.form.submit()">
                <option value="">Semua Pembayaran</option>
                <option value="Valid"   {{ request('payment') === 'Valid'   ? 'selected' : '' }}>Valid</option>
                <option value="Pending" {{ request('payment') === 'Pending' ? 'selected' : '' }}>Pending</option>
            </select>

            <button type="submit" class="btn-primary-sm" style="min-height: 38px; padding: 0 16px;">
                <i class="bi bi-search"></i> Cari
            </button>

            @if(request('search') || request('prodi') || (request('status') !== null && request('status') !== '') || request('payment'))
                <a href="{{ route('admin.peserta') }}" class="btn-outline-sm" style="min-height: 38px; padding: 0 12px; display: inline-flex; align-items: center;">
                    <i class="bi bi-x-lg"></i> Reset
                </a>
            @endif

            <a href="{{ route('admin.export', request()->query()) }}" class="btn-outline-sm ms-auto" style="min-height: 38px; padding: 0 14px; display: inline-flex; align-items: center;" download onclick="notifyExport(event)">
                <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
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
        @php
            if (!function_exists('driveFileId')) {
                function driveFileId($url) {
                    if (preg_match('/id=([\w-]+)/', $url, $m)) return $m[1];
                    if (preg_match('/\/d\/([\w-]+)/', $url, $m)) return $m[1];
                    return '';
                }
            }
        @endphp

        {{-- ── 1. MOBILE CARDS VIEW (< 768px) ──────────────────────────────── --}}
        <div class="d-block d-md-none p-3">
            @foreach($pagination['data'] as $i => $p)
                @php
                    $namaPeserta = $p['Nama Lengkap'] ?? $p['Nama Lengkap '] ?? $p['Nama'] ?? $p['nama'] ?? '-';
                    $nim   = $p['NIM'] ?? '';
                    $email = $p['Email Address'] ?? $p['Email Address '] ?? $p['Email'] ?? $p['Email '] ?? '';
                    $key   = $p['_key'] ?? ($nim && $nim !== '-' ? $nim : ($email ? 'EMAIL:'.$email : 'NAMA:'.$namaPeserta));
                    $prodi = $p['Program Studi'] ?? '-';
                    $phone = $p['No. Handphone (WA)'] ?? $p['No. Handphone (WA) '] ?? $p['No. HP (WA)'] ?? '-';
                    $kursi = $p['Nomor Kursi'] ?? '-';
                    $pay = $p['Status Pembayaran'] ?? '-';
                    $isValidPay = in_array(strtolower($pay), ['valid', 'validkan']);
                    $buktiBayar = trim($p['Bukti Transfer'] ?? $p['Bukti Transfer '] ?? '');
                    $fotoFormal = trim($p['Upload Foto (Formal)'] ?? $p['Upload Foto Formal'] ?? $p['Upload Foto (Formal) '] ?? '');
                    $idBukti = driveFileId($buktiBayar);
                    $idFoto  = driveFileId($fotoFormal);
                    $waktuHadir = $p['Waktu Kehadiran'] ?? '';
                    $isHadir = !empty($waktuHadir);
                @endphp

                <div class="peserta-card-mobile">
                    {{-- Header Row: Avatar, Name, NIM, Seat --}}
                    <div class="peserta-card-header">
                        <div class="d-flex align-items-center gap-3" style="min-width: 0;">
                            <div class="peserta-avatar-mobile">
                                {{ strtoupper(substr($namaPeserta !== '-' ? $namaPeserta : 'X', 0, 1)) }}
                            </div>
                            <div style="min-width: 0;">
                                <div class="fw-bold text-dark text-truncate" style="font-size: 15px; line-height: 1.2;">
                                    {{ $namaPeserta }}
                                </div>
                                <div class="text-muted" style="font-size: 12px; margin-top: 2px;">
                                    NIM: <strong>{{ $nim ?: '-' }}</strong>
                                </div>
                                <div class="badge bg-light text-secondary border mt-1" style="font-size: 11px; font-weight: 500;">
                                    {{ $prodi }}
                                </div>
                            </div>
                        </div>

                        {{-- Seat Pill & Edit Trigger --}}
                        <div class="d-flex flex-column align-items-end flex-shrink-0">
                            <span class="badge {{ $kursi !== '-' ? 'bg-primary' : 'bg-light text-muted border' }}" style="font-size:12px; font-weight:700; padding:6px 10px; border-radius:8px;">
                                🪑 {{ $kursi }}
                            </span>
                            <button type="button" class="btn btn-sm btn-link text-primary p-0 mt-1" style="font-size:12px; text-decoration:none;"
                                    data-nim="{{ $nim }}"
                                    data-email="{{ $email }}"
                                    data-nama="{{ e($namaPeserta) }}"
                                    data-key="{{ $key }}"
                                    data-kursi="{{ $kursi !== '-' ? $kursi : '' }}"
                                    onclick="triggerEditKursi(this)">
                                <i class="bi bi-pencil-square me-1"></i> Edit Kursi
                            </button>
                        </div>
                    </div>

                    {{-- Info Grid: Pembayaran & Kehadiran --}}
                    <div class="peserta-info-block">
                        <div>
                            <div class="text-muted" style="font-size: 11px; margin-bottom: 2px;">Pembayaran:</div>
                            @if($isValidPay)
                                <span class="badge-status badge-hadir" style="font-size:11px;">valid</span>
                                @if(!empty($p['Validated By']))
                                    <div class="text-muted mt-1" style="font-size: 10px; line-height: 1.2;">
                                        <i class="bi bi-person-check-fill text-success me-1"></i>{{ $p['Validated By'] }}
                                    </div>
                                @endif
                            @else
                                <span class="badge-status badge-belum" style="font-size:11px;">{{ $pay ?: 'Pending' }}</span>
                            @endif
                        </div>

                        <div>
                            <div class="text-muted" style="font-size: 11px; margin-bottom: 2px;">Status Hadir:</div>
                            @if($isHadir)
                                <span class="badge-status badge-hadir" style="font-size:11px;">Hadir</span>
                                <div class="text-muted mt-1" style="font-size: 10px;">{{ $waktuHadir }}</div>
                            @else
                                <span class="badge-status badge-belum" style="font-size:11px;">Belum Hadir</span>
                            @endif
                        </div>
                    </div>

                    {{-- Mobile Touch Action Buttons Grid --}}
                    <div class="peserta-action-bar">

                        {{-- Action 1: Pembayaran Validasi / Batalkan --}}
                        @if($isValidPay)
                            <form action="{{ route('admin.peserta.pembayaran') }}" method="POST" class="d-inline confirm-action-form flex-fill"
                                  data-confirm-title="Batalkan Status Valid"
                                  data-confirm-message="Batalkan status valid pembayaran untuk <strong>{{ e($namaPeserta) }}</strong>?"
                                  data-confirm-icon="bi-exclamation-triangle-fill"
                                  data-confirm-icon-bg="#FEF3C7"
                                  data-confirm-icon-color="#D97706"
                                  data-confirm-btn-text="Ya, Ubah Status"
                                  data-confirm-btn-class="btn-warning text-white">
                                @csrf
                                <input type="hidden" name="nim" value="{{ $nim }}">
                                <input type="hidden" name="email" value="{{ $email }}">
                                <input type="hidden" name="nama" value="{{ $namaPeserta }}">
                                <input type="hidden" name="key" value="{{ $key }}">
                                <input type="hidden" name="status" value="Tidak Valid">
                                <button type="submit" class="btn-action-mobile btn-cancel-valid-action w-100" title="Batalkan Validasi">
                                    <i class="bi bi-arrow-counterclockwise"></i> Batal Valid
                                </button>
                            </form>
                        @else
                            <form action="{{ route('admin.peserta.pembayaran') }}" method="POST" class="d-inline confirm-action-form flex-fill"
                                  data-confirm-title="Validasi Pembayaran"
                                  data-confirm-message="Setujui & ubah status pembayaran <strong>{{ e($namaPeserta) }}</strong> menjadi <strong class='text-success'>VALID</strong>?"
                                  data-confirm-icon="bi-shield-check"
                                  data-confirm-icon-bg="#D1FAE5"
                                  data-confirm-icon-color="#059669"
                                  data-confirm-btn-text="Ya, Validkan"
                                  data-confirm-btn-class="btn-success">
                                @csrf
                                <input type="hidden" name="nim" value="{{ $nim }}">
                                <input type="hidden" name="email" value="{{ $email }}">
                                <input type="hidden" name="nama" value="{{ $namaPeserta }}">
                                <input type="hidden" name="key" value="{{ $key }}">
                                <input type="hidden" name="status" value="valid">
                                <button type="submit" class="btn-action-mobile btn-valid-action w-100" title="Verifikasi Valid">
                                    <i class="bi bi-check-circle-fill"></i> Validkan
                                </button>
                            </form>

                            @if($pay !== 'Tidak Valid')
                                <form action="{{ route('admin.peserta.pembayaran') }}" method="POST" class="d-inline confirm-action-form" style="flex:0 0 auto;"
                                      data-confirm-title="Tolak Pembayaran"
                                      data-confirm-message="Tolak bukti pembayaran <strong>{{ e($namaPeserta) }}</strong>?"
                                      data-confirm-icon="bi-x-circle-fill"
                                      data-confirm-icon-bg="#FEE2E2"
                                      data-confirm-icon-color="#DC2626"
                                      data-confirm-btn-text="Ya, Tolak"
                                      data-confirm-btn-class="btn-danger">
                                    @csrf
                                    <input type="hidden" name="nim" value="{{ $nim }}">
                                    <input type="hidden" name="email" value="{{ $email }}">
                                    <input type="hidden" name="nama" value="{{ $namaPeserta }}">
                                    <input type="hidden" name="key" value="{{ $key }}">
                                    <input type="hidden" name="status" value="Tidak Valid">
                                    <button type="submit" class="btn-action-mobile btn-reject-action" title="Tolak Pembayaran">
                                        <i class="bi bi-x-lg"></i> Tolak
                                    </button>
                                </form>
                            @endif
                        @endif

                        {{-- Action 2: Lihat Bukti TF / Foto --}}
                        @if($buktiBayar)
                            <button type="button" class="btn-action-mobile btn-proof-action btn-action-mobile-sm"
                                    data-fileid="{{ $idBukti }}"
                                    data-rawurl="{{ $buktiBayar }}"
                                    data-nama="{{ e($namaPeserta) }}"
                                    data-judul="Bukti Transfer"
                                    data-nim="{{ $nim }}"
                                    data-email="{{ $email }}"
                                    data-key="{{ $key }}"
                                    onclick="triggerShowBukti(this)">
                                <i class="bi bi-receipt text-primary"></i> Bukti TF
                            </button>
                        @endif

                        @if($fotoFormal)
                            <button type="button" class="btn-action-mobile btn-proof-action btn-action-mobile-sm"
                                    data-fileid="{{ $idFoto }}"
                                    data-rawurl="{{ $fotoFormal }}"
                                    data-nama="{{ e($namaPeserta) }}"
                                    data-judul="Foto Formal"
                                    data-nim="{{ $nim }}"
                                    data-email="{{ $email }}"
                                    data-key="{{ $key }}"
                                    onclick="triggerShowBukti(this)">
                                <i class="bi bi-person-badge text-info"></i> Foto
                            </button>
                        @endif

                        {{-- Action 3: Hadir / Batal Hadir --}}
                        @if(!$isHadir)
                            <form action="{{ route('admin.peserta.hadir') }}" method="POST" class="d-inline confirm-action-form flex-fill"
                                  data-confirm-title="Konfirmasi Kehadiran"
                                  data-confirm-message="Tandai <strong>{{ e($namaPeserta) }}</strong> sebagai <strong class='text-success'>HADIR</strong>?"
                                  data-confirm-icon="bi-person-check-fill"
                                  data-confirm-icon-bg="#D1FAE5"
                                  data-confirm-icon-color="#059669"
                                  data-confirm-btn-text="Ya, Tandai Hadir"
                                  data-confirm-btn-class="btn-success">
                                @csrf
                                <input type="hidden" name="nim" value="{{ $nim }}">
                                <input type="hidden" name="email" value="{{ $email }}">
                                <input type="hidden" name="nama" value="{{ $namaPeserta }}">
                                <input type="hidden" name="key" value="{{ $key }}">
                                <button type="submit" class="btn-action-mobile btn-hadir-action w-100" title="Tandai Hadir">
                                    <i class="bi bi-person-check-fill"></i> Hadir
                                </button>
                            </form>
                        @else
                            <form action="{{ route('admin.peserta.batal-hadir') }}" method="POST" class="d-inline confirm-action-form" style="flex:0 0 auto;"
                                  data-confirm-title="Batalkan Kehadiran"
                                  data-confirm-message="Batalkan status kehadiran <strong>{{ e($namaPeserta) }}</strong>?"
                                  data-confirm-icon="bi-x-circle-fill"
                                  data-confirm-icon-bg="#FEE2E2"
                                  data-confirm-icon-color="#DC2626"
                                  data-confirm-btn-text="Ya, Batal Hadir"
                                  data-confirm-btn-class="btn-danger">
                                @csrf
                                <input type="hidden" name="nim" value="{{ $nim }}">
                                <input type="hidden" name="email" value="{{ $email }}">
                                <input type="hidden" name="nama" value="{{ $namaPeserta }}">
                                <input type="hidden" name="key" value="{{ $key }}">
                                <button type="submit" class="btn-action-mobile btn-batal-hadir-action" title="Batalkan Kehadiran">
                                    <i class="bi bi-person-x-fill"></i> Batal Hadir
                                </button>
                            </form>
                        @endif

                    </div>
                </div>
            @endforeach
        </div>

        {{-- ── 2. DESKTOP TABLE VIEW (>= 768px) ────────────────────────────── --}}
        <div class="d-none d-md-block" style="overflow-x:auto;">
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
                        <th style="width:110px; text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pagination['data'] as $i => $p)
                    @php
                        $namaPeserta = $p['Nama Lengkap'] ?? $p['Nama Lengkap '] ?? $p['Nama'] ?? $p['nama'] ?? '-';
                        $nim   = $p['NIM'] ?? '';
                        $email = $p['Email Address'] ?? $p['Email Address '] ?? $p['Email'] ?? $p['Email '] ?? '';
                        $key   = $p['_key'] ?? ($nim && $nim !== '-' ? $nim : ($email ? 'EMAIL:'.$email : 'NAMA:'.$namaPeserta));
                        $prodi = $p['Program Studi'] ?? '-';
                        $phone = $p['No. Handphone (WA)'] ?? $p['No. Handphone (WA) '] ?? $p['No. HP (WA)'] ?? '-';
                        $kursi = $p['Nomor Kursi'] ?? '-';
                        $pay = $p['Status Pembayaran'] ?? '-';
                        $isValidPay = in_array(strtolower($pay), ['valid', 'validkan']);
                        $buktiBayar = trim($p['Bukti Transfer'] ?? $p['Bukti Transfer '] ?? '');
                        $fotoFormal = trim($p['Upload Foto (Formal)'] ?? $p['Upload Foto Formal'] ?? $p['Upload Foto (Formal) '] ?? '');
                        $idBukti = driveFileId($buktiBayar);
                        $idFoto  = driveFileId($fotoFormal);
                        $waktuHadir = $p['Waktu Kehadiran'] ?? '';
                        $isHadir = !empty($waktuHadir);
                    @endphp
                    <tr>
                        <td style="color:var(--color-gray-400);font-size:12px;">
                            {{ $pagination['from'] + $i }}
                        </td>

                        <td>
                            <div class="table-name-cell">
                                <div class="table-avatar">
                                    {{ strtoupper(substr($namaPeserta !== '-' ? $namaPeserta : 'X', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="table-name">{{ $namaPeserta }}</div>
                                    <div class="table-nim">{{ $nim ?: '-' }}</div>
                                </div>
                            </div>
                        </td>

                        <td style="color:var(--color-gray-600); font-size:12px;">
                            {{ $email ?: '-' }}
                        </td>

                        <td>{{ $prodi }}</td>

                        <td style="color:var(--color-gray-600);">
                            {{ $phone }}
                        </td>

                        {{-- Nomor Kursi --}}
                        <td>
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span class="badge {{ $kursi !== '-' ? 'bg-primary' : 'bg-light text-muted border' }}" style="font-size:12px;font-weight:600;padding:4px 8px;border-radius:6px;">
                                    🪑 {{ $kursi }}
                                </span>
                                <button type="button" class="btn btn-sm p-0 text-primary" title="Atur / Edit Kursi"
                                        data-nim="{{ $nim }}"
                                        data-email="{{ $email }}"
                                        data-nama="{{ e($namaPeserta) }}"
                                        data-key="{{ $key }}"
                                        data-kursi="{{ $kursi !== '-' ? $kursi : '' }}"
                                        onclick="triggerEditKursi(this)">
                                    <i class="bi bi-pencil-square" style="font-size:13px;"></i>
                                </button>
                            </div>
                        </td>

                        {{-- Pembayaran --}}
                        <td>
                            @if($isValidPay)
                                <div class="d-flex flex-column gap-1">
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="badge-status badge-hadir">valid</span>
                                        <form action="{{ route('admin.peserta.pembayaran') }}" method="POST" class="d-inline confirm-action-form"
                                              data-confirm-title="Batalkan Status Valid"
                                              data-confirm-message="Batalkan status valid pembayaran untuk <strong>{{ e($namaPeserta) }}</strong>?"
                                              data-confirm-icon="bi-exclamation-triangle-fill"
                                              data-confirm-icon-bg="#FEF3C7"
                                              data-confirm-icon-color="#D97706"
                                              data-confirm-btn-text="Ya, Ubah Status"
                                              data-confirm-btn-class="btn-warning text-white">
                                            @csrf
                                            <input type="hidden" name="nim" value="{{ $nim }}">
                                            <input type="hidden" name="email" value="{{ $email }}">
                                            <input type="hidden" name="nama" value="{{ $namaPeserta }}">
                                            <input type="hidden" name="key" value="{{ $key }}">
                                            <input type="hidden" name="status" value="Tidak Valid">
                                            <button type="submit" class="btn btn-sm p-0 text-muted" title="Ubah status ke Tidak Valid" style="font-size:13px;border:none;background:none;">
                                                <i class="bi bi-x-circle-fill text-warning"></i>
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
                                        <form action="{{ route('admin.peserta.pembayaran') }}" method="POST" class="d-inline confirm-action-form"
                                              data-confirm-title="Validasi Pembayaran"
                                              data-confirm-message="Setujui & ubah status pembayaran <strong>{{ e($namaPeserta) }}</strong> menjadi <strong class='text-success'>VALID</strong>?"
                                              data-confirm-icon="bi-shield-check"
                                              data-confirm-icon-bg="#D1FAE5"
                                              data-confirm-icon-color="#059669"
                                              data-confirm-btn-text="Ya, Validkan"
                                              data-confirm-btn-class="btn-success">
                                            @csrf
                                            <input type="hidden" name="nim" value="{{ $nim }}">
                                            <input type="hidden" name="email" value="{{ $email }}">
                                            <input type="hidden" name="nama" value="{{ $namaPeserta }}">
                                            <input type="hidden" name="key" value="{{ $key }}">
                                            <input type="hidden" name="status" value="valid">
                                            <button type="submit" class="btn btn-sm btn-success py-1 px-2" style="font-size:11px; border-radius:6px; font-weight:600;" title="Verifikasi Pembayaran Valid">
                                                <i class="bi bi-check-lg"></i> Validkan
                                            </button>
                                        </form>

                                        @if($pay !== 'Tidak Valid')
                                        <form action="{{ route('admin.peserta.pembayaran') }}" method="POST" class="d-inline confirm-action-form"
                                              data-confirm-title="Tolak Pembayaran"
                                              data-confirm-message="Tolak bukti pembayaran <strong>{{ e($namaPeserta) }}</strong>?"
                                              data-confirm-icon="bi-x-circle-fill"
                                              data-confirm-icon-bg="#FEE2E2"
                                              data-confirm-icon-color="#DC2626"
                                              data-confirm-btn-text="Ya, Tolak"
                                              data-confirm-btn-class="btn-danger">
                                            @csrf
                                            <input type="hidden" name="nim" value="{{ $nim }}">
                                            <input type="hidden" name="email" value="{{ $email }}">
                                            <input type="hidden" name="nama" value="{{ $namaPeserta }}">
                                            <input type="hidden" name="key" value="{{ $key }}">
                                            <input type="hidden" name="status" value="Tidak Valid">
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" style="font-size:11px; border-radius:6px;" title="Tolak Pembayaran">
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
                                @if($buktiBayar)
                                    <button type="button" class="action-btn" title="Lihat Bukti Transfer"
                                            data-fileid="{{ $idBukti }}"
                                            data-rawurl="{{ $buktiBayar }}"
                                            data-nama="{{ e($namaPeserta) }}"
                                            data-judul="Bukti Transfer"
                                            data-nim="{{ $nim }}"
                                            data-email="{{ $email }}"
                                            data-key="{{ $key }}"
                                            onclick="triggerShowBukti(this)">
                                        <i class="bi bi-receipt"></i>
                                    </button>
                                @endif

                                @if($fotoFormal)
                                    <button type="button" class="action-btn" title="Lihat Foto Formal"
                                            data-fileid="{{ $idFoto }}"
                                            data-rawurl="{{ $fotoFormal }}"
                                            data-nama="{{ e($namaPeserta) }}"
                                            data-judul="Foto Formal"
                                            data-nim="{{ $nim }}"
                                            data-email="{{ $email }}"
                                            data-key="{{ $key }}"
                                            onclick="triggerShowBukti(this)">
                                        <i class="bi bi-person-badge"></i>
                                    </button>
                                @endif

                                @if(!$buktiBayar && !$fotoFormal)
                                    <span style="font-size:12px;color:var(--color-gray-400);">—</span>
                                @endif
                            </div>
                        </td>

                        <td>
                            @if($isHadir)
                                <span class="badge-status badge-hadir">Hadir</span>
                            @else
                                <span class="badge-status badge-belum">Belum Hadir</span>
                            @endif
                        </td>

                        <td style="font-size:12px; color:var(--color-gray-500);">
                            {{ $waktuHadir ?: '—' }}
                        </td>

                        <td style="text-align:center;">
                            @if(!$isHadir)
                                <form action="{{ route('admin.peserta.hadir') }}" method="POST" class="d-inline confirm-action-form"
                                      data-confirm-title="Konfirmasi Kehadiran"
                                      data-confirm-message="Tandai <strong>{{ e($namaPeserta) }}</strong> sebagai <strong class='text-success'>HADIR</strong>?"
                                      data-confirm-icon="bi-person-check-fill"
                                      data-confirm-icon-bg="#D1FAE5"
                                      data-confirm-icon-color="#059669"
                                      data-confirm-btn-text="Ya, Tandai Hadir"
                                      data-confirm-btn-class="btn-success">
                                    @csrf
                                    <input type="hidden" name="nim" value="{{ $nim }}">
                                    <input type="hidden" name="email" value="{{ $email }}">
                                    <input type="hidden" name="nama" value="{{ $namaPeserta }}">
                                    <input type="hidden" name="key" value="{{ $key }}">
                                    <button type="submit" class="action-btn text-success" title="Tandai Hadir">
                                        <i class="bi bi-check-lg" style="font-size:16px;"></i>
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.peserta.batal-hadir') }}" method="POST" class="d-inline confirm-action-form"
                                      data-confirm-title="Batalkan Kehadiran"
                                      data-confirm-message="Batalkan status kehadiran <strong>{{ e($namaPeserta) }}</strong>?"
                                      data-confirm-icon="bi-x-circle-fill"
                                      data-confirm-icon-bg="#FEE2E2"
                                      data-confirm-icon-color="#DC2626"
                                      data-confirm-btn-text="Ya, Batal Hadir"
                                      data-confirm-btn-class="btn-danger">
                                    @csrf
                                    <input type="hidden" name="nim" value="{{ $nim }}">
                                    <input type="hidden" name="email" value="{{ $email }}">
                                    <input type="hidden" name="nama" value="{{ $namaPeserta }}">
                                    <input type="hidden" name="key" value="{{ $key }}">
                                    <button type="submit" class="action-btn text-danger" title="Batalkan Kehadiran Peserta">
                                        <i class="bi bi-person-x-fill" style="font-size:16px;"></i>
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

{{-- ── Modal Bukti Pembayaran & Foto Formal ───── --}}
<div class="modal fade" id="buktiModal" tabindex="-1" aria-labelledby="buktiModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:20px;border:none;overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">

            <div class="modal-header" style="padding:16px 20px;border-bottom:1px solid var(--color-gray-200); background:#F8FAFC;">
                <div>
                    <h6 class="modal-title mb-0" id="buktiModalTitle" style="font-weight:700;font-size:16px; color:#0F172A;"></h6>
                    <small id="buktiModalNama" class="text-muted" style="font-size:13px;"></small>
                </div>
                <div style="display:flex;gap:8px;align-items:center;">
                    <a id="buktiDirectLink" href="#" target="_blank" class="btn-outline-sm" style="font-size:12px; border-radius:8px; padding:6px 12px;" title="Buka gambar di tab baru">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Buka Langsung
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            </div>

            <div class="modal-body" style="padding:20px;background:#0F172A;min-height:380px;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                {{-- Direct Image Preview --}}
                <img id="buktiImg" src="" style="max-width:100%;max-height:460px;border-radius:12px;object-fit:contain;display:none;box-shadow:0 10px 25px rgba(0,0,0,0.5);" alt="Preview">

                {{-- Iframe Fallback --}}
                <iframe id="buktiFrame" src="" style="width:100%;height:460px;border:none;display:none;border-radius:12px;"></iframe>

                {{-- Loading --}}
                <div id="buktiLoading" style="text-align:center;color:#fff;padding:40px;">
                    <div class="spinner-border text-light mb-3" role="status"></div>
                    <div style="font-size:13px;opacity:0.8;">Memuat gambar langsung dari Drive...</div>
                </div>

                {{-- Access Hint --}}
                <div class="alert alert-info border-0 mt-3 mb-0 w-100" style="font-size:12px;background:rgba(255,255,255,0.1);color:#93C5FD;border-radius:10px;">
                    <i class="bi bi-info-circle me-1"></i> <strong>Tips Drive:</strong> Jika gambar belum muncul, pastikan link file Google Form Anda sudah di-set <em>"Anyone with the link can view"</em>.
                </div>
            </div>

            <div class="modal-footer" style="padding:14px 20px;border-top:1px solid var(--color-gray-200);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px; background:#F8FAFC;">
                <div style="display:flex;gap:8px;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="switchViewMode('img')" style="font-size:12px; border-radius:8px;">
                        🖼️ Mode Gambar Direct
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="switchViewMode('iframe')" style="font-size:12px; border-radius:8px;">
                        📄 Mode Viewer Drive
                    </button>
                </div>
                <div id="modalBuktiActions" style="display:flex;gap:8px;align-items:center;">
                    <form action="{{ route('admin.peserta.pembayaran') }}" method="POST" class="d-inline confirm-action-form"
                          data-confirm-title="Setujui Pembayaran"
                          data-confirm-message="Verifikasi & setujui bukti pembayaran ini menjadi <strong class='text-success'>VALID</strong>?"
                          data-confirm-icon="bi-shield-check"
                          data-confirm-icon-bg="#D1FAE5"
                          data-confirm-icon-color="#059669"
                          data-confirm-btn-text="Ya, Setujui"
                          data-confirm-btn-class="btn-success">
                        @csrf
                        <input type="hidden" name="nim" id="modalNimValid" value="">
                        <input type="hidden" name="email" id="modalEmailValid" value="">
                        <input type="hidden" name="nama" id="modalNamaValid" value="">
                        <input type="hidden" name="key" id="modalKeyValid" value="">
                        <input type="hidden" name="status" value="valid">
                        <button type="submit" class="btn btn-sm btn-success" style="font-weight:600;font-size:12.5px;border-radius:10px;padding:8px 16px;">
                            <i class="bi bi-check-circle-fill me-1"></i> Setujui (Valid)
                        </button>
                    </form>
                    <form action="{{ route('admin.peserta.pembayaran') }}" method="POST" class="d-inline confirm-action-form"
                          data-confirm-title="Tolak Pembayaran"
                          data-confirm-message="Tolak bukti pembayaran peserta ini?"
                          data-confirm-icon="bi-x-circle-fill"
                          data-confirm-icon-bg="#FEE2E2"
                          data-confirm-icon-color="#DC2626"
                          data-confirm-btn-text="Ya, Tolak"
                          data-confirm-btn-class="btn-danger">
                        @csrf
                        <input type="hidden" name="nim" id="modalNimReject" value="">
                        <input type="hidden" name="email" id="modalEmailReject" value="">
                        <input type="hidden" name="nama" id="modalNamaReject" value="">
                        <input type="hidden" name="key" id="modalKeyReject" value="">
                        <input type="hidden" name="status" value="Tidak Valid">
                        <button type="submit" class="btn btn-sm btn-outline-danger" style="font-weight:600;font-size:12.5px;border-radius:10px;padding:8px 16px;">
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
        <div class="modal-content" style="border-radius:20px;border:none; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
            <div class="modal-header" style="border-bottom:1px solid var(--color-gray-200);padding:16px 20px;">
                <h6 class="modal-title mb-0" style="font-weight:700; color:#111827;">Atur Nomor Kursi</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.peserta.kursi') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding:20px;">
                    <div style="font-size:14px;font-weight:700;color:var(--color-gray-900);margin-bottom:2px;" id="editKursiNama">-</div>
                    <div style="font-size:12px;color:var(--color-gray-500);margin-bottom:16px;" id="editKursiNim">-</div>

                    <input type="hidden" name="nim" id="editKursiNimInput" value="">
                    <input type="hidden" name="email" id="editKursiEmailInput" value="">
                    <input type="hidden" name="nama" id="editKursiNamaInput" value="">
                    <input type="hidden" name="key" id="editKursiKeyInput" value="">

                    <div class="mb-2">
                        <label class="form-label" style="font-size:12px;font-weight:600; color:#374151;">Nomor Kursi (misal: SI-01, TI-01)</label>
                        <input type="text" name="nomor_kursi" id="editKursiValInput" class="form-control" placeholder="Contoh: SI-01 atau TI-05" style="font-size:13.5px; border-radius:10px; padding:10px 12px;" required uppercase>
                    </div>
                </div>
                <div class="modal-footer d-flex flex-column gap-2" style="border-top:none;padding:0 20px 20px;">
                    <button type="submit" class="btn-primary-sm w-100 justify-content-center py-2.5" style="border-radius:10px; font-weight:600; font-size:13px;">
                        <i class="bi bi-check-circle-fill me-1"></i> Simpan Nomor Kursi
                    </button>
                    <button type="button" id="deleteKursiBtnInModal" class="btn btn-outline-danger w-100" style="border-radius:10px;font-size:12.5px;font-weight:600;padding:8px;" onclick="submitDeleteKursiFromPesertaModal()">
                        <i class="bi bi-trash3-fill me-1"></i> Hapus / Kosongkan Kursi
                    </button>
                </div>
            </form>
            <form id="deleteKursiFormFromPeserta" action="{{ route('admin.peserta.kursi.hapus') }}" method="POST" style="display:none;">
                @csrf
                <input type="hidden" name="nim" id="deleteKursiFormNim">
                <input type="hidden" name="email" id="deleteKursiFormEmail">
                <input type="hidden" name="nama" id="deleteKursiFormNama">
                <input type="hidden" name="key" id="deleteKursiFormKey">
            </form>
        </div>
    </div>
</div>

{{-- ── Modal Konfirmasi Hapus Bangku ── --}}
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

{{-- ── Custom Singleton Confirmation Modal ────────────────────────────── --}}
<div class="modal fade" id="customConfirmModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" style="z-index: 1080;">
    <div class="modal-dialog modal-dialog-centered modal-sm" style="max-width:380px;">
        <div class="modal-content" style="border-radius:22px;border:none;box-shadow:0 25px 50px -12px rgba(0,0,0,0.3);overflow:hidden;">
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
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="flex:1;border-radius:12px;font-weight:600;font-size:13.5px;padding:10px;color:#4B5563;background:#F3F4F6;border:none;">
                    Batal
                </button>
                <button type="button" id="confirmModalSubmitBtn" class="btn btn-primary" style="flex:1;border-radius:12px;font-weight:600;font-size:13.5px;padding:10px;">
                    Ya, Lanjutkan
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let pendingConfirmForm = null;
let customConfirmModalInstance = null;
let currentFileId = '';
let currentRawUrl  = '';

// ── Global Event Delegation for Declarative Forms ────────────────
document.addEventListener('DOMContentLoaded', function() {

    // Global listener for forms marked with .confirm-action-form
    document.addEventListener('submit', function(e) {
        const form = e.target.closest('.confirm-action-form');
        if (!form) return;

        // If form has already been verified by modal, let it submit naturally
        if (form.dataset.confirmed === 'true') {
            return;
        }

        e.preventDefault();
        pendingConfirmForm = form;

        const title     = form.dataset.confirmTitle     || 'Konfirmasi';
        const message   = form.dataset.confirmMessage   || 'Apakah Anda yakin?';
        const icon      = form.dataset.confirmIcon      || 'bi-question-circle-fill';
        const iconBg    = form.dataset.confirmIconBg    || '#EEF2FF';
        const iconColor = form.dataset.confirmIconColor || '#4F46E5';
        const btnText   = form.dataset.confirmBtnText   || 'Ya, Lanjutkan';
        const btnClass  = form.dataset.confirmBtnClass  || 'btn-primary';

        const modalEl = document.getElementById('customConfirmModal');

        // Close proof modal if open to prevent modal backdrop collisions
        const buktiModalEl = document.getElementById('buktiModal');
        if (buktiModalEl && typeof bootstrap !== 'undefined') {
            const buktiModalInstance = bootstrap.Modal.getInstance(buktiModalEl);
            if (buktiModalInstance) {
                buktiModalInstance.hide();
            }
        }

        if (modalEl && typeof bootstrap !== 'undefined') {
            document.getElementById('confirmModalTitle').textContent = title;
            document.getElementById('confirmModalMessage').innerHTML = message;

            const iconWrap = document.getElementById('confirmModalIconWrap');
            iconWrap.style.background = iconBg;
            iconWrap.style.color      = iconColor;
            document.getElementById('confirmModalIcon').className = 'bi ' + icon;

            const submitBtn = document.getElementById('confirmModalSubmitBtn');
            submitBtn.innerHTML = btnText;
            submitBtn.className   = 'btn ' + btnClass;
            submitBtn.disabled    = false;

            if (!customConfirmModalInstance) {
                customConfirmModalInstance = new bootstrap.Modal(modalEl);
            }
            customConfirmModalInstance.show();
        } else {
            // Fallback if modal DOM element or Bootstrap JS fails
            const stripHtml = message.replace(/<[^>]*>?/gm, '');
            if (confirm(stripHtml)) {
                form.dataset.confirmed = 'true';
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';
                }
                HTMLFormElement.prototype.submit.call(form);
            }
        }
    });

    // Submit handler for confirmModalSubmitBtn
    const submitBtn = document.getElementById('confirmModalSubmitBtn');
    if (submitBtn) {
        submitBtn.addEventListener('click', function() {
            if (pendingConfirmForm) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Memproses...';

                if (customConfirmModalInstance) {
                    customConfirmModalInstance.hide();
                }

                // Also update form's submit button visual state
                const formBtn = pendingConfirmForm.querySelector('button[type="submit"]');
                if (formBtn) {
                    formBtn.disabled = true;
                    formBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';
                }

                // Mark form confirmed and submit using native prototype to bypass handler loops
                pendingConfirmForm.dataset.confirmed = 'true';
                HTMLFormElement.prototype.submit.call(pendingConfirmForm);
            }
        });
    }
});

// Helper triggers from dataset elements
function triggerEditKursi(btn) {
    const nim   = btn.dataset.nim || '';
    const email = btn.dataset.email || '';
    const nama  = btn.dataset.nama || '-';
    const key   = btn.dataset.key || '';
    const kursi = btn.dataset.kursi || '';
    openEditKursiModal(nim, email, nama, key, kursi);
}

function triggerShowBukti(btn) {
    const fileId = btn.dataset.fileid || '';
    const rawUrl = btn.dataset.rawurl || '';
    const nama   = btn.dataset.nama || '';
    const judul  = btn.dataset.judul || '';
    const nim    = btn.dataset.nim || '';
    const email  = btn.dataset.email || '';
    const key    = btn.dataset.key || '';
    showBukti(fileId, rawUrl, nama, judul, nim, email, key);
}

function openEditKursiModal(nim, email, nama, key, kursi) {
    document.getElementById('editKursiNama').textContent = nama;
    document.getElementById('editKursiNim').textContent  = (nim ? 'NIM: ' + nim : (email ? 'Email: ' + email : ''));
    document.getElementById('editKursiNimInput').value   = nim;
    document.getElementById('editKursiEmailInput').value = email;
    document.getElementById('editKursiNamaInput').value  = nama;
    document.getElementById('editKursiKeyInput').value   = key;
    document.getElementById('editKursiValInput').value   = (kursi === '-' ? '' : kursi);

    const delBtn = document.getElementById('deleteKursiBtnInModal');
    if (delBtn) {
        delBtn.style.display = (kursi && kursi !== '-') ? 'block' : 'none';
    }

    const modalEl = document.getElementById('editKursiModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        new bootstrap.Modal(modalEl).show();
    }
}

function submitDeleteKursiFromPesertaModal() {
    const nim   = document.getElementById('editKursiNimInput').value;
    const email = document.getElementById('editKursiEmailInput').value;
    const nama  = document.getElementById('editKursiNamaInput').value;
    const key   = document.getElementById('editKursiKeyInput').value;

    document.getElementById('deleteSeatModalMsg').innerHTML = `Apakah Anda yakin ingin mengosongkan bangku untuk <strong>${nama}</strong>?`;
    document.getElementById('deleteKursiFormNim').value   = nim;
    document.getElementById('deleteKursiFormEmail').value = email;
    document.getElementById('deleteKursiFormNama').value  = nama;
    document.getElementById('deleteKursiFormKey').value   = key;

    const modalEl = document.getElementById('deleteSeatConfirmModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        new bootstrap.Modal(modalEl).show();
    }
}

function executeDeleteSeatFromModal() {
    const modalEl = document.getElementById('deleteSeatConfirmModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();
    }
    document.getElementById('deleteKursiFormFromPeserta').submit();
}

function showBukti(fileId, rawUrl, nama, judul, nim = '', email = '', key = '') {
    currentFileId = fileId;
    currentRawUrl  = rawUrl;

    document.getElementById('buktiModalTitle').textContent = judul;
    document.getElementById('buktiModalNama').textContent  = nama + (nim ? ' (' + nim + ')' : (email ? ' (' + email + ')' : ''));

    document.getElementById('modalNimValid').value   = nim;
    document.getElementById('modalEmailValid').value = email;
    document.getElementById('modalNamaValid').value  = nama;
    document.getElementById('modalKeyValid').value   = key;

    document.getElementById('modalNimReject').value   = nim;
    document.getElementById('modalEmailReject').value = email;
    document.getElementById('modalNamaReject').value  = nama;
    document.getElementById('modalKeyReject').value   = key;

    const actions = document.getElementById('modalBuktiActions');
    actions.style.display = (judul === 'Bukti Transfer') ? 'flex' : 'none';

    const directImg = fileId ? `https://lh3.googleusercontent.com/d/${fileId}` : rawUrl;
    document.getElementById('buktiDirectLink').href = fileId ? `https://drive.google.com/file/d/${fileId}/view` : rawUrl;

    switchViewMode('img');
    const modalEl = document.getElementById('buktiModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        new bootstrap.Modal(modalEl).show();
    }
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
