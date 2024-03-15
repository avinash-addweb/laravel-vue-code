<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RouteCollectionStoreRequest;
use App\Http\Requests\RouteCollectionUpdateRequest;
use App\Models\Location;
use App\Models\Route;
use App\Models\RouteCollection;
use App\Models\Vehicle;
use App\Repositories\ReadRepositories\LocationReadRepository;
use App\Repositories\ReadRepositories\RouteCollectionReadRepository;
use App\Repositories\ReadRepositories\RouteReadRepository;
use App\Repositories\ReadRepositories\VehicleReadRepository;
use App\Repositories\WriteRepositories\RouteCollectionWriteRepository;
use App\Repositories\ReadRepositories\VehicleDataReadRepository;
use App\Tool\SaasView;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RouteCollectionImport;
use Inertia\Inertia;


class RouteCollectionController extends Controller
{
    private RouteCollectionWriteRepository $routeCollectionWriteRepository;
    private RouteCollectionReadRepository $routeCollectionReadRepository;
    private VehicleReadRepository $vehicleReadRepository;
    private RouteReadRepository $routeReadRepository;
    private LocationReadRepository $locationReadRepository;
    private VehicleDataReadRepository $vehicleDataRepository;

    public function __construct(
        RouteCollectionWriteRepository $routeCollectionWriteRepository,
        RouteCollectionReadRepository $routeCollectionReadRepository,
        VehicleReadRepository $vehicleReadRepository,
        RouteReadRepository $routeReadRepository,
        LocationReadRepository $locationReadRepository,
        VehicleDataReadRepository $vehicleDataRepository
        )
    {
        $this->routeCollectionWriteRepository = $routeCollectionWriteRepository;
        $this->routeCollectionReadRepository = $routeCollectionReadRepository;
        $this->vehicleReadRepository = $vehicleReadRepository;
        $this->routeReadRepository = $routeReadRepository;
        $this->locationReadRepository = $locationReadRepository;
        $this->vehicleDataRepository = $vehicleDataRepository;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $props=[];
        $props= SaasView::shareToSelectList($props,"vehicles",$this->vehicleReadRepository->getVehicles());
        $props= SaasView::shareToSelectList($props,"routes",$this->routeReadRepository->getAll());
        $props= SaasView::shareToSelectList($props,"locations",$this->locationReadRepository->getAll());

        $props= SaasView::shareToDataTable($props,"route_collection",$this->routeCollectionReadRepository->getAll());




        return SaasView::render('Tenant/RouteCollection/Index', $props);
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
    public function store(RouteCollectionStoreRequest $request)
    {
        // dd($request->all());
        $this->routeCollectionWriteRepository->create($request);

        return redirect()->back();

    }

    /**
     * Display the specified resource.
     */
    public function show(routeCollection $routeCollection)
    {
        return $routeCollection;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(routeCollection $routeCollection)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RouteCollectionUpdateRequest $request, routeCollection $routeCollection)
    {
        $this->routeCollectionWriteRepository->update($routeCollection,$request);
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(routeCollection $routeCollection)
    {
        //
    }
    public function export(Request $request){
       
        $ids = $request->ids;
        $file=$this->routeCollectionReadRepository->export($ids);

        return Inertia::location(route("downloader",['filename'=> $file->fileName]));
    }

    public function totalcollectionlogs(){
        $props=[];
        $props = SaasView::shareToDataTable($props,'route_collection',$this->vehicleDataRepository->getTenantTotalSummeryCollectionLogs());
        return SaasView::render('Tenant/CollectionLogSummary/Index', $props);
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
            Excel::import(new RouteCollectionImport, $file);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                 $failures = $e->failures();
                 $errormessage = "";
   		return redirect()->back();
        }
        
        return redirect()->back();
    }

}
