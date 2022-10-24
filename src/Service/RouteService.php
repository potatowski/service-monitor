<?php

namespace App\Service;

use App\Entity\RequestMethod;
use App\Entity\Route;
use App\Repository\RequestMethodRepository;
use App\Repository\RouteRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RouteService
{
    private $routeRepository;
    private $requestMethodRepository;
    private $client;

    public function __construct(
        RouteRepository $routeRepository,
        RequestMethodRepository $requestMethodRepository,
        HttpClientInterface $client
    )
    {
        $this->routeRepository = $routeRepository;
        $this->requestMethodRepository = $requestMethodRepository;
        $this->client = $client;
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
            throw new \Exception('Request method not found', Response::HTTP_NOT_FOUND);
        }

        $route->setRequestMethod($requestMethod);

        $this->routeRepository->add($route, true);
        return ['route' => $route->getId()];
    }

    private function validSaveParams(array $params): void
    {
        if (!isset($params['name']) || !isset($params['url'])) {
            throw new \Exception('Invalid params', Response::HTTP_BAD_REQUEST);
        }

        $params['url'] = str_contains($params['url'], 'http') ? $params['url'] : 'http://' . $params['url'];
        if (!filter_var($params['url'], FILTER_VALIDATE_URL)) {
            throw new \Exception('Invalid url', Response::HTTP_BAD_REQUEST);
        }

        if (!isset($params['method']) || !in_array($params['method'], [RequestMethod::METHOD_GET, RequestMethod::METHOD_POST, RequestMethod::METHOD_PUT, RequestMethod::METHOD_PATCH])) {
            throw new \Exception('Invalid method', Response::HTTP_BAD_REQUEST);
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
                throw new \Exception('Request method not found', Response::HTTP_NOT_FOUND);
            }

            $route->setRequestMethod($requestMethod);
        }    
        
        try {
            $this->routeRepository->flush();
        } catch (\Exception $e) {
            throw new \Exception('Error saving route', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return;
    }

    private function validEditParams(array $params): void
    {
        $params['url'] = str_contains($params['url'], 'http') ? $params['url'] : 'http://' . $params['url'];
        if (isset($params['url']) && !filter_var($params['url'], FILTER_VALIDATE_URL)) {
            throw new \Exception('Invalid url', Response::HTTP_BAD_REQUEST);
        }

        if (isset($params['method']) && !in_array($params['method'], [RequestMethod::METHOD_GET, RequestMethod::METHOD_POST, RequestMethod::METHOD_PUT, RequestMethod::METHOD_PATCH])) {
            throw new \Exception('Invalid method', Response::HTTP_BAD_REQUEST);
        }
    }

    public function deleteRoute(Route $route): void
    {
        try {
            $this->routeRepository->remove($route, true);
        } catch (\Exception $e) {
            throw new \Exception('Error deleting route', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return;
    }

    public function checkRoute(Route $route): array
    {
        try {
            $response = $this->client->request(
                $route->getMethod(),
                $route->getUrl()
            );

            $routeResponse = new RouteResponse($response);
            $lastRegistry = $route->getLastRegistry();
            $repeatedStatus = 1;
            if ($lastRegistry) {
                if ($lastRegistry->getMessageIdetifier() === $routeResponse->getMessage()) {
                    $repeatedStatus += $lastRegistry->getRepeatedStatus();
                }
            }

            return array_merge($routeResponse->_toArray(), ['repeatedStatus' => $repeatedStatus]);
        } catch (\Exception $e) {
            throw new \Exception('Error checking route', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}