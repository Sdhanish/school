<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <div class="flex items-center gap-2">
          <h2 class="font-headline-md text-headline-md text-on-surface"><?php echo html_escape($driver->driver_name); ?></h2>
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold <?php echo ($driver->status === 'Active') ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container-high text-on-surface-variant'; ?>">
            <?php echo html_escape($driver->status); ?>
          </span>
        </div>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">
          License: <strong class="font-mono"><?php echo html_escape($driver->license_number); ?></strong> (<?php echo html_escape($driver->license_type); ?>)
        </p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('transport/drivers'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>Back to Drivers
        </a>
      </div>
    </div>

    <!-- Driver Info Cards Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
      
      <!-- Left 1 Col: Credentials -->
      <div class="space-y-6">
        <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2 pb-2 border-b border-outline-variant/50">
            <span class="material-symbols-outlined text-primary text-[22px]">badge</span>Driver Credentials
          </h3>

          <div class="space-y-3 text-body-md">
            <div>
              <span class="text-xs text-on-surface-variant uppercase font-semibold block">Contact Phone</span>
              <strong class="text-on-surface font-mono"><?php echo html_escape($driver->phone); ?></strong>
            </div>

            <div>
              <span class="text-xs text-on-surface-variant uppercase font-semibold block">Commercial License</span>
              <span class="text-on-surface font-mono font-bold"><?php echo html_escape($driver->license_number); ?></span>
              <span class="text-xs text-on-surface-variant block font-mono">
                Expires on: <?php echo date('d M Y', strtotime($driver->license_expiry_date)); ?> (<?php echo $driver->license_status; ?>)
              </span>
            </div>

            <div>
              <span class="text-xs text-on-surface-variant uppercase font-semibold block">Assigned Vehicle</span>
              <span class="text-on-surface font-medium"><?php echo html_escape($driver->vehicle_number ? $driver->vehicle_number . ' (' . $driver->registration_number . ')' : 'Unassigned'); ?></span>
            </div>

            <div>
              <span class="text-xs text-on-surface-variant uppercase font-semibold block">Assigned Route</span>
              <span class="text-on-surface font-semibold text-primary"><?php echo html_escape($driver->route_name ?: 'Unassigned'); ?></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right 2 Cols: Driver Documents -->
      <div class="lg:col-span-2 space-y-6">
        <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
          <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
            <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
              <span class="material-symbols-outlined text-secondary text-[22px]">folder</span>Compliance & Identification Documents
            </h3>
          </div>

          <div class="divide-y divide-outline-variant/40">
            <?php if (empty($documents)): ?>
              <div class="p-6 text-center text-on-surface-variant text-xs">No documents uploaded for this driver.</div>
            <?php else: ?>
              <?php foreach ($documents as $doc): ?>
                <div class="p-4 flex items-center justify-between gap-3">
                  <div class="space-y-1">
                    <strong class="text-body-md text-on-surface"><?php echo html_escape($doc->document_type); ?></strong>
                    <div class="text-[12px] text-on-surface-variant font-mono">
                      <span>Doc #: <?php echo html_escape($doc->document_number ?: 'N/A'); ?></span> • 
                      <span>Expires: <?php echo date('d M Y', strtotime($doc->expiry_date)); ?></span>
                    </div>
                  </div>
                  <div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-secondary-container text-on-secondary-container">
                      <?php echo html_escape($doc->status); ?>
                    </span>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
