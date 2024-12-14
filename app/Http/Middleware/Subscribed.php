<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Subscribed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && ! $request->user()?->subscribed('premium_plan')){
            // ユーザーを有料プラン登録ページへリダイレクトし、有料プランに登録するか尋ねる
            return redirect('subscription/create');
        }

        return $next($request);
    }
}
