<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $user = Auth::user();

        // Check if user has the required role
        if ($user->role !== $role) {
            // Redirect based on user's actual role
            switch ($user->role) {
                case 'admin':
                    return redirect()->route('admin.dashboard')->with('error', 'Access denied. Admin area only.');
                case 'farmer':
                    return redirect()->route('farmer.dashboard')->with('error', 'Access denied. Farmer area only.');
                case 'buyer':
                    return redirect()->route('buyer.dashboard')->with('error', 'Access denied. Buyer area only.');
                case 'driver':
                    return redirect()->route('driver.dashboard')->with('error', 'Access denied. Driver area only.');
                default:
                    return redirect()->route('home')->with('error', 'Access denied.');
            }
        }

        return $next($request);
    }
}