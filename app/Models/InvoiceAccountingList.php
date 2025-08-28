<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceAccountingList extends Model
{
    protected $table = 'invoice_accounting_lists';

    protected $fillable = [
        'name',
        'code',
    ];
}
