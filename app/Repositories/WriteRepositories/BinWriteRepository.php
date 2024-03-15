<?php

namespace App\Repositories\WriteRepositories;

use App\Exceptions\ModelClassNotFoundException;
use App\Models\Route;
use App\Models\Bin;
use App\Repositories\ReadRepositories\UserReadRepository;
use App\Tool\TelegramBot;
use App\Tool\ViberBot\Messages\ViberTextMessage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Str;

class BinWriteRepository extends WriteBaseRepository
{

    protected $modelClass = Bin::class;


    public function create(FormRequest|array $modelDetails)
    {

        $this->modelClass::create([
            'name' => trim($modelDetails->name),
            'bin_number' => trim($modelDetails->bin_number),
            "bin_type" => $modelDetails->bin_type,
            'lat' => $modelDetails->lat,
            'lng' => $modelDetails->lng,
            "bin_char" => $modelDetails->bin_char,
            "status" => $modelDetails->status,
            "capacity" => $modelDetails->capacity,
            "waste_type" => $modelDetails->waste_type,
            "bin_network" => $modelDetails->bin_network,
            "route_id" => $modelDetails->route_id,
            "iot_sensor_id" => $modelDetails->iot_sensor_id,
            "current_capacity" => $modelDetails->current_capacity,
            "bin_color" => $modelDetails->bin_color,
            "people_coverage" => $modelDetails->people_coverage,
        ]);

        $route = Route::where('id',$modelDetails->route_id)->first();
        if($route){
            $bin_count = Bin::where('route_id',$modelDetails->route_id)->count();
            $totalCapacity = Bin::where('route_id',$modelDetails->route_id)->sum('capacity');
            $peopleCoverage = Bin::where('route_id',$modelDetails->route_id)->sum('people_coverage');
            $route->total_bins_number = $bin_count;
            $route->total_bins_capacity = $totalCapacity/1000;
            $route->total_people_coverage = $peopleCoverage;
            $route->save();
        }
        
        
    }

    public function delete(Bin $bin)
    {
        $bin->delete();

    }


    public function markAsFull(Bin $bin)
    {
        $bin->completeness = 100;
        $bin->save();

       // $this->makeTelegramMessageForCollection($bin);
        $this->makeViberMessageForCollection($bin);
        return $bin;
    }


    private function makeViberMessageForCollection($model)
    {
        $userRepo = (new UserReadRepository())->getViberEmployesModels();
        $msg = new ViberTextMessage("$model->name FULL");

        $buttons = [
            [
                'text' => "🗑 $model->name",
                'url' => route("bins.show", ['bin' => $model->id]),
            ],

        ];

        if ($model->clients()->first()) {
            $client = $model->clients()->first();
            $buttons[]=[
                'text' => "🏢 $client->name",
                'url' => route("clients.show", ['client' => $client->id]),
            ];
        }

        foreach ($userRepo['data'] as $user) {

            $msg->SendButtons($user['viber_id'], $buttons );
        }


    }

    private function makeTelegramMessageForCollection($model)
    {
        $telegram = new TelegramBot(TelegramBot::WASTE_GROUP);
        $telegram->getUpdates();
        $buttons[] = $telegram->generateInlineButtons(["🗑 $model->name" => route("bins.show", ['bin' => $model->id])]);

        if ($model->clients()->first()) {
            $client = $model->clients()->first();
            $buttons[] = $telegram->generateInlineButtons(["🏢 $client->name" => route("clients.show", ['client' => $client->id])]);
        }


        $telegram->attachText("BIN [$model->name]  is Full \n\n  OWNED BY [$client->name]  ");
        $telegram->attachButtons($buttons);
        $telegram->sendRequest();
        $telegram->sendLocation(TelegramBot::WASTE_GROUP, $model->lat, $model->lng);

    }


}