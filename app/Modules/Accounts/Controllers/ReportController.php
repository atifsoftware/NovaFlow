<?php

namespace App\Modules\Accounts\Controllers;

use NovaFlow\Core\Controller;

class ReportController extends Controller
{
    public function ledger(): void
    {
        $this->view('Accounts::reports.ledger', ['title' => 'General Ledger — Accounts'], 'main');
    }

    public function trialBalance(): void
    {
        $this->view('Accounts::reports.trial_balance', ['title' => 'Trial Balance — Accounts'], 'main');
    }

    public function profitLoss(): void
    {
        $this->view('Accounts::reports.profit_loss', ['title' => 'Profit & Loss Statement'], 'main');
    }

    public function balanceSheet(): void
    {
        $this->view('Accounts::reports.balance_sheet', ['title' => 'Balance Sheet — Accounts'], 'main');
    }
}
