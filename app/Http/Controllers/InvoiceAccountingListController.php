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
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:invoice_accounting_lists,code',
        ]);

        InvoiceAccountingList::create([
            'name' => $request->name,
            'code' => $request->code,
        ]);

        return redirect()->route('/listas-contabilidad')->with('success', 'Lista de contabilidad creada exitosamente.');
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
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:invoice_accounting_lists,code,' . $invoiceAccountingList->id,
        ]);

        $invoiceAccountingList->update($validatedData);
        return redirect()->route('/listas-contabilidad')->with('success', 'Lista de contabilidad actualizada exitosamente.');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvoiceAccountingList $invoiceAccountingList)
    {
        $invoiceAccountingList->delete();
        return redirect()->route('/listas-contabilidad')->with('success', 'Lista de contabilidad eliminada exitosamente.');
    }
}
