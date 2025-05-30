<?php

namespace App\Models;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;


class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'product',
        'price',
        'category_id',
        'timestamp',
        'user_id', // Added user_id to fillable for mass assignment
        'local_id', // Added local_id to fillable for mass assignment
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
