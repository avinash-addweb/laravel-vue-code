<?php

namespace App\Http\Controllers;


use App\Http\Requests\BinStoreRequest;
use App\Http\Requests\BinUpdateRequest;
use App\Models\Bin;
use App\Models\BinRequest;
use App\Models\Route;
use App\Repositories\ReadRepositories\BinReadRepository;
use App\Repositories\WriteRepositories\BinWriteRepository;
use App\Repositories\ReadRepositories\SensorDataReadRepository;
use App\Repositories\ReadRepositories\BinClientReadRepository;
use App\Tool\SaasView;
use App\Tool\VueNotification;
use Datatable;
use Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Imports\BinImport;
use App\Models\WasteType;
use Maatwebsite\Excel\Facades\Excel;


class BinController extends Controller
{

    private BinReadRepository $binRead;
    private BinWriteRepository $writeBin;
    private BinClientReadRepository $binClientReadRepository;
    private SensorDataReadRepository $sensorDataReadRepository;

    public function __construct(BinReadRepository $binRead,BinWriteRepository $binWrite, BinClientReadRepository $binClientReadRepository, SensorDataReadRepository $sensorDataReadRepository)
    {

        $this->readBin = $binRead;
        $this->writeBin = $binWrite;
        $this->binClientReadRepository = $binClientReadRepository;
        $this->sensorDataReadRepository = $sensorDataReadRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $props=[];

        $bins=$this->readBin->getAllBins();
        $user = Auth::user();
        $roles = $user->getRoleNames()->toArray();
        $routes = Route::get()->toArray();
        $props= SaasView::shareToDataTable($props,"bins",$bins);
        $props= SaasView::shareToSelectList($props,"waste_profiles",$bins);
        $props= SaasView::shareToSelectList($props,"routes",$routes);
        //dd($roles[0]);
        if(isset($roles[0]) && $roles[0]=='CLIENT'){
            return SaasView::render('TenantClient/Bins/Index',
                $props
            );
        }else{
            return SaasView::render('Tenant/Bins/Index',
                $props
            );
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return to_route("bins.read");

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BinStoreRequest $request)
    {
        $this->writeBin->create($request);
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Bin $bin)
    {

        $this->authorize('view',$bin);

        $props=[];

        $bin['client']=$bin->clients()->first();
        $routes = Route::get()->toArray();
        
        $props= SaasView::shareToSelectList($props,"routes",$routes);
        $props = SaasView::shareToModel($props,'bin',$bin);



        return SaasView::render('Tenant/Bins/Edit',
            $props
        );
    }

    public function collect(Bin $bin)
    {
        //dd($bin);
        $bin=$this->writeBin->markAsFull($bin);
        $data = [
            'bin_id' => $bin->id,
            'client_id' => Auth::user()->client->id,
            'quantity' => 0,
            'collection_date' => date('Y-m-d'),
            'mobile' => null,
            'additional_info' => $bin->address,
            'lat' => $bin->lat,
            'lng' => $bin->lng,
            'waste_type' => $bin->waste_type,
            'type' => 'Auto',
            'status'=> 'Pending'
        ];
        $bin_request = BinRequest::create($data);

        return redirect()->back()->with('model_bin',$bin);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bin $bin)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BinUpdateRequest $request, bin $bin)
    {
        $bin->update([
            'name' => $request->name,
            'bin_number' => $request->bin_number,
            "bin_type" => $request->bin_type,
            'lat' => $request->lat,
            'lng' => $request->lng,
            "status" => $request->status,
            "capacity" => $request->capacity,
            "waste_type" => $request->waste_type,
            "bin_network" => $request->bin_network,
            "route_id" => $request->route_id,
            "iot_sensor_id" => $request->iot_sensor_id,
            "current_capacity" => $request->current_capacity,
            "bin_color" => $request->bin_color,
            "people_coverage" => $request->people_coverage,
        ]);


        $route = Route::where('id',$request->route_id)->first();
        if($route){
            $bin_count = Bin::where('route_id',$request->route_id)->count();
            $totalCapacity = Bin::where('route_id',$request->route_id)->sum('capacity');
            $peopleCoverage = Bin::where('route_id',$request->route_id)->sum('people_coverage');
            $route->total_bins_number = $bin_count;
            $route->total_bins_capacity = $totalCapacity/1000;
            $route->total_people_coverage = $peopleCoverage;
            $route->save();
        }

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     * @throws AuthorizationException
     */
    public function destroy(Bin $bin): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('delete',$bin);
        $this->writeBin->delete($bin);
        return (new VueNotification())->sendType("$bin->name Deleted",'info');

    }


    public function makeQrCodes()
    {

        $bins = $this->readBin->getPaginated();

        $props=SaasView::shareToDataTable([],"bins",$bins);


        return SaasView::render('Tenant/Bins/QRIndex', $props);


    }

    /** 
     * Export CSV file data
     */
    public function export(Request $request){
        $ids = $request->ids;
        $file=$this->readBin->export($ids);
        return Inertia::location(route("downloader",['filename'=> $file->fileName]));

    }

    /** 
     * Export CSV file data
     */
    public function bindepositeExport(Bin $bin){
        $file=$this->binClientReadRepository->bindepositeExport($bin);
        return Inertia::location(route("downloader",['filename'=> $file->fileName]));

    }


    /** 
     * Export CSV file data
     */
    public function binrequestHistoryExport(Bin $bin){
        $client_id = Auth::user()->client->id;
        $file=$this->binClientReadRepository->bindepositeClientExport($client_id);
        return Inertia::location(route("downloader",['filename'=> $file->fileName]));

    }

    /** 
     * Import Excel file data
     * Store into Route tables
     */
    public function import(Request $request){
        
        $request->validate([
            'csvfile' => 'required|max:4048',
        ],[
            'csvfile.required' => 'The Excel file is required.', // Custom error message for required rule
            'csvfile.max' => 'The uploaded file may not be greater than 4MB.', // Custom error message for max rule
        ]);
    
        $file1 = $request->file('csvfile');
        $file = $file1[0];

       // try {
            Excel::import(new BinImport,$file);
            //Excel::import(new BinImport, $file);
       // } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                // $failures = $e->failures();
                // $errormessage = "";
      //  }
        
        return redirect()->back();
    }


    /**
     * Display the specified resource.
     */
    public function bindetail(Bin $bin)
    {
  
        $this->authorize('view',$bin);

        $props=[];

        $bin_deposits = $this->binClientReadRepository->getBinDeposits($bin);
        $props= SaasView::shareToDataTable($props,"bin_deposits",$bin_deposits);
        $props= SaasView::shareToSelectList($props,"waste_profiles",$bin_deposits);
        return SaasView::render('Tenant/Bins/Detail',
            $props
        );
    }


     /**
     * Display the specified resource.
     */
    public function citizenBinDetail(Bin $bin)
    {
        $this->authorize('view',$bin);

        $props=[];

        $bin_deposits = $this->binClientReadRepository->getBinDeposits($bin);
        $props= SaasView::shareToDataTable($props,"bin_deposits",$bin_deposits);
        $props= SaasView::shareToSelectList($props,"waste_profiles",$bin_deposits);
        return SaasView::render('Tenant/Bins/Detail',
            $props
        );
    }


    /**
     * Display the specified resource.
     */
    public function binrequestHistory()
    {
        $client_id = Auth::user()->client->id;
        $props=[];
        $bin_deposits = $this->binClientReadRepository->getClientDepositHistory($client_id);
        $props= SaasView::shareToDataTable($props,"BinRequestHistory",$bin_deposits);

        return SaasView::render('TenantClient/BinRequests/Index',
            $props
        );
    }

         /**
     * Display the specified resource.
     */
    public function allClientBinDiposite(Request $request)
    {
        $props=[];
        $client = Auth::user();
        $bins =  Bin::ClientBins()->get()->toArray();
        $props = SaasView::shareToDataTable($props,'bin_deposits',$this->sensorDataReadRepository->getCitizenSensorData());
        //$props = SaasView::shareToSelectList($props,'chart1_data_table',$this->sensorDataReadRepository->getCitizenBinDepositChart1Table());
        //$props = SaasView::shareToSelectList($props,'chart1_data',$this->sensorDataReadRepository->getCitizenBinDepositChart1Data());
        //$props = SaasView::shareToSelectList($props,'chart2_data_table',$this->sensorDataReadRepository->getCitizenBinDepositChart2Table());
        //$props = SaasView::shareToSelectList($props,'chart2_data',$this->sensorDataReadRepository->getCitizenBinDepositChart2Data());
        //$props = SaasView::shareToSelectList($props,'chart3_data_table',$this->sensorDataReadRepository->getCitizenBinDepositChart3Table());
        //$props = SaasView::shareToSelectList($props,'chart3_data',$this->sensorDataReadRepository->getCitizenBinDepositChart3Data());
        $props= SaasView::shareToSelectList($props,"bins",$bins);

        //$startDate = '2023-01-01';
        //$endDate = '2024-02-26';

        //$data['chart1'] = $this->sensorDataReadRepository->getCitizenBinDepositChart1Data($startDate,$endDate);
        //$data['chart2'] = $this->sensorDataReadRepository->getCitizenBinDepositChart2Data($startDate,$endDate);
        //$data['chart3'] = $this->sensorDataReadRepository->getCitizenBinDepositChart3Data($startDate,$endDate);

        //dd($data['chart2']);
        return SaasView::render('TenantClient/BinDeposit/Index',
            $props
        );
    }

    
}
