<?php

namespace Enesisrl\RestServices;

use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

class Client
{
    protected bool $debug = false;
    protected string $baseUri = 'https://rest2.ene.si';

    private HttpClient $client;

    public function __construct()
    {
        $this->client = new HttpClient();
    }

    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
        if ($this->debug) {
            $this->baseUri = 'https://rest2.enesi.vm';
        }else{
            $this->baseUri = 'https://rest2.ene.si';
        }
    }

    /**
     * @throws Exception
     */
    public function login($username, $password){
        $uri = '/api/v2/login';
        $options = [
            'headers' => [
                'Accept' => 'application/json'
            ]
        ];
        // Prima richiesta per ottenere nonce e realm
        $response = $this->get($this->baseUri . $uri, $options);
        if (isset($response['error'])) {
            if (isset($response['headers'])) {
                // Estrai nonce e realm dagli header di risposta
                $headers = $response['headers'];
                $wwwAuthenticateHeader = $headers['WWW-Authenticate'][0];
                if (!preg_match('/realm="([^"]+)", nonce="([^"]+)"/', $wwwAuthenticateHeader, $matches)) {
                    throw new Exception("Errore: Impossibile estrarre i dati Digest dall\'header");
                }
                $realm = $matches[1];
                $nonce = $matches[2];
                // Calcola l'header di autenticazione Digest
                $ha1 = md5($username . ':' . $realm . ':' . $password);
                $ha2 = md5('GET:' . $uri);
                $responseDigest = md5($ha1 . ':' . $nonce . ':' . $ha2);

                // Seconda richiesta con Digest Authentication
                $authHeader = "Digest username=\"$username\", realm=\"$realm\", nonce=\"$nonce\", uri=\"$uri\", response=\"$responseDigest\"";
                $options['headers']['Authorization'] = $authHeader;
                $response = $this->get($this->baseUri . $uri, $options);
                if (isset($response['error'])) {
                    throw new Exception("Errore nel login: " . $response['error']);
                } else {
                    return new User($response['body']);
                }
            }
            throw new Exception("Errore nel login: " . $response['error']);
        } else {
            return new User($response['body']);
        }
    }

    public function get(string $url, array $options = []): array
    {
        try {
            $response = $this->client->request('GET', $url, $options);
            return [
                'body' => $response->getBody()->getContents(),
                'headers' => $response->getHeaders()
            ];
        } catch (RequestException $e) {
            // Gestisci l'eccezione come preferisci
            $response = $e->getResponse();
            return [
                'error' => $e->getMessage(),
                'status_code' => $response ? $response->getStatusCode() : null,
                'body' => $response ? $response->getBody()->getContents() : null,
                'headers' => $response ? $response->getHeaders() : []
            ];
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