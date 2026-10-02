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
        <h2 class="font-headline-md text-headline-md text-on-surface">Institutional Announcements</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">High-level school announcements, annual functions, reopening bulletins, and major institutional updates.</p>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('communication/announcements'); ?>" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Priority</label>
          <select name="priority" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Priorities</option>
            <option value="Normal" <?php echo (($filters['priority'] ?? '') === 'Normal') ? 'selected' : ''; ?>>Normal</option>
            <option value="Important" <?php echo (($filters['priority'] ?? '') === 'Important') ? 'selected' : ''; ?>>Important</option>
            <option value="Urgent" <?php echo (($filters['priority'] ?? '') === 'Urgent') ? 'selected' : ''; ?>>Urgent</option>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Audience</label>
          <select name="audience" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Audiences</option>
            <option value="Whole School" <?php echo (($filters['audience'] ?? '') === 'Whole School') ? 'selected' : ''; ?>>Whole School</option>
            <option value="Parents" <?php echo (($filters['audience'] ?? '') === 'Parents') ? 'selected' : ''; ?>>Parents Only</option>
            <option value="Staff" <?php echo (($filters['audience'] ?? '') === 'Staff') ? 'selected' : ''; ?>>Staff Only</option>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Search</label>
          <div class="flex gap-2">
            <input type="text" name="search" value="<?php echo html_escape($filters['search'] ?? ''); ?>" placeholder="Title or text..." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            <button type="submit" class="px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant cursor-pointer">
              Go
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Announcements Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
      <?php if (empty($announcements)): ?>
        <div class="col-span-3 p-8 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 text-center text-on-surface-variant text-body-md">
          No announcements found matching criteria.
        </div>
      <?php else: ?>
        <?php foreach ($announcements as $a): ?>
          <?php
            $prioColor = ($a->priority === 'Urgent') ? 'bg-error-container text-error font-bold' : (($a->priority === 'Important') ? 'bg-amber-100 text-amber-900 font-bold' : 'bg-surface-container-high text-on-surface-variant');
          ?>
          <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex flex-col justify-between space-y-4 hover:border-primary transition-all">
            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-surface-container-high text-on-surface-variant"><?php echo html_escape($a->category ?: 'General'); ?></span>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] <?php echo $prioColor; ?>"><?php echo html_escape($a->priority); ?></span>
              </div>
              <h3 class="font-headline-md text-title-md font-bold text-on-surface line-clamp-2">
                <a href="<?php echo site_url('communication/announcement_details/' . $a->announcement_id); ?>" class="hover:text-primary transition-colors">
                  <?php echo html_escape($a->title); ?>
                </a>
              </h3>
              <p class="text-body-md text-on-surface-variant line-clamp-3 leading-relaxed">
                <?php echo html_escape(strip_tags($a->content)); ?>
              </p>
            </div>

            <div class="pt-3 border-t border-outline-variant/40 flex items-center justify-between text-xs text-on-surface-variant">
              <span>Audience: <strong><?php echo html_escape($a->audience); ?></strong></span>
              <span class="font-mono"><?php echo date('d M Y', strtotime($a->announcement_date)); ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
