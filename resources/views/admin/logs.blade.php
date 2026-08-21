@extends('layouts.admin')

@section('title', 'Log Scan Panitia')
@section('page-title', 'Log Aktivitas Scan Panitia')
@section('breadcrumb', 'Log Scan Panitia')

@section('content')

{{-- ── Data Card ────────────────────────────────────────────── --}}
<div class="data-card">

    <div class="data-card-header">
        <div class="data-card-title">
            <i class="bi bi-journal-check me-2 text-primary"></i>
            Riwayat Aktivitas Scan Panitia
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
