<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\QueueModel;

class Queue extends BaseController
{
    protected QueueModel $queueModel;

    public function __construct()
    {
        $this->queueModel = new QueueModel();
    }

    /**
     * Main Display - View Only
     */
    public function index()
    {
        $data['rooms'] = [];
        for ($i = 1; $i <= 5; $i++) {
            $data['rooms'][$i] = $this->queueModel->getTicketByRoom($i);
        }

        $data['up_next'] = $this->queueModel->getMostRecentServing();
        $data['waiting_list'] = $this->queueModel->getAllWaitingAllRooms();
        $data['waiting_count'] = count($data['waiting_list']);

        return view('queue/display', $data);
    }

    /**
     * Staff Kiosk - Display Form Only (GET)
     */
    public function kiosk()
    {
        $type = $this->request->getGet('type') ?? 'chemo';
        $roomId = $this->request->getGet('room') ?? null;
        
        if (!in_array($type, ['chemo', 'consultation'])) {
            $type = 'chemo';
        }
        
        if ($roomId !== null) {
            $roomId = (int) $roomId;
            if ($roomId < 1 || $roomId > 5) {
                $roomId = null;
            }
        }

        $data['selected_type'] = $type;
        $data['selected_room'] = $roomId;
        
        return view('queue/kiosk', $data);
    }

    /**
     * Generate Ticket
     */
    public function generate()
    {
        $type = $this->request->getPost('type') ?? 'chemo';
        $roomId = $this->request->getPost('room') ?? null;
        
        if (!in_array($type, ['chemo', 'consultation'])) {
            $type = 'chemo';
        }
        
        if ($roomId !== null && $roomId !== '') {
            $roomId = (int) $roomId;
            if ($roomId < 1 || $roomId > 5) {
                $roomId = null;
            }
        } else {
            $roomId = null;
        }

        $newNumber = $this->queueModel->generateNextNumber($type);
        $this->queueModel->addTicket($newNumber, $type, $roomId);

        return redirect()->to('queue/kiosk?type=' . $type . '&room=' . ($roomId ?? '') . '&generated=1&number=' . $newNumber);
    }

    /**
     * Call next ticket to specific room
     */
    public function callRoom(int $roomId)
    {
        $nextTicket = $this->queueModel->getUpNextByRoom($roomId);
        
        if ($nextTicket) {
            $this->queueModel->callTicket($nextTicket['id'], $roomId);
            session()->setFlashdata('message', 'Ticket #' . $nextTicket['ticket_number'] . ' called to Room ' . $roomId);
        } else {
            session()->setFlashdata('error', 'No waiting tickets for Room ' . $roomId);
        }

        return redirect()->to("/queue/room/{$roomId}");
    }

    /**
     * Room Control
     */
    public function room(int $roomId)
    {
        $data['room_id'] = $roomId;
        $data['current_ticket'] = $this->queueModel->getTicketByRoom($roomId);
        $data['next_in_line'] = $this->queueModel->getUpNextByRoom($roomId);
        $data['waiting_list'] = $this->queueModel->getAllWaitingByRoom($roomId);
        $data['waiting_count'] = $this->queueModel->getWaitingCountByRoom($roomId);
        $data['hold_list'] = $this->queueModel->getHoldListByRoom($roomId);

        return view('queue/room_control', $data);
    }

    /**
     * Serve next - Complete current and call next
     */
    public function serveNext(int $roomId)
    {
        // 1. Complete current ticket (mark as served)
        $currentTicket = $this->queueModel->getTicketByRoom($roomId);
        if ($currentTicket) {
            $this->queueModel->serveTicket($roomId);
        }
        
        // 2. Call next ticket to this room
        // Optional filter parameter: consultation / chemo / all
        $filter = $this->request->getGet('filter') ?? 'consultation';
        if (!in_array($filter, ['consultation', 'chemo', 'all'])) {
            $filter = 'consultation';
        }

        // Fetch waiting list for this room and pick according to filter
        $waiting = $this->queueModel->getAllWaitingByRoom($roomId);
        $nextTicket = null;

        if ($filter === 'all') {
            $nextTicket = count($waiting) ? $waiting[0] : null;
        } elseif ($filter === 'chemo') {
            // find first chemo
            foreach ($waiting as $w) {
                if (isset($w['type']) && $w['type'] === 'chemo') {
                    $nextTicket = $w;
                    break;
                }
            }
            // fallback: if none found, leave null (do not pick consultation)
        } else { // consultation (default)
            // prefer consultation (non-chemo), otherwise fallback to chemo
            foreach ($waiting as $w) {
                if (!isset($w['type']) || $w['type'] !== 'chemo') {
                    $nextTicket = $w;
                    break;
                }
            }
            if (!$nextTicket && count($waiting)) {
                // fallback to first available (chemo)
                $nextTicket = $waiting[0];
            }
        }

        if ($nextTicket) {
            $this->queueModel->callTicket($nextTicket['id'], $roomId);
            session()->setFlashdata('message', 'Ticket #' . $nextTicket['ticket_number'] . ' now serving');
        } else {
            session()->setFlashdata('message', 'Room is now empty');
        }

        return redirect()->to("/queue/room/{$roomId}");
    }

    /**
     * Move current consultation ticket from this room to room 5 (waiting state)
     */
    public function moveToRoom5(int $roomId)
    {
        $currentTicket = $this->queueModel->getTicketByRoom($roomId);
        if ($currentTicket && isset($currentTicket['type']) && $currentTicket['type'] === 'consultation') {
            // change status back to waiting and assign to room 5
            $this->queueModel->update($currentTicket['id'], [
                'status'  => 'waiting',
                'room_id' => 5
            ]);
            session()->setFlashdata('message', 'Moved consultation #' . $currentTicket['ticket_number'] . ' to Room 5 waiting list.');
        } else {
            session()->setFlashdata('error', 'No consultation ticket currently serving in this room.');
        }

        return redirect()->to("/queue/room/{$roomId}");
    }

    /**
     * Move current consultation ticket from one room to another waiting list
     */
    public function moveToRoom(int $roomId, int $targetRoom)
    {
        $currentTicket = $this->queueModel->getTicketByRoom($roomId);
        if ($currentTicket && isset($currentTicket['type']) && $currentTicket['type'] === 'consultation') {
            $this->queueModel->update($currentTicket['id'], [
                'status'  => 'waiting',
                'room_id' => $targetRoom
            ]);
            session()->setFlashdata('message', 'Moved consultation #' . $currentTicket['ticket_number'] . ' to Room ' . $targetRoom . ' waiting list.');
        } else {
            session()->setFlashdata('error', 'No consultation ticket currently serving in this room.');
        }

        return redirect()->to("/queue/room/{$roomId}");
    }

    /**
     * Skip current ticket (Put in Skip List) AND call next
     */
    public function skip(int $roomId)
    {
        // 1. Get current ticket
        $currentTicket = $this->queueModel->getTicketByRoom($roomId);
        
        if ($currentTicket) {
            // 2. Put current ticket in Skip List (mark skipped)
            $this->queueModel->skipTicket($roomId);
            
            // 3. Call next ticket to this room
            $nextTicket = $this->queueModel->getUpNextByRoom($roomId);
            
            if ($nextTicket) {
                $this->queueModel->callTicket($nextTicket['id'], $roomId);
                session()->setFlashdata('message', 'Moved to Skip List. Calling next: ' . $nextTicket['ticket_number']);
            } else {
                session()->setFlashdata('message', 'Moved to Skip List. No more tickets waiting.');
            }
        }

        return redirect()->to("/queue/room/{$roomId}");
    }

    /**
     * Hold current ticket and call next one
     */
    public function hold(int $roomId)
    {
        // 1. Get current ticket
        $currentTicket = $this->queueModel->getTicketByRoom($roomId);
        
        if ($currentTicket) {
            // 2. Put current ticket on hold (moves it to the Skip List)
            $this->queueModel->holdTicket($roomId);
            
            // 3. Automatically call the next ticket to this room
            $nextTicket = $this->queueModel->getUpNextByRoom($roomId);
            
            if ($nextTicket) {
                $this->queueModel->callTicket($nextTicket['id'], $roomId);
                session()->setFlashdata('message', 'Moved to Skip List. Calling next ticket: ' . $nextTicket['ticket_number']);
            } else {
                session()->setFlashdata('message', 'Moved to Skip List. No more tickets waiting.');
            }
        }

        return redirect()->to("/queue/room/{$roomId}");
    }

    /**
     * Resume from hold (Recall from Skip List)
     */
    public function resume(int $roomId, int $ticketId = null)
    {
        // If a specific ticket id is provided, resume that ticket. Otherwise fallback to previous behavior.
        $result = $this->queueModel->resumeTicket($roomId, $ticketId);

        if ($result) {
            session()->setFlashdata('message', 'Recalled ticket to Room ' . $roomId);
        } else {
            session()->setFlashdata('error', 'No ticket available to recall.');
        }

        return redirect()->to("/queue/room/{$roomId}");
    }

    /**
     * Repeat calling the current ticket number
     */
    public function repeat(int $roomId)
    {
        $currentTicket = $this->queueModel->getTicketByRoom($roomId);
        
        if ($currentTicket) {
            session()->setFlashdata('message', 'Calling Ticket #' . $currentTicket['ticket_number'] . ' again...');
        } else {
            session()->setFlashdata('error', 'No ticket is currently being served in this room.');
        }

        return redirect()->to("/queue/room/{$roomId}");
    }

    /**
     * Call Off - Serve all waiting tickets for today
     */
    public function callOff(int $roomId = 1)
    {
        // Get all waiting tickets
        $waitingTickets = $this->queueModel->getAllWaitingAllRooms();
        
        // Also get all currently serving tickets
        $servingTickets = $this->queueModel->getAllServingTickets();
        
        // Combine both lists
        $allTickets = array_merge($waitingTickets, $servingTickets);
        
        // Check if there are any tickets
        if (empty($allTickets)) {
            session()->setFlashdata('message', 'No tickets waiting! All queues are empty.');
            return redirect()->to("/queue/room/{$roomId}");
        }
        
        // Serve all tickets
        $count = 0;
        foreach ($allTickets as $ticket) {
            $this->queueModel->update($ticket['id'], [
                'status'  => 'served',
                'room_id' => null
            ]);
            $count++;
        }
        
        session()->setFlashdata('message', 'Call Off completed! ' . $count . ' tickets marked as served.');
        return redirect()->to("/queue/room/{$roomId}");
    }

    /**
     * API - Get display data in JSON format
     */
    public function apiDisplay()
    {
        $data['rooms'] = [];
        for ($i = 1; $i <= 5; $i++) {
            $data['rooms'][$i] = $this->queueModel->getTicketByRoom($i);
        }

        $data['up_next'] = $this->queueModel->getMostRecentServing();
        $data['waiting_list'] = $this->queueModel->getAllWaitingAllRooms();
        $data['waiting_count'] = count($data['waiting_list']);

        return $this->response->setJSON($data);
    }

    public function apiRoomDisplay(int $roomId)
    {
        $current = $this->queueModel->getTicketByRoom($roomId);

        $waiting = $this->queueModel->getAllWaitingByRoom($roomId);

        return $this->response->setJSON([
            'up_next'       => $current,
            'waiting_list'  => $waiting,
            'waiting_count' => count($waiting)
        ]);
    }
    public function roomView(int $roomId) {
         return view('queue/room_view', 
         [ 'roomId' => $roomId ]); 
    }
    public function apiRoom($roomId)
    {
        return $this->response->setJSON([
            'current_ticket' => $this->queueModel->getTicketByRoom($roomId),
            'next_in_line'   => $this->queueModel->getUpNextByRoom($roomId),
            'waiting_list'   => $this->queueModel->getAllWaitingByRoom($roomId),
            'waiting_count'  => $this->queueModel->getWaitingCountByRoom($roomId),
            'hold_list'      => $this->queueModel->getHoldListByRoom($roomId)
        ]);
    }

    /**
     * Print view preview - display ticket in printable format
     */
    public function printView(?string $number = null, ?int $room = null)
    {
        $data = [
            'number' => $number ?? 'CONSULTATION-028',
            'room'   => $room ?? 1
        ];
        return view('queue/print_view', $data);
    }
}