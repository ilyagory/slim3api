<?php

namespace App\Model;

use JsonSerializable;

class Contact implements JsonSerializable
{
    public $name;
    public $phone;
    public $email;

    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
        ];
    }

    static function ensure(array $item)
    {
        $n = [];
        foreach (['name', 'phone', 'email'] as $k) {
            $n[$k] = isset($item[$k]) ? $item[$k] : '';
        }
        return $n;
    }
}