<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Domain\Common\Cargo;
use Illuminate\Support\Facades\Hash;

class RegisterUserAction
{
    public function execute(array $data): User
    {
        return User::create([
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'nome' => $data['nome'],
            'sobrenome' => $data['sobrenome'],
            'telefone' => $data['telefone'] ?? null,
            'profissao' => $data['profissao'] ?? null,
            'staff' => false,
            'cargo' => Cargo::CLIENTE,
        ]);
    }
}
