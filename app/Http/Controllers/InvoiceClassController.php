<?php

namespace App\Http\Controllers;

use App\Models\InvoiceClass;
use Illuminate\Http\Request;

class InvoiceClassController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoiceClasses = InvoiceClass::all();
        return Inertia('ClasesFacturacion/Index', [
            'classes' => $invoiceClasses,
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
            'code' => 'nullable|string|max:50',
            'active' => 'required|boolean',
        ]);

        InvoiceClass::create([
            'name' => $request->name,
            'code' => $request->code,
            'description' => null,
            'active' => $request->active,
        ]);

        return redirect()->route('/clases-facturacion')->with('success', 'Clase de facturación creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(InvoiceClass $invoiceClass)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InvoiceClass $invoiceClass)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InvoiceClass $invoiceClass)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'active' => 'required|boolean',
        ]);

        $invoiceClass->update([
            'name' => $request->name,
            'code' => $request->code,
            'description' => null,
            'active' => $request->active,
        ]);

        return redirect()->route('/clases-facturacion')->with('success', 'Clase de facturación actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvoiceClass $invoiceClass)
    {
        $invoiceClass->delete();
        return redirect()->route('/clases-facturacion')->with('success', 'Clase de facturación eliminada exitosamente.');
    }

    public function deleteMultiple(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!empty($ids)) {
            InvoiceClass::whereIn('id', $ids)->delete();
            return redirect()->route('/clases-facturacion')->with('success', 'Clases de facturación eliminadas exitosamente.');
        }
        return redirect()->route('/clases-facturacion')->with('error', 'No se seleccionaron clases de facturación para eliminar.');
    }
}
