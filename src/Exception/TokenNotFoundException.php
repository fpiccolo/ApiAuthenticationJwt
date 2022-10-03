<?php
declare(strict_types=1);

namespace App\Exception;

class TokenNotFoundException extends UnauthorizedException
{
    protected $message = "Token not found";

    public function __construct()
    {
        parent::__construct($this->message);
    }
}