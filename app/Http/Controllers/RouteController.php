<?php

namespace  App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RouteStoreRequest;
use App\Http\Requests\RouteUpdateRequest;
use App\Models\Bin;
use App\Models\BinRoute;
use App\Models\PlatformSetting;
use App\Models\Route;
use App\Repositories\ReadRepositories\RouteRepository;
use App\Tool\SaasView;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RouteImport;

class RouteController extends Controller
{
    private RouteRepository $routeRepository;

    public function __construct(RouteRepository $routeRepository)
    {
        $this->routeRepository = $routeRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $props=[];


        $props = SaasView::shareToDataTable($props,'routes',$this->routeRepository->getPaginated());


        return SaasView::render('Tenant/Routes/Index',
            $props
        );

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RouteStoreRequest $request)
    {
        Route::create([
            'name'=>$request->name,
            'waste_type'=>$request->waste_type,
            'weekly_collection_rate'=>$request->weekly_collection_rate,
            'toll_cost'=>$request->toll_cost,
            'note'=>$request->desc,
            'total_distance'=>$request->total_distance,
            'location'=>$request->location
        ]);

        return redirect()->back();
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(route $route)
    {
        $props=[];
        $route['allowed_bins']= $route->getAllowedBins();
        $props=SaasView::shareToModel($props,"route",$route);




        return SaasView::render('Tenant/Routes/Edit',
            $props

        );

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(route $route)
    {

    }


    /**
     * Update the specified resource in storage.
     */
    public function update(RouteUpdateRequest $request, route $route)
    {




        $route->update([
            'lat'=>$request->lat,
            'lng'=>$request->lng,
            'address'=>$request->address,
            'waste_type'=>$request->waste_type,
            'toll_cost'=>$request->toll_cost,
            'note'=>$request->desc
        ]);



        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(route $route)
    {
        $route->delete();
        return redirect()->back();
    }

    public function setStops(Request $request,route $route)
    {


        $route->replaceAllRouteBins($request->bins);
        return redirect()->back();
    }

    /** 
     * Export CSV file data
     */
    public function export(Request $request){
        $file=$this->routeRepository->export();
        return Inertia::location(route("downloader",['filename'=> $file->fileName]));

    }

    /** 
     * Import Excel file data
     * Store into Route tables
     */
    public function import(Request $request){
        
        $request->validate([
            'csvfile' => 'required|max:2048',
        ],[
            'csvfile.required' => 'The CSV file is required.', // Custom error message for required rule
            'csvfile.max' => 'The uploaded file may not be greater than 4MB.', // Custom error message for max rule
        ]);

        $file = $request->file('csvfile');
        $file = $file[0];
        try {
            Excel::import(new RouteImport, $file);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                 $failures = $e->failures();
                 $errormessage = "";
   		return redirect()->back();
        }
        
        return redirect()->back();
    }
}
