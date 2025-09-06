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
use App\Support\SatCredentialLoader;
use CfdiUtils\Nodes\XmlNodeUtils;
use DateTimeImmutable;
use DateTimeZone;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use PhpCfdi\CfdiCleaner\Cleaner;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\FielRequestBuilder\Fiel;
use PhpCfdi\CfdiSatScraper\Contracts\ResourceDownloadHandlerInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\ResourceDownloadError;
use PhpCfdi\CfdiSatScraper\Exceptions\ResourceDownloadRequestExceptionError;
use PhpCfdi\CfdiSatScraper\Exceptions\ResourceDownloadResponseError;
use PhpCfdi\CfdiSatScraper\Filters\DownloadType as ScrapperDownloadType;
use PhpCfdi\CfdiSatScraper\Filters\Options\StatesVoucherOption;
use PhpCfdi\CfdiSatScraper\QueryByFilters;
use PhpCfdi\CfdiSatScraper\ResourceType;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\Sessions\Fiel\FielSessionManager;
use PhpCfdi\CfdiToJson\JsonConverter;
use PhpCfdi\CfdiToPdf\Builders\Html2PdfBuilder;
use PhpCfdi\CfdiToPdf\CfdiDataBuilder;
use PhpCfdi\Credentials\Credential;
use PhpCfdi\CfdiToPdf\Converter;

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
    public function index(Request $request)
    {
        $planta = $request->input('planta');
        $planta = $planta ? (int) $planta : null;
        $dates = $request->input('dates');
        $sesion = auth()->user();
        $plantaEmpleado = auth()->user()->planta_empleado;
        $plantaActual = auth()->user()->current_branch_office_id;

        $terms = InvoiceTerm::all();
        $locations = InvoiceLocation::all();
        $exclusions = InvoiceExclusionCategory::all();
        $departments = InvoiceDepartment::all();
        $classes = InvoiceClass::all();
        $categories = InvoiceCategory::all();
        $articles = InvoiceArticles::all();
        $accountingLists = InvoiceAccountingList::all();
        $operationTypes = InvoiceOperationTypes::all();
        $plantas = $sesion->branchOffices()->get(['id', 'code']);
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
            'invoice_sats.invoice_exclusion_category_id',
            'invoice_sats.invoice_department_id',
            'invoice_sats.invoice_class_id',
            'invoice_sats.external_id',
            'invoice_sats.service_processing_at',
            'invoice_sats.service_ends_at',
            'invoice_sats.processing_at',
            'invoice_sats.invoice_category_id',
            'invoice_sats.invoice_location_id',
            'invoice_sats.invoice_term_id',
            'invoice_sats.invoice_operation_type_id',
            'invoice_sats.invoice_accounting_id',
            'invoice_sats.invoice_article_id',
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
        ->leftJoin('invoice_operation_types', 'invoice_operation_types.id', '=', 'invoice_sats.invoice_operation_type_id');
        // ->where('invoice_sats.branch_office_id', 13)
        if ($planta) {
            $invoices->where('invoice_sats.branch_office_id', $planta);
        }else if($plantaActual){
            $invoices ->where('invoice_sats.branch_office_id', $plantaActual);
        }else if($plantaEmpleado){
            $invoices ->where('invoice_sats.branch_office_id', $plantaEmpleado);
        }else{
            $invoices ->where('invoice_sats.branch_office_id', 0);
        }

        if (is_array($dates) && count($dates) === 2) {
            $invoices->whereBetween('invoice_sats.trandate', [$dates[0], $dates[1]]);
        }else{
            $dates = [
                now()->startOfWeek()->format('Y-m-d'),
                now()->endOfWeek()->format('Y-m-d')
            ];
            $invoices->whereBetween('invoice_sats.trandate', [$dates[0], $dates[1]]);
        }
        $invoices = $invoices
        ->orderBy('trandate', 'desc')
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
        })
        ->map(function ($invoice) {
            $invoice->reseteable =
                $invoice->send_status != 'pending' &&
                $invoice->external_id;

            return $invoice;
        })
        ->map(function ($invoice) {
            $invoice->loading =
                ($invoice->service_processing_at && !$invoice->service_ends_at) ||
                $invoice->processing_at;

            return $invoice;
        })
        ;

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
            'plantaEmpleado' => $plantaEmpleado,
            'plantaActual' => $plantaActual,
            'plantaFiltro' => $planta,
            'datesFiltro' => $dates,
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

    public function dailyChunks(\DateTimeImmutable $start, \DateTimeImmutable $end): array
    {
        $chunks = [];
        $cur = $start->setTime(0, 0, 0);
        while ($cur <= $end) {
            $a = $cur;
            $b = $cur->setTime(23, 59, 59);
            if ($b > $end) $b = $end;
            $chunks[] = [$a, $b];
            $cur = $b->modify('+1 second'); // evita misma ventana exacta (cuota 5002)
        }
        return $chunks;
    }

    

    public function sendNetsuite(Request $request, InvoiceSat $invoiceSat)
    {
        $meta_x = $invoiceSat->meta_xml;

        $costs = map_xml_costs(data_get($meta_x, 'Conceptos.Concepto'), $invoiceSat);

        //return $costs;

        $folio = data_get($meta_x, 'Folio');

        if (is_null($folio)) {
            $folio = Str::limit(data_get($meta_x, 'Complemento.TimbreFiscalDigital.UUID'), 5, '');
        }

        $xmlPath = $invoiceSat->xml_path;
        $pdfPath = $invoiceSat->pdf_path; 

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
            'clase'         => 1,
            'operacion'     => 1,
            'tipocambio'    => $invoiceSat?->exchange_rate,
            //            'fecha'         => Carbon::parse(data_get($meta_xml, 'Complemento.TimbreFiscalDigital.FechaTimbrado'))->format('d/m/Y'),
            'fecha'      => Carbon::parse($invoiceSat->trandate)->format('d/m/Y'),
            'ubicacion'  => $invoiceSat?->invoice_location_id ?? '',
            'idnetsuite' => '',
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


    public function descargaSat(Request $request)
    {

        set_time_limit(3000);                // o 0 = ilimitado (no recomendado en web)
        ini_set('max_execution_time', '300');

        $fechaHoraActual = Carbon::now()->format('Y-m-d H:i:s');
        $fechaHoraCero = Carbon::today()->format('Y-m-d H:i:s');

        $brancOffice = Planta::select([
            'branch_offices.id',
            'branch_office_fiscal_keys.key_path',
            'branch_office_fiscal_keys.certificate_path',
            'branch_office_fiscal_keys.passphrase',
            ])
            ->join('branch_office_fiscal_keys', 'branch_office_fiscal_keys.branch_office_id', '=', 'branch_offices.id')
            ->whereNotNull('branch_office_fiscal_keys.key_path')
            ->whereNotNull('branch_office_fiscal_keys.certificate_path')
            ->whereNotNull('branch_office_fiscal_keys.passphrase')
            ->get();
        //return $brancOffice;

        foreach ($brancOffice as $branch) {
            echo "Procesando branch {$branch->id}...\n";
            $cerKey = 'fiscal_keys/'.$branch->certificate_path;
            $keyKey = 'fiscal_keys/'.$branch->key_path;

            // 1) Verifica que existen y tienen contenido (>0 bytes)
            $spaces = Storage::disk('spaces');
            if (! $spaces->exists($cerKey) || ! $spaces->exists($keyKey)) {
                Log::warning("Faltan archivos CER/KEY en Spaces para branch {$branch->branch_id}");
                continue;
            }
            if ($spaces->size($cerKey) <= 0 || $spaces->size($keyKey) <= 0) {
                Log::warning("CER/KEY vacío para branch {$branch->branch_id}");
                continue;
            }

            // 2) Lee contenidos y normaliza passphrase
            $cerContents = $spaces->get($cerKey);
            $keyContents = $spaces->get($keyKey);
            $pass = trim($branch->passphrase);

            $cookiePath = storage_path("app/sat_cookies/cookies.json");
            if (file_exists($cookiePath)) {
                @unlink($cookiePath); // fuerza sesión nueva
            }
            if (! is_dir(dirname($cookiePath))) {
                mkdir(dirname($cookiePath), 0777, true);
            }

            try {
                $tmpDir = storage_path('app/tmp_fiel');
                if (! is_dir($tmpDir)) mkdir($tmpDir, 0777, true);
                $cerPath = $tmpDir.'/test.cer';
                $keyPath = $tmpDir.'/test.key';
                file_put_contents($cerPath, $cerContents);
                file_put_contents($keyPath, $keyContents);

                // 2) Intenta construir la Credential completa: aquí sabremos si pass/pareja son correctos
                $credential = \PhpCfdi\Credentials\Credential::openFiles($cerPath, $keyPath, $pass);
                $cert = $credential->certificate();
            } catch (\Throwable $e) {
                // Contraseña incorrecta, llave no pertenece al cert, formato inválido, etc.
                Log::error('FIEL inválida: '.$e->getMessage());
                echo "FIEL inválida para branch {$branch->id}: {$e->getMessage()}\n";
                continue;
            }

            $cookieJar = new FileCookieJar(storage_path('app/sat_cookies/cookies.json'), true);
            $client = new Client([
                    'timeout'         => 300,
                    'connect_timeout' => 30,
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0) SATScraper',
                    ],
                    'curl' => [
                        CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1',
                        CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
                    ],
                ]);

            $gateway   = new SatHttpGateway($client, $cookieJar);

            $satScraper = new SatScraper(FielSessionManager::create($credential), $gateway);
            
            $from = new DateTimeImmutable($fechaHoraCero, new DateTimeZone('America/Mexico_City'));
            $to   = new DateTimeImmutable($fechaHoraActual, new DateTimeZone('America/Mexico_City'));

            $query = new QueryByFilters($from, $to);
            $query
                ->setDownloadType(ScrapperDownloadType::recibidos())
                ->setStateVoucher(StatesVoucherOption::vigentes());

            $list = $satScraper->listByDateTime($query);

            echo "Encontrados {$list->count()} CFDI(s) para branch {$branch->id} desde {$from->format('Y-m-d H:i:s')} hasta {$to->format('Y-m-d H:i:s')}\n";

            $dest = storage_path('app/sat_xml');
            if (!is_dir($dest)) {
                mkdir($dest, 0777, true);
            }

            $downloadedUuids = $satScraper
                ->resourceDownloader(ResourceType::xml(), $list, 20)
                ->saveTo($dest, true, 0777);
            
            

                
            foreach ($downloadedUuids as $uuid) {
                try {
                    $uuidUpper = Str::upper($uuid);
                    $remote = "sat_xml/{$uuidUpper}.xml";
                    $remotePdf = "sat_xml/{$uuidUpper}.pdf";
                    if (Storage::disk('spaces')->exists($remote)) {
                        echo "El archivo {$remote} ya existe en Spaces.\n";
                        continue; // ya existe: no subas duplicado
                    }
                    $local = "{$dest}/{$uuid}.xml";
                    // Sube por stream (eficiente)
                    Storage::disk('spaces')->put($remote, fopen($local, 'r'));

                
                    $xmlPath = "{$dest}/{$uuid}.xml";
                    if (!file_exists($xmlPath)) {
                        Log::warning("XML no encontrado para {$uuid}");
                        continue;
                    }

                    if (Storage::disk('spaces')->exists($remotePdf)) {
                        echo "El archivo {$remotePdf} ya existe en Spaces.\n";
                        continue; // ya existe: no subas duplicado
                    }
                    

                    $xml = file_get_contents($xmlPath);
                    $xml = Cleaner::staticClean($xml);
                    $pdfPath = "{$dest}/{$uuid}.pdf";

                    $comprobante = XmlNodeUtils::nodeFromXmlString($xml);
                    $cfdiData    = (new CfdiDataBuilder())->build($comprobante);
                    (new Converter(new Html2PdfBuilder()))
                        ->createPdfAs($cfdiData, $pdfPath);
                    $converter = new Converter(new Html2PdfBuilder());

                    
                    $converter->createPdfAs($cfdiData, $pdfPath);

                    Storage::disk('spaces')->put($remotePdf, fopen($pdfPath, 'r'));

                    $array = json_decode(JsonConverter::convertToJson($xml), true);

                    $invoice_id         = '';
                    $emisor_name        = data_get($array, 'Emisor.Nombre');
                    $emisor_rfc         = data_get($array, 'Emisor.Rfc');
                    $trandate           = data_get($array, 'Fecha');
                    $total              = data_get($array, 'Total');
                    $status             = '1';
                    $subsidiary         = data_get($array, 'Receptor.Nombre');
                    $efecto_comprobante = data_get($array, 'TipoDeComprobante');
                    $trandate_cer       = data_get($array, 'Complemento.TimbreFiscalDigital.FechaTimbrado');
                    $trandate_cancel    = data_get($array, 'Complemento.TimbreFiscalDigital.FechaCancelacion');

                    echo json_encode([
                        'uuid'               => $uuidUpper,
                        'invoice_id'         => $invoice_id,
                        'emisor_name'        => $emisor_name,
                        'emisor_rfc'         => $emisor_rfc,
                        'trandate'           => $trandate,
                        'total'              => $total,
                        'status'             => $status,
                        'subsidiary'         => $subsidiary,
                        'efecto_comprobante' => $efecto_comprobante,
                        'branch_office_id'   => 1,
                        'rfc_pac'            => null,
                        'trandate_cer'       => $trandate_cer,
                        'trandate_cancel'    => $trandate_cancel,
                        'meta_xml'          => null,
                        'xml_path'          => "{$uuid}.xml",
                        'pdf_path'          => "{$uuid}.pdf",
                        'branch_office_id'   => $branch->id,
                    ])."\n";

                    InvoiceSat::create(
                        [
                            'uuid'               => $uuidUpper,
                            'invoice_id'         => $invoice_id,
                            'emisor_name'        => $emisor_name,
                            'emisor_rfc'         => $emisor_rfc,
                            'trandate'           => $trandate,
                            'total'              => $total,
                            'status'             => $status,
                            'subsidiary'         => $subsidiary,
                            'efecto_comprobante' => $efecto_comprobante,
                            'branch_office_id'   => 1,
                            'rfc_pac'            => null,
                            'trandate_cer'       => $trandate_cer,
                            'trandate_cancel'    => $trandate_cancel,
                            'meta_xml'          => $array,
                            'xml_path'          => "{$uuidUpper}.xml",
                            'pdf_path'          => "{$uuidUpper}.pdf",
                            'branch_office_id'   => $branch->id,
                        ]
                    );

                } catch (\Throwable $e) {
                    Log::warning("Fallo generando PDF {$uuid}: {$e->getMessage()}");
                    continue;
                }
            }

            usleep(300 * 1000); // 300ms entre branches (no es necesario, pero por si acaso)
        }
        
    }
    
}
