<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PropertyService
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private EntityManagerInterface $entityManager;
    private $cache;
    private string $sprengnetterApiUrl;
    private string $europaceApiUrl;

    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->sprengnetterApiUrl = $params->get('API_SPRENGNETTER_URL');
        $this->europaceApiUrl = $params->get('API_EUROPACE_URL');
        $this->cache = new RedisAdapter(RedisAdapter::createConnection($params->get('REDIS_URL')));
    }

    public function fetchProperties(): array
{
    $cachedData = $this->cache->getItem('properties_data');

    // Return cached data if available
    if ($cachedData->isHit()) {
        $this->logger->info('Returning cached property data');
        return $cachedData->get();
    }

    try {
        $sprengnetterData = $this->fetchApiData($this->sprengnetterApiUrl);
        $europaceData = $this->fetchApiData($this->europaceApiUrl);
        
        $mergedData = $this->mergeData($sprengnetterData, $europaceData);

        // **Corrected: Now properly setting & saving cache**
        $cachedData->set($mergedData);
        $cachedData->expiresAfter(3600); // Cache expires in 1 hour
        $this->cache->save($cachedData);

        $this->logger->info('Fetched and cached property data');


        return $mergedData;
    } catch (\Exception $e) {
        $this->logger->error('Failed to fetch property data', ['error' => $e->getMessage()]);
        return [];
    }
}

    private function fetchApiData(string $url): array
    {
        try {
            $response = $this->httpClient->request('GET', $url);
            return $response->toArray();
        } catch (\Exception $e) {
            $this->logger->error('API request failed', ['url' => $url, 'error' => $e->getMessage()]);
            return [];
        }
    }

    private function mergeData(array $data1, array $data2): array
    {
        // Logic to merge API data ensuring consistency
        return array_merge($data1, $data2);
    }
}
