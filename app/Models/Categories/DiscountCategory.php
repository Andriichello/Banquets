<?php

namespace App\Models\Categories;

use App\Constrainters\Implementations\DescriptionConstrainter;
use App\Constrainters\Implementations\NameConstrainter;
use App\Models\BaseDeletableModel;
use App\Models\BaseModel;
use App\Models\Discount;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DiscountCategory extends BaseDeletableModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'discount_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
    ];

    protected $cascadeDeletes = ['discounts'];

    /**
     * The discounts associated with the model.
     */
    public function discounts()
    {
        return $this->hasMany(Discount::class, 'category_id', 'id');
    }
}
