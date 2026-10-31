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
     * Display the Tenant Data Export dashboard in Admin / ResellerHub.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('super-admin');
        $reseller = null;

        $baseQuery = Tenant::query();

        if ($isSuperAdmin) {
            $resellersList = Reseller::with('user')->orderBy('company_name')->get();
            $selectedResellerId = $request->input('reseller_id');
            if ($selectedResellerId && $selectedResellerId !== 'all') {
                $baseQuery->where('reseller_id', $selectedResellerId);
            }
        } else {
            $reseller = $this->getReseller();
            $baseQuery->where('reseller_id', $reseller->id);
            $resellersList = collect([]);
        }

        // Summary Counters before search/status filter
        $totalTenants = (clone $baseQuery)->count();
        $activeTenants = (clone $baseQuery)->where('status', Tenant::STATUS_ACTIVE)->count();
        $customDomainCount = (clone $baseQuery)->whereNotNull('custom_domain')
            ->where('custom_domain', '!=', '')->count();

        // Search Filter
        $search = trim((string)$request->query('search', ''));
        $this->applyTenantSearch($baseQuery, $search);

        // Status Filter
        $status = $request->query('status');
        if ($status && in_array($status, [Tenant::STATUS_ACTIVE, Tenant::STATUS_SUSPENDED, Tenant::STATUS_EXPIRED], true)) {
            $baseQuery->where('status', $status);
        }

        $this->applySorting($baseQuery, $request, [
            'name' => 'tenants.name',
            'subdomain' => 'tenants.subdomain',
            'status' => 'tenants.status',
            'reseller' => 'tenants.reseller_id',
            'reseller_id' => 'tenants.reseller_id',
            'date' => 'tenants.created_at',
            'created_at' => 'tenants.created_at',
        ], defaultColumn: 'tenants.id');

        // Per Page
        $perPage = $this->resolvePerPage($request, default: 10);

        $tenants = $baseQuery->with(['subAccount.user', 'reseller.user', 'package'])
            ->paginate($perPage)
            ->withQueryString();

        // Attach statistics summary for all tenants on this page in batch (avoids N+1 query storm)
        $tenantsData = $this->exporter->getStatisticsForMultipleTenants($tenants->items());

        return view('resellerhub::user.tenants.export', compact(
            'tenants',
            'tenantsData',
            'search',
            'status',
            'isSuperAdmin',
            'resellersList',
            'reseller',
            'totalTenants',
            'activeTenants',
            'customDomainCount'
        ));
    }

    /**
     * Download standalone SQL dump for a specific tenant with signed URL protection.
     */
    public function exportSql(Request $request, Tenant $tenant): StreamedResponse|RedirectResponse
    {
        $this->authorizeTenantAccess($tenant);

        $user = Auth::user();
        $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('super-admin');
        if (!$isSuperAdmin && !$user->can('resellerhub.tenants.export_sql')) {
            abort(403, 'شما دسترسی مجاز برای دریافت خروجی دیتابیس (SQL) را ندارید.');
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
        if ($isSuperAdmin) {
            return;
        }

        $reseller = $this->getReseller();
        if ((int)$tenant->reseller_id !== (int)$reseller->id) {
            abort(403, __('resellerhub::messages.tenants.unauthorized_export'));
        }
    }
}
