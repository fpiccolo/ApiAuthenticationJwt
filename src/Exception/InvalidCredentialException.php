<?php
declare(strict_types=1);

namespace App\Exception;

class InvalidCredentialException extends BadRequestException
{
    public function __construct()
    {
        parent::__construct('Invalid credentials');
    }
}