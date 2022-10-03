<?php
declare(strict_types=1);

namespace App\DTO\Input;

/**
 * @codeCoverageIgnore
 */
class ChangePasswordInput
{
    public string $email;
    public string $password;
}