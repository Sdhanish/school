<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- Header & Title -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
  <div>
    <div class="flex items-center gap-2 mb-1">
      <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider bg-primary/10 text-primary border border-primary/20">
        <?php echo html_escape($badge ?? 'NEW'); ?>
      </span>
      <span class="text-body-sm font-semibold text-on-surface-variant">
        <?php echo html_escape($group_name ?? 'Fee & Finance'); ?>
      </span>
    </div>
    <h2 class="font-headline-md text-headline-md text-on-surface">
      <?php echo html_escape($module_name ?? $title ?? 'Module'); ?>
    </h2>
    <p class="text-body-md font-body-md text-on-surface-variant mt-1">
      <?php echo html_escape($description ?? 'This module is scheduled for implementation in Phase 2.'); ?>
    </p>
  </div>
  <div class="flex items-center gap-2 flex-wrap shrink-0">
    <a href="<?php echo site_url('fee-finance/dashboard'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-lowest text-label-md font-semibold hover:bg-surface-container-high transition-colors shadow-2xs">
      <span class="material-symbols-outlined text-[18px]">dashboard</span>Finance Dashboard
    </a>
  </div>
</div>

<!-- Placeholder Card -->
<div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/60 p-8 sm:p-12 text-center max-w-3xl mx-auto my-6">
  <div class="w-16 h-16 rounded-2xl bg-primary/10 text-primary mx-auto flex items-center justify-center mb-5 border border-primary/20 shadow-xs">
    <span class="material-symbols-outlined text-[36px]">schedule</span>
  </div>
  <h3 class="font-headline-md text-headline-md text-on-surface font-bold mb-2">
    <?php echo html_escape($module_name ?? $title); ?> Module
  </h3>
  <p class="text-body-md font-body-md text-on-surface-variant max-w-xl mx-auto mb-6">
    <?php echo html_escape($description ?? 'This screen is a registered navigation item for Phase 2 implementation. The accounting and ledger engine for existing finance components remains active.'); ?>
  </p>

  <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-surface-container-high text-on-surface-variant text-body-sm font-medium border border-outline-variant/40 mb-8">
    <span class="material-symbols-outlined text-[18px] text-primary">info</span>
    <span>Target Navigation Group: <strong class="text-on-surface font-semibold"><?php echo html_escape($group_name ?? 'Fee & Finance'); ?></strong></span>
  </div>

  <div class="pt-6 border-t border-outline-variant/50 flex flex-wrap items-center justify-center gap-3">
    <a href="<?php echo site_url('finance/staff_payouts'); ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm">
      <span class="material-symbols-outlined text-[18px]">payments</span>View Salary Payments
    </a>
    <a href="<?php echo site_url('finance/ledger_staff'); ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-low text-label-md font-medium hover:bg-surface-container-high transition-colors">
      <span class="material-symbols-outlined text-[18px]">menu_book</span>View Staff Ledgers
    </a>
  </div>
</div>
