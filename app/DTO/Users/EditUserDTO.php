<?php

namespace App\DTO\Users;

class EditUserDTO
{
    public function __construct(
        readonly public string $id,
        readonly public string $name,
        readonly public ?string $password = null,
    ) {
        //
    }
}