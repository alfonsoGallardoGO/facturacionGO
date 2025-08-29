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
use App\Models\InvoiceOperationTypes;
use App\Models\Planta;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Services\NetSuiteRestService;
use Inertia\Inertia;

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
        $operationTypes = InvoiceOperationTypes::all();
        $plantas = Planta::all('code','id');
        $invoices = InvoiceSat::select(
            'invoice_sats.id',
            'invoice_sats.emisor_name',
            'invoice_sats.emisor_rfc',
            'invoice_sats.trandate',
            DB::raw('DATE(trandate_cer) as trandate_cer'),
            'invoice_sats.total',
            'invoice_sats.xml_path',
            'invoice_sats.pdf_path',
            'invoice_sats.send_status',
            'invoice_sats.uuid',
            'invoice_sats.rfc_pac',
            'invoice_sats.trandate_cancel',
            'invoice_sats.order_id',
            'invoice_sats.notes',
            'invoice_sats.invoice_provider_type',
            'invoice_sats.efecto_comprobante',
            'invoice_sats.status',
            'invoice_companies.name as company_name',
            'invoice_categories.name as categoria',
            'invoice_locations.name as ubicacion',
            'invoice_departments.name as departamento',
            'invoice_classes.name as clase',
            'invoice_terms.name as termino',
            'invoice_accounting_lists.name as importacion',
            'invoice_articles.name as articulo',
            'invoice_exclusion_categories.name as exclusion',
            'invoice_operation_types.name as tipo_operacion'
        )
        ->leftJoin('invoice_companies', 'invoice_companies.id', '=', 'invoice_sats.invoice_company_id')
        ->leftJoin('invoice_categories', 'invoice_categories.id', '=', 'invoice_sats.invoice_category_id')
        ->leftJoin('invoice_locations', 'invoice_locations.id', '=', 'invoice_sats.invoice_location_id')
        ->leftJoin('invoice_departments', 'invoice_departments.id', '=', 'invoice_sats.invoice_department_id')
        ->leftJoin('invoice_classes', 'invoice_classes.id', '=', 'invoice_sats.invoice_class_id')
        ->leftJoin('invoice_terms', 'invoice_terms.id', '=', 'invoice_sats.invoice_term_id')
        ->leftJoin('invoice_accounting_lists', 'invoice_accounting_lists.id', '=', 'invoice_sats.invoice_accounting_id')
        ->leftJoin('invoice_articles', 'invoice_articles.id', '=', 'invoice_sats.invoice_article_id')
        ->leftJoin('invoice_exclusion_categories', 'invoice_exclusion_categories.id', '=', 'invoice_sats.invoice_exclusion_category_id')
        ->leftJoin('invoice_operation_types', 'invoice_operation_types.id', '=', 'invoice_sats.invoice_operation_type_id')
        ->where('invoice_sats.branch_office_id', 13)
        ->get()
        ->map(function ($invoice) {
            $invoice->ready_to_netsuite =
                $invoice->efecto_comprobante === 'I' &&
                !$invoice->external_id &&
                !$invoice->service_processing_at &&
                !$invoice->service_ends_at &&
                $invoice->xml_path &&
                $invoice->pdf_path &&
                !$invoice->trandate_cancel;

            return $invoice;
        });

        return Inertia::render('Xml/Table', [
            'invoices' => $invoices,
            'terms' => $terms,
            'locations' => $locations,
            'exclusions' => $exclusions,
            'departments' => $departments,
            'classes' => $classes,
            'categories' => $categories,
            'articles' => $articles,
            'accountingLists' => $accountingLists,
            'operationTypes' => $operationTypes,
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
        // $meta_x = $invoiceSat->meta_xml;

        // $costs = map_xml_costs(data_get($meta_x, 'Conceptos.Concepto'), $invoiceSat);

        // $folio = data_get($meta_x, 'Folio');

        // if (is_null($folio)) {
        //     $folio = Str::limit(data_get($meta_x, 'Complemento.TimbreFiscalDigital.UUID'), 5, '');
        // }

        // $xmlPath = $invoiceSat->xml_path; // p.ej: "sat_xml/mi-archivo.xml"
        // $pdfPath = $invoiceSat->pdf_path; // p.ej: "sat_xml/mi-archivo.pdf"

        // $xmlBase64 = null;
        // $pdfBase64 = null;

        // if (Storage::disk('spaces')->exists('sat_xml/'.$xmlPath)) {
        //     $xmlContent = Storage::disk('spaces')->get('sat_xml/'.$xmlPath);
        //     $xmlBase64 = base64_encode($xmlContent);
        // }

        // if (Storage::disk('spaces')->exists('sat_xml/'.$pdfPath)) {
        //     $pdfContent = Storage::disk('spaces')->get('sat_xml/'.$pdfPath);
        //     $pdfBase64 = base64_encode($pdfContent);
        // }

        // $data = [
        //     'rfc'           => $invoiceSat->emisor_rfc,
        //     'nfactura'      => $folio,
        //     'regimenfiscal' => data_get($meta_x, 'Emisor.RegimenFiscal'),
        //     'moneda'        => data_get($meta_x, 'Moneda'),
        //     'termino'       => $invoiceSat?->invoice_term_id ?? '',
        //     'departamento'  => $invoiceSat?->invoice_department_id ?? '',
        //     'clase'         => $invoiceSat?->invoice_class_id ?? '',
        //     'operacion'     => $invoiceSat?->invoice_operation_type_id ?? '',
        //     'tipocambio'    => $invoiceSat?->exchange_rate,
        //     //            'fecha'         => Carbon::parse(data_get($meta_xml, 'Complemento.TimbreFiscalDigital.FechaTimbrado'))->format('d/m/Y'),
        //     'fecha'      => Carbon::parse($invoiceSat->trandate)->format('d/m/Y'),
        //     'ubicacion'  => $invoiceSat?->invoice_location_id ?? '',
        //     'idnetsuite' => $invoiceSat->order_id ?? '',
        //     'uuid'       => $invoiceSat->uuid,
        //     'gastos'     => $costs,
        //     'articulos'  => [],
        //     'nota'       => $invoiceSat->notes                 ??= '',
        //     'generico'   => $invoiceSat->invoice_provider_type ??= '',
        //     'xml'        => $xmlBase64,
        //     'pdf'        => $pdfBase64,
        // ];

        // //return $data;

        // if ($invoiceSat->invoice_article_id || $invoiceSat->invoice_accounting_id) {
        //     $articles          = map_xml_articles(data_get($meta_x, 'Conceptos.Concepto'), $invoiceSat);
        //     $data['articulos'] = $articles;
        //     $data['gastos']    = [];
        // }

        // $restletPath = '/restlet.nl?script=1908&deploy=1';

        // //return $data;
        
        // try {
        //     $response = $this->netSuiteRestService->request($restletPath, 'POST', $data);
        //     return response()->json(['ok' => true, 'response' => $response]);
        // } catch (\Throwable $e) {
        //     return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        // }

        
    }

    
}
