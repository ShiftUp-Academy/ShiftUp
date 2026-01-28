<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LienGoogle extends Model
{
    protected $table = 'LienGoogle';
    protected $primaryKey = 'IdLien';
    protected $fillable = ['LienGoogleMeet'];
}
