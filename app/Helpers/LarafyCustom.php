<?php

namespace App\Helpers;

use Rennokki\Larafy\Larafy;

class LarafyCustom extends Larafy{

    public function searchCustom(string $query, int $limit = 10, int $offset = 0, string $type)
    {
        $json = $this->get()->request('/search', [
            'q' => $query,
            #'type' => 'artist,playlist,track',
            'type' => $type,
            'market' => $this->market,
            'limit' => $limit,
            'offset' => $offset,
        ]);

        return $json;
    }

}