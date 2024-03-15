<?php
namespace App\Http\Controllers;
use App\Models\Client;
use App\Models\Bin;
use App\Repositories\ReadRepositories\BinClientReadRepository;
use App\Repositories\ReadRepositories\BinReadRepository;
use App\Repositories\ReadRepositories\ClientReadRepository;
use App\Repositories\WriteRepositories\ClientWriteRepository;
use App\Tool\SaasView;
use File;
use Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Response;
use Storage;
class ClientAnalyticsController extends Controller
{
    private ClientReadRepository $clientReadRepository;
    private BinReadRepository $binRead;
    public function __construct(BinReadRepository $binRead,
        ClientReadRepository    $clientReadRepository,
        )
    {
        $this->clientReadRepository = $clientReadRepository;
        $this->readBin = $binRead;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // /dd($request->all());
        $props=[];
        $bins = Bin::take(100)->get()->toArray();
        $props = SaasView::shareToSelectList($props, 'bins', $bins);
        // $bins = $this->readBin->getClientBinSearch();
        $clients = Client::take(100)->get()->toArray();
        $props = SaasView::shareToDataTable($props, 'deposits', $this->clientReadRepository->getAllClientDeposits($request));
        $props = SaasView::shareToSelectList($props, 'clients', $clients);
        // $props = SaasView::shareToSelectList($props, 'bins', $bins);
        return SaasView::render('Tenant/ClientsAnalytics/Index',
            $props
        );
        //
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
    public function store(Request $request)
    {
        //
    }
    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        //
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        //
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        //
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        //
    }
    public function export(Request $request){
        $file=$this->clientReadRepository->export();
        return Inertia::location(route("downloader",['filename'=> $file->fileName]));
    }
    public function clientsAnalyticsChart(Request $request)
    {
       // dd($request->all());
        $client = Auth::user()->client->id;
        $startDate = $request->startDate;
        $endDate = $request->endDate;
        $data =[];
        if(!empty($request->filter) && $request->filter=="bin"){
            $binData = DB::select("SELECT bin_deposits.bin_id, bin_deposits.client_id, bins.name, SUM(bin_deposits.quantity) as total_quantity
            FROM bin_deposits
            JOIN bins ON bin_deposits.bin_id = bins.id
            WHERE bin_deposits.client_id = $client 
            AND bin_deposits.created_at BETWEEN '$startDate' AND '$endDate'
            GROUP BY bin_deposits.bin_id, bin_deposits.client_id, bins.name");
             $data =$binData;
        } 
        else if(!empty($request->filter) && $request->filter=="wasteType"){
            $binData = DB::select("SELECT bin_deposits.client_id, bins.waste_type, SUM(bin_deposits.quantity) as total_quantity
            FROM bin_deposits
            JOIN bins ON bin_deposits.bin_id = bins.id
            WHERE bin_deposits.client_id = $client 
            AND bin_deposits.created_at BETWEEN '$startDate' AND '$endDate'
        GROUP BY bin_deposits.client_id, bins.waste_type");
          if(!empty($binData)){
              foreach($binData as $key => $binrecord){
                  // $bin = Bin::with('waste_scenario')->where('id',$binrecord->id)->first();
                  $bin = WasteType::where('id',$binrecord->waste_type)->first();
                  //dd($bin);
                  $data[$key]['name'] = $bin->name;
                  $data[$key]['total_quantity'] = $binrecord->total_quantity;
                  $data[$key]['client_id'] = $binrecord->client_id;
              }
          }
        };
        return Response::json(['binRequests' => $data]);
    }
}