<?php

namespace App\Tool;


use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class TelegramBot
{

    private $apiUrl;
    private $botToken;
    private $httpClient;
    private const GET_ME = "getMe";
    private const GET_UPDATES = "getUpdates";

    private const PARSE_MODE_HTML = "HTML";
    private const SEND_MESSAGE = "sendMessage";
    private const SEND_LOCATION = "sendLocation";
    private const INLINE_KEYBOARD_MARKUP = "inline_keyboard";
    private const SEND_PHOTO = "sendPhoto";
    public const WASTE_GROUP = "-906118246" ;
    public const JSON_ID = "973093704";
    public const george_ID = "6154880521";
    private $hasLink;
    /**
     * @var array|mixed
     */
    private mixed $params = [
        'chat_id' => null,
        'text' => null,
        ];

    public function __construct($receiver = null)
    {
        $this->botToken = env('TELEGRAM_API');
        $this->apiUrl = env('TELEGRAM_URL') . $this->botToken . "/";
        $this->httpClient = new Client(['base_uri' => $this->apiUrl]);

        $this->params['chat_id'] = $receiver;
        $this->getUpdates();

    }


    public function setMsgReceiver($receiver)
    {
        $this->sendRequest(self::GET_ME);
    }

    public function getMe()
    {
        $this->sendRequest(self::GET_ME);
    }

    public function makeLink($url, $text = null)
    {
        $this->hasLink = true;
        if (is_null($text)) {
            return "<a href='$url'>$url</a>";
        }
        return "<a href='$url'>$text</a>";

    }

    public function getUpdates()
    {
        $this->sendRequest(self::GET_UPDATES);
    }

    public function sendMessage($chatId, $message)
    {
        $params = array(
            "chat_id" => $chatId,
            "text" => $message
        );
        if ($this->hasLink) {
            $params['parse_mode'] = self::PARSE_MODE_HTML;
        }
        $this->sendRequest(self::SEND_MESSAGE, $params);
    }

    public function sendLocation($chatId, $lat, $long)
    {
        $params = array(
            "chat_id" => $chatId,
            "latitude" => $lat,
            "longitude" => $long
        );
        $this->sendRequest(self::SEND_LOCATION, $params);
    }


    public function sendPhoto($chatId, $photoPath, $caption = "")
    {
        $params = array(
            "chat_id" => $chatId,
            "photo" => new CURLFile(realpath($photoPath)),
            "caption" => $caption
        );
        $this->sendRequest(self::SEND_MESSAGE, $params);
    }

    public function sendInlineButtons($chatId, $text, $buttons)
    {
        $inlineKeyboard = [];

        foreach ($buttons as $buttonRow) {
            $row = [];
            foreach ($buttonRow as $button) {
                $row[] = [
                    "text" => $button['text'],
                    "callback_data" => $button['callback_data']
                ];
            }
            $inlineKeyboard[] = $row;
        }

        $params = [
            "chat_id" => $chatId,
            "text" => $text,
            "reply_markup" => [
                self::INLINE_KEYBOARD_MARKUP => $inlineKeyboard
            ]
        ];

        $this->sendRequest(self::SEND_MESSAGE, $params);
    }

    public function attachText($text)
    {
        $this->params["text"] = $text;

    }

    public function attachLocation($lat, $long)
    {
        $this->params["latitude"] = $lat;
        $this->params["longitude"] = $long;
    }


    public function attachButtons(array $buttons)
    {
        $inlineKeyboard = [];

        foreach ($buttons as $buttonRow) {
            $row = [];
            foreach ($buttonRow as $button) {
                $row[] = [
                    "text" => $button['text'],
                    "url" => $button['url']
                ];
            }
            $inlineKeyboard[] = $row;
        }

        $this->params["reply_markup"][self::INLINE_KEYBOARD_MARKUP] = $inlineKeyboard;
    }


    public function generateInlineButtons($buttonTexts)
    {
        $buttons = [];

        foreach ($buttonTexts as $text => $url) {
            $buttons[] = [
                "text" => $text,
                "url" => $url
               // "callback_data" => $url
            ];
        }

        return $buttons;
    }

    public function sendRequest($method = self::SEND_MESSAGE, $params = null)
    {
        $response = $this->httpClient->post($method, [
            RequestOptions::JSON => $params ?? $this->params
        ]);
        info($response->getBody());
        return $response->getBody()->getContents();
    }

}