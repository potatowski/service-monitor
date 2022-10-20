<?php

namespace App\Service;

use App\Entity\Route;
use App\Repository\RouteRepository;
use Symfony\Component\HttpFoundation\Response;

class RouteService
{
    private $routeRepository;
    public function __construct(RouteRepository $routeRepository)
    {
        $this->routeRepository = $routeRepository;
    }

    public function newRoute(array $params): array
    {
        $this->validSaveParams($params);

        $route = new Route();
        $route->setName($params['name']);
        $route->setUrl($params['url']);
        
        $this->routeRepository->add($route, true);
        return ['route' => $route->getId()];
    }

    private function validSaveParams(array $params): void
    {
        if (!isset($params['name']) || !isset($params['url'])) {
            throw new \Exception('Invalid params', Response::HTTP_BAD_REQUEST);
        }

        if (!filter_var($params['url'], FILTER_VALIDATE_URL)) {
            throw new \Exception('Invalid url', Response::HTTP_BAD_REQUEST);
        }
    }
}