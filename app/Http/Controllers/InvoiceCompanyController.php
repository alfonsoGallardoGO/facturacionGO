<?php

namespace App\Http\Controllers;

use App\Models\InvoiceCompany;
use Illuminate\Http\Request;

class InvoiceCompanyController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoiceCompany = InvoiceCompany::all();
        return inertia('Empresas/Index', ['empresas' => $invoiceCompany]);
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
            'code' => 'required|string|max:50|unique:invoice_companies,code',
            'account' => 'nullable|string|max:100',
            'currency' => 'nullable|string|max:10',
            'foreign_account' => 'nullable|string|max:100',
            'foreign_currency' => 'nullable|string|max:10',
        ]);

        InvoiceCompany::create($request->all());

        return redirect()->back()->with('success', 'Empresa creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(InvoiceCompany $invoiceCompany)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InvoiceCompany $invoiceCompany)
    {
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InvoiceCompany $invoiceCompany)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:invoice_companies,code,' . $invoiceCompany->id,
            'account' => 'nullable|string|max:100',
            'currency' => 'nullable|string|max:10',
            'foreign_account' => 'nullable|string|max:100',
            'foreign_currency' => 'nullable|string|max:10',
        ]);

        $invoiceCompany->update($request->all());

        return redirect()->back()->with('success', 'Empresa actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvoiceCompany $invoiceCompany)
    {
        $invoiceCompany->delete();
        return redirect()->back()->with('success', 'Empresa eliminada exitosamente.');
    }
}
