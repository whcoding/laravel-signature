<?php

namespace Signature\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SignatureAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $guard = Auth::guard();
        if (!$guard->validated()) {
            return  response()->json([
                'code' => $guard->getErrCode(),
                'message' => $guard->getErrMessage()
            ]);
        }

        return $next($request);
    }
}
