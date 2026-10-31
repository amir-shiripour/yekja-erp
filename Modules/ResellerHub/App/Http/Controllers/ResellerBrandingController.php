<?php

namespace Modules\ResellerHub\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\FileUploadTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Modules\ResellerHub\App\Http\Requests\UpdateBrandingRequest;
use Modules\ResellerHub\App\Traits\HasReseller;

class ResellerBrandingController extends Controller
{
    use FileUploadTrait;
    use HasReseller;

    /**
     * Redirect to settings branding tab.
     */
    public function edit(): RedirectResponse
    {
        return redirect()->route('reseller.settings.index', ['tab' => 'branding']);
    }

    /**
     * Update reseller branding assets and settings.
     */
    public function update(UpdateBrandingRequest $request): JsonResponse|RedirectResponse
    {
        $reseller = $this->getReseller();

        $validated = $request->validated();
        $data = [
            'company_name' => $validated['company_name'],
            'brand_title' => $validated['brand_title'] ?? null,
            'brand_footer_text' => $validated['brand_footer_text'] ?? null,
            'support_phone' => $validated['support_phone'] ?? null,
            'support_email' => $validated['support_email'] ?? null,
        ];

        // Handle brand logo
        if ($request->boolean('remove_brand_logo')) {
            $this->deleteStoredFile($reseller->brand_logo);
            $data['brand_logo'] = null;
        } elseif ($request->hasFile('brand_logo')) {
            $this->deleteStoredFile($reseller->brand_logo);
            $data['brand_logo'] = $this->uploadFile($request->file('brand_logo'), 'branding/logos', 'public');
        }

        // Handle brand favicon
        if ($request->boolean('remove_brand_favicon')) {
            $this->deleteStoredFile($reseller->brand_favicon);
            $data['brand_favicon'] = null;
        } elseif ($request->hasFile('brand_favicon')) {
            $this->deleteStoredFile($reseller->brand_favicon);
            $data['brand_favicon'] = $this->uploadFile($request->file('brand_favicon'), 'branding/favicons', 'public');
        }

        $reseller->update($data);

        $successMessage = __('resellerhub::messages.branding.updated_success');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'brand_logo_url' => $reseller->brand_logo ? asset('storage/' . $reseller->brand_logo) : null,
                'brand_favicon_url' => $reseller->brand_favicon ? asset('storage/' . $reseller->brand_favicon) : null,
            ]);
        }

        return back()->with('success', $successMessage);
    }

    /**
     * Delete stored file from public disk if present.
     */
    protected function deleteStoredFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
