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
        <h2 class="font-headline-md text-headline-md text-on-surface">Homework Configuration & Settings</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Configure global assignment policies, deadline defaults, upload restrictions, and audit trails.</p>
      </div>
    </div>

    <!-- Settings Form -->
    <?php echo form_open('homework/settings'); ?>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        
        <!-- Division 1: Deadlines & Upload Policies -->
        <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
          <div class="flex items-center gap-2 pb-3 border-b border-outline-variant/50">
            <span class="material-symbols-outlined text-primary text-[24px]">tune</span>
            <h3 class="font-headline-md text-title-lg font-bold text-on-surface">Defaults & File Constraints</h3>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Default Deadline (Days)</label>
              <input type="number" min="1" max="60" name="default_submission_deadline_days" value="<?php echo (int)$settings->default_submission_deadline_days; ?>" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Max Upload Size (MB)</label>
              <input type="number" min="1" max="100" name="max_upload_size_mb" value="<?php echo (int)$settings->max_upload_size_mb; ?>" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Allowed File Extensions</label>
            <input type="text" name="allowed_file_extensions" value="<?php echo html_escape($settings->allowed_file_extensions); ?>" placeholder="pdf,doc,docx,jpg,png,zip" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            <span class="text-[11px] text-on-surface-variant block mt-1">Comma-separated list of safe educational file formats</span>
          </div>
        </div>

        <!-- Division 2: Grading & Notification Switches -->
        <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
          <div class="flex items-center gap-2 pb-3 border-b border-outline-variant/50">
            <span class="material-symbols-outlined text-secondary text-[24px]">verified</span>
            <h3 class="font-headline-md text-title-lg font-bold text-on-surface">Policies & Notifications</h3>
          </div>

          <div class="space-y-3">
            <label class="flex items-center justify-between p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/40 cursor-pointer">
              <div>
                <strong class="text-on-surface text-body-md block">Accept Late Submissions by Default</strong>
                <span class="text-[12px] text-on-surface-variant">Allow students to submit past due date with an automatic Late tag.</span>
              </div>
              <input type="checkbox" name="allow_late_submissions_default" value="1" <?php echo $settings->allow_late_submissions_default ? 'checked' : ''; ?> class="w-5 h-5 rounded text-secondary focus:ring-secondary"/>
            </label>

            <label class="flex items-center justify-between p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/40 cursor-pointer">
              <div>
                <strong class="text-on-surface text-body-md block">Automatic Grade Calculation</strong>
                <span class="text-[12px] text-on-surface-variant">Resolve letter grades using the school Examination grading scale.</span>
              </div>
              <input type="checkbox" name="enable_grading" value="1" <?php echo $settings->enable_grading ? 'checked' : ''; ?> class="w-5 h-5 rounded text-secondary focus:ring-secondary"/>
            </label>

            <label class="flex items-center justify-between p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/40 cursor-pointer">
              <div>
                <strong class="text-on-surface text-body-md block">Parent Notification Queue</strong>
                <span class="text-[12px] text-on-surface-variant">Generate automated notification alerts for assignment releases and evaluations.</span>
              </div>
              <input type="checkbox" name="enable_parent_notifications" value="1" <?php echo $settings->enable_parent_notifications ? 'checked' : ''; ?> class="w-5 h-5 rounded text-secondary focus:ring-secondary"/>
            </label>
          </div>
        </div>
      </div>

      <div class="flex items-center justify-end p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 mb-6">
        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">save</span>Save Settings
        </button>
      </div>
    <?php echo form_close(); ?>

    <!-- Audit Trail Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
          <span class="material-symbols-outlined text-primary text-[20px]">history</span>Homework Audit Trail (Last 30 Events)
        </h3>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Timestamp</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Action</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Entity</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Details</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($audit_logs)): ?>
              <tr><td colspan="4" class="px-4 py-6 text-center text-on-surface-variant">No audit logs recorded yet.</td></tr>
            <?php else: ?>
              <?php foreach ($audit_logs as $l): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[12px] text-on-surface-variant"><?php echo date('d M Y, h:i A', strtotime($l->created_at)); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap font-bold text-primary"><?php echo html_escape($l->action); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-on-surface font-mono text-[12px]">#<?php echo $l->entity_id; ?></td>
                  <td class="px-4 py-3 text-on-surface text-[13px]"><?php echo html_escape($l->details); ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
