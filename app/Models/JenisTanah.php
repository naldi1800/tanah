<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;


#[Fillable(["jenis", "ciri_ciri"])]
class JenisTanah extends Model
{
    protected $table =  "jenis_tanahs";
}
