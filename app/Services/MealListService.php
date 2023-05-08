<?php

namespace App\Services;

final class MealListService
{
    protected string $input;
    protected $result = '';
    protected array $inputTypes = [];
    protected $ingredients;

    public function __construct(string $input = '')
    {
        $this->input = $input;
    }

    public function compose()
    {
        $this->checkIngredientsType();
        return $this->result;
    }

    public function validateInput(string $input)
    {

        if (!preg_match('/^[a-z]*$/', $input)) {
            $this->result = 'Error! Wrong input.';
        }
        $this->input = $input;
        return $this;
    }

    private function checkIngredientsType()
    {
        return true;
    }
}
