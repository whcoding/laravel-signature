<?php

return [
    // token 有效期
    'token_expired_at' => env('TOKEN_EXPIRED_AT', '86400'),
    // 请求有效期
    'request_period' => env('REQUEST_PERIOD', '30')
];
