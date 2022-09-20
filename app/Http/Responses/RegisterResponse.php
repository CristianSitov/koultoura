<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Auth\StatefulGuard;
use Laravel\Fortify\Http\Responses\RegisterResponse as FortifyRegisterResponse;

class RegisterResponse extends FortifyRegisterResponse
{
    protected StatefulGuard $guard;

    public function __construct(StatefulGuard $guard)
    {
        $this->guard = $guard;
    }

    public function toResponse($request)
    {
        $userId = $this->guard->user()->slug;

        $this->guard->logout();

        return redirect()->route('confirmation', ['id' => $userId]);
    }
}
