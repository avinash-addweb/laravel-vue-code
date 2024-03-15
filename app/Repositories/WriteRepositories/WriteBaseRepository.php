<?php

namespace App\Repositories\WriteRepositories;

use App\Exceptions\ModelClassNotFoundException;
use App\Interfaces\WriteRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Str;

class WriteBaseRepository implements WriteRepositoryInterface
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

    protected $query=null;


    protected array $data = [];
    protected string $className;

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
            $this->query =  null;
        }
    }




    public function __toArray()
    {
        return get_object_vars($this);
    }

    protected function closure(){
        return $this->__toArray();
    }





    public function create(FormRequest|array $attributes)
    {
        $this->data=$this->modelClass::create($attributes);


        return $this->closure();


    }




    protected function attachWhereInFilter($key,$column=false): void
    {
        if ($this->filters){

            if (!$column){
                $column=$key;
            }

            $values=Arr::get($this->filters,$key);

            if ($values){
                if(is_null($this->query)){
                    throw new ModelClassNotFoundException("ModelClass must be defined");
                }
                $this->query->whereIn($column,$values);
            }


        }

    }

    protected function hasQueryOf($key): bool
    {
        return Arr::has($this->filters,$key);
    }
    protected function getQueryValues($key): array
    {
        return Arr::get($this->filters,$key);
    }




}