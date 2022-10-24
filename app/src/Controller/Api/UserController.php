<?php

namespace App\Controller\Api;

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
     * @Route("", name="api_user_create", methods={"POST"})
     */
    public function createUser(Request $request, UserService $userService): Response
    {
        try {
            $params = json_decode($request->getContent() ?? [], true);
            $user = $userService->createUser($params);

            return $this->json(['user' => $user->getId()], Response::HTTP_CREATED);
        } catch(\Exception $e) {
            $code = HttpStatusCodeExceptionTrait::getHttpStatusCode($e->getCode());
            return $this->json(['message' => $e->getMessage()], $code);
        }
    }
}
