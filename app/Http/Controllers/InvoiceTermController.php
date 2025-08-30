<?php

namespace App\Http\Controllers;

use App\Models\InvoiceTerm;
use Illuminate\Http\Request;

class InvoiceTermController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $terms = InvoiceTerm::all();
        return inertia('Terminos/Index', ['terms' => $terms]);
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
            'code' => 'required|string|max:50|unique:invoice_terms,code',
        ]);

        InvoiceTerm::create([
            'name' => $request->name,
            'code' => $request->code,
            'description' => null,
        ]);

        return redirect()->route('/terminos-pago')->with('success', 'Termino de pago creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(InvoiceTerm $invoiceTerm)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InvoiceTerm $invoiceTerm)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InvoiceTerm $invoiceTerm)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:invoice_terms,code,'.$invoiceTerm->id,
        ]);

        $invoiceTerm->update([
            'name' => $request->name,
            'code' => $request->code,
            'description' => null,
        ]);

        return redirect()->route('/terminos-pago')->with('success', 'Termino de pago actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvoiceTerm $invoiceTerm)
    {
        $invoiceTerm->delete();

        return redirect()->route('/terminos-pago')->with('success', 'Termino de pago eliminado exitosamente.');
    }
}
