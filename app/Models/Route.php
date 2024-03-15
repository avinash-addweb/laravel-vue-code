<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;

/**
 * App\Models\Route
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Bin> $bins
 * @property-read int|null $bins_count
 * @method static \Database\Factories\RouteFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Route newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Route newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Route query()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Bin> $bin
 * @mixin \Eloquent
 */
class Route extends Model
{
    use HasFactory,SoftDeletes;


    public const TABLE_HEADERS = [
        ["title" => 'name', "key" => 'name',"sortable" => true],
        [
            "title" => 'waste type',
            "key" => 'waste_type.name',
            "align" => "start",
            "sortable" => true
        ],
        ["title" => 'weekly collection rate', "key" => 'weekly_collection_rate',"sortable" => true],
        [
            "title" => 'toll distance (km)',
            "key" => 'toll_distance',
            "sortable" => true,
        ],
        ["title" => 'toll cost', "key" => 'toll_cost',"sortable" => true],
        ["title" => 'other costs', "key" => 'other_costs',"sortable" => true],
        [
            "title" => 'toll bins number (stops)',
            "key" => 'total_bins_number',
            "sortable" => true,
        ],
        ["title" => 'toll bins capacity', "key" => 'total_bins_capacity',"sortable" => true],
        ["title" => 'toll people coverage', "key" => 'total_people_coverage',"sortable" => true],
        ["title" => 'note', "key" => 'note'],
        ["title" => 'gpx', "key" => 'gpx']
    ];
    

    protected $fillable = [
        'name',
        'waste_type',
        'weekly_collection_rate',
        'toll_cost',
        'total_bins_number',
        'total_people_coverage',
        'total_bins_capacity',
        'note',
        'gpx'
    ];

    protected $with = ['bins', 'waste_type'];

    protected $appends = ['avg_bin_center'];


    public function bins(): BelongsToMany
    {
        return $this->belongsToMany(Bin::class);
    }

    public function waste_type(): HasOne
    {
        return $this->HasOne(WasteType::class,'id', 'waste_type');
    }

    public function waste_scenario(): HasOne
    {
        return $this->HasOne(WasteScenario::class, 'id', 'waste_type');
    }


    public function addBin(Bin $bin)
    {
        $old = $this->getBins();
        $count = count($old);
        try {
            $this->bins()->attach($bin, ['route_position' => $count + 1]);
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23000') {
                dd($exception);
            } else {
                dd($exception);
            }
        }
    }

    public function getBins(): Collection
    {

        return $this->bins()->orderBy('route_position')->get();
    }


    public function getAllowedBins()
    {
        return Bin::where(['waste_type' => $this->waste_type])
            ->whereNotIn('id',
                $this->getBins()->pluck('id')
            )->get();
    }


    public function addBinAfter(Bin $target_bin, Bin $bin,)
    {


        $old_bins = $this->getBins();

        $this->bins()->detach($old_bins);

        foreach ($old_bins as $old_bin) {
            if ($old_bin->id === $target_bin->id) {
                $this->addBin($old_bin);
                $this->addBin($bin);
            } else {
                $this->addBin($old_bin);
            }

        }


    }

    public function replaceAllRouteBins(array $new_bins_id)
    {


        $old_bins = $this->getBins();
        $this->bins()->detach($old_bins);
        $new_bins_id= collect($new_bins_id)->pluck("id");
        /** @var Collection $bins */
        $bins = Bin::whereIn('id',$new_bins_id)->get();

        foreach ($new_bins_id as $new_bin_id) {

            $new_bin = $bins->find($new_bin_id);
            $this->addBin($new_bin);
        }

        // dd( $this->getBins()->pluck('gps'));
        /* @toDO send to service and generate route */


    }

    protected function AvgBinCenter(): Attribute
    {
        $center = [
            'long' => Bin::avg('lng'),
            'lat' => Bin::avg('lat')];

        if (
            $this->getBins()->count()
        ) {
            $center = ['lat' => $this->getBins()->avg('lat'),
                'long' => $this->getBins()->avg('lng'),
            ];
        }


        return new Attribute(
            get: fn() => $center,
        );
    }



}
