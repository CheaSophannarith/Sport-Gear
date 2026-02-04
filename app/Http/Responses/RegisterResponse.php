<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
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

        // Redirect based on user role after registration
        if ($user && $user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        // For customers, redirect to home or return JSON for API
        if ($request->wantsJson()) {
            return response()->json([
                'user' => $user,
            ]);
        }

        return redirect('/');
    }
}
