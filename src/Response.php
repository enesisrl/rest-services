<?php

namespace Enesisrl\RestServices;
/**
 * Classe Response per gestire le risposte delle chiamate REST.
 * Fornisce un wrapper per i dati della risposta con accesso dinamico alle proprietà.
 *
 * @author Emanuele Toffolon - Enesi srl - www.enesi.it
 */
class Response {
    /**
     * Contiene i dati della risposta in formato array.
     *
     * @var array|null
     */
    private $data;
    /**
     * Inizializza una nuova istanza della classe Response.
     * Converte automaticamente le stringhe JSON in array.
     *
     * @param string|array $data Dati della risposta in formato stringa JSON o array
     */
    public function __construct($data){
        if (!is_array($data)) {
            $data = json_decode((string)$data, true);
        }
        $this->data = $data;
    }
    /**
     * Getter magico per accedere dinamicamente ai dati della risposta.
     *
     * @param string $name Nome della proprietà da recuperare
     * @return mixed|null Il valore della proprietà se esiste, null altrimenti
     */
    public function __get($name) {
        return $this->data[$name] ?? null;
    }
    /**
     * Setter magico per impostare dinamicamente i dati della risposta.
     *
     * @param string $name Nome della proprietà da impostare
     * @param mixed $value Valore da assegnare alla proprietà
     */
    public function __set($name, $value) {
        $this->data[$name] = $value;
    }

}