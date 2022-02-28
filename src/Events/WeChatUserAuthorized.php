<?php

/*
 * This file is part of the overtrue/laravel-wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Overtrue\LaravelWeChat\Events;

use Illuminate\Queue\SerializesModels;
use Overtrue\Socialite\User;

class WeChatUserAuthorized
{
    use SerializesModels;

    public $user;

    public $isNewSession;

    public $account;

    public function __construct(User $user, bool $isNewSession = false, string $account = '')
    {
        $this->user = $user;
        $this->isNewSession = $isNewSession;
        $this->account = $account;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getAccount(): string
    {
        return $this->account;
    }

    public function isNewSession(): bool
    {
        return $this->isNewSession;
    }
}
