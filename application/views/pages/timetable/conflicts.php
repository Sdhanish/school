<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('success')): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-secondary-container text-on-secondary-container text-body-md font-medium flex items-center gap-2 border border-secondary/20">
        <span class="material-symbols-outlined text-[20px] text-secondary">check_circle</span>
        <?php echo html_escape($this->session->flashdata('success')); ?>
      </div>
    <?php endif; ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Timetable Conflict Analyzer</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Automated validation engine detecting teacher double-booking, class period collisions, and workload clashes.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('timetable/builder'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">edit_calendar</span>Open Builder
        </a>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('timetable/conflicts'); ?>" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Academic Year</label>
          <select name="academic_year_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <?php foreach ($academic_years as $ay): ?>
              <option value="<?php echo $ay->academic_year_id; ?>" <?php echo ($selected_year == $ay->academic_year_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($ay->year_name); ?><?php echo (!empty($ay->is_active)) ? ' (Active)' : ''; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </form>
    </div>

    <!-- Conflicts Audit Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Detected Conflicts (<?php echo count($conflicts); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Severity</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Conflict Type</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Slot</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Collision Details & Impact</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($conflicts)): ?>
              <tr>
                <td colspan="5" class="px-4 py-12 text-center text-on-surface-variant">
                  <span class="material-symbols-outlined text-[48px] text-secondary mb-2">verified</span>
                  <h4 class="text-title-md font-bold text-on-surface">Zero Conflicts Detected</h4>
                  <p class="text-body-md text-on-surface-variant mt-1">All teacher schedules and class periods across the institution are conflict-free.</p>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($conflicts as $c): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-error-container text-on-error-container">
                      <?php echo html_escape($c->severity); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 font-bold text-on-surface whitespace-nowrap">
                    <?php echo html_escape($c->type); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[13px] text-primary">
                    <?php echo html_escape($c->day . ' — ' . $c->period_name); ?>
                  </td>
                  <td class="px-4 py-3 text-on-surface text-body-md">
                    <?php echo html_escape($c->description); ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <a href="<?php echo site_url('timetable/builder'); ?>" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-secondary text-on-secondary text-[12px] font-semibold hover:bg-on-secondary-fixed-variant transition-colors">
                      <span class="material-symbols-outlined text-[16px]">build</span>Resolve in Builder
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
