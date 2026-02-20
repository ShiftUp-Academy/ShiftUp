<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterSubscription extends Model
{
    protected $table = 'NewsletterSubscriptions';
    protected $primaryKey = 'IdSubscription';
    public $timestamps = false; // We use DateSouscription

    protected $fillable = [
        'Email',
        'DateSouscription'
    ];
}
