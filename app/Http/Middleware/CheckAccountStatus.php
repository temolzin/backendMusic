<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAccountStatus
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user) {
            if ($user->account_status === 'restricted') {
                if (!$request->isMethod('get')) {
                    
                    $restrictedPatterns = [
                        '*/artist/approval/*',
                        '*/artist/offers*',
                        
                        '*/cliente/shopping_card/create',
                        '*/process-payment*',
                        '*/payment/cash*',
                    ];

                    foreach ($restrictedPatterns as $pattern) {
                        if ($request->is($pattern)) {
                            return response()->json([
                                'success' => false,
                                'error' => 'ACCOUNT_RESTRICTED',
                                'message' => 'Función deshabilitada: Tu cuenta está restringida. Puedes gestionar tus eventos en curso, pero no puedes realizar nuevas transacciones u ofertas.'
                            ], 403);
                        }
                    }
                }
            }
        }
        
        return $next($request);
    }
}