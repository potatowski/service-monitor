<?php

namespace App\Exception;

use App\Traiter\HttpStatusCodeExceptionTrait;
use Symfony\Component\HttpFoundation\JsonResponse;

class ResponseException extends \Exception
{
    use HttpStatusCodeExceptionTrait;
    private array $_data = [];

    public function __construct(string $message, int $httpCode = JsonResponse::HTTP_BAD_REQUEST, array $data = [])
    {
        $this->_data = $data;
        parent::__construct($message, $httpCode);
    }

    public function getData()
    {
        return $this->_data;
    }

    public function getResponse(): JsonResponse
    {
        $httpCode = HttpStatusCodeExceptionTrait::getHttpStatusCode($this->code);
        $data = [
            'error' => $this->getMessage(),
            'data' => $this->getData()
        ];

        return new JsonResponse($data, $httpCode);
    }
}