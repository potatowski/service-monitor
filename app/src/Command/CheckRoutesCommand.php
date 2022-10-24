<?php

namespace App\Command;

use App\Entity\Registry;
use App\Repository\RouteRepository;
use App\Service\RegistryService;
use App\Service\RouteService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckRoutesCommand extends Command
{
    protected static $defaultName = 'app:check-routes';
    protected static $defaultDescription = 'Command to checker registered routes';

    private $routeRepository;
    private $routeService;
    private $registryService;

    public function __construct(
        RouteRepository $routeRepository,
        RouteService $routeService,
        RegistryService $registryService
    )
    {
        parent::__construct();
        $this->routeRepository = $routeRepository;
        $this->routeService = $routeService;
        $this->registryService = $registryService;
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $routes = $this->routeRepository->findBy(['removed' => false]);

        $routesConsulted = 0;
        $routesNotSave = 0;
        foreach ($routes as $route) {
            $io->writeln('Name: '. $route->getName());
            $data = $this->routeService->checkRoute($route);

            $registry = $this->registryService->newRegistry($route, $data);
            if (is_null($registry)) {
                $io->error('Error to create registry');
                $routesNotSave++;
                continue;
            }

            $io->writeln('URL: ' . $data['copyUrl']);
            $io->writeln('Status: ' . $data['httpStatusCode']);
            $io->writeln('Time: ' . $data['timeExecution']);
            $io->writeln('Repeated: ' . $data['repeatedStatus']);
            $io->writeln('');
            $routesConsulted++;
        }

        $io->success('Routes consulted: ' . $routesConsulted);

        return Command::SUCCESS;
    }
}
