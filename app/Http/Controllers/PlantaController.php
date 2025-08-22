<?php

namespace App\Http\Controllers;

use App\Models\Planta;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PlantaController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $selectColumns = [
            'bo.id AS planta_id',
            'bo.code AS codigo',
            'bo.name AS planta',
            'co.name AS empresa',
            // Para JSON_UNQUOTE(json_extract(bo.meta, '$.tax_id')) se puede usar DB::raw
            DB::raw("JSON_UNQUOTE(JSON_EXTRACT(bo.meta, '$.tax_id')) AS rfc"),
            'bo.internal_code AS clave_netsuite',
            'bo.external_location_id AS clave_ubicacion_netsuite',
            'bok.certificate_path AS certificado_fiscal',
            // Para DATE(bok.expires_at) se puede usar DB::raw
            DB::raw("DATE(bok.expires_at) AS expiracion_clave"),
            'bo.created_at',
            'bo.updated_at',
            'bo.description',
            'bo.active',
        ];

        // Construye la consulta usando el Query Builder de Laravel
        $plantas = DB::table('branch_offices as bo')
            ->select($selectColumns)
            ->join('companies as co', 'bo.company_id', '=', 'co.id')
            ->join('branch_office_fiscal_keys as bok', 'bo.id', '=', 'bok.branch_office_id')
            ->orderBy('bo.id', 'DESC')
            ->get(); // Obtiene los resultados de la consulta

        // Retorna la vista de Inertia, pasando los datos a un componente Vue
        return Inertia::render('Catalogo/Plantas/Index', [
            'plantas' => $plantas,
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
    public function show(Planta $planta)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Planta $planta)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Planta $planta)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Planta $planta)
    {
        //
    }

    public function byUser()
    {
        $userId = Auth::id();

        $offices = User::findOrFail($userId)
            ->branchOffices()
            ->select('branch_offices.id', 'branch_offices.code')
            ->get();

        return response()->json($offices);
    }
}
