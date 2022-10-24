<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;

class UserService
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function createUser(array $data): User
    {
        $this->validateNewUser($data);
        $user = new User;
        $user->setName($data['name']);
        $user->setEmail($data['email']);
        $user->setPassword($data['password']);
        $this->userRepository->add($user, true);

        return $user;
    }

    public function validateNewUser(array $data): void
    {
        if (!isset($data['name']) || !isset($data['email']) || !isset($data['password'])) {
            throw new \Exception('Missing parameters', Response::HTTP_BAD_REQUEST);
        }

        $email = $data['email'];
        $cleanEmail = filter_var($email,FILTER_SANITIZE_EMAIL);

        if (!$email == $cleanEmail || !filter_var($email,FILTER_VALIDATE_EMAIL)){
            throw new \Exception('Invalid email', Response::HTTP_BAD_REQUEST);
        }

        
        $password = $data['password'];
        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $number    = preg_match('@[0-9]@', $password);
        $specialChars = preg_match('@[^\w]@', $password);

        if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
            throw new \Exception('Invalid password', Response::HTTP_BAD_REQUEST);
        }
    }
}