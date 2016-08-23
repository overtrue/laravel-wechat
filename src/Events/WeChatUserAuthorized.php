<?php

namespace Overtrue\LaravelWechat\Events;

use Illuminate\Queue\SerializesModels;
use Overtrue\Socialite\User;

class WeChatUserAuthorized
{
    use SerializesModels;

    public $user;
    public $isNewSession;

    /**
     * Create a new event instance.
     *
     * @param \Overtrue\Socialite\User $user
     * @param bool                     $isNewSession
     *
     * @return void
     */
    public function __construct(User $user, $isNewSession = false)
    {
        $this->user = $user;
        $this->isNewSession = $isNewSession;
    }

    /**
     * Retrieve the authorized user.
     *
     * @return \Overtrue\Socialite\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Check the user session is first created.
     *
     * @return bool
     */
    public function isNewSession()
    {
        return $this->isNewSession;
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
