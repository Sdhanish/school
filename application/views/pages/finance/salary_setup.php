<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
  <div>
    <nav class="flex items-center gap-1.5 text-[12px] text-on-surface-variant mb-1">
      <a href="<?php echo site_url('finance/dashboard'); ?>" class="hover:text-primary">Fee & Finance</a>
      <span class="material-symbols-outlined text-[14px]">chevron_right</span>
      <span class="text-on-surface-variant">Staff Finance</span>
      <span class="material-symbols-outlined text-[14px]">chevron_right</span>
      <span class="text-primary font-semibold">Salary Setup</span>
    </nav>
    <h2 class="font-headline-md text-headline-md text-on-surface">Staff Salary Setup & Structures</h2>
    <p class="text-body-md font-body-md text-on-surface-variant mt-0.5">Staff compensation foundation — define salary structures, earnings, and statutory deductions linked to employees.</p>
  </div>
  <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
    <button onclick="openComponentModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors shadow-2xs cursor-pointer">
      <span class="material-symbols-outlined text-[18px]">add_circle</span>New Component
    </button>
    <button onclick="openStructureModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm cursor-pointer">
      <span class="material-symbols-outlined text-[18px]">post_add</span>Assign Salary Structure
    </button>
  </div>
</div>

<!-- Financial KPI Summary Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
    <div class="flex items-center gap-3 mb-2">
      <span class="material-symbols-outlined text-primary text-[22px]">payments</span>
      <span class="text-[11px] font-semibold text-on-surface-variant uppercase tracking-wider">Gross Payroll</span>
    </div>
    <div class="text-2xl font-bold font-mono text-on-surface">₹<?php echo number_format($total_gross_payroll, 2); ?></div>
    <div class="text-body-xs text-on-surface-variant mt-1">Total monthly gross earnings</div>
  </div>

  <div class="p-5 rounded-2xl bg-amber-50/80 border border-amber-200/70 elevation-1">
    <div class="flex items-center gap-3 mb-2">
      <span class="material-symbols-outlined text-amber-700 text-[22px]">remove_circle</span>
      <span class="text-[11px] font-semibold text-amber-800 uppercase tracking-wider">Total Deductions</span>
    </div>
    <div class="text-2xl font-bold font-mono text-amber-700">₹<?php echo number_format($total_deductions, 2); ?></div>
    <div class="text-body-xs text-amber-600 mt-1">PF, taxes & withholdings</div>
  </div>

  <div class="p-5 rounded-2xl bg-emerald-50/80 border border-emerald-200/70 elevation-1">
    <div class="flex items-center gap-3 mb-2">
      <span class="material-symbols-outlined text-emerald-700 text-[22px]">account_balance_wallet</span>
      <span class="text-[11px] font-semibold text-emerald-800 uppercase tracking-wider">Net Monthly Payable</span>
    </div>
    <div class="text-2xl font-bold font-mono text-emerald-700">₹<?php echo number_format($total_net_payroll, 2); ?></div>
    <div class="text-body-xs text-emerald-600 mt-1">Take-home payroll liability</div>
  </div>

  <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
    <div class="flex items-center gap-3 mb-2">
      <span class="material-symbols-outlined text-secondary text-[22px]">badge</span>
      <span class="text-[11px] font-semibold text-on-surface-variant uppercase tracking-wider">Coverage</span>
    </div>
    <div class="text-2xl font-bold font-mono text-on-surface"><?php echo $configured_count; ?> <span class="text-base font-normal text-on-surface-variant">/ <?php echo $total_staff_count; ?></span></div>
    <div class="text-body-xs text-on-surface-variant mt-1">Staff with active pay structure</div>
  </div>
</div>

<!-- Tabs Navigation -->
<div class="flex items-center gap-3 border-b border-outline-variant/50 mb-6">
  <button type="button" onclick="switchMainTab('structures')" id="tab-btn-structures"
          class="tab-btn pb-3 px-1 text-sm font-semibold flex items-center gap-2 border-b-2 transition-colors cursor-pointer <?php echo ($tab === 'structures') ? 'border-primary text-primary' : 'border-transparent text-on-surface-variant hover:text-on-surface'; ?>">
    <span class="material-symbols-outlined text-[18px]">badge</span>
    Staff Salary Structures
    <span class="px-2 py-0.5 rounded-full text-[11px] bg-primary/10 text-primary font-mono"><?php echo count($structures); ?></span>
  </button>
  <button type="button" onclick="switchMainTab('components')" id="tab-btn-components"
          class="tab-btn pb-3 px-1 text-sm font-semibold flex items-center gap-2 border-b-2 transition-colors cursor-pointer <?php echo ($tab === 'components') ? 'border-primary text-primary' : 'border-transparent text-on-surface-variant hover:text-on-surface'; ?>">
    <span class="material-symbols-outlined text-[18px]">category</span>
    Salary Components Master
    <span class="px-2 py-0.5 rounded-full text-[11px] bg-surface-container-high text-on-surface-variant font-mono"><?php echo count($components); ?></span>
  </button>
</div>

<!-- ========================================================================= -->
<!-- TAB 1: SALARY STRUCTURES                                                  -->
<!-- ========================================================================= -->
<div id="tab-content-structures" class="<?php echo ($tab === 'structures') ? '' : 'hidden'; ?>">
  <!-- Filter Bar -->
  <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-5">
    <form method="get" action="<?php echo site_url('finance/salary_setup'); ?>" class="flex flex-wrap items-end gap-3">
      <input type="hidden" name="tab" value="structures">
      <div class="flex-1 min-w-[200px]">
        <label class="block text-body-xs text-on-surface-variant font-semibold mb-1 uppercase tracking-wide">Search Staff</label>
        <input type="text" name="search" value="<?php echo html_escape($filters['search'] ?? ''); ?>"
               placeholder="Name, employee code, structure name…"
               class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:outline-none focus:border-primary"/>
      </div>
      <div class="min-w-[160px]">
        <label class="block text-body-xs text-on-surface-variant font-semibold mb-1 uppercase tracking-wide">Staff Type</label>
        <select name="staff_type" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:outline-none focus:border-primary">
          <option value="">All Types</option>
          <option value="teacher" <?php echo (($filters['staff_type'] ?? '') === 'teacher') ? 'selected' : ''; ?>>Teaching Faculty</option>
          <option value="non_teaching" <?php echo (($filters['staff_type'] ?? '') === 'non_teaching') ? 'selected' : ''; ?>>Non-Teaching Staff</option>
        </select>
      </div>
      <div class="min-w-[130px]">
        <label class="block text-body-xs text-on-surface-variant font-semibold mb-1 uppercase tracking-wide">Status</label>
        <select name="status" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:outline-none focus:border-primary">
          <option value="">All Statuses</option>
          <option value="1" <?php echo (($filters['status'] ?? '') === '1') ? 'selected' : ''; ?>>Active Only</option>
          <option value="0" <?php echo (($filters['status'] ?? '') === '0') ? 'selected' : ''; ?>>Inactive</option>
        </select>
      </div>
      <div class="flex gap-2">
        <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-on-primary text-body-sm font-semibold hover:bg-primary/90 transition-colors">
          <span class="material-symbols-outlined text-[16px] align-middle">search</span> Filter
        </button>
        <a href="<?php echo site_url('finance/salary_setup?tab=structures'); ?>" class="px-3 py-2 rounded-lg bg-surface-container-high text-on-surface text-body-sm hover:bg-surface-container-highest transition-colors">Reset</a>
      </div>
    </form>
  </div>

  <!-- Salary Structures Table -->
  <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
    <div class="px-5 py-3 border-b border-outline-variant/40 flex items-center justify-between">
      <span class="text-title-sm font-semibold text-on-surface flex items-center gap-1.5">
        <span class="material-symbols-outlined text-primary text-[18px]">account_balance_wallet</span>
        Employee Salary Structures
        <span class="text-body-xs text-on-surface-variant font-normal">(<?php echo count($structures); ?> structures)</span>
      </span>
      <span class="text-body-xs text-on-surface-variant">Linked to staff profiles for payroll calculation</span>
    </div>

    <div class="table-scroll overflow-x-auto">
      <table class="w-full data-table border-collapse text-body-md" id="structures-table">
        <thead>
          <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">#</th>
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Employee / Staff</th>
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Designation & Role</th>
            <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Gross Earnings</th>
            <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Deductions</th>
            <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Net Salary</th>
            <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Effective From</th>
            <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Status</th>
            <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-outline-variant/40">
          <?php if (empty($structures)): ?>
            <tr>
              <td colspan="9" class="px-4 py-12 text-center text-on-surface-variant">
                <span class="material-symbols-outlined text-[40px] block mb-2 text-on-surface-variant/40">account_balance_wallet</span>
                No salary structures found. Click "Assign Salary Structure" to create one.
              </td>
            </tr>
          <?php else: ?>
            <?php $idx = 1; foreach ($structures as $s): ?>
              <tr class="hover:bg-surface-container-low/30 transition-colors">
                <td class="px-4 py-3 text-on-surface-variant text-[12px]"><?php echo $idx++; ?></td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-secondary/15 text-secondary font-bold text-sm flex items-center justify-center shrink-0">
                      <?php echo strtoupper(substr($s->full_name ?? 'S', 0, 1)); ?>
                    </div>
                    <div>
                      <div class="font-semibold text-on-surface text-[13px]"><?php echo html_escape($s->full_name); ?></div>
                      <div class="text-[11px] font-mono text-on-surface-variant"><?php echo html_escape($s->employee_code); ?></div>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3 text-[13px] text-on-surface">
                  <div><?php echo html_escape($s->designation_name ?: 'Staff Member'); ?></div>
                  <span class="inline-block mt-0.5 px-2 py-0.2 rounded-full text-[10px] font-semibold <?php echo ($s->staff_type === 'teacher') ? 'bg-blue-100 text-blue-800' : 'bg-surface-container-high text-on-surface-variant'; ?>">
                    <?php echo ($s->staff_type === 'teacher') ? 'Teaching' : 'Non-Teaching'; ?>
                  </span>
                </td>
                <td class="px-4 py-3 text-right font-mono text-on-surface font-semibold text-[13px]">
                  ₹<?php echo number_format((float)$s->gross_salary, 2); ?>
                </td>
                <td class="px-4 py-3 text-right font-mono text-amber-700 font-semibold text-[13px]">
                  ₹<?php echo number_format((float)$s->total_deductions, 2); ?>
                </td>
                <td class="px-4 py-3 text-right font-mono text-emerald-700 font-bold text-[13px]">
                  ₹<?php echo number_format((float)$s->net_salary, 2); ?>
                </td>
                <td class="px-4 py-3 text-center text-[12px] text-on-surface whitespace-nowrap">
                  <?php echo date('d-M-Y', strtotime($s->effective_from)); ?>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                  <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold <?php echo ($s->status == 1) ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-surface-container text-on-surface-variant'; ?>">
                    <?php echo ($s->status == 1) ? 'Active' : 'Inactive'; ?>
                  </span>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                  <div class="flex items-center justify-center gap-1">
                    <button onclick="viewStructure(<?php echo $s->id; ?>)" class="p-1.5 rounded-lg text-primary hover:bg-primary/10 transition-colors" title="View Breakdown">
                      <span class="material-symbols-outlined text-[16px]">visibility</span>
                    </button>
                    <button onclick="editStructure(<?php echo $s->id; ?>)" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors" title="Edit Structure">
                      <span class="material-symbols-outlined text-[16px]">edit</span>
                    </button>
                    <form method="post" action="<?php echo site_url('finance/salary_setup'); ?>" class="inline" onsubmit="return confirm('Are you sure you want to delete this salary structure?');">
                      <input type="hidden" name="action" value="delete_structure">
                      <input type="hidden" name="id" value="<?php echo $s->id; ?>">
                      <button type="submit" class="p-1.5 rounded-lg text-error hover:bg-error/10 transition-colors" title="Delete Structure">
                        <span class="material-symbols-outlined text-[16px]">delete</span>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ========================================================================= -->
<!-- TAB 2: SALARY COMPONENTS MASTER                                           -->
<!-- ========================================================================= -->
<div id="tab-content-components" class="<?php echo ($tab === 'components') ? '' : 'hidden'; ?>">
  <div class="flex items-center justify-between mb-4">
    <div>
      <h3 class="font-headline-md text-title-md font-bold text-on-surface">Salary Component Library</h3>
      <p class="text-body-sm text-on-surface-variant">Standard and custom salary heads (Earnings and Deductions) available for salary structures.</p>
    </div>
    <button onclick="openComponentModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-primary text-on-primary text-body-sm font-semibold hover:bg-primary/90 transition-colors shadow-2xs">
      <span class="material-symbols-outlined text-[16px]">add</span>Add Component
    </button>
  </div>

  <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
    <div class="table-scroll overflow-x-auto">
      <table class="w-full data-table border-collapse text-body-md" id="components-table">
        <thead>
          <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">#</th>
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Component Name</th>
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Code</th>
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Type / Payer</th>
            <th class="px-4 py-3 text-left font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Calculation Type</th>
            <th class="px-4 py-3 text-right font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Default Rate / Amount</th>
            <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Taxable</th>
            <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Status</th>
            <th class="px-4 py-3 text-center font-semibold text-on-surface-variant uppercase text-[11px] tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-outline-variant/40">
          <?php if (empty($components)): ?>
            <tr>
              <td colspan="9" class="px-4 py-8 text-center text-on-surface-variant">No salary components defined.</td>
            </tr>
          <?php else: ?>
            <?php $cIdx = 1; foreach ($components as $c): ?>
              <?php $is_earn = ($c->component_type === 'Earning'); ?>
              <tr class="hover:bg-surface-container-low/30 transition-colors">
                <td class="px-4 py-3 text-on-surface-variant text-[12px]"><?php echo $cIdx++; ?></td>
                <td class="px-4 py-3 font-semibold text-on-surface text-[13px]">
                  <?php echo html_escape($c->component_name); ?>
                  <?php if (!empty($c->description)): ?>
                    <div class="text-[11px] text-on-surface-variant font-normal"><?php echo html_escape($c->description); ?></div>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-3 font-mono text-primary font-semibold text-[12px]"><?php echo html_escape($c->component_code); ?></td>
                <td class="px-4 py-3 whitespace-nowrap">
                  <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold <?php echo $is_earn ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-amber-100 text-amber-800 border border-amber-200'; ?>">
                    <?php echo html_escape($c->component_type); ?>
                  </span>
                  <?php if (!$is_earn): ?>
                    <span class="ml-1 px-2 py-0.5 rounded-full text-[10px] font-semibold <?php echo ($c->deduction_payer === 'Employer') ? 'bg-purple-100 text-purple-800 border border-purple-200' : 'bg-slate-100 text-slate-700 border border-slate-200'; ?>">
                      <?php echo ($c->deduction_payer === 'Employer') ? 'Employer Contrib.' : 'Employee Ded.'; ?>
                    </span>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-[13px] text-on-surface">
                  <span class="inline-flex items-center gap-1">
                    <span class="material-symbols-outlined text-[15px] text-on-surface-variant"><?php echo ($c->calculation_type === 'Percentage') ? 'percent' : 'payments'; ?></span>
                    <?php echo ($c->calculation_type === 'Percentage') ? '% of Basic' : 'Fixed Amount'; ?>
                  </span>
                </td>
                <td class="px-4 py-3 text-right font-mono text-[13px] text-on-surface">
                  <?php if ($c->calculation_type === 'Percentage'): ?>
                    <?php echo (float)$c->percentage_value; ?>% of Basic
                  <?php else: ?>
                    ₹<?php echo number_format((float)$c->default_amount, 2); ?>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-center text-[12px]">
                  <?php echo $c->is_taxable ? '<span class="text-emerald-700 font-semibold">Yes</span>' : '<span class="text-on-surface-variant">No</span>'; ?>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                  <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold <?php echo ($c->status == 1) ? 'bg-emerald-100 text-emerald-800' : 'bg-surface-container text-on-surface-variant'; ?>">
                    <?php echo ($c->status == 1) ? 'Active' : 'Inactive'; ?>
                  </span>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                  <div class="flex items-center justify-center gap-1">
                    <button onclick='editComponent(<?php echo json_encode($c); ?>)' class="p-1.5 rounded-lg text-primary hover:bg-primary/10 transition-colors" title="Edit Component">
                      <span class="material-symbols-outlined text-[16px]">edit</span>
                    </button>
                    <form method="post" action="<?php echo site_url('finance/salary_setup'); ?>" class="inline" onsubmit="return confirm('Are you sure you want to delete this component?');">
                      <input type="hidden" name="action" value="delete_component">
                      <input type="hidden" name="id" value="<?php echo $c->id; ?>">
                      <button type="submit" class="p-1.5 rounded-lg text-error hover:bg-error/10 transition-colors" title="Delete Component">
                        <span class="material-symbols-outlined text-[16px]">delete</span>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 1: SALARY STRUCTURE BUILDER / EDITOR                                -->
<!-- ========================================================================= -->
<div id="structureModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
  <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/60 shadow-xl w-full max-w-4xl max-h-[92vh] flex flex-col overflow-hidden animate-in fade-in zoom-in duration-150">
    <!-- Modal Header -->
    <div class="px-6 py-4 border-b border-outline-variant/40 flex items-center justify-between bg-surface-container-low/40">
      <div>
        <h3 class="font-headline-md text-title-lg font-bold text-on-surface" id="structModalTitle">Assign Salary Structure</h3>
        <p class="text-body-xs text-on-surface-variant mt-0.5">Link an employee to a defined breakdown of Earnings and Deductions.</p>
      </div>
      <button onclick="closeStructureModal()" class="w-8 h-8 rounded-full flex items-center justify-center text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>

    <!-- Modal Form Body -->
    <form method="post" action="<?php echo site_url('finance/salary_setup'); ?>" class="flex-1 overflow-y-auto p-6 space-y-6" id="structureForm">
      <input type="hidden" name="action" value="save_structure">
      <input type="hidden" name="id" id="struct_id" value="">

      <!-- Staff & Header Fields -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block text-body-xs font-semibold text-on-surface uppercase tracking-wider mb-1">Select Staff Member <span class="text-error">*</span></label>
          <select name="staff_id" id="struct_staff_id" required onchange="onStaffSelected(this.value)"
                  class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:outline-none focus:border-primary">
            <option value="">-- Choose Employee --</option>
            <?php foreach ($staff_members as $sf): ?>
              <option value="<?php echo $sf->staff_id; ?>" data-name="<?php echo html_escape($sf->full_name); ?>" data-salary="<?php echo (float)$sf->salary; ?>">
                <?php echo html_escape($sf->full_name . ' (' . ($sf->employee_code ?: 'ID:' . $sf->staff_id) . ' - ' . ($sf->designation_name ?: 'Staff') . ')'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-body-xs font-semibold text-on-surface uppercase tracking-wider mb-1">Structure Name <span class="text-error">*</span></label>
          <input type="text" name="structure_name" id="struct_name" required placeholder="e.g. Standard Pay Structure"
                 class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:outline-none focus:border-primary"/>
        </div>

        <div>
          <label class="block text-body-xs font-semibold text-on-surface uppercase tracking-wider mb-1">Effective From <span class="text-error">*</span></label>
          <input type="date" name="effective_from" id="struct_eff_from" required value="<?php echo date('Y-m-d'); ?>"
                 class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:outline-none focus:border-primary"/>
        </div>
      </div>

      <!-- Quick Prefill helper banner -->
      <div id="staffPrefillBanner" class="hidden p-3 rounded-xl bg-primary/10 border border-primary/20 text-body-xs flex items-center justify-between">
        <span class="text-primary font-medium" id="prefillMessage">Loaded staff baseline profile.</span>
        <button type="button" onclick="autoBreakdownSalary()" class="text-primary font-bold hover:underline cursor-pointer">
          ⚡ Auto-Calculate Standard Breakdown
        </button>
      </div>

      <!-- Two-Column Breakdown: Earnings vs Deductions -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Earnings Column -->
        <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/30">
          <div class="flex items-center justify-between mb-3">
            <span class="text-title-sm font-bold text-emerald-900 flex items-center gap-1.5">
              <span class="material-symbols-outlined text-[18px] text-emerald-700">add_circle</span>
              Earnings & Allowances
            </span>
            <button type="button" onclick="addEmptyRow('Earning')" class="text-[12px] font-semibold text-emerald-700 hover:text-emerald-900 flex items-center gap-1 cursor-pointer">
              <span class="material-symbols-outlined text-[16px]">add</span> Add Earning
            </button>
          </div>

          <div class="space-y-2.5" id="earningsContainer">
            <!-- Dynamic rows injected here -->
          </div>

          <div class="mt-4 pt-3 border-t border-emerald-200 flex items-center justify-between font-bold text-sm text-emerald-900">
            <span>Total Gross Earnings:</span>
            <span class="font-mono text-base" id="lblGrossEarnings">₹0.00</span>
          </div>
        </div>

        <!-- Deductions Column -->
        <div class="p-4 rounded-xl border border-amber-200 bg-amber-50/30">
          <div class="flex items-center justify-between mb-3">
            <span class="text-title-sm font-bold text-amber-900 flex items-center gap-1.5">
              <span class="material-symbols-outlined text-[18px] text-amber-700">remove_circle</span>
              Deductions & Withholdings
            </span>
            <button type="button" onclick="addEmptyRow('Deduction')" class="text-[12px] font-semibold text-amber-700 hover:text-amber-900 flex items-center gap-1 cursor-pointer">
              <span class="material-symbols-outlined text-[16px]">add</span> Add Deduction
            </button>
          </div>

          <div class="space-y-2.5" id="deductionsContainer">
            <!-- Dynamic rows injected here -->
          </div>

          <div class="mt-4 pt-3 border-t border-amber-200 flex items-center justify-between font-bold text-sm text-amber-900">
            <span>Total Deductions:</span>
            <span class="font-mono text-base" id="lblTotalDeductions">₹0.00</span>
          </div>
        </div>
      </div>

      <!-- Live Calculated Net Summary -->
      <div class="p-4 rounded-2xl bg-surface-container-high border border-outline-variant/60 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <span class="text-body-xs font-semibold uppercase tracking-wider text-on-surface-variant block">Calculated Take-Home Compensation</span>
          <div class="text-2xl font-bold font-mono text-primary mt-0.5" id="lblNetSalary">₹0.00 / month</div>
        </div>
        <div class="flex flex-wrap items-center gap-3 text-body-xs text-on-surface-variant">
          <div class="px-2.5 py-1.5 rounded-lg bg-surface-container-lowest border border-outline-variant/50">
            <span class="text-on-surface-variant block text-[10px] uppercase font-semibold">Gross Earnings</span>
            <strong class="font-mono text-emerald-800 text-sm" id="summaryGross">₹0.00</strong>
          </div>
          <span>−</span>
          <div class="px-2.5 py-1.5 rounded-lg bg-surface-container-lowest border border-outline-variant/50">
            <span class="text-on-surface-variant block text-[10px] uppercase font-semibold">Employee Ded.</span>
            <strong class="font-mono text-amber-700 text-sm" id="summaryDeductions">₹0.00</strong>
          </div>
          <span>|</span>
          <div class="px-2.5 py-1.5 rounded-lg bg-purple-50/80 border border-purple-200">
            <span class="text-purple-800 block text-[10px] uppercase font-semibold">Employer Contrib.</span>
            <strong class="font-mono text-purple-700 text-sm" id="summaryEmployer">₹0.00</strong>
          </div>
        </div>
      </div>

      <!-- Remarks & Status -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-body-xs font-semibold text-on-surface uppercase tracking-wider mb-1">Remarks / Internal Notes</label>
          <input type="text" name="remarks" id="struct_remarks" placeholder="e.g. Approved 6th Pay Revision compensation"
                 class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:outline-none focus:border-primary"/>
        </div>
        <div class="flex items-center gap-3 pt-5">
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" name="status" id="struct_status" value="1" checked class="sr-only peer">
            <div class="w-11 h-6 bg-surface-container-high peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
            <span class="ml-3 text-body-sm font-semibold text-on-surface">Mark as Active Structure</span>
          </label>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="pt-4 border-t border-outline-variant/40 flex items-center justify-end gap-3">
        <button type="button" onclick="closeStructureModal()" class="px-4 py-2 rounded-lg bg-surface-container-high text-on-surface text-body-sm hover:bg-surface-container-highest transition-colors cursor-pointer">
          Cancel
        </button>
        <button type="submit" class="px-5 py-2 rounded-lg bg-primary text-on-primary text-body-sm font-semibold hover:bg-primary/90 transition-colors shadow-sm cursor-pointer">
          Save Structure
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: VIEW STRUCTURE BREAKDOWN                                         -->
<!-- ========================================================================= -->
<div id="viewModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
  <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/60 shadow-xl w-full max-w-2xl overflow-hidden animate-in fade-in zoom-in duration-150">
    <div class="px-6 py-4 border-b border-outline-variant/40 flex items-center justify-between bg-surface-container-low/40">
      <div>
        <h3 class="font-headline-md text-title-lg font-bold text-on-surface" id="viewModalTitle">Salary Structure Breakdown</h3>
        <p class="text-body-xs text-on-surface-variant mt-0.5" id="viewModalSubtitle">Employee compensation statement</p>
      </div>
      <button onclick="closeViewModal()" class="w-8 h-8 rounded-full flex items-center justify-center text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>
    <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
      <div id="viewBreakdownContent">
        <!-- Rendered dynamically -->
      </div>
    </div>
    <div class="px-6 py-3 border-t border-outline-variant/40 flex justify-end bg-surface-container-low/20">
      <button onclick="closeViewModal()" class="px-4 py-2 rounded-lg bg-surface-container-high text-on-surface text-body-sm hover:bg-surface-container-highest transition-colors cursor-pointer">
        Close
      </button>
    </div>
  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: COMPONENT MASTER ADD/EDIT                                        -->
<!-- ========================================================================= -->
<div id="componentModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
  <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/60 shadow-xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in duration-150">
    <div class="px-6 py-4 border-b border-outline-variant/40 flex items-center justify-between bg-surface-container-low/40">
      <h3 class="font-headline-md text-title-md font-bold text-on-surface" id="compModalTitle">Salary Component</h3>
      <button onclick="closeComponentModal()" class="w-8 h-8 rounded-full flex items-center justify-center text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>
    <form method="post" action="<?php echo site_url('finance/salary_setup'); ?>" class="p-6 space-y-4">
      <input type="hidden" name="action" value="save_component">
      <input type="hidden" name="id" id="comp_id" value="">

      <div>
        <label class="block text-body-xs font-semibold text-on-surface uppercase tracking-wider mb-1">Component Name <span class="text-error">*</span></label>
        <input type="text" name="component_name" id="comp_name" required placeholder="e.g. Performance Bonus"
               class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:outline-none focus:border-primary"/>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-body-xs font-semibold text-on-surface uppercase tracking-wider mb-1">Code <span class="text-error">*</span></label>
          <input type="text" name="component_code" id="comp_code" required placeholder="e.g. PERF_BONUS"
                 class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm font-mono uppercase focus:outline-none focus:border-primary"/>
        </div>
        <div>
          <label class="block text-body-xs font-semibold text-on-surface uppercase tracking-wider mb-1">Type <span class="text-error">*</span></label>
          <select name="component_type" id="comp_type" onchange="togglePayerField(this.value)" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:outline-none focus:border-primary">
            <option value="Earning">Earning</option>
            <option value="Deduction">Deduction</option>
          </select>
        </div>
      </div>

      <div id="deductionPayerGroup" class="hidden">
        <label class="block text-body-xs font-semibold text-on-surface uppercase tracking-wider mb-1">Deduction Borne By</label>
        <select name="deduction_payer" id="comp_deduction_payer" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:outline-none focus:border-primary">
          <option value="Employee">Employee Deduction (Reduces Net Salary)</option>
          <option value="Employer">Employer Contribution (School Cost / Non-reducing)</option>
        </select>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-body-xs font-semibold text-on-surface uppercase tracking-wider mb-1">Calculation</label>
          <select name="calculation_type" id="comp_calc" onchange="toggleCalcUnit(this.value)" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:outline-none focus:border-primary">
            <option value="Fixed">Fixed Amount</option>
            <option value="Percentage">Percentage (% of Basic)</option>
          </select>
        </div>
        <div>
          <label class="block text-body-xs font-semibold text-on-surface uppercase tracking-wider mb-1" id="lblDefaultValue">Default Amount (₹)</label>
          <input type="number" step="0.01" name="default_amount" id="comp_default_amt" value="0.00"
                 class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm font-mono focus:outline-none focus:border-primary"/>
          <input type="hidden" name="percentage_value" id="comp_pct_val" value="0.00"/>
        </div>
      </div>

      <div>
        <label class="block text-body-xs font-semibold text-on-surface uppercase tracking-wider mb-1">Description</label>
        <input type="text" name="description" id="comp_desc" placeholder="Brief explanation of policy or purpose"
               class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-body-sm focus:outline-none focus:border-primary"/>
      </div>

      <div class="flex items-center gap-4 pt-2">
        <label class="flex items-center gap-2 cursor-pointer text-body-sm">
          <input type="checkbox" name="is_taxable" id="comp_taxable" value="1" checked class="rounded border-outline-variant text-primary focus:ring-primary"/>
          <span>Taxable Earning</span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer text-body-sm">
          <input type="checkbox" name="status" id="comp_status" value="1" checked class="rounded border-outline-variant text-primary focus:ring-primary"/>
          <span>Active</span>
        </label>
      </div>

      <div class="pt-4 border-t border-outline-variant/40 flex justify-end gap-2">
        <button type="button" onclick="closeComponentModal()" class="px-4 py-2 rounded-lg bg-surface-container-high text-on-surface text-body-sm hover:bg-surface-container-highest transition-colors cursor-pointer">Cancel</button>
        <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-on-primary text-body-sm font-semibold hover:bg-primary/90 transition-colors shadow-2xs cursor-pointer">Save Component</button>
      </div>
    </form>
  </div>
</div>

<!-- Available master components for JS injection -->
<script>
var availableComponents = <?php echo json_encode($components); ?>;
var currentStaffBaselineSalary = 0;

function switchMainTab(t) {
  document.getElementById('tab-content-structures').classList.toggle('hidden', t !== 'structures');
  document.getElementById('tab-content-components').classList.toggle('hidden', t !== 'components');

  var btnS = document.getElementById('tab-btn-structures');
  var btnC = document.getElementById('tab-btn-components');

  if (t === 'structures') {
    btnS.className = 'tab-btn pb-3 px-1 text-sm font-semibold flex items-center gap-2 border-b-2 border-primary text-primary transition-colors cursor-pointer';
    btnC.className = 'tab-btn pb-3 px-1 text-sm font-semibold flex items-center gap-2 border-b-2 border-transparent text-on-surface-variant hover:text-on-surface transition-colors cursor-pointer';
  } else {
    btnC.className = 'tab-btn pb-3 px-1 text-sm font-semibold flex items-center gap-2 border-b-2 border-primary text-primary transition-colors cursor-pointer';
    btnS.className = 'tab-btn pb-3 px-1 text-sm font-semibold flex items-center gap-2 border-b-2 border-transparent text-on-surface-variant hover:text-on-surface transition-colors cursor-pointer';
  }
}

function openStructureModal() {
  document.getElementById('struct_id').value = '';
  document.getElementById('struct_staff_id').value = '';
  document.getElementById('struct_staff_id').disabled = false;
  document.getElementById('struct_name').value = '';
  document.getElementById('struct_remarks').value = '';
  document.getElementById('struct_status').checked = true;
  document.getElementById('structModalTitle').textContent = 'Assign Salary Structure';
  document.getElementById('staffPrefillBanner').classList.add('hidden');

  document.getElementById('earningsContainer').innerHTML = '';
  document.getElementById('deductionsContainer').innerHTML = '';

  // Add default Basic row
  addPredefinedRow('Basic Salary', 'Earning', 0.00, 'Fixed');
  addPredefinedRow('House Rent Allowance (HRA)', 'Earning', 0.00, 'Fixed');
  addPredefinedRow('Provident Fund (PF)', 'Deduction', 0.00, 'Percentage', 12.00);
  addPredefinedRow('Professional Tax (PT)', 'Deduction', 200.00, 'Fixed');

  recalcTotals();
  document.getElementById('structureModal').classList.remove('hidden');
}

function closeStructureModal() {
  document.getElementById('structureModal').classList.add('hidden');
}

function onStaffSelected(staffId) {
  if (!staffId) {
    document.getElementById('staffPrefillBanner').classList.add('hidden');
    return;
  }
  var sel = document.getElementById('struct_staff_id');
  var opt = sel.options[sel.selectedIndex];
  var staffName = opt.getAttribute('data-name') || '';
  var salary = parseFloat(opt.getAttribute('data-salary') || '0');
  currentStaffBaselineSalary = salary;

  if (!document.getElementById('struct_name').value || document.getElementById('struct_name').value.startsWith('Standard Pay Structure')) {
    document.getElementById('struct_name').value = 'Standard Pay Structure — ' + staffName;
  }

  // Check if staff has existing active structure via AJAX
  fetch('<?php echo site_url("finance/get_staff_salary_ajax/"); ?>' + staffId)
    .then(function(r) { return r.json(); })
    .then(function(res) {
      if (res.success) {
        var banner = document.getElementById('staffPrefillBanner');
        var msg = document.getElementById('prefillMessage');
        banner.classList.remove('hidden');

        if (res.active_structure && res.active_items && res.active_items.length > 0) {
          msg.textContent = 'Staff currently has active structure (Gross: ₹' + parseFloat(res.active_structure.gross_salary).toFixed(2) + '). Click to auto-breakdown baseline (₹' + salary.toFixed(2) + ') or customize.';
          loadItemsIntoForm(res.active_items);
        } else {
          msg.textContent = 'Staff baseline salary: ₹' + salary.toFixed(2) + '. Click "Auto-Calculate" to populate standard formula breakdown.';
          if (salary > 0) {
            autoBreakdownSalary();
          }
        }
      }
    });
}

function autoBreakdownSalary() {
  var sal = currentStaffBaselineSalary > 0 ? currentStaffBaselineSalary : 35000;
  var basic = Math.round(sal * 0.50);
  var hra   = Math.round(sal * 0.30);
  var ta    = Math.round(sal * 0.10);
  var spl   = Math.round(sal - (basic + hra + ta));
  var pf    = Math.round(basic * 0.12);
  var pt    = 200.00;

  document.getElementById('earningsContainer').innerHTML = '';
  document.getElementById('deductionsContainer').innerHTML = '';

  addPredefinedRow('Basic Salary', 'Earning', basic, 'Fixed');
  addPredefinedRow('House Rent Allowance (HRA)', 'Earning', hra, 'Fixed');
  addPredefinedRow('Transport Allowance', 'Earning', ta, 'Fixed');
  if (spl > 0) {
    addPredefinedRow('Special Allowance', 'Earning', spl, 'Fixed');
  }
  addPredefinedRow('Provident Fund (PF)', 'Deduction', pf, 'Percentage', 12.00);
  addPredefinedRow('Professional Tax (PT)', 'Deduction', pt, 'Fixed');

  recalcTotals();
}

function togglePayerField(val) {
  var grp = document.getElementById('deductionPayerGroup');
  if (grp) {
    grp.classList.toggle('hidden', val !== 'Deduction');
  }
}

function toggleCalcUnit(val) {
  var lbl = document.getElementById('lblDefaultValue');
  if (lbl) {
    lbl.textContent = (val === 'Percentage') ? 'Default Percentage (%)' : 'Default Amount (₹)';
  }
}

function getBasicSalaryValue() {
  var basicRow = null;
  var rows = document.querySelectorAll('#earningsContainer .item-row');
  for (var i = 0; i < rows.length; i++) {
    var nameInp = rows[i].querySelector('.item-name-input');
    if (nameInp && nameInp.value.trim().toLowerCase().startsWith('basic')) {
      var amtInp = rows[i].querySelector('.item-amount-input');
      return parseFloat(amtInp ? amtInp.value : 0) || 0;
    }
  }
  return 0;
}

function onBasicSalaryChanged() {
  var basic = getBasicSalaryValue();
  // Recalculate any row whose calculation is Percentage
  var pctRows = document.querySelectorAll('.item-row[data-calc="Percentage"]');
  pctRows.forEach(function(row) {
    var pctVal = parseFloat(row.getAttribute('data-pct') || 0);
    var amt = (basic * pctVal) / 100;
    var amtInp = row.querySelector('.item-amount-input');
    if (amtInp) {
      amtInp.value = amt.toFixed(2);
    }
  });
  recalcTotals();
}

function autoBreakdownSalary() {
  var sal = currentStaffBaselineSalary > 0 ? currentStaffBaselineSalary : 35000;
  var basic = Math.round(sal * 0.50);
  var hra   = Math.round(sal * 0.30);
  var ta    = Math.round(sal * 0.10);
  var spl   = Math.round(sal - (basic + hra + ta));
  var pf    = Math.round(basic * 0.12);
  var pt    = 200.00;

  document.getElementById('earningsContainer').innerHTML = '';
  document.getElementById('deductionsContainer').innerHTML = '';

  addPredefinedRow('Basic Salary', 'Earning', basic, 'Fixed', 0, 'Employee');
  addPredefinedRow('House Rent Allowance (HRA)', 'Earning', hra, 'Fixed', 0, 'Employee');
  addPredefinedRow('Transport Allowance', 'Earning', ta, 'Fixed', 0, 'Employee');
  if (spl > 0) {
    addPredefinedRow('Special Allowance', 'Earning', spl, 'Fixed', 0, 'Employee');
  }
  addPredefinedRow('Provident Fund (PF)', 'Deduction', pf, 'Percentage', 12.00, 'Employee');
  addPredefinedRow('Professional Tax (PT)', 'Deduction', pt, 'Fixed', 0, 'Employee');

  recalcTotals();
}

function addPredefinedRow(compName, compType, amt, calcType, pct, payer) {
  var matchedComp = availableComponents.find(function(c) { return c.component_name === compName; });
  var cid = matchedComp ? matchedComp.id : '';
  var actualPayer = payer || (matchedComp ? matchedComp.deduction_payer : 'Employee');
  addRow(compName, compType, amt || 0, calcType || 'Fixed', pct || 0, cid, actualPayer);
}

function addEmptyRow(compType) {
  var defaultName = (compType === 'Earning') ? 'Special Allowance' : 'Other Deduction';
  addRow(defaultName, compType, 0, 'Fixed', 0, '', 'Employee');
}

function addRow(name, type, amt, calc, pct, cid, payer) {
  var container = (type === 'Earning') ? document.getElementById('earningsContainer') : document.getElementById('deductionsContainer');
  var div = document.createElement('div');
  payer = payer || 'Employee';
  calc = calc || 'Fixed';
  pct = parseFloat(pct || 0);

  div.className = 'item-row p-2.5 rounded-lg bg-surface-container-lowest border border-outline-variant/50 flex flex-col sm:flex-row sm:items-center gap-2';
  div.setAttribute('data-calc', calc);
  div.setAttribute('data-pct', pct);

  var isEarning = (type === 'Earning');
  var isBasic = name.toLowerCase().startsWith('basic');

  div.innerHTML = `
    <div class="flex-1 min-w-[150px]">
      <input type="text" name="items[name][]" value="${name}" required placeholder="Component name"
             ${isBasic ? 'oninput="onBasicSalaryChanged()"' : ''}
             class="item-name-input w-full px-2 py-1.5 rounded text-xs border border-outline-variant bg-surface text-on-surface focus:border-primary font-medium"/>
      <input type="hidden" name="items[type][]" value="${type}"/>
      <input type="hidden" name="items[cid][]" value="${cid || ''}"/>
      <input type="hidden" name="items[calc][]" class="item-calc-input" value="${calc}"/>
      <input type="hidden" name="items[pct][]" class="item-pct-input" value="${pct}"/>
    </div>

    <!-- Calc Type Selector (Fixed vs % of Basic) -->
    <div class="w-36">
      <select onchange="onRowCalcChanged(this)" class="row-calc-select w-full px-2 py-1.5 rounded text-[11px] border border-outline-variant bg-surface text-on-surface focus:border-primary">
        <option value="Fixed" ${calc === 'Fixed' ? 'selected' : ''}>Fixed Amount</option>
        <option value="Percentage" ${calc === 'Percentage' ? 'selected' : ''}>% of Basic</option>
      </select>
    </div>

    <!-- Percentage field if Percentage -->
    <div class="w-20 ${calc === 'Percentage' ? '' : 'hidden'} pct-wrap">
      <div class="relative">
        <input type="number" step="0.01" value="${pct}" placeholder="%" oninput="onRowPctInput(this)"
               class="row-pct-field w-full pr-5 pl-2 py-1.5 rounded text-xs font-mono text-right border border-outline-variant bg-surface text-on-surface focus:border-primary"/>
        <span class="absolute right-1.5 top-1.5 text-[10px] text-on-surface-variant">%</span>
      </div>
    </div>

    ${!isEarning ? `
    <!-- Deduction Payer selector (Employee Deduction vs Employer Contribution) -->
    <div class="w-32">
      <select name="items[payer][]" onchange="recalcTotals()" class="item-payer-select w-full px-2 py-1.5 rounded text-[11px] border border-outline-variant bg-surface text-on-surface focus:border-primary">
        <option value="Employee" ${payer === 'Employee' ? 'selected' : ''}>Employee Ded.</option>
        <option value="Employer" ${payer === 'Employer' ? 'selected' : ''}>Employer Contrib.</option>
      </select>
    </div>
    ` : `
    <input type="hidden" name="items[payer][]" value="Employee"/>
    `}

    <div class="w-28">
      <div class="relative">
        <span class="absolute left-2 top-1.5 text-xs font-mono text-on-surface-variant">₹</span>
        <input type="number" step="0.01" name="items[amount][]" value="${parseFloat(amt || 0).toFixed(2)}" required
               oninput="${isBasic ? 'onBasicSalaryChanged();' : ''} recalcTotals();"
               class="item-amount-input w-full pl-6 pr-2 py-1.5 rounded text-xs font-mono text-right border border-outline-variant bg-surface text-on-surface focus:border-primary"/>
      </div>
    </div>

    <button type="button" onclick="this.closest('.item-row').remove(); recalcTotals();" class="text-on-surface-variant hover:text-error p-1 rounded transition-colors cursor-pointer shrink-0" title="Remove line">
      <span class="material-symbols-outlined text-[18px]">remove_circle_outline</span>
    </button>
  `;

  container.appendChild(div);

  // If initial calculation is percentage and basic is available, compute amount immediately
  if (calc === 'Percentage' && pct > 0) {
    var basic = getBasicSalaryValue();
    if (basic > 0) {
      div.querySelector('.item-amount-input').value = ((basic * pct) / 100).toFixed(2);
    }
  }

  recalcTotals();
}

function onRowCalcChanged(sel) {
  var row = sel.closest('.item-row');
  var calcVal = sel.value;
  row.setAttribute('data-calc', calcVal);
  row.querySelector('.item-calc-input').value = calcVal;

  var pctWrap = row.querySelector('.pct-wrap');
  if (pctWrap) {
    pctWrap.classList.toggle('hidden', calcVal !== 'Percentage');
  }

  if (calcVal === 'Percentage') {
    var pctField = row.querySelector('.row-pct-field');
    var pct = parseFloat(pctField ? pctField.value : 0) || 0;
    var basic = getBasicSalaryValue();
    var amt = (basic * pct) / 100;
    row.querySelector('.item-amount-input').value = amt.toFixed(2);
  }
  recalcTotals();
}

function onRowPctInput(inp) {
  var row = inp.closest('.item-row');
  var pct = parseFloat(inp.value || 0) || 0;
  row.setAttribute('data-pct', pct);
  row.querySelector('.item-pct-input').value = pct;

  var basic = getBasicSalaryValue();
  var amt = (basic * pct) / 100;
  row.querySelector('.item-amount-input').value = amt.toFixed(2);
  recalcTotals();
}

function recalcTotals() {
  var gross = 0;
  var employeeDeductions = 0;
  var employerContributions = 0;

  var earnInputs = document.querySelectorAll('#earningsContainer .item-amount-input');
  earnInputs.forEach(function(inp) {
    gross += parseFloat(inp.value) || 0;
  });

  var dedRows = document.querySelectorAll('#deductionsContainer .item-row');
  dedRows.forEach(function(row) {
    var amtInp = row.querySelector('.item-amount-input');
    var payerSel = row.querySelector('.item-payer-select');
    var amt = parseFloat(amtInp ? amtInp.value : 0) || 0;
    var payer = payerSel ? payerSel.value : 'Employee';

    if (payer === 'Employer') {
      employerContributions += amt;
    } else {
      employeeDeductions += amt;
    }
  });

  var net = gross - employeeDeductions;

  document.getElementById('lblGrossEarnings').textContent = '₹' + gross.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  document.getElementById('lblTotalDeductions').textContent = '₹' + (employeeDeductions + employerContributions).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  document.getElementById('lblNetSalary').textContent = '₹' + net.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' / month';

  document.getElementById('summaryGross').textContent = '₹' + gross.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  document.getElementById('summaryDeductions').textContent = '₹' + employeeDeductions.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  document.getElementById('summaryEmployer').textContent = '₹' + employerContributions.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function loadItemsIntoForm(items) {
  document.getElementById('earningsContainer').innerHTML = '';
  document.getElementById('deductionsContainer').innerHTML = '';

  items.forEach(function(itm) {
    addRow(itm.component_name, itm.component_type, itm.amount, itm.calculation_type, itm.percentage, itm.component_id, itm.deduction_payer);
  });
  recalcTotals();
}

function editStructure(id) {
  fetch('<?php echo site_url("finance/salary_structure_view_ajax/"); ?>' + id)
    .then(function(r) { return r.json(); })
    .then(function(res) {
      if (res.success && res.structure) {
        var s = res.structure;
        document.getElementById('struct_id').value = s.id;
        document.getElementById('struct_staff_id').value = s.staff_id;
        document.getElementById('struct_staff_id').disabled = false;
        document.getElementById('struct_name').value = s.structure_name;
        document.getElementById('struct_eff_from').value = s.effective_from;
        document.getElementById('struct_remarks').value = s.remarks || '';
        document.getElementById('struct_status').checked = (s.status == 1);
        document.getElementById('structModalTitle').textContent = 'Edit Salary Structure — ' + s.full_name;

        loadItemsIntoForm(res.items || []);
        document.getElementById('structureModal').classList.remove('hidden');
      }
    });
}

function viewStructure(id) {
  fetch('<?php echo site_url("finance/salary_structure_view_ajax/"); ?>' + id)
    .then(function(r) { return r.json(); })
    .then(function(res) {
      if (res.success && res.structure) {
        var s = res.structure;
        var items = res.items || [];
        var earnItems = items.filter(function(i) { return i.component_type === 'Earning'; });
        var dedItems = items.filter(function(i) { return i.component_type === 'Deduction'; });
        var empDedItems = dedItems.filter(function(i) { return (i.deduction_payer || 'Employee') === 'Employee'; });
        var empyrItems = dedItems.filter(function(i) { return i.deduction_payer === 'Employer'; });

        document.getElementById('viewModalTitle').textContent = s.structure_name;
        document.getElementById('viewModalSubtitle').textContent = s.full_name + ' (' + s.employee_code + ') — ' + (s.designation_name || 'Staff');

        var html = `
          <div class="p-4 rounded-xl bg-surface-container-low mb-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div>
              <span class="text-[11px] text-on-surface-variant uppercase font-semibold block">Staff Information</span>
              <strong class="text-sm text-on-surface">${s.full_name}</strong>
              <div class="text-xs text-on-surface-variant font-mono">${s.employee_code} • ${s.staff_type === 'teacher' ? 'Teaching' : 'Non-Teaching'}</div>
            </div>
            <div class="text-left sm:text-right">
              <span class="text-[11px] text-on-surface-variant uppercase font-semibold block">Net Take-Home Salary</span>
              <strong class="text-2xl font-mono text-emerald-700">₹${parseFloat(s.net_salary).toLocaleString('en-IN', {minimumFractionDigits: 2})}</strong>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="p-3.5 rounded-xl border border-emerald-200 bg-emerald-50/20">
              <h4 class="text-xs font-bold text-emerald-900 uppercase tracking-wider mb-2 flex items-center justify-between">
                <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">add_circle</span> Earnings</span>
                <span class="font-mono text-emerald-800">₹${parseFloat(s.gross_salary).toFixed(2)}</span>
              </h4>
              <div class="space-y-1.5 text-xs">
                ${earnItems.map(function(i) {
                  var calcBadge = i.calculation_type === 'Percentage' ? ' <span class="text-[10px] text-on-surface-variant">(' + parseFloat(i.percentage) + '% of Basic)</span>' : '';
                  return '<div class="flex justify-between py-1 border-b border-emerald-100"><span class="text-on-surface">' + i.component_name + calcBadge + '</span><span class="font-mono font-semibold text-on-surface">₹' + parseFloat(i.amount).toFixed(2) + '</span></div>';
                }).join('')}
              </div>
            </div>

            <div class="p-3.5 rounded-xl border border-amber-200 bg-amber-50/20">
              <h4 class="text-xs font-bold text-amber-900 uppercase tracking-wider mb-2 flex items-center justify-between">
                <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">remove_circle</span> Employee Deductions</span>
                <span class="font-mono text-amber-800">₹${parseFloat(s.total_deductions).toFixed(2)}</span>
              </h4>
              <div class="space-y-1.5 text-xs">
                ${empDedItems.length > 0 ? empDedItems.map(function(i) {
                  var calcBadge = i.calculation_type === 'Percentage' ? ' <span class="text-[10px] text-on-surface-variant">(' + parseFloat(i.percentage) + '% of Basic)</span>' : '';
                  return '<div class="flex justify-between py-1 border-b border-amber-100"><span class="text-on-surface">' + i.component_name + calcBadge + '</span><span class="font-mono font-semibold text-amber-800">₹' + parseFloat(i.amount).toFixed(2) + '</span></div>';
                }).join('') : '<div class="text-on-surface-variant italic py-1">No employee deductions.</div>'}
              </div>

              ${empyrItems.length > 0 ? `
              <h4 class="text-xs font-bold text-purple-900 uppercase tracking-wider mt-3 mb-1 pt-2 border-t border-purple-200 flex items-center justify-between">
                <span>Employer Contributions</span>
                <span class="font-mono text-purple-800">₹${parseFloat(s.employer_contributions || 0).toFixed(2)}</span>
              </h4>
              <div class="space-y-1 text-xs">
                ${empyrItems.map(function(i) {
                  var calcBadge = i.calculation_type === 'Percentage' ? ' <span class="text-[10px] text-on-surface-variant">(' + parseFloat(i.percentage) + '% of Basic)</span>' : '';
                  return '<div class="flex justify-between py-0.5 border-b border-purple-100"><span class="text-on-surface">' + i.component_name + calcBadge + '</span><span class="font-mono text-purple-700 font-semibold">₹' + parseFloat(i.amount).toFixed(2) + '</span></div>';
                }).join('')}
              </div>
              ` : ''}
            </div>
          </div>

          <div class="text-[12px] text-on-surface-variant pt-2 flex items-center justify-between">
            <span>Effective from: <strong>${s.effective_from}</strong></span>
            <span>Status: <strong class="${s.status == 1 ? 'text-emerald-700' : 'text-on-surface-variant'}">${s.status == 1 ? 'Active' : 'Inactive'}</strong></span>
          </div>
        `;

        document.getElementById('viewBreakdownContent').innerHTML = html;
        document.getElementById('viewModal').classList.remove('hidden');
      }
    });
}

function closeViewModal() {
  document.getElementById('viewModal').classList.add('hidden');
}

function openComponentModal() {
  document.getElementById('comp_id').value = '';
  document.getElementById('comp_name').value = '';
  document.getElementById('comp_code').value = '';
  document.getElementById('comp_type').value = 'Earning';
  document.getElementById('comp_deduction_payer').value = 'Employee';
  togglePayerField('Earning');
  document.getElementById('comp_calc').value = 'Fixed';
  toggleCalcUnit('Fixed');
  document.getElementById('comp_default_amt').value = '0.00';
  document.getElementById('comp_desc').value = '';
  document.getElementById('comp_taxable').checked = true;
  document.getElementById('comp_status').checked = true;
  document.getElementById('compModalTitle').textContent = 'Add Salary Component';
  document.getElementById('componentModal').classList.remove('hidden');
}

function editComponent(c) {
  document.getElementById('comp_id').value = c.id;
  document.getElementById('comp_name').value = c.component_name;
  document.getElementById('comp_code').value = c.component_code;
  document.getElementById('comp_type').value = c.component_type;
  togglePayerField(c.component_type);
  if (c.deduction_payer) {
    document.getElementById('comp_deduction_payer').value = c.deduction_payer;
  }
  document.getElementById('comp_calc').value = c.calculation_type;
  toggleCalcUnit(c.calculation_type);
  if (c.calculation_type === 'Percentage') {
    document.getElementById('comp_default_amt').value = c.percentage_value || 0;
  } else {
    document.getElementById('comp_default_amt').value = c.default_amount || 0;
  }
  document.getElementById('comp_desc').value = c.description || '';
  document.getElementById('comp_taxable').checked = (c.is_taxable == 1);
  document.getElementById('comp_status').checked = (c.status == 1);
  document.getElementById('compModalTitle').textContent = 'Edit Salary Component';
  document.getElementById('componentModal').classList.remove('hidden');
}

function closeComponentModal() {
  document.getElementById('componentModal').classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', function() {
  var tableS = document.getElementById('structures-table');
  if (tableS && typeof $.fn.DataTable !== 'undefined') {
    $(tableS).DataTable({
      responsive: true,
      pageLength: 25,
      order: [[1, 'asc']],
      language: {
        search: "Quick search:",
        lengthMenu: "Show _MENU_ per page",
        info: "_START_ – _END_ of _TOTAL_ structures",
        paginate: { next: 'Next →', previous: '← Prev' }
      },
      columnDefs: [{ orderable: false, targets: [0, 8] }]
    });
  }

  var tableC = document.getElementById('components-table');
  if (tableC && typeof $.fn.DataTable !== 'undefined') {
    $(tableC).DataTable({
      responsive: true,
      pageLength: 25,
      order: [[3, 'asc'], [1, 'asc']],
      columnDefs: [{ orderable: false, targets: [0, 8] }]
    });
  }
});
</script>
