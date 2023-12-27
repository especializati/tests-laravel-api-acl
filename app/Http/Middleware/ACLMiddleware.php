<?php

namespace App\Http\Middleware;

use App\Repositories\UserRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class ACLMiddleware
{
    public function __construct(private UserRepository $userRepository)
    {
        
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = Route::currentRouteName();
        if (!$this->userRepository->hasPermissions($request->user(), $routeName)) {
            abort(403, 'Not authorized');
        }
        return $next($request);
    }
}
