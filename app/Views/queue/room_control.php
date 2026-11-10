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

// Rooms to disable
$disabledRooms = [2, 4];
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Control</title>
    <link rel="stylesheet" href="<?= base_url("assets/bootstrap5/css/bootstrap.min.css"); ?>">
    <style>
        body { background-color: #f8f9fa; }
        
    .main-content {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .status-card { 
        padding: 40px; 
        text-align: center; 
        border-radius: 15px; 
        margin-bottom: 20px; 
    }
    .serving { background-color: #d1e7dd; border: 3px solid #0f5132; }
    .hold { background-color: #fff3cd; border: 3px solid #664d03; }
    .empty { background-color: #e2e3e5; border: 3px solid #6c757d; }
    
    .big-number { font-size: 6rem; font-weight: bold; }
    
    .action-btn { 
        width: 120px; 
        height: 120px; 
        border-radius: 50%; 
        font-size: 1.3rem; 
        font-weight: bold; 
        margin: 10px; 
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        border: none;
    }
    .btn-next { background-color: #0d6efd; color: white; text-decoration: none; }
    .btn-next:disabled { background-color: #6c757d; color: white; opacity: 0.7; cursor: not-allowed; }
    
    .btn-hold { background-color: #ffc107; color: black; text-decoration: none; }
    .btn-hold:disabled { background-color: #ccc; color: #666; opacity: 0.7; cursor: not-allowed; }
    
    .btn-skip { background-color: #dc3545; color: white; text-decoration: none; }
    .btn-skip:disabled { background-color: #aeb5bb; color: #fff; opacity: 0.7; cursor: not-allowed; }
    
    .btn-resume { background-color: #198754; color: white; text-decoration: none; }
    .btn-resume:disabled { background-color: #6c757d; color: white; opacity: 0.7; cursor: not-allowed; }
    
    /* REPEAT BUTTON STYLE */
    .btn-repeat { 
        background-color: #6f42c1; /* Purple */
        color: white; 
        text-decoration: none; 
    }
    .btn-repeat:disabled { 
        background-color: #aeb5bb; 
        color: #fff; 
        opacity: 0.7; 
        cursor: not-allowed; 
    }
    
    .btn-calloff { 
        background-color: #6c757d; 
        color: white; 
        text-decoration: none;
        padding: 10px 25px;
        border-radius: 25px;
        font-size: 1rem;
        font-weight: bold;
    }
    .btn-calloff:hover {
        background-color: #495057;
    }
    
    .sidebar {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .ticket-item {
        background: white;
        padding: 10px 15px;
        border-radius: 8px;
        margin-bottom: 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .ticket-num { font-weight: bold; font-size: 1.2rem; }
    
    /* Flash message animation */
    .alert {
        animation: slideDown 0.5s ease-out;
    }
    @keyframes slideDown {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    /* Sound Toggle Button */
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
    .sound-on { background: #22c55e; color: white; }
    .sound-off { background: #dc2626; color: white; }

    /* ===== HEADER IMPROVED ===== */
            .header-wrapper {
                width: 100%;
                display: flex;
                margin-top: 20px;
                margin-bottom: 30px;
            }

            .header-content {
                display: flex;
                align-items: center;
                gap: 20px;
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
                color: #ffffff;
                letter-spacing: 1px;
            }

            .system-name {
                font-size: 1.4rem;
                font-weight: 800;
                color: #ffffff;
            }
            /* Limit waiting list height to about 5 tickets */
            .waiting-scroll {
                max-height: 340px;   /* approx height of 5 items */
                overflow-y: auto;
                padding-right: 5px;
            }

            /* Optional: nicer scrollbar */
            .waiting-scroll::-webkit-scrollbar {
                width: 6px;
            }
            .waiting-scroll::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 10px;
            }

            /* Limit skip list height (approx 5 items) */
            .skip-scroll {
                max-height: 340px;
                overflow-y: auto;
                padding-right: 5px;
            }
            .skip-scroll::-webkit-scrollbar {
                width: 6px;
            }
            .skip-scroll::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 10px;
            }
</style>
</head>
<body>

<!-- Sound Toggle Button -->
<button class="btn sound-toggle sound-on" id="soundToggle" title="Toggle Sound">
    🔊
</button>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3">
    <div class="container-fluid">

        <!-- LEFT SIDE (Logo + Title) -->
        <a class="navbar-brand d-flex align-items-center gap-3" href="#">
            <img src="<?= base_url('assets/img/cvmc_logo.png'); ?>" 
                 alt="Hospital Logo"
                 style="width:60px; height:60px; object-fit:contain;">

            <div class="text-white">
                <div style="font-size:0.8rem; font-weight:600; letter-spacing:1px;">
                    Cagayan Valley Medical Center
                </div>
                <div style="font-size:1.3rem; font-weight:800;">
                    Cancer Center Queuing System
                </div>
            </div>
        </a>

        <!-- TOGGLER -->
        <button class="navbar-toggler" type="button" 
                data-bs-toggle="collapse" 
                data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- RIGHT SIDE MENU -->
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">

            <ul class="navbar-nav align-items-lg-center me-3">
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('queue'); ?>">Patient View</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" 
                    target="_blank"
                    href="<?= base_url('queue/view/'.$room_id); ?>">
                        Room View
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('queue/kiosk'); ?>">Generate Number</a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Rooms</a>
                    <ul class="dropdown-menu">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <?php if(in_array($i, $disabledRooms)) continue; ?>
                            <li>
                                <a class="dropdown-item" href="<?php echo base_url('queue/call_room/'.$i); ?>">
                                    <?php echo $roomNames[$i] ?? 'Room '.$i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </li>
            </ul>

            <!-- CALL OFF BUTTON -->
            <a href="<?= base_url('queue/calloff'); ?>" 
               class="btn btn-secondary rounded-pill px-4 fw-bold"
               onclick="return confirm('Are you sure you want to call off all waiting tickets?')">
                Call Off
            </a>

        </div>
    </div>
</nav>

<div class="container-fluid p-4">
    <div class="row">
        
        <!-- LEFT: Main Controls -->
        <div class="col-lg-8">
            <div class="main-content">
                 <?php for($i = 1; $i <= 5; $i++): 
                    if ($i !== $room_id) continue;
                    ?>
                    
                <h2 class="mb-4"> <?php echo $roomNames[$i] ?? 'Room '.$i; ?> Control</h2>
                <?php endfor; ?>
                <!-- Flash Messages -->
                <?php if(session()->getFlashdata('message')): ?>
                    <script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: '<?php echo addslashes(session()->getFlashdata('message')); ?>',
                            timer: 2500,
                            showConfirmButton: false
                        });
                    </script>
                <?php endif; ?>
                <?php if(session()->getFlashdata('error')): ?>
                    <script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: '<?php echo addslashes(session()->getFlashdata('error')); ?>'
                        });
                    </script>
                <?php endif; ?>

                <!-- Current Status -->
                    <?php 
                    $statusClass = 'empty';
                    $currentTicketNum = '---';
                    if($current_ticket) {
                        $statusClass = 'serving';
                        $currentTicketNum = $current_ticket['ticket_number'];
                    }
                ?>
                <div class="status-card <?php echo $statusClass; ?>">
                    <?php if($current_ticket): ?>
                        <h4>CURRENTLY SERVING</h4>
                        <div class="big-number"><?php echo $currentTicketNum; ?></div>
                    
                    <?php else: ?>
                        <h4>ROOM EMPTY</h4>
                        <div class="big-number text-muted">---</div>
                        <p>Click "NEXT" to call a number</p>
                    <?php endif; ?>
                </div>

                <!-- ALL Control Buttons -->
                <div class="text-center">
                    
                    <!-- NEXT BUTTON: always enabled and follows selected filter -->
                    <button type="button" id="nextBtn" class="btn btn-next action-btn" onclick="serveNextWithFilter(<?php echo $room_id; ?>)">NEXT</button>

                    <!-- SKIP + REPEAT BUTTONS -->
                    <?php if($current_ticket): ?>
                        <a href="<?php echo base_url('queue/skip/'.$room_id); ?>" class="btn btn-skip action-btn">SKIP</a>
                        <button type="button" 
                                class="btn btn-repeat action-btn" 
                                onclick="repeatAndNotify('<?php echo $currentTicketNum; ?>', <?php echo $room_id; ?>)">
                            REPEAT
                        </button>
                        <?php if(isset($current_ticket['type']) && $current_ticket['type'] === 'consultation'): ?>
                            <!-- Move to a specific room button for consultation tickets -->
                            <?php $targetRoom = 5; // change if needed or make dynamic ?>
                            <button type="button" 
                                    class="btn btn-warning action-btn" 
                                    onclick="confirmMove(<?php echo $room_id; ?>, <?php echo $targetRoom; ?>)">
                                GO TO ROOM <?php echo $targetRoom; ?>
                            </button>
                        <?php endif; ?>
                    <?php else: ?>
                        <button class="btn btn-skip action-btn" disabled>SKIP</button>
                        <button class="btn btn-repeat action-btn" disabled>REPEAT</button>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <!-- RIGHT: Lists -->
        <div class="col-lg-4">
            
                    
            <!-- SKIP button (already present in main controls) -->
            <!-- Waiting List -->
            <div class="sidebar mb-3" id="waitingSidebar">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">Next in Queue</h5>
                    <div class="d-flex align-items-center gap-2">
                        
                        <select id="filterSelect" class="form-select form-select-sm">
                            <option value="all" selected>All</option>
                            <option value="consultation" >Consultation</option>
                            <option value="chemo">Chemo</option>
                            
                        </select>
                        <span class="badge bg-primary" id="waitingCountBadge"><?php echo $waiting_count; ?></span>
                    </div>
                </div>

                <div class="waiting-scroll" id="waitingListContainer">
                <?php if(!empty($waiting_list)): ?>
                    <?php
                        // Order server-side: consultations first, then chemo
                        $consult = array();
                        $chemo = array();
                        foreach($waiting_list as $w) {
                            if (isset($w['type']) && $w['type'] === 'chemo') {
                                $chemo[] = $w;
                            } else {
                                $consult[] = $w;
                            }
                        }
                        $ordered = array_merge($consult, $chemo);
                    ?>
                    <?php foreach($ordered as $wait): ?>
                        <div class="ticket-item">
                            <span class="ticket-num">#<?php echo $wait['ticket_number']; ?></span>
                            <span class="badge bg-<?php echo ($wait['type'] === 'chemo') ? 'info' : 'success'; ?>">
                                <?php echo strtoupper($wait['type']); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No waiting tickets</p>
                <?php endif; ?>
                </div>
            </div>

            <!-- SKIP LIST (Previously Hold List) -->
            <div class="sidebar">
                <h5 class="mb-3">Skip List 
                    <span class="badge bg-warning text-dark"><?php echo count($hold_list); ?></span>
                </h5>
                <div class="skip-scroll">
                <?php if(!empty($hold_list)): ?>
                    <?php foreach($hold_list as $hold): ?>
                        <div class="ticket-item" style="border-left: 4px solid #dc3545;">
                            <div>
                                <span class="ticket-num">#<?php echo $hold['ticket_number']; ?></span>
                                <br>
                                <small class="text-muted">
                                    <?php 
                                        // Check if updated_at exists, otherwise use created_at
                                        $time = $hold['updated_at'] ?? $hold['created_at'] ?? date('Y-m-d H:i:s');
                                        echo date('h:i A', strtotime($time));
                                    ?>
                                </small>
                            </div>
                            <a href="<?php echo base_url('queue/resume/'.$room_id.'/'.$hold['id']); ?>" class="btn btn-sm btn-primary">Recall</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">Skip list is empty</p>
                <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="<?= base_url('assets/bootstrap5/js/bootstrap.bundle.min.js') ?>"></script>

<!-- include local SweetAlert2 (downloaded) -->
<script src="<?= base_url('assets/js/sweetalert2@11.js'); ?>"></script>

<!-- Sound Logic Script -->
<script>
    const roomNames = <?= json_encode($roomNames); ?>;
    let soundEnabled = true;
    // Doorbell audio (use the same file as display)
    const doorbellSrc = '<?= base_url("sounds/Doorbell.mp3") ?>';
    const doorbellAudio = new Audio(doorbellSrc);
    doorbellAudio.preload = 'auto';

    // Toggle sound button logic
    document.getElementById('soundToggle').addEventListener('click', function() {
        soundEnabled = !soundEnabled;
        this.textContent = soundEnabled ? '🔊' : '🔇';
        this.className = soundEnabled ? 'btn sound-toggle sound-on' : 'btn sound-toggle sound-off';
        
        // Test sound when enabling
        if(soundEnabled) {
            speak('Sound enabled');
        }
    });

    // Play a short doorbell using the provided audio file, return a Promise
    function playDoorbell() {
        return new Promise((resolve, reject) => {
            if (!soundEnabled) {
                resolve();
                return;
            }

            try {
                const a = doorbellAudio.cloneNode(true);
                const onEnded = () => {
                    a.removeEventListener('ended', onEnded);
                    resolve();
                };
                a.addEventListener('ended', onEnded);

                const p = a.play();
                if (p && typeof p.then === 'function') {
                    p.catch(err => {
                        a.removeEventListener('ended', onEnded);
                        reject(err);
                    });
                }
                // if play succeeds, resolution will be on 'ended'
            } catch (e) {
                resolve();
            }
        });
    }

    function speak(text) {
        if (!soundEnabled) return;

        // Cancel any ongoing speech
        if (window.speechSynthesis.speaking) {
            window.speechSynthesis.cancel();
        }

        // Play doorbell first, then speak
        playDoorbell().then(() => {
            setTimeout(() => {
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.rate = 0.9;
                utterance.pitch = 1;
                window.speechSynthesis.speak(utterance);
            }, 80);
        }).catch(() => {
            // if doorbell play is blocked, still attempt to speak
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.rate = 0.9;
            utterance.pitch = 1;
            window.speechSynthesis.speak(utterance);
        });
    }

    // Function called when REPEAT button is clicked (play doorbell then TTS)
    function playRepeatSound(ticketNumber, roomId) {
        if (!soundEnabled) return;

        const roomName = roomNames[roomId] ?? ('Room ' + roomId);

            const text = 'Ticket number ' + ticketNumber.replace(/-/g, ' ') + 
                        '. Please proceed to ' + roomName;
        speak(text);
    }

    // Play repeat announcement and notify server without navigating (prevents audio cutoff)
    function repeatAndNotify(ticketNumber, roomId) {
        // Play bell + TTS
        playRepeatSound(ticketNumber, roomId);

        // Notify server asynchronously so existing server-side logging/flash can run
        try {
            fetch('<?= base_url('queue/repeat/') ?>' + roomId, { method: 'GET', credentials: 'same-origin' })
                .catch(() => {});
        } catch (e) {
            // ignore
        }
    }

    // Serve next with the currently selected filter. Always enabled.
    function serveNextWithFilter(roomId) {
        const filterEl = document.getElementById('filterSelect');
        const filter = filterEl ? filterEl.value : 'consultation';

        // Send request to server to serve next according to filter, then reload page
        try {
            fetch('<?= base_url('queue/serve_next/') ?>' + roomId + '?filter=' + encodeURIComponent(filter), { method: 'GET', credentials: 'same-origin' })
                .then(() => {
                    // Force full reload so flash messages and server state update are visible
                    window.location.href = '<?= base_url('queue/room/') ?>' + roomId;
                })
                .catch(() => {
                    window.location.href = '<?= base_url('queue/room/') ?>' + roomId;
                });
        } catch (e) {
            window.location.href = '<?= base_url('queue/room/') ?>' + roomId;
        }
    }

    // Ask user for confirmation using SweetAlert, then navigate to move route
    function confirmMove(fromRoom, toRoom) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'Send this consultation ticket to Room ' + toRoom + ' waiting list?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, move it',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // redirect to generic move route
                window.location.href = '<?= base_url('queue/move_to_room/') ?>' + fromRoom + '/' + toRoom;
            }
        });
    }
document.addEventListener("DOMContentLoaded", function () {

    function updateWaitingList() {

        fetch("<?= base_url('queue/api/room/'.$room_id); ?>")
        .then(res => res.json())
        .then(data => {

            const container = document.getElementById('waitingSidebar');

            // Preserve current filter selection (if user changed it)
            const existingFilterEl = document.getElementById('filterSelect');
            const currentFilter = existingFilterEl ? existingFilterEl.value : 'consultation';

            // Build header with filter select and a badge (badge will show filtered count)
            let html = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">Next in Queue</h5>
                    <div class="d-flex align-items-center gap-2">
                        <select id="filterSelect" class="form-select form-select-sm">
                            <option value="consultation" ${currentFilter === 'consultation' ? 'selected' : ''}>Consultation</option>
                            <option value="chemo" ${currentFilter === 'chemo' ? 'selected' : ''}>Chemo</option>
                            <option value="all" ${currentFilter === 'all' ? 'selected' : ''}>All</option>
                        </select>
                        <span class="badge bg-primary" id="waitingCountBadge">${data.waiting_count}</span>
                    </div>
                </div>
                <div class="waiting-scroll">
            `;

            // Sort so consultation (non-chemo) items appear first
            const consultations = data.waiting_list.filter(w => w.type !== 'chemo');
            const chemos = data.waiting_list.filter(w => w.type === 'chemo');
            const ordered = consultations.concat(chemos);

            // Apply current filter
            let filtered = ordered;
            if (currentFilter === 'consultation') {
                filtered = ordered.filter(w => w.type !== 'chemo');
            } else if (currentFilter === 'chemo') {
                filtered = ordered.filter(w => w.type === 'chemo');
            }

            // Update badge count to show the filtered count
            const filteredCount = filtered.length;

            if (filteredCount > 0) {
                filtered.forEach(wait => {
                    html += `
                        <div class="ticket-item">
                            <span class="ticket-num">#${wait.ticket_number}</span>
                            <span class="badge bg-${wait.type === 'chemo' ? 'info' : 'success'}">${wait.type.toUpperCase()}</span>
                        </div>
                    `;
                });
            } else {
                html += `
                    <p class="text-muted">No waiting tickets</p>
                `;
            }

            html += `</div>`;
            container.innerHTML = html;

            // Replace badge count (in case badge ID existed from server-side render)
            const badge = document.getElementById('waitingCountBadge');
            if (badge) badge.textContent = filteredCount;

            // Attach change handler to the filter select to re-run update immediately
            const filterEl = document.getElementById('filterSelect');
            if (filterEl) {
                filterEl.addEventListener('change', function () {
                    // call updateWaitingList to re-render with new filter immediately
                    updateWaitingList();
                });
            }

        });
    }
    // run immediately
    updateWaitingList();

    // refresh every 3 seconds
    setInterval(updateWaitingList, 3000);

    });

</script>
</body>
</html>