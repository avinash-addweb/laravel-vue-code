<?php

namespace App\Repositories\WriteRepositories;

use App\Exceptions\ModelClassNotFoundException;

use App\Models\Bin;
use App\Models\Client;
use App\Models\RouteCollection;
use App\Models\Vehicle;
use App\Models\CollectionLogSummary;
use App\Repositories\ReadRepositories\ReadBaseRepository;
use Arr;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use phpDocumentor\Reflection\Types\Parent_;
use Str;
use function Symfony\Component\Translation\t;

class RouteCollectionWriteRepository extends ReadBaseRepository
{

    /**
     * @var string
     */
    protected $modelClass = RouteCollection::class;

    public function __construct()
    {
        parent::__construct();


    }


    public function create(FormRequest $request){
        $date = Carbon::parse($request->collected_date)->format('Y-m-d');
        $time = Carbon::parse($request->collected_date)->format('H:i:s');
        $vehicle = Vehicle::where('id',$request->vehicle_id)->first();
        if($time=='00:00:00'){
            $time = '10:10:10';
        }
        $data = [
            'vehicle' => $vehicle->licence_plate,
            'route_id' => $request->route_id,
            'fuel_cost' => $request->fuel_cost,
            'receiver' => $request->receiver,
            'collection_percentage' => $request->collected_percentage,
            'waste_weight' => $request->collected_waste_mass,
            'collected_time' => $time,
            'collected_date' => $date,
            'input_type' => 'Manual',
        ];

        CollectionLogSummary::create($data);
        $this->modelClass::create([
            'collected_date'=>$request->collected_date,
            'vehicle_id'=>$request->vehicle_id,
            'fuel_cost'=>$request->fuel_cost,
            'route_id'=>$request->route_id,
            'receiver'=>$request->receiver,
            'collected_percentage'=>$request->collected_percentage,
            'collected_waste_mass'=>$request->collected_waste_mass,
        ]);
    }



    public function update(RouteCollection $model,FormRequest $request){

        $model->update([
            'collected_date'=>$request->collected_date,
            'vehicle_id'=>$request->vehicle_id,
            'fuel_cost'=>$request->fuel_cost,
            'route_id'=>$request->route_id,
            'receiver'=>$request->receiver,
            'collected_percentage'=>$request->collected_percentage,
            'collected_waste_mass'=>$request->collected_waste_mass,
        ]);
        return $model;

    }







}