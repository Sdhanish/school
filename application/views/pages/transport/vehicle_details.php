<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <div class="flex items-center gap-2">
          <h2 class="font-headline-md text-headline-md text-on-surface"><?php echo html_escape($vehicle->vehicle_number); ?></h2>
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold font-mono bg-primary-container text-on-primary-container">
            <?php echo html_escape($vehicle->registration_number); ?>
          </span>
        </div>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">
          Type: <strong><?php echo html_escape($vehicle->vehicle_type); ?></strong> • Capacity: <strong><?php echo $vehicle->seating_capacity; ?> Seats</strong> (<?php echo $vehicle->occupied_seats; ?> Occupied)
        </p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('transport/vehicles'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>Back to Vehicles
        </a>
      </div>
    </div>

    <!-- Quick Stats Metric Bar -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-1">
        <span class="text-xs font-semibold text-on-surface-variant uppercase">Seating Capacity</span>
        <div class="text-title-lg font-bold text-on-surface"><?php echo $vehicle->seating_capacity; ?> seats</div>
      </div>
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-1">
        <span class="text-xs font-semibold text-on-surface-variant uppercase">Students Assigned</span>
        <div class="text-title-lg font-bold text-secondary"><?php echo $vehicle->occupied_seats; ?></div>
      </div>
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-1">
        <span class="text-xs font-semibold text-on-surface-variant uppercase">Available Free Seats</span>
        <div class="text-title-lg font-bold text-primary"><?php echo $vehicle->available_seats; ?></div>
      </div>
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-1">
        <span class="text-xs font-semibold text-on-surface-variant uppercase">Total Maintenance Cost</span>
        <div class="text-title-lg font-bold text-on-surface font-mono">₹<?php echo number_format($total_maintenance_cost, 2); ?></div>
      </div>
    </div>

    <!-- 2 Column Profile Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
      
      <!-- Left 1 Col: Vehicle Specs & Driver Info -->
      <div class="space-y-6">
        <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2 pb-2 border-b border-outline-variant/50">
            <span class="material-symbols-outlined text-primary text-[22px]">directions_bus</span>Vehicle Specs
          </h3>
          <div class="space-y-3 text-body-md">
            <div>
              <span class="text-xs text-on-surface-variant uppercase font-semibold block">Manufacturer & Model</span>
              <span class="text-on-surface font-medium"><?php echo html_escape($vehicle->manufacturer . ' ' . $vehicle->model); ?></span>
            </div>
            <div>
              <span class="text-xs text-on-surface-variant uppercase font-semibold block">Assigned Route</span>
              <span class="text-on-surface font-semibold text-primary"><?php echo html_escape($vehicle->route_name ?: 'Unassigned'); ?></span>
            </div>
            <div>
              <span class="text-xs text-on-surface-variant uppercase font-semibold block">Assigned Driver</span>
              <span class="text-on-surface font-medium"><?php echo html_escape($vehicle->driver_name ?: 'Unassigned'); ?></span>
              <span class="text-xs text-on-surface-variant block font-mono"><?php echo html_escape($vehicle->driver_phone); ?></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right 2 Cols: Assigned Students & Maintenance -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Students Manifest -->
        <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
          <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
            <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
              <span class="material-symbols-outlined text-secondary text-[22px]">group</span>Assigned Students (<?php echo count($students); ?>)
            </h3>
          </div>

          <div class="table-scroll overflow-x-auto max-h-80">
            <table class="w-full data-table zebra border-collapse text-body-md">
              <thead>
                <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
                  <th class="text-left px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase">Student</th>
                  <th class="text-left px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase">Class & Sec</th>
                  <th class="text-left px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase">Pickup Stop</th>
                  <th class="text-left px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase">Timing</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-outline-variant/40">
                <?php if (empty($students)): ?>
                  <tr><td colspan="4" class="px-4 py-6 text-center text-on-surface-variant text-xs">No students currently allocated to this vehicle.</td></tr>
                <?php else: ?>
                  <?php foreach ($students as $s): ?>
                    <tr class="hover:bg-surface-container-low transition-colors">
                      <td class="px-4 py-2.5 whitespace-nowrap font-medium text-on-surface">
                        <?php echo html_escape($s->first_name . ' ' . $s->last_name); ?>
                        <span class="text-[11px] text-on-surface-variant font-mono block"><?php echo html_escape($s->admission_number ?? $s->admission_no ?? ''); ?></span>
                      </td>
                      <td class="px-4 py-2.5 whitespace-nowrap text-[13px] text-on-surface">
                        <?php echo html_escape($s->class_name . ' - ' . $s->section_name); ?>
                      </td>
                      <td class="px-4 py-2.5 whitespace-nowrap text-[13px] text-on-surface">
                        <?php echo html_escape($s->pickup_stop_name); ?>
                      </td>
                      <td class="px-4 py-2.5 whitespace-nowrap font-mono text-[12px] text-on-surface-variant">
                        <?php echo html_escape($s->pickup_time); ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Maintenance History -->
        <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
          <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
            <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
              <span class="material-symbols-outlined text-amber-600 text-[22px]">build</span>Service & Maintenance History
            </h3>
          </div>

          <div class="divide-y divide-outline-variant/40">
            <?php if (empty($maintenance)): ?>
              <div class="p-6 text-center text-on-surface-variant text-xs">No service records logged for this vehicle.</div>
            <?php else: ?>
              <?php foreach ($maintenance as $m): ?>
                <div class="p-4 flex items-center justify-between gap-3">
                  <div class="space-y-1">
                    <strong class="text-body-md text-on-surface"><?php echo html_escape($m->maintenance_type); ?></strong>
                    <div class="text-[12px] text-on-surface-variant">
                      <span>Provider: <?php echo html_escape($m->service_provider); ?></span> • 
                      <span>Date: <?php echo date('d M Y', strtotime($m->service_date)); ?></span>
                    </div>
                    <p class="text-[12px] text-on-surface-variant italic">"<?php echo html_escape($m->description); ?>"</p>
                  </div>
                  <div class="text-right shrink-0">
                    <span class="text-body-md font-bold text-on-surface font-mono">₹<?php echo number_format($m->cost, 2); ?></span>
                    <span class="block text-[11px] text-secondary font-semibold"><?php echo html_escape($m->status); ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
