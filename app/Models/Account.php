<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'client_id',
        'account_number',
        'account_type',
        'account_name',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
