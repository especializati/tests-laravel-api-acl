<?php

namespace App\DTO\Users;

class CreateUserDTO
{
    public function __construct(
        readonly public string $name,
        readonly public string $email,
        readonly public string $password,
    ) {
        //
    }
}