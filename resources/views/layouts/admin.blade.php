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

        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
        }

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
            z-index: 1005;
            transition: transform 0.3s ease;
        }

        /* ── Responsive Sidebar & Mobile Layout ── */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
                box-shadow: 10px 0 30px rgba(0,0,0,0.5);
            }
            .main-wrapper {
                margin-left: 0 !important;
            }
            .topbar {
                padding: 0 16px;
            }
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

        /* ── Topbar Perfect Alignment ── */
        .topbar {
            position: sticky;
            top: 0;
            height: var(--topbar-height);
            background: #fff;
            border-bottom: 1px solid var(--color-gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            gap: 12px;
            z-index: 100;
            box-sizing: border-box;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            flex: 1;
        }

        .topbar-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--color-gray-900);
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .topbar-breadcrumb {
            font-size: 12px;
            color: var(--color-gray-500);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .topbar-breadcrumb a {
            color: var(--color-gray-500);
            text-decoration: none;
        }

        .topbar-breadcrumb a:hover { color: var(--color-primary); }

        .topbar-breadcrumb .separator { margin: 0 4px; }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
            margin-left: auto;
            height: 100%;
        }

        .topbar-actions form {
            margin: 0;
            padding: 0;
            display: inline-flex;
            align-items: center;
        }

        .topbar-btn, .topbar-avatar {
            height: 36px;
            margin: 0;
            padding: 0 12px;
            border: 1px solid var(--color-gray-200);
            background: #fff;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 500;
            color: var(--color-gray-700);
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
            text-decoration: none;
            box-sizing: border-box;
            line-height: 1;
            vertical-align: middle;
        }

        .topbar-btn.icon-only {
            width: 36px;
            min-width: 36px;
            padding: 0;
        }

        .topbar-btn:hover {
            background: var(--color-gray-50);
            border-color: var(--color-gray-400);
            color: var(--color-gray-900);
        }

        .topbar-badge {
            position: absolute;
            top: 5px; right: 5px;
            width: 8px; height: 8px;
            background: var(--color-danger);
            border-radius: 50%;
            border: 1.5px solid #fff;
        }

        .topbar-avatar {
            background: var(--color-primary-light);
            color: var(--color-primary);
            border-color: rgba(79, 70, 229, 0.2);
            font-weight: 600;
        }

        .topbar-avatar:hover {
            background: var(--color-primary);
            color: #fff;
        }

        /* ── Page content ── */
        .page-content {
            flex: 1;
            padding: 24px;
        }

        /* ── Stat cards ── */
        .stat-card {
            background: #fff;
            border: 1px solid var(--color-gray-200);
            border-radius: 12px;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.2s;
            text-decoration: none;
            color: inherit;
        }

        .stat-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); transform: translateY(-2px); }

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
        .stat-icon.info    { background: #E0F2FE; color: #0284C7; }

        .stat-info { flex: 1; min-width: 0; }

        .stat-value {
            font-size: 24px;
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
            margin-top: 4px;
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
            padding: 16px 20px;
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
            min-width: 140px;
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

        /* ── Responsive ── */
        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-wrapper { margin-left: 0; }
            .topbar { padding: 0 16px; }
            .page-content { padding: 16px; }
            .search-input { width: 100%; }
        }

        @media (max-width: 576px) {
            .search-input { width: 100%; }
            .data-card-header { flex-direction: column; align-items: flex-start; gap: 10px; }
            .topbar-title { font-size: 14px; }
            .topbar-breadcrumb { font-size: 11px; }
            .topbar-btn span { display: none; }
            .topbar-avatar span { display: none; }
        }

        /* ── Participant Mobile Cards & Action Buttons ── */
        .peserta-card-mobile {
            background: #ffffff;
            border: 1px solid var(--color-gray-200);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .peserta-card-mobile:active {
            transform: scale(0.995);
        }
        .peserta-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--color-gray-100);
            margin-bottom: 12px;
        }
        .peserta-avatar-mobile {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #EEF2FF 0%, #E0E7FF 100%);
            color: var(--color-primary);
            font-weight: 700;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .peserta-info-block {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            padding: 10px 12px;
            background: var(--color-gray-50);
            border-radius: 10px;
            margin-bottom: 12px;
            font-size: 12px;
        }
        .peserta-action-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }
        .btn-action-mobile {
            min-height: 42px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s ease;
            text-decoration: none;
            border: none;
            cursor: pointer;
            flex: 1;
            min-width: 100px;
        }
        .btn-action-mobile:active {
            transform: scale(0.97);
        }
        .btn-action-mobile-sm {
            min-height: 38px;
            padding: 6px 12px;
            font-size: 12px;
            flex: 0 0 auto;
        }
        .btn-valid-action {
            background: #10B981;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3);
        }
        .btn-valid-action:hover, .btn-valid-action:focus {
            background: #059669;
            color: #ffffff !important;
        }
        .btn-reject-action {
            background: #FEE2E2;
            color: #DC2626 !important;
            border: 1px solid #FCA5A5;
        }
        .btn-reject-action:hover {
            background: #FCA5A5;
            color: #991B1B !important;
        }
        .btn-cancel-valid-action {
            background: #FEF3C7;
            color: #D97706 !important;
            border: 1px solid #FDE68A;
        }
        .btn-cancel-valid-action:hover {
            background: #FDE68A;
            color: #B45309 !important;
        }
        .btn-hadir-action {
            background: #4F46E5;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.3);
        }
        .btn-hadir-action:hover {
            background: #4338CA;
            color: #ffffff !important;
        }
        .btn-batal-hadir-action {
            background: #F3F4F6;
            color: #EF4444 !important;
            border: 1px solid #E5E7EB;
        }
        .btn-batal-hadir-action:hover {
            background: #FEE2E2;
        }
        .btn-proof-action {
            background: #F3F4F6;
            color: #374151 !important;
            border: 1px solid #E5E7EB;
        }
        .btn-proof-action:hover {
            background: #E5E7EB;
            color: #111827 !important;
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
            <span class="sidebar-brand-subtitle">Admin & Panitia Panel</span>
        </div>
    </a>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <div class="nav-section-label">Menu Utama</div>

        <a href="{{ route('admin.dashboard') }}"
           onclick="closeSidebar()"
           class="nav-item-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill"></i>
            Dashboard
        </a>

        <a href="{{ route('admin.peserta') }}"
           onclick="closeSidebar()"
           class="nav-item-link {{ request()->routeIs('admin.peserta*') ? 'active' : '' }}">
            <i class="bi bi-people-fill"></i>
            Data Peserta
        </a>

        <a href="{{ route('admin.plotting') }}"
           onclick="closeSidebar()"
           class="nav-item-link {{ request()->routeIs('admin.plotting*') ? 'active' : '' }}">
            <i class="bi bi-layout-three-columns"></i>
            Plotting Kursi
        </a>

        <a href="{{ route('admin.absensi') }}"
           onclick="closeSidebar()"
           class="nav-item-link {{ request()->routeIs('admin.absensi*') ? 'active' : '' }}">
            <i class="bi bi-qr-code-scan"></i>
            Scan Absensi
        </a>

        <a href="{{ route('admin.logs') }}"
           onclick="closeSidebar()"
           class="nav-item-link {{ request()->routeIs('admin.logs*') ? 'active' : '' }}">
            <i class="bi bi-journal-text"></i>
            Log Scan Panitia
        </a>


        <div class="nav-section-label">Laporan</div>

        <a href="{{ route('admin.export') }}"
           class="nav-item-link {{ request()->routeIs('admin.export*') ? 'active' : '' }}"
           target="_blank" download onclick="notifyExport(event)">
            <i class="bi bi-file-earmark-spreadsheet"></i>
            Export Data
        </a>

    </nav>

    <!-- Footer -->
    <div class="sidebar-footer">
        <div style="display:flex;align-items:center;gap:10px;padding:10px 12px;margin-bottom:4px;">
            <div style="width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,0.15);
                        display:flex;align-items:center;justify-content:center;
                        font-size:14px;font-weight:700;color:#fff;flex-shrink:0;">
                {{ strtoupper(substr(Auth::user()->name ?? 'P', 0, 1)) }}
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:13px;font-weight:600;color:#fff;
                            white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ Auth::user()->name ?? 'Panitia' }}
                </div>
                <div style="font-size:11px;color:rgba(199,210,254,0.7);display:flex;align-items:center;gap:4px;">
                    <span style="background:rgba(255,255,255,0.2);padding:1px 5px;border-radius:6px;font-family:monospace;">🔑 {{ Auth::user()->pin ?? '123456' }}</span>
                    <span>• {{ ucfirst(Auth::user()->role ?? 'panitia') }}</span>
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
        <div class="topbar-left">
            <!-- Mobile toggle -->
            <button class="topbar-btn icon-only d-lg-none" onclick="toggleSidebar()" title="Buka Menu">
                <i class="bi bi-list fs-5"></i>
            </button>

            <div>
                <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
                <div class="topbar-breadcrumb">
                    <a href="{{ route('admin.dashboard') }}">Admin</a>
                    <span class="separator">/</span>
                    @yield('breadcrumb', 'Dashboard')
                </div>
            </div>
        </div>

        <div class="topbar-actions">
            {{-- Tombol Refresh Data --}}
            <form action="{{ route('admin.refresh') }}" method="POST">
                @csrf
                <button type="submit" class="topbar-btn" title="Refresh data dari Google Sheets">
                    <i class="bi bi-arrow-clockwise"></i>
                    <span class="d-none d-md-inline">Refresh</span>
                </button>
            </form>

            {{-- Tombol Notifikasi Modal --}}
            <button type="button" class="topbar-btn icon-only" title="Notifikasi Realtime" onclick="openNotifModal()">
                <i class="bi bi-bell fs-6"></i>
                <span class="topbar-badge"></span>
            </button>

            {{-- Profil Avatar --}}
            <div class="topbar-avatar" onclick="openProfileModal()" title="Profil Operator Panitia">
                <i class="bi bi-person-circle fs-6"></i>
                <span>{{ Auth::user()->name ?? 'Panitia' }}</span>
            </div>
        </div>
    </header>

    <!-- Page Content -->
    <main class="page-content">
        @yield('content')
    </main>

</div>

<!-- ═══ Modal Notifikasi Realtime ═════════════════════════════ -->
<div class="modal fade" id="notifModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="border-bottom:1px solid var(--color-gray-200);padding:16px 20px;">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:36px;height:36px;border-radius:50%;background:var(--color-primary-light);color:var(--color-primary);display:flex;align-items:center;justify-content:center;font-size:18px;">
                        <i class="bi bi-bell-fill"></i>
                    </div>
                    <div>
                        <h6 class="modal-title mb-0" style="font-weight:700;">Notifikasi & Aktivitas System</h6>
                        <small class="text-muted">Realtime status absensi panitia</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:20px;">
                <div style="background:var(--color-gray-50);border:1px solid var(--color-gray-200);border-radius:12px;padding:14px;margin-bottom:16px;">
                    <div style="font-size:12px;font-weight:600;color:var(--color-gray-500);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Status Operator Saat Ini</div>
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <div>
                            <div style="font-weight:700;font-size:14px;color:var(--color-gray-900);">{{ Auth::user()->name ?? 'Panitia' }}</div>
                            <div style="font-size:12px;color:var(--color-gray-500);">PIN Operator: <span style="font-family:monospace;font-weight:600;">{{ Auth::user()->pin ?? '123456' }}</span></div>
                        </div>
                        <span class="badge bg-success" style="font-weight:500;padding:6px 10px;border-radius:20px;">
                            🟢 Active
                        </span>
                    </div>
                </div>

                <div style="font-size:13px;font-weight:600;color:var(--color-gray-800);margin-bottom:10px;">
                    <i class="bi bi-clock-history me-1 text-primary"></i> 5 Scan Terakhir Oleh Panitia
                </div>

                <div id="notifRecentList" style="max-height:240px;overflow-y:auto;">
                    <div class="text-center py-3 text-muted" style="font-size:13px;">
                        <div class="spinner-border spinner-border-sm me-1" role="status"></div> Memuat aktivitas terbaru...
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--color-gray-200);padding:12px 20px;justify-content:space-between;">
                <a href="{{ route('admin.logs') }}" class="btn-outline-sm" style="font-size:12px;">
                    <i class="bi bi-journal-text me-1"></i> Lihat Semua Log
                </a>
                <button type="button" class="btn-primary-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- ═══ Modal Profil Operator Panitia ═════════════════════════ -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center" style="border-radius:20px;border:none;overflow:hidden;">
            <div style="background:linear-gradient(135deg, #4F46E5, #3730A3);padding:32px 20px 24px;color:#fff;">
                <div style="width:64px;height:64px;border-radius:50%;background:rgba(255,255,255,0.2);color:#fff;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:28px;font-weight:700;border:2px solid rgba(255,255,255,0.5);">
                    {{ strtoupper(substr(Auth::user()->name ?? 'P', 0, 1)) }}
                </div>
                <h6 style="font-weight:700;font-size:16px;margin-bottom:2px;">{{ Auth::user()->name ?? 'Panitia' }}</h6>
                <div style="font-size:12px;opacity:0.8;">{{ Auth::user()->email ?? '' }}</div>
            </div>
            <div class="modal-body" style="padding:20px;">
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="font-size:13px;">
                    <span class="text-muted">Role</span>
                    <span class="fw-semibold text-capitalize">{{ Auth::user()->role ?? 'panitia' }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="font-size:13px;">
                    <span class="text-muted">PIN Operator</span>
                    <span class="fw-bold font-monospace text-primary">🔑 {{ Auth::user()->pin ?? '123456' }}</span>
                </div>
            </div>
            <div class="modal-footer justify-content-center" style="border-top:none;padding:0 20px 20px;">
                <form method="POST" action="{{ route('logout') }}" class="w-100">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100" style="border-radius:10px;font-size:13px;font-weight:600;">
                        <i class="bi bi-box-arrow-left me-1"></i> Keluar / Ganti Akun
                    </button>
                </form>
            </div>
        </div>
    </div>
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

    function openProfileModal() {
        new bootstrap.Modal(document.getElementById('profileModal')).show();
    }

    function openNotifModal() {
        const modal = new bootstrap.Modal(document.getElementById('notifModal'));
        modal.show();
        loadNotifRecent();
    }

    async function loadNotifRecent() {
        const list = document.getElementById('notifRecentList');
        try {
            const res = await fetch('{{ route("admin.logs.recent") }}');
            const data = await res.json();

            if (!data || data.length === 0) {
                list.innerHTML = '<div class="text-center py-3 text-muted" style="font-size:13px;">Belum ada aktivitas scan terbaru</div>';
                return;
            }

            let html = '';
            data.slice(0, 5).forEach(item => {
                const badgeColor = item.status === 'success' ? '#10B981' : (item.status === 'already' ? '#F59E0B' : '#EF4444');
                const time = item.scanned_at ? new Date(item.scanned_at).toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'}) : '-';
                html += `
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px;border-bottom:1px solid #F3F4F6;">
                        <div style="display:flex;align-items:center;gap:10px;min-width:0;">
                            <span style="width:8px;height:8px;border-radius:50%;background:${badgeColor};flex-shrink:0;"></span>
                            <div style="min-width:0;">
                                <div style="font-weight:600;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${item.peserta_nama || item.peserta_nim || 'Scan Code'}</div>
                                <div style="font-size:11px;color:#6B7280;">Panitia: ${item.panitia_name} (${item.panitia_pin})</div>
                            </div>
                        </div>
                        <span style="font-size:11px;color:#9CA3AF;white-space:nowrap;">${time}</span>
                    </div>
                `;
            });
            list.innerHTML = html;
        } catch(e) {
            list.innerHTML = '<div class="text-center py-3 text-muted" style="font-size:13px;">Gagal memuat log terbaru</div>';
        }
    }

    function notifyExport(e) {
        const toast = document.createElement('div');
        toast.style.cssText = 'position:fixed;bottom:20px;right:20px;background:#10B981;color:#fff;padding:12px 20px;border-radius:10px;font-size:13px;font-weight:600;z-index:9999;box-shadow:0 10px 25px rgba(0,0,0,0.2);';
        toast.innerHTML = '<i class="bi bi-download me-2"></i> Mengunduh file CSV... Periksa folder Unduhan / Downloads HP atau Komputer Anda.';
        document.body.appendChild(toast);
        setTimeout(() => { toast.remove(); }, 5000);
    }
</script>

@stack('scripts')
</body>
</html>
