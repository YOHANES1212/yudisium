@extends('layouts.admin')

@section('title', 'Log Panitia')
@section('page-title', 'Log Aktivitas & Audit Panitia')
@section('breadcrumb', 'Log Panitia')

@section('content')

{{-- ── Banner Panitia Terakhir Login & Akses Operator ─────────── --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-lg-5">
        <div style="background:linear-gradient(135deg, #1E1B4B, #4F46E5);color:#fff;border-radius:16px;padding:22px;box-shadow:0 8px 20px rgba(79, 70, 229, 0.2);height:100%;display:flex;flex-direction:column;justify-content:space-between;">
            <div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="badge" style="background:rgba(255,255,255,0.2);color:#fff;font-size:11px;font-weight:600;padding:4px 10px;border-radius:20px;letter-spacing:0.5px;">
                        🔑 OPERATOR TERAKHIR LOGIN
                    </span>
                    <span style="font-size:12px;opacity:0.8;">Status Panitia</span>
                </div>

                @if($lastLoginUser)
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div style="width:54px;height:54px;border-radius:50%;background:rgba(255,255,255,0.2);color:#fff;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:700;flex-shrink:0;">
                            {{ strtoupper(substr($lastLoginUser->name, 0, 1)) }}
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0" style="font-size:18px;">{{ $lastLoginUser->name }}</h5>
                            <div style="font-size:13px;opacity:0.9;">{{ $lastLoginUser->email }}</div>
                            <div style="font-size:12px;opacity:0.8;margin-top:2px;">
                                <span class="badge bg-light text-dark" style="font-family:monospace;font-size:11px;">PIN: {{ $lastLoginUser->pin }}</span>
                                <span class="ms-1">• Role: {{ ucfirst($lastLoginUser->role) }}</span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-white-50" style="font-size:13px;">Belum ada panitia login</div>
                @endif
            </div>

            <div style="background:rgba(0,0,0,0.2);padding:10px 14px;border-radius:10px;font-size:12px;display:flex;align-items:center;justify-content:space-between;margin-top:12px;">
                <span><i class="bi bi-clock-history me-1"></i> Waktu Login Terakhir:</span>
                <strong style="color:#A7F3D0;">
                    @if($lastLoginUser && $lastLoginUser->last_login_at)
                        {{ $lastLoginUser->last_login_at->diffForHumans() }} ({{ $lastLoginUser->last_login_at->format('d/m/Y H:i') }})
                    @else
                        Belum terekam
                    @endif
                </strong>
            </div>
        </div>
    </div>

    {{-- Daftar Akun Panitia & Login Stat --}}
    <div class="col-12 col-lg-7">
        <div class="data-card" style="height:100%;">
            <div class="data-card-header" style="padding:14px 18px;">
                <div class="data-card-title" style="font-size:14px;">
                    <i class="bi bi-people-fill me-2 text-primary"></i>
                    Daftar Akun Panitia & Riwayat Login
                    <span class="badge bg-primary ms-1" style="font-size:11px;">{{ count($panitiaList) }} Panitia</span>
                </div>
            </div>
            <div style="overflow-x:auto;max-height:220px;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Panitia</th>
                            <th>Email</th>
                            <th>PIN</th>
                            <th>Role</th>
                            <th>Terakhir Login</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($panitiaList as $p)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:28px;height:28px;border-radius:50%;background:var(--color-primary-light);color:var(--color-primary);font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;">
                                        {{ strtoupper(substr($p->name, 0, 1)) }}
                                    </div>
                                    <span style="font-weight:600;font-size:13px;color:var(--color-gray-900);">
                                        {{ $p->name }}
                                        @if($lastLoginUser && $lastLoginUser->id === $p->id)
                                            <span class="badge bg-success" style="font-size:9px;padding:2px 6px;">Active Last</span>
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td style="font-size:12px;color:var(--color-gray-600);">{{ $p->email }}</td>
                            <td>
                                <span class="badge" style="background:#F3F4F6;color:#374151;border:1px solid #E5E7EB;font-family:monospace;font-size:11px;padding:2px 6px;border-radius:4px;">
                                    🔑 {{ $p->pin }}
                                </span>
                            </td>
                            <td><span class="badge bg-light text-dark border" style="font-size:11px;">{{ ucfirst($p->role) }}</span></td>
                            <td style="font-size:12px;color:var(--color-gray-600);">
                                @if($p->last_login_at)
                                    <span class="text-success fw-semibold"><i class="bi bi-check-circle me-1"></i>{{ $p->last_login_at->format('d/m/Y H:i') }}</span>
                                    <small class="text-muted d-block" style="font-size:10px;">({{ $p->last_login_at->diffForHumans() }})</small>
                                @else
                                    <span class="text-muted">— Belum Login</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ── Log Validasi Pembayaran Panitia ───────────────────────── --}}
@if(isset($validationLogs) && $validationLogs->isNotEmpty())
<div class="data-card mb-4">
    <div class="data-card-header" style="padding:14px 18px;">
        <div class="data-card-title" style="font-size:14px;">
            <i class="bi bi-shield-check me-2 text-success"></i>
            Riwayat Validasi Pembayaran Peserta oleh Panitia
            <span class="badge bg-success ms-1" style="font-size:11px;">{{ $validationLogs->count() }} Terbaru</span>
        </div>
    </div>
    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th>Waktu Validasi</th>
                    <th>NIM Peserta</th>
                    <th>Status Setujui</th>
                    <th>Nomor Kursi</th>
                    <th>Divalidasi Oleh</th>
                </tr>
            </thead>
            <tbody>
                @foreach($validationLogs as $idx => $v)
                <tr>
                    <td style="font-size:12px;color:var(--color-gray-400);">{{ $idx + 1 }}</td>
                    <td style="font-size:12px;font-weight:500;">
                        <i class="bi bi-clock me-1 text-muted"></i>
                        {{ $v->validated_at ? $v->validated_at->format('d/m/Y H:i:s') : '-' }}
                    </td>
                    <td style="font-weight:600;font-size:13px;color:var(--color-gray-900);">{{ $v->nim }}</td>
                    <td>
                        <span class="badge-status badge-hadir">
                            <i class="bi bi-check-circle-fill me-1"></i> {{ ucfirst($v->status_pembayaran) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-primary" style="font-size:11px;">
                            🪑 {{ $v->nomor_kursi ?: '-' }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-1" style="font-size:13px;font-weight:600;color:var(--color-primary);">
                            <i class="bi bi-person-check-fill"></i> {{ $v->validated_by }}
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ── Data Card Log Scan QR Panitia ─────────────────────────── --}}
<div class="data-card">

    <div class="data-card-header">
        <div class="data-card-title">
            <i class="bi bi-journal-check me-2 text-primary"></i>
            Riwayat Aktivitas Scan QR Absensi Panitia
            <span style="background:var(--color-primary-light);color:var(--color-primary);
                         font-size:11px;font-weight:600;padding:2px 8px;border-radius:20px;
                         margin-left:8px;">
                {{ $logs->total() }} Log
            </span>
        </div>

        <form method="GET" action="{{ route('admin.logs') }}"
              class="d-flex align-items-center gap-2" style="flex-wrap:wrap;">
            <div class="search-input-wrap">
                <i class="bi bi-search"></i>
                <input type="text" name="search" class="search-input"
                       placeholder="Cari panitia / PIN / peserta / NIM..."
                       value="{{ request('search') }}">
            </div>

            <select name="status" class="search-input" style="width:auto;padding-left:10px;"
                    onchange="this.form.submit()">
                <option value="">Semua Status Scan</option>
                <option value="success"   {{ request('status') === 'success'   ? 'selected' : '' }}>Berhasil (Hadir)</option>
                <option value="already"   {{ request('status') === 'already'   ? 'selected' : '' }}>Sudah Hadir</option>
                <option value="not_found" {{ request('status') === 'not_found' ? 'selected' : '' }}>Tidak Ditemukan</option>
                <option value="error"     {{ request('status') === 'error'     ? 'selected' : '' }}>Error</option>
            </select>

            <button type="submit" class="btn-primary-sm">
                <i class="bi bi-search"></i> Cari
            </button>

            @if(request('search') || request('status'))
                <a href="{{ route('admin.logs') }}" class="btn-outline-sm">
                    <i class="bi bi-x"></i> Reset
                </a>
            @endif

            <form action="{{ route('admin.logs.clear') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus seluruh log scan absensi secara permanen?')">
                @csrf
                <button type="submit" class="btn-outline-sm text-danger border-danger" style="font-size:12px;">
                    <i class="bi bi-trash3"></i> Bersihkan Log
                </button>
            </form>
        </form>
    </div>

    @if($logs->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-journal-x"></i></div>
            <h6>Belum ada riwayat aktivitas scan</h6>
            <p>Setiap scan QR Code oleh panitia akan tercatat secara otomatis di sini.</p>
        </div>
    @else
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th>Waktu Scan</th>
                        <th>Operator Panitia</th>
                        <th>PIN Panitia</th>
                        <th>Peserta</th>
                        <th>Program Studi</th>
                        <th>Status</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $i => $log)
                    <tr>
                        <td style="color:var(--color-gray-400);font-size:12px;">
                            {{ $logs->firstItem() + $i }}
                        </td>

                        <td style="font-size:12px;font-weight:500;color:var(--color-gray-700);">
                            <i class="bi bi-clock me-1 text-muted"></i>
                            {{ $log->scanned_at ? $log->scanned_at->format('d/m/Y H:i:s') : '-' }}
                        </td>

                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:28px;height:28px;border-radius:50%;background:var(--color-primary-light);color:var(--color-primary);font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;">
                                    {{ strtoupper(substr($log->panitia_name ?? 'P', 0, 1)) }}
                                </div>
                                <span style="font-weight:600;font-size:13px;color:var(--color-gray-800);">
                                    {{ $log->panitia_name }}
                                </span>
                            </div>
                        </td>

                        <td>
                            <span class="badge" style="background:#F3F4F6;color:#374151;border:1px solid #E5E7EB;font-family:monospace;font-size:12px;padding:4px 8px;border-radius:6px;">
                                🔑 {{ $log->panitia_pin ?? '123456' }}
                            </span>
                        </td>

                        <td>
                            @if($log->peserta_nama)
                                <div style="font-weight:600;font-size:13px;">{{ $log->peserta_nama }}</div>
                                <div style="font-size:11px;color:var(--color-gray-500);">NIM: {{ $log->peserta_nim ?? '-' }}</div>
                            @else
                                <span style="font-size:12px;color:var(--color-gray-500);">NIM: {{ $log->peserta_nim ?? '-' }}</span>
                            @endif
                        </td>

                        <td style="font-size:12px;color:var(--color-gray-600);">
                            {{ $log->peserta_prodi ?? '-' }}
                        </td>

                        <td>
                            @if($log->status === 'success')
                                <span class="badge-status badge-hadir">
                                    <i class="bi bi-check-circle-fill me-1"></i> Berhasil (Hadir)
                                </span>
                            @elseif($log->status === 'already')
                                <span class="badge-status" style="background:#FEF3C7;color:#D97706;">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i> Sudah Hadir
                                </span>
                            @elseif($log->status === 'not_found')
                                <span class="badge-status badge-belum">
                                    <i class="bi bi-question-circle-fill me-1"></i> Tidak Ditemukan
                                </span>
                            @else
                                <span class="badge-status badge-belum">
                                    <i class="bi bi-x-circle-fill me-1"></i> Error
                                </span>
                            @endif
                        </td>

                        <td style="font-size:12px;color:var(--color-gray-500);">
                            {{ $log->message ?? '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="table-footer">
            <div class="table-footer-info">
                Menampilkan {{ $logs->firstItem() }}–{{ $logs->lastItem() }} dari {{ $logs->total() }} log
            </div>
            <div>
                {{ $logs->links() }}
            </div>
        </div>
    @endif

</div>

@endsection
