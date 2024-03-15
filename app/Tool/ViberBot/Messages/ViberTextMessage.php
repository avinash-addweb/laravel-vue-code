<?php

namespace App\Tool\ViberBot\Messages;

use Str;

class ViberTextMessage extends BaseViberMessage
{
    public const TYPE = "text";
    protected string $text = '';

    public function __construct($text = '')
    {
        parent::__construct();
        $this->text = $text;

    }

    public function getText(): string
    {
        return $this->text;
    }

    public function contains($str): bool
    {
        return Str::contains(Str::lower($this->text), Str::lower($str));
    }


    public function sendText($receiver_id, $text)
    {
        $this->httpClient->post('send_message', [
            'json' => [
                'receiver' => $receiver_id,
                'type' => self::TYPE,
                'text' => $text,
                'sender' => ['name' => "WastePlan"]
            ]
        ]);

    }


    public function SendButton($receiver_id, $text, $url)
    {
        $html = "<a href='$url'>$text</a>";
        $this->httpClient->post('send_message', [
            'json' => [
                'receiver' => $receiver_id,
                'type' => 'rich_media',
                'text' => $html,
                'sender' => ['name' => "WastePlan"]

            ]
        ]);

    }

    public function SendButtons($receiver_id, $buttons)

    {
        $_buttons=[];
        foreach ($buttons as $button) {
            $_buttons[]= [
                "Columns" => 6,
                "Rows" => 1,
                "ActionType" => "open-url",
                "Text" => $button['text'],
                "ActionBody"=> $button['url'],
                "TextVAlign" => "middle",
                "TextHAlign"=> "center",
            ];
        }



        $this->httpClient->post('send_message', [
            'json' => [
                'receiver' => $receiver_id,
                'keyboard' => [
                    "Type"=>"keyboard",
                    "Buttons" => $_buttons,
                ],
                'type' => self::TYPE,
                'text' => $this->text,

                'sender' => ['name' => "WastePlan"]

            ]
        ]);

    }

    public function sendTo($receiver_id)
    {
        $this->httpClient->post('send_message', [
            'json' => [
                'receiver' => $receiver_id,
                'type' => self::TYPE,
                'text' => $this->text,
                'sender' => ['name' => "WastePlan"]
            ]
        ]);

    }

}