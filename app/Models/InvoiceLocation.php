<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceLocation extends Model
{
    protected $table = 'invoice_locations';

    protected $fillable = [
        'name',
        'code',
        'invoice_company_id',
        'city_id',
    ];
}
