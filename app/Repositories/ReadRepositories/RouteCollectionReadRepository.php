<?php

namespace App\Repositories\ReadRepositories;

use App\Exceptions\ModelClassNotFoundException;

use App\Models\Bin;
use App\Models\Client;
use App\Models\RouteCollection;
use App\Repositories\ReadRepositories\ReadBaseRepository;
use Arr;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Exports\QuickExport;

use Illuminate\Support\Facades\Auth;
use phpDocumentor\Reflection\Types\Parent_;
use Str;
use function Symfony\Component\Translation\t;

class RouteCollectionReadRepository extends ReadBaseRepository
{

    protected $modelClass = RouteCollection::class;

    public function __construct()
    {
        parent::__construct();


    }


    public function getRouteCollections()
    {
        $this->query = $this->modelClass::query();


        $this->data = $this->query->get()->toArray();

        return $this->closure();
    }

        public function export($ids)
    {
        $this->headers= [
            'collected_date',
            'vehicle_id',
            'fuel_cost',
            'route_id',
            'receiver',
            'collected_percentage',
            'collected_waste_mass',
        ];



        $export = new QuickExport(RouteCollection::query()->whereIn('id',$ids),"route_collections",$this->headers);
        $name=$export->fileName;
        $export->store("public/$name");
        return $export;



    }





}