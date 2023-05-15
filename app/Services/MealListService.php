<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\IngredientType;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
            $json = json_encode($this->result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            return $json;
        } catch (BusinessException $e) {
            return $e->getUserMessage();
        }
    }

    private function validateInput()
    {
        if (!preg_match('/^[a-z]*$/', $this->input)) {
            throw new BusinessException('Wrong input! Only lowercase alphabetical letters allowed!');
        }
        return $this;
    }

    private function getIngredients()
    {
        $this->ingredientTypes = IngredientType::all();
        $this->ingredients = DB::table('ingredient')
            ->join('ingredient_type', 'ingredient.type_id', '=', 'ingredient_type.id')
            ->select(['ingredient.id',
                'ingredient.title',
                'ingredient.price',
                'ingredient_type.code',
                DB::raw('ingredient_type.title as type_title')])
            ->get();
        return $this;
    }

    private function checkIngredientTypes()
    {
        $inputTypes = collect(str_split($this->input))->uniqueStrict();
        $filtered = $inputTypes->diff($this->ingredientTypes->pluck('code'));
        if ($filtered->isNotEmpty()) {
            throw new BusinessException('No such ingredient type!');
        }
        return $this;
    }

    private function calculate()
    {
        $inputTypes = collect(str_split($this->input));
        if ($inputTypes->count() === 1) {
            $this->result = $this->ingredients->where('code','=', $inputTypes->first());
            return;
        }

        //создаем набор из разных комбинаций блюд, включая с одинаковыми ингредиентами
        $combinations = collect(
            $inputTypes->reduce(function (Collection $carry, $item) {
                $ingredients = $this->ingredients->whereStrict('code', $item);
                $carry = $carry->crossJoin($ingredients);
                if ($carry->isEmpty()) {
                    $carry = $ingredients;
                }
                return $carry;
            }, collect())
        )
            ->map(function ($item) {
                return collect($item)->flatten();
            });

        //добавляем в каждое блюдо поле с айдишниками входящих в него ингредиентов
        $combinationsArray = $combinations->toArray();
        foreach ($combinationsArray as $key => $combo) {
            $ids = [];
            foreach ($combo as $ingredient) {
                $ids[] = $ingredient->id;
            }
            $ids = Arr::sort($ids);;
            $combinationsArray[$key]['combo_ids'] = $ids;
        }

        //отбрасываем блюда где один ингредиент встречается несколько раз
        $filtered = collect($combinationsArray)->map(function ($combo) {
            $arr = collect($combo['combo_ids'])->uniqueStrict()->toArray();
            $combo['combo_ids'] = $arr;
            return $combo;
        })
            ->filter(function ($combo) use ($inputTypes) {
                //объявление в начале метода
                $res = count($combo['combo_ids']) === $inputTypes->count();
                return $res;
            })
            ->map(function ($combo) {
                $combo['combo_ids'] = implode('-', $combo['combo_ids']);
                return $combo;
            })
            ->uniqueStrict('combo_ids')  //отбрасываем блюда с одинаковым набором ингредиентов
            ->values();

        $calculated = $filtered->map(function ($combo) {
            $combo['price'] = collect($combo)->sum('price');
            return $combo;
        });

        $prettified = $calculated->map(function ($combo) {
            unset($combo['combo_ids']);
            $price = $combo['price'];
            unset($combo['price']);
            return ['products' => $combo, 'price' => $price];
        });

        $this->result = $prettified;
    }
}

