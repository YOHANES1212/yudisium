@extends('layouts.admin')

@section('title', 'Scan Absensi')
@section('page-title', 'Scan Absensi')
@section('breadcrumb', 'Scan Absensi')

@section('content')

<div class="row g-3">

    {{-- ── Panel Scanner ──────────────────────────────────────── --}}
    <div class="col-12 col-lg-7">

        {{-- Status bar --}}
        <div style="display:flex;align-items:center;justify-content:space-between;
                    background:#fff;border:1px solid var(--color-gray-200);
                    border-radius:12px;padding:12px 18px;margin-bottom:12px;flex-wrap:wrap;gap:8px;">
            <div style="display:flex;align-items:center;gap:8px;">
                <span id="statusDot" style="width:10px;height:10px;border-radius:50%;
                      background:#10B981;display:inline-block;animation:pulse 1.5s infinite;"></span>
                <span id="statusText" style="font-size:13px;font-weight:600;color:#065F46;">
                    Siap Scan
                </span>
            </div>
            <div style="font-size:12px;color:var(--color-gray-600);display:flex;align-items:center;gap:10px;">
                <span style="background:var(--color-primary-light);color:var(--color-primary);font-weight:600;padding:3px 10px;border-radius:20px;">
                    <i class="bi bi-person-badge me-1"></i> {{ Auth::user()->name ?? 'Panitia' }} (🔑 {{ Auth::user()->pin ?? '123456' }})
                </span>
                <span>
                    <i class="bi bi-people-fill me-1 text-primary"></i>
                    <span id="totalHadir">0</span> hadir sesi ini
                </span>
            </div>
        </div>

        <div class="data-card">
            <div style="padding:20px;">

                {{-- Tab toggle: Kamera vs Scanner Fisik --}}
                <div style="display:flex;gap:0;margin-bottom:20px;
                            background:var(--color-gray-100);border-radius:10px;padding:4px;">
                    <button id="tabKamera" onclick="switchTab('kamera')"
                            style="flex:1;padding:8px;border:none;border-radius:8px;
                                   font-size:13px;font-weight:600;cursor:pointer;
                                   background:var(--color-primary);color:#fff;
                                   font-family:'Inter',sans-serif;transition:all 0.2s;">
                        <i class="bi bi-camera-video me-1"></i> Kamera
                    </button>
                    <button id="tabFisik" onclick="switchTab('fisik')"
                            style="flex:1;padding:8px;border:none;border-radius:8px;
                                   font-size:13px;font-weight:600;cursor:pointer;
                                   background:transparent;color:var(--color-gray-500);
                                   font-family:'Inter',sans-serif;transition:all 0.2s;">
                        <i class="bi bi-upc-scan me-1"></i> Scanner Fisik
                    </button>
                </div>

                {{-- ── TAB KAMERA ────────────────────────────── --}}
                <div id="panelKamera">

                    {{-- Pilih kamera (depan/belakang untuk HP) --}}
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;flex-wrap:wrap;">
                        <select id="cameraSelect" class="search-input"
                                style="flex:1;min-width:180px;padding-left:10px;font-size:13px;"
                                onchange="gantikamera()">
                            <option value="">Memuat kamera...</option>
                        </select>
                        <button id="btnKamera" onclick="toggleKamera()"
                                class="btn-primary-sm" style="white-space:nowrap;">
                            <i class="bi bi-camera-video" id="btnKameraIcon"></i>
                            <span id="btnKameraText">Nyalakan Kamera</span>
                        </button>
                    </div>

                    {{-- Viewfinder --}}
                    <div style="position:relative;background:#000;border-radius:14px;
                                overflow:hidden;aspect-ratio:4/3;max-height:320px;">
                        <video id="videoEl" autoplay playsinline muted
                               style="width:100%;height:100%;object-fit:cover;display:block;"></video>
                        <canvas id="canvasEl" style="display:none;"></canvas>

                        {{-- Overlay garis scan --}}
                        <div id="scanOverlay" style="display:none;position:absolute;inset:0;
                             pointer-events:none;">
                            <div style="position:absolute;top:20%;left:15%;width:70%;height:60%;
                                        border:2px solid rgba(79,70,229,0.8);border-radius:8px;"></div>
                            <div id="scanLine" style="position:absolute;left:15%;width:70%;
                                 height:2px;background:linear-gradient(90deg,transparent,#4F46E5,transparent);
                                 animation:scanAnim 2s linear infinite;top:20%;"></div>
                            <div style="position:absolute;bottom:12px;left:0;right:0;
                                        text-align:center;color:rgba(255,255,255,0.8);
                                        font-size:12px;font-weight:500;">
                                Arahkan QR Code ke dalam kotak
                            </div>
                        </div>

                        {{-- Placeholder saat kamera mati --}}
                        <div id="cameraPlaceholder"
                             style="position:absolute;inset:0;display:flex;flex-direction:column;
                                    align-items:center;justify-content:center;color:rgba(255,255,255,0.5);">
                            <i class="bi bi-camera-video-off" style="font-size:48px;margin-bottom:12px;"></i>
                            <div style="font-size:13px;">Kamera belum aktif</div>
                            <div style="font-size:11px;opacity:0.6;margin-top:4px;">
                                Klik "Nyalakan Kamera" di atas
                            </div>
                        </div>
                    </div>

                    {{-- Status kamera --}}
                    <div id="cameraStatus" style="font-size:12px;color:var(--color-gray-400);
                         margin-top:8px;text-align:center;min-height:18px;"></div>

                    {{-- Tips --}}
                    <div style="background:#F0FDF4;border-radius:10px;padding:12px 14px;
                                margin-top:12px;font-size:12px;color:#166534;line-height:1.7;">
                        <strong>💡 Cara scan yang benar:</strong><br>
                        • Dekatkan QR Code HP ke kamera (jarak 10-25 cm)<br>
                        • Pastikan QR mengisi minimal <strong>setengah</strong> area kamera<br>
                        • Tahan HP diam beberapa detik
                    </div>
                </div>

                {{-- ── TAB SCANNER FISIK ─────────────────────── --}}
                <div id="panelFisik" style="display:none;">
                    <input type="text" id="nimInput" autocomplete="off"
                           style="position:fixed;opacity:0;pointer-events:none;
                                  width:1px;height:1px;top:-9999px;">

                    <div id="scanArea" onclick="refocus()"
                         style="border:2px dashed var(--color-primary);border-radius:14px;
                                padding:40px 20px;text-align:center;cursor:pointer;
                                background:var(--color-primary-light);margin-bottom:0;
                                transition:all 0.2s;">
                        <i class="bi bi-qr-code-scan"
                           style="color:var(--color-primary);font-size:56px;display:block;margin-bottom:10px;"></i>
                        <div style="font-size:15px;font-weight:600;color:var(--color-primary);margin-bottom:4px;">
                            Hubungkan Scanner Barcode/QR
                        </div>
                        <div style="font-size:12px;color:var(--color-gray-500);">
                            Tap area ini lalu scan — otomatis terproses
                        </div>
                    </div>
                </div>

                {{-- ── Result Box ────────────────────────────── --}}
                <div id="resultCard" style="display:none;margin-top:16px;">
                    <div id="resultBox" style="border-radius:14px;padding:20px 22px;
                                               display:flex;align-items:center;gap:16px;">
                        <div id="resultIcon" style="font-size:44px;line-height:1;flex-shrink:0;"></div>
                        <div style="flex:1;min-width:0;">
                            <div id="resultNama"
                                 style="font-size:18px;font-weight:700;line-height:1.2;
                                        margin-bottom:4px;overflow:hidden;text-overflow:ellipsis;
                                        white-space:nowrap;"></div>
                            <div id="resultDetail"
                                 style="font-size:13px;opacity:0.85;margin-bottom:4px;"></div>
                            <div id="resultKursi" style="font-size:13px;font-weight:700;margin-bottom:6px;display:none;"></div>
                            <div id="resultMsg" style="font-size:13px;font-weight:500;"></div>
                        </div>
                    </div>
                </div>

                {{-- ── Input Manual ──────────────────────────── --}}
                <div style="display:flex;align-items:center;gap:10px;margin-top:16px;">
                    <div style="flex:1;height:1px;background:var(--color-gray-200);"></div>
                    <span style="font-size:11px;color:var(--color-gray-400);white-space:nowrap;">
                        atau input manual
                    </span>
                    <div style="flex:1;height:1px;background:var(--color-gray-200);"></div>
                </div>

                <form id="manualForm" style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap;">
                    @csrf
                    <div class="search-input-wrap" style="flex:1;min-width:200px;">
                        <i class="bi bi-keyboard"></i>
                        <input type="text" id="manualInput" class="search-input"
                               style="width:100%;padding-left:32px;"
                               placeholder="Ketik ID Unik / NIM lalu Enter...">
                    </div>
                    <button type="submit" class="btn-primary-sm" style="padding:8px 16px;">
                        <i class="bi bi-check-lg"></i> Absen
                    </button>
                </form>

            </div>
        </div>
    </div>

    {{-- ── Riwayat Scan ────────────────────────────────────────── --}}
    <div class="col-12 col-lg-5">
        <div class="data-card">
            <div class="data-card-header">
                <div class="data-card-title" style="font-size:14px;">
                    <i class="bi bi-clock-history me-2 text-primary"></i>
                    Riwayat Sesi Ini
                    <span id="historyCount"
                          style="background:var(--color-primary-light);color:var(--color-primary);
                                 font-size:11px;font-weight:600;padding:2px 8px;
                                 border-radius:20px;margin-left:6px;">0</span>
                </div>
                <button class="btn-outline-sm" onclick="clearHistory()" style="font-size:12px;">
                    <i class="bi bi-trash3"></i> Bersihkan
                </button>
            </div>
            <div id="scanHistory" style="max-height:520px;overflow-y:auto;">
                <div class="empty-state" id="emptyHistory" style="padding:40px 20px;">
                    <div class="empty-state-icon"><i class="bi bi-qr-code"></i></div>
                    <h6>Belum ada scan</h6>
                    <p>Scan pertama akan muncul di sini.</p>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
@keyframes pulse {
    0%,100%{opacity:1;transform:scale(1);}
    50%{opacity:0.4;transform:scale(1.4);}
}
@keyframes slideIn {
    from{opacity:0;transform:translateY(-6px);}
    to{opacity:1;transform:translateY(0);}
}
@keyframes scanAnim {
    0%  { top: 20%; }
    50% { top: 75%; }
    100%{ top: 20%; }
}
</style>

@endsection

@push('scripts')
<script>
(function() {
    var s = document.createElement('script');
    s.src = '{{ asset("js/jsqr.js") }}';
    s.onload = function() { console.log('jsQR loaded from local'); };
    s.onerror = function() {
        var s2 = document.createElement('script');
        s2.src = 'https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js';
        document.head.appendChild(s2);
    };
    document.head.appendChild(s);
})();
</script>

<script>
let isProcessing  = false;
let cameraStream  = null;
let scanLoop      = null;
let currentTab    = 'kamera';
let resultTimer;
let debounceTimer;
let scanHistory   = [];
let cameras       = [];
let lastCode      = '';
let lastTime      = 0;

const videoEl      = document.getElementById('videoEl');
const canvasEl     = document.getElementById('canvasEl');
const scanOverlay  = document.getElementById('scanOverlay');
const camPlaceholder = document.getElementById('cameraPlaceholder');
const cameraStatus = document.getElementById('cameraStatus');
const cameraSelect = document.getElementById('cameraSelect');
const nimInput     = document.getElementById('nimInput');
const manualInput  = document.getElementById('manualInput');
const manualForm   = document.getElementById('manualForm');
const resultCard   = document.getElementById('resultCard');
const resultBox    = document.getElementById('resultBox');
const resultIcon   = document.getElementById('resultIcon');
const resultNama   = document.getElementById('resultNama');
const resultDetail = document.getElementById('resultDetail');
const resultKursi  = document.getElementById('resultKursi');
const resultMsg    = document.getElementById('resultMsg');
const historyEl    = document.getElementById('scanHistory');
const emptyHist    = document.getElementById('emptyHistory');
const histCount    = document.getElementById('historyCount');
const totalHadir   = document.getElementById('totalHadir');
const statusDot    = document.getElementById('statusDot');
const statusText   = document.getElementById('statusText');

const STYLES = {
    success  : {bg:'#D1FAE5',color:'#065F46',icon:'✅',dot:'#10B981',label:'Hadir'},
    already  : {bg:'#FEF3C7',color:'#92400E',icon:'⚠️',dot:'#F59E0B',label:'Sudah Hadir'},
    not_found: {bg:'#FEE2E2',color:'#991B1B',icon:'❌',dot:'#EF4444',label:'Tidak Ada'},
    error    : {bg:'#FEE2E2',color:'#991B1B',icon:'❌',dot:'#EF4444',label:'Error'},
};

function switchTab(tab) {
    currentTab = tab;
    document.getElementById('panelKamera').style.display = tab === 'kamera' ? 'block' : 'none';
    document.getElementById('panelFisik').style.display  = tab === 'fisik'  ? 'block' : 'none';
    document.getElementById('tabKamera').style.background = tab === 'kamera' ? 'var(--color-primary)' : 'transparent';
    document.getElementById('tabKamera').style.color = tab === 'kamera' ? '#fff' : 'var(--color-gray-500)';
    document.getElementById('tabFisik').style.background = tab === 'fisik' ? 'var(--color-primary)' : 'transparent';
    document.getElementById('tabFisik').style.color = tab === 'fisik' ? '#fff' : 'var(--color-gray-500)';
    if (tab === 'fisik') { stopKamera(); setTimeout(refocus, 200); }
}

async function loadCameras() {
    try {
        if (typeof jsQR === 'undefined') {
            cameraStatus.textContent = '❌ Library QR gagal dimuat.';
            cameraStatus.style.color = '#EF4444';
            return;
        }

        const devices = await navigator.mediaDevices.enumerateDevices();
        cameras = devices.filter(d => d.kind === 'videoinput');
        cameraSelect.innerHTML = '';

        if (cameras.length === 0) {
            cameraSelect.innerHTML = '<option>Tidak ada kamera terdeteksi</option>';
            return;
        }

        cameras.forEach((cam, i) => {
            const opt = document.createElement('option');
            opt.value = cam.deviceId;
            opt.textContent = cam.label || ('Kamera ' + (i + 1));
            cameraSelect.appendChild(opt);
        });

        const back = cameras.find(c =>
            c.label.toLowerCase().includes('back') ||
            c.label.toLowerCase().includes('environment') ||
            c.label.toLowerCase().includes('belakang')
        );
        if (back) cameraSelect.value = back.deviceId;

        cameraStatus.textContent = cameras.length + ' kamera ditemukan';
    } catch(e) {
        cameraStatus.textContent = 'Klik "Nyalakan Kamera" untuk mulai.';
    }
}

async function toggleKamera() {
    if (cameraStream) { stopKamera(); } else { await startKamera(); }
}

async function startKamera() {
    const deviceId = cameraSelect.value;
    const constraints = {
        video: deviceId
            ? { deviceId: { exact: deviceId }, width: { ideal: 1280 }, height: { ideal: 720 } }
            : { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } }
    };
    try {
        cameraStatus.textContent = 'Menyalakan kamera...';
        cameraStatus.style.color = '#6B7280';

        cameraStream = await navigator.mediaDevices.getUserMedia(constraints);
        videoEl.srcObject = cameraStream;
        await videoEl.play();

        camPlaceholder.style.display = 'none';
        scanOverlay.style.display    = 'block';
        document.getElementById('btnKameraIcon').className = 'bi bi-camera-video-off';
        document.getElementById('btnKameraText').textContent = 'Matikan Kamera';
        cameraStatus.textContent = '🟢 Kamera aktif — arahkan QR Code ke kotak biru';
        cameraStatus.style.color = '#059669';

        const devices = await navigator.mediaDevices.enumerateDevices();
        const vids = devices.filter(d => d.kind === 'videoinput');
        if (vids.length > 0 && vids[0].label) {
            cameraSelect.innerHTML = '';
            vids.forEach((cam, i) => {
                const opt = document.createElement('option');
                opt.value = cam.deviceId;
                opt.textContent = cam.label || 'Kamera ' + (i+1);
                if (cam.deviceId === deviceId) opt.selected = true;
                cameraSelect.appendChild(opt);
            });
        }

        startScanLoop();
    } catch(e) {
        cameraStatus.textContent = e.name === 'NotAllowedError'
            ? '❌ Izin kamera ditolak. Klik ikon kunci di address bar → izinkan kamera.'
            : '❌ Gagal buka kamera: ' + e.message;
        cameraStatus.style.color = '#EF4444';
        cameraStream = null;
    }
}

function stopKamera() {
    if (scanLoop) { cancelAnimationFrame(scanLoop); scanLoop = null; }
    if (cameraStream) { cameraStream.getTracks().forEach(t => t.stop()); cameraStream = null; }
    videoEl.srcObject = null;
    camPlaceholder.style.display = 'flex';
    scanOverlay.style.display    = 'none';
    document.getElementById('btnKameraIcon').className = 'bi bi-camera-video';
    document.getElementById('btnKameraText').textContent = 'Nyalakan Kamera';
    cameraStatus.textContent = '';
}

function gantikamera() { if (cameraStream) { stopKamera(); startKamera(); } }

function startScanLoop() {
    const ctx = canvasEl.getContext('2d', { willReadFrequently: true });
    let frameCount = 0;

    function tick() {
        if (!cameraStream) return;
        frameCount++;

        if (videoEl.readyState >= 2 && videoEl.videoWidth > 0) {
            const vw = videoEl.videoWidth;
            const vh = videoEl.videoHeight;
            const targetW = Math.min(480, vw);
            const targetH = Math.round(targetW * (vh / vw));

            canvasEl.width = targetW;
            canvasEl.height = targetH;
            ctx.drawImage(videoEl, 0, 0, targetW, targetH);

            if (frameCount % 60 === 0) {
                cameraStatus.textContent = `🟢 Kamera aktif — Scanning kilat...`;
                cameraStatus.style.color = '#059669';
            }

            if (frameCount % 2 === 0 && !isProcessing) {
                const imgData = ctx.getImageData(0, 0, targetW, targetH);
                const code = jsQR(imgData.data, targetW, targetH, { inversionAttempts: 'dontInvert' })
                          || jsQR(imgData.data, targetW, targetH, { inversionAttempts: 'attemptBoth' });

                if (code && code.data) {
                    const result = code.data;
                    const now = Date.now();
                    if (result !== lastCode || now - lastTime > 2500) {
                        lastCode = result;
                        lastTime = now;
                        cameraStatus.textContent = `⚡ Terdeteksi! Memproses...`;
                        cameraStatus.style.color = '#059669';
                        processCode(result);
                    }
                }
            }
        }
        scanLoop = requestAnimationFrame(tick);
    }
    scanLoop = requestAnimationFrame(tick);
}

function refocus() {
    if (currentTab === 'fisik') nimInput.focus({ preventScroll: true });
}

document.addEventListener('visibilitychange', () => {
    if (!document.hidden && currentTab === 'fisik') refocus();
});

nimInput.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        const val = nimInput.value.trim();
        nimInput.value = '';
        if (val && !isProcessing) processCode(val);
    }, 250);
});

nimInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
        e.preventDefault();
        clearTimeout(debounceTimer);
        const val = nimInput.value.trim();
        nimInput.value = '';
        if (val && !isProcessing) processCode(val);
    }
});

manualForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const val = manualInput.value.trim();
    if (!val || isProcessing) return;
    manualInput.value = '';
    processCode(val);
});

async function processCode(code) {
    if (isProcessing) return;
    isProcessing = true;
    setStatus('loading');

    try {
        const res = await fetch('{{ route("admin.absensi.scan") }}', {
            method : 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
            body   : JSON.stringify({ nim: code })
        });
        const data = await res.json();
        showResult(data, code);
        addHistory(data, code);
        if (data.status === 'success') {
            totalHadir.textContent = parseInt(totalHadir.textContent || 0) + 1;
        }
    } catch(err) {
        showResult({ status:'error', message:'Gagal terhubung ke server.' }, code);
    }

    setStatus('ready');
    isProcessing = false;
}

function cleanCodeDisplay(str) {
    if (!str) return '';
    if (str.startsWith('http://') || str.startsWith('https://')) {
        try {
            const url = new URL(str);
            const id = url.searchParams.get('id');
            if (id) return id;
            return url.pathname.split('/').pop() || str;
        } catch(e) {
            return str;
        }
    }
    return str;
}

function showResult(data, code) {
    const s = STYLES[data.status] || STYLES.error;
    const p = data.peserta;
    const cleanCode = cleanCodeDisplay(code);
    const nama  = p ? (p['Nama Lengkap']  || p.nama  || cleanCode) : cleanCode;
    const nim   = p ? (p['NIM']           || p.nim   || cleanCode) : cleanCode;
    const prodi = p ? (p['Program Studi'] || p.prodi || '')        : '';
    const kursi = p ? (p['Nomor Kursi']    || p.nomor_kursi || '-') : '-';

    resultCard.style.display   = 'block';
    resultCard.style.animation = 'slideIn 0.25s ease';
    resultBox.style.cssText    = `border-radius:14px;padding:20px 22px;display:flex;
        align-items:center;gap:16px;background:${s.bg};color:${s.color};`;
    resultIcon.textContent  = s.icon;
    resultNama.textContent  = data.status === 'not_found' ? 'Peserta Tidak Ditemukan' : nama;
    resultDetail.textContent= data.status === 'not_found' ? 'ID / Kode: '+cleanCode : nim+(prodi?' · '+prodi:'');

    if (kursi !== '-' && data.status !== 'not_found') {
        resultKursi.style.display = 'block';
        resultKursi.innerHTML = `🪑 <strong>Kursi:</strong> Baris/Nomor <span style="background:rgba(0,0,0,0.08);padding:2px 8px;border-radius:6px;">${esc(kursi)}</span>`;
    } else {
        resultKursi.style.display = 'none';
    }

    resultMsg.textContent   = data.status === 'success' ? '✓ Kehadiran berhasil dicatat' : data.message;

    if      (data.status === 'success') beepSuccess();
    else if (data.status === 'already') beepAlready();
    else                                beepError();

    clearTimeout(resultTimer);
    resultTimer = setTimeout(() => { resultCard.style.display = 'none'; }, 5000);
}

function addHistory(data, code) {
    const now   = new Date().toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit',second:'2-digit'});
    const s     = STYLES[data.status] || STYLES.error;
    const p     = data.peserta;
    const cleanCode = cleanCodeDisplay(code);
    const nama  = p ? (p['Nama Lengkap']  || p.nama  || cleanCode) : cleanCode;
    const nim   = p ? (p['NIM']           || p.nim   || cleanCode) : cleanCode;
    const prodi = p ? (p['Program Studi'] || p.prodi || '')        : '';
    const kursi = p ? (p['Nomor Kursi']    || p.nomor_kursi || '-') : '-';

    scanHistory.unshift({ data, code, nama, time: now });
    histCount.textContent   = scanHistory.length;
    emptyHist.style.display = 'none';

    const iconClass = data.status === 'success' ? 'bi-check-circle-fill'
                    : data.status === 'already' ? 'bi-exclamation-circle-fill'
                    : 'bi-x-circle-fill';

    const div = document.createElement('div');
    div.className = 'history-item';
    div.style.cssText = `display:flex;align-items:center;gap:12px;padding:12px 16px;
        border-bottom:1px solid var(--color-gray-100);animation:slideIn 0.2s ease;`;
    div.innerHTML = `
        <i class="bi ${iconClass}" style="color:${s.dot};font-size:18px;flex-shrink:0;"></i>
        <div style="flex:1;min-width:0;">
            <div style="font-size:13px;font-weight:600;color:var(--color-gray-900);
                        white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${esc(nama)}</div>
            <div style="font-size:11px;color:var(--color-gray-500);">
                ${esc(nim)}${prodi?' · '+esc(prodi):''}${kursi !== '-' ? ' · 🪑 '+esc(kursi) : ''}</div>
        </div>
        <div style="text-align:right;flex-shrink:0;">
            <div style="font-size:11px;font-weight:600;color:${s.dot};">${s.label}</div>
            <div style="font-size:10px;color:var(--color-gray-400);">${now}</div>
        </div>`;

    const first = historyEl.querySelector('.history-item');
    first ? historyEl.insertBefore(div, first) : historyEl.appendChild(div);
}

// ── Load scan logs dari server ──────────────────────────────────
async function loadHistoryFromServer() {
    try {
        const res = await fetch('{{ route("admin.logs.recent") }}');
        const data = await res.json();
        if (data && data.length > 0) {
            emptyHist.style.display = 'none';
            historyEl.querySelectorAll('.history-item').forEach(e => e.remove());
            histCount.textContent = data.length;

            let hadirCount = 0;
            data.forEach(item => {
                if (item.status === 'success') hadirCount++;
                const time = item.scanned_at ? new Date(item.scanned_at).toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit',second:'2-digit'}) : '-';
                const s = STYLES[item.status] || STYLES.error;
                const iconClass = item.status === 'success' ? 'bi-check-circle-fill'
                                : item.status === 'already' ? 'bi-exclamation-circle-fill'
                                : 'bi-x-circle-fill';

                const div = document.createElement('div');
                div.className = 'history-item';
                div.style.cssText = `display:flex;align-items:center;gap:12px;padding:12px 16px;
                    border-bottom:1px solid var(--color-gray-100);`;
                div.innerHTML = `
                    <i class="bi ${iconClass}" style="color:${s.dot};font-size:18px;flex-shrink:0;"></i>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;font-weight:600;color:var(--color-gray-900);
                                    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${esc(item.peserta_nama || item.peserta_nim || 'Scan Code')}</div>
                        <div style="font-size:11px;color:var(--color-gray-500);">
                            ${esc(item.peserta_nim || '-')}${item.peserta_prodi ? ' · '+esc(item.peserta_prodi) : ''} · Panitia: ${esc(item.panitia_name || 'Panitia')}</div>
                    </div>
                    <div style="text-align:right;flex-shrink:0;">
                        <div style="font-size:11px;font-weight:600;color:${s.dot};">${s.label}</div>
                        <div style="font-size:10px;color:var(--color-gray-400);">${time}</div>
                    </div>`;
                historyEl.appendChild(div);
            });
            if (hadirCount > 0) totalHadir.textContent = hadirCount;
        }
    } catch(e) {}
}

async function clearHistory() {
    if (!confirm('Apakah Anda yakin ingin membersihkan seluruh riwayat scan absensi secara permanen?')) return;
    try {
        const res = await fetch('{{ route("admin.logs.clear") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        const data = await res.json();
        if (data.status === 'success') {
            scanHistory = [];
            historyEl.querySelectorAll('.history-item').forEach(e => e.remove());
            emptyHist.style.display = '';
            resultCard.style.display = 'none';
            histCount.textContent = '0';
            totalHadir.textContent = '0';
        } else {
            alert(data.message || 'Gagal membersihkan riwayat scan.');
        }
    } catch(e) {
        alert('Gagal terhubung ke server untuk membersihkan riwayat.');
    }
}

function setStatus(state) {
    if (state === 'loading') {
        statusDot.style.background = '#F59E0B'; statusDot.style.animation = 'none';
        statusText.textContent = 'Memproses...'; statusText.style.color = '#92400E';
    } else {
        statusDot.style.background = '#10B981'; statusDot.style.animation = 'pulse 1.5s infinite';
        statusText.textContent = 'Siap Scan'; statusText.style.color = '#065F46';
    }
}

function beep(freq,dur,type='sine',vol=0.3){
    try{const ctx=new(window.AudioContext||window.webkitAudioContext)();
    const o=ctx.createOscillator(),g=ctx.createGain();
    o.connect(g);g.connect(ctx.destination);
    o.type=type;o.frequency.value=freq;g.gain.value=vol;
    o.start();o.stop(ctx.currentTime+dur/1000);o.onended=()=>ctx.close();}catch(e){}
}
function beepSuccess(){beep(880,100);setTimeout(()=>beep(1100,160),110);}
function beepAlready(){beep(660,200);}
function beepError(){beep(200,350,'sawtooth',0.2);}

function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}

function waitForJsQR(cb, attempts) {
    attempts = attempts || 0;
    if (typeof jsQR === 'function') {
        cb();
    } else if (attempts < 20) {
        setTimeout(function() { waitForJsQR(cb, attempts + 1); }, 200);
    } else {
        cameraStatus.textContent = '❌ jsQR gagal dimuat. Coba refresh halaman.';
        cameraStatus.style.color = '#EF4444';
    }
}

waitForJsQR(function() {
    loadCameras();
    loadHistoryFromServer();
    cameraStatus.textContent = '✅ Library QR siap';
    setTimeout(function() { cameraStatus.textContent = ''; }, 2000);
});
window.addEventListener('beforeunload', stopKamera);
</script>
@endpush