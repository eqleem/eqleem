<?php

namespace App\Actions;

use Illuminate\Support\Facades\Hash;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use App\Models\User;

class CreateUser
{
    use AsAction, WithAttributes;

    public function rules(): array
    {
        return [
            'name' => ['required', 'min:2', 'max:155'],
            'email' => ['required', 'email', 'unique:users,email', 'max:255'],
            'password' => ['required', 'min:6', 'max:155'],
        ];
    }

    public function handle(array $data): User
    {
        $this->fill($data);
        $validatedData = $this->validateAttributes();
        
        $validatedData['password'] = Hash::make($validatedData['password']);

        return User::create($validatedData);
    }

}
