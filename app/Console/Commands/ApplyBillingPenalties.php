<?php

namespace App\Console\Commands;

use App\Mail\BillStatusNoticeMail;
use App\Models\Bill;
use App\Models\NotificationLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ApplyBillingPenalties extends Command
{
    protected $signature = 'billing:apply-penalties';
    protected $description = 'Apply daily penalties to overdue bills and escalate status (SS3)';

    public function handle(): int
    {
        $bills = Bill::whereIn('status', ['unpaid', 'grace', 'overdue', 'delinquent'])
            ->where('due_date', '<', now()->startOfDay())
            ->with('contract', 'tenant')
            ->get();

        $updated = 0;

        foreach ($bills as $bill) {
            $daysOverdue = (int) $bill->due_date->diffInDays(now()->startOfDay());
            $graceDays   = $bill->contract->penalty_grace_days ?? 3;
            $penaltyRate = $bill->contract->penalty_rate ?? 100;
            $delinquentDay = config('citiescapes.penalty.delinquent_day', 14);
            $evictionDay   = config('citiescapes.penalty.eviction_day', 30);

            $bill->days_overdue = $daysOverdue;

            // Grace period: days 1-3 (no penalty, just reminders)
            if ($daysOverdue <= $graceDays) {
                $bill->status = 'grace';
                // Send grace reminder
                if ($daysOverdue === 1 || $daysOverdue === $graceDays) {
                    NotificationLog::create([
                        'user_id' => $bill->tenant_id,
                        'type'    => 'grace_reminder',
                        'source'  => 'SS3',
                        'message' => "Reminder: Your bill for {$bill->billing_period} is overdue. Grace period ends in " . ($graceDays - $daysOverdue) . " day(s).",
                    ]);
                    $this->sendBillStatusEmail($bill, 'grace', $daysOverdue);
                }
            }
            // Day 4+: apply penalty
            elseif ($daysOverdue < $delinquentDay) {
                $penaltyDays = $daysOverdue - $graceDays;
                $bill->penalty_amount = $penaltyRate * $penaltyDays;
                $bill->total_amount   = $bill->base_rent + $bill->utilities + $bill->penalty_amount;
                $bill->status         = 'overdue';
            }
            // Day 14+: delinquent
            elseif ($daysOverdue < $evictionDay) {
                $penaltyDays = $daysOverdue - $graceDays;
                $bill->penalty_amount = $penaltyRate * $penaltyDays;
                $bill->total_amount   = $bill->base_rent + $bill->utilities + $bill->penalty_amount;
                $bill->status         = 'delinquent';

                if ($daysOverdue === $delinquentDay) {
                    NotificationLog::create([
                        'user_id' => $bill->tenant_id,
                        'type'    => 'delinquent_notice',
                        'source'  => 'SS3',
                        'message' => "NOTICE: Your bill for {$bill->billing_period} is now {$daysOverdue} days overdue and marked Delinquent.",
                    ]);
                    $this->sendBillStatusEmail($bill, 'delinquent', $daysOverdue);
                }
            }
            // Day 30+: eviction
            else {
                $penaltyDays = $daysOverdue - $graceDays;
                $bill->penalty_amount = $penaltyRate * $penaltyDays;
                $bill->total_amount   = $bill->base_rent + $bill->utilities + $bill->penalty_amount;
                $bill->status         = 'eviction';

                if ($daysOverdue === $evictionDay) {
                    NotificationLog::create([
                        'user_id' => $bill->tenant_id,
                        'type'    => 'eviction_notice',
                        'source'  => 'SS3',
                        'message' => "URGENT: Your bill for {$bill->billing_period} is {$daysOverdue} days overdue. Eviction proceedings may begin.",
                    ]);
                    $this->sendBillStatusEmail($bill, 'eviction', $daysOverdue);
                }
            }

            $bill->save();
            $updated++;
        }

        $this->info("Processed {$updated} overdue bills.");
        return self::SUCCESS;
    }

    /**
     * Bell row is the authoritative delivery channel (already created by the
     * caller); this is the best-effort email accompaniment that matches the
     * SS7 delivery guarantee. Failure is logged but does not block the cron.
     */
    private function sendBillStatusEmail(Bill $bill, string $stage, int $daysOverdue): void
    {
        if (!$bill->tenant?->email) return;

        try {
            Mail::to($bill->tenant->email)->send(
                new BillStatusNoticeMail(
                    $bill->tenant->full_name,
                    $stage,
                    (string) ($bill->billing_period ?? '—'),
                    $daysOverdue,
                    number_format((float) $bill->total_amount, 2),
                )
            );
        } catch (\Throwable $e) {
            \Log::error('BillStatusNoticeMail send failed', [
                'bill_id' => $bill->id,
                'stage'   => $stage,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
