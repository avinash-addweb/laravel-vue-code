<?php

namespace App\Repositories\ReadRepositories;

use App\Exceptions\ModelClassNotFoundException;
use App\Models\ContactEnquiry;
use App\Exports\QuickExport;
use App\Repositories\ReadRepositories\ReadBaseRepository;
use Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Tenancy;
use Illuminate\Support\Collection;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Str;

class ContactEnquiryReadRepository extends ReadBaseRepository
{
    protected $modelClass = ContactEnquiry::class;



    public function getContactEnquiries()
    {
        $this->query=$this->modelClass::query();

        $this->pagination=$this->query->paginate(10)->toArray();

        return $this->closure();
    }
    
    public function getPaginated()
    {
        $this->query = $this->modelClass::query();
        $this->pagination = $this->query->with('user')->paginate(10)->toArray();
        return $this->closure();
    }


    public function getAdminPaginated()
    {
        $foo = new Tenancy();
        $tenants = Tenant::get();
        $combinedEnquiries = new Collection();
        foreach($tenants as $k => $tenant){
            $foo->initialize($tenant);
            $enquiries = ContactEnquiry::get();
            $combinedEnquiries = $combinedEnquiries->concat($enquiries);
        }
        $perPage = 10;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $items = $combinedEnquiries->slice(($page - 1) * $perPage, $perPage)->values();

        $pagination = new LengthAwarePaginator(
            $items,
            $combinedEnquiries->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        $this->pagination = $pagination->toArray();
        return $this->closure();
    }
    
    public function export()
    {
        $this->headers = [

            [
                "title" => 'id',
                "key" => 'id',
                "sortable" => true,
            ],
            [
                "title" => 'Title',
                "key" => 'title',
                "sortable" => true,
            ],
            [
                "title" => 'Description',
                "key" => 'description',
                "sortable" => true,
            ],

            [
                "title" => 'From',
                "key" => 'from_id',
                "sortable" => false,
            ],
            [
                "title" => 'Replied',
                "key" => 'is_reply',
                "sortable" => false,
            ],
            [
                "title" => 'Date',
                "key" => 'created_at',
                "sortable" => true,
            ]
        ];

        $export = new QuickExport(ContactEnquiry::query(),"ContactEnquiry",$this->headers);
        $name=$export->fileName;
        $export->store("public/$name");
        return $export;
    }

}
