<?php

namespace Overtrue\LaravelWechat\Events\OpenPlatform;

use EasyWeChat\Support\Collection;
use Illuminate\Queue\SerializesModels;

class UnAuthorized
{
    use SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     *
     * @param \EasyWeChat\Support\Collection $event
     */
    public function __construct(Collection $event)
    {
        $this->message = $event;
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
