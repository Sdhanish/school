<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <div class="flex items-center gap-2">
          <h2 class="font-headline-md text-headline-md text-on-surface">Leave Application #<?php echo $app->application_id; ?></h2>
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold <?php echo ($app->status === 'Approved') ? 'bg-secondary-container text-on-secondary-container' : (($app->status === 'Pending') ? 'bg-amber-100 text-amber-900' : 'bg-error-container text-error'); ?>">
            <?php echo html_escape($app->status); ?>
          </span>
        </div>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">
          Applied on: <?php echo date('d M Y', strtotime($app->applied_date)); ?> • Category: <strong><?php echo $app->applicant_type; ?></strong>
        </p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('leave/history'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>Back to History
        </a>
      </div>
    </div>

    <!-- 2 Column Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
      
      <!-- Left 2 Cols: Details -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Applicant & Schedule Card -->
        <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2 pb-2 border-b border-outline-variant/50">
            <span class="material-symbols-outlined text-primary text-[22px]">info</span>Applicant & Leave Overview
          </h3>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-body-md">
            <div>
              <span class="text-xs text-on-surface-variant uppercase font-semibold block">Applicant Name</span>
              <strong class="text-on-surface text-title-md">
                <?php echo html_escape(($app->applicant_type === 'Student') ? $app->first_name . ' ' . $app->last_name : $app->staff_name); ?>
              </strong>
            </div>

            <div>
              <span class="text-xs text-on-surface-variant uppercase font-semibold block">Scope / Allocation</span>
              <span class="text-on-surface font-medium">
                <?php echo html_escape(($app->applicant_type === 'Student') ? $app->class_name . ' - ' . $app->section_name : ($app->designation_name ?: 'Faculty')); ?>
              </span>
            </div>

            <div>
              <span class="text-xs text-on-surface-variant uppercase font-semibold block">Leave Category</span>
              <span class="text-on-surface font-semibold"><?php echo html_escape($app->type_name); ?> (<?php echo html_escape($app->type_code); ?>)</span>
            </div>

            <div>
              <span class="text-xs text-on-surface-variant uppercase font-semibold block">Duration & Dates</span>
              <span class="text-on-surface font-bold text-primary">
                <?php echo $app->duration_days; ?> Working Day(s) 
                <?php if ($app->is_half_day): ?>(<?php echo $app->half_day_type; ?>)<?php endif; ?>
              </span>
              <span class="text-xs text-on-surface-variant block font-mono">
                <?php echo date('d M Y', strtotime($app->from_date)); ?> to <?php echo date('d M Y', strtotime($app->to_date)); ?>
              </span>
            </div>
          </div>

          <div class="pt-3 border-t border-outline-variant/40">
            <span class="text-xs text-on-surface-variant uppercase font-semibold block mb-1">Stated Reason:</span>
            <div class="p-3.5 rounded-xl bg-surface-container-low text-on-surface text-[14px] leading-relaxed">
              <?php echo html_escape($app->reason); ?>
            </div>
          </div>

          <?php if ($app->attachment): ?>
            <div class="pt-3 border-t border-outline-variant/40">
              <span class="text-xs text-on-surface-variant uppercase font-semibold block mb-2">Supporting Attachment:</span>
              <a href="<?php echo base_url('uploads/leaves/' . $app->attachment); ?>" target="_blank" class="inline-flex items-center gap-2 p-3 rounded-xl bg-surface-container-low border border-outline-variant/40 hover:bg-surface-container transition-colors">
                <span class="material-symbols-outlined text-primary text-[22px]">attach_file</span>
                <span class="font-bold text-on-surface text-[13px]"><?php echo html_escape($app->attachment); ?></span>
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Right 1 Col: Decision / Timeline -->
      <div class="space-y-6">
        <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2 pb-2 border-b border-outline-variant/50">
            <span class="material-symbols-outlined text-secondary text-[22px]">history</span>Application Timeline
          </h3>

          <div class="space-y-3">
            <?php if (empty($history)): ?>
              <div class="text-xs text-on-surface-variant">No timeline events recorded.</div>
            <?php else: ?>
              <?php foreach ($history as $h): ?>
                <div class="p-3 rounded-xl bg-surface-container-low border border-outline-variant/30 space-y-1">
                  <div class="flex items-center justify-between">
                    <strong class="text-on-surface text-xs font-bold"><?php echo html_escape($h->action); ?></strong>
                    <span class="text-[10px] font-mono text-on-surface-variant"><?php echo date('d M, h:i A', strtotime($h->created_at)); ?></span>
                  </div>
                  <p class="text-[11px] text-on-surface-variant"><?php echo html_escape($h->comments); ?></p>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
