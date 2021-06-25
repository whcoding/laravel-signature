<?php


namespace Signature;

use Illuminate\Contracts\Auth\UserProvider;

/**
 * 签名验证器
 * Class Signature
 * @package Signature
 */
class Signature
{
    const MUST_REQUEST_PARAMS = ['ts', 'user_id', 'sign'];

    /**
     * @var UserProvider
     */
    protected $provider;

    protected $request;

    protected $errMessage;

    protected $errCode;

    protected $user;

    public function setProvider($provider)
    {
        $this->provider = $provider;
        return $this;
    }

    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    public function validated()
    {
        $input = $this->request->all();
        $method = $this->request->method();
        $path = $this->request->path();

        return $this->validateRequest($input)
            && $this->validateRequestTime($input['ts'])
            && $this->validateUser($input['user_id'])
            && $this->validateLoginStatus()
            && $this->validateSign($input, $method, $path);
    }

    /**
     * 验证请求
     * @param $input
     * @return bool
     */
    public function validateRequest($input): bool
    {
        foreach (self::MUST_REQUEST_PARAMS as $params) {
            if (!isset($input[$params])) {
                $this->errCode = 40301;
                $this->errMessage = '缺少必要参数' . $params;
                return false;
            }
        }
        return true;
    }

    /**
     * 验证请求时间
     * @param $ts
     * @return bool
     */
    public function validateRequestTime($ts): bool
    {
        if (time() - $ts > config('signature.request_period')) {
            $this->errCode = 40302;
            $this->errMessage = '请求过期';
            return false;
        }
        return true;
    }

    /**
     * 验证用户
     * @param $userId
     * @return bool
     */
    public function validateUser($userId): bool
    {
        if (!$this->getUser($userId)) {
            $this->errCode = 40303;
            $this->errMessage = '用户不存在';
            return false;
        }
        return true;

    }


    public function getUser($userId)
    {
        return $this->user = $this->provider->retrieveById($userId);
    }

    /**
     * 验证登录是否过期
     * @return bool
     */
    public function validateLoginStatus(): bool
    {
        if ($this->user->token_expired_at < time()) {
            $this->errCode = 40304;
            $this->errMessage = '登录已过期';
            return false;
        }
        return true;
    }

    public function getUserId()
    {
        return $this->user->id;
    }


    public function validateSign($input, $method, $path)
    {
        $sign = $this->makeSign($input, $method, $path);
        if ($input['sign'] != $sign) {
            $this->errCode = 40305;
            if (env('APP_DEBUG')) {
                $this->errMessage = '签名验证失败  ' . $sign;
            } else {
                $this->errMessage = '签名验证失败';
            }
            return false;
        }
        return true;
    }

    /**
     * 生成sign
     * @param $input
     * @param $method
     * @param $path
     * @return string
     */
    public function makeSign($input, $method, $path)
    {
        unset($input['sign']);
        ksort($input);
        $str = '';
        foreach ($input as $key => $value) {
            $str .= $key . $value;
        }

        $str .= $this->user->api_token;
        $str .= $method;
        $str .= $path;

        return sha1($str);
    }

    public function getErrMessage()
    {
        return $this->errMessage;
    }

    public function getErrCode()
    {
        return $this->errCode;
    }


}
