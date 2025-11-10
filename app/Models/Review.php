<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'author_name',
        'review_text',
        'rating',
        'author_avatar',
        'week_label'
    ];
}
