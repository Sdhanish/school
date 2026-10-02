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

    <!-- Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Transport Management Dashboard</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Fleet operations, bus routes, driver assignments, student transport allocations, and maintenance compliance.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('transport/assignments'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">group_add</span>Allocations
        </a>
        <a href="<?php echo site_url('transport/vehicles'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">directions_bus</span>Manage Fleet
        </a>
      </div>
    </div>

    <!-- STATS CARDS GRID -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <!-- Total Vehicles -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Fleet Vehicles</span>
          <span class="material-symbols-outlined text-primary text-[22px]">directions_bus</span>
        </div>
        <div class="text-headline-md font-bold text-on-surface"><?php echo $stats->total_vehicles; ?></div>
        <div class="text-xs text-on-surface-variant flex items-center gap-1">
          <span class="font-semibold text-secondary"><?php echo $stats->active_vehicles; ?> Active</span> • <span class="font-semibold text-amber-600"><?php echo $stats->vehicles_maintenance; ?> Maintenance</span>
        </div>
      </div>

      <!-- Students Transported -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Students Transported</span>
          <span class="material-symbols-outlined text-secondary text-[22px]">school</span>
        </div>
        <div class="text-headline-md font-bold text-secondary"><?php echo $stats->students_using_transport; ?></div>
        <div class="text-xs text-on-surface-variant">
          Total Seating Capacity: <strong><?php echo $stats->total_capacity; ?> seats</strong>
        </div>
      </div>

      <!-- Active Routes & Stops -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Routes & Stops</span>
          <span class="material-symbols-outlined text-primary text-[22px]">alt_route</span>
        </div>
        <div class="text-headline-md font-bold text-primary"><?php echo $stats->active_routes; ?> Routes</div>
        <div class="text-xs text-on-surface-variant">
          Serving <strong><?php echo $stats->total_stops; ?> Designated Stops</strong>
        </div>
      </div>

      <!-- Total Drivers -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Registered Drivers</span>
          <span class="material-symbols-outlined text-amber-600 text-[22px]">badge</span>
        </div>
        <div class="text-headline-md font-bold text-on-surface"><?php echo $stats->total_drivers; ?></div>
        <div class="text-xs text-on-surface-variant">
          Heavy & passenger licensed staff
        </div>
      </div>
    </div>

    <!-- Fleet Occupancy & Service Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
      
      <!-- Left 2 Cols: Vehicle Fleet & Capacity Occupancy -->
      <div class="lg:col-span-2 p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[22px]">directions_bus</span>Vehicle Capacity & Live Occupancy
          </h3>
          <a href="<?php echo site_url('transport/vehicles'); ?>" class="text-body-md text-primary hover:underline font-semibold text-xs">View All Vehicles</a>
        </div>

        <div class="divide-y divide-outline-variant/40">
          <?php if (empty($vehicles)): ?>
            <div class="py-6 text-center text-on-surface-variant text-body-md">No vehicles registered in fleet.</div>
          <?php else: ?>
            <?php foreach ($vehicles as $v): ?>
              <?php
                $pct = (int)$v->occupancy_rate;
                $barColor = ($pct >= 90) ? 'bg-error' : (($pct >= 70) ? 'bg-amber-500' : 'bg-secondary');
              ?>
              <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="space-y-1">
                  <div class="flex items-center gap-2">
                    <strong class="text-body-md text-on-surface"><?php echo html_escape($v->vehicle_number); ?></strong>
                    <span class="px-2 py-0.2 rounded text-[11px] font-mono font-bold bg-surface-container-high text-on-surface-variant"><?php echo html_escape($v->registration_number); ?></span>
                    <span class="px-2 py-0.2 rounded text-[10px] font-semibold bg-primary-container text-on-primary-container"><?php echo html_escape($v->vehicle_type); ?></span>
                  </div>
                  <div class="text-[12px] text-on-surface-variant">
                    <span>Route: <strong><?php echo html_escape($v->route_name ?: 'Unassigned'); ?></strong></span> • 
                    <span>Driver: <strong><?php echo html_escape($v->driver_name ?: 'Unassigned'); ?></strong></span>
                  </div>
                </div>

                <div class="sm:w-48 space-y-1">
                  <div class="flex items-center justify-between text-xs text-on-surface font-semibold">
                    <span><?php echo $v->occupied_seats; ?> / <?php echo $v->seating_capacity; ?> seats</span>
                    <span><?php echo $pct; ?>%</span>
                  </div>
                  <div class="w-full h-2 rounded-full bg-surface-container-high overflow-hidden">
                    <div class="h-full rounded-full <?php echo $barColor; ?>" style="width: <?php echo min(100, $pct); ?>%"></div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Right 1 Col: Maintenance & Compliance Alerts -->
      <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-[22px]">build</span>Recent Maintenance
          </h3>
          <a href="<?php echo site_url('transport/maintenance'); ?>" class="text-body-md text-primary hover:underline font-semibold text-xs">View Logs</a>
        </div>

        <div class="divide-y divide-outline-variant/40">
          <?php if (empty($recent_maintenance)): ?>
            <div class="py-6 text-center text-on-surface-variant text-body-md">No recent service records.</div>
          <?php else: ?>
            <?php foreach (array_slice($recent_maintenance, 0, 4) as $m): ?>
              <div class="py-3 space-y-1">
                <div class="flex items-center justify-between">
                  <strong class="text-body-md text-on-surface"><?php echo html_escape($m->vehicle_number); ?></strong>
                  <span class="text-xs font-bold text-on-surface font-mono">₹<?php echo number_format($m->cost, 2); ?></span>
                </div>
                <div class="text-[12px] text-on-surface-variant flex items-center justify-between">
                  <span><?php echo html_escape($m->maintenance_type); ?></span>
                  <span class="font-mono text-[11px]"><?php echo date('d M Y', strtotime($m->service_date)); ?></span>
                </div>
                <p class="text-[11px] text-on-surface-variant line-clamp-1 italic">"<?php echo html_escape($m->description); ?>"</p>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
