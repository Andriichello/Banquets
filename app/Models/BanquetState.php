<?php

namespace App\Models;

use App\Constrainters\Implementations\DescriptionConstrainter;
use App\Constrainters\Implementations\NameConstrainter;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BanquetState extends BaseDeletableModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'banquet_states';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get banquets associated with the model.
     */
    public function banquets()
    {
        return $this->hasMany(Banquet::class, 'state_id', 'id');
    }
}
