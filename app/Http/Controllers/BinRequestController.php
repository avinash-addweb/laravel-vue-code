<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\BinRequest;
use App\Models\Client;
use App\Models\WasteType;
use App\Models\Bin;
use Auth;
use Response;
use DB;
use Inertia\Inertia;
use App\Models\User;
use App\Tool\SaasView;
use App\Tool\VueNotification;
use App\Mail\AcceptBinRequest;
use App\Mail\RejectBinRequest;
use Illuminate\Support\Facades\Mail;
use Datatable;
use App\Repositories\ReadRepositories\BinReadRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Maatwebsite\Excel\Facades\Excel;
use App\Repositories\ReadRepositories\SensorDataReadRepository;
use App\Repositories\ReadRepositories\BinClientRequestReadRepository;
class BinRequestController extends Controller
{
    private BinClientRequestReadRepository $binClientRequestReadRepository;
    private BinReadRepository $binRead;
    private SensorDataReadRepository $sensorDataReadRepository;

    public function __construct(BinReadRepository $binRead, BinClientRequestReadRepository $binClientRequestReadRepository, SensorDataReadRepository $sensorDataReadRepository)
    {
        $this->binClientRequestReadRepository = $binClientRequestReadRepository;
        $this->readBin = $binRead;
        $this->sensorDataReadRepository = $sensorDataReadRepository;
    }
    public function index()
    {
        $props=[];
        $client_id = Auth::user()->client->id;
        $bin_requests=$this->binClientRequestReadRepository->getAllBinRequests($client_id);
        $props= SaasView::shareToDataTable($props,"BinRequest",$bin_requests);
        return SaasView::render('TenantClient/BinRequests/Index',
            $props
        );
    }
    public function tenantBinRequestIndex()
    {
        $props=[];
        //$client_id = Auth::user()->client->id;
        $bin_requests=$this->binClientRequestReadRepository->getAllBinRequestData();
        $props= SaasView::shareToDataTable($props,"BinRequest",$bin_requests);
        return SaasView::render('Tenant/BinRequests/Index',
            $props
        );
    }
    public function create()
    {
        // Return a view for creating a new bin request
        return view('bin_requests.create');
    }
    public function store(Request $request)
    {
        // Validate and store the new bin request
        $request->validate([
            // 'bin_id' => 'required',
            'address' => 'required',
            // 'quantity' => 'required|numeric|between:1,100',
            // 'collection_date' => 'required',
            'mobile' => 'required|regex:/^[0-9]{9,11}$/',
            'additional_info' => 'required',
            // 'lat' => 'required',
            // 'lng' => 'required',
            'waste_type' => 'required'
        ]);
        
        $client = Client::where('user_id',$request->client_id)->first();
        $bin_request = BinRequest::create($request->all());
        $bin_request->client_id = $client->id;
        $bin_request->status = 'Pending';
        $bin_request->save();
        return redirect()->route('binrequests.history');
    }
     /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $status="Accept";
        if($request->status==0){
            $request->validate([
                'reason' => 'required',
            ]);
            $status="Reject";
        }else{
            $request->reason = null;
            $status="Accept";
        }
        
        $binRequest = BinRequest::findorfail($id);
        $binRequest->update([
            'status'=>$status,
            'reason'=>$request->reason ?? null,
        ]);
        $wastetype = WasteType::where('id',$binRequest->waste_type)->first();
        if($status=='Accpet'){
            $mailData = [
                'waste_type' => $wastetype->name,
                'address' => $binRequest->address,
                'status' => $binRequest->status,
                'reason' => 'N/A',
                'reject_date' => $binRequest->updated_at,
            ];
        }else{
            $mailData = [
                'waste_type' => $wastetype->name,
                'address' => $binRequest->address,
                'status' => $binRequest->status,
                'reason' => $request->reason,
                'reject_date' => $binRequest->updated_at,
            ];
        }
        
        $user = User::where('id',$binRequest->client->user_id)->first();
        if($request->status==0){
            Mail::to($user->email)->send(new RejectBinRequest($mailData));
        }else{
            Mail::to($user->email)->send(new AcceptBinRequest($mailData));
        }
        return redirect()->route('tenant.binrequests.index');
    }
        /** 
     * Export CSV file data
     */
    public function export(Bin $bin){
        $file=$this->binClientReadRepository->bindepositeExport($bin);
        return Inertia::location(route("downloader",['filename'=> $file->fileName]));
    }
    public function tenantExport(Request $request){
        $ids = $request->ids;
        $file=$this->binClientRequestReadRepository->binRequestExport($ids);
        return Inertia::location(route("downloader",['filename'=> $file->fileName]));
    }
        /** 
     * Export CSV file data
     */
    public function binrequestHistoryExport(Request $request){
        $ids = $request->ids;
        $file=$this->binClientRequestReadRepository->binRequestExport($ids);
        return Inertia::location(route("downloader",['filename'=> $file->fileName]));
    }


            /** 
     * Export CSV file data
     */
    public function binrequestAutoHistoryExport(Request $request){
        $ids = $request->ids;
        $file=$this->binClientRequestReadRepository->binRequestExport($ids);
        return Inertia::location(route("downloader",['filename'=> $file->fileName]));
    }
    /**
     * Display the specified resource.
     */
    public function binrequestHistory(Request $request)
    {
        $client = Auth::user()->client;
        $props=[];
        $bins = $this->readBin->getClientBinSearch();
        $props = SaasView::shareToSelectList($props, 'bins', $bins);
        $bin_deposits = $this->binClientRequestReadRepository->getClientDeposits($request->query('binrequesthistory'));
        $props= SaasView::shareToDataTable($props,"BinRequestHistory",$bin_deposits);
        return SaasView::render('TenantClient/BinRequests/Index',
            $props
        );
    }


    /**
     * Display the specified resource.
     */
    public function binrequestAutoHistory(Request $request)
    {
        $client = Auth::user()->client;
        $props=[];
        $bins = $this->readBin->getClientBinSearch();
        $props = SaasView::shareToSelectList($props, 'bins', $bins);
        $bin_deposits = $this->binClientRequestReadRepository->getClientRequestHistory($request->query('binrequesthistory'));
        $props= SaasView::shareToDataTable($props,"BinRequestHistory",$bin_deposits);
        //dd($bin_deposits);
        $props = SaasView::shareToDataTable($props,'chart1_data',$this->binClientRequestReadRepository->getMyCollectionChart1Data());
        $props = SaasView::shareToDataTable($props,'chart2_data',$this->binClientRequestReadRepository->getMyCollectionChart2Data());
        return SaasView::render('TenantClient/BinRequests/AutoIndex',
            $props
        );
    }


    /**
     * Display the specified resource.
     * Citizen Panel -> BinCollection(QR) Chart Data with filter
     */
    public function binRequestChart(Request $request)
    {
        $client = Auth::user()->client;

        $startDate = $request->startDate;
        $endDate = $request->endDate;
        $data =[];

        $data['chart1'] = $this->binClientRequestReadRepository->getCitizenBinRequestChart1Data($startDate,$endDate);
        $data['chart2'] = $this->binClientRequestReadRepository->getCitizenBinRequestChart2Data($startDate,$endDate);

        
        return Response::json(['binRequests' => $data]);
    }

// Citizen Panel -> BinDeposit(RFID) Chart Data with filter
    public function binDepositChart(Request $request)
    {
       // dd($request->all());
        $client = Auth::user()->client->id;
        $startDate = $request->startDate;
        $endDate = $request->endDate;
        $data =[];

        //$data['chart1'] = $this->binRepository->getCitizenWastePointsDailyCategory($startDate,$endDate);
        $data['chart1'] = $this->readBin->getCitizenWastePointsDailyCategory($startDate,$endDate);
        $data['chart2'] = $this->readBin->getCitizenWastePointsCategory($startDate,$endDate);
        // $data['chart1'] = $this->sensorDataReadRepository->getCitizenBinDepositChart1Data($startDate,$endDate);
        // $data['chart2'] = $this->sensorDataReadRepository->getCitizenBinDepositChart2Data($startDate,$endDate);
        // $data['chart3'] = $this->sensorDataReadRepository->getCitizenBinDepositChart3Data($startDate,$endDate);
        
        // if(!empty($request->filter) && $request->filter=="bin"){
        //     $binData = DB::select("SELECT bin_deposits.bin_id, bin_deposits.client_id, bins.name, SUM(bin_deposits.quantity) as total_quantity
        //     FROM bin_deposits
        //     JOIN bins ON bin_deposits.bin_id = bins.id
        //     WHERE bin_deposits.client_id = $client 
        //     AND bin_deposits.created_at BETWEEN '$startDate' AND '$endDate'
        //     GROUP BY bin_deposits.bin_id, bin_deposits.client_id, bins.name");
        //      $data =$binData;
        // } 
        // else if(!empty($request->filter) && $request->filter=="wasteType"){
        //     $binData = DB::select("SELECT bin_deposits.client_id, bins.waste_type, SUM(bin_deposits.quantity) as total_quantity
        //     FROM bin_deposits
        //     JOIN bins ON bin_deposits.bin_id = bins.id
        //     WHERE bin_deposits.client_id = $client 
        //     AND bin_deposits.created_at BETWEEN '$startDate' AND '$endDate'
        // GROUP BY bin_deposits.client_id, bins.waste_type");
        //   if(!empty($binData)){
        //       foreach($binData as $key => $binrecord){
        //           // $bin = Bin::with('waste_scenario')->where('id',$binrecord->id)->first();
        //           $bin = WasteType::where('id',$binrecord->waste_type)->first();
        //           //dd($bin);
        //           $data[$key]['name'] = $bin->name;
        //           $data[$key]['total_quantity'] = $binrecord->total_quantity;
        //           $data[$key]['client_id'] = $binrecord->client_id;
        //       }
        //   }
        //           };
       
        return Response::json(['binRequests' => $data]);
    }
}