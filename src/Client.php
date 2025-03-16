<?php

namespace Enesisrl\RestServices;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;

class Client
{
    private HttpClient $client;

    public function __construct()
    {
        $this->client = new HttpClient();
    }

    public function get(string $url, string $username, string $password, array $options = []): string
    {
        try {
            $options['auth'] = [$username, $password, 'digest'];
            $response = $this->client->request('GET', $url, $options);
            return $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            // Gestisci l'eccezione come preferisci
            return 'Error: ' . $e->getMessage();
        }
    }

    public function post(string $url, string $username, string $password, array $data = [], array $options = []): string
    {
        try {
            $options['auth'] = [$username, $password, 'digest'];
            $options['form_params'] = $data;
            $response = $this->client->request('POST', $url, $options);
            return $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            // Gestisci l'eccezione come preferisci
            return 'Errore: ' . $e->getMessage();
        }
    }
}