<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentOdd extends Model
{

    protected $casts = [
        'content' => 'object'
    ];

}
