<?php

namespace App\Tool;

use Str;

class VueFiltering
{


    protected $list = [];

    public function __construct()
    {

        $this->res = redirect()->back();
    }

    public function sendWarn($text)
    {
        return $this->res->with('queries', [self::TYPE_WARN => ["text" => $text, "type" => self::TYPE_WARN]]);
    }

    public function sendType($text, $type)
    {

        return $this->res->with('notification', [$type => ["text" => $text, "type" => $type]]);
    }

    public function addError($text)
    {
        $this->list[] = [self::TYPE_WARN => $text];
    }

    public function makeResponse(): \Illuminate\Http\RedirectResponse
    {
        return $this->res->with('error', $this->list);
        return $this->res->with('notification', $this->list);

    }


}