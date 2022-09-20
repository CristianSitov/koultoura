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
        $input['name'] = implode(' ', [$input['first_name'], $input['last_name']]);
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10'],
            'job' => ['required', 'string'],
            'organization' => ['required', 'string'],
            'country' => ['required', 'string'],
            'event_details.*' => ['required', 'numeric', 'in:1,2,3'],
        ])->validate();

        $user = User::create([
            'slug' => Str::uuid(),
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make(Str::uuid()),
        ]);

        Profile::create([
            'user_id' => $user->id,
            'phone' => $input['phone'],
            'job' => $input['job'],
            'organization' => $input['organization'],
            'country' => $input['country'],
            'event_details' => json_encode(['days' => $input['event_details']], JSON_THROW_ON_ERROR),
        ]);

        return $user->load('profile');
    }
}
