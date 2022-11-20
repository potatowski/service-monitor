<?php

namespace App\Service;

use App\Entity\User;
use App\Exception\ResponseException;
use App\Repository\UserRepository;
use Lcobucci\Clock\FrozenClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;

class TokenService
{
    private $userRepository;

    public function __construct(
        UserRepository $userRepository,
        ParameterBagInterface $paramsBag
    )
    {
        $this->userRepository = $userRepository;
        $paramsBag->get('jwt_secret');
        $this->key = $paramsBag->get('jwt_secret');
    }

    public function generate(User $user): ?array
    {
        $key = InMemory::base64Encoded($this->key);
        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            $key,
        );

        $timeNow = new \DateTimeImmutable('now');
        try {
            $token = $config->builder()
                        ->withClaim('email', $user->getEmail())
                        ->withClaim('user_id', $user->getID())
                        ->expiresAt($timeNow->modify('+30 days'))
                        ->getToken($config->signer(), $config->signingKey());
                    
            $data['userId'] = $user->getId();
            $data['token'] = $token->toString();
            $data['expireIn'] = date_timestamp_get($token->claims()->get('exp'));

            return $data;
        } catch (\Exception $e) {
            throw new ResponseException('Error generating token', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    public function getUser(array $token): ?User
    {
        list($type, $token) = $token;
        if ($type != "Bearer") {
            throw new ResponseException('Not contain a Bearer Token', Response::HTTP_FORBIDDEN);
        }
        
        if($user = $this->unencryptedToken($token)){
            return $user;
        }
        
        return null;
    }

    private function unencryptedToken(string $token): ?User
    {
        $key = InMemory::base64Encoded($this->key);
        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            $key,
        );

        $token = $config->parser()->parse($token);
        if (assert($token instanceof UnencryptedToken)) {
            $clock = new FrozenClock(new \DateTimeImmutable('now'));
            $constraint = new LooseValidAt($clock);
            if(!$config->validator()->validate($token, $constraint)) {
                throw new ResponseException('Token expired');
            }

            $userId = $token->claims()->get('user_id') ?? $token->claims()->get('merchant_id');
            $user = $this->userRepository->findOneBy(['id' => $userId]);
            if (is_null($user)) {
                throw new ResponseException('User not found', Response::HTTP_NOT_FOUND);
            }

            return $user;
        }
        
        return null;
    }
}
