<?php
declare(strict_types=1);

namespace App\Exception;

class NoApiTokenProvidedException extends UnauthorizedException
{
    protected $message = 'No API token provided';

    public function __construct()
    {
        parent::__construct($this->message);
    }
}