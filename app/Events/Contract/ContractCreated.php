<?php

namespace App\Events\Contract;

use App\Events\Event;
use App\Models\Contract;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ContractCreated extends Event implements ShouldBroadcast
{
    use SerializesModels;
    /**
     * @var Contract
     */
    public $contract;

    /**
     * Create a new event instance.
     * @param Contract $contract
     */
    public function __construct(Contract $contract)
    {
        $this->contract = $contract;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
