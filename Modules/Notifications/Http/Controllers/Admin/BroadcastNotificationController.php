<?php

namespace Modules\Notifications\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Notifications\Entities\NotificationBroadcast;
use Modules\Notifications\Services\NotificationService;
use Morilog\Jalali\Jalalian;
use Spatie\Permission\Models\Role;
use Throwable;

class BroadcastNotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }


    public function index(Request $request)
    {
        $priorityFilter = $request->get('priority', 'all');
        $targetFilter = $request->get('target_type', 'any');
        $periodFilter = $request->get('period', 'all');
        $fromJalali = trim((string)$request->get('from_jalali', ''));
        $toJalali = trim((string)$request->get('to_jalali', ''));
        $perPage = min(max((int)$request->get('per_page', 15), 5), 100);

        $baseQuery = NotificationBroadcast::query();

        $totalBroadcasts = (clone $baseQuery)->count();
        $totalRecipients = (int)(clone $baseQuery)->sum('recipients_count');
        $urgentBroadcasts = (clone $baseQuery)->whereIn('priority', ['urgent', 'high'])->count();
        $allUsersBroadcasts = (clone $baseQuery)->where('target_type', 'all')->count();

        $query = NotificationBroadcast::with('creator');

        if ($priorityFilter && $priorityFilter !== 'all') {
            $query->where('priority', $priorityFilter);
        }

        if ($targetFilter && $targetFilter !== 'any') {
            $query->where('target_type', $targetFilter);
        }

        if ($fromJalali !== '' || $toJalali !== '') {
            if ($from = $this->parseJalaliDate($fromJalali, false)) {
                $query->where('created_at', '>=', $from);
            }
            if ($to = $this->parseJalaliDate($toJalali, true)) {
                $query->where('created_at', '<=', $to);
            }
        } else {
            match ($periodFilter) {
                'today' => $query->whereDate('created_at', today()),
                'week' => $query->where('created_at', '>=', now()->startOfWeek()),
                'month' => $query->where('created_at', '>=', now()->startOfMonth()),
                'year' => $query->where('created_at', '>=', now()->startOfYear()),
                default => null,
            };
        }

        $broadcasts = $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();
        $priorities = config('notifications.priorities', []);

        return view('notifications::admin.broadcast.index', compact(
            'broadcasts',
            'priorities',
            'priorityFilter',
            'targetFilter',
            'periodFilter',
            'fromJalali',
            'toJalali',
            'totalBroadcasts',
            'totalRecipients',
            'urgentBroadcasts',
            'allUsersBroadcasts'
        ));
    }


    private function parseJalaliDate(?string $jalali, bool $endOfDay = false): ?\Carbon\Carbon
    {
        if (empty($jalali)) {
            return null;
        }

        $normalized = str_replace(
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            trim($jalali)
        );

        try {
            $carbon = Jalalian::fromFormat('Y/m/d', $normalized)->toCarbon();
            return $endOfDay ? $carbon->endOfDay() : $carbon->startOfDay();
        } catch (Throwable) {
            return null;
        }
    }


    public function create()
    {
        $roles = Role::all();
        $categories = config('notifications.categories', []);
        $priorities = config('notifications.priorities', []);
        $severities = config('notifications.severities', []);

        return view('notifications::admin.broadcast.create', compact(
            'roles',
            'categories',
            'priorities',
            'severities'
        ));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'category' => 'required|string',
            'priority' => 'required|string',
            'severity' => 'required|string',
            'target_type' => 'required|in:all,role,users',
            'target_roles' => 'nullable|array',
            'target_users' => 'nullable|array',
            'channels' => 'required|array|min:1',
            'action_url' => 'nullable|string|max:500',
        ]);

        $recipientsQuery = User::query();

        if ($validated['target_type'] === 'role' && !empty($validated['target_roles'])) {
            $recipientsQuery->whereHas('roles', function ($q) use ($validated) {
                $q->whereIn('name', $validated['target_roles']);
            });
        } elseif ($validated['target_type'] === 'users' && !empty($validated['target_users'])) {
            $recipientsQuery->whereIn('id', $validated['target_users']);
        }

        $recipients = $recipientsQuery->get();
        $recipientsCount = $recipients->count();

        if ($recipientsCount === 0) {
            return back()->withInput()->with('error', 'هیچ کاربری با معیارهای انتخاب شده یافت نشد.');
        }

        $targetValues = match ($validated['target_type']) {
            'role' => $validated['target_roles'] ?? [],
            'users' => $validated['target_users'] ?? [],
            default => null,
        };

        $broadcast = NotificationBroadcast::create([
            'created_by' => Auth::id(),
            'title' => $validated['title'],
            'message' => $validated['message'],
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'severity' => $validated['severity'],
            'target_type' => $validated['target_type'],
            'target_values' => $targetValues,
            'channels' => $validated['channels'],
            'action_url' => $validated['action_url'],
            'recipients_count' => $recipientsCount,
            'sent_at' => now(),
        ]);

        $sentCount = $this->notificationService->send(
            recipients: $recipients,
            title: $validated['title'],
            message: $validated['message'],
            category: $validated['category'],
            priority: $validated['priority'],
            severity: $validated['severity'],
            actionUrl: $validated['action_url'],
            channels: $validated['channels'],
            options: [
                'broadcast_id' => $broadcast->id,
            ]
        );

        $broadcast->update(['recipients_count' => $sentCount]);

        return redirect()->route('admin.notifications.broadcast.index')
            ->with('success', "اطلاعیه همگانی با موفقیت برای {$sentCount} کاربر ارسال شد.");
    }


    public function destroy($id)
    {
        $broadcast = NotificationBroadcast::findOrFail($id);
        $broadcast->delete();

        return back()->with('success', 'لاگ اطلاعیه با موفقیت حذف شد.');
    }
}
