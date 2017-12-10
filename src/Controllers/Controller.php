<?php

/*
 * This file is part of the overtrue/laravel-wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Overtrue\LaravelWeChat\Controllers;

use Barryvdh\Debugbar\LaravelDebugbar;

class Controller
{
    public function __construct()
    {
        if (class_exists(LaravelDebugbar::class)) {
            resolve(LaravelDebugbar::class)->disable();
        }
    }
}
