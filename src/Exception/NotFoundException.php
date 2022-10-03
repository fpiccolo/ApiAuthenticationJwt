<?php
declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class NotFoundException extends \Exception
{
    protected $code = Response::HTTP_NOT_FOUND;

    public function __construct(string $message = "")
    {
        parent::__construct($message, $this->code);
    }
}