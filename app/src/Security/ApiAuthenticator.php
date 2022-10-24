<?php

namespace App\Security;

use App\Service\TokenService;
use App\Traiter\HttpStatusCodeExceptionTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiAuthenticator extends AbstractAuthenticator
{
    use HttpStatusCodeExceptionTrait;

    private $tokenService;
    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    public function supports(Request $request): ?bool
    {
        $auth = $request->headers->get('Authorization');
        return $auth && strpos($auth, 'Bearer ') === 0;
    }

    public function authenticate(Request $request): Passport
    {
        $auth = $request->headers->get('Authorization');
        $authorization = explode(' ', $auth);

        $user = null;
        try {
            $user = $this->tokenService->getUser($authorization);
        } catch (\Exception $e) {
            $httpCode = HttpStatusCodeExceptionTrait::getHttpStatusCode($e->getCode());
            throw new CustomUserMessageAuthenticationException($e->getMessage(), ['statusCode' => $httpCode]);
        }

        if (is_null($user)) {
            throw new CustomUserMessageAuthenticationException('User not found', ['statusCode' => Response::HTTP_NOT_FOUND]);
        }

        return new SelfValidatingPassport(new UserBadge($user->getEmail()));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'error' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];
        $messageData = $exception->getMessageData();
        return new JsonResponse($data, $messageData['statusCode']);
    }
}
