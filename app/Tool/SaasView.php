<?php

namespace App\Tool;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SaasView extends Inertia
{

    public string $component;
    public array $props;

    public function __construct(string $component = '', array $props = [])
    {
        $this->component = $component;
        $this->props = $props;


    }

    /**
     * 'Tenant/Clients/Index'
     * @param string $component
     * @param array $props
     * @return Response
     */
    public static function render(string $component, array $props = []): \Inertia\Response
    {
        if (!Auth::guest()) {
            if (Auth::user()->isClient()) {
                $component = explode('/', $component);
                $component[0] = 'TenantClient';
                $component = Arr::join($component, '/');
            }
        }
        return Inertia::render($component, $props);
    }

    public static function location($url): \Symfony\Component\HttpFoundation\Response
    {
        return Inertia::location($url);
    }



    public static function shareToPlatfom($props, $modelKey, $data)
    {
        $key = 'datatable_';
        return Arr::add($props, $key.$modelKey, $data);
    }

    public static function shareToModel($props, $modelKey, array|Model $data)
    {
        $key = 'model_';
        return Arr::add($props, $key.$modelKey, $data);
    }

    public static function shareToDataTable($props, $modelKey, $data)
    {
        // dd($modelKey);
        $key = 'datatable_';
        return Arr::add($props, $key.$modelKey, $data);
    }

    public static function shareToSelectList($props, $modelKey, array $data)
    {
        $key = 'select_list_';
        return Arr::add($props, $key.$modelKey, $data);
    }


    public static function shareSimply($props, $modelKey, array $data)
    {
        return Arr::add($props, $modelKey, $data);
    }


    public static function shareAlways($props, $modelKey, array $data)
    {
        Inertia::share($modelKey,$data);
    }


}