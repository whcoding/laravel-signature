<?php

namespace Signature;

use App\Models\User;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * 守卫者
 * Class SignatureGuard
 * @package Signature
 */
class SignatureGuard implements Guard
{
    use GuardHelpers;

    protected $signature;

    protected $request;

    protected $provider;

    protected $user;

    public function __construct(Signature $signature, UserProvider $provider, Request $request)
    {
        $this->signature = $signature;
        $this->provider = $provider;
        $this->request = $request;
        $this->signature->setRequest($this->request);
        $this->signature->setProvider($this->provider);
    }


    public function user(): ?Authenticatable
    {
        if ($this->user != null) {
            return $this->user;
        }

        $user = null;
        if ($this->signature->validated()) {
            $user = $this->provider->retrieveById($this->signature->getUserId());
        }

        return $this->user = $user;
    }

    public function validate(array $credentials = [])
    {
        return $this->attempt($credentials, false);
    }


    public function attempt($credentials, $login = true)
    {
        $user = $this->provider->retrieveByCredentials($credentials);
        if ($this->hasVaildCredentials($user, $credentials)) {
            return $this->login($user);
        }
        return false;
    }


    public function login($user)
    {
        $token = (!$user->api_token || $user->token_expored_at < time()) ? $this->refreshToken($user) : $user->api_token;
        $this->user = $user;
        return $token;
    }

    protected function refreshToken(User $user)
    {
        $user->api_token = Str::random(60);
        $user->token_expired_at = time() + config('signature.token_expired_at');
        $user->save();
        return $user->api_token;
    }

    protected function hasVaildCredentials($user, $credentials)
    {
        return $user !== null && $this->provider->validateCredentials($user, $credentials);
    }

    public function __call($method, $parameters)
    {
        if (method_exists($this->signature, $method)) {
            return call_user_func_array([$this->signature, $method], $parameters);
        }
        throw new  \BadMethodCallException("Method [$method], does not exist");
    }

}
