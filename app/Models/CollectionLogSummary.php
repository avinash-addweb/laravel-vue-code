<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CollectionLogSummary extends Model
{
    use HasFactory;
    
    protected $table = 'collectionlog_summaries';

    public const TABLE_HEADERS = [
        'collected_date',
        'collected_time',
        'vehicle',
        'fuel_cost',
        'route_id',
        'receiver',
        'collection_percentage',
        'waste_weight',
        'input_type'
    ];
    
    protected $fillable = [
        'collected_date',
        'collected_time',
        'vehicle',
        'fuel_cost',
        'route_id',
        'receiver',
        'collection_percentage',
        'waste_weight',
        'waste_type',
        'input_type',
    ];
    protected $casts = [
        'collected_date' => 'date:d/m/Y',
    ];

    public function waste_type(): HasOne
    {
        return $this->HasOne(WasteType::class, 'id', 'waste_type');
    }

    public function route(): HasOne
    {
        return $this->HasOne(Route::class, 'id', 'route_id');
    }
}
