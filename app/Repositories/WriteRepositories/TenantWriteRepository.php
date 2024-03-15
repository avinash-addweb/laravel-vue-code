<?php

namespace App\Repositories\WriteRepositories;

use App\Exceptions\ModelClassNotFoundException;
use App\Models\Tenant;
use App\Models\WasteType;
use App\Models\WasteCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;
use Stancl\Tenancy\Tenancy;
use Illuminate\Support\Facades\Log;
use App\Models\MasterData;
use Str;

class TenantWriteRepository extends WriteBaseRepository
{

    protected $modelClass = Tenant::class;


    public function update(FormRequest $request, $tenant)
    {
        $tenant = Tenant::find($tenant);
        $tenant->update(
            [
                'name' => $request->name,
                'mobile' => $request->mobile,
                'logo' => $request->logo,
                'waste_profiles' => $request->waste_profiles,
                'map_center' => [
                    "lng" => $request->map_center['lng'],
                    "lat" => $request->map_center['lat'],
                    "zoom" => $request->map_center['zoom']
                ]
            ]);
        return $tenant;

    }

    public function createMigration($masterdatas,$waste_types,$waste_categories,$tenant){
        // start Master Data Table Migration
        $masterData = [];
        foreach($masterdatas as $masterdata){
            $data['label'] = $masterdata->label;
            $data['module_type'] = $masterdata->module_type;
            $data['detail'] = $masterdata->detail;
            $data['created_at'] = $masterdata->created_at;
            $data['updated_at'] = $masterdata->updated_at;
            $masterData[] = $data; 
        }

        $waste_data = [];
        foreach($waste_types as $wastetype){
            $data1['name'] = $wastetype->name;
            $data1['waste_category_id'] = $wastetype->waste_category_id;
            $data1['co2_kg'] = $wastetype->co2_kg;
            $data1['payt_points'] = $wastetype->payt_points;
            $data1['denisity_kg_l'] = $wastetype->denisity_kg_l;
            $data1['created_at'] = $wastetype->created_at;
            $data1['updated_at'] = $wastetype->updated_at;
            $waste_data[] = $data1; 
        }

        $waste_category1 = [];
        foreach($waste_categories as $waste_category){
            $data2['label'] = $waste_category->label;
            $data2['targets'] = $waste_category->targets;
            $data2['created_at'] = $waste_category->created_at;
            $data2['updated_at'] = $waste_category->updated_at;
            $waste_category1[] = $data2; 
        }

        $general_settings = [];
        $general_settings['copyright_text'] = 'All Right Reserved By Waste Panel';
        $general_settings['logo'] = NULL;
        $general_settings['fevicon'] = NULL;
        $general_settings['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
        $general_settings['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');


        $tenants = Tenant::all();
        $db = new Tenancy();
        $db->initialize($tenant); 

        try {
            DB::beginTransaction();
            $query = "CREATE TABLE IF NOT EXISTS `master_datas` (id INT AUTO_INCREMENT PRIMARY KEY, label VARCHAR(255) DEFAULT NULL , detail VARCHAR(255) DEFAULT NULL, module_type VARCHAR(255) DEFAULT 'BinType', created_at timestamp, updated_at timestamp)";
            DB::statement($query);
            Log::debug("masterdata Logs");
            DB::table('master_datas')->insert($masterData);
//-----------------------------------------------------------------//
            $query1 = "CREATE TABLE IF NOT EXISTS `waste_types` (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) DEFAULT NULL , payt_points VARCHAR(255) DEFAULT NULL, denisity_kg_l VARCHAR(255) DEFAULT NULL, co2_kg DECIMAL(10,2) DEFAULT NULL, waste_category_id INT(11), created_at timestamp, updated_at timestamp)";
            DB::statement($query1);
            DB::table('waste_types')->insert($waste_data);
//-----------------------------------------------------------------//
            $query2 = "CREATE TABLE IF NOT EXISTS `waste_categories` (id INT AUTO_INCREMENT PRIMARY KEY, label VARCHAR(255) DEFAULT NULL , targets VARCHAR(255) DEFAULT NULL,  created_at timestamp, updated_at timestamp)";
            DB::statement($query2);
            Log::debug("waste_categories Logs");
            DB::table('waste_categories')->insert($waste_category1);
//-----------------------------------------------------------------//
            $query3 = "CREATE TABLE IF NOT EXISTS `general_settings` (id INT AUTO_INCREMENT PRIMARY KEY, copyright_text VARCHAR(255) DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, fevicon VARCHAR(255) DEFAULT NULL, created_at timestamp, updated_at timestamp)";
            DB::statement($query3);
            Log::debug("waste_cgeneral_settingsategories Logs");
            DB::table('general_settings')->insert($general_settings);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
      // End MasterData Migration
    }
}