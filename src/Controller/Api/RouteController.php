<?php

namespace App\Controller\Api;

use App\Entity\Route;
use App\Service\RouteService;
use App\Traiter\HttpStatusCodeExceptionTrait;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;


/**
 * @Route("/api/route", name="api_route_")
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
     * @Route("", name="get_routes", methods={"GET"})
     */
    public function routes(NormalizerInterface $normalizer): Response
    {
        try {
            $routes = $this->managerRegistry->getRepository(Route::class)->findAll();
            
            return $this->json($normalizer->normalize($routes, null, ['groups' => 'route']));
        } catch (\Exception $e) {
            $code = HttpStatusCodeExceptionTrait::getHttpStatusCode($e->getCode());
            return $this->json(['error' => $e->getMessage()], $code);
        }
    }

    /**
     * @Route("/api/route/{route}", name="get_route", methods={"GET"})
     */
    public function route(NormalizerInterface $normalizer, Route $route = null): Response
    {
        try {
            if (!$route) {
                throw new \Exception('Route not found', Response::HTTP_NOT_FOUND);
            }
            
            return $this->json($normalizer->normalize($route, null, ['groups' => 'route']));
        } catch (\Exception $e) {
            $code = HttpStatusCodeExceptionTrait::getHttpStatusCode($e->getCode());
            return $this->json(['error' => $e->getMessage()], $code);
        }
    }

    /**
     * @Route("/api/route", name="new_route", methods={"POST"})
     */
    public function newRoute(
        Request $request,
        NormalizerInterface $normalizer,
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
}
