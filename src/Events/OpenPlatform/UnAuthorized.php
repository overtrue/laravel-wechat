<?php

namespace Overtrue\LaravelWechat\Events\OpenPlatform;

use EasyWeChat\Support\Collection;
use Illuminate\Queue\SerializesModels;

class Unauthorized
{
    use SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     *
     * @param \EasyWeChat\Support\Collection $message
     */
    public function __construct(Collection $message)
    {
        $this->message = $message;
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
