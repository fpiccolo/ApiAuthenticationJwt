<?php
declare(strict_types=1);

namespace App\Exception;

class UserNotFoundException extends NotFoundException
{
    public function __construct(string $email)
    {
        parent::__construct("User [$email] not found");
    }
}