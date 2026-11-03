<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Modules\ResellerHub\App\Jobs\ExportTenantDataJob;
use Modules\ResellerHub\App\Models\Reseller;
use Modules\ResellerHub\App\Services\ResellerAuditService;
use Modules\ResellerHub\App\Services\TenantDataExporterService;
use Modules\ResellerHub\App\Traits\HasReseller;
use Modules\ResellerHub\App\Traits\HasResellerPagination;
use Modules\ResellerHub\App\Traits\HasResellerSorting;
use Modules\ResellerHub\App\Traits\HasSubAccountSearch;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TenantDataExportController extends Controller
{
    use HasReseller, HasSubAccountSearch, HasResellerSorting, HasResellerPagination;

    protected TenantDataExporterService $exporter;
    protected ResellerAuditService $auditService;

    public function __construct(TenantDataExporterService $exporter, ResellerAuditService $auditService)
    {
        $this->exporter = $exporter;
        $this->auditService = $auditService;
    }

    /**
     * Redirect standalone Tenant Data Export route to settings tab.
     */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('reseller.settings.index', array_merge(['tab' => 'database'], $request->query()));
    }

    /**
     * Download standalone SQL dump for a specific tenant with signed URL protection.
     */
    public function exportSql(Request $request, Tenant $tenant): StreamedResponse|RedirectResponse
    {
        $this->authorizeTenantAccess($tenant);

        $user = Auth::user();
        $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('super-admin');
        if (!$isSuperAdmin) {
            abort(403, 'شما دسترسی مجاز برای دریافت خروجی دیتابیس (SQL) را ندارید. این بخش منحصراً در اختیار مدیر ارشد سیستم می‌باشد.');
        }

        if (!$request->hasValidSignature()) {
            $signedUrl = URL::temporarySignedRoute(
                'reseller.tenants.export-sql',
                now()->addMinutes(15),
                ['tenant' => $tenant->id]
            );
            return redirect()->to($signedUrl);
        }

        $filename = 'tenant-' . $tenant->subdomain . '-dump-' . now()->format('Y-m-d_H-i-s') . '.sql';

        $this->auditService->logDataExport($tenant, 'sql', $filename);

        return response()->streamDownload(function () use ($tenant) {
            $stream = fopen('php://output', 'w');
            $this->exporter->streamSqlDump($tenant, $stream);
            fclose($stream);
        }, $filename, [
            'Content-Type' => 'application/sql; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Download JSON export for a specific tenant with signed URL protection.
     */
    public function exportJson(Request $request, Tenant $tenant): StreamedResponse|RedirectResponse
    {
        $this->authorizeTenantAccess($tenant);

        if (!$request->hasValidSignature()) {
            $signedUrl = URL::temporarySignedRoute(
                'reseller.tenants.export-json',
                now()->addMinutes(15),
                ['tenant' => $tenant->id]
            );
            return redirect()->to($signedUrl);
        }

        $filename = 'tenant-' . $tenant->subdomain . '-data-' . now()->format('Y-m-d_H-i-s') . '.json';

        $this->auditService->logDataExport($tenant, 'json', $filename);

        return response()->streamDownload(function () use ($tenant) {
            $stream = fopen('php://output', 'w');
            $this->exporter->streamJsonExport($tenant, $stream);
            fclose($stream);
        }, $filename, [
            'Content-Type' => 'application/json; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Queue asynchronous data export job in background.
     */
    public function queueExport(Request $request, Tenant $tenant): JsonResponse|RedirectResponse
    {
        $this->authorizeTenantAccess($tenant);
        $format = in_array(strtolower((string)$request->input('format')), ['json', 'sql'], true) ? strtolower((string)$request->input('format')) : 'sql';

        ExportTenantDataJob::dispatch($tenant, $format, (int)Auth::id());

        $msg = "درخواست خروجی {$format} برای تنانت {$tenant->subdomain} در صف پردازش قرار گرفت.";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Check if authenticated user can export data of given tenant.
     */
    protected function authorizeTenantAccess(Tenant $tenant): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('super-admin');
        if (!$isSuperAdmin) {
            abort(403, 'دسترسی به استخراج دیتابیس منحصراً در اختیار مدیر ارشد سیستم می‌باشد.');
        }
    }
}
