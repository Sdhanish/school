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
        <h2 class="font-headline-md text-headline-md text-on-surface">Fleet & Vehicle Management</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Manage school buses, vans, seating capacity, driver assignments, and compliance documents.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="document.getElementById('vehicleModal').showModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Register Vehicle
        </button>
      </div>
    </div>

    <!-- Vehicles Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Registered Vehicles (<?php echo count($vehicles); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Vehicle</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Registration No</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Type & Model</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Capacity & Occupancy</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Assigned Driver</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Assigned Route</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($vehicles)): ?>
              <tr><td colspan="8" class="px-4 py-8 text-center text-on-surface-variant">No vehicles found. Click 'Register Vehicle' to add one.</td></tr>
            <?php else: ?>
              <?php foreach ($vehicles as $v): ?>
                <?php
                  $stBadge = ($v->status === 'Active') ? 'bg-secondary-container text-on-secondary-container' : (($v->status === 'Maintenance') ? 'bg-amber-100 text-amber-900' : 'bg-surface-container-high text-on-surface-variant');
                ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap">
                    <strong class="text-on-surface block"><?php echo html_escape($v->vehicle_number); ?></strong>
                    <span class="text-[11px] text-on-surface-variant"><?php echo html_escape($v->manufacturer . ' ' . $v->model); ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono font-bold text-on-surface text-[13px]">
                    <?php echo html_escape($v->registration_number); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-surface-container-high text-on-surface-variant">
                      <?php echo html_escape($v->vehicle_type); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="font-bold text-on-surface"><?php echo $v->occupied_seats; ?></span> / <span class="text-on-surface-variant"><?php echo $v->seating_capacity; ?></span>
                    <span class="text-[10px] text-on-surface-variant block font-mono"><?php echo $v->available_seats; ?> seats free</span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-[13px] text-on-surface">
                    <?php echo html_escape($v->driver_name ?: 'Unassigned'); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-[13px] text-on-surface font-medium">
                    <?php echo html_escape($v->route_name ?: 'Unassigned'); ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold <?php echo $stBadge; ?>">
                      <?php echo html_escape($v->status); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <div class="flex items-center justify-center gap-1">
                      <a href="<?php echo site_url('transport/vehicle_details/' . $v->vehicle_id); ?>" class="p-1 rounded hover:bg-surface-container-high text-primary font-semibold text-xs">
                        Details
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Register Vehicle Modal Dialog -->
    <dialog id="vehicleModal" class="p-0 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-3 w-full max-w-2xl backdrop:bg-scrim/40">
      <div class="p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface">Register New Vehicle</h3>
          <button onclick="document.getElementById('vehicleModal').close()" class="p-1 rounded-full hover:bg-surface-container-high text-on-surface-variant">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('transport/vehicles'); ?>
          <div class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Vehicle Name / Number *</label>
                <input type="text" name="vehicle_number" required placeholder="e.g. Bus 04" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Registration Number *</label>
                <input type="text" name="registration_number" required placeholder="e.g. KL-02-GH-3456" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md uppercase font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Vehicle Type *</label>
                <select name="vehicle_type" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <option value="School Bus">School Bus</option>
                  <option value="Mini Bus">Mini Bus</option>
                  <option value="Van">Van</option>
                  <option value="Other">Other</option>
                </select>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Seating Capacity *</label>
                <input type="number" name="seating_capacity" min="5" max="100" value="40" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Manufacturer</label>
                <input type="text" name="manufacturer" placeholder="Tata / Ashok Leyland" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Assign Driver</label>
                <select name="assigned_driver_id" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <option value="">-- No Driver Assigned --</option>
                  <?php foreach ($drivers as $d): ?>
                    <option value="<?php echo $d->driver_id; ?>"><?php echo html_escape($d->driver_name . ' (' . $d->license_number . ')'); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Assign Route</label>
                <select name="assigned_route_id" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <option value="">-- No Route Assigned --</option>
                  <?php foreach ($routes as $r): ?>
                    <option value="<?php echo $r->route_id; ?>"><?php echo html_escape($r->route_name . ' (' . $r->route_code . ')'); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-outline-variant/50">
              <button type="button" onclick="document.getElementById('vehicleModal').close()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-label-md">Cancel</button>
              <button type="submit" class="px-5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">Save Vehicle</button>
            </div>
          </div>
        <?php echo form_close(); ?>
      </div>
    </dialog>
