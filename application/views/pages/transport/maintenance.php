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
        <h2 class="font-headline-md text-headline-md text-on-surface">Vehicle Maintenance & Service Logs</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Track scheduled servicing, tyre replacements, engine overhauls, and total maintenance expenditures.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('transport/maintenance_history'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">history</span>History Ledger
        </a>
        <button onclick="document.getElementById('serviceModal').showModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Log Service Record
        </button>
      </div>
    </div>

    <!-- Maintenance Records Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Service Logs (<?php echo count($records); ?>)</span>
        <span class="text-xs text-on-surface-variant font-mono font-bold">Total Fleet Cost: ₹<?php echo number_format($total_cost, 2); ?></span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Vehicle</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Maintenance Type</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Service Date</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Next Due Date</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Service Provider</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Description</th>
              <th class="text-right px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Cost</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($records)): ?>
              <tr><td colspan="8" class="px-4 py-8 text-center text-on-surface-variant">No maintenance logs recorded.</td></tr>
            <?php else: ?>
              <?php foreach ($records as $m): ?>
                <?php
                  $stBadge = ($m->status === 'Completed') ? 'bg-secondary-container text-on-secondary-container' : (($m->status === 'Scheduled') ? 'bg-primary-container text-on-primary-container' : 'bg-error-container text-error');
                ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap">
                    <strong class="text-on-surface block"><?php echo html_escape($m->vehicle_number); ?></strong>
                    <span class="text-[11px] text-on-surface-variant font-mono"><?php echo html_escape($m->registration_number); ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-surface-container-high text-on-surface-variant">
                      <?php echo html_escape($m->maintenance_type); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[12px] text-on-surface">
                    <?php echo date('d M Y', strtotime($m->service_date)); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[12px] text-on-surface">
                    <?php echo date('d M Y', strtotime($m->next_service_date)); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-[13px] text-on-surface">
                    <?php echo html_escape($m->service_provider); ?>
                  </td>
                  <td class="px-4 py-3 text-[12px] text-on-surface-variant max-w-[200px] truncate">
                    <?php echo html_escape($m->description); ?>
                  </td>
                  <td class="px-4 py-3 text-right whitespace-nowrap font-mono font-bold text-on-surface">
                    ₹<?php echo number_format($m->cost, 2); ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold <?php echo $stBadge; ?>">
                      <?php echo html_escape($m->status); ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Log Service Modal Dialog -->
    <dialog id="serviceModal" class="p-0 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-3 w-full max-w-lg backdrop:bg-scrim/40">
      <div class="p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface">Log Vehicle Service</h3>
          <button onclick="document.getElementById('serviceModal').close()" class="p-1 rounded-full hover:bg-surface-container-high text-on-surface-variant">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('transport/maintenance'); ?>
          <div class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Select Vehicle *</label>
                <select name="vehicle_id" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <?php foreach ($vehicles as $v): ?>
                    <option value="<?php echo $v->vehicle_id; ?>"><?php echo html_escape($v->vehicle_number . ' (' . $v->registration_number . ')'); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Maintenance Type *</label>
                <select name="maintenance_type" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <option value="Routine Service">Routine Service</option>
                  <option value="Tyres">Tyres & Alignment</option>
                  <option value="Brake">Brakes & Suspension</option>
                  <option value="Engine">Engine Overhaul</option>
                  <option value="Electrical">Electrical & Battery</option>
                  <option value="Repair">General Repair</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Service Date *</label>
                <input type="date" name="service_date" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Next Due Date *</label>
                <input type="date" name="next_service_date" value="<?php echo date('Y-m-d', strtotime('+3 months')); ?>" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Service Provider *</label>
                <input type="text" name="service_provider" required placeholder="Authorized Service Centre" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Total Cost (₹) *</label>
                <input type="number" step="100" name="cost" required placeholder="8500" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Description *</label>
              <textarea name="description" rows="2" required placeholder="Details of repair or replacement..." class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-outline-variant/50">
              <button type="button" onclick="document.getElementById('serviceModal').close()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-label-md">Cancel</button>
              <button type="submit" class="px-5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">Save Service Record</button>
            </div>
          </div>
        <?php echo form_close(); ?>
      </div>
    </dialog>
