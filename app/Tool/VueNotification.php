<?php

namespace App\Tool;

use Str;

class VueNotification
{

    public const TYPE_WARN = "warning";
    public const TYPE_ERROR = "error";
    public const TYPE_INFO = "info";

    protected $list = [];

    public function __construct()
    {

        $this->res = redirect()->back();
    }

    public function sendWarn($text)
    {
        $type=self::TYPE_WARN;
        return $this->res->with('notification', [$type => ["text" => $text, "type" =>$type]]);
    }

    public function sendType($text, $type)
    {

        return $this->res->with('notification', [$type => ["text" => $text, "type" => $type]]);
    }

    public function withError($text)
    {
        $type=self::TYPE_ERROR;
        return $this->res->with('notification', [$type => ["text" => $text, "type" => $type]]);
    }

    public function makeResponse(): \Illuminate\Http\RedirectResponse
    {
        return $this->res->with('error', $this->list);
        return $this->res->with('notification', $this->list);

    }


}