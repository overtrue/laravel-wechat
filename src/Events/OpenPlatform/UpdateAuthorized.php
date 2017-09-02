<?php

/*
 * This file is part of the overtrue/easy-sms.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Overtrue\LaravelWechat\Events\OpenPlatform;

use EasyWeChat\Support\Collection;
use Illuminate\Queue\SerializesModels;

class UpdateAuthorized
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
