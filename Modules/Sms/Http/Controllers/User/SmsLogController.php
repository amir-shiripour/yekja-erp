<?php

namespace Modules\Sms\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Sms\Entities\SmsMessage;

class SmsLogController extends Controller
{
    public function index(Request $request)
    {
        $baseQuery = SmsMessage::query();

        $totalCount   = (clone $baseQuery)->count();
        $sentCount    = (clone $baseQuery)->where('status', SmsMessage::STATUS_SENT)->count();
        $failedCount  = (clone $baseQuery)->where('status', SmsMessage::STATUS_FAILED)->count();
        $pendingCount = (clone $baseQuery)->where('status', SmsMessage::STATUS_PENDING)->count();

        $query = SmsMessage::query()->latest();

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($qbuilder) use ($q) {
                $qbuilder->where('to', 'like', "%{$q}%")
                    ->orWhere('message', 'like', "%{$q}%")
                    ->orWhere('template_key', 'like', "%{$q}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $period = $request->input('period', 'all');
        match ($period) {
            'today' => $query->whereDate('created_at', today()),
            'week'  => $query->where('created_at', '>=', now()->startOfWeek()),
            'month' => $query->where('created_at', '>=', now()->startOfMonth()),
            'year'  => $query->where('created_at', '>=', now()->startOfYear()),
            default => null,
        };

        $perPage = min(max((int) $request->input('per_page', 15), 5), 100);
        $messages = $query->paginate($perPage)->withQueryString();

        return view('sms::user.logs.index', [
            'messages'     => $messages,
            'totalCount'   => $totalCount,
            'sentCount'    => $sentCount,
            'failedCount'  => $failedCount,
            'pendingCount' => $pendingCount,
        ]);
    }

    public function resend(SmsMessage $message, \Modules\Sms\Services\SmsManager $smsManager)
    {
        if ($message->status === 'sent') {
            return back()->with('error', 'این پیامک قبلاً با موفقیت ارسال شده است.');
        }

        $options = [
            'type'         => $message->type,
            'driver'       => $message->driver,
            'related_type' => $message->related_type,
            'related_id'   => $message->related_id,
            'created_by'   => auth()->id(),
            'meta'         => array_merge($message->meta ?? [], [
                'resend_of' => $message->id,
            ]),
        ];

        try {
            if ($message->template_key) {
                $newSms = $smsManager->sendPattern(
                    $message->to,
                    $message->template_key,
                    $message->params ?? [],
                    $options
                );
            } else {
                $newSms = $smsManager->sendText(
                    $message->to,
                    $message->message ?? '',
                    $options
                );
            }

            if ($newSms && $newSms->status === SmsMessage::STATUS_SENT) {
                return back()->with('success', 'پیامک با موفقیت مجدداً ارسال شد.');
            } elseif ($newSms && $newSms->status === SmsMessage::STATUS_FAILED) {
                return back()->with('error', 'ارسال مجدد پیامک با خطا مواجه شد: ' . ($newSms->error ?? 'خطای نامشخص'));
            }

            return back()->with('success', 'پیامک برای ارسال مجدد در صف قرار گرفت.');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('[SmsLogController] resend failed', [
                'id' => $message->id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'خطا در ارسال مجدد: ' . $e->getMessage());
        }
    }
}

