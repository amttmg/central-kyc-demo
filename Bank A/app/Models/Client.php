<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'citizenship_number',
        'citizenship_issued_date',
        'citizenship_issued_place',
        'father_name',
        'mother_name',
        'spouse_name',
        'permanent_address',
        'temporary_address',
        'occupation',
        'income_source',
        'income_range',
        'marital_status',
        'national_id',
        'dob',
        'img'
    ];

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }
}
