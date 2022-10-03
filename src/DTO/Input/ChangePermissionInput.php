<?php
declare(strict_types=1);

namespace App\DTO\Input;

/**
 * @codeCoverageIgnore
 */
class ChangePermissionInput
{
    public string $email;
    public array $roles;
}