<?php

namespace App\Command;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\PropertyService;
use App\Entity\Property;

#[AsCommand(name: 'sync:properties')]
class SyncPropertiesCommand extends Command
{
    private PropertyService $propertyService;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(PropertyService $propertyService, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        parent::__construct();
        $this->propertyService = $propertyService;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Fetching property data...</info>');

        try {
            $properties = $this->propertyService->fetchProperties();
            
            foreach ($properties as $propertyData) {
                $property = new Property();
                $property->setTitle($propertyData['name'] ?? '');
                $property->setLocation($propertyData['location'] ?? '');
                $property->setPrice($propertyData['price'] ?? 0);
                $property->setSize($propertyData['size'] ?? 0);
                
                $this->entityManager->persist($property);
            }
            
            $this->entityManager->flush();
            $output->writeln('<info>Properties synchronized successfully!</info>');
            $this->logger->info('Properties synchronized successfully');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Failed to sync properties: ' . $e->getMessage() . '</error>');
            $this->logger->error('Failed to sync properties', ['error' => $e->getMessage()]);
            return Command::FAILURE;
        }
    }
}
