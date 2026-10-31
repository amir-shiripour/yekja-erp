<?php

namespace Modules\ResellerHub\App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Workflows\Entities\Workflow;
use Modules\Workflows\Entities\WorkflowAction;
use Modules\Workflows\Entities\WorkflowTrigger;

class SeedResellerWorkflows extends Command
{
    protected $signature = 'reseller:seed-workflows';
    protected $description = 'Seed default workflows for reseller invoices, payments, and orders';

    public function handle()
    {
        $this->info('Seeding default workflows for ResellerHub...');

        if (!class_exists(Workflow::class)) {
            $this->error('Workflows module is not available.');
            return 1;
        }

        // Clean up legacy reseller workflows if present
        $legacyKeys = [
            'reseller_wf_invoice_paid_activate_order',
            'reseller_wf_invoice_cancelled_cancel_order',
            'reseller_wf_payment_cancelled_suspend_order',
            'reseller_wf_overdue_invoice_deactivate_order',
            'reseller_wf_order_renewal_auto_invoice',
        ];
        foreach ($legacyKeys as $oldKey) {
            $old = Workflow::where('key', $oldKey)->first();
            if ($old) {
                $old->triggers()->delete();
                $old->stages()->delete();
                $old->nodes()->delete();
                $old->edges()->delete();
                $old->delete();
            }
        }

        // 1. Cancel Invoice -> Cancel Reseller Invoice & Reseller Order
        $wf1 = Workflow::updateOrCreate(
            ['key' => 'reseller_system_cancel_invoice_order'],
            ['name' => 'لغو سفارش و فاکتور با لغو فاکتور نمایندگی', 'is_active' => true, 'created_by' => 1]
        );
        $wf1->triggers()->delete();
        $wf1->triggers()->create([
            'type' => WorkflowTrigger::TYPE_EVENT,
            'config' => ['event_key' => ['reseller_invoice_cancelled']]
        ]);
        $wf1->stages()->delete();
        $wf1->nodes()->delete();
        $wf1->edges()->delete();
        $stage1 = $wf1->stages()->create(['name' => 'عملیات لغو', 'sort_order' => 1]);
        $stage1->actions()->create([
            'action_type' => WorkflowAction::TYPE_CHANGE_RESELLER_STATUS,
            'sort_order' => 10,
            'config' => ['entity_type' => 'invoice', 'status' => 'cancelled']
        ]);
        $stage1->actions()->create([
            'action_type' => WorkflowAction::TYPE_CHANGE_RESELLER_STATUS,
            'sort_order' => 20,
            'config' => ['entity_type' => 'order', 'status' => 'cancelled']
        ]);

        // 2. Overdue Invoice Daily
        $wf2 = Workflow::updateOrCreate(
            ['key' => 'reseller_system_overdue_invoice_daily'],
            ['name' => 'معوقه شدن فاکتور و غیرفعال شدن سفارش نمایندگی', 'is_active' => true, 'created_by' => 1]
        );
        $wf2->triggers()->delete();
        $wf2->triggers()->create([
            'type' => WorkflowTrigger::TYPE_RESELLER_INVOICE_REMINDER,
            'config' => [
                'offset_days' => 1,
                'run_at_time' => '08:00',
                'reseller_invoice_statuses' => ['unpaid']
            ]
        ]);
        $wf2->stages()->delete();
        $wf2->nodes()->delete();
        $wf2->edges()->delete();
        $stage2 = $wf2->stages()->create(['name' => 'معوقه شدن', 'sort_order' => 1]);
        $stage2->actions()->create([
            'action_type' => WorkflowAction::TYPE_CHANGE_RESELLER_STATUS,
            'sort_order' => 10,
            'config' => ['entity_type' => 'invoice', 'status' => 'overdue']
        ]);
        $stage2->actions()->create([
            'action_type' => WorkflowAction::TYPE_CHANGE_RESELLER_STATUS,
            'sort_order' => 20,
            'config' => ['entity_type' => 'order', 'status' => 'inactive']
        ]);

        // 3. Invoice Paid -> Reseller Order Active
        $wf3 = Workflow::updateOrCreate(
            ['key' => 'reseller_system_invoice_paid'],
            ['name' => 'تکمیل پرداخت فاکتور و فعال‌سازی سفارش نمایندگی', 'is_active' => true, 'created_by' => 1]
        );
        $wf3->triggers()->delete();
        $wf3->triggers()->create([
            'type' => WorkflowTrigger::TYPE_EVENT,
            'config' => [
                'event_key' => ['reseller_invoice_paid'],
                'reseller_invoice_statuses' => ['paid']
            ]
        ]);
        $wf3->stages()->delete();
        $wf3->nodes()->delete();
        $wf3->edges()->delete();
        $stage3 = $wf3->stages()->create(['name' => 'تکمیل پرداخت', 'sort_order' => 1]);
        $stage3->actions()->create([
            'action_type' => WorkflowAction::TYPE_CHANGE_RESELLER_STATUS,
            'sort_order' => 10,
            'config' => ['entity_type' => 'order', 'status' => 'active']
        ]);

        // 4. Invoice Unpaid -> Reseller Order Pending
        $wf4 = Workflow::updateOrCreate(
            ['key' => 'reseller_system_invoice_unpaid'],
            ['name' => 'لغو پرداخت فاکتور و تعلیق سفارش نمایندگی', 'is_active' => true, 'created_by' => 1]
        );
        $wf4->triggers()->delete();
        $wf4->triggers()->create([
            'type' => WorkflowTrigger::TYPE_EVENT,
            'config' => [
                'event_key' => ['reseller_invoice_unpaid'],
                'reseller_invoice_statuses' => ['unpaid', 'overdue']
            ]
        ]);
        $wf4->stages()->delete();
        $wf4->nodes()->delete();
        $wf4->edges()->delete();
        $stage4 = $wf4->stages()->create(['name' => 'تعلیق سفارش', 'sort_order' => 1]);
        $stage4->actions()->create([
            'action_type' => WorkflowAction::TYPE_CHANGE_RESELLER_STATUS,
            'sort_order' => 10,
            'config' => ['entity_type' => 'order', 'status' => 'pending']
        ]);

        // 5. Invoice Created -> Reseller Order Pending
        $wf5 = Workflow::updateOrCreate(
            ['key' => 'reseller_system_invoice_created'],
            ['name' => 'ثبت فاکتور و انتظار سفارش نمایندگی', 'is_active' => true, 'created_by' => 1]
        );
        $wf5->triggers()->delete();
        $wf5->triggers()->create([
            'type' => WorkflowTrigger::TYPE_EVENT,
            'config' => [
                'event_key' => ['reseller_invoice_created'],
                'reseller_invoice_statuses' => ['unpaid']
            ]
        ]);
        $wf5->stages()->delete();
        $wf5->nodes()->delete();
        $wf5->edges()->delete();
        $stage5 = $wf5->stages()->create(['name' => 'در انتظار پرداخت', 'sort_order' => 1]);
        $stage5->actions()->create([
            'action_type' => WorkflowAction::TYPE_CHANGE_RESELLER_STATUS,
            'sort_order' => 10,
            'config' => ['entity_type' => 'order', 'status' => 'pending']
        ]);

        // 6. Overdue 7 days -> Cancel Reseller Order
        $wf6 = Workflow::updateOrCreate(
            ['key' => 'reseller_system_overdue_7days_cancel_order'],
            ['name' => 'لغو سفارش نمایندگی به دلیل عدم پرداخت طولانی (۷ روز)', 'is_active' => true, 'created_by' => 1]
        );
        $wf6->triggers()->delete();
        $wf6->triggers()->create([
            'type' => WorkflowTrigger::TYPE_RESELLER_INVOICE_REMINDER,
            'config' => [
                'offset_days' => 7,
                'run_at_time' => '08:00',
                'reseller_invoice_statuses' => ['overdue']
            ]
        ]);
        $wf6->stages()->delete();
        $wf6->nodes()->delete();
        $wf6->edges()->delete();
        $stage6 = $wf6->stages()->create(['name' => 'لغو خودکار', 'sort_order' => 1]);
        $stage6->actions()->create([
            'action_type' => WorkflowAction::TYPE_CHANGE_RESELLER_STATUS,
            'sort_order' => 10,
            'config' => ['entity_type' => 'order', 'status' => 'cancelled']
        ]);

        // 7. Revert Reseller Invoice Status to Overdue on Payment Cancelled
        $wf7 = Workflow::updateOrCreate(
            ['key' => 'reseller_system_invoice_unpaid_revert_overdue'],
            ['name' => 'بازگشت وضعیت فاکتور نمایندگی به معوقه در صورت لغو پرداخت', 'is_active' => true, 'created_by' => 1]
        );
        $wf7->triggers()->delete();
        $wf7->triggers()->create([
            'type' => WorkflowTrigger::TYPE_EVENT,
            'config' => [
                'event_key' => ['reseller_invoice_unpaid'],
                'reseller_invoice_statuses' => ['overdue']
            ]
        ]);
        $wf7->stages()->delete();
        $wf7->nodes()->delete();
        $wf7->edges()->delete();
        $stage7 = $wf7->stages()->create(['name' => 'بازگشت وضعیت معوقه', 'sort_order' => 1]);
        $stage7->actions()->create([
            'action_type' => WorkflowAction::TYPE_CHANGE_RESELLER_STATUS,
            'sort_order' => 10,
            'config' => ['entity_type' => 'invoice', 'status' => 'overdue']
        ]);

        // 8. Revert Reseller Invoice Status to Pending on Payment Cancelled
        $wf8 = Workflow::updateOrCreate(
            ['key' => 'reseller_system_invoice_unpaid_revert_pending'],
            ['name' => 'بازگشت وضعیت فاکتور نمایندگی به در انتظار پرداخت در صورت لغو پرداخت', 'is_active' => true, 'created_by' => 1]
        );
        $wf8->triggers()->delete();
        $wf8->triggers()->create([
            'type' => WorkflowTrigger::TYPE_EVENT,
            'config' => [
                'event_key' => ['reseller_invoice_unpaid'],
                'reseller_invoice_statuses' => ['unpaid']
            ]
        ]);
        $wf8->stages()->delete();
        $wf8->nodes()->delete();
        $wf8->edges()->delete();
        $stage8 = $wf8->stages()->create(['name' => 'بازگشت وضعیت در انتظار', 'sort_order' => 1]);
        $stage8->actions()->create([
            'action_type' => WorkflowAction::TYPE_CHANGE_RESELLER_STATUS,
            'sort_order' => 10,
            'config' => ['entity_type' => 'invoice', 'status' => 'unpaid']
        ]);

        // 9. Auto Create Invoice on Order Renewal Date
        $wf9 = Workflow::updateOrCreate(
            ['key' => 'reseller_system_order_renewal_auto_invoice'],
            ['name' => 'ساخت خودکار فاکتور با فرارسیدن تاریخ تمدید سفارش نمایندگی', 'is_active' => true, 'created_by' => 1]
        );
        $wf9->triggers()->delete();
        $wf9->triggers()->create([
            'type' => WorkflowTrigger::TYPE_RESELLER_ORDER_RENEWAL_REMINDER,
            'config' => [
                'offset_days' => 0,
                'run_at_time' => '08:00',
                'reseller_order_statuses' => ['active'],
            ]
        ]);
        $wf9->stages()->delete();
        $wf9->nodes()->delete();
        $wf9->edges()->delete();
        $stage9 = $wf9->stages()->create(['name' => 'ساخت فاکتور تمدید نمایندگی', 'sort_order' => 1]);
        $stage9->actions()->create([
            'action_type' => WorkflowAction::TYPE_CREATE_RESELLER_INVOICE,
            'sort_order' => 10,
            'config' => ['due_date_offset' => 7]
        ]);

        // 10. SMS Renewal Reminder - 7 Days Before Expiration
        $wf10 = Workflow::updateOrCreate(
            ['key' => 'reseller_system_renewal_reminder_7days'],
            ['name' => 'یادآوری تمدید اشتراک ۷ روز قبل از انقضا (پیامک به زیرمجموعه)', 'is_active' => true, 'created_by' => 1]
        );
        $wf10->triggers()->delete();
        $wf10->triggers()->create([
            'type' => WorkflowTrigger::TYPE_RESELLER_ORDER_RENEWAL_REMINDER,
            'config' => [
                'offset_days' => -7,
                'run_at_time' => '09:00',
                'reseller_order_statuses' => ['active'],
            ]
        ]);
        $wf10->stages()->delete();
        $wf10->nodes()->delete();
        $wf10->edges()->delete();
        $stage10 = $wf10->stages()->create(['name' => 'ارسال پیامک یادآوری ۷ روز', 'sort_order' => 1]);
        $stage10->actions()->create([
            'action_type' => WorkflowAction::TYPE_SEND_SMS,
            'sort_order' => 10,
            'config' => [
                'target' => 'RESELLER_SUBACCOUNT',
                'message' => 'مدیر محترم {reseller_subaccount_name}، اشتراک سامانه شما تا ۷ روز آینده ({reseller_order_renewal_date}) به پایان می‌رسد. جهت تمدید بدون قطعی سرویس، به پنل مدیریت خود مراجعه فرمایید.',
            ]
        ]);

        // 11. SMS Renewal Urgent Reminder - 3 Days Before Expiration
        $wf11 = Workflow::updateOrCreate(
            ['key' => 'reseller_system_renewal_reminder_3days'],
            ['name' => 'هشدار فوری موعد تمدید اشتراک ۳ روز قبل از انقضا (پیامک به زیرمجموعه)', 'is_active' => true, 'created_by' => 1]
        );
        $wf11->triggers()->delete();
        $wf11->triggers()->create([
            'type' => WorkflowTrigger::TYPE_RESELLER_ORDER_RENEWAL_REMINDER,
            'config' => [
                'offset_days' => -3,
                'run_at_time' => '10:00',
                'reseller_order_statuses' => ['active'],
            ]
        ]);
        $wf11->stages()->delete();
        $wf11->nodes()->delete();
        $wf11->edges()->delete();
        $stage11 = $wf11->stages()->create(['name' => 'ارسال پیامک هشدار فوری ۳ روز', 'sort_order' => 1]);
        $stage11->actions()->create([
            'action_type' => WorkflowAction::TYPE_SEND_SMS,
            'sort_order' => 10,
            'config' => [
                'target' => 'RESELLER_SUBACCOUNT',
                'message' => 'مدیر محترم {reseller_subaccount_name}، تنها ۳ روز تا پایان مهلت اشتراک سامانه شما باقی مانده است ({reseller_order_renewal_date}). لطفا جهت جلوگیری از تعلیق نسبت به تمدید اقدام فرمایید.',
            ]
        ]);

        // 12. SMS Invoice Issued on Due Date with Payment Link
        $wf12 = Workflow::updateOrCreate(
            ['key' => 'reseller_system_invoice_created_sms'],
            ['name' => 'اطلاع‌رسانی صدور فاکتور و لینک پرداخت به مدیر زیرمجموعه (پیامک)', 'is_active' => true, 'created_by' => 1]
        );
        $wf12->triggers()->delete();
        $wf12->triggers()->create([
            'type' => WorkflowTrigger::TYPE_EVENT,
            'config' => [
                'event_key' => ['reseller_invoice_created'],
                'reseller_invoice_statuses' => ['unpaid']
            ]
        ]);
        $wf12->stages()->delete();
        $wf12->nodes()->delete();
        $wf12->edges()->delete();
        $stage12 = $wf12->stages()->create(['name' => 'ارسال پیامک صدور فاکتور', 'sort_order' => 1]);
        $stage12->actions()->create([
            'action_type' => WorkflowAction::TYPE_SEND_SMS,
            'sort_order' => 10,
            'config' => [
                'target' => 'RESELLER_SUBACCOUNT',
                'message' => 'مدیر محترم {reseller_subaccount_name}، فاکتور اشتراک شماره {reseller_invoice_number} به مبلغ {reseller_invoice_total} صادر شد. لینک پرداخت آنلاین: {reseller_invoice_payment_link}',
            ]
        ]);

        // 13. SMS Service Features Deactivation Notification (1 Day Overdue)
        $wf13 = Workflow::updateOrCreate(
            ['key' => 'reseller_system_suspension_sms_overdue'],
            ['name' => 'اطلاع‌رسانی غیرفعال‌سازی امکانات سامانه به دلیل عدم پرداخت فاکتور (پیامک به زیرمجموعه)', 'is_active' => true, 'created_by' => 1]
        );
        $wf13->triggers()->delete();
        $wf13->triggers()->create([
            'type' => WorkflowTrigger::TYPE_RESELLER_INVOICE_REMINDER,
            'config' => [
                'offset_days' => 1,
                'run_at_time' => '10:00',
                'reseller_invoice_statuses' => ['unpaid', 'overdue']
            ]
        ]);
        $wf13->stages()->delete();
        $wf13->nodes()->delete();
        $wf13->edges()->delete();
        $stage13 = $wf13->stages()->create(['name' => 'ارسال پیامک غیرفعال‌سازی امکانات', 'sort_order' => 1]);
        $stage13->actions()->create([
            'action_type' => WorkflowAction::TYPE_SEND_SMS,
            'sort_order' => 10,
            'config' => [
                'target' => 'RESELLER_SUBACCOUNT',
                'message' => 'مدیر محترم {reseller_subaccount_name}، دسترسی به امکانات سامانه شما به دلیل سررسید فاکتور شماره {reseller_invoice_number} موقتاً غیرفعال شد. جهت بازگشایی و دسترسی کامل به پنل، از لینک زیر نسبت به تسویه اقدام فرمایید: {reseller_invoice_payment_link}',
            ]
        ]);

        // 14. SMS Payment Confirmation & Service Delivery
        $wf14 = Workflow::updateOrCreate(
            ['key' => 'reseller_system_payment_received_sms'],
            ['name' => 'تاییدیه دریافت وجه و فعال‌سازی سرویس (پیامک به مدیر زیرمجموعه)', 'is_active' => true, 'created_by' => 1]
        );
        $wf14->triggers()->delete();
        $wf14->triggers()->create([
            'type' => WorkflowTrigger::TYPE_EVENT,
            'config' => [
                'event_key' => ['reseller_invoice_paid'],
                'reseller_invoice_statuses' => ['paid']
            ]
        ]);
        $wf14->stages()->delete();
        $wf14->nodes()->delete();
        $wf14->edges()->delete();
        $stage14 = $wf14->stages()->create(['name' => 'ارسال پیامک تایید پرداخت', 'sort_order' => 1]);
        $stage14->actions()->create([
            'action_type' => WorkflowAction::TYPE_SEND_SMS,
            'sort_order' => 10,
            'config' => [
                'target' => 'RESELLER_SUBACCOUNT',
                'message' => 'مدیر محترم {reseller_subaccount_name}، پرداخت فاکتور اشتراک شماره {reseller_invoice_number} به مبلغ {reseller_invoice_paid_amount} با موفقیت دریافت و سرویس شما فعال گردید.',
            ]
        ]);

        // 15. SMS Dunning Reminder - 3 Days Overdue
        $wf15 = Workflow::updateOrCreate(
            ['key' => 'reseller_system_overdue_dunning_reminder_3days'],
            ['name' => 'یادآوری مجدد فاکتور معوقه ۳ روز پس از سررسید (پیامک به زیرمجموعه)', 'is_active' => true, 'created_by' => 1]
        );
        $wf15->triggers()->delete();
        $wf15->triggers()->create([
            'type' => WorkflowTrigger::TYPE_RESELLER_INVOICE_REMINDER,
            'config' => [
                'offset_days' => 3,
                'run_at_time' => '10:00',
                'reseller_invoice_statuses' => ['overdue', 'unpaid'],
            ]
        ]);
        $wf15->stages()->delete();
        $wf15->nodes()->delete();
        $wf15->edges()->delete();
        $stage15 = $wf15->stages()->create(['name' => 'ارسال پیامک یادآوری معوقه ۳ روزه', 'sort_order' => 1]);
        $stage15->actions()->create([
            'action_type' => WorkflowAction::TYPE_SEND_SMS,
            'sort_order' => 10,
            'config' => [
                'target' => 'RESELLER_SUBACCOUNT',
                'message' => 'مدیر محترم {reseller_subaccount_name}، فاکتور شماره {reseller_invoice_number} به مبلغ {reseller_invoice_total} به مدت ۳ روز معوق مانده است. جهت تداوم خدمات و جلوگیری از قطع سرویس نسبت به پرداخت اقدام فرمایید: {reseller_invoice_payment_link}',
            ]
        ]);

        // 16. SMS Welcome & Activation on Order Provisioned
        $wf16 = Workflow::updateOrCreate(
            ['key' => 'reseller_system_order_provisioned_welcome'],
            ['name' => 'خوش‌آمدگویی و فعال‌سازی سرویس پس از تحویل سفارش (پیامک به زیرمجموعه)', 'is_active' => true, 'created_by' => 1]
        );
        $wf16->triggers()->delete();
        $wf16->triggers()->create([
            'type' => WorkflowTrigger::TYPE_EVENT,
            'config' => [
                'event_key' => ['reseller_order_provisioned'],
            ]
        ]);
        $wf16->stages()->delete();
        $wf16->nodes()->delete();
        $wf16->edges()->delete();
        $stage16 = $wf16->stages()->create(['name' => 'ارسال پیامک خوش‌آمدگویی و تحویل', 'sort_order' => 1]);
        $stage16->actions()->create([
            'action_type' => WorkflowAction::TYPE_SEND_SMS,
            'sort_order' => 10,
            'config' => [
                'target' => 'RESELLER_SUBACCOUNT',
                'message' => 'مدیر محترم {reseller_subaccount_name}، سفارش سامانه شما به شماره {reseller_order_number} با موفقیت تحویل و آماده استفاده گردید. از اعتماد و همراهی شما سپاسگزاریم.',
            ]
        ]);

        // 17. SMS Quota 90% Threshold Warning
        $wf17 = Workflow::updateOrCreate(
            ['key' => 'reseller_system_quota_warning_alert'],
            ['name' => 'هشدار تکمیل سهمیه منابع سامانه - آستانه ۹۰٪ (پیامک به زیرمجموعه)', 'is_active' => true, 'created_by' => 1]
        );
        $wf17->triggers()->delete();
        $wf17->triggers()->create([
            'type' => WorkflowTrigger::TYPE_EVENT,
            'config' => [
                'event_key' => ['reseller_quota_threshold_exceeded'],
            ]
        ]);
        $wf17->stages()->delete();
        $wf17->nodes()->delete();
        $wf17->edges()->delete();
        $stage17 = $wf17->stages()->create(['name' => 'ارسال پیامک هشدار سهمیه', 'sort_order' => 1]);
        $stage17->actions()->create([
            'action_type' => WorkflowAction::TYPE_SEND_SMS,
            'sort_order' => 10,
            'config' => [
                'target' => 'RESELLER_SUBACCOUNT',
                'message' => 'مدیر محترم {tenant_name}، مصرف سهمیه {reseller_quota_title} شما به {reseller_quota_percent}٪ رسیده است ({reseller_quota_used} از {reseller_quota_limit}). جهت جلوگیری از توقف خدمات نسبت به ارتقای بسته اقدام فرمایید.',
            ]
        ]);

        // 18. SMS Failed Online Payment Followup
        $wf18 = Workflow::updateOrCreate(
            ['key' => 'reseller_system_payment_failed_followup'],
            ['name' => 'اطلاع‌رسانی پرداخت ناموفق آنلاین و راهنمای تلاش مجدد (پیامک به زیرمجموعه)', 'is_active' => true, 'created_by' => 1]
        );
        $wf18->triggers()->delete();
        $wf18->triggers()->create([
            'type' => WorkflowTrigger::TYPE_EVENT,
            'config' => [
                'event_key' => ['reseller_payment_failed'],
            ]
        ]);
        $wf18->stages()->delete();
        $wf18->nodes()->delete();
        $wf18->edges()->delete();
        $stage18 = $wf18->stages()->create(['name' => 'ارسال پیامک پیگیری تراکنش ناموفق', 'sort_order' => 1]);
        $stage18->actions()->create([
            'action_type' => WorkflowAction::TYPE_SEND_SMS,
            'sort_order' => 10,
            'config' => [
                'target' => 'RESELLER_SUBACCOUNT',
                'message' => 'مدیر محترم {reseller_subaccount_name}، تراکنش پرداخت فاکتور شماره {reseller_invoice_number} ناموفق بود. در صورت کسر وجه تا ۷۲ ساعت به حسابتان بازمی‌گردد. جهت پرداخت مجدد: {reseller_invoice_payment_link}',
            ]
        ]);

        $this->info('Successfully seeded all 18 default Workflows for ResellerHub (sub-account notifications)!');
        return 0;
    }
}
