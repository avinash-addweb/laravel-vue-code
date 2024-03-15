<?php

namespace App\Tool\ViberBot\Messages;

use GuzzleHttp\Client;

class BaseViberMessage
{

    /**
     * @var string ['text','location']
     */

    protected $type;

    public function __construct()
    {
        $this->botToken = env('VIBERBOT_API');
        $this->apiUrl = "https://chatapi.viber.com/pa/";
        $this->httpClient = new Client(['base_uri' => $this->apiUrl,
            'headers' =>
                [
                    'X-Viber-Auth-Token' =>   $this->botToken
                ],
        ]);

    }

}