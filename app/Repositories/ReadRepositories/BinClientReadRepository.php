<?php
namespace App\Repositories\ReadRepositories;
use App\Exceptions\ModelClassNotFoundException;
use App\Models\BinDeposit;
use App\Models\Client;
use App\Models\Tenant;
use App\Exports\QuickExport;
use App\Models\User;
use App\Notifications\InviteMember;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Auth;
use Str;
class BinClientReadRepository extends ReadBaseRepository
{
    protected $modelClass = BinDeposit::class;
    public function getClientDeposits($client)
    {
        $this->query=$this->modelClass::query();
        $this->query->OfClient($client->id);
        $this->pagination=$this->query->paginate(10)->toArray();
        return $this->closure();
    }
    public function getClientDepositHistory($id)
    {
        $this->query=$this->modelClass::query();
        $this->query->where('type','Custom')->OfClient($id);
        $this->pagination=$this->query->paginate(10)->toArray();
        return $this->closure();
    }

    public function getBinDeposits($bin)
    {
        $this->query=$this->modelClass::query();
        $this->query->where('bin_id',$bin->id);
        $this->pagination=$this->query->paginate(10)->toArray();
        return $this->closure();
    }
    public function getAllClientDeposits($data)
    {
        // dd($data);
        $client = Auth::user()->client;
        $this->query=$this->modelClass::query();
        $this->query->where('client_id',$client->id);
        
        if(isset($data)){
             if (isset($data['bin_id'])) {
                $this->query->where('bin_id', $data['bin_id']);
             }
            // if (isset($data['waste_type'])) {
            //         $this->query->whereIn('waste_type', $data['waste_type']);
            //     //}
            // }
            if (isset($data['quantity'])) {
                    $this->query->where('quantity', $data['quantity']);
            }
        }

        //dd($this->query->get());
        $this->pagination=$this->query->paginate(10)->toArray();
        return $this->closure();
    }



    public function bindepositeExport($bin)
    {
        $this->headers= [
            [
                "title" => 'id',
                "key" => 'id',
                "sortable" => true,
            ],
            [
                "title" => 'Bin',
                "key" => 'bin.name',
                "sortable" => true,
            ],
            [
                "title" => 'Client',
                "key" => 'client.name',
                "sortable" => true,
            ],
            [
                "title" => 'quantity',
                "key" => 'quantity',
                "sortable" => true,
            ],
            [
                "title" => 'Date',
                "key" => 'created_at',
                "sortable" => true,
            ]
        ];
        $export = new QuickExport(BinDeposit::query()->where('bin_id',$bin->id),"BinDeposit",$this->headers);
        $name=$export->fileName;
        $export->store("public/$name");
        return $export;
    }
    public function bindepositeClientExport($id)
    {
        $this->headers= [
            [
                "title" => 'id',
                "key" => 'id',
                "sortable" => true,
            ],
            [
                "title" => 'Bin',
                "key" => 'bin.name',
                "sortable" => true,
            ],
            [
                "title" => 'Client',
                "key" => 'client.name',
                "sortable" => true,
            ],
            [
                "title" => 'quantity',
                "key" => 'quantity',
                "sortable" => true,
            ],
            [
                "title" => 'Status',
                "key" => 'status',
                "sortable" => true,
            ],
            [
                "title" => 'Date',
                "key" => 'created_at',
                "sortable" => true,
            ]
        ];
        $export = new QuickExport(BinDeposit::query()->where('client_id',$id),"BinDepositHistory",$this->headers);
        $name=$export->fileName;
        $export->store("public/$name");
        return $export;
    }
}