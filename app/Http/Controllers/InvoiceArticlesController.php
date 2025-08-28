<?php

namespace App\Http\Controllers;

use App\Models\InvoiceArticles;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceArticlesController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $articles = InvoiceArticles::all();
        return Inertia::render('Articulos/Index', ['articles' => $articles]);
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
            'code' => 'required|string|max:100|unique:invoice_articles,code',
        ]);

        $article = InvoiceArticles::create([
            'name' => $request->name,
            'code' => $request->code,
        ]);

        return redirect()->route('/articulos')->with('success', 'Artículo creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(InvoiceArticles $invoiceArticles)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InvoiceArticles $invoiceArticles)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InvoiceArticles $invoiceArticles)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:invoice_articles,code,' . $invoiceArticles->id,
        ]);

        $invoiceArticles->update([
            'name' => $request->name,
            'code' => $request->code,
        ]);

        return redirect()->route('/articulos')->with('success', 'Artículo actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvoiceArticles $invoiceArticles)
    {
        $invoiceArticles->delete();
        return redirect()->route('/articulos')->with('success', 'Artículo eliminado exitosamente.');
    }
}
