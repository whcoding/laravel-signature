### laravel-signature 

laravel-signature 是一个接口签名验证包, 其中包括了对token生成, token过期时间, 请求的有效期, sign生成

#### 运行环境
- php >=7.3
- composer
- laravel >= 8.40
- .env 开启 debug 会返回正确sign, 生产环境记得关闭. 

#### 使用方法
`composer require laravel/signature`

设置 auth.php 
```
    'defaults' => [
        'guard' => 'api', // laravel 默认 session 更改为 api
        'passwords' => 'users',
    ],
    
    'guards' => [
        .....
        
        'api' => [
            'driver' => 'signature', // 更改为 signature
            'provider' => 'users',
            'hash' => false,
        ],
    ],

```

#### 配置文件
`
php artisan vendor:publish --provider="Signature\SignatureProvider"
`

#### 执行 migration
`
php artisan migrate
`
