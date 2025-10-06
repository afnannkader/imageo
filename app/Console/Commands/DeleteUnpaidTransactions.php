<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteUnpaidTransactions extends Command
{
    protected $signature = 'transactions:unpaid-delete';

    protected $description = 'Delete unpaid transactions';

    public function handle()
    {
        $transactions = Transaction::where('created_at', '<=', Carbon::now()->subHour())->whereIn('status', [0, 1])->get();
        if ($transactions->count() > 0) {
            foreach ($transactions as $transaction) {
                $transaction->delete();
            }
        }
    }
}