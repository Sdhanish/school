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
        <h2 class="font-headline-md text-headline-md text-on-surface">Notification History & Delivery Logs</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Audit log of all sent, delivered, failed, and scheduled notifications with exact preserved messages.</p>
      </div>
      <div class="flex items-center gap-2">
        <a href="<?php echo site_url('communication/reports'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">bar_chart</span>Reports
        </a>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('communication/history'); ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Channel</label>
          <select name="channel" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Channels</option>
            <option value="In-App" <?php echo (($filters['channel'] ?? '') === 'In-App') ? 'selected' : ''; ?>>In-App</option>
            <option value="SMS" <?php echo (($filters['channel'] ?? '') === 'SMS') ? 'selected' : ''; ?>>SMS</option>
            <option value="WhatsApp" <?php echo (($filters['channel'] ?? '') === 'WhatsApp') ? 'selected' : ''; ?>>WhatsApp</option>
            <option value="Email" <?php echo (($filters['channel'] ?? '') === 'Email') ? 'selected' : ''; ?>>Email</option>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Source Module</label>
          <select name="source_module" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Modules</option>
            <option value="Attendance" <?php echo (($filters['source_module'] ?? '') === 'Attendance') ? 'selected' : ''; ?>>Attendance</option>
            <option value="Fees" <?php echo (($filters['source_module'] ?? '') === 'Fees') ? 'selected' : ''; ?>>Fees</option>
            <option value="Homework" <?php echo (($filters['source_module'] ?? '') === 'Homework') ? 'selected' : ''; ?>>Homework</option>
            <option value="Examination" <?php echo (($filters['source_module'] ?? '') === 'Examination') ? 'selected' : ''; ?>>Examination</option>
            <option value="Leave" <?php echo (($filters['source_module'] ?? '') === 'Leave') ? 'selected' : ''; ?>>Leave</option>
            <option value="Transport" <?php echo (($filters['source_module'] ?? '') === 'Transport') ? 'selected' : ''; ?>>Transport</option>
            <option value="Certificates" <?php echo (($filters['source_module'] ?? '') === 'Certificates') ? 'selected' : ''; ?>>Certificates</option>
            <option value="Direct" <?php echo (($filters['source_module'] ?? '') === 'Direct') ? 'selected' : ''; ?>>Direct</option>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Status</label>
          <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Statuses</option>
            <option value="Delivered" <?php echo (($filters['status'] ?? '') === 'Delivered') ? 'selected' : ''; ?>>Delivered</option>
            <option value="Sent" <?php echo (($filters['status'] ?? '') === 'Sent') ? 'selected' : ''; ?>>Sent</option>
            <option value="Pending" <?php echo (($filters['status'] ?? '') === 'Pending') ? 'selected' : ''; ?>>Pending</option>
            <option value="Scheduled" <?php echo (($filters['status'] ?? '') === 'Scheduled') ? 'selected' : ''; ?>>Scheduled</option>
            <option value="Failed" <?php echo (($filters['status'] ?? '') === 'Failed') ? 'selected' : ''; ?>>Failed</option>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Recipient Type</label>
          <select name="recipient_type" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Recipients</option>
            <option value="Parent" <?php echo (($filters['recipient_type'] ?? '') === 'Parent') ? 'selected' : ''; ?>>Parent</option>
            <option value="Student" <?php echo (($filters['recipient_type'] ?? '') === 'Student') ? 'selected' : ''; ?>>Student</option>
            <option value="Teacher" <?php echo (($filters['recipient_type'] ?? '') === 'Teacher') ? 'selected' : ''; ?>>Teacher</option>
            <option value="Staff" <?php echo (($filters['recipient_type'] ?? '') === 'Staff') ? 'selected' : ''; ?>>Staff</option>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Search</label>
          <div class="flex gap-2">
            <input type="text" name="search" value="<?php echo html_escape($filters['search'] ?? ''); ?>" placeholder="Recipient, contact, subject..." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            <button type="submit" class="px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant cursor-pointer">Go</button>
          </div>
        </div>
      </form>
    </div>

    <!-- History Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Notification History Logs (<?php echo count($history); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">ID</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Recipient</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Channel</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Source</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Rendered Message</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Date / Time</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($history)): ?>
              <tr><td colspan="8" class="px-4 py-8 text-center text-on-surface-variant">No notification history matching filters.</td></tr>
            <?php else: ?>
              <?php foreach ($history as $h): ?>
                <?php
                  $stBadge = ($h->status === 'Delivered') ? 'bg-secondary-container text-on-secondary-container' : (($h->status === 'Sent') ? 'bg-primary-container text-on-primary-container' : (($h->status === 'Pending') ? 'bg-amber-100 text-amber-900' : 'bg-error-container text-error'));
                ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap font-mono font-bold text-primary text-[12px]">#<?php echo $h->message_id; ?></td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <strong class="text-on-surface block"><?php echo html_escape($h->recipient_name); ?></strong>
                    <span class="text-[11px] text-on-surface-variant font-mono"><?php echo html_escape($h->recipient_contact); ?> (<?php echo $h->recipient_type; ?>)</span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-surface-container-high text-primary font-mono"><?php echo $h->channel; ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <strong class="text-on-surface block text-[13px]"><?php echo html_escape($h->source_module); ?></strong>
                    <span class="text-[11px] text-on-surface-variant font-mono"><?php echo html_escape($h->template_code ?: 'Direct'); ?></span>
                  </td>
                  <td class="px-4 py-3 text-[12px] text-on-surface-variant max-w-[280px]">
                    <div class="line-clamp-2 font-mono text-[11px]"><?php echo html_escape($h->rendered_message ?: $h->message); ?></div>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[12px] text-on-surface">
                    <?php echo date('d M Y, h:i A', strtotime($h->created_at)); ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold <?php echo $stBadge; ?>"><?php echo $h->status; ?></span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <a href="<?php echo site_url('communication/details/' . $h->message_id); ?>" class="p-1 rounded hover:bg-surface-container-high text-primary font-semibold text-xs inline-flex items-center gap-1">
                      <span class="material-symbols-outlined text-[16px]">visibility</span>Inspect
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
