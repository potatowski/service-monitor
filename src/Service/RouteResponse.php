<?php

namespace App\Service;

use App\Entity\Message;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

class RouteResponse
{
    const MS_TO_LIMITED = 5000;

    private int $httpStatusCode;
    private int $timeExecution;
    private string $copyUrl;
    private int $repeatedStatus = 1;
    private string $message;

    public function __construct(ResponseInterface $response = null)
    {
        if (!is_null($response)) {
            $this->prepare($response);
        }
    }

    private function prepare(ResponseInterface $response)
    {
        $this->copyUrl = $response->getInfo('url');
        $startTime = $response->getInfo('start_time');
        $this->httpStatusCode =  $response->getStatusCode();
        $this->timeExecution = round((microtime(true) - $startTime) * 1000);
    }

    public static function defaultResponse(ResponseInterface $response)
    {
        $routeResponse = new RouteResponse;
        $routeResponse->setTimeExecution(0);
        $routeResponse->setCopyUrl($response->getInfo('url'));
        $statusCode = $response->getStatusCode();
        $routeResponse->setHttpStatusCode($statusCode);

        $message = Message::MESSAGE_SUCESS;
        if ($routeResponse->getTimeExecution() > self::MS_TO_LIMITED) {
            $message = Message::MESSAGE_LIMITED;
        }

        if ($statusCode >= Response::HTTP_INTERNAL_SERVER_ERROR) {
            $message = Message::MESSAGE_FAILED;
        }

        $routeResponse->setMessage($message);
        return $routeResponse;
    }

    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    public function setHttpStatusCode(int $httpStatusCode): void
    {
        $this->httpStatusCode = $httpStatusCode;
    }

    public function getTimeExecution(): int
    {
        return $this->timeExecution;
    }

    public function setTimeExecution(int $timeExecution): void
    {
        $this->timeExecution = $timeExecution;
    }

    public function getCopyUrl(): string
    {
        return $this->copyUrl;
    }

    public function setCopyUrl(string $copyUrl): void
    {
        $this->copyUrl = $copyUrl;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        if (!in_array($message, [Message::MESSAGE_FAILED, Message::MESSAGE_LIMITED, Message::MESSAGE_SUCESS])) {
            throw new \Exception('Invalid message');
        }

        $this->message = $message;
    }


    public function _toArray()
    {
        return [
            'httpStatusCode' => $this->httpStatusCode,
            'timeExecution' => $this->timeExecution,
            'copyUrl' => $this->copyUrl,
            'repeatedStatus' => $this->repeatedStatus
        ];
    }
}
