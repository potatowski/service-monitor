<?php

namespace App\Controller\Api;

use App\Exception\ResponseException;
use App\Service\TokenService;
use App\Service\UserService;
use App\Traiter\HttpStatusCodeExceptionTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthenticatorController extends AbstractController
{
    use HttpStatusCodeExceptionTrait;

    /**
     * @Route("/api/auth", name="api_authenticator", methods={"POST"})
     */
    public function auth(Request $request, UserService $userService, TokenService $tokenService): Response
    {
        try {
            $data = json_decode($request->getContent() ?? [], true);
            $user = $userService->login($data);

            $data = $tokenService->generate($user);

            return $this->json($data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            if ($e instanceof ResponseException) return $e->getResponse();
            $httpCode = HttpStatusCodeExceptionTrait::getHttpStatusCode($e->getCode());
            return $this->json(['error' => $e->getMessage()], $httpCode);
        }
    }
}
