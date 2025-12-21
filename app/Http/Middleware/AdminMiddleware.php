<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // إضافة هذه المكتبة

class AdminMiddleware
{
    /**
     * معالجة طلب وارد.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. التحقق أن المستخدم مسجل دخول
        if (!Auth::check()) {
            // For API requests, return JSON response
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated. Please login first.'
                ], 401);
            }
            // For web requests, redirect to login
            return redirect('/login'); 
        }

        // 2. التحقق أن المستخدم أدمن (is_admin = true)
        if (!Auth::user()->is_admin) {
            // For API requests, return JSON response
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthorized. Admin access required.'
                ], 403);
            }
            // For web requests, redirect with error
            return redirect('/')->with('error', 'ليس لديك صلاحية الوصول لهذه الصفحة.');
        }

        // 3. إذا كان أدمن، اسمح بالمرور
        return $next($request);
    }
}