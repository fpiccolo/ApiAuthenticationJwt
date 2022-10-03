<?php
declare(strict_types=1);

namespace App\Exception;

class TokenNotValidException extends UnauthorizedException
{
    public function __construct(string $message = "")
    {
        parent::__construct("Token not valid: ". $message);
    }
}