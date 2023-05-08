<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Ingredient;
use App\Models\IngredientType;
use Illuminate\Support\Collection;

final class MealListService
{
    protected string $input;
    protected $result = '';
    protected Collection $ingredientTypes;
    protected Collection $ingredients;

    public function __construct(string $input = '')
    {
        $this->input = $input;
    }

    public function compose(string $input)
    {
        $this->input = $input;
        try {
            $this->validateInput()
                ->getIngredients()
                ->checkIngredientTypes()
                ->calculate();
            return json_encode($this->result);
        } catch (BusinessException $e) {
            return $e->getUserMessage();
        }
    }

    private function validateInput()
    {
        if (!preg_match('/^[a-z]*$/', $this->input)) {
            throw new BusinessException('Error! Wrong input.');
        }
        return $this;
    }

    private function getIngredients()
    {
        $this->ingredientTypes = IngredientType::all();
        $this->ingredients = Ingredient::all();
        return $this;
    }

    private function checkIngredientTypes()
    {
        $inputTypes = collect(str_split($this->input));
        $intersected = $inputTypes->intersect($this->ingredientTypes);
        if ($intersected->isNotEmpty()) {
            throw new BusinessException('No such ingredient type!');
        }
        return $this;
    }

    private function calculate()
    {

    }
}
