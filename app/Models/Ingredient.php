<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ingredient extends Model
{
    protected $table = 'ingredient';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function type (): BelongsTo
    {
        return $this->belongsTo(IngredientType::class, 'type_id', 'id');
    }
}
