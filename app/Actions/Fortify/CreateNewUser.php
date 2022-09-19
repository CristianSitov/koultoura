<?php

namespace App\Actions\Fortify;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use JsonException;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    /**
     * @throws ValidationException
     * @throws JsonException
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10'],
            'job' => ['required', 'string'],
            'institution' => ['required', 'string'],
            'event_2021.*' => ['required', 'numeric'],
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make(Str::uuid()),
        ]);

        Profile::create([
            'user_id' => $user->id,
            'phone' => $input['phone'],
            'job' => $input['job'],
            'institution' => $input['institution'],
            'event_2021' => json_encode(['days' => $input['day']], JSON_THROW_ON_ERROR),
        ]);

        return $user;
    }
}
