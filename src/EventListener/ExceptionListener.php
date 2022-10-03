<?php
declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

/**
 * @codeCoverageIgnore
 */
class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        $code = $exception->getCode();

        $statusCode = key_exists($code, Response::$statusTexts) ? $code : Response::HTTP_INTERNAL_SERVER_ERROR;

        $customResponse = new JsonResponse(
            ['code' => $code, 'message' => $exception->getMessage()],
            $statusCode
        );

        $event->setResponse($customResponse);

    }
}