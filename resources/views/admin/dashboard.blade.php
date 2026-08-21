@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')

{{-- ── Stat Cards ──────────────────────────────────────────── --}}
<div class="row g-3 mb-4">

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="bi bi-people-fill"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $totalPeserta }}</div>
                <div class="stat-label">Total Peserta Terdaftar</div>
                <div class="stat-change up"><i class="bi bi-arrow-up-short"></i> Dari Google Form</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon success"><i class="bi bi-check-circle-fill"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $totalHadir }}</div>
                <div class="stat-label">Sudah Hadir</div>
                <div class="stat-change up"><i class="bi bi-person-check"></i>
                    {{ $totalPeserta > 0 ? round(($totalHadir / $totalPeserta) * 100) : 0 }}% kehadiran
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon warning"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $totalBelumHadir }}</div>
                <div class="stat-label">Belum Hadir</div>
                <div class="stat-change down"><i class="bi bi-clock"></i> Menunggu kehadiran</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon danger"><i class="bi bi-credit-card-2-front-fill"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $totalValid }}</div>
                <div class="stat-label">Pembayaran Valid</div>
                <div class="stat-change up"><i class="bi bi-shield-check"></i> Terverifikasi</div>
            </div>
        </div>
    </div>

</div>

{{-- ── Progress Kehadiran ───────────────────────────────────── --}}
<div class="data-card mb-4">
    <div class="data-card-header">
        <div class="data-card-title"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Progress Kehadiran</div>
    </div>
    <div style="padding: 20px 24px;">
        @php $pct = $totalPeserta > 0 ? round(($totalHadir / $totalPeserta) * 100) : 0; @endphp
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span style="font-size:13px; color:var(--color-gray-600);">
                {{ $totalHadir }} dari {{ $totalPeserta }} peserta telah hadir
            </span>
            <span style="font-size:14px; font-weight:700; color:var(--color-primary);">{{ $pct }}%</span>
        </div>
        <div style="height:10px; background:var(--color-gray-200); border-radius:20px; overflow:hidden;">
            <div style="height:100%; width:{{ $pct }}%; background: linear-gradient(90deg, #4F46E5, #7C3AED); border-radius:20px; transition: width 0.5s ease;"></div>
        </div>
    </div>
</div>

{{-- ── Peserta Terbaru ──────────────────────────────────────── --}}
<div class="data-card">
    <div class="data-card-header">
        <div class="data-card-title"><i class="bi bi-clock-history me-2 text-primary"></i>Peserta Terbaru Mendaftar</div>
        <a href="{{ route('admin.peserta') }}" class="btn-outline-sm">
            Lihat Semua <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    @if(empty($recentPeserta))
        <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-inbox"></i></div>
            <h6>Belum ada peserta terdaftar</h6>
            <p>Peserta yang mengisi Google Form akan muncul di sini.</p>
        </div>
    @else
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Peserta</th>
                        <th>Program Studi</th>
                        <th>No. HP</th>
                        <th>Pembayaran</th>
                        <th>Status Hadir</th>
                        <th>Waktu Daftar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentPeserta as $p)
                    @php
                        $namaPeserta = $p['Nama Lengkap'] ?? $p['Nama Lengkap '] ?? $p['Nama'] ?? $p['nama'] ?? '-';
                    @endphp
                    <tr>
                        <td>
                            <div class="table-name-cell">
                                <div class="table-avatar">{{ strtoupper(substr($namaPeserta !== '-' ? $namaPeserta : 'X', 0, 1)) }}</div>
                                <div>
                                    <div class="table-name">{{ $namaPeserta }}</div>
                                    <div class="table-nim">{{ $p['NIM'] ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $p['Program Studi'] ?? '-' }}</td>
                        <td>{{ $p['No. Handphone (WA)'] ?? $p['No. Handphone (WA) '] ?? $p['No. HP (WA)'] ?? '-' }}</td>
                        <td>
                            @php $status = $p['Status Pembayaran'] ?? '-'; @endphp
                            @if($status === 'Valid')
                                <span class="badge-status badge-hadir">Valid</span>
                            @elseif(in_array($status, ['Pending', 'pending', '-', '']))
                                <span class="badge-status badge-belum">Pending</span>
                            @elseif(strlen($status) > 20)
                                {{-- Pesan error panjang dari Apps Script — tampilkan sebagai error kecil --}}
                                <span class="badge-status badge-belum"
                                      title="{{ $status }}"
                                      style="cursor:help;max-width:100px;overflow:hidden;
                                             text-overflow:ellipsis;white-space:nowrap;display:inline-block;">
                                    ⚠️ Error API
                                </span>
                            @else
                                <span class="badge-status badge-belum">{{ $status }}</span>
                            @endif
                        </td>
                        <td>
                            @if(!empty($p['Waktu Kehadiran']))
                                <span class="badge-status badge-hadir">Hadir</span>
                            @else
                                <span class="badge-status badge-belum">Belum Hadir</span>
                            @endif
                        </td>
                        <td style="color:var(--color-gray-500); font-size:12px;">{{ $p['Timestamp'] ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection
