<?php

namespace App\Repositories\ReadRepositories;

use App\Exceptions\ModelClassNotFoundException;
use App\Interfaces\ModelRepositoryInterface;
use App\Interfaces\ReadRepositoryInterface;
use App\Tool\VueNotification;
use Arr;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Str;

class ReadBaseRepository implements  ReadRepositoryInterface
{

    /**
     * @var Model
     */
    protected $modelClass = null;


    protected $class_name = null;

    protected $singular = null;

    protected $plural = null;

    protected $fillable = null;
    protected $headers = null;

    /**
     * @var Builder|null
     */
    protected Builder|null $query=null;


    protected array $data = [];
    protected array|false $pagination =false;
    protected string $className;
    private string|array|null $filters = null;

    /**
     * @throws ModelClassNotFoundException
     */
    public function __construct()
    {
        if ($this->modelClass == null) {
            throw new ModelClassNotFoundException("ModelClass must be defined");
        } else {
            $this->className = class_basename($this->modelClass);
            $this->class_name = $this->className;
            $this->singular = Str::lower(Str::singular($this->className));
            $this->plural = Str::snake(Str::plural($this->className));
            $this->fillable = (new $this->modelClass())->getFillable();
            $this->headers = $this->modelClass::TABLE_HEADERS;
            $this->filters=request()->query($this->plural);
            $this->search=request()->query('search');
            $this->query =  null;
            $this->path = url()->current();
        }
    }




    public function __toArray()
    {
        if ($this->pagination)
        {
            $data=array_merge(get_object_vars($this),$this->pagination);
            unset($data['pagination']);
            return $data;
        }
        return get_object_vars($this);
    }

    protected function closure(){
        return $this->__toArray();
    }

    public function getPaginated()
    {
        $this->query=$this->modelClass::query();


        $this->pagination=$this->query->paginate(10)->toArray();

        return $this->closure();


    }


    public function getAll()
    {
        $this->query=$this->modelClass::query();

        $this->data=$this->query->get()->toArray();

        return $this->closure();

    }













    protected function hasQueryOf($key): bool
    {
        return Arr::has($this->filters,$key);
    }
    protected function getQueryValues($key): array
    {
        return Arr::get($this->filters,$key);
    }

    protected function hasSearch(): bool
    {
        return !is_null($this->search);
    }






}