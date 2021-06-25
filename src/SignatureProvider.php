<?php

namespace Signature;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Signature\Middleware\SignatureAuthenticate;

class SignatureProvider extends ServiceProvider
{

    protected $middlewares = [
        'auth.signature' => SignatureAuthenticate::class
    ];

    public function register()
    {

    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->publishes([
            __DIR__ . '/../config/signature.php' => config_path('signature.php'),
        ]);
        $this->aliasMiddlewares();
        $this->registerGuards();
    }

    protected function aliasMiddlewares()
    {
        $router = $this->app['router'];
        $method = method_exists($router, 'aliasMiddleware') ? 'aliasMiddleware' : 'middleware';
        foreach ($this->middlewares as $alias => $middleware) {
            $router->$method($alias, $middleware);
        }
    }

    protected function registerGuards()
    {
        Auth::extend('signature', function ($app, $name, $config) {
            return new SignatureGuard(
                new Signature(),
                $app['auth']->createUserProvider($config['provider']),
                $app['request']
            );
        });
    }


}
