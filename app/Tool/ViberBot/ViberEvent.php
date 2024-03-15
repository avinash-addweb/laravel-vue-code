<?php

namespace App\Tool\ViberBot;

use App\Tool\ViberBot\Messages\ViberTextMessage;
use Illuminate\Http\Request;

class ViberEvent
{

    protected $sender;
    protected $event;
    protected $message;

    public function __construct(Request $request)
    {
        $this->event=$request->event;
        $this->sender=$request->sender;
        $this->message=$request->message;
    }



    public function messageContains($msg){


    }


    public function getSenderId(){
        return $this->sender['id'];
    }

    public function getMessage(){
        if ($this->getMessageType() == ViberTextMessage::TYPE) {
            return new ViberTextMessage($this->getMessageText());
        }
        return null;

    }

    public function getMessageType(){
        return $this->message['type'];
    }
    public function getMessageText(){
        return $this->message['text'];
    }


    public function replyToSender($text): void
    {
        $msg= new ViberTextMessage();
        $msg->sendText($this->getSenderId(),$text);
    }


}