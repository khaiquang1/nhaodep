<?php

namespace App\Events\Email;

use App\Repositories\EmailRepositoryEloquent;
use App\Repositories\UserRepositoryEloquent;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class EmailSentEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $email;
    private $emailRepository;
    private $userRepository;
    private $message;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($email)
    {
        $this->email = $email;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */

    public function broadcastOn()
    {
        $this->emailRepository = new EmailRepositoryEloquent(app());
        $this->userRepository = new UserRepositoryEloquent(app());
        $email = $this->emailRepository->find($this->email);
        $user = $this->userRepository->find($email->to);
        $this->message = $user->full_name.' has sent you this message '.$email->message;
        return new Channel('mail_compose_channel'.$user->id);
    }

    public function broadcastWith()
    {
        $data['message'] = $this->message;
        return $data;
    }

}
