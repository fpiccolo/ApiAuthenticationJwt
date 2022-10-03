<?php
declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class BadRequestException extends \Exception
{
    protected $code = Response::HTTP_BAD_REQUEST;

    public function __construct(string $message = "")
    {
        parent::__construct($message, $this->code);
    }
}