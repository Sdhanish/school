<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <div class="flex items-center gap-2">
          <h2 class="font-headline-md text-headline-md text-on-surface"><?php echo html_escape($route->route_name); ?></h2>
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold font-mono bg-primary-container text-on-primary-container">
            <?php echo html_escape($route->route_code); ?>
          </span>
        </div>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">
          <?php echo html_escape($route->start_point); ?> → <?php echo html_escape($route->end_point); ?> (<?php echo $route->estimated_distance_km; ?> km • <?php echo $route->estimated_travel_time_min; ?> min)
        </p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('transport/routes'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>Back to Routes
        </a>
      </div>
    </div>

    <!-- 2 Column Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
      
      <!-- Left 1 Col: Route Assignment Specs -->
      <div class="space-y-6">
        <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2 pb-2 border-b border-outline-variant/50">
            <span class="material-symbols-outlined text-primary text-[22px]">info</span>Route Overview
          </h3>

          <div class="space-y-3 text-body-md">
            <div>
              <span class="text-xs text-on-surface-variant uppercase font-semibold block">Vehicle Assigned</span>
              <strong class="text-on-surface"><?php echo html_escape($route->vehicle_number ?: 'Unassigned'); ?></strong>
              <span class="text-xs text-on-surface-variant block font-mono"><?php echo html_escape($route->registration_number); ?> (<?php echo $route->seating_capacity; ?> seats)</span>
            </div>

            <div>
              <span class="text-xs text-on-surface-variant uppercase font-semibold block">Driver Assigned</span>
              <strong class="text-on-surface"><?php echo html_escape($route->driver_name ?: 'Unassigned'); ?></strong>
              <span class="text-xs text-on-surface-variant block font-mono"><?php echo html_escape($route->driver_phone); ?></span>
            </div>

            <div>
              <span class="text-xs text-on-surface-variant uppercase font-semibold block">Total Stops</span>
              <span class="text-on-surface font-bold text-primary font-mono text-title-md"><?php echo count($stops); ?> Designated Stops</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right 2 Cols: Ordered Stops Sequence -->
      <div class="lg:col-span-2 space-y-6">
        <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
          <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
            <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
              <span class="material-symbols-outlined text-secondary text-[22px]">pin_drop</span>Ordered Stop Itinerary
            </h3>
            <a href="<?php echo site_url('transport/stops?route_id=' . $route->route_id); ?>" class="text-xs text-primary font-semibold hover:underline">Manage Stops</a>
          </div>

          <div class="divide-y divide-outline-variant/40">
            <?php if (empty($stops)): ?>
              <div class="p-6 text-center text-on-surface-variant text-xs">No stops added to this route yet.</div>
            <?php else: ?>
              <?php foreach ($stops as $idx => $st): ?>
                <div class="p-4 flex items-center justify-between gap-3 hover:bg-surface-container-low transition-colors">
                  <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-full bg-primary-container text-on-primary-container text-xs font-bold flex items-center justify-center font-mono">
                      <?php echo $st->sequence_order; ?>
                    </span>
                    <div>
                      <strong class="text-body-md text-on-surface block"><?php echo html_escape($st->stop_name); ?></strong>
                      <span class="text-[12px] text-on-surface-variant"><?php echo html_escape($st->landmark ?: 'Main road stop'); ?></span>
                    </div>
                  </div>
                  <div class="text-right shrink-0 font-mono text-[12px]">
                    <span class="text-on-surface font-semibold block">Pickup: <?php echo html_escape($st->pickup_time); ?></span>
                    <span class="text-on-surface-variant text-[11px]">Drop: <?php echo html_escape($st->drop_time); ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
