<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetUserLocale
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = null;

        if ($request->user() && $request->user()->locale) {
            $locale = $request->user()->locale;
        } elseif ($request->hasSession() && $request->session()->has('app_locale')) {
            $locale = $request->session()->get('app_locale');
        }

        $allowed = ['sk', 'en'];

        if (!$locale || !in_array($locale, $allowed, true)) {
            $locale = config('app.locale');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
