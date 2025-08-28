<?php

namespace App\Http\Controllers;

use App\Models\InvoiceLocation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceLocationController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $locations = InvoiceLocation::join('invoice_companies', 'invoice_locations.invoice_company_id', '=', 'invoice_companies.id')
            ->join('cities', 'invoice_locations.city_id', '=', 'cities.id')
            ->select('invoice_locations.name', 'invoice_locations.code', 'invoice_companies.name as company_name', 'cities.name as city_name')
            ->get();
        return Inertia::render('Ubicaciones/Index', ['locations' => $locations]);
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
    public function show(InvoiceLocation $invoiceLocation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InvoiceLocation $invoiceLocation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InvoiceLocation $invoiceLocation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvoiceLocation $invoiceLocation)
    {
        //
    }
}
