<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Transport Reports & Fleet Analytics</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Passenger manifests, vehicle occupancy ratios, driver rosters, and CSV/Print exports.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('transport/reports?type=' . $type . '&export=csv'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">download</span>Export CSV
        </a>
        <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">print</span>Print Report
        </button>
      </div>
    </div>

    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 mb-6">
      <a href="<?php echo site_url('transport/reports?type=vehicle'); ?>" class="px-4 py-2 rounded-lg text-xs font-semibold <?php echo ($type === 'vehicle') ? 'bg-secondary text-on-secondary shadow-sm' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high'; ?>">
        Vehicle Capacity & Fleet
      </a>
      <a href="<?php echo site_url('transport/reports?type=student'); ?>" class="px-4 py-2 rounded-lg text-xs font-semibold <?php echo ($type === 'student') ? 'bg-secondary text-on-secondary shadow-sm' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high'; ?>">
        Student Passenger Manifest
      </a>
      <a href="<?php echo site_url('transport/reports?type=driver'); ?>" class="px-4 py-2 rounded-lg text-xs font-semibold <?php echo ($type === 'driver') ? 'bg-secondary text-on-secondary shadow-sm' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high'; ?>">
        Driver Rosters
      </a>
    </div>

    <!-- Reports Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Report Data</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <?php if ($type === 'student'): ?>
          <table class="w-full data-table zebra border-collapse text-body-md">
            <thead>
              <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Student</th>
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Class</th>
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Route</th>
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Pickup Stop</th>
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Vehicle</th>
                <th class="text-right px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Monthly Fee</th>
                <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/40">
              <?php foreach ($assignments as $a): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap font-medium text-on-surface">
                    <?php echo html_escape($a->first_name . ' ' . $a->last_name); ?>
                    <span class="text-[11px] text-on-surface-variant font-mono block"><?php echo html_escape($a->admission_number ?? $a->admission_no ?? ''); ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-[13px] text-on-surface"><?php echo html_escape($a->class_name . ' ' . $a->section_name); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-[13px] text-on-surface"><?php echo html_escape($a->route_name); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-[13px] text-on-surface"><?php echo html_escape($a->pickup_stop_name); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-[13px] text-on-surface"><?php echo html_escape($a->vehicle_number); ?></td>
                  <td class="px-4 py-3 text-right whitespace-nowrap font-mono font-bold text-on-surface">₹<?php echo number_format($a->monthly_fee, 2); ?></td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-secondary-container text-on-secondary-container"><?php echo $a->status; ?></span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <table class="w-full data-table zebra border-collapse text-body-md">
            <thead>
              <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Vehicle</th>
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Registration No</th>
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Type</th>
                <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Capacity</th>
                <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Occupied</th>
                <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Available</th>
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Assigned Driver</th>
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Route</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/40">
              <?php foreach ($vehicles as $v): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap font-bold text-on-surface"><?php echo html_escape($v->vehicle_number); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono font-semibold text-on-surface text-[13px]"><?php echo html_escape($v->registration_number); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap"><?php echo html_escape($v->vehicle_type); ?></td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-mono"><?php echo $v->seating_capacity; ?></td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-mono font-bold text-secondary"><?php echo $v->occupied_seats; ?></td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-mono font-bold text-primary"><?php echo $v->available_seats; ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-[13px] text-on-surface"><?php echo html_escape($v->driver_name ?: 'Unassigned'); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-[13px] text-on-surface"><?php echo html_escape($v->route_name ?: 'Unassigned'); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
