<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceCompany extends Model
{
    use SoftDeletes;

    protected $table = 'invoice_companies';

    protected $fillable = [
        'name',
        'code',
        'account',
        'currency',
        'foreign_account',
        'foreign_currency',
    ];
}
