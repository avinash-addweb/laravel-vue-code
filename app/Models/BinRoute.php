<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\BinRoute
 *
 * @property-read \App\Models\Route|null $route
 * @method static \Database\Factories\BinRouteFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|BinRoute newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BinRoute newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BinRoute query()
 * @mixin \Eloquent
 */
class BinRoute extends Model
{
    use HasFactory;

     protected $fillable=[
            'route_id',
			'bin_id',
			'route_position'];

    public function route(): HasOne
    {
        return $this->hasOne(Route::class);
    }


}
