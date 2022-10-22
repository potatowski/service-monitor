<?php

namespace App\Service;

use App\Entity\Registry;
use App\Entity\Route;
use App\Repository\MessageRepository;
use App\Repository\RegistryRepository;

use function PHPSTORM_META\type;

class RegistryService
{
    private $registryRepository;
    private $messageRepository;

    public function __construct(
        RegistryRepository $registryRepository,
        MessageRepository $messageRepository
    )
    {
        $this->registryRepository = $registryRepository;
        $this->messageRepository = $messageRepository;
    }

    public function newRegistry(Route $route, array $data): ?Registry
    {
        try {
            $registry = new Registry;
            $registry->setRoute($route);
            $registry->setHttpStatusCode($data['httpStatusCode']);
            $registry->setCopyUrl($data['copyUrl']);
            $registry->setTimeExecution($data['timeExecution']);
            $registry->setRepeatedStatus($data['repeatedStatus']);
            $registry->setCreateAt(new \DateTime);

            $message = $this->messageRepository->findOneBy(['identifier' => $data['message']]);

            if (is_null($message)) {
                throw new \Exception('Message not found');
            }

            $registry->setMessage($message);
            $this->registryRepository->add($registry, true);

            return $registry;
        } catch (\Exception $e) {
            var_dump($e->getMessage());exit;
            return null;
        }
    }
}