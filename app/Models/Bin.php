<?php
namespace App\Models;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * App\Models\Bin
 *
 *
 */
class Bin extends Model
{
    use HasFactory,SoftDeletes;
    protected $cacheFor = null;
    public const TABLE_HEADERS = [
        [
            "title" => 'ID',
            "key" => 'id',
            "sortable" => true,
        ],
        [
            "title" => 'Name',
            "key" => 'name',
            "sortable" => true,
        ],
        [
            "title" => 'Waste Type',
            "key" => 'waste_type.name',
            "sortable" => true,
        ],
        [
            "title" => 'Capacity',
            "key" => 'capacity',
            "sortable" => true,
        ],
        [
            "title" => 'Bin Type',
            "key" => 'bin_type',
            "sortable" => true,
        ],
        // [
        //     "title" => 'Color',
        //     "key" => 'color',
        //     "sortable" => true,
        // ],
        [
            "title" => 'Route ID',
            "key" => 'route_id',
            "sortable" => false,
        ],
        [
            "title" => 'Bin Character',
            "key" => 'bin_char',
            "sortable" => true,
        ],
        "address",
        [
            "title" => 'Status',
            "key" => 'status',
            "sortable" => true,
        ],
        [
            "title" => 'IoT sensor ID',
            "key" => 'iot_sensor_id',
            "sortable" => false,
        ],
        [
            "title" => 'Capacity',
            "key" => 'current_capacity',
            "sortable" => true,
        ],
        "bin_network",
        [
            "title" => 'Completeness',
            "key" => 'completeness',
            "sortable" => true,
        ],
        // 'service_capacity',
//        'color','gps',
    ];
    protected $fillable = [
        'name',
        'bin_type',
        'bin_number',
        'address',
        'lat',
        'lng',
        'status',
        'completeness',
        'capacity',
        "bin_network",
        "bin_char",
        'waste_type',
        "iot_sensor_id",
        "current_capacity",
        "route_id",
        "people_coverage"
    ];
    protected $with = ['waste_scenario','waste_type'];
    protected $appends = ['color', 'gps', 'marker_options'];
    protected $casts = [
        'bin_network' => 'array',
    ];
    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class);
    }
    public function waste_type(): HasOne
    {
        return $this->HasOne(WasteType::class, 'id', 'waste_type');
    }
    public function routes(): BelongsToMany
    {
        return $this->belongsToMany(Route::class);
    }
    public function waste_scenario(): BelongsTo
    {
        return $this->belongsTo(WasteScenario::class,'waste_type');
    }
    protected function color(): Attribute
    {
        $waste_type = $this->waste_scenario;
        if (is_array($this->waste_scenario->bin_color)) {
            return new Attribute(
                get: fn() => $waste_type->bin_color[0],
            );
        } else {
            return new Attribute(
                get: fn() => "#a8a8a8",
            );
        }
    }
    protected function gps(): Attribute
    {
        return new Attribute(
            get: fn() => [(float)$this->lng, (float)$this->lat,],
        );
    }
    protected function markerOptions(): Attribute
    {
        return new Attribute(
            get: fn() => [
                'position' => [
                    "lng" => (float)$this->lng,
                    "lat" => (float)$this->lat
                ],
                "label" => $this->name,
                "title" => $this->name
            ],
        );
    }
    public function scopeFullBins($query, $value=90)
    {
        return $query->where('completeness','>=', $value);
    }
    public function scopeClientBins($query)
    {
        if (Auth::user()->isClient()) {
            $bin_ids=[];
            /** @var Client $client */
            $client = Auth::user()->client()->first();
            $bin_ids=$client->bins()->get()->pluck('id');
            $query->whereIn('id', $bin_ids);
        }
        return $query;
    }
}