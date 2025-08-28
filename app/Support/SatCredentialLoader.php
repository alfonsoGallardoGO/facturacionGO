<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use PhpCfdi\Credentials\Credential;

class SatCredentialLoader
{
    /**
     * Carga .cer y .key desde DigitalOcean Spaces a archivos temporales locales
     * y devuelve un PhpCfdi\Credentials\Credential listo para usar.
     *
     * @param  string $cerPath p.ej. "sat/empresa/efirma.cer"
     * @param  string $keyPath p.ej. "sat/empresa/efirma.key"
     * @param  string $password contraseña de la e.firma
     */
    public static function fromSpaces(string $cerPath, string $keyPath, string $password): Credential
    {
        $disk = Storage::disk('spaces');

        $tmpCer = self::tempWithSuffix('.cer');
        $tmpKey = self::tempWithSuffix('.key');

        try {
            // Usa streams para no cargar todo en memoria
            $cerStream = $disk->readStream($cerPath);
            if ($cerStream === false) {
                throw new \RuntimeException("No se pudo leer {$cerPath} de Spaces");
            }
            $outCer = fopen($tmpCer, 'wb');
            stream_copy_to_stream($cerStream, $outCer);
            fclose($cerStream);
            fclose($outCer);

            $keyStream = $disk->readStream($keyPath);
            if ($keyStream === false) {
                throw new \RuntimeException("No se pudo leer {$keyPath} de Spaces");
            }
            $outKey = fopen($tmpKey, 'wb');
            stream_copy_to_stream($keyStream, $outKey);
            fclose($keyStream);
            fclose($outKey);

            // Crea las credenciales desde rutas locales temporales
            return Credential::openFiles($tmpCer, $tmpKey, $password);
        } finally {
            // Limpieza siempre, incluso si hubo excepción
            if (is_file($tmpCer)) @unlink($tmpCer);
            if (is_file($tmpKey)) @unlink($tmpKey);
        }
    }

    private static function tempWithSuffix(string $suffix): string
    {
        $base = tempnam(sys_get_temp_dir(), 'sat_');
        $path = $base . $suffix;
        @rename($base, $path);
        return $path;
    }
}
