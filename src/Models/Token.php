<?php

namespace Rupadana\ApiService\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\PersonalAccessToken;

class Token extends PersonalAccessToken
{
    use HasFactory;
    protected $table = 'personal_access_tokens';
}
