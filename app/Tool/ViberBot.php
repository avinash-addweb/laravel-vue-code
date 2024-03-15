<?php

namespace App\Tool;

class ViberBot
{
    public function __construct()
    {
        $this->botToken = env('VIBERBOT_API');
        $this->apiUrl = "https://api.telegram.org/bot" . $this->botToken . "/";
        $this->httpClient = new Client(['base_uri' => $this->apiUrl]);

    }

}