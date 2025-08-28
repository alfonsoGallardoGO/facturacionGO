<?php

namespace App\Http\Controllers;

use App\Models\InvoiceCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceCategoryController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoiceCategories = InvoiceCategory::all();
        return Inertia('CategoriaFacturas/Index', [
            'categories' => $invoiceCategories,
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
        ]);

        InvoiceCategory::create([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->name,
        ]);

        return redirect()->route('/categoria-facturas')->with('success', 'Categoría de factura creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(InvoiceCategory $invoiceCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InvoiceCategory $invoiceCategory)
    {
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InvoiceCategory $invoiceCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
        ]);

        $invoiceCategory->update([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->name,
        ]);

        return redirect()->route('/categoria-facturas')->with('success', 'Categoría de factura actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvoiceCategory $invoiceCategory)
    {
        $invoiceCategory->delete();
        return redirect()->route('/categoria-facturas')->with('success', 'Categoría de factura eliminada exitosamente.');
    }

    public function deleteMultiple(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!empty($ids)) {
            InvoiceCategory::whereIn('id', $ids)->delete();
            return redirect()->route('/categoria-facturas')->with('success', 'Categorías de factura eliminadas exitosamente.');
        }
        return redirect()->route('/categoria-facturas')->with('error', 'No se seleccionaron categorías para eliminar.');
    }
}
