<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Ticket Preview</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #f0f0f0;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .ticket-container {
            background: white;
            width: 80mm;
            height: 50mm;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 8px;
            text-align: center;
        }
        
        .ticket-header {
            font-size: 0.75rem;
            color: #999;
            margin-bottom: 3px;
        }
        
        .ticket-number {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
            margin: 2px 0;
            border-top: 1px solid #333;
            border-bottom: 1px solid #333;
            padding: 4px 0;
        }
        
        .ticket-room {
            font-size: 0.75rem;
            color: #333;
            margin: 2px 0;
        }
        
        .ticket-time {
            font-size: 0.75rem;
            color: #333;
            margin: 1px 0;
        }
        
        .ticket-footer {
            font-size: 0.65rem;
            color: #666;
            margin-top: 3px;
            line-height: 1.1;
        }
        
        .info-panel {
            margin-top: 30px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-width: 400px;
        }
        
        .info-panel h3 {
            margin-bottom: 15px;
            color: #333;
        }
        
        .info-panel p {
            margin: 8px 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        .print-button {
            margin-top: 20px;
            padding: 10px 20px;
            background: #198754;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
        }
        
        .print-button:hover {
            background: #157347;
        }
    </style>
</head>
<body>
    <div>
        <div class="ticket-container">
            <div>
                <div class="ticket-header">Cancer Center</div>
                <div class="ticket-number"><?php echo isset($number) ? $number : 'CONSULTATION-0'; ?></div>
                <div class="ticket-room">
                    <?php 
                    if(isset($room) && $room) {
                        echo 'Room ' . $room;
                    } else {
                        echo 'Skipped';
                    }
                    ?>
                </div>
                <!-- server-side fallback in case JS hasn't run yet -->
                <div class="ticket-time" id="timeDisplay"><?php echo date('g:ia'); ?></div>
                <div class="ticket-time" id="dateDisplay"><?php echo date('n/j/Y'); ?></div>
                <div class="ticket-footer">Wait until your number will called</div>
            </div>
        </div>
        
        <!-- <div class="info-panel">
            <h3>Ticket Preview (80mm × 40mm)</h3>
            <p><strong>Ticket Number:</strong> <?php echo isset($number) ? $number : 'CONSULTATION-028'; ?></p>
            <p><strong>Room:</strong> <?php echo isset($room) && $room ? 'Room ' . $room : 'General Queue'; ?></p>
            <p><strong>Paper Size:</strong> 80mm × 40mm (8cm × 4cm)</p>
            <p><strong>Format:</strong> Thermal paper ticket</p>
            <button class="print-button" onclick="window.print()">🖨️ Print This Ticket</button>
        </div> -->
    </div>
    
    <script>
        function updateDateTime() {
            const now = new Date();
            
            // Format time as h:mmpm/am
            const hours = now.getHours();
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const ampm = hours >= 12 ? 'pm' : 'am';
            const displayHours = hours % 12 || 12;
            const timeStr = displayHours + ':' + minutes + ampm;
            
            // Format date as M/D/YYYY
            const month = now.getMonth() + 1;
            const day = now.getDate();
            const year = now.getFullYear();
            const dateStr = month + '/' + day + '/' + year;
            
            document.getElementById('timeDisplay').textContent = timeStr;
            document.getElementById('dateDisplay').textContent = dateStr;
        }
        
        updateDateTime();
        // keep ticking every 30 seconds so the time stays current while the tab remains open
        setInterval(updateDateTime, 30000);
        // once the date/time and layout is ready, immediately open print dialog
        // so the user doesn't have to click anything. the info panel is hidden
        // during printing via CSS.
        window.onload = function() {
            // open print dialog shortly after load
            setTimeout(() => { window.print(); }, 100);
        };

        // when the print dialog closes (either printed or cancelled), close this tab
        if ('onafterprint' in window) {
            window.onafterprint = function() {
                window.close();
            };
        } else {
            // fallback: attempt to close a short time after the dialog would normally finish
            window.onfocus = function() {
                // after returning focus, assume print dialog closed
                setTimeout(() => { window.close(); }, 200);
            };
        }
    </script>
    
    <style media="print">
        body {
            background: white;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 0;
            margin: 0;
        }
        
        .info-panel {
            display: none;
        }
        
        .ticket-container {
            box-shadow: none;
            margin: 0;
            width: 80mm;
            height: 40mm;
        }
    </style>
</body>
</html>
