<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener(event: 'kernel.exception')]
class ValidationExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof UnprocessableEntityHttpException) {
            $previous = $exception->getPrevious();
            
            if ($previous instanceof ValidationFailedException) {
                $violations = $previous->getViolations();
                $errors = [];

                foreach ($violations as $violation) {
                    $field = $violation->getPropertyPath();
                    $message = $violation->getMessage();
                    
                    if (!isset($errors[$field])) {
                        $errors[$field] = [];
                    }
                    $errors[$field][] = $message;
                }

                $response = new JsonResponse([
                    'error' => 'Validation failed',
                    'details' => $errors
                ], 422);

                $event->setResponse($response);
            }
        }
    }
}