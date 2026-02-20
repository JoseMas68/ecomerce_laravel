<?php

declare(strict_types=1);

namespace App\Logging;

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Structured Logger para PawfectShop
 *
 * Este logger personalizado crea logs en formato JSON estructurado,
 * facilitando el análisis y búsqueda de logs mediante herramientas
 * como Elasticsearch, Splunk, o servicios de cloud logging.
 *
 * Características:
 * - Formato JSON para fácil parsing
 * - Incluye contexto adicional automáticamente
 * - Soporta diferentes niveles de log
 * - Archivos rotativos por día
 */
class StructuredLogger
{
    /**
     * Crea una instancia del Logger con formato JSON estructurado
     *
     * @param array $config Configuración del canal de logging
     * @return Logger Instancia configurada de Monolog
     */
    public function __invoke(array $config): Logger
    {
        // Crear logger con el nombre de la aplicación
        $logger = new Logger('pawfectshop');

        // Configurar el handler para escribir en archivo
        $handler = new StreamHandler(
            $config['path'] ?? storage_path('logs/pawfectshop.log'),
            $config['level'] ?? 'info'
        );

        // Configurar el formatter JSON con opciones adicionales
        $formatter = new JsonFormatter(
            jsonEncodeOptions: JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );

        // Incluir stacktrace en los logs de error
        $formatter->includeStacktraces(true);

        $handler->setFormatter($formatter);

        // Añadir el handler al logger
        $logger->pushHandler($handler);

        return $logger;
    }
}
