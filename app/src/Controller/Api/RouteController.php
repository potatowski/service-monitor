<?php

namespace App\Controller\Api;

use App\Entity\Route as RouteEntity;
use App\Exception\ResponseException;
use App\Service\RouteService;
use App\Traiter\HttpStatusCodeExceptionTrait;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;


/**
 * @Route("/api/route")
 */
class RouteController extends AbstractController
{
    use HttpStatusCodeExceptionTrait;

    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }
    
    /**
     * @Route("", name="api_route_get_routes", methods={"GET"})
     */
    public function routes(NormalizerInterface $normalizer): Response
    {
        try {
            $routes = $this->managerRegistry->getRepository(RouteEntity::class)->findBy(['removed' => false], ['name' => 'ASC']);
            
            return $this->json(['routes' => $normalizer->normalize($routes, null, ['groups' => 'route'])]);
        } catch (\Exception $e) {
            $code = HttpStatusCodeExceptionTrait::getHttpStatusCode($e->getCode());
            return $this->json(['error' => $e->getMessage()], $code);
        }
    }

    /**
     * @Route("/{route}", name="api_route_get_route", methods={"GET"})
     */
    public function route(NormalizerInterface $normalizer, RouteEntity $route = null): Response
    {
        try {
            if (!$route) {
                throw new ResponseException('Route not found', Response::HTTP_NOT_FOUND);
            }
            
            return $this->json($normalizer->normalize($route, null, ['groups' => 'route']));
        } catch (\Exception $e) {
            if ($e instanceof ResponseException) return $e->getResponse();
            $code = HttpStatusCodeExceptionTrait::getHttpStatusCode($e->getCode());
            return $this->json(['error' => $e->getMessage()], $code);
        }
    }

    /**
     * @Route("", name="api_route_new", methods={"POST"})
     */
    public function newRoute(
        Request $request,
        RouteService $routeService
    ): Response
    {
        try {
            $params = json_decode($request->getContent() ?? [], true);
            $data = $routeService->newRoute($params);
            return $this->json($data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            $code = HttpStatusCodeExceptionTrait::getHttpStatusCode($e->getCode());
            return $this->json(['error' => $e->getMessage()], $code);
        }
    }

    /**
     * @Route("/{route}", name="api_route_update", methods={"PATCH"})
     */
    public function editRoute(
        Request $request,
        RouteService $routeService,
        RouteEntity $route = null
    ): Response
    {
        try {
            if (!$route) {
                throw new ResponseException('Route not found', Response::HTTP_NOT_FOUND);
            }

            $params = json_decode($request->getContent() ?? [], true);
            $routeService->editRoute($route, $params);
            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            if ($e instanceof ResponseException) return $e->getResponse();
            $code = HttpStatusCodeExceptionTrait::getHttpStatusCode($e->getCode());
            return $this->json(['error' => $e->getMessage()], $code);
        }
    }

    /**
     * @Route("/{route}", name="api_route_delete", methods={"DELETE"})
     */
    public function deleteRoute(
        RouteService $routeService,
        RouteEntity $route = null
    ): Response
    {
        try {
            if (is_null($route)) {
                throw new ResponseException('Route not found', Response::HTTP_NOT_FOUND);
            }

            $routeService->deleteRoute($route);
            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            if ($e instanceof ResponseException) return $e->getResponse();
            $code = HttpStatusCodeExceptionTrait::getHttpStatusCode($e->getCode());
            return $this->json(['error' => $e->getMessage()], $code);
        }
    }

    /**
     * @Route("/{route}/status", name="api_route_status", methods={"GET"})
     */
    public function status(
        RouteService $routeService,
        RouteEntity $route = null
    ): Response
    {
        try {
            if (is_null($route)) {
                throw new ResponseException('Route not found', Response::HTTP_NOT_FOUND);
            }

            $data = $routeService->getStatus($route);
            return $this->json($data);
        } catch (\Exception $e) {
            if ($e instanceof ResponseException) return $e->getResponse();
            $code = HttpStatusCodeExceptionTrait::getHttpStatusCode($e->getCode());
            return $this->json(['error' => $e->getMessage()], $code);
        }
    }
}
