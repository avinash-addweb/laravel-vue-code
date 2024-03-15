<?php

namespace App\Repositories\ReadRepositories;


use App\Exports\QuickExport;
use App\Models\Bin;
use App\Models\BinDeposit;
use App\Models\Client;
use App\Models\SensorData;
use App\Models\WasteType;
use App\Models\WasteCategory;
use Datatable;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

use Illuminate\Support\Facades\Auth;

class SensorDataReadRepository extends ReadBaseRepository
{

    protected $modelClass = SensorData::class;

    public function __construct()
    {
        parent::__construct();
    }

    //Citizen's My Deposit(RFID) Datatable Data
    public function getCitizenSensorData(){
        $data = DB::table('vehicle_sensors')
                    ->join('bins', 'vehicle_sensors.bin_sensor_ID', '=', 'bins.iot_sensor_id')
                    ->join('waste_types', 'bins.waste_type', '=', 'waste_types.id')
                    //->join('master_datas', 'waste_types.waste_category_id', '=', 'master_datas.id')
                    ->select(DB::raw('DATE(vehicle_sensors.date_insert) as created_date'),
                            DB::raw('TIME(vehicle_sensors.date_insert) as created_time'),
                            'waste_types.name as waste_type',
                            'vehicle_sensors.weight',
                            'bins.address',
                            'bins.name as bin_name',
                            DB::raw('waste_types.co2_kg * vehicle_sensors.weight as co2kg_total'),
                            DB::raw('waste_types.payt_points * vehicle_sensors.weight as payt_points_total'))
                    //->whereYear('sensor_datas.created_at', Carbon::now()->year)
                    //->whereYear('vehicle_sensors.date_insert', Carbon::now()->year)
                    ->where('vehicle_sensors.rfid', Auth::user()->client->rfid)
                    ->orderBy('vehicle_sensors.date_insert');
        // $data = DB::table('sensor_datas')
        //             ->join('bins', 'sensor_datas.bin', '=', 'bins.id')
        //             ->join('waste_types', 'bins.waste_type', '=', 'waste_types.id')
        //             ->join('bin_client', 'bin_client.bin_id', '=', 'bins.id')
        //             ->join('master_datas', 'waste_types.waste_category_id', '=', 'master_datas.id')
        //             ->select(DB::raw('DATE(sensor_datas.created_at) as created_date'),
        //                     DB::raw('TIME(sensor_datas.created_at) as created_time'),
        //                     'bins.name as bin_name',
        //                     'waste_types.name as waste_type',
        //                     'sensor_datas.weight',
        //                     'bins.address',
        //                     DB::raw('waste_types.payt_points * sensor_datas.weight as payt_points_total'), // Multiply payt_points with weight
        //                     DB::raw('waste_types.co2_kg * sensor_datas.weight as co2kg_total'))
        //             ->whereYear('sensor_datas.created_at', Carbon::now()->year)
        //             ->where('bin_client.client_id', Auth::user()->client->id);
        
        $this->pagination=$data->paginate(10)->toArray();
        
        return $this->closure();
    }

    //Citizen's My Deposit(RFID) Chart1 Data
    public function getCitizenBinDepositChart1Data($startDate,$endDate){
        $data = $this->citizenChart1MainData($startDate,$endDate);
        $final_data = [];
        $labels = [];
        $weight_array = [];
        $total_weight=0;
        foreach($data['result'] as $key => $datarecord){
            $x=0;
            foreach($datarecord as $k => $wastetype){
                $final_data['data'][] = $wastetype['weight'];
                $final_data['labels'][] = $k;
                $x++;
            }
        }

        return $final_data;
        
    }

 // Main Functionality for Calculating data
    function citizenChart1MainData($startDate,$endDate){
        $data = DB::table('vehicle_sensors')
                    ->join('bins', 'vehicle_sensors.bin_sensor_ID', '=', 'bins.iot_sensor_id')
                    ->join('waste_types', 'bins.waste_type', '=', 'waste_types.id')
                    ->join('waste_categories', 'waste_types.waste_category_id', '=', 'waste_categories.id')
                    ->select(DB::raw('DATE(vehicle_sensors.date_insert) as created_date'),
                            DB::raw('TIME(vehicle_sensors.date_insert) as created_time'),
                            'waste_types.name as waste_type',
                            'vehicle_sensors.weight',
                            'waste_categories.label as waste_category',
                            DB::raw('waste_types.payt_points * vehicle_sensors.weight as payt_points_total'))
                    //->whereYear('sensor_datas.created_at', Carbon::now()->year)
                    ->whereBetween('vehicle_sensors.date_insert', [ $startDate, $endDate ])
                    ->where('vehicle_sensors.rfid', Auth::user()->client->rfid)
                    ->orderBy('vehicle_sensors.date_insert')->get();
        
        $waste_types = WasteCategory::pluck('label','label')->toArray();
        $waste_type_array = [];
        foreach($data as $dataval){
            if(array_search($dataval->waste_category,$waste_types)>0){
                $waste_type_array[] = $dataval->waste_category;
            }
        }
        $waste_type_array = array_unique($waste_type_array);
        $waste_type_array = array_values($waste_type_array);
        $result = [];
        $dataArray = [];
        // Group the data by date and waste type
        foreach ($data as $item) {
            $monthDate = Carbon::parse($item->created_date);
            $date = $monthDate->format('Y');
            //$date = $item->created_date;
            $waste_type = $item->waste_category;
            $weight = $item->weight;
            $payt_points_total = $item->payt_points_total;
            if (!isset($result[$date])) {
                $result[$date] = [];
            }
        
            if (!isset($result[$date][$waste_type])) {
                // Initialize a new entry for the waste type
                $result[$date][$waste_type] = [
                    'weight' => 0,
                    'payt_points' => 0,
                ];
            }
            // Add the weight and payt_points_total to the sum for the waste type on the current date
            $result[$date][$waste_type]['weight'] += $weight;
            $result[$date][$waste_type]['payt_points'] += $payt_points_total;
        }
        
        // Add all waste types as headers
        foreach ($result as &$dateData) {
            foreach ($waste_type_array as $waste_type) {
                if (!isset($dateData[$waste_type])) {
                    $dateData[$waste_type] = [
                        'weight' => 0,
                        'payt_points' => 0,
                    ];
                }
            }
        }
        $dataArray['result'] = $result;
        foreach($dataArray['result'] as $key => $datarecord){
            krsort($datarecord);
            $dataArray['result'][$key] = $datarecord;
        }
        rsort($waste_type_array);

        $dataArray['waste_type'] = $waste_type_array;
        return $dataArray;
    }


    //Citizen's My Deposit(RFID) Chart2  Data
    public function getCitizenBinDepositChart2Data($startDate,$endDate){
        $data = $this->citizenChart2MainData($startDate,$endDate);
        $final_data = [];
        $labels = [];
        $total_weight=0;
        foreach($data['result'] as $key => $datarecord){
            $x=0;
            foreach($datarecord as $k => $wastetype){
                
                $final_data['data'][] = $wastetype['payt_points'];
                $final_data['labels'][] = $k;
                $x++;
            }
        }
        return $final_data;
        
    }


    // Main Functionality for Calculating data
    function citizenChart2MainData($startDate,$endDate){
        $data = DB::table('vehicle_sensors')
                    ->join('bins', 'vehicle_sensors.bin_sensor_ID', '=', 'bins.iot_sensor_id')
                    ->join('waste_types', 'bins.waste_type', '=', 'waste_types.id')
                    ->join('waste_categories', 'waste_types.waste_category_id', '=', 'waste_categories.id')
                    ->select(DB::raw('DATE(vehicle_sensors.date_insert) as created_date'),
                            DB::raw('TIME(vehicle_sensors.date_insert) as created_time'),
                            'waste_types.name as waste_type',
                            'vehicle_sensors.weight',
                            'waste_categories.label as waste_category',
                            DB::raw('waste_types.payt_points * vehicle_sensors.weight as payt_points_total'))
                    //->whereYear('sensor_datas.created_at', Carbon::now()->year)
                    ->whereBetween('vehicle_sensors.date_insert', [ $startDate, $endDate ])
                    ->where('vehicle_sensors.rfid', Auth::user()->client->rfid)
                    ->orderBy('vehicle_sensors.date_insert')->get();
       
        // $data = DB::table('sensor_datas')
        //             ->join('bins', 'sensor_datas.bin', '=', 'bins.id')
        //             ->join('waste_types', 'bins.waste_type', '=', 'waste_types.id')
        //             ->join('bin_client', 'bin_client.bin_id', '=', 'bins.id')
        //             ->join('master_datas', 'waste_types.waste_category_id', '=', 'master_datas.id')
        //             ->select(DB::raw('DATE(sensor_datas.created_at) as created_date'),
        //                     DB::raw('TIME(sensor_datas.created_at) as created_time'),
        //                     'waste_types.name as waste_type',
        //                     'sensor_datas.weight',
        //                     DB::raw('waste_types.payt_points * sensor_datas.weight as payt_points_total'), // Multiply payt_points with weight
        //                     DB::raw('sensor_datas.weight'))
        //             //->whereYear('sensor_datas.created_at', Carbon::now()->year)
        //             ->whereBetween('sensor_datas.created_at', [ $startDate, $endDate ])
        //             ->where('bin_client.client_id', Auth::user()->client->id)
        //             ->orderBy('sensor_datas.created_at')->get();

        $waste_types = WasteType::pluck('name','name')->toArray();
        $waste_type_array = [];
        foreach($data as $dataval){
            if(array_search($dataval->waste_type,$waste_types)>0){
                $waste_type_array[] = $dataval->waste_type;
            }
        }
        $waste_type_array = array_unique($waste_type_array);
        $waste_type_array = array_values($waste_type_array);

        $result = [];
        $dataArray = [];
        // Group the data by date and waste type
        foreach ($data as $item) {
            $monthDate = Carbon::parse($item->created_date);
            $date = $monthDate->format('Y');
            $waste_type = $item->waste_type;
            $weight = $item->weight;
            $payt_points_total = $item->payt_points_total;

            if (!isset($result[$date])) {
                $result[$date] = [];
            }
        
            if (!isset($result[$date][$waste_type])) {
                // Initialize a new entry for the waste type
                $result[$date][$waste_type] = [
                    'weight' => 0,
                    'payt_points' => 0,
                ];
            }
        
            // Add the weight and payt_points_total to the sum for the waste type on the current date
            $result[$date][$waste_type]['weight'] += $weight;
            $result[$date][$waste_type]['payt_points'] += $payt_points_total;
        }
        
        // Add all waste types as headers
        foreach ($result as &$dateData) {
            foreach ($waste_type_array as $waste_type) {
                if (!isset($dateData[$waste_type])) {
                    $dateData[$waste_type] = [
                        'weight' => 0,
                        'payt_points' => 0,
                    ];
                }
            }
        }

        $dataArray['result'] = $result;
        foreach($dataArray['result'] as $key => $datarecord){
            krsort($datarecord);
            $dataArray['result'][$key] = $datarecord;
        }
        rsort($waste_type_array);
        
        $dataArray['waste_type'] = $waste_type_array;
        return $dataArray;
    }
    //Citizen's My Deposit(RFID) Chart1 Data
    public function getCitizenBinDepositChart3Data($startDate,$endDate){
        $data = $this->citizenChart3MainData($startDate,$endDate);
        $final_data = [];
        $k = 0;
        $total_weight=0;
        foreach($data['result'] as $key => $datarecord){
            $x=0;
            foreach($datarecord as $k => $wastetype){
                $final_data['data'][] = $wastetype['payt_points'];
                $final_data['labels'][] = $k;
                $x++;
            }
        }
        return $final_data;

        
       /*foreach($data['result'] as $key => $datarecord){
            //$final_data[$k]['Date'] =  $key;
            $sum_weight = 0;
            $sum_points = 0;
            foreach($datarecord as $name => $wastetype){
                $sum_weight = $sum_weight + $wastetype['weight'];
                $sum_points = $sum_points + $wastetype['payt_points'];
                $final_data[$k][$name] =  $wastetype['weight'];
            }
            $final_data[$k]['Sum'] =  $sum_weight;
            $final_data[$k]['Points'] = $sum_points;
            $k++;
       }*/

      // return $final_data;
    }


        //Citizen's My Deposit(RFID) Chart1 Data
    public function getCitizenBinDepositChart3Table(){
           /* $data = $this->citizenChart3MainData();
            $final_data = [];
            $k = 0;
            
           foreach($data['result'] as $key => $datarecord){
                $final_data[$k]['Date'] =  $key;
                $sum_weight = 0;
                $sum_points = 0;
                foreach($datarecord as $name => $wastetype){
                    $sum_weight = $sum_weight + $wastetype['weight'];
                    $sum_points = $sum_points + $wastetype['payt_points'];
                    $final_data[$k][$name] =  $wastetype['weight'];
                }
                $final_data[$k]['Sum'] =  $sum_weight;
                $final_data[$k]['Points'] = $sum_points;
                $k++;
           }
    
           return $final_data; */
        }


    // Main Functionality for Calculating data
    function citizenChart3MainData($startDate,$endDate){
        $data = DB::table('vehicle_sensors')
                    ->join('bins', 'vehicle_sensors.bin_sensor_ID', '=', 'bins.iot_sensor_id')
                    ->join('waste_types', 'bins.waste_type', '=', 'waste_types.id')
                    //->join('master_datas', 'waste_types.waste_category_id', '=', 'master_datas.id')
                    ->select(DB::raw('DATE(vehicle_sensors.date_insert) as created_date'),
                            DB::raw('TIME(vehicle_sensors.date_insert) as created_time'),
                            'waste_types.name as waste_type',
                            'vehicle_sensors.weight',
                            DB::raw('waste_types.co2_kg * vehicle_sensors.weight as payt_points_total'))
                    //->whereYear('sensor_datas.created_at', Carbon::now()->year)
                    ->whereBetween('vehicle_sensors.date_insert', [ $startDate, $endDate ])
                    ->where('vehicle_sensors.rfid', Auth::user()->client->rfid)
                    ->orderBy('vehicle_sensors.date_insert')->get();

        // $data = DB::table('sensor_datas')
        //             ->join('bins', 'sensor_datas.bin', '=', 'bins.id')
        //             ->join('waste_types', 'bins.waste_type', '=', 'waste_types.id')
        //             ->join('bin_client', 'bin_client.bin_id', '=', 'bins.id')
        //             ->join('master_datas', 'waste_types.waste_category_id', '=', 'master_datas.id')
        //             ->select(DB::raw('DATE(sensor_datas.created_at) as created_date'),
        //                     DB::raw('TIME(sensor_datas.created_at) as created_time'),
        //                     'waste_types.name as waste_type',
        //                     'sensor_datas.weight',
        //                     DB::raw('waste_types.payt_points * sensor_datas.weight as payt_points_total'), // Multiply payt_points with weight
        //                     DB::raw('sensor_datas.weight'))
        //             ->where('bin_client.client_id', Auth::user()->client->id)
        //             ->whereBetween('sensor_datas.created_at', [ $startDate, $endDate ])
        //             ->orderBy('sensor_datas.created_at')->get();

        $waste_types = WasteType::pluck('name','name')->toArray();
        $waste_type_array = [];
        foreach($data as $dataval){
            if(array_search($dataval->waste_type,$waste_types)>0){
                $waste_type_array[] = $dataval->waste_type;
            }
        }
        $waste_type_array = array_unique($waste_type_array);

        $result = [];
        $dataArray = [];
        // Group the data by date and waste type
        foreach ($data as $item) {
            $monthDate = Carbon::parse($item->created_date);
            $date = $monthDate->format('Y');
            $waste_type = $item->waste_type;
            $weight = $item->weight;
            $payt_points_total = $item->payt_points_total;

            if (!isset($result[$date])) {
                $result[$date] = [];
            }
        
            if (!isset($result[$date][$waste_type])) {
                // Initialize a new entry for the waste type
                $result[$date][$waste_type] = [
                    'weight' => 0,
                    'payt_points' => 0,
                ];
            }
        
            // Add the weight and payt_points_total to the sum for the waste type on the current date
            $result[$date][$waste_type]['weight'] += $weight;
            $result[$date][$waste_type]['payt_points'] += $payt_points_total;
        }
        
        // Add all waste types as headers
        foreach ($result as &$dateData) {
            foreach ($waste_type_array as $waste_type) {
                if (!isset($dateData[$waste_type])) {
                    $dateData[$waste_type] = [
                        'weight' => 0,
                        'payt_points' => 0,
                    ];
                }
            }
        }

        $dataArray['result'] = $result;
        $dataArray['waste_type'] = $waste_type_array;  

        return $dataArray;
    }


    

    public function export()
    {
        $this->headers= [
            [
                "title" => 'id',
                "key" => 'id',
                "sortable" => true,
            ],

            [
                "title" => 'Bin Name',
                "key" => 'name',
                "sortable" => true,
            ],
            [
                "title" => 'bin_char',
                "key" => 'bin_char',
                "sortable" => true,
            ],

            [
                "title" => 'bin_type',
                "key" => 'bin_type',
                "sortable" => true,
            ],
            [
                "title" => 'Latitude',
                "key" => 'lat',
                "sortable" => true,
            ],
            [
                "title" => 'Longitude',
                "key" => 'lng',
                "sortable" => true,
            ],
            //   'address',
//        'lat',
//        'lng',
            [
                "title" => 'status',
                "key" => 'status',
                "sortable" => true,
            ],
            [
                "title" => 'completeness',
                "key" => 'completeness',
                "sortable" => true,
            ],
            [
                "title" => 'capacity',
                "key" => 'capacity',
                "sortable" => true,
            ],
            'service_capacity',
            "bin_network",
//        'color','gps',

        ];



        $export = new QuickExport(Bin::query(),"Bins",$this->headers);
        $name=$export->fileName;
        $export->store("public/$name");
        return $export;


    }

}
