<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <div class="flex items-center gap-2">
          <h2 class="font-headline-md text-headline-md text-on-surface"><?php echo html_escape($announcement->title); ?></h2>
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold <?php echo ($announcement->priority === 'Urgent') ? 'bg-error-container text-error' : 'bg-primary-container text-on-primary-container'; ?>">
            <?php echo html_escape($announcement->priority); ?>
          </span>
        </div>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">
          Posted by: <strong><?php echo html_escape($announcement->posted_by ?: 'Principal'); ?></strong> • Published: <?php echo date('d M Y', strtotime($announcement->announcement_date)); ?>
        </p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('communication/announcements'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>Back to Announcements
        </a>
      </div>
    </div>

    <!-- Announcement Body -->
    <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-6 mb-6">
      <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
        <div class="flex items-center gap-3 text-xs text-on-surface-variant">
          <span>Audience: <strong class="text-on-surface"><?php echo html_escape($announcement->audience); ?></strong></span>
          <span>•</span>
          <span>Category: <strong class="text-on-surface"><?php echo html_escape($announcement->category ?: 'General'); ?></strong></span>
        </div>
      </div>

      <div class="text-body-md text-on-surface leading-relaxed whitespace-pre-line text-[15px]">
        <?php echo html_escape($announcement->content); ?>
      </div>

      <?php if ($announcement->attachment): ?>
        <div class="pt-4 border-t border-outline-variant/40">
          <span class="text-xs text-on-surface-variant uppercase font-semibold block mb-2">Attached Material:</span>
          <a href="<?php echo base_url('uploads/notices/' . $announcement->attachment); ?>" target="_blank" class="inline-flex items-center gap-2 p-3 rounded-xl bg-surface-container-low border border-outline-variant/40 hover:bg-surface-container transition-colors">
            <span class="material-symbols-outlined text-primary text-[22px]">attach_file</span>
            <span class="font-bold text-on-surface text-[13px]"><?php echo html_escape($announcement->attachment); ?></span>
          </a>
        </div>
      <?php endif; ?>
    </div>
