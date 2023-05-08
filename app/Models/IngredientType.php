<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IngredientType extends Model
{
    protected $table = 'ingredient_type';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class, 'type_id');
    }
}
