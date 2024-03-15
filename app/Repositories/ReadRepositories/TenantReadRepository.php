<?php
namespace App\Repositories\ReadRepositories;
use App\Models\Bin;
use App\Models\Tenant;
use App\Models\WasteType;
use App\Models\WasteScenario;
use App\Repositories\ReadRepositories\ReadBaseRepository;
use function Symfony\Component\Translation\t;
class TenantReadRepository extends ReadBaseRepository
{
    protected $modelClass = Tenant::class;
    public function __construct()
    {
        parent::__construct();
    }
    public function getPaginated()
    {
        $this->query = $this->modelClass::query();
        $this->pagination = $this->query->paginate(10)->toArray();
        return $this->closure();
    }
    public function getTenantConfig( $model){
        $tenant = Tenant::find($model->platform);
        $token = tenancy()->impersonate($tenant, 1, "/profile");
        $tenant_metrics = tenancy()->find($model->platform)->run(function () {
            return [
                'bins' => [
                    'count_all' =>00,
                    'count_under' => 100,
                    'count_over' => 200,
                ]
            ];
        });
        $platform = [
            "name" => $tenant->id,
            "owner_email" => $tenant->owner_email,
            "domains" => $tenant->domains,
            'waste_profiles'=>WasteScenario::whereIn('id',$tenant->waste_profiles)->get(['id','label','waste_type']),
            "tenant_metrics" => $tenant_metrics,
            "impersonate_token"=> "$token->token",
            "waste_receivers" => $tenant->waste_receivers,
            'map_center'=>$tenant->map_center
        ];
        return  $platform;
    }
  public function getTanants(){
        $tenants = Tenant::get();
        $platform =[];
        foreach($tenants as $k => $tenant){
            // dd($tenant->waste_profiles);
            $waste_types = WasteType::whereIn('id',$tenant->waste_profiles)->pluck('name','name')->toArray();
            if(isset($waste_types) && count($waste_types)>0){
                $waste_types = implode(",",$waste_types);
            }else{
                $waste_types = 'Βιοαπόβλητα';
            }
            $platform[$k]['name'] = $tenant->id;
            $platform[$k]['owner_email'] = $tenant->owner_email ?? 'n/a';
            $platform[$k]['domains'] = $tenant->domains[0]->domain ?? 'n/a';
            $platform[$k]['waste_profiles'] = $waste_types;
            $platform[$k]['waste_receivers'] = $tenant->waste_receivers;
            $platform[$k]['map_center'] = $tenant->map_center ?? 'n/a';
        }
        return  $platform;
    }
}