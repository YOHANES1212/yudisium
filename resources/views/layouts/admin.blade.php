<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') — Absensi Yudisium</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- CSRF Token (used by JS fetch) -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php use Illuminate\Support\Facades\Auth; @endphp
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --topbar-height: 64px;
            --color-primary: #4F46E5;
            --color-primary-hover: #4338CA;
            --color-primary-light: #EEF2FF;
            --color-success: #10B981;
            --color-warning: #F59E0B;
            --color-danger: #EF4444;
            --color-gray-50: #F9FAFB;
            --color-gray-100: #F3F4F6;
            --color-gray-200: #E5E7EB;
            --color-gray-400: #9CA3AF;
            --color-gray-500: #6B7280;
            --color-gray-600: #4B5563;
            --color-gray-700: #374151;
            --color-gray-900: #111827;
            --color-sidebar-bg: #1E1B4B;
            --color-sidebar-text: #C7D2FE;
            --color-sidebar-active: #4F46E5;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--color-gray-100);
            color: var(--color-gray-900);
            overflow-x: hidden;
        }

        /* ── Sidebar ── */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--color-sidebar-bg);
            display: flex;
            flex-direction: column;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            padding: 0 20px;
            height: var(--topbar-height);
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            text-decoration: none;
        }

        .sidebar-brand-icon {
            width: 36px; height: 36px;
            background: var(--color-primary);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; color: white;
            flex-shrink: 0;
        }

        .sidebar-brand-text {
            display: flex; flex-direction: column;
        }

        .sidebar-brand-name {
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            line-height: 1.2;
        }

        .sidebar-brand-subtitle {
            font-size: 11px;
            color: var(--color-sidebar-text);
            opacity: 0.7;
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 16px 12px;
        }

        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 4px; }

        .nav-section-label {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: rgba(199, 210, 254, 0.4);
            padding: 8px 12px 4px;
            margin-top: 8px;
        }

        .nav-item-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            color: var(--color-sidebar-text);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
            margin-bottom: 2px;
        }

        .nav-item-link i {
            font-size: 16px;
            width: 20px;
            text-align: center;
            opacity: 0.8;
        }

        .nav-item-link:hover {
            background: rgba(255,255,255,0.08);
            color: #fff;
        }

        .nav-item-link:hover i { opacity: 1; }

        .nav-item-link.active {
            background: var(--color-primary);
            color: #fff;
        }

        .nav-item-link.active i { opacity: 1; }

        .nav-badge {
            margin-left: auto;
            background: rgba(255,255,255,0.15);
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 20px;
        }

        .nav-item-link.active .nav-badge {
            background: rgba(255,255,255,0.25);
        }

        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }

        /* ── Main layout ── */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Topbar ── */
        .topbar {
            position: sticky;
            top: 0;
            height: var(--topbar-height);
            background: #fff;
            border-bottom: 1px solid var(--color-gray-200);
            display: flex;
            align-items: center;
            padding: 0 24px;
            gap: 16px;
            z-index: 100;
        }

        .topbar-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--color-gray-900);
            flex: 1;
        }

        .topbar-breadcrumb {
            font-size: 13px;
            color: var(--color-gray-500);
        }

        .topbar-breadcrumb a {
            color: var(--color-gray-500);
            text-decoration: none;
        }

        .topbar-breadcrumb a:hover { color: var(--color-primary); }

        .topbar-breadcrumb .separator { margin: 0 6px; }

        .topbar-actions { display: flex; align-items: center; gap: 10px; }

        .topbar-btn {
            width: 36px; height: 36px;
            border: 1px solid var(--color-gray-200);
            background: #fff;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            color: var(--color-gray-600);
            font-size: 16px;
            transition: all 0.2s;
            position: relative;
        }

        .topbar-btn:hover { background: var(--color-gray-50); border-color: var(--color-gray-400); }

        .topbar-badge {
            position: absolute;
            top: 4px; right: 4px;
            width: 8px; height: 8px;
            background: var(--color-danger);
            border-radius: 50%;
            border: 1px solid #fff;
        }

        .topbar-avatar {
            width: 36px; height: 36px;
            background: var(--color-primary-light);
            color: var(--color-primary);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        /* ── Page content ── */
        .page-content {
            flex: 1;
            padding: 28px 24px;
        }

        /* ── Stat cards ── */
        .stat-card {
            background: #fff;
            border: 1px solid var(--color-gray-200);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: box-shadow 0.2s;
        }

        .stat-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }

        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .stat-icon.primary { background: var(--color-primary-light); color: var(--color-primary); }
        .stat-icon.success { background: #D1FAE5; color: var(--color-success); }
        .stat-icon.warning { background: #FEF3C7; color: var(--color-warning); }
        .stat-icon.danger  { background: #FEE2E2; color: var(--color-danger); }

        .stat-info { flex: 1; min-width: 0; }

        .stat-value {
            font-size: 26px;
            font-weight: 700;
            color: var(--color-gray-900);
            line-height: 1.2;
        }

        .stat-label {
            font-size: 13px;
            color: var(--color-gray-500);
            margin-top: 2px;
        }

        .stat-change {
            font-size: 12px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 3px;
            margin-top: 6px;
        }

        .stat-change.up   { color: var(--color-success); }
        .stat-change.down { color: var(--color-danger); }

        /* ── Table card ── */
        .data-card {
            background: #fff;
            border: 1px solid var(--color-gray-200);
            border-radius: 12px;
            overflow: hidden;
        }

        .data-card-header {
            padding: 18px 20px;
            border-bottom: 1px solid var(--color-gray-200);
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .data-card-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--color-gray-900);
            flex: 1;
            min-width: 0;
        }

        .search-input-wrap {
            position: relative;
        }

        .search-input-wrap i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--color-gray-400);
            font-size: 14px;
        }

        .search-input {
            border: 1px solid var(--color-gray-200);
            border-radius: 8px;
            padding: 7px 12px 7px 32px;
            font-size: 13px;
            outline: none;
            width: 220px;
            font-family: 'Inter', sans-serif;
            color: var(--color-gray-700);
            background: var(--color-gray-50);
            transition: all 0.2s;
        }

        .search-input:focus {
            border-color: var(--color-primary);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .btn-primary-sm {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--color-primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .btn-primary-sm:hover { background: var(--color-primary-hover); color: #fff; }

        .btn-outline-sm {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fff;
            color: var(--color-gray-700);
            border: 1px solid var(--color-gray-200);
            border-radius: 8px;
            padding: 7px 14px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .btn-outline-sm:hover {
            border-color: var(--color-gray-400);
            background: var(--color-gray-50);
            color: var(--color-gray-900);
        }

        /* ── Data Table ── */
        .data-table { width: 100%; border-collapse: collapse; }

        .data-table th {
            padding: 11px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            color: var(--color-gray-500);
            background: var(--color-gray-50);
            border-bottom: 1px solid var(--color-gray-200);
            white-space: nowrap;
        }

        .data-table td {
            padding: 13px 16px;
            font-size: 13px;
            color: var(--color-gray-700);
            border-bottom: 1px solid var(--color-gray-100);
            vertical-align: middle;
        }

        .data-table tr:last-child td { border-bottom: none; }

        .data-table tbody tr { transition: background 0.15s; }

        .data-table tbody tr:hover { background: var(--color-gray-50); }

        /* ── Avatar in table ── */
        .table-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: var(--color-primary-light);
            color: var(--color-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            flex-shrink: 0;
        }

        .table-name-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table-name { font-weight: 500; color: var(--color-gray-900); }
        .table-nim  { font-size: 12px; color: var(--color-gray-500); margin-top: 1px; }

        /* ── Status badge ── */
        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-status::before {
            content: '';
            width: 6px; height: 6px;
            border-radius: 50%;
            display: inline-block;
        }

        .badge-hadir  { background: #D1FAE5; color: #065F46; }
        .badge-hadir::before  { background: var(--color-success); }
        .badge-belum  { background: #F3F4F6; color: var(--color-gray-600); }
        .badge-belum::before  { background: var(--color-gray-400); }

        /* ── Row actions ── */
        .action-btn {
            width: 28px; height: 28px;
            border-radius: 6px;
            border: 1px solid var(--color-gray-200);
            background: #fff;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--color-gray-500);
            font-size: 13px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .action-btn:hover { background: var(--color-primary-light); border-color: var(--color-primary); color: var(--color-primary); }
        .action-btn.danger:hover { background: #FEE2E2; border-color: var(--color-danger); color: var(--color-danger); }

        /* ── Pagination ── */
        .table-footer {
            padding: 14px 20px;
            border-top: 1px solid var(--color-gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .table-footer-info {
            font-size: 13px;
            color: var(--color-gray-500);
        }

        .pagination-wrap { display: flex; align-items: center; gap: 4px; }

        .page-btn {
            min-width: 32px; height: 32px;
            border-radius: 6px;
            border: 1px solid var(--color-gray-200);
            background: #fff;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            color: var(--color-gray-700);
            transition: all 0.2s;
            padding: 0 8px;
            font-family: 'Inter', sans-serif;
            text-decoration: none;
        }

        .page-btn:hover { background: var(--color-gray-50); color: var(--color-gray-900); }
        .page-btn.active { background: var(--color-primary); border-color: var(--color-primary); color: #fff; }
        .page-btn:disabled, .page-btn.disabled { opacity: 0.4; pointer-events: none; }

        /* ── Empty state ── */
        .empty-state {
            text-align: center;
            padding: 56px 24px;
            color: var(--color-gray-500);
        }

        .empty-state-icon {
            font-size: 40px;
            color: var(--color-gray-300);
            margin-bottom: 12px;
        }

        .empty-state h6 { color: var(--color-gray-600); font-size: 15px; margin-bottom: 6px; }
        .empty-state p  { font-size: 13px; margin: 0; }

        /* ── QR Modal ── */
        .qr-img { max-width: 200px; margin: 0 auto; display: block; border-radius: 8px; }

        /* ── Responsive ── */
        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-wrapper { margin-left: 0; }
            .page-content { padding: 20px 16px; }
        }

        @media (max-width: 576px) {
            .search-input { width: 100%; }
            .data-card-header { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>

<!-- Sidebar Overlay (mobile) -->
<div class="sidebar-overlay d-none" id="sidebarOverlay"
     style="position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:999;"
     onclick="closeSidebar()"></div>

<!-- ═══ Sidebar ════════════════════════════════════════════════ -->
<aside class="sidebar" id="sidebar">

    <!-- Brand -->
    <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
        <div class="sidebar-brand-icon"><i class="bi bi-mortarboard-fill"></i></div>
        <div class="sidebar-brand-text">
            <span class="sidebar-brand-name">Yudisium</span>
            <span class="sidebar-brand-subtitle">Admin Panel</span>
        </div>
    </a>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <div class="nav-section-label">Menu Utama</div>

        <a href="{{ route('admin.dashboard') }}"
           class="nav-item-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill"></i>
            Dashboard
        </a>

        <a href="{{ route('admin.peserta') }}"
           class="nav-item-link {{ request()->routeIs('admin.peserta*') ? 'active' : '' }}">
            <i class="bi bi-people-fill"></i>
            Data Peserta
        </a>

        <a href="{{ route('admin.absensi') }}"
           class="nav-item-link {{ request()->routeIs('admin.absensi*') ? 'active' : '' }}">
            <i class="bi bi-qr-code-scan"></i>
            Scan Absensi
        </a>

        <a href="{{ route('admin.logs') }}"
           class="nav-item-link {{ request()->routeIs('admin.logs*') ? 'active' : '' }}">
            <i class="bi bi-journal-text"></i>
            Log Scan Panitia
        </a>

        <div class="nav-section-label">Laporan</div>

        <a href="{{ route('admin.export') }}"
           class="nav-item-link {{ request()->routeIs('admin.export*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-spreadsheet"></i>
            Export Data
        </a>

    </nav>

    <!-- Footer -->
    <div class="sidebar-footer">
        <div style="display:flex;align-items:center;gap:10px;padding:10px 12px;margin-bottom:4px;">
            <div style="width:32px;height:32px;border-radius:50%;background:rgba(255,255,255,0.15);
                        display:flex;align-items:center;justify-content:center;
                        font-size:13px;font-weight:600;color:#fff;flex-shrink:0;">
                {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:13px;font-weight:600;color:#fff;
                            white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ Auth::user()->name ?? 'Panitia' }}
                    <span style="font-size:10px;background:rgba(255,255,255,0.2);padding:1px 5px;border-radius:8px;margin-left:2px;font-family:monospace;">🔑 {{ Auth::user()->pin ?? '123456' }}</span>
                </div>
                <div style="font-size:11px;color:rgba(199,210,254,0.6);
                            white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ Auth::user()->email ?? '' }}
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nav-item-link w-100" style="margin-bottom:0;
                    background:none;border:none;cursor:pointer;color:rgba(199,210,254,0.7);
                    text-align:left;">
                <i class="bi bi-box-arrow-left"></i>
                Keluar
            </button>
        </form>
    </div>

</aside>

<!-- ═══ Main Wrapper ═══════════════════════════════════════════ -->
<div class="main-wrapper">

    <!-- Topbar -->
    <header class="topbar">
        <!-- Mobile toggle -->
        <button class="topbar-btn d-lg-none" onclick="toggleSidebar()" style="border:none; margin-right:4px;">
            <i class="bi bi-list"></i>
        </button>

        <div>
            <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
            <div class="topbar-breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Admin</a>
                <span class="separator">/</span>
                @yield('breadcrumb', 'Dashboard')
            </div>
        </div>

        <div class="topbar-actions">
            {{-- Tombol Refresh Data dari SheetDB --}}
            <form action="{{ route('admin.refresh') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="topbar-btn" title="Refresh data dari Google Sheets"
                        style="width:auto;padding:0 12px;gap:6px;font-size:13px;font-weight:500;">
                    <i class="bi bi-arrow-clockwise"></i>
                    <span class="d-none d-md-inline">Refresh</span>
                </button>
            </form>
            <div class="topbar-btn" title="Notifikasi">
                <i class="bi bi-bell"></i>
                <span class="topbar-badge"></span>
            </div>
            <div class="topbar-avatar" title="Admin">A</div>
        </div>
    </header>

    <!-- Page Content -->
    <main class="page-content">
        @yield('content')
    </main>

</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function toggleSidebar() {
        const sidebar  = document.getElementById('sidebar');
        const overlay  = document.getElementById('sidebarOverlay');
        sidebar.classList.toggle('open');
        overlay.classList.toggle('d-none');
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.add('d-none');
    }
</script>

@stack('scripts')
</body>
</html>
