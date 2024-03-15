<?php

namespace App\Repositories\ReadRepositories;

use App\Exceptions\ModelClassNotFoundException;

use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Tenancy;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Str;
use App\Models\CollectionLogSummary;
use App\Repositories\ReadRepositories\ReadBaseRepository;
use Arr;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Exports\QuickExport;
use phpDocumentor\Reflection\Types\Parent_;
use function Symfony\Component\Translation\t;


class CollectionLogSummaryReadRepository extends ReadBaseRepository
{

    protected $modelClass = CollectionLogSummary::class;

    public function __construct()
    {
        parent::__construct();


    }


    public function getCollectionLogSummery($type,$startDate,$endDate)
    {
        $foo = new Tenancy();
        $tenant1 = tenant();
        $tenantUser = Tenant::where('id',$tenant1->id)->first();
        $tenants = Tenant::get();
        foreach($tenants as $k => $tenant){
            if($tenant->id == $tenantUser->id){
                $foo->initialize($tenant);
                
                if($type != 'All'){
                    $wasteTypes = DB::table('collectionlog_summaries')
                                ->whereBetween('collectionlog_summaries.collected_date', [ $startDate, $endDate ])
                                ->where('input_type',$type)
                                ->orderBy('id','asc');
                }else{
                    $wasteTypes = DB::table('collectionlog_summaries')
                                ->whereBetween('collectionlog_summaries.collected_date', [ $startDate, $endDate ])
                                ->orderBy('id','asc');
                }

                $this->pagination=$wasteTypes->paginate(10)->toArray();
            }
        }
        return $this->closure();
    }

    public function export($ids)
    {
        $this->headers= [
        'collected_date',
        'collected_time',
        'vehicle',
        'fuel_cost',
        'route_id',
        'receiver',
        'collection_percentage',
        'waste_weight',
        'input_type'
        ];

        $export = new QuickExport(CollectionLogSummary::query()->whereIn('id',$ids),"collectionlog_summeries",$this->headers);
        $name=$export->fileName;
        $export->store("public/$name");
        return $export;
    }

    public function update($data,$request){
        $logs = CollectionLogSummary::find($request->id);
        $logs->update($data);
    }
}
