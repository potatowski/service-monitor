<?php

namespace App\Controller\Api;

use App\Exception\ResponseException;
use App\Service\UserService;
use App\Traiter\HttpStatusCodeExceptionTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/user")
 */
class UserController extends AbstractController
{
    use HttpStatusCodeExceptionTrait;

    /**
     * @Route("/create", name="api_user_create", methods={"POST"})
     */
    public function createUser(Request $request, UserService $userService): Response
    {
        try {
            $params = json_decode($request->getContent() ?? [], true);
            $user = $userService->createUser($params);

            return $this->json(['user' => $user->getId()], Response::HTTP_CREATED);
        } catch(\Exception $e) {
            if ($e instanceof ResponseException) return $e->getResponse();
            $code = HttpStatusCodeExceptionTrait::getHttpStatusCode($e->getCode());
            return $this->json(['error' => $e->getMessage()], $code);
        }
    }
}
