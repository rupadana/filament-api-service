<?php

namespace Rupadana\ApiService\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public static array $allowedFields = [
        'name',
        // 'slug' is not allowed
        'description',
        'price',
        'created_at',
    ];
    public static array $allowedSorts = [
        'name',
        'price',
        'created_at',
    ];
    public static array $allowedFilters = [
        'name',
        'price',
        'created_at',
    ];
    protected $guarded = [];
    protected $hidden = [
        'slug',
    ];
}
