<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->count(100)->create()->each(function ($user) {
            Company::factory([
                'user_id' => $user->id,
            ])->count(10)->create();
        });
    }
}
