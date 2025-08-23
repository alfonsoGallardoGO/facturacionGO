<?php

namespace App\Http\Controllers;

use App\Models\InvoiceAccountingList;
use App\Models\InvoiceArticles;
use App\Models\InvoiceCategory;
use App\Models\InvoiceClass;
use App\Models\InvoiceDepartment;
use App\Models\InvoiceExclusionCategory;
use App\Models\InvoiceLocation;
use App\Models\InvoiceSat;
use App\Models\InvoiceTerm;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Services\NetSuiteRestService;

class InvoiceSatController
{

    private NetSuiteRestService $netSuiteRestService;
    public function __construct(NetSuiteRestService $netSuiteRestService)
    {
        $this->netSuiteRestService = $netSuiteRestService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $terms = InvoiceTerm::all();
        $locations = InvoiceLocation::all();
        $exclusions = InvoiceExclusionCategory::all();
        $departments = InvoiceDepartment::all();
        $classes = InvoiceClass::all();
        $categories = InvoiceCategory::all();
        $articles = InvoiceArticles::all();
        $accountingLists = InvoiceAccountingList::all();
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
    public function show(InvoiceSat $invoiceSat)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InvoiceSat $invoiceSat)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InvoiceSat $invoiceSat)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvoiceSat $invoiceSat)
    {
        //
    }

    public function sendNetsuite(Request $request, InvoiceSat $invoiceSat)
    {
        $meta_x = $invoiceSat->meta_xml;

        $costs = map_xml_costs(data_get($meta_x, 'Conceptos.Concepto'), $invoiceSat);

        $folio = data_get($meta_x, 'Folio');

        if (is_null($folio)) {
            $folio = Str::limit(data_get($meta_x, 'Complemento.TimbreFiscalDigital.UUID'), 5, '');
        }

        $xmlPath = $invoiceSat->xml_path; // p.ej: "sat_xml/mi-archivo.xml"
        $pdfPath = $invoiceSat->pdf_path; // p.ej: "sat_xml/mi-archivo.pdf"

        $xmlBase64 = null;
        $pdfBase64 = null;

        if (Storage::disk('spaces')->exists('sat_xml/'.$xmlPath)) {
            $xmlContent = Storage::disk('spaces')->get('sat_xml/'.$xmlPath);
            $xmlBase64 = base64_encode($xmlContent);
        }

        if (Storage::disk('spaces')->exists('sat_xml/'.$pdfPath)) {
            $pdfContent = Storage::disk('spaces')->get('sat_xml/'.$pdfPath);
            $pdfBase64 = base64_encode($pdfContent);
        }

        $data = [
            'rfc'           => $invoiceSat->emisor_rfc,
            'nfactura'      => $folio,
            'regimenfiscal' => data_get($meta_x, 'Emisor.RegimenFiscal'),
            'moneda'        => data_get($meta_x, 'Moneda'),
            'termino'       => $invoiceSat?->invoice_term_id ?? '',
            'departamento'  => $invoiceSat?->invoice_department_id ?? '',
            'clase'         => $invoiceSat?->invoice_class_id ?? '',
            'operacion'     => $invoiceSat?->invoice_operation_type_id ?? '',
            'tipocambio'    => $invoiceSat?->exchange_rate,
            //            'fecha'         => Carbon::parse(data_get($meta_xml, 'Complemento.TimbreFiscalDigital.FechaTimbrado'))->format('d/m/Y'),
            'fecha'      => Carbon::parse($invoiceSat->trandate)->format('d/m/Y'),
            'ubicacion'  => $invoiceSat?->invoice_location_id ?? '',
            'idnetsuite' => $invoiceSat->order_id ?? '',
            'uuid'       => $invoiceSat->uuid,
            'gastos'     => $costs,
            'articulos'  => [],
            'nota'       => $invoiceSat->notes                 ??= '',
            'generico'   => $invoiceSat->invoice_provider_type ??= '',
            'xml'        => $xmlBase64,
            'pdf'        => $pdfBase64,
        ];

        //return $data;

        if ($invoiceSat->invoice_article_id || $invoiceSat->invoice_accounting_id) {
            $articles          = map_xml_articles(data_get($meta_x, 'Conceptos.Concepto'), $invoiceSat);
            $data['articulos'] = $articles;
            $data['gastos']    = [];
        }

        $restletPath = '/restlet.nl?script=1908&deploy=1';

        //return $data;
        
        try {
            $response = $this->netSuiteRestService->request($restletPath, 'POST', $data);
            return response()->json(['ok' => true, 'response' => $response]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }

        
    }

    
}
