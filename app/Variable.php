<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Variable extends Model
{
    protected $fillable = ['slug','section','standard_name','terminology','page'];

}
