<?php

namespace App\Repositories\ReadRepositories;

use App\Exceptions\ModelClassNotFoundException;

use App\Models\Bin;
use App\Models\Client;
use App\Models\Route;
use App\Repositories\ReadRepositories\ReadBaseRepository;
use Arr;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Auth;
use phpDocumentor\Reflection\Types\Parent_;
use Str;
use function Symfony\Component\Translation\t;

class RouteReadRepository extends ReadBaseRepository
{

    protected $modelClass = Route::class;

    public function __construct()
    {
        parent::__construct();


    }


    public function getAll()
    {
        $this->query = $this->modelClass::query();


        $this->data = $this->query->get()->toArray();

        return $this->closure();
    }





}