<?php

use Illuminate\Routing\Route as RoutingRoute;
use Rupadana\ApiService\Tests\Fixtures\Database\Seeders\ProductsSeeder;
use Rupadana\ApiService\Tests\Fixtures\Models\Product;

it('can make routes for a product resource', function () {
    $routes = collect(app('router')->getRoutes())->map(function (RoutingRoute $route) {
        return implode('|', $route->methods()) . ' ' . $route->uri();
    });

    // The route name is customized to `our-products` in the `ProductApiService` class
    expect($routes)->toContain('POST api/our-products');
    expect($routes)->toContain('PUT api/our-products/{id}');
    expect($routes)->toContain('DELETE api/our-products/{id}');
    expect($routes)->toContain('GET|HEAD api/our-products');
    expect($routes)->toContain('GET|HEAD api/our-products/{id}');
});

it('can return a list of products with allowed attributes', function () {
    $this->seed(ProductsSeeder::class);

    $response = $this->get('/api/our-products')
        ->assertStatus(200);

    $products = Product::all()->map(function ($product) {
        return [
            'name' => $product['name'],
            'description' => $product['description'],
            'price' => $product['price'],
        ];
    })->toArray();

    foreach ($products as $product) {
        $response->assertJsonFragment($product);
    }

    // Check that the slug (hidden) is not returned
    $response->assertJsonMissing([
        'slug' => 't-shirt',
    ]);
});

it('can return a list of products with selected fields', function () {
    $this->seed(ProductsSeeder::class);

    $response = $this->get('/api/our-products?fields[products]=name,price')
        ->assertStatus(200);

    $response->assertJsonFragment([
        'name' => 'T-Shirt',
        'price' => 500,
    ]);
});

it('throws when selecting a field that is not allowed', function () {
    $this->seed(ProductsSeeder::class);

    $this->get('/api/our-products?fields[products]=name,slug,price')
        ->assertStatus(400)
        ->assertJsonFragment([
            'message' => 'Requested field(s) `products.slug` are not allowed.',
        ]);
})->throws(\Spatie\QueryBuilder\Exceptions\InvalidFieldQuery::class);

it('can return a list of products with selected sorts', function () {
    $this->seed(ProductsSeeder::class);

    $response = $this->get('/api/our-products?sort=-price')
        ->assertStatus(200);

    $data = Product::all()
        ->sortByDesc('price')
        ->values()
        ->map(function ($product) {
            return [
                'name' => $product['name'],
                'price' => $product['price'],
            ];
        })
        ->toArray();

    foreach ($data as $product) {
        $response->assertJsonFragment($product);
    }
});

it('throws when sorting by a field that is not allowed', function () {
    $this->seed(ProductsSeeder::class);

    $this->get('/api/our-products?sort=-slug')
        ->assertStatus(400)
        ->assertJsonFragment([
            'message' => 'Requested sort(s) `products.slug` are not allowed.',
        ]);
})->throws(\Spatie\QueryBuilder\Exceptions\InvalidSortQuery::class);

it('can return a list of products with selected filters', function () {
    $this->seed(ProductsSeeder::class);

    $response = $this->get('/api/our-products?filter[name]=T-Shirt&filter[price]=500')
        ->assertStatus(200);

    $response->assertJsonFragment([
        'name' => 'T-Shirt',
        'price' => 500,
    ]);
});

it('throws when filtering by a field that is not allowed', function () {
    $this->seed(ProductsSeeder::class);

    $this->get('/api/our-products?filter[name]=T-Shirt&filter[slug]=t-shirt')
        ->assertStatus(400)
        ->assertJsonFragment([
            'message' => 'Requested filter(s) `products.slug` are not allowed.',
        ]);
})->throws(\Spatie\QueryBuilder\Exceptions\InvalidFilterQuery::class);

it('can return a list of products with a custom transformer', function () {
    $this->seed(ProductsSeeder::class);

    $response = $this->get('/api/our-products')
        ->assertStatus(200);

    $product = Product::first();

    $response->assertJsonFragment([
        'hash' => md5($product['name']),
    ]);
});
