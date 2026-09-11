<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipment';

    protected $fillable = [
        'name',
        'description',
        'category',
        'price',
        'stock',
        'status', // 'in_stock', 'low_stock', 'out_of_stock', 'restricted'
        'image',
    ];

    protected $casts = [
        'price' => 'float',
        'stock' => 'integer',
    ];
}
