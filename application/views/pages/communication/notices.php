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
        <h2 class="font-headline-md text-headline-md text-on-surface">Notices & Circulars</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Institutional notices, holiday circulars, academic notifications, and emergency alerts.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('communication/create_notice'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Create Notice
        </a>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('communication/notices'); ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Category</label>
          <select name="category" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Categories</option>
            <?php foreach (['General', 'Academic', 'Examination', 'Holiday', 'Fee', 'Attendance', 'Event', 'Emergency'] as $cat): ?>
              <option value="<?php echo $cat; ?>" <?php echo (($filters['category'] ?? '') === $cat) ? 'selected' : ''; ?>><?php echo $cat; ?></option>
            <?php endforeach; ?>
          </select>
        </div>

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
          <select name="target_role" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Roles</option>
            <option value="All" <?php echo (($filters['target_role'] ?? '') === 'All') ? 'selected' : ''; ?>>All Users</option>
            <option value="Parents" <?php echo (($filters['target_role'] ?? '') === 'Parents') ? 'selected' : ''; ?>>Parents</option>
            <option value="Teachers" <?php echo (($filters['target_role'] ?? '') === 'Teachers') ? 'selected' : ''; ?>>Teachers</option>
            <option value="Students" <?php echo (($filters['target_role'] ?? '') === 'Students') ? 'selected' : ''; ?>>Students</option>
            <option value="Staff" <?php echo (($filters['target_role'] ?? '') === 'Staff') ? 'selected' : ''; ?>>Staff</option>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Search</label>
          <div class="flex gap-2">
            <input type="text" name="search" value="<?php echo html_escape($filters['search'] ?? ''); ?>" placeholder="Notice title, keywords..." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            <button type="submit" class="px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant cursor-pointer">
              Go
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Notices Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Notices (<?php echo count($notices); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Notice Title</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Category</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Audience</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Publish Date</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Priority</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($notices)): ?>
              <tr><td colspan="7" class="px-4 py-8 text-center text-on-surface-variant">No notices found.</td></tr>
            <?php else: ?>
              <?php foreach ($notices as $n): ?>
                <?php
                  $prioColor = ($n->priority === 'Urgent') ? 'bg-error-container text-error font-bold' : (($n->priority === 'Important') ? 'bg-amber-100 text-amber-900 font-bold' : 'bg-surface-container-high text-on-surface-variant');
                  $statusBadge = ($n->status === 'Published') ? 'bg-secondary-container text-on-secondary-container font-bold' : (($n->status === 'Scheduled') ? 'bg-primary-container text-on-primary-container font-bold' : 'bg-surface-container-high text-on-surface-variant');
                ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap">
                    <strong class="text-on-surface block"><?php echo html_escape($n->title); ?></strong>
                    <span class="text-[11px] text-on-surface-variant">Posted by: <?php echo html_escape($n->posted_by); ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-surface-container-high text-on-surface-variant">
                      <?php echo html_escape($n->category); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-on-surface text-[13px]">
                    <?php echo html_escape($n->target_role ?: 'All'); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[12px] text-on-surface">
                    <?php echo date('d M Y', strtotime($n->publish_date)); ?>
                    <?php if ($n->expiry_date): ?>
                      <span class="text-[10px] text-on-surface-variant block">Exp: <?php echo date('d M Y', strtotime($n->expiry_date)); ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] <?php echo $prioColor; ?>">
                      <?php echo html_escape($n->priority); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] <?php echo $statusBadge; ?>">
                      <?php echo html_escape($n->status); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <div class="flex items-center justify-center gap-1">
                      <a href="<?php echo site_url('communication/archive_notice/' . $n->notice_id); ?>" class="p-1 rounded hover:bg-surface-container-high text-on-surface-variant" title="Archive">
                        <span class="material-symbols-outlined text-[18px]">archive</span>
                      </a>
                      <a href="<?php echo site_url('communication/delete_notice/' . $n->notice_id); ?>" onclick="return confirm('Delete this notice?');" class="p-1 rounded hover:bg-error-container text-error" title="Delete">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
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
