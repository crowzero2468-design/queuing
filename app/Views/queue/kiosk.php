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
    <title>Cancer Center Queing System</title>
    <link rel="stylesheet" href="<?= base_url("assets/bootstrap5/css/bootstrap.min.css"); ?>">
    <style>
        body { 
            background-color: #f0f2f5; 
            margin-bottom: 0;
        }
        .ticket-box { 
            background: white; 
            color: #333; 
            padding: 30px; 
            border-radius: 20px; 
            text-align: center; 
            width: 95%; 
            max-width: 1200px; 
            margin: 0 auto;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .ticket-num { font-size: 4rem; font-weight: bold; color: #0d6efd; }
        .ticket-type { 
            display: inline-block; 
            padding: 5px 20px; 
            border-radius: 20px; 
            color: white;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .btn-generate { 
            padding: 25px 20px; 
            font-size: 1.2rem; 
            border-radius: 15px;
            text-decoration: none;
            white-space: nowrap;
            cursor: pointer;
            display: block;
            width: 100%;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-generate:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .room-badge {
            background-color: #6c757d;
            padding: 5px 15px;
            border-radius: 20px;
            color: white;
            font-size: 0.9rem;
            display: inline-block;
            margin-top: 5px;
        }
        .section-title {
            font-size: 1.2rem;
            color: #6c757d;
            margin-bottom: 20px;
            font-weight: bold;
            text-align: center;
        }
        .btn-print {
            padding: 15px 40px;
            font-size: 1.2rem;
            border-radius: 50px;
            background-color: #198754;
            color: white;
            border: none;
            cursor: pointer;
            margin-top: 20px;
        }
        .btn-print:hover {
            background-color: #157347;
        }
        
        /* Print Ticket Styles */
        .print-ticket {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: white;
            z-index: 9999;
        }
        .print-ticket.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
                .print-ticket-content {
            /* width/height for 8cm x 4cm thermal paper (80mm x 40mm) */
            width: var(--ticket-width, 80mm);
            height: var(--ticket-height, 40mm);
            margin: auto;
            padding: 12px;
            border: none;
            text-align: center;
            border-radius: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .print-ticket h3 {
            font-size: 0.75rem;
            margin: 0 0 3px 0;
            color: #999;
            font-weight: normal;
            line-height: 1;
        }
        .print-ticket .ticket-full-number {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
            margin: 2px 0;
            line-height: 1;
            border-top: 1px solid #333;
            border-bottom: 1px solid #333;
            padding: 4px 0;
        }
        .print-ticket .type-badge {
            display: none;
        }
        .print-ticket .room-info {
            margin: 2px 0;
            font-size: 0.75rem;
            color: #333;
            line-height: 1;
        }
        .print-ticket .time-display {
            font-size: 0.75rem;
            color: #333;
            margin: 1px 0;
            line-height: 1;
        }
        .print-ticket .footer {
            margin: 3px 0 0 0;
            font-size: 0.65rem;
            color: #666;
            line-height: 1.1;
        }
        .print-close {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 2rem;
            cursor: pointer;
            color: #333;
        }
        
        /* Print Styles */
            @media print {

                @page {
                    size: var(--ticket-width,80mm) var(--ticket-height,40mm);
                    margin: 0;
                }

                html, body {
                    width: var(--ticket-width,80mm) !important;
                    height: var(--ticket-height,40mm) !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    background: #fff;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                }

                body * {
                    visibility: hidden;
                }

                .print-ticket,
                .print-ticket * {
                    visibility: visible;
                }

                .print-ticket {
                    position: relative !important;
                    top: auto;
                    left: auto;
                    width: var(--ticket-width,80mm);
                    height: var(--ticket-height,40mm);
                    margin: 0 auto;
                    display: flex;
                    flex-direction: column;
                    justify-content: space-between;
                }

                .print-ticket-content {
                    width: var(--ticket-width,80mm);
                    height: var(--ticket-height,40mm);
                    padding: 8px;
                    border: none;
                    border-radius: 0;
                    text-align: center;
                    display: flex;
                    flex-direction: column;
                    justify-content: space-between;
                }

                .print-close {
                    display: none;
                }

                @page {
                    margin: 0;
                    padding: 0;
                }
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

<!-- Navbar -->
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

<!-- Main Content -->
<div class="container-fluid px-4 mt-4">
    <div class="ticket-box">
        <h2 class="mb-4">Generate Ticket</h2>
        
        <!-- Form for Generating Ticket -->
        <form action="<?= base_url('queue/generate'); ?>" method="post" id="ticketForm">
            <?= csrf_field(); ?>
            
            <!-- Hidden inputs -->
            <input type="hidden" name="type" id="selectedType" value="<?= $selected_type ?? 'chemo'; ?>">
            <input type="hidden" name="room" id="selectedRoom" value="<?= $selected_room ?? ''; ?>">
            
            <!-- CHEMO Section -->
            <h4 class="section-title">CHEMO - Select Room</h4>
            <div class="row justify-content-center g-2 mb-4">
                <?php for($i = 1; $i <= 5; $i++): ?>
                    <?php if(in_array($i, $disabledRooms)) continue; ?>
                    <?php 
                        $isActiveChemo = (
                            isset($selected_type) && 
                            $selected_type === 'chemo' && 
                            isset($selected_room) && 
                            $selected_room == $i
                        );
                        $btnClassChemo = $isActiveChemo 
                            ? 'btn btn-info text-white' 
                            : 'btn btn-outline-info';
                    ?>
                    <div class="col-6 col-md-4 col-lg-2" style="max-width: 200px;">
                        <button type="button" 
                                class="btn-generate <?= $btnClassChemo; ?>" 
                                onclick="selectOption('chemo', <?= $i; ?>)">
                            <?= $roomNames[$i] ?? 'Room '.$i; ?>
                        </button>
                    </div>
                <?php endfor; ?>
            </div>
            
            <hr class="my-5">
            
            <!-- CONSULTATION Section -->
            <h4 class="section-title">CONSULTATION - Select Room</h4>
            <div class="row justify-content-center g-2 mb-4">
                <?php for($i = 1; $i <= 5; $i++): ?>
                    <?php if(in_array($i, $disabledRooms)) continue; ?>
                    <?php 
                        $isActiveConsultation = (
                            isset($selected_type) && 
                            $selected_type === 'consultation' && 
                            isset($selected_room) && 
                            $selected_room == $i
                        );
                        $btnClassConsultation = $isActiveConsultation 
                            ? 'btn btn-success text-white' 
                            : 'btn btn-outline-success';
                    ?>
                    <div class="col-6 col-md-4 col-lg-2" style="max-width: 200px;">
                        <button type="button" 
                                class="btn-generate <?= $btnClassConsultation; ?>" 
                                onclick="selectOption('consultation', <?= $i; ?>)">
                            <?= $roomNames[$i] ?? 'Room '.$i; ?>
                        </button>
                    </div>
                <?php endfor; ?>
            </div>
        </form>

        <!-- Generated Ticket Display -->
        <?php if(isset($_GET['generated']) && $_GET['generated'] == '1'): ?>
            <hr class="my-5">
            <div class="mt-3">

                <div class="ticket-type bg-<?= strtolower($selected_type) === 'chemo' ? 'info' : 'success'; ?>">
                    <?= strtoupper($selected_type); ?>
                </div>
                
                <?php if(isset($selected_room) && $selected_room): ?>
                    <div class="room-badge">
                        <?= $roomNames[$selected_room] ?? 'Room '.$selected_room; ?>
                    </div>
                <?php else: ?>
                    <div class="room-badge">General Queue</div>
                <?php endif; ?>
                
                <div class="ticket-num"><?= $_GET['number'] ?? ''; ?></div>
                <p class="text-muted">Ticket generated! Waiting to be called...</p>
                
                <div class="d-flex justify-content-center mt-3">
                    <?php
                        $printUrl = base_url(
                            'queue/print_view/' . 
                            ($_GET['number'] ?? '') . '/' . 
                            ($selected_room ?? '')
                        );
                    ?>
                    <a href="<?= $printUrl ?>" 
                       class="btn btn-print" 
                       target="_blank">
                        🖨️ Print
                    </a>
                </div>

            </div>
        <?php endif; ?>

    </div>
</div>

<!-- Print Ticket Modal -->
<div class="print-ticket" id="printTicket">
    <span class="print-close" onclick="closePrintTicket()">&times;</span>
    <div class="print-ticket-content">
        <div>
            <h3>Cancer Center</h3>
            <?php
                $printFullNumber = $_GET['number'] ?? '';
            ?>
            <div class="ticket-full-number"><?php echo $printFullNumber; ?></div>
            <div class="room-info">
                <?php if(isset($selected_room) && $selected_room): ?>
                    Room <?php echo $selected_room; ?>
                <?php else: ?>
                    General Queue
                <?php endif; ?>
            </div>
            <div class="time-display" id="printTime"></div>
            <div class="time-display" id="printDate"></div>
            <div class="footer">
                Wait until your number will called
            </div>
        </div>
    </div>
</div>

<!-- JavaScript to handle selection -->
<script>
function selectOption(type, room) {
    // Update hidden inputs
    document.getElementById('selectedType').value = type;
    document.getElementById('selectedRoom').value = room;
    
    // Submit the form
    document.getElementById('ticketForm').submit();
}

// Print Ticket Functions
function openPrintTicket() {
    // default to 80mm x 40mm (8cm x 4cm thermal paper)
    openPrintTicketSize(80, 40);
}

// Format and display current date/time on print ticket
function updatePrintDateTime() {
    const now = new Date();
    
    // Format time as h:mmpm/am (e.g., 9:06pm)
    const hours = now.getHours();
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const ampm = hours >= 12 ? 'pm' : 'am';
    const displayHours = hours % 12 || 12;
    const timeStr = displayHours + ':' + minutes + ampm;
    
    // Format date as M/D/YYYY (e.g., 2/27/2026)
    const month = now.getMonth() + 1;
    const day = now.getDate();
    const year = now.getFullYear();
    const dateStr = month + '/' + day + '/' + year;
    
    const timeEl = document.getElementById('printTime');
    const dateEl = document.getElementById('printDate');
    if (timeEl) {
        timeEl.textContent = timeStr;
    }
    if (dateEl) {
        dateEl.textContent = dateStr;
    }
}

// Open the print modal and set width variable
function openPrintTicketSize(widthMm, heightMm = 40) {
    const printModal = document.getElementById('printTicket');
    // set CSS variables for size on root element (default 80mm x 40mm for 8cm x 4cm thermal paper)
    document.documentElement.style.setProperty('--ticket-width', widthMm + 'mm');
    document.documentElement.style.setProperty('--ticket-height', heightMm + 'mm');
    updatePrintDateTime();
    printModal.classList.add('active');

    // delay before printing so layout stabilizes
    setTimeout(function() {
        // disable header/footer for cleaner thermal paper output
        if (window.chrome !== undefined) {
            // Chrome-based browsers
            window.print();
        } else {
            // Firefox and others
            window.print();
        }
    }, 300);
    window.onafterprint = function() {
        closePrintTicket();
    };
}

function closePrintTicket() {
    document.getElementById('printTicket').classList.remove('active');
}

function printTicket() {
    // retain backwards compatibility; print at default thermal paper size (80mm x 40mm)
    openPrintTicketSize(80, 40);
}

// Close print modal when clicking outside
document.getElementById('printTicket').addEventListener('click', function(e) {
    if (e.target === this) {
        closePrintTicket();
    }
});
</script>

<!-- Bootstrap JS -->
<script src="<?= base_url('assets/bootstrap5/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>