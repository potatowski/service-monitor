<?php 

namespace App\Traiter;

use Symfony\Component\HttpFoundation\Response;

trait HttpStatusCodeExceptionTrait
{
    public static function getHttpStatusCode(int $code): int
    {
        $statusText = Response::$statusTexts[$code] ?? null;

        if ($statusText) {
            return $code;
        }

        return Response::HTTP_BAD_REQUEST;
    }
}
