<?php

namespace Enesisrl\RestServices;

class Response {

    private array $data;
    public function __construct(mixed $data){
        if (!is_array($data)) {
            $data = json_decode((string)$data, true);
        }
        $this->data = $data;
    }

    public function __get($name) {
        return $this->data[$name] ?? null;
    }

    public function __set($name, $value) {
        $this->data[$name] = $value;
    }

}