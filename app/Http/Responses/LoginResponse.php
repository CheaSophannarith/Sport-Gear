<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        // Redirect based on user role
        if ($user && $user->hasRole('admin')) {
            return redirect()->intended(route('admin.dashboard'));
        }

        // For customers, redirect to home or return JSON for API
        if ($request->wantsJson()) {
            return response()->json([
                'two_factor' => false,
                'user' => $user,
            ]);
        }

        return redirect()->intended('/');
    }
}
