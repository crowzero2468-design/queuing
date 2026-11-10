<?php

namespace App\Models;

use CodeIgniter\Model;

class QueueModel extends Model
{
    protected $table            = 'queue_tickets';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    // include timestamp fields so that the model can write them when
    // `$useTimestamps` is enabled below.  We only ever set them via the
    // framework, but they must be allowed to avoid protection errors.
    protected $allowedFields    = ['ticket_number', 'type', 'status', 'room_id', 'created_at', 'updated_at'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // enable automatic timestamping so that ``updated_at`` reflects the
    // last time the ticket was modified.  This allows the display logic to
    // show the **most recently called** ticket instead of the one with the
    // highest numerical ID (which is what we were seeing when an older ticket
    // was summoned after a newer one).
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get ticket by room
     */
    public function getTicketByRoom(int $roomId): ?array
    {
        return $this->where('room_id', $roomId)
                    ->where('status', 'serving')
                    ->first();
    }

    /**
     * Get next waiting ticket for specific room
     */
public function getUpNextByRoom(int $roomId): ?array
{
    if ($roomId === 1) {
        // Room 1 pulls from general queue (unassigned)
        return $this->where('status', 'waiting')
                    ->where('room_id', null)
                    ->orderBy('id', 'ASC')
                    ->first();
    }

    // Other rooms pull only their own assigned tickets
    return $this->where('status', 'waiting')
                ->where('room_id', $roomId)
                ->orderBy('id', 'ASC')
                ->first();
}

    /**
     * Get all waiting tickets for specific room
     */
    public function getAllWaitingByRoom(int $roomId): array
    {
        return $this->where('room_id', $roomId)
                    ->where('status', 'waiting')
                    ->orderBy('id', 'ASC')
                    ->findAll();
    }

    /**
     * Get all waiting tickets (general queue)
     */
    public function getAllWaiting(): array
    {
        return $this->where('status', 'waiting')
                    ->where('room_id', null)
                    ->orderBy('id', 'ASC')
                    ->findAll();
    }

    /**
     * Get next waiting ticket (general queue)
     */
    public function getUpNext(): ?array
    {
        return $this->where('status', 'waiting')
                    ->where('room_id', null)
                    ->orderBy('id', 'ASC')
                    ->first();
    }

    /**
     * Get last ticket by type
     */
    public function getLastTicketByType(string $type): ?array
    {
        return $this->where('type', $type)
                    ->orderBy('id', 'DESC')
                    ->first();
    }

    /**
     * Get last ticket by type for TODAY - FIXED
     */
    public function getLastTicketByTypeToday(string $type): ?array
    {
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        
        return $this->where('type', $type)
                    ->where('created_at >=', $today . ' 00:00:00')
                    ->where('created_at <', $tomorrow . ' 00:00:00')
                    ->orderBy('id', 'DESC')
                    ->first();
    }

    /**
     * Add new ticket with optional room assignment
     */
    public function addTicket(string $number, string $type, ?int $roomId = null): int
    {
        date_default_timezone_set('Asia/Manila');
        
        $data = [
            'ticket_number' => $number,
            'type'          => $type,
            'status'        => 'waiting',
            'room_id'       => $roomId,
            'created_at'    => date('Y-m-d H:i:s')
        ];
        
        $this->insert($data);
        return (int) $this->insertID();
    }

    /**
     * Generate next number by type - RESETS DAILY
     */
    public function generateNextNumber(string $type): string
    {
        date_default_timezone_set('Asia/Manila');
        $prefix = strtoupper($type);
        
        // Get last ticket of this type for TODAY only
        $last = $this->getLastTicketByTypeToday($type);
        
        $num = 1;
        
        if ($last && !empty($last['ticket_number'])) {
            $lastNumber = trim($last['ticket_number']);
            $numericPart = str_replace([$prefix . '-', $prefix], '', $lastNumber);
            preg_match('/(\d+)/', $numericPart, $matches);
            
            if (!empty($matches)) {
                $num = (int) $matches[1] + 1;
            }
        }
        
        return $prefix . '-' . str_pad($num, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Call ticket to room
     */
    public function callTicket(int $ticketId, int $roomId): bool
    {
        // 1️⃣ Check if this room already has a serving ticket
        $existing = $this->where('room_id', $roomId)
                        ->where('status', 'serving')
                        ->first();

        if ($existing) {
            // Do NOT call another ticket if room is busy
            return false;
        }

        // 2️⃣ Make sure ticket is still waiting
        $ticket = $this->find($ticketId);
        if (!$ticket || $ticket['status'] !== 'waiting') {
            return false;
        }

        // 3️⃣ Assign ticket to this room ONLY now
        return $this->update($ticketId, [
            'status'  => 'serving',
            'room_id' => $roomId
        ]);
    }

    /**
     * Serve ticket - mark as served
     */
public function serveTicket(int $roomId): bool
{
    $ticket = $this->getTicketByRoom($roomId);

    if (!$ticket) {
        return false;
    }

    // If triage (Room 1), send to Room 3 waiting
    if ($roomId === 1) {
        return $this->update($ticket['id'], [
            'status'  => 'waiting',
            'room_id' => 3
        ]);
    }

    // Other rooms fully serve the ticket
    return $this->update($ticket['id'], [
        'status' => 'served'
    ]);
}
    /**
     * Skip ticket - alias for serve
     */
    public function skipTicket(int $roomId): bool
    {
        $ticket = $this->getTicketByRoom($roomId);
        if ($ticket) {
            return $this->update($ticket['id'], [
                'status'  => 'skipped',
                'room_id' => null
            ]);
        }

        return false;
    }

    /**
     * Put current ticket to skipped list (previously hold)
     */
    public function holdTicket(int $roomId): bool
    {
        // legacy name kept for compatibility; delegates to skipTicket
        return $this->skipTicket($roomId);
    }

    /**
     * Resume ticket
     */
        public function resumeTicket(int $roomId, ?int $ticketId = null): bool
        {
            // Determine which skipped ticket to resume
            if ($ticketId !== null) {
                $ticket = $this->find($ticketId);
                // ensure it's actually skipped
                if (!$ticket || ($ticket['status'] ?? '') !== 'skipped') {
                    return false;
                }
            } else {
                // fallback: get latest skipped ticket
                $ticket = $this->where('status', 'skipped')
                            ->orderBy('updated_at', 'DESC')
                            ->first();
                if (!$ticket) {
                    return false;
                }
            }

            // If there's currently a serving ticket in this room, move it back to waiting
            $currentServing = $this->where('room_id', $roomId)
                                   ->where('status', 'serving')
                                   ->first();

            // If current serving exists and it's not the same ticket, set it to waiting
            if ($currentServing && $currentServing['id'] != $ticket['id']) {
                $this->update($currentServing['id'], [
                    'status'  => 'waiting',
                ]);
            }

            // Now assign the skipped ticket to serving in this room
            return $this->update($ticket['id'], [
                'status'  => 'serving',
                'room_id' => $roomId
            ]);
        }

    /**
     * Get waiting count for specific room
     */
    public function getWaitingCountByRoom(int $roomId): int
    {
        return $this->where('status', 'waiting')
                    ->where('room_id', $roomId)
                    ->countAllResults();
    }

    /**
     * Get general waiting count
     */
    public function getWaitingCount(): int
    {
        return $this->where('status', 'waiting')
                    ->where('room_id', null)
                    ->countAllResults();
    }

    /**
     * Get tickets on hold for a specific room
     */
    public function getHoldListByRoom(int $roomId): array
    {
        // return skipped tickets (legacy method name kept)
        return $this
                    ->where('status', 'skipped')
                    ->orderBy('id', 'ASC')
                    ->findAll();
    }

    // ============================================
    // NEW FUNCTIONS ADDED FOR DISPLAY
    // ============================================

    /**
     * Get most recent serving ticket (for UP NEXT display)
     */
    public function getMostRecentServing(): ?array
    {
        // Use the timestamp instead of the auto-incrementing id so that the
        // "up next" display reflects the ticket that was *most recently
        // moved into serving* mode.  Previously we ordered by id which meant
        // calling an older ticket (lower id) after a newer one would leave the
        // display stuck on the higher number.  `updated_at` is now maintained
        // automatically via `$useTimestamps` above.
        return $this->where('status', 'serving')
                    ->orderBy('updated_at', 'DESC')
                    ->first();
    }

    /**
     * Get all waiting tickets from all rooms (for display)
     */
    public function getAllWaitingAllRooms(): array
    {
        return $this->whereIn('status', ['waiting', 'skipped'])
                    ->orderBy('id', 'ASC')
                    ->findAll();
    }

    /**
     * Get all serving tickets (from all rooms)
     */
    public function getAllServingTickets(): array
    {
        return $this->where('status', 'serving')
                    ->findAll();
    }
}