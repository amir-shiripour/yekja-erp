<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VersionLog;
use App\Services\GitHubService;
use Illuminate\Http\Request;

class VersionControlController extends Controller
{
    protected GitHubService $gitService;

    public function __construct(GitHubService $gitService)
    {
        $this->gitService = $gitService;
    }

    public function index()
    {
        $versions = VersionLog::orderBy('release_date', 'desc')->paginate(15);
        $currentLocal = VersionLog::current();
        $remoteInfo = session('remote_version_info');
        $repoName = $this->gitService->getRepo();
        $branchName = $this->gitService->getBranch();

        return view('admin.version_control.index', compact('versions', 'currentLocal', 'remoteInfo', 'repoName', 'branchName'));
    }

    /**
     * چک کردن وضعیت مخزن گیت‌هاب
     */
    public function checkRemote()
    {
        $info = $this->gitService->getLatestRemoteInfo();
        if ($info) {
            session(['remote_version_info' => $info]);
            return redirect()->back()->with('success', 'اطلاعات مخزن با موفقیت دریافت و همگام شد.');
        }
        return redirect()->back()->with('error', 'امکان برقراری ارتباط با مخزن گیت‌هاب (' . $this->gitService->getRepo() . ') وجود ندارد.');
    }

    /**
     * اجرای عملیات بروزرسانی مستقیم
     */
    public function deployUpdate()
    {
        $result = $this->gitService->performUpdate();

        if ($result['success']) {
            return redirect()->route('admin.version-control.index')->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message']);
    }

    public function create()
    {
        return view('admin.version_control.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'version_number' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'release_date' => 'required|date',
            'summary' => 'nullable|string',
            'changelog' => 'nullable|array',
            'is_current' => 'nullable|boolean',
        ]);

        if (!empty($validated['is_current'])) {
            VersionLog::where('is_current', true)->update(['is_current' => false]);
        }

        VersionLog::create($validated);

        return redirect()->route('admin.version-control.index')->with('success', 'نسخه با موفقیت ثبت شد.');
    }

    public function edit(VersionLog $versionControl)
    {
        return view('admin.version_control.edit', ['version' => $versionControl]);
    }

    public function update(Request $request, VersionLog $versionControl)
    {
        $validated = $request->validate([
            'version_number' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'release_date' => 'required|date',
            'summary' => 'nullable|string',
            'changelog' => 'nullable|array',
            'is_current' => 'nullable|boolean',
        ]);

        if (!empty($validated['is_current'])) {
            VersionLog::where('id', '!=', $versionControl->id)->update(['is_current' => false]);
        }

        $versionControl->update($validated);

        return redirect()->route('admin.version-control.index')->with('success', 'نسخه با موفقیت ویرایش شد.');
    }

    public function destroy(VersionLog $versionControl)
    {
        $versionControl->delete();
        return redirect()->route('admin.version-control.index')->with('success', 'نسخه با موفقیت حذف شد.');
    }
}
