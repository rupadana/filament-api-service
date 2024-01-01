<?php

namespace Rupadana\ApiService\Tests\Fixtures\Database\Seeders;

use Illuminate\Database\Seeder;
use Rupadana\ApiService\Tests\Fixtures\Models\Product;

class ProductsSeeder extends Seeder
{
    public function run()
    {
        Product::create([
            'name' => 'Jeans',
            'slug' => 'jeans',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'price' => 1000,
        ]);

        Product::create([
            'name' => 'T-Shirt',
            'slug' => 't-shirt',
            'description' => 'Donec sed odio dui. Nullam quis risus eget urna mollis ornare vel eu leo.',
            'price' => 500,
        ]);

        Product::create([
            'name' => 'Shoes',
            'slug' => 'shoes',
            'description' => 'Maecenas faucibus mollis interdum. Nullam id dolor id nibh ultricies vehicula ut id elit.',
            'price' => 2000,
        ]);

        Product::create([
            'name' => 'Hat',
            'slug' => 'hat',
            'description' => 'Maecenas sed diam eget risus varius blandit sit amet non magna.',
            'price' => 100,
        ]);

        Product::create([
            'name' => 'Socks',
            'slug' => 'socks',
            'description' => 'Cras mattis consectetur purus sit amet fermentum.',
            'price' => 50,
        ]);

        Product::create([
            'name' => 'Gloves',
            'slug' => 'gloves',
            'description' => 'Maecenas sed diam eget risus varius blandit sit amet non magna.',
            'price' => 150,
        ]);

        Product::create([
            'name' => 'Jacket',
            'slug' => 'jacket',
            'description' => 'Maecenas sed diam eget risus varius blandit sit amet non magna.',
            'price' => 200,
        ]);
    }
}
