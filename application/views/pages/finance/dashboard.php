<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- Flash Messages -->
<?php if ($this->session->flashdata('success')): ?>
  <div class="mb-4 p-3.5 rounded-xl bg-secondary-container text-on-secondary-container text-body-md font-medium flex items-center gap-2 border border-secondary/20">
    <span class="material-symbols-outlined text-[20px] text-secondary">check_circle</span>
    <?php echo html_escape($this->session->flashdata('success')); ?>
  </div>
<?php endif; ?>
<?php if ($this->session->flashdata('error')): ?>
  <div class="mb-4 p-3.5 rounded-xl bg-error-container text-on-error-container text-body-md font-medium flex items-center gap-2 border border-error/20">
    <span class="material-symbols-outlined text-[20px] text-error">error</span>
    <?php echo html_escape($this->session->flashdata('error')); ?>
  </div>
<?php endif; ?>

<!-- Page Header & Quick Navigation -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
  <div>
    <h2 class="font-headline-md text-headline-md text-on-surface">Fee & Finance Dashboard</h2>
    <p class="text-body-md font-body-md text-on-surface-variant mt-1">Institutional accounting overview, live liquidity, student receivables, and Chart of Accounts breakdown.</p>
  </div>
  <div class="flex items-center gap-2 flex-wrap shrink-0">
    <a href="<?php echo site_url('fee-finance/account-groups'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
      <span class="material-symbols-outlined text-[18px]">folder</span>Account Groups
    </a>
    <a href="<?php echo site_url('fee-finance/account-heads'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
      <span class="material-symbols-outlined text-[18px]">account_tree</span>Account Heads
    </a>
    <a href="<?php echo site_url('fee-finance/custom-accounts'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm">
      <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>Custom Accounts
    </a>
  </div>
</div>

<!-- 1. Key Accounting Metric Cards (8 Cards - Reference Design) -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
  <!-- Card 1: Total Receivable -->
  <div class="relative overflow-hidden p-5 rounded-2xl bg-[#FFFBF4] border border-amber-200/80 shadow-[0_2px_10px_rgba(245,158,11,0.06)] hover:shadow-[0_6px_20px_rgba(245,158,11,0.12)] hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between min-h-[195px]">
    <div>
      <div class="w-12 h-12 rounded-full bg-amber-500 text-white flex items-center justify-center shadow-xs mb-3.5">
        <span class="material-symbols-outlined text-[24px]">pending_actions</span>
      </div>
      <span class="text-sm font-semibold text-amber-700 block tracking-tight">Total Receivable</span>
      <div class="text-2xl sm:text-[28px] font-bold font-mono text-slate-900 mt-1 mb-1 tracking-tight">
        ₹<?php echo number_format($metrics['total_receivable'] ?? 0, 2); ?>
      </div>
      <span class="text-xs text-slate-500 font-medium block">Fees & accounts receivable</span>
    </div>
    <div class="mt-3 -mx-2 -mb-2 opacity-85">
      <svg class="w-full h-8" viewBox="0 0 200 35" fill="none" preserveAspectRatio="none">
        <path d="M0,22 C25,30 45,12 70,20 C95,28 115,8 140,18 C165,26 185,10 200,15" stroke="#F59E0B" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>
  </div>

  <!-- Card 2: Total Received -->
  <div class="relative overflow-hidden p-5 rounded-2xl bg-[#F4FBF7] border border-emerald-200/80 shadow-[0_2px_10px_rgba(16,185,129,0.06)] hover:shadow-[0_6px_20px_rgba(16,185,129,0.12)] hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between min-h-[195px]">
    <div>
      <div class="w-12 h-12 rounded-full bg-emerald-500 text-white flex items-center justify-center shadow-xs mb-3.5">
        <span class="material-symbols-outlined text-[24px]">check_circle</span>
      </div>
      <span class="text-sm font-semibold text-emerald-700 block tracking-tight">Total Received</span>
      <div class="text-2xl sm:text-[28px] font-bold font-mono text-slate-900 mt-1 mb-1 tracking-tight">
        ₹<?php echo number_format($metrics['total_received'] ?? 0, 2); ?>
      </div>
      <span class="text-xs text-slate-500 font-medium block">Total collected receipts</span>
    </div>
    <div class="mt-3 -mx-2 -mb-2 opacity-85">
      <svg class="w-full h-8" viewBox="0 0 200 35" fill="none" preserveAspectRatio="none">
        <path d="M0,24 C30,28 50,15 80,18 C110,22 130,8 160,12 C180,15 190,6 200,8" stroke="#10B981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>
  </div>

  <!-- Card 3: Total Payable -->
  <div class="relative overflow-hidden p-5 rounded-2xl bg-[#FFF5F6] border border-rose-200/80 shadow-[0_2px_10px_rgba(244,63,94,0.06)] hover:shadow-[0_6px_20px_rgba(244,63,94,0.12)] hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between min-h-[195px]">
    <div>
      <div class="w-12 h-12 rounded-full bg-rose-500 text-white flex items-center justify-center shadow-xs mb-3.5">
        <span class="material-symbols-outlined text-[24px]">assignment_late</span>
      </div>
      <span class="text-sm font-semibold text-rose-700 block tracking-tight">Total Payable</span>
      <div class="text-2xl sm:text-[28px] font-bold font-mono text-slate-900 mt-1 mb-1 tracking-tight">
        ₹<?php echo number_format($metrics['total_payable'] ?? 0, 2); ?>
      </div>
      <span class="text-xs text-slate-500 font-medium block">Staff & vendor liabilities</span>
    </div>
    <div class="mt-3 -mx-2 -mb-2 opacity-85">
      <svg class="w-full h-8" viewBox="0 0 200 35" fill="none" preserveAspectRatio="none">
        <path d="M0,15 C25,10 45,26 70,18 C95,10 120,28 145,20 C170,12 185,24 200,18" stroke="#F43F5E" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>
  </div>

  <!-- Card 4: Total Expenses -->
  <div class="relative overflow-hidden p-5 rounded-2xl bg-[#F8F6FF] border border-indigo-200/80 shadow-[0_2px_10px_rgba(99,102,241,0.06)] hover:shadow-[0_6px_20px_rgba(99,102,241,0.12)] hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between min-h-[195px]">
    <div>
      <div class="w-12 h-12 rounded-full bg-indigo-600 text-white flex items-center justify-center shadow-xs mb-3.5">
        <span class="material-symbols-outlined text-[24px]">receipt_long</span>
      </div>
      <span class="text-sm font-semibold text-indigo-700 block tracking-tight">Total Expenses</span>
      <div class="text-2xl sm:text-[28px] font-bold font-mono text-slate-900 mt-1 mb-1 tracking-tight">
        ₹<?php echo number_format($metrics['total_expenses'] ?? 0, 2); ?>
      </div>
      <span class="text-xs text-slate-500 font-medium block">Operational disbursements</span>
    </div>
    <div class="mt-3 -mx-2 -mb-2 opacity-85">
      <svg class="w-full h-8" viewBox="0 0 200 35" fill="none" preserveAspectRatio="none">
        <path d="M0,25 C30,22 55,30 85,22 C115,14 140,26 170,16 C185,11 195,14 200,12" stroke="#6366F1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>
  </div>

  <!-- Card 5: Cash Balance -->
  <div class="relative overflow-hidden p-5 rounded-2xl bg-[#F2FAF9] border border-teal-200/80 shadow-[0_2px_10px_rgba(20,184,166,0.06)] hover:shadow-[0_6px_20px_rgba(20,184,166,0.12)] hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between min-h-[195px]">
    <div>
      <div class="w-12 h-12 rounded-full bg-teal-500 text-white flex items-center justify-center shadow-xs mb-3.5">
        <span class="material-symbols-outlined text-[24px]">payments</span>
      </div>
      <span class="text-sm font-semibold text-teal-700 block tracking-tight">Cash Balance</span>
      <div class="text-2xl sm:text-[28px] font-bold font-mono text-slate-900 mt-1 mb-1 tracking-tight">
        ₹<?php echo number_format($metrics['cash_balance'] ?? 0, 2); ?>
      </div>
      <span class="text-xs text-slate-500 font-medium block">Counter & petty cash</span>
    </div>
    <div class="mt-3 -mx-2 -mb-2 opacity-85">
      <svg class="w-full h-8" viewBox="0 0 200 35" fill="none" preserveAspectRatio="none">
        <path d="M0,20 C25,26 45,10 75,18 C105,26 135,12 165,16 C180,18 190,14 200,15" stroke="#14B8A6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>
  </div>

  <!-- Card 6: Bank Balance -->
  <div class="relative overflow-hidden p-5 rounded-2xl bg-[#F4F8FE] border border-blue-200/80 shadow-[0_2px_10px_rgba(59,130,246,0.06)] hover:shadow-[0_6px_20px_rgba(59,130,246,0.12)] hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between min-h-[195px]">
    <div>
      <div class="w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center shadow-xs mb-3.5">
        <span class="material-symbols-outlined text-[24px]">account_balance</span>
      </div>
      <span class="text-sm font-semibold text-blue-700 block tracking-tight">Bank Balance</span>
      <div class="text-2xl sm:text-[28px] font-bold font-mono text-slate-900 mt-1 mb-1 tracking-tight">
        ₹<?php echo number_format($metrics['bank_balance'] ?? 0, 2); ?>
      </div>
      <span class="text-xs text-slate-500 font-medium block">Institutional bank accounts</span>
    </div>
    <div class="mt-3 -mx-2 -mb-2 opacity-85">
      <svg class="w-full h-8" viewBox="0 0 200 35" fill="none" preserveAspectRatio="none">
        <path d="M0,22 C30,16 60,26 90,18 C120,10 150,22 175,14 C188,10 195,12 200,11" stroke="#2563EB" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>
  </div>

  <!-- Card 7: Pending Payments -->
  <div class="relative overflow-hidden p-5 rounded-2xl bg-[#FFF7ED] border border-orange-200/80 shadow-[0_2px_10px_rgba(249,115,22,0.06)] hover:shadow-[0_6px_20px_rgba(249,115,22,0.12)] hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between min-h-[195px]">
    <div>
      <div class="w-12 h-12 rounded-full bg-orange-500 text-white flex items-center justify-center shadow-xs mb-3.5">
        <span class="material-symbols-outlined text-[24px]">hourglass_top</span>
      </div>
      <span class="text-sm font-semibold text-orange-700 block tracking-tight">Pending Payments</span>
      <div class="text-2xl sm:text-[28px] font-bold font-mono text-slate-900 mt-1 mb-1 tracking-tight">
        ₹<?php echo number_format($metrics['pending_payments'] ?? 0, 2); ?>
      </div>
      <span class="text-xs text-slate-500 font-medium block">Outstanding fee dues</span>
    </div>
    <div class="mt-3 -mx-2 -mb-2 opacity-85">
      <svg class="w-full h-8" viewBox="0 0 200 35" fill="none" preserveAspectRatio="none">
        <path d="M0,18 C25,24 50,12 75,20 C100,28 125,8 150,16 C175,24 190,14 200,16" stroke="#F97316" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>
  </div>

  <!-- Card 8: Active Accounts -->
  <div class="relative overflow-hidden p-5 rounded-2xl bg-[#F0F9FF] border border-sky-200/80 shadow-[0_2px_10px_rgba(14,165,233,0.06)] hover:shadow-[0_6px_20px_rgba(14,165,233,0.12)] hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between min-h-[195px]">
    <div>
      <div class="w-12 h-12 rounded-full bg-sky-500 text-white flex items-center justify-center shadow-xs mb-3.5">
        <span class="material-symbols-outlined text-[24px]">dns</span>
      </div>
      <span class="text-sm font-semibold text-sky-700 block tracking-tight">Active Accounts</span>
      <div class="text-2xl sm:text-[28px] font-bold font-mono text-slate-900 mt-1 mb-1 tracking-tight">
        <?php echo (int)($metrics['active_accounts'] ?? 0); ?>
      </div>
      <span class="text-xs text-slate-500 font-medium block">COA Heads & Custom Accounts</span>
    </div>
    <div class="mt-3 -mx-2 -mb-2 opacity-85">
      <svg class="w-full h-8" viewBox="0 0 200 35" fill="none" preserveAspectRatio="none">
        <path d="M0,20 C30,25 60,14 90,18 C120,22 150,10 175,15 C188,18 195,14 200,16" stroke="#0EA5E9" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>
  </div>
</div>

<!-- 2. Financial Summary Section -->
<div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-6 mb-6">
  <div class="flex items-center justify-between pb-4 border-b border-outline-variant/50 mb-5">
    <div>
      <h3 class="font-headline-md text-title-md font-semibold text-on-surface">Financial Summary</h3>
      <p class="text-body-sm text-on-surface-variant mt-0.5">Consolidated double-entry ledger status for <?php echo html_escape($this->current_school->school_name ?? 'Current School'); ?>.</p>
    </div>
    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-primary/10 text-primary border border-primary/20">
      <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>Live Accounting
    </span>
  </div>

  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
    <!-- Receivable -->
    <div class="p-4 rounded-xl bg-surface-container-low/70 border border-outline-variant/40">
      <span class="text-[11px] font-semibold text-on-surface-variant uppercase tracking-wider block mb-1">Receivable</span>
      <span class="text-lg font-bold font-mono text-on-surface block">₹<?php echo number_format($metrics['total_receivable'] ?? 0, 2); ?></span>
      <span class="text-[11px] text-amber-700 font-medium">To Collect</span>
    </div>

    <!-- Received -->
    <div class="p-4 rounded-xl bg-surface-container-low/70 border border-outline-variant/40">
      <span class="text-[11px] font-semibold text-on-surface-variant uppercase tracking-wider block mb-1">Received</span>
      <span class="text-lg font-bold font-mono text-emerald-700 block">₹<?php echo number_format($metrics['total_received'] ?? 0, 2); ?></span>
      <span class="text-[11px] text-emerald-700 font-medium">In Bank / Cash</span>
    </div>

    <!-- Payable -->
    <div class="p-4 rounded-xl bg-surface-container-low/70 border border-outline-variant/40">
      <span class="text-[11px] font-semibold text-on-surface-variant uppercase tracking-wider block mb-1">Payable</span>
      <span class="text-lg font-bold font-mono text-rose-700 block">₹<?php echo number_format($metrics['total_payable'] ?? 0, 2); ?></span>
      <span class="text-[11px] text-rose-700 font-medium">Current Dues</span>
    </div>

    <!-- Expenses -->
    <div class="p-4 rounded-xl bg-surface-container-low/70 border border-outline-variant/40">
      <span class="text-[11px] font-semibold text-on-surface-variant uppercase tracking-wider block mb-1">Expenses</span>
      <span class="text-lg font-bold font-mono text-on-surface block">₹<?php echo number_format($metrics['total_expenses'] ?? 0, 2); ?></span>
      <span class="text-[11px] text-on-surface-variant font-medium">Recorded Costs</span>
    </div>

    <!-- Cash -->
    <div class="p-4 rounded-xl bg-surface-container-low/70 border border-outline-variant/40">
      <span class="text-[11px] font-semibold text-on-surface-variant uppercase tracking-wider block mb-1">Cash</span>
      <span class="text-lg font-bold font-mono text-teal-700 block">₹<?php echo number_format($metrics['cash_balance'] ?? 0, 2); ?></span>
      <span class="text-[11px] text-teal-700 font-medium">Hand Balance</span>
    </div>

    <!-- Bank -->
    <div class="p-4 rounded-xl bg-surface-container-low/70 border border-outline-variant/40">
      <span class="text-[11px] font-semibold text-on-surface-variant uppercase tracking-wider block mb-1">Bank</span>
      <span class="text-lg font-bold font-mono text-blue-700 block">₹<?php echo number_format($metrics['bank_balance'] ?? 0, 2); ?></span>
      <span class="text-[11px] text-blue-700 font-medium">Total Liquidity</span>
    </div>
  </div>
</div>

<!-- 3. Account Summary Section (Actual DB counts) -->
<div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-6 mb-6">
  <div class="flex items-center justify-between pb-4 border-b border-outline-variant/50 mb-5">
    <div>
      <h3 class="font-headline-md text-title-md font-semibold text-on-surface">Account Summary</h3>
      <p class="text-body-sm text-on-surface-variant mt-0.5">Database hierarchy counts configured for Chart of Accounts and sub-ledgers.</p>
    </div>
    <a href="<?php echo site_url('fee-finance/account-heads'); ?>" class="text-[13px] font-semibold text-primary hover:underline flex items-center gap-1">
      Chart of Accounts &rarr;
    </a>
  </div>

  <?php $summary = $metrics['account_summary'] ?? []; ?>
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
    <!-- Account Groups -->
    <a href="<?php echo site_url('fee-finance/account-groups'); ?>" class="p-4 rounded-xl bg-surface-container-low/50 hover:bg-surface-container-low border border-outline-variant/40 transition-colors group">
      <div class="flex items-center justify-between mb-2">
        <span class="material-symbols-outlined text-primary text-[22px]">folder</span>
        <span class="text-xs text-primary font-semibold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
      </div>
      <span class="text-2xl font-bold font-mono text-on-surface block"><?php echo (int)($summary['account_groups'] ?? 0); ?></span>
      <span class="text-body-sm font-medium text-on-surface-variant mt-0.5 block">Account Groups</span>
      <span class="text-[10px] text-on-surface-variant">Assets, Liabilities, Equity, etc.</span>
    </a>

    <!-- Account Heads -->
    <a href="<?php echo site_url('fee-finance/account-heads'); ?>" class="p-4 rounded-xl bg-surface-container-low/50 hover:bg-surface-container-low border border-outline-variant/40 transition-colors group">
      <div class="flex items-center justify-between mb-2">
        <span class="material-symbols-outlined text-emerald-600 text-[22px]">account_tree</span>
        <span class="text-xs text-emerald-600 font-semibold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
      </div>
      <span class="text-2xl font-bold font-mono text-on-surface block"><?php echo (int)($summary['account_heads'] ?? 0); ?></span>
      <span class="text-body-sm font-medium text-on-surface-variant mt-0.5 block">Account Heads</span>
      <span class="text-[10px] text-on-surface-variant">General Ledger Heads</span>
    </a>

    <!-- Custom Accounts -->
    <a href="<?php echo site_url('fee-finance/custom-accounts'); ?>" class="p-4 rounded-xl bg-surface-container-low/50 hover:bg-surface-container-low border border-outline-variant/40 transition-colors group">
      <div class="flex items-center justify-between mb-2">
        <span class="material-symbols-outlined text-blue-600 text-[22px]">account_balance_wallet</span>
        <span class="text-xs text-blue-600 font-semibold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
      </div>
      <span class="text-2xl font-bold font-mono text-on-surface block"><?php echo (int)($summary['custom_accounts'] ?? 0); ?></span>
      <span class="text-body-sm font-medium text-on-surface-variant mt-0.5 block">Custom Accounts</span>
      <span class="text-[10px] text-on-surface-variant">School bank & cash accounts</span>
    </a>

    <!-- Student Accounts -->
    <div class="p-4 rounded-xl bg-surface-container-low/50 border border-outline-variant/40">
      <div class="flex items-center justify-between mb-2">
        <span class="material-symbols-outlined text-amber-600 text-[22px]">school</span>
        <span class="px-1.5 py-0.2 rounded text-[9px] font-semibold bg-amber-100 text-amber-800">Sub-Ledger</span>
      </div>
      <span class="text-2xl font-bold font-mono text-on-surface block"><?php echo (int)($summary['student_accounts'] ?? 0); ?></span>
      <span class="text-body-sm font-medium text-on-surface-variant mt-0.5 block">Student Accounts</span>
      <span class="text-[10px] text-on-surface-variant">Active student accounts</span>
    </div>

    <!-- Staff Accounts -->
    <div class="p-4 rounded-xl bg-surface-container-low/50 border border-outline-variant/40">
      <div class="flex items-center justify-between mb-2">
        <span class="material-symbols-outlined text-purple-600 text-[22px]">badge</span>
        <span class="px-1.5 py-0.2 rounded text-[9px] font-semibold bg-purple-100 text-purple-800">Sub-Ledger</span>
      </div>
      <span class="text-2xl font-bold font-mono text-on-surface block"><?php echo (int)($summary['staff_accounts'] ?? 0); ?></span>
      <span class="text-body-sm font-medium text-on-surface-variant mt-0.5 block">Staff Accounts</span>
      <span class="text-[10px] text-on-surface-variant">Active staff payroll accounts</span>
    </div>
  </div>
</div>

<!-- 4. Recent Accounting Transactions -->
<div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
  <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/50">
    <div>
      <h3 class="font-headline-md text-title-md font-semibold text-on-surface">Recent Transactions</h3>
      <p class="text-body-sm text-on-surface-variant mt-0.5">Chronological double-entry transactions recorded in the system.</p>
    </div>
    <span class="text-body-sm text-on-surface-variant"><?php echo count($recent_transactions ?? []); ?> Transactions</span>
  </div>

  <div class="table-scroll overflow-x-auto">
    <table class="w-full data-table border-collapse text-body-md">
      <thead>
        <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Date</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Reference</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Description</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Account</th>
          <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Type</th>
          <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Debit</th>
          <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Credit</th>
          <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Status</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-outline-variant/40">
        <?php if (!empty($recent_transactions)): ?>
          <?php foreach ($recent_transactions as $txn): ?>
            <tr class="hover:bg-surface-container-low/30 transition-colors">
              <td class="px-4 py-3 text-on-surface whitespace-nowrap text-xs">
                <?php echo date('d-M-Y', strtotime($txn->transaction_date)); ?>
              </td>
              <td class="px-4 py-3 font-mono text-[13px] text-primary font-semibold whitespace-nowrap">
                <?php echo html_escape($txn->transaction_number); ?>
              </td>
              <td class="px-4 py-3 text-on-surface text-sm max-w-[240px] truncate" title="<?php echo html_escape($txn->description ?? '—'); ?>">
                <?php echo html_escape($txn->description ?? '—'); ?>
              </td>
              <td class="px-4 py-3 text-on-surface text-sm whitespace-nowrap">
                <?php echo html_escape($txn->account_name ?? 'Multiple Accounts'); ?>
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-surface-container-high text-on-surface">
                  <?php echo html_escape(str_replace('_', ' ', $txn->transaction_type)); ?>
                </span>
              </td>
              <td class="px-4 py-3 text-right font-mono font-semibold text-on-surface whitespace-nowrap text-sm">
                <?php $deb = (float)($txn->debit_amount ?? 0); ?>
                <?php echo ($deb > 0) ? ('₹' . number_format($deb, 2)) : '—'; ?>
              </td>
              <td class="px-4 py-3 text-right font-mono font-semibold text-on-surface whitespace-nowrap text-sm">
                <?php $cred = (float)($txn->credit_amount ?? 0); ?>
                <?php echo ($cred > 0) ? ('₹' . number_format($cred, 2)) : '—'; ?>
              </td>
              <td class="px-4 py-3 text-center whitespace-nowrap">
                <?php if (($txn->status ?? '') === 'Reversed'): ?>
                  <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-error-container text-on-error-container">Reversed</span>
                <?php elseif (($txn->status ?? '') === 'Draft'): ?>
                  <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-100 text-amber-800">Draft</span>
                <?php else: ?>
                  <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-secondary-container text-on-secondary-container">Posted</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="8" class="px-4 py-10 text-center text-on-surface-variant">
              <div class="flex flex-col items-center justify-center gap-1.5">
                <span class="material-symbols-outlined text-[32px] text-on-surface-variant/40">receipt_long</span>
                <span class="font-medium text-sm">No transactions recorded yet.</span>
                <span class="text-xs text-on-surface-variant/70">Transactions will appear here automatically when fee collections, payments, or journal entries are posted.</span>
              </div>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
