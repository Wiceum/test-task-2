<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MealList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'MealList {ingredientList}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Allows you to get a list of possible dishes from the specified types of ingredients';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $ingredientList = $this->argument('ingredientList');
        $this->line($ingredientList);
        return Command::SUCCESS;
    }
}
