<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Planta extends Model
{
    protected $table = 'branch_offices';

    protected $fillable = [
        'internal_code',
        'code',
        'name',
        'description',
    ];

    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'branch_office_user',
            'branch_office_id',
            'user_id'
        );
    }
}
