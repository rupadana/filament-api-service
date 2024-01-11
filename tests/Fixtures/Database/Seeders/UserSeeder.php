<?php

namespace Rupadana\ApiService\Tests\Fixtures\Database\Seeders;

use Illuminate\Database\Seeder;
use Rupadana\ApiService\Tests\Fixtures\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Rupadana',
            'email' => 'rupadanawayan@gmail.com',
            'password' => bcrypt('12345678'),
        ]);
    }
}
