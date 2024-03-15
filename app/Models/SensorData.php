<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use App\Models\Bin;


class SensorData extends Model
{
    use HasFactory;
    protected $table = 'sensor_datas';
    protected $fillable = [
        'bin',
        'plate',
        'imei',
        'lat',
        'lon',
        'date_insert',
        'weight'
    ];
    public const TABLE_HEADERS = [
        [
            "title" => 'Date',
            "key" => 'created_date',
            "sortable" => true,
        ],
        [
            "title" => 'Time',
            "key" => 'created_time',
            "sortable" => true,
        ],
        [
            "title" => 'Bin',
            "key" => 'bin_name',
            "sortable" => true,
        ],
        [
            "title" => 'Waste Type',
            "key" => 'waste_type',
            "sortable" => true,
        ],
        [
            "title" => 'Weight',
            "key" => 'weight',
            "sortable" => true,
        ],
        [
            "title" => 'Address',
            "key" => 'address',
            "align" => "start"
            ],
        [
            "title" => 'PayT Points',
            "key" => 'payt_points_total',
            "sortable" => true,
        ],
        [
            "title" => 'Co2 Kg',
            "key" => 'co2kg_total',
            "sortable" => true,
        ]
    ];
    protected $with = ['bin','waste_type'];
    protected $casts = [
        '_created_at' => 'date:d/m/Y - H:i:s',
        '_date_insert' => 'date:d/m/Y'
    ];
    
    
    // Define custom attribute for getting time from timestamp
    public function getTimeAttribute()
    {
        return Carbon::parse($this->attributes['date_insert'])->format('H:i:s');
    }

    public function bin(): belongsTo
    {
        return $this->belongsTo(Bin::class);
    }
}
