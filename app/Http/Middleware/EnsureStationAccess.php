<?php

namespace App\Http\Middleware;

use App\Enums\Station;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStationAccess
{
    public function handle(Request $request, Closure $next, string $station): Response
    {
        $station = Station::from($station);
        $user = $request->user();

        if ($user === null || $user->role->value !== $station->value) {
            abort(403, 'غير مصرح لك بالدخول لهذه المحطة');
        }

        return $next($request);
    }
}
