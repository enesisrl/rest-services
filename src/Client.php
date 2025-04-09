<?php

namespace Enesisrl\RestServices;

use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
/**
 * Client per la gestione delle chiamate REST ai servizi Enesi.
 * @author Emanuele Toffolon - Enesi srl - www.enesi.it
 */
class Client
{
    /**
     * Flag per attivare/disattivare la modalità debug.
     *
     * @var bool
     */
    protected $debug = false;
    /**
     * URI base per le chiamate API.
     *
     * @var string
     */
    protected $baseUri = 'https://rest2.ene.si';
    /**
     * Istanza del client HTTP Guzzle.
     *
     * @var HttpClient
     */
    private $client;

    /**
     * Inizializza una nuova istanza del client REST.
     */
    public function __construct()
    {
        $this->client = new HttpClient();
    }
    /**
     * Imposta la modalità debug e l'URI base corrispondente.
     *
     * @param bool $debug True per attivare la modalità debug, false per disattivarla
     */
    public function setDebug(bool $debug) {
        $this->debug = $debug;
        if ($this->debug) {
            $this->baseUri = 'https://rest2.enesi.vm';
        }else{
            $this->baseUri = 'https://rest2.ene.si';
        }
    }

    /**
     * Esegue il login utilizzando l'autenticazione Digest.
     *
     * @param string $username Nome utente per l'autenticazione
     * @param string $password Password per l'autenticazione
     * @return Response Risposta del server contenente i dati di login
     * @throws Exception Se si verificano errori durante il login
     */
    public function login($username, $password)
    {
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
                    return new Response($response['body']);
                }
            }
            throw new Exception("Errore nel login: " . $response['error']);
        } else {
            return new Response($response['body']);
        }
    }

    /**
     * Esegue una richiesta GET all'endpoint specificato.
     *
     * @param string $url URL completo dell'endpoint
     * @param array $options Opzioni aggiuntive per la richiesta
     * @return array Risposta del server con corpo, headers ed eventuali errori
     * @throws GuzzleException
     */
    public function get($url, $options = []){
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


    /**
     * Esegue una richiesta POST all'endpoint specificato con autenticazione Digest.
     *
     * @param string $url URL completo dell'endpoint
     * @param string $username Nome utente per l'autenticazione
     * @param string $password Password per l'autenticazione
     * @param array $data Dati da inviare nel corpo della richiesta
     * @param array $options Opzioni aggiuntive per la richiesta
     * @return string Risposta del server o messaggio di errore
     */
    public function post($url, $username, $password, $data = [], $options = [])  {
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