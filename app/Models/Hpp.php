<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hpp extends Model
{
    use HasFactory;

    protected $table = 'hpp';

    protected $guarded = ['id'];
}
