<?php

namespace App\Http\Controllers;

use App\Models\InvoiceAccountingList;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceAccountingListController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $accountingLists = InvoiceAccountingList::all();
        return Inertia::render('ListasContabilidad/Index', [
            'accountingLists' => $accountingLists
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(InvoiceAccountingList $invoiceAccountingList)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InvoiceAccountingList $invoiceAccountingList)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InvoiceAccountingList $invoiceAccountingList)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvoiceAccountingList $invoiceAccountingList)
    {
        //
    }
}
