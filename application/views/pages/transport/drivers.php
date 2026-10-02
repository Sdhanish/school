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
        <h2 class="font-headline-md text-headline-md text-on-surface">Driver & Crew Management</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Manage transport drivers, commercial licenses, contact credentials, and vehicle assignments.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="document.getElementById('driverModal').showModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Register Driver
        </button>
      </div>
    </div>

    <!-- Drivers Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Registered Drivers (<?php echo count($drivers); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Driver Name</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">License Number</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">License Expiry</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Contact Phone</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Assigned Vehicle</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($drivers)): ?>
              <tr><td colspan="7" class="px-4 py-8 text-center text-on-surface-variant">No drivers registered.</td></tr>
            <?php else: ?>
              <?php foreach ($drivers as $d): ?>
                <?php
                  $licBadge = ($d->license_status === 'Valid') ? 'bg-secondary-container text-on-secondary-container' : (($d->license_status === 'Expiring Soon') ? 'bg-amber-100 text-amber-900' : 'bg-error-container text-error');
                ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap">
                    <strong class="text-on-surface block"><?php echo html_escape($d->driver_name); ?></strong>
                    <span class="text-[11px] text-on-surface-variant"><?php echo $d->experience_years; ?> yrs exp</span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono font-bold text-on-surface text-[13px]">
                    <?php echo html_escape($d->license_number); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span class="font-mono text-[12px] text-on-surface block"><?php echo date('d M Y', strtotime($d->license_expiry_date)); ?></span>
                    <span class="px-2 py-0.2 rounded text-[10px] font-bold <?php echo $licBadge; ?>"><?php echo $d->license_status; ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[13px] text-on-surface">
                    <?php echo html_escape($d->phone); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-[13px] text-on-surface font-medium">
                    <?php echo html_escape($d->vehicle_number ? $d->vehicle_number . ' (' . $d->registration_number . ')' : 'Unassigned'); ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-secondary-container text-on-secondary-container">
                      <?php echo html_escape($d->status); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <a href="<?php echo site_url('transport/driver_details/' . $d->driver_id); ?>" class="p-1 rounded hover:bg-surface-container-high text-primary font-semibold text-xs">
                      Profile
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Register Driver Modal Dialog -->
    <dialog id="driverModal" class="p-0 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-3 w-full max-w-lg backdrop:bg-scrim/40">
      <div class="p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface">Register Driver</h3>
          <button onclick="document.getElementById('driverModal').close()" class="p-1 rounded-full hover:bg-surface-container-high text-on-surface-variant">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('transport/drivers'); ?>
          <div class="space-y-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Driver Full Name *</label>
              <input type="text" name="driver_name" required placeholder="e.g. Ramesh Kumar" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Phone Number *</label>
                <input type="text" name="phone" required placeholder="9847XXXXXX" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Alternate Phone</label>
                <input type="text" name="alternate_phone" placeholder="Emergency phone" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">License Number *</label>
                <input type="text" name="license_number" required placeholder="KL-02-XXXX-XXXXXXX" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md uppercase font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">License Expiry *</label>
                <input type="date" name="license_expiry_date" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Assign Vehicle</label>
              <select name="assigned_vehicle_id" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="">-- No Vehicle Assigned --</option>
                <?php foreach ($vehicles as $v): ?>
                  <option value="<?php echo $v->vehicle_id; ?>"><?php echo html_escape($v->vehicle_number . ' (' . $v->registration_number . ')'); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-outline-variant/50">
              <button type="button" onclick="document.getElementById('driverModal').close()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-label-md">Cancel</button>
              <button type="submit" class="px-5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">Save Driver</button>
            </div>
          </div>
        <?php echo form_close(); ?>
      </div>
    </dialog>
