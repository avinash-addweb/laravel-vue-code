<?php
namespace App\Repositories\ReadRepositories;
use App\Exports\QuickExport;
use App\Models\Route;
use App\Repositories\ReadRepositories\ReadBaseRepository;
use Arr;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
class RouteRepository extends ReadBaseRepository
{
    protected $modelClass = Route::class;
    public function __construct()
    {
        parent::__construct();
    }
    public function export(){
        $this->headers= [
            'name',
           [
            "title" => 'waste_type',
            "key" => 'waste_type.name',
           ],
            'weekly_collection_rate',
            'toll_cost',
            'note',
            'location',
            'total_bins_capacity',
            'total_people_coverage',
            'total_bins_number',
            'total_distance',
            'gpx',
            // 'name',
            // 'waste_type',
            // 'weekly_collection_rate',
            // 'toll_cost',
            // 'note',
            // 'gpx',
            // 'created_at'
        ];
        $export = new QuickExport(Route::query(),"Routes",$this->headers);
        $name=$export->fileName;
        $export->store("public/$name");
        return $export;
    }
}