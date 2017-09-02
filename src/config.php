<?php

return [
    /**
     * 路由配置
     */
    'route' => [
        'enabled' => false,         // 是否开启路由
        'attributes' => [           // 路由 group 参数
            'prefix' => null,
            'middleware' => null,
            'as' => 'easywechat::',
        ],
        'open_platform_serve_url' => 'open-platform-serve', // 开放平台服务URL
    ],

    // 默认配置，将会合并到各模块中
    'defaults' => [
        /**
         * Debug 模式，bool 值：true/false
         *
         * 当值为 false 时，所有的日志都不会记录
         */
        'debug'  => true,

        /**
         * 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
         */
        'response_type' => 'array',

        /**
         * 使用 Laravel 的缓存系统
         */
        'use_laravel_cache' => true,

        /**
         * 日志配置
         *
         * level: 日志级别，可选为：
         *                 debug/info/notice/warning/error/critical/alert/emergency
         * file：日志文件位置(绝对路径!!!)，要求可写权限
         */
        'log' => [
            'level' => env('WECHAT_LOG_LEVEL', 'debug'),
            'file'  => env('WECHAT_LOG_FILE', storage_path('logs/wechat.log')),
        ],
    ],

    // 公众号
    'official_account' => [
        /**
         * 账号基本信息，请从微信公众平台/开放平台获取
         */
        'app_id'  => env('WECHAT_OFFICIAL_ACCOUNT_APPID', 'your-app-id'),         // AppID
        'secret'  => env('WECHAT_OFFICIAL_ACCOUNT_SECRET', 'your-app-secret'),     // AppSecret
        'token'   => env('WECHAT_OFFICIAL_ACCOUNT_TOKEN', 'your-token'),          // Token
        'aes_key' => env('WECHAT_OFFICIAL_ACCOUNT_AES_KEY', ''),                    // EncodingAESKey

        /**
         * OAuth 配置
         *
         * only_wechat_browser: 只在微信浏览器跳转
         * scopes：公众平台（snsapi_userinfo / snsapi_base），开放平台：snsapi_login
         * callback：OAuth授权完成后的回调页地址(如果使用中间件，则随便填写。。。)
         */
        // 'oauth' => [
        //     'only_wechat_browser' => false,
        //     'scopes'   => array_map('trim', explode(',', env('WECHAT_OFFICIAL_ACCOUNT_OAUTH_SCOPES', 'snsapi_userinfo'))),
        //     'callback' => env('WECHAT_OFFICIAL_ACCOUNT_OAUTH_CALLBACK', '/examples/oauth_callback.php'),
        // ],
    ],

    /**
     * 开放平台第三方平台配置信息
     */
    // 'open_platform' => [
    //     'app_id'  => env('WECHAT_OPEN_PLATFORM_APPID', ''),
    //     'secret'  => env('WECHAT_OPEN_PLATFORM_SECRET', ''),
    //     'token'   => env('WECHAT_OPEN_PLATFORM_TOKEN', ''),
    //     'aes_key' => env('WECHAT_OPEN_PLATFORM_AES_KEY', ''),
    // ],

    /**
     * 小程序配置信息
     */
    // 'mini_program' => [
    //     'app_id'  => env('WECHAT_MINI_PROGRAM_APPID', ''),
    //     'secret'  => env('WECHAT_MINI_PROGRAM_SECRET', ''),
    //     'token'   => env('WECHAT_MINI_PROGRAM_TOKEN', ''),
    //     'aes_key' => env('WECHAT_MINI_PROGRAM_AES_KEY', ''),
    // ],

    /*
     * 微信支付
     */
    // 'payment' => [
    //     'sandbox_mode'       => env('WECHAT_PAYMENT_SANDBOX', true),
    //     'app_id'             => env('WECHAT_PAYMENT_APPID', ''),
    //     'secret'             => env('WECHAT_PAYMENT_SECRET', ''),
    //     'merchant_id'        => env('WECHAT_PAYMENT_MERCHANT_ID', 'your-mch-id'),
    //     'key'                => env('WECHAT_PAYMENT_KEY', 'key-for-signature'),
    //     'cert_path'          => env('WECHAT_PAYMENT_CERT_PATH', 'path/to/cert/apiclient_cert.pem'),    // XXX: 绝对路径！！！！
    //     'key_path'           => env('WECHAT_PAYMENT_KEY_PATH', 'path/to/cert/apiclient_key.pem'),          // XXX: 绝对路径！！！！
    //     'notify_url'         => '/payments/wechat-notify', // 默认支付通知地址
    //     // 'device_info'     => env('WECHAT_PAYMENT_DEVICE_INFO', ''),
    //     // 'sub_app_id'      => env('WECHAT_PAYMENT_SUB_APP_ID', ''),
    //     // 'sub_merchant_id' => env('WECHAT_PAYMENT_SUB_MERCHANT_ID', ''),
    //     // ...
    // ],

    // 企业微信
    // 'we_work' => [
    //     // 企业 ID
    //     'corp_id' => 'xxxxxxxxxxxxxxxxx',

    //     // 应用列表
    //     'agents' => [
    //         'contacts' => [
    //             'agent_id' => 100020,
    //             'secret'   => env('WECHAT_WEWORK_AGENT_CONTACTS_SECRET', ''),
    //         ],
    //        //...
    //    ],
    // ],
];
