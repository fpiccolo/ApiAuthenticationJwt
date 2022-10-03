<?php
declare(strict_types=1);

namespace App\Service;

class PasswordEncrypt
{
    public function encrypt(string $password): string
    {
        return md5($password);
    }
}