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
    <title>Cancer Center Queuing System</title>
    <link rel="stylesheet" href="<?= base_url("assets/bootstrap5/css/bootstrap.min.css"); ?>">
    <style>
        body {
            background: linear-gradient(135deg, #f0f9ff, #fdf2f8);
            font-family: 'Segoe UI', sans-serif;
        }

        h2, h4, h5 {
            font-weight: 700;
            color: #1e293b;
        }

        .room-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 35px 25px;
            margin-bottom: 25px;
            text-align: center;
            border: 3px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .room-card.active {
            border-color: #22c55e;
        }

        .room-card.hold {
            border-color: #f59e0b;
        }

        .room-card.empty {
            border-color: #e2e8f0;
            opacity: 0.7;
        }

        .room-card.updated {
            animation: pulse 0.6s ease;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.04); }
            100% { transform: scale(1); }
        }

        .room-number {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #64748b;
            margin-bottom: 15px;
        }

        .ticket-display {
            font-size: 2rem;
            font-weight: 800;
            color: #0ea5e9;
        }

        .ticket-display.hold {
            color: #f59e0b;
        }

        .ticket-display.empty {
            font-size: 1.8rem;
            color: #94a3b8;
        }

        .sidebar {
            background: #ffffff;
            border-radius: 18px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.06);
            position: sticky;
            top: 20px;
        }

        .up-next-box {
            background: linear-gradient(135deg, #0ea5e9, #2563eb);
            color: white;
            border-radius: 18px;
            padding: 40px 25px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(37,99,235,0.4);
        }

        .up-next-number {
            font-size: 4.5rem;
            font-weight: 900;
        }

        .up-next-room {
            background: rgba(255,255,255,0.2);
            padding: 8px 20px;
            border-radius: 30px;
            margin-top: 15px;
            display: inline-block;
            font-weight: 600;
        }

        .list-group-item {
            border: none;
            border-radius: 12px;
            margin-bottom: 10px;
            background: #f8fafc;
            transition: 0.2s ease;
        }

        .list-group-item:hover {
            background: #e0f2fe;
        }

        .sound-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .sound-on {
            background: #22c55e;
            color: white;
        }
        .sound-off {
            background: #dc2626;
            color: white;
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
            
    </style>
</head>
<body>

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

    <!-- Sound Toggle Button -->
    <button class="btn sound-toggle sound-on" id="soundToggle" title="Toggle Sound">
        🔊
    </button>

    <!-- Test Sound Button (for debugging) -->
    <button class="btn btn-primary" id="testSound" style="position: fixed; bottom: 90px; right: 20px; z-index: 1000; border-radius: 50%; width: 60px; height: 60px; font-size: 1.2rem;" title="Test Sound">
        🔈
    </button>

    <div class="container-fluid px-4 mt-5">
        <div class="row">
            
            <!-- LEFT: Rooms -->
            <div class="col-md-8">
                <h2 class="text-center mb-3">CURRENT SERVING</h2>
                <div class="row" id="rooms-container">


                        <?php for($i = 1; $i <= 5; $i++): 
                            $ticket = $rooms[$i] ?? null;
                            $statusClass = 'empty';

                            if ($ticket) {
                                if ($ticket['status'] === 'hold') {
                                    $statusClass = 'hold';
                                } else {
                                    $statusClass = 'active';
                                }
                            }
                        ?>
                        <div class="col-md-4 col-sm-6" id="room-<?php echo $i; ?>">
                            <div class="room-card <?php echo $statusClass; ?>">
                                <div class="room-number">
                                    <?php echo $roomNames[$i] ?? 'Room '.$i; ?>
                                </div>
                                
                                <div class="ticket-display <?php echo $statusClass; ?>" id="room-ticket-<?php echo $i; ?>">
                                    <?php if($ticket): ?>
                                        <?php echo $ticket['ticket_number']; ?>
                                        <?php if($ticket['status'] === 'hold'): ?>
                                            <small class="d-block text-warning" id="room-hold-<?php echo $i; ?>">ON HOLD</small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="ticket-display empty">Empty</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- RIGHT: Waiting only -->
            <div class="col-md-4">
                <div class="sidebar">
                    <h4 class="text-center mb-3">WAITING</h4>
                    <h5>Waiting (<span id="waiting-count"><?php echo $waiting_count; ?></span>)</h5>
                    <ul class="list-group" style="max-height: 400px; overflow-y: auto;" id="waiting-list">
                        <?php if(!empty($waiting_list)): ?>
                            <?php
                            // ensure consultations are shown first in the initial render
                            usort($waiting_list, function($a, $b) {
                                if ($a['type'] === $b['type']) return 0;
                                if ($a['type'] === 'consultation') return -1;
                                if ($b['type'] === 'consultation') return 1;
                                return 0;
                            });
                            ?>
                            
                            <?php foreach($waiting_list as $wait): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center waiting-item">
                                    <div>
                                        <strong>#<?php echo $wait['ticket_number']; ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?php if($wait['room_id']): ?>
                                                <?= $roomNames[$wait['room_id']] ?? 'Room '.$wait['room_id']; ?>
                                            <?php else: ?>
                                                General Queue
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-<?php echo $wait['type'] === 'chemo' ? 'info' : 'success'; ?>">
                                        <?php echo strtoupper($wait['type']); ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-muted" id="no-waiting">No one waiting</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="<?= base_url('assets/bootstrap5/js/bootstrap.bundle.min.js') ?>"></script>

    <!-- AJAX Update Script with Sound -->
 <script>
/* =========================================================
   QUEUE DISPLAY + SOUND SYSTEM (REFINED VERSION)
========================================================= */

// =======================
// STATE
// =======================
let soundEnabled = true;
let lastRoomTickets = {};
let isFetching = false;
let isSpeaking = false;

const pendingSpeeches = [];

// =======================
// AUDIO SETUP
// =======================
const doorbellSrc = '<?= base_url("sounds/Doorbell.mp3") ?>';
const baseDoorbell = new Audio(doorbellSrc);
baseDoorbell.preload = 'auto';

// =======================
// INITIALIZE ROOMS
// =======================
<?php for($i = 1; $i <= 5; $i++): ?>
    <?php if(isset($rooms[$i])): ?>
        lastRoomTickets[<?= $i ?>] = '<?= $rooms[$i]['ticket_number']; ?>';
    <?php else: ?>
        lastRoomTickets[<?= $i ?>] = null;
    <?php endif; ?>
<?php endfor; ?>


// =======================
// SOUND CONTROLS
// =======================
const soundToggleBtn = document.getElementById('soundToggle');
const testSoundBtn   = document.getElementById('testSound');

if (soundToggleBtn) {
    soundToggleBtn.addEventListener('click', () => {
        unlockAudio();
        soundEnabled = !soundEnabled;

        soundToggleBtn.textContent = soundEnabled ? '🔊' : '🔇';
        soundToggleBtn.className   = soundEnabled
            ? 'btn sound-toggle sound-on'
            : 'btn sound-toggle sound-off';

        if (soundEnabled) flushSpeechQueue();
    });
}

if (testSoundBtn) {
    testSoundBtn.addEventListener('click', () => {
        unlockAudio();
        speak("Ticket number CHEMO 001. Please proceed to room 1");
    });
}

function unlockAudio() {
    try {
        baseDoorbell.play().then(() => {
            baseDoorbell.pause();
            baseDoorbell.currentTime = 0;
        }).catch(() => {});
    } catch (e) {}
}

// =======================
// SPEECH SYSTEM
// =======================
function speak(text) {
    if (!soundEnabled) return;

    pendingSpeeches.push(text);
    processSpeechQueue();
}

function processSpeechQueue() {
    if (isSpeaking || pendingSpeeches.length === 0) return;
    if (!soundEnabled) return;

    isSpeaking = true;
    const text = pendingSpeeches.shift();

    playDoorbell()
        .then(() => speakText(text))
        .then(() => {
            isSpeaking = false;
            processSpeechQueue();
        })
        .catch(err => {
            console.warn("Speech blocked:", err);
            showEnableSoundBanner();
            isSpeaking = false;
        });
}

function speakText(text, repeat = 2) {
    return new Promise((resolve, reject) => {
        try {
            let count = 0;

            function speakOnce() {
                const utter = new SpeechSynthesisUtterance(text);
                utter.rate = 0.9;
                utter.pitch = 1;
                utter.volume = 1;

                utter.onend = () => {
                    count++;
                    if (count < repeat) {
                        setTimeout(speakOnce, 700);
                    } else {
                        resolve();
                    }
                };

                utter.onerror = (e) => {
                    reject(e);
                };

                window.speechSynthesis.speak(utter);
            }

            window.speechSynthesis.cancel();
            speakOnce();

        } catch (e) {
            reject(e);
        }
    });
}

function playDoorbell() {
    return new Promise((resolve, reject) => {
        if (!soundEnabled) return resolve();

        try {
            const bell = baseDoorbell.cloneNode(true);
            const playPromise = bell.play();

            bell.onended = () => resolve();

            if (playPromise !== undefined) {
                playPromise.catch(reject);
            }
        } catch (e) {
            reject(e);
        }
    });
}

function flushSpeechQueue() {
    processSpeechQueue();
}

// =======================
// AUTOPLAY WARNING BANNER
// =======================
function showEnableSoundBanner() {
    if (document.getElementById("enableSoundBanner")) return;

    const banner = document.createElement("div");
    banner.id = "enableSoundBanner";
    banner.style.cssText = `
        position:fixed;
        bottom:90px;
        left:20px;
        z-index:2000;
        background:#ffc107;
        padding:10px 14px;
        border-radius:8px;
        font-weight:600;
        box-shadow:0 6px 18px rgba(0,0,0,0.15);
    `;

    banner.innerHTML = `
        Sound blocked by browser.
        <button id="enableSoundBtn"
            style="margin-left:8px;padding:6px 10px;border-radius:6px;border:0;background:#198754;color:#fff;">
            Enable
        </button>
    `;

    document.body.appendChild(banner);

    document.getElementById("enableSoundBtn").addEventListener("click", () => {
        unlockAudio();
        flushSpeechQueue();
        banner.remove();
    });
}

// =======================
// HELPER
// =======================
function getSpeechText(ticket, roomId) {
    let text = "Ticket number " + ticket.replace(/-/g, " ");
    if (roomId) {
        const roomName = roomNames[roomId] ?? ("Room " + roomId);
        text += ". Please proceed to " + roomName;
    }
    return text;
}

// =======================
// UPDATE DISPLAY
// =======================
const roomNames = {
    1: "Triage",
    2: "Room 2",
    3: "Room 3",
    4: "Room 4",
    5: "Room 5"
};
function updateDisplay() {
    if (isFetching) return;
    isFetching = true;

    fetch('<?= base_url("queue/api/display"); ?>')
        .then(res => res.json())
        .then(data => {

            // collect all room changes so we can announce them sequentially
            // when multiple rooms are updated at the same time.
            const announcements = [];

            // ---- (up next removed for display) ----
            // no special handling here; only room updates and waiting list are refreshed

            // ---- ROOMS ----
            for (let i = 1; i <= 5; i++) {
                const room = data.rooms[i];
                const ticketEl = document.getElementById('room-ticket-' + i);
                const cardEl   = document.getElementById('room-' + i);

                if (!ticketEl || !cardEl) continue;

                const oldTicket = lastRoomTickets[i];

                if (room) {
                    const newTicket = room.ticket_number;
                    lastRoomTickets[i] = newTicket;

                    ticketEl.innerHTML = newTicket;
                    ticketEl.className = 'ticket-display active';

                    // update card border class based on status
                    if (cardEl) {
                        cardEl.querySelector('.room-card')?.classList.remove('empty','hold','active');
                        if (room.status === 'hold') {
                            cardEl.querySelector('.room-card')?.classList.add('hold');
                        } else {
                            cardEl.querySelector('.room-card')?.classList.add('active');
                        }
                    }

                    if (oldTicket !== newTicket && newTicket) {
                        // queue each room change individually so that if 2+ rooms
                        // are updated simultaneously, the doorbell and announcements
                        // play one after another in room order.
                        announcements.push(getSpeechText(newTicket, i));
                        cardEl.querySelector('.room-card')?.classList.add('updated');
                        setTimeout(() => {
                            cardEl.querySelector('.room-card')?.classList.remove('updated');
                        }, 600);
                    }
                } else {
                    lastRoomTickets[i] = null;
                    ticketEl.innerHTML = "Empty";
                    ticketEl.className = "ticket-display empty";
                    if (cardEl) {
                        cardEl.querySelector('.room-card')?.classList.remove('active','hold');
                        cardEl.querySelector('.room-card')?.classList.add('empty');
                    }
                }
            }

            // ---- SPEAK ----
            // enqueue all collected announcements; the speech queue ensures
            // they play one at a time with the doorbell before each one.
            if (announcements.length) {
                announcements.forEach(text => speak(text));
            }

            // ---- WAITING COUNT & LIST ----
            const waitingCount = document.getElementById("waiting-count");
            if (waitingCount) {
                waitingCount.textContent = data.waiting_count ?? 0;
            }

            const waitingListEl = document.getElementById('waiting-list');
            if (waitingListEl) {
                if (data.waiting_list && data.waiting_list.length > 0) {
                    // sort consultations to the front
                    data.waiting_list.sort((a, b) => {
                        if (a.type === b.type) return 0;
                        if (a.type === 'consultation') return -1;
                        if (b.type === 'consultation') return 1;
                        return 0;
                    });
                    let html = '';
                    data.waiting_list.forEach(wait => {
                        const typeClass = wait.type === 'chemo' ? 'info' : 'success';
                        const roomText = wait.room_id 
                        ? (roomNames[wait.room_id] ?? ('Room ' + wait.room_id)) 
                        : 'General Queue';
                        html += `
                            <li class="list-group-item d-flex justify-content-between align-items-center waiting-item">
                                <div>
                                    <strong>#${wait.ticket_number}</strong><br>
                                    <small class="text-muted">${roomText}</small>
                                </div>
                                <span class="badge bg-${typeClass}">${wait.type.toUpperCase()}</span>
                            </li>
                        `;
                    });
                    waitingListEl.innerHTML = html;
                } else {
                    waitingListEl.innerHTML = '<li class="list-group-item text-muted" id="no-waiting">No one waiting</li>';
                }
            }

        })
        .catch(err => console.error("Fetch error:", err))
        .finally(() => {
            isFetching = false;
        });
}

// =======================
// AUTO REFRESH
// =======================
setInterval(updateDisplay, 3000);
updateDisplay();

</script>
</body>
</html>