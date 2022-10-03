<?php
declare(strict_types=1);

namespace App\Enum;

/**
 * @codeCoverageIgnore
 */
enum Roles: string
{
    case ROLE_ADMIN = 'ROLE_ADMIN';
    case ROLE_USER = 'ROLE_USER';
}