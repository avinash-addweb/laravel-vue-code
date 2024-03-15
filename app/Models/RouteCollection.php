<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\RouteCollection
 *
 * @method static \Database\Factories\RouteCollectionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|RouteCollection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RouteCollection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RouteCollection query()
 * @mixin \Eloquent
 */
class RouteCollection extends Model
{
    use HasFactory,SoftDeletes;

    public const TABLE_HEADERS = [
        'collected_date',
        'vehicle_id',
        'fuel_cost',
        'route_id',
        'receiver',
        'collected_percentage',
        'collected_waste_mass'
    ];
    protected $attributes = [
        'collected_percentage' => 100,
    ];


    protected $fillable = [
        'collected_date',
        'vehicle_id',
        'fuel_cost',
        'route_id',
        'receiver',
        'collected_percentage',
        'collected_waste_mass'
    ];
    protected $casts = [
        'collected_date' => 'date:d/m/Y - H:i:s',
        "collected_percentage"=>'integer'
    ];


    /*@todo Relations*/
}
