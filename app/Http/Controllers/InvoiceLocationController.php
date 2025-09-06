<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\InvoiceCompany;
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
            ->select('invoice_locations.id','invoice_locations.name', 'invoice_locations.code', 'invoice_companies.name as company_name', 'cities.name as city_name')
            ->get();
        $cities = City::all();
        $companies = InvoiceCompany::all();
        return Inertia::render('Ubicaciones/Index', [
            'ubicaciones' => $locations,
            'cities' => $cities,
            'companies' => $companies,
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
            'code' => 'required|string|max:255|unique:invoice_locations,code',
            'invoice_company_id' => 'required|exists:invoice_companies,id',
            'city_id' => 'required|exists:cities,id',
        ]);

        InvoiceLocation::create($request->all());

        return redirect()->route('/ubicaciones')->with('success', 'Ubicación creada exitosamente.');
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
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:invoice_locations,code,' . $invoiceLocation->id,
            'invoice_company_id' => 'required|exists:invoice_companies,id',
            'city_id' => 'required|exists:cities,id',
        ]);

        $invoiceLocation->update($request->all());

        return redirect()->route('/ubicaciones')->with('success', 'Ubicación actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvoiceLocation $invoiceLocation)
    {
        $invoiceLocation->delete();

        return redirect()->route('/ubicaciones')->with('success', 'Ubicación eliminada exitosamente.');
    }
}
