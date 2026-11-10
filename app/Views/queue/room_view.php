<!DOCTYPE html>
<html lang="en">
    <?php
$roomNames = [
    1 => 'Triage',
    2 => 'Room 2',
    3 => 'Room 3',
    4 => 'Room 4',
    5 => 'Room 5'
];
?>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc($roomNames[$roomId] ?? 'Room '.$roomId) ?> - Now Serving</title>
<link rel="stylesheet" href="<?= base_url("assets/bootstrap5/css/bootstrap.min.css"); ?>">

<style>
body {
    background: linear-gradient(135deg, #f0f9ff, #fdf2f8);
    font-family: 'Segoe UI', sans-serif;
}

.sidebar {
    background: #ffffff;
    border-radius: 18px;
    padding: 40px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.06);
}

.up-next-box {
    background: linear-gradient(135deg, #0ea5e9, #2563eb);
    color: white;
    border-radius: 18px;
    padding: 60px 25px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(37,99,235,0.4);
    transition: all 0.4s ease;
}

.up-next-type {
    font-size: 2.2rem;
    font-weight: 800;
    letter-spacing: 2px;
    text-transform: uppercase;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.up-next-number {
    font-size: 4.5rem;
    font-weight: 900;
    letter-spacing: 3px;
    line-height: 1;
}

/* Ensure long type names don't overflow the container on small screens */
.up-next-box .up-next-type,
.up-next-box .up-next-number {
    display: block;
    width: 100%;
}

@media (max-width: 576px) {
    .up-next-type { font-size: 1.6rem; }
    .up-next-number { font-size: 3rem; }
}

.list-group-item {
    border: none;
    border-radius: 12px;
    margin-bottom: 10px;
    background: #f8fafc;
}

/* ===== HEADER IMPROVED ===== */
            .header-wrapper {
                width: 100%;
                display: flex;
                justify-content: center;
                margin-top: 20px;
                margin-bottom: 30px;
            }

            .header-content {
                display: flex;
                align-items: center;
                gap: 20px;
                background: white;
                padding: 15px 30px;
                border-radius: 60px;
                box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            }

            .logo-container {
                width: 75px;
                height: 75px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .logo-container img {
                max-width: 100%;
                max-height: 100%;
                object-fit: contain;
            }

            .system-text {
                text-align: left;
            }

            .hospital-name {
                font-size: 0.9rem;
                font-weight: 600;
                color: #64748b;
                letter-spacing: 1px;
            }

            .system-name {
                font-size: 1.4rem;
                font-weight: 800;
                color: #1e293b;
            }
            /* ===== MIDDLE DIVIDER ===== */
        .divider-middle {
            border-left: 2px solid #e2e8f0;
        }

        @media (max-width: 991px) {
            .divider-middle {
                border-left: none;
                border-top: 2px solid #e2e8f0;
                margin-top: 20px;
                padding-top: 20px;
            }
        }
</style>
</head>
<body>

<div class="container mt-5" style="max-width:900px;">

        <!-- Logo Header -->
        <div class="header-wrapper">
            <div class="header-content">
                <div class="logo-container">
                    <img src="<?= base_url('assets/img/cvmc_logo.png'); ?>" 
                        alt="Hospital Logo">
                </div>
                <div class="system-text">
                    <div class="hospital-name">Cagayan Valley Medical Center</div>
                    <div class="system-name">Cancer Center Queuing System</div>
                </div>
            </div>
        </div>


    <div class="sidebar">

        <h2 class="text-center mb-4">
    NOW SERVING - <?= esc($roomNames[$roomId] ?? 'Room '.$roomId) ?>
</h2>

<div class="row">

    <!-- LEFT: NOW SERVING -->
    <div class="col-lg-8 mb-4 mb-lg-0">
        <div id="up-next-box">
            <div class="up-next-box">
                <div class="up-next-number">--</div>
                <p class="mt-3">Waiting for next patient</p>
            </div>
        </div>
    </div>

    <!-- RIGHT: WAITING LIST -->
    <div class="col-lg-4 divider-middle">

        <div class="ps-lg-3">

            <h5 class="mb-3">
                Waiting (<span id="waiting-count">0</span>)
            </h5>

            <ul class="list-group"
                id="waiting-list"
                style="max-height: 400px; overflow-y: auto;">
            </ul>

        </div>

    </div>

</div>

<script src="<?= base_url('assets/bootstrap5/js/bootstrap.bundle.min.js') ?>"></script>



<script>
const ROOM_ID = <?= (int)$roomId ?>;
let lastUpNextId = null;
let speakingNow = false;

const pendingSpeeches = [];

const doorbell = new Audio('<?= base_url("sounds/Doorbell.mp3") ?>');
doorbell.preload = 'auto';

/* ================================
   AUTOPLAY UNLOCK
================================ */
function unlockAudio(){
    try{
        doorbell.play().then(()=>{
            doorbell.pause();
            doorbell.currentTime = 0;
        }).catch(()=>{});
    }catch(e){}
}

/* ================================
   ENABLE SOUND BANNER
================================ */
function showEnableSoundBanner(){

    if(document.getElementById('enableSoundBanner')) return;

    const banner = document.createElement('div');
    banner.id = 'enableSoundBanner';
    banner.style.position = 'fixed';
    banner.style.bottom = '90px';
    banner.style.left = '20px';
    banner.style.zIndex = '2000';

    banner.innerHTML = `
        <div style="
            background:#ffc107;
            color:#000;
            padding:10px 14px;
            border-radius:8px;
            box-shadow:0 6px 18px rgba(0,0,0,0.12);
            font-weight:600;">
            Sound blocked by browser.
            <button id="enableSoundBtn"
                style="
                    margin-left:8px;
                    padding:6px 10px;
                    border-radius:6px;
                    border:0;
                    background:#198754;
                    color:#fff;">
                Enable
            </button>
        </div>
    `;

    document.body.appendChild(banner);

    document.getElementById('enableSoundBtn').addEventListener('click', ()=>{
        unlockAudio();
        flushPendingSpeeches();
        banner.remove();
    });
}

/* ================================
   SPEECH SYSTEM (2x)
================================ */
function speak(text){

    if(speakingNow) return;

    speakingNow = true;

    doorbell.play().then(()=>{

        let repeatCount = 0;
        const maxRepeats = 2;

        function speakOnce(){

            const utterance = new SpeechSynthesisUtterance(text);
            utterance.rate = 0.9;

            utterance.onend = ()=>{
                repeatCount++;

                if(repeatCount < maxRepeats){
                    setTimeout(speakOnce, 800);
                }else{
                    speakingNow = false;
                }
            };

            utterance.onerror = (e)=>{
                pendingSpeeches.push(text);
                showEnableSoundBanner();
                speakingNow = false;
            };

            window.speechSynthesis.cancel();
            window.speechSynthesis.speak(utterance);
        }

        speakOnce();

    }).catch(()=>{
        pendingSpeeches.push(text);
        showEnableSoundBanner();
        speakingNow = false;
    });
}

function flushPendingSpeeches(){
    while(pendingSpeeches.length > 0){
        const t = pendingSpeeches.shift();
        speak(t);
    }
}

function getSpeechText(ticket){

    return "Ticket number " + ticket.replace(/-/g,' ') +
           ". Kindly proceed to OPHTHA .";
}

/* ================================
   UPDATE DISPLAY
================================ */
function updateDisplay(){

    fetch('<?= base_url('queue/api/display/') ?>' + ROOM_ID)
    .then(res => res.json())
    .then(data => {

        const box = document.getElementById('up-next-box');

        /* ---------- NOW SERVING ---------- */

            if(data.up_next){

            if(lastUpNextId !== data.up_next.id){
                lastUpNextId = data.up_next.id;
                speak(getSpeechText(data.up_next.ticket_number));
            }

            // Extract numeric suffix from ticket_number (e.g. CONSULTATION-003 -> 003)
            let numberText = data.up_next.ticket_number || '';
            let numericPart = numberText;
            try {
                const parts = numberText.split('-');
                if (parts.length > 1) numericPart = parts[parts.length - 1];
            } catch (e) {
                numericPart = numberText;
            }

            const typeLabel = (data.up_next.type || '').toUpperCase();

            box.innerHTML = `
                <div class="up-next-box">
                    <div class="up-next-type">${typeLabel}</div>
                    <div class="up-next-number">${numericPart}</div>
                    <span class="badge bg-light text-dark fs-6 mt-3">${typeLabel}</span>
                </div>
            `;

        } else {

            lastUpNextId = null;

            box.innerHTML = `
                <div class="up-next-box">
                    <div class="up-next-number">--</div>
                    <p class="mt-3">Waiting for next patient</p>
                </div>
            `;
        }

        /* ---------- WAITING LIST ---------- */

        document.getElementById('waiting-count').textContent =
            data.waiting_count ?? 0;

        const waitingList = document.getElementById('waiting-list');

        if(data.waiting_list && data.waiting_list.length > 0){

            let html = '';

            data.waiting_list.forEach(wait => {

                const badgeColor =
                    wait.type === 'chemo' ? 'info' :
                    wait.type === 'consultation' ? 'success' :
                    'secondary';

                html += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <strong>#${wait.ticket_number}</strong>
                    </li>
                `;
            });

            waitingList.innerHTML = html;

        } else {

            waitingList.innerHTML =
                '<li class="list-group-item text-muted text-center">No one waiting</li>';
        }

    })
    .catch(err => {
        console.log("API error:", err);
    });
}

/* Refresh every 3 seconds */
setInterval(updateDisplay, 3000);
updateDisplay();
</script>

</body>
</html>