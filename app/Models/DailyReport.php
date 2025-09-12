<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    protected $fillable = ['user_id','date','mood','effort','content'];
    protected $casts = ['date' => 'date'];
}