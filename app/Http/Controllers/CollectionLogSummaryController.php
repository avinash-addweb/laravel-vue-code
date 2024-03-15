<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Tool\SaasView;
use Inertia\Inertia;
use App\Models\Route;
use App\Models\CollectionLogSummary;
use App\Repositories\ReadRepositories\CollectionLogSummaryReadRepository;
use App\Repositories\ReadRepositories\VehicleDataReadRepository;
use Carbon\Carbon;

class CollectionLogSummaryController extends Controller
{

    private CollectionLogSummaryReadRepository $collectionLogSummeryReadRepository;

    public function __construct(CollectionLogSummaryReadRepository $collectionLogSummeryReadRepository)
    {
        $this->collectionLogSummeryReadRepository = $collectionLogSummeryReadRepository;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // dd($request);
        $props=[];
        $currentYear = Carbon::now()->year;
        $januaryFirst = Carbon::createFromDate($currentYear, 1, 1)->toDateString();
        $currentDate = Carbon::now()->format('Y-m-d');
        if(!empty($request->startDate) && !empty($request->endDate)){
            $startDate = $request->startDate;
            $endDate = $request->endDate;
            $type = $request->type;
        }else{
            $startDate = $januaryFirst;
            $endDate = $currentDate;
            $type = 'All';
        }
        $props= SaasView::shareToDataTable($props,"route_collection",$this->collectionLogSummeryReadRepository->getCollectionLogSummery($type,$startDate,$endDate));

        return SaasView::render('Tenant/CollectionLogSummary/Index', $props);
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
        $this->collectionLogSummeryReadRepository->create($request);

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $route = Route::where('name',$request->route_id)->first();
        $data['fuel_cost'] = $request->fuel_cost;
        $data['route_id'] = $route->id;
        $data['receiver'] = $request->receiver;
        $data['collection_percentage'] = $request->collection_percentage;
        $this->collectionLogSummeryReadRepository->update($data,$request);
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

        /** 
     * Export CSV file data
     */
    public function export(Request $request){
        $ids = $request->ids;
        $file=$this->collectionLogSummeryReadRepository->export($ids);
        return Inertia::location(route("downloader",['filename'=> $file->fileName]));

    }
}
