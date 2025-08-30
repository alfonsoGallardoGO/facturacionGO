<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceLocation extends Model
{
    use SoftDeletes;

    protected $table = 'invoice_locations';

    protected $fillable = [
        'name',
        'code',
        'invoice_company_id',
        'city_id',
    ];
}
