<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CentralClient extends Model
{
    protected $table = 'clients';

    protected $connection = 'central';

    public $timestamps = false; // set true if timestamps exist
}
