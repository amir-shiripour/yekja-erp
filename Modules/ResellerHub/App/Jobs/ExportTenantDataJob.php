<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Jobs;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\ResellerHub\App\Services\ResellerAuditService;
use Modules\ResellerHub\App\Services\TenantDataExporterService;
use Throwable;

class ExportTenantDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600; // 10 minutes timeout for large dumps
    public int $tries = 2;

    public function __construct(
        public Tenant $tenant,
        public string $format = 'sql',
        public ?int $requestedByUserId = null
    ) {}

    public function handle(TenantDataExporterService $exporter, ResellerAuditService $auditService): void
    {
        $tenant = $this->tenant;
        $format = strtolower($this->format);
        $dateStr = now()->format('Y-m-d_H-i-s');
        $filename = "tenant-{$tenant->subdomain}-dump-{$dateStr}." . ($format === 'json' ? 'json' : 'sql');
        $relativeDir = "exports/tenant-{$tenant->id}";
        $relativePath = "{$relativeDir}/{$filename}";

        try {
            Storage::disk('local')->makeDirectory($relativeDir);
            $fullPath = Storage::disk('local')->path($relativePath);
            $stream = fopen($fullPath, 'w');

            if ($format === 'json') {
                $exporter->streamJsonExport($tenant, $stream);
            } else {
                $exporter->streamSqlDump($tenant, $stream);
            }

            fclose($stream);

            $auditService->logDataExport($tenant, $format, $filename, [
                'async' => true,
                'path' => $relativePath,
                'user_id' => $this->requestedByUserId,
            ]);

            Log::info("[ExportTenantDataJob] Successfully generated {$format} export for tenant {$tenant->subdomain} at {$relativePath}");
        } catch (Throwable $e) {
            Log::error("[ExportTenantDataJob] Failed to export {$format} for tenant {$tenant->subdomain}: " . $e->getMessage(), [
                'exception' => $e,
            ]);
            throw $e;
        }
    }
}
