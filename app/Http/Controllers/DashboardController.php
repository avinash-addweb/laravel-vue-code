<?php

namespace App\Http\Controllers;

use App\Providers\RouteServiceProvider;
use App\Repositories\BinRepository;
use App\Repositories\ReadRepositories\BinReadRepository;
use App\Repositories\ReadRepositories\TenantReadRepository;
use App\Repositories\ReadRepositories\WasteScenarioReadRepository;
use App\Repositories\ReadRepositories\VehicleDataReadRepository;
use App\Repositories\ReadRepositories\SensorDataReadRepository;
use App\Repositories\UserRepository;
use App\Tool\SaasView;
use Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use function Termwind\render;

class DashboardController extends Controller
{


    private BinReadRepository $binRepository;
    private TenantReadRepository $readTenantRepository;
    private VehicleDataReadRepository $vehicleDataRepository;
    private SensorDataReadRepository $sensorDataReadRepository;

    public function __construct(TenantReadRepository $readTenantRepository, 
    BinReadRepository $binRepository,
    WasteScenarioReadRepository $wasteScenarioRepository,
    VehicleDataReadRepository $vehicleDataRepository,
    SensorDataReadRepository $sensorDataReadRepository)
    {
        $this->binRepository = $binRepository;
        $this->readTenantRepository = $readTenantRepository;
        $this->wasteScenarioRepository = $wasteScenarioRepository;
        $this->vehicleDataRepository = $vehicleDataRepository;
        $this->sensorDataReadRepository = $sensorDataReadRepository;
    }

    public function index(Request $request)
    {
        if (tenant()) {

            $props=[];

            if(isset($request->startDate) && isset($request->endDate)){
                $startDate = $request->startDate;
                $endDate = $request->endDate;
            }
            $year = Carbon::parse($request->startDate)->year;

            $startDate1 = '2024-01-01';
            $endDate1 = date('Y-m-d');

            $currentYear = Carbon::now()->format('Y');

            //$props = SaasView::shareToSelectList($props,'graph_data_vehicles',$this->binRepository->getTenantWasteCategory());
            if($request->id=='waste_production_daily_production'){
                $props = SaasView::shareToSelectList($props,'waste_production_daily_production',$this->vehicleDataRepository->getTenantWasteTypeDailyProduction($startDate,$endDate));
            }else{
                $props = SaasView::shareToSelectList($props,'waste_production_daily_production',$this->vehicleDataRepository->getTenantWasteTypeDailyProduction($startDate1,$endDate1));
            }

            if($request->id=='waste_production_monthly_production'){
                $props = SaasView::shareToSelectList($props,'waste_production_monthly_production',$this->vehicleDataRepository->getTenantWasteTypeMonthlyProduction($startDate,$endDate));
            }else{
                $props = SaasView::shareToSelectList($props,'waste_production_monthly_production',$this->vehicleDataRepository->getTenantWasteTypeMonthlyProduction($startDate1,$endDate1));
            }

            if($request->id=='waste_production_yearly_production'){
                $props = SaasView::shareToSelectList($props,'waste_production_yearly_production',$this->vehicleDataRepository->getTenantWasteTypeYearlyProduction($startDate,$endDate));
            }else{
                $props = SaasView::shareToSelectList($props,'waste_production_yearly_production',$this->vehicleDataRepository->getTenantWasteTypeYearlyProduction($startDate1,$endDate1));
            }

            if($request->id=='waste_categories_year'){
                $props = SaasView::shareToSelectList($props,'waste_categories_year',$this->vehicleDataRepository->getTenantWasteCategoryYear($startDate,$endDate));
            }else{
                $currentYear = Carbon::now()->year;
                $props = SaasView::shareToSelectList($props,'waste_categories_year',$this->vehicleDataRepository->getTenantWasteCategoryYear($startDate1,$endDate1));
            }

            if($request->id=='waste_categories_target'){
                $props = SaasView::shareToSelectList($props,'waste_categories_target',$this->vehicleDataRepository->getTenantWasteCategoryTarget($currentYear));
            }else{
                $currentYear = Carbon::now()->year;
                $props = SaasView::shareToSelectList($props,'waste_categories_target',$this->vehicleDataRepository->getTenantWasteCategoryTarget($currentYear));
            }


            if($request->id=='daily_vehicle_capacity'){
                $props = SaasView::shareToSelectList($props,'daily_vehicle_capacity',$this->vehicleDataRepository->getTenantDailyVehicleCapaticty($startDate,$endDate));
            }else{
                $props = SaasView::shareToSelectList($props,'daily_vehicle_capacity',$this->vehicleDataRepository->getTenantDailyVehicleCapaticty($startDate1,$endDate1));
            }

            if($request->id=='monthly_vehicle_capacity'){
                $props = SaasView::shareToSelectList($props,'monthly_vehicle_capacity',$this->vehicleDataRepository->getTenantMonthlyVehicleCapaticty($startDate,$endDate));
            }else{
                $props = SaasView::shareToSelectList($props,'monthly_vehicle_capacity',$this->vehicleDataRepository->getTenantMonthlyVehicleCapaticty($startDate1,$endDate1));
            }

            if($request->id=='daily_average_bin_capacity'){
                $props = SaasView::shareToSelectList($props,'daily_average_bin_capacity',$this->vehicleDataRepository->getTenantDailyBinCapaticty($startDate,$endDate));
            }else{
                $props = SaasView::shareToSelectList($props,'daily_average_bin_capacity',$this->vehicleDataRepository->getTenantDailyBinCapaticty($startDate1,$endDate1));
            }

            if($request->id=='monthly_average_bin_capacity'){
                $props = SaasView::shareToSelectList($props,'monthly_average_bin_capacity',$this->vehicleDataRepository->getTenantMonthlyBinCapaticty($startDate,$endDate));
            }else{
                $props = SaasView::shareToSelectList($props,'monthly_average_bin_capacity',$this->vehicleDataRepository->getTenantMonthlyBinCapaticty($startDate1,$endDate1));
            }

            if($request->id=='daily_iot_bins_payt'){
                $props = SaasView::shareToSelectList($props,'daily_iot_bins_payt',$this->vehicleDataRepository->getTenantDailyIOTBinPayTData($startDate,$endDate));
            }else{
                $props = SaasView::shareToSelectList($props,'daily_iot_bins_payt',$this->vehicleDataRepository->getTenantDailyIOTBinPayTData($startDate1,$endDate1));
            }
            
            $props = SaasView::shareToDataTable($props,'bins',$this->binRepository->getFullBins());
           // $props = SaasView::shareToSelectList($props,'newbins',$this->binRepository->getNewFullBins());
            $props = SaasView::shareToSelectList($props,'dashbaords',$this->wasteScenarioRepository->getTenantDashboardCount());
            

            return SaasView::render('Tenant/Home', $props);

        }

    }

    public function dashboardIndex()
    {
        if (tenant()) {
            $props=[];
            //$props = SaasView::shareToSelectList($props,'graph_data',$this->binRepository->getTenantWasteCategory());
            //$props = SaasView::shareToSelectList($props,'graph_data1',$this->binRepository->getTenantWasteTypeWeight());
            $props = SaasView::shareToDataTable($props,'bins',$this->binRepository->getFullBins());
            $props = SaasView::shareToSelectList($props,'dashbaords',$this->wasteScenarioRepository->getTenantDashboardCount());
            //dd($props);
            return SaasView::render('Tenant/dashboard/index', $props);

        }

    }


    public function dashboardCitizenIndex(Request $request)
    {
        if (tenant()) 
        {
                $props=[];
                $data = $request->all();
                $currentMonth = Carbon::now()->format('m-Y');
               // $year = Carbon::parse($request->date)->year;
             
               if(isset($request->date)){
                    if($request->id == 'stackedBarChart' || $request->id == 'barChart'){
                        $date = Carbon::createFromFormat('Y', $request->date);
                        $month = '01';
                        $year = $date->format('Y');
                    }else{
                        $date = Carbon::createFromFormat('m-Y', $request->date);
                        $month = $date->format('m');
                        $year = $date->format('Y');
                        $lastDayOfMonth = $date->endOfMonth()->format('Y-m-d');
                        $startDate = $year.'-'.$month.'-'.'01';
                        $endDate = $lastDayOfMonth;
                    } 
                    
                }
                $year1 = Carbon::now()->year;
                $month1 = Carbon::now()->month;
                $startDate1 = $year1.'-'.$month1.'-'.'01';
                $endDate1 = date('Y-m-d');

                if($request->id=='pieChart'){
                    //$data['chart1'] = $this->sensorDataReadRepository->getCitizenBinDepositChart1Data($startDate,$endDate);
                    $props = SaasView::shareToSelectList($props,'graph_category_weight_pia_data',$this->sensorDataReadRepository->getCitizenBinDepositChart1Data($startDate,$endDate));
                }else{
                    $props = SaasView::shareToSelectList($props,'graph_category_weight_pia_data',$this->sensorDataReadRepository->getCitizenBinDepositChart1Data($startDate1,$endDate1));
                }
                    
                if(isset($request->id) && $request->id=='stackedBarChartWithGroup'){
                    $props = SaasView::shareToSelectList($props,'graph_category_point_coloumn_data',$this->sensorDataReadRepository->getCitizenBinDepositChart2Data($startDate,$endDate));
                }else{
                    $props = SaasView::shareToSelectList($props,'graph_category_point_coloumn_data',$this->sensorDataReadRepository->getCitizenBinDepositChart2Data($startDate1,$endDate1));
                }
                    
                if(isset($request->id) && $request->id=='stackedBarChart'){
                   
                    $props = SaasView::shareToSelectList($props,'graph_category_weight_bar_chart',$this->binRepository->getCitizenWasteWeightCategory($year));
                }else{
                    $newData = [];
                    $newData['id'] = 'Chart';
                    $newData['date'] = Carbon::now()->format('Y');
                    $props = SaasView::shareToSelectList($props,'graph_category_weight_bar_chart',$this->binRepository->getCitizenWasteWeightCategory($year1));
                }
                    
                if(isset($request->id) && $request->id=='barChart'){
                    $props = SaasView::shareToSelectList($props,'graph_category_points_bar_chart',$this->binRepository->getCitizenWastePointsCategory($year));
                }else{
                    $newData = [];
                    $newData['id'] = 'Chart';
                    $newData['date'] = $currentMonth;
                    $props = SaasView::shareToSelectList($props,'graph_category_points_bar_chart',$this->binRepository->getCitizenWastePointsCategory($year1));
                }


                if($request->id=='waste_categories_target'){
                    $props = SaasView::shareToSelectList($props,'waste_categories_target',$this->binRepository->getCitizenWasteCategoryTarget($startDate,$endDate));
                }else{
                    $currentYear = Carbon::now()->year;
                    $props = SaasView::shareToSelectList($props,'waste_categories_target',$this->binRepository->getCitizenWasteCategoryTarget($startDate1,$endDate1));
                }

                if($request->id=='total_co2_emission'){
                    $props = SaasView::shareToSelectList($props,'total_co2_emission',$this->sensorDataReadRepository->getCitizenBinDepositChart3Data($startDate,$endDate));
                }else{
                    $props = SaasView::shareToSelectList($props,'total_co2_emission',$this->sensorDataReadRepository->getCitizenBinDepositChart3Data($startDate1,$endDate1));
                }

            $props = SaasView::shareToSelectList($props,'graph_current_data',$this->binRepository->getCitizenCurrentPoints());
            $props = SaasView::shareToDataTable($props,'bins',$this->binRepository->getFullBins());
            $props = SaasView::shareToSelectList($props,'dashbaords',$this->wasteScenarioRepository->getCitizenDashboardCount());
         
            return SaasView::render('TenantClient/dashboard/index', $props);

        }

    }

}