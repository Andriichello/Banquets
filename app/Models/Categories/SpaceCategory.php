<?php

namespace App\Models\Categories;

use App\Constrainters\Implementations\DescriptionConstrainter;
use App\Constrainters\Implementations\NameConstrainter;
use App\Models\BaseDeletableModel;
use App\Models\BaseModel;
use App\Models\Space;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SpaceCategory extends BaseDeletableModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'space_categories';

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
     * Get array of model's validation rules.
     *
     * @var bool $forInsert
     * @return array
     */
    public static function getValidationRules($forInsert = false) {
        return Category::getValidationRules($forInsert, 'space');
    }

    /**
     * Get spaces associated with the model.
     */
    public function spaces()
    {
        return $this->hasMany(Space::class, 'category_id', 'id');
    }
}
