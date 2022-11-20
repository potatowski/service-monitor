<?php

namespace App\Service;

use App\Entity\RequestMethod;
use App\Entity\Route;
use App\Exception\ResponseException;
use App\Repository\RegistryRepository;
use App\Repository\RequestMethodRepository;
use App\Repository\RouteRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RouteService
{
    private $routeRepository;
    private $requestMethodRepository;
    private $client;
    private $registryRepository;

    public function __construct(
        RouteRepository $routeRepository,
        RequestMethodRepository $requestMethodRepository,
        HttpClientInterface $client,
        RegistryRepository $registryRepository
    )
    {
        $this->routeRepository = $routeRepository;
        $this->requestMethodRepository = $requestMethodRepository;
        $this->client = $client;
        $this->registryRepository = $registryRepository;
    }

    public function newRoute(array $params): array
    {
        $this->validSaveParams($params);

        $route = new Route();
        $route->setName($params['name']);
        $route->setUrl($params['url']);
        $route->setCreateAt(new \DateTime());

        $requestMethod = $this->requestMethodRepository->findOneBy(['method' => $params['method']]);
        if (!$requestMethod) {
            throw new ResponseException('Request method not found', Response::HTTP_NOT_FOUND);
        }
        $route->setRequestMethod($requestMethod);

        if (isset($params['hasToken']) && $params['hasToken']) {
            $route->setHasToken($params['hasToken']);
            $route->setToken($params['token']);
            $route->setTypeToken($params['typeToken']);
        }

        $this->routeRepository->add($route, true);
        return ['route' => $route->getId()];
    }

    private function validSaveParams(array $params): void
    {
        if (!isset($params['name']) || !isset($params['url'])) {
            throw new ResponseException('Invalid params', Response::HTTP_BAD_REQUEST);
        }

        $params['url'] = str_contains($params['url'], 'http') ? $params['url'] : 'http://' . $params['url'];
        if (!filter_var($params['url'], FILTER_VALIDATE_URL)) {
            throw new ResponseException('Invalid url', Response::HTTP_BAD_REQUEST);
        }

        if (!isset($params['method']) || !in_array($params['method'], [RequestMethod::METHOD_GET, RequestMethod::METHOD_POST, RequestMethod::METHOD_PUT, RequestMethod::METHOD_PATCH])) {
            throw new ResponseException('Invalid method', Response::HTTP_BAD_REQUEST);
        }

        if (isset($params['hasToken']) && $params['hasToken']) {
            if (!isset($params['typeToken']) || !isset($params['token'])) {
                throw new ResponseException('Invalid token', Response::HTTP_BAD_REQUEST);
            }

            if (!in_array($params['typeToken'], [Route::TYPE_TOKEN_BEARER, Route::TYPE_TOKEN_BASIC])) {
                throw new ResponseException('Invalid type token', Response::HTTP_BAD_REQUEST);
            }
        }
    }

    public function editRoute(Route $route, array $params): void
    {
        $this->validEditParams($params);

        if (isset($params['name'])) {
            $route->setName($params['name']);
        }

        if (isset($params['url'])) {
            $route->setUrl($params['url']);
        }

        if (isset($params['method'])) {
            $requestMethod = $this->requestMethodRepository->findOneBy(['method' => $params['method']]);
            if (!$requestMethod) {
                throw new ResponseException('Request method not found', Response::HTTP_NOT_FOUND);
            }

            $route->setRequestMethod($requestMethod);
        }
        
        if (isset($params['hasToken'])) {
            $route->setHasToken($params['hasToken']);
        }

        if (isset($params['typeToken'])) {
            $route->setTypeToken($params['typeToken']);
        }

        if (isset($params['token'])) {
            $route->setToken($params['token']);
        }
        try {
            $this->routeRepository->flush();
        } catch (\Exception $e) {
            throw new ResponseException('Error saving route', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return;
    }

    private function validEditParams(array $params): void
    {
        if (isset($params['url'])) {
            $url = str_contains($params['url'], 'http') ? $params['url'] : 'http://' . $params['url'];
            if (!filter_var($$url, FILTER_VALIDATE_URL)) {
                throw new ResponseException('Invalid url', Response::HTTP_BAD_REQUEST);
            }
        }

        if (isset($params['method']) && !in_array($params['method'], [RequestMethod::METHOD_GET, RequestMethod::METHOD_POST, RequestMethod::METHOD_PUT, RequestMethod::METHOD_PATCH])) {
            throw new ResponseException('Invalid method', Response::HTTP_BAD_REQUEST);
        }

        if (isset($params['hasToken']) && $params['hasToken']) {
            if (!isset($params['typeToken']) || !isset($params['token'])) {
                throw new ResponseException('Invalid token', Response::HTTP_BAD_REQUEST);
            }

            if (!in_array($params['typeToken'], [Route::TYPE_TOKEN_BEARER, Route::TYPE_TOKEN_BASIC])) {
                throw new ResponseException('Invalid type token', Response::HTTP_BAD_REQUEST);
            }
        }
    }

    public function deleteRoute(Route $route): void
    {
        try {
            $this->routeRepository->remove($route, true);
        } catch (\Exception $e) {
            throw new ResponseException('Error deleting route', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return;
    }

    public function checkRoute(Route $route): array
    {
        try {
            $dataAuth = [];
            if ($route->getHasToken()) {
                $dataAuth = [
                    'headers' => [
                        'Authorization' => $route->getAuthorization()
                    ]
                ];
            }

            $response = $this->client->request(
                $route->getMethod(),
                $route->getUrl(),
                $dataAuth
            );

            $routeResponse = new RouteResponse($response);
            $lastRegistry = $route->getLastRegistry();
            $repeatedStatus = 1;
            if ($lastRegistry) {
                if ($lastRegistry->getMessageIdetifier() == $routeResponse->getMessage()) {
                    $repeatedStatus += $lastRegistry->getRepeatedStatus();
                }
            }

            return array_merge($routeResponse->_toArray(), ['repeatedStatus' => $repeatedStatus]);
        } catch (\Exception $e) {
            throw new ResponseException('Error checking route', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getStatus(Route $route): array
    {
        $data = [];
        $day = $this->registryRepository->getStatus($route, new \DateTime('-1 day'));
        $week = $this->registryRepository->getStatus($route, new \DateTime('-7 days'));
        $month = $this->registryRepository->getStatus($route, new \DateTime('-30 days'));

        $data['day'] = $this->formatDataStatus($day);
        $data['week'] = $this->formatDataStatus($week);
        $data['month'] = $this->formatDataStatus($month);
        return $data;
    }

    private function formatDataStatus(array $data)
    {
        $dataStatus = [
            'success' => 0,
            'limited' => 0,
            'failed' => 0
        ];

        if ($data['total'] <= 0) {
            return $dataStatus;
        }

        $dataStatus['success'] = round($data['success']*100/($data['total']),3);
        $dataStatus['limited'] = round($data['limited']*100/($data['total']),3);
        $dataStatus['failed'] = round($data['failed']*100/($data['total']),3);

        return $dataStatus;
    }
}