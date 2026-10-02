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

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Attendance Settings</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Configure attendance statuses, period tracking, parent alert triggers, and dynamic notification templates.</p>
      </div>
    </div>

    <?php echo form_open('attendance/settings'); ?>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        
        <!-- DIVISION 1: Status & Period Options -->
        <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-5">
          <div class="flex items-center gap-2.5 pb-3 border-b border-outline-variant/50">
            <span class="material-symbols-outlined text-primary text-[24px]">checklist</span>
            <h3 class="font-headline-md text-title-lg font-bold text-on-surface">Active Attendance Statuses</h3>
          </div>

          <p class="text-body-md text-on-surface-variant">Enable or disable statuses available during attendance marking.</p>

          <div class="space-y-3">
            <label class="flex items-center justify-between p-3 rounded-xl bg-surface-container-low border border-outline-variant/40 cursor-pointer">
              <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-secondary-container text-on-secondary-container flex items-center justify-center font-bold text-[13px]">P</span>
                <div>
                  <div class="font-semibold text-on-surface text-body-md">Present</div>
                  <div class="text-[12px] text-on-surface-variant">Student is in class and present for academic sessions.</div>
                </div>
              </div>
              <input type="checkbox" name="enable_present" value="1" <?php echo ($settings->enable_present) ? 'checked' : ''; ?> class="w-5 h-5 rounded border-outline text-secondary focus:ring-secondary"/>
            </label>

            <label class="flex items-center justify-between p-3 rounded-xl bg-surface-container-low border border-outline-variant/40 cursor-pointer">
              <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-error-container text-on-error-container flex items-center justify-center font-bold text-[13px]">A</span>
                <div>
                  <div class="font-semibold text-on-surface text-body-md">Absent</div>
                  <div class="text-[12px] text-on-surface-variant">Student is absent without pre-approved leave.</div>
                </div>
              </div>
              <input type="checkbox" name="enable_absent" value="1" <?php echo ($settings->enable_absent) ? 'checked' : ''; ?> class="w-5 h-5 rounded border-outline text-error focus:ring-error"/>
            </label>

            <label class="flex items-center justify-between p-3 rounded-xl bg-surface-container-low border border-outline-variant/40 cursor-pointer">
              <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-amber-100 text-amber-900 flex items-center justify-center font-bold text-[13px]">L</span>
                <div>
                  <div class="font-semibold text-on-surface text-body-md">Late</div>
                  <div class="text-[12px] text-on-surface-variant">Student arrived late after session roll call.</div>
                </div>
              </div>
              <input type="checkbox" name="enable_late" value="1" <?php echo ($settings->enable_late) ? 'checked' : ''; ?> class="w-5 h-5 rounded border-outline text-amber-600 focus:ring-amber-500"/>
            </label>

            <label class="flex items-center justify-between p-3 rounded-xl bg-surface-container-low border border-outline-variant/40 cursor-pointer">
              <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-primary text-white flex items-center justify-center font-bold text-[13px]">E</span>
                <div>
                  <div class="font-semibold text-on-surface text-body-md">Excused / Approved Leave</div>
                  <div class="text-[12px] text-on-surface-variant">Official medical exemption or authorized school absence.</div>
                </div>
              </div>
              <input type="checkbox" name="enable_excused" value="1" <?php echo ($settings->enable_excused) ? 'checked' : ''; ?> class="w-5 h-5 rounded border-outline text-primary focus:ring-primary"/>
            </label>
          </div>

          <div class="pt-4 border-t border-outline-variant/50">
            <h4 class="font-semibold text-on-surface text-body-md mb-2 flex items-center gap-2">
              <span class="material-symbols-outlined text-primary text-[20px]">schedule</span>Period-Wise Attendance Tracking
            </h4>
            <label class="flex items-center justify-between p-3 rounded-xl bg-surface-container-low border border-outline-variant/40 cursor-pointer">
              <div>
                <div class="font-semibold text-on-surface text-body-md">Enable Period Attendance</div>
                <div class="text-[12px] text-on-surface-variant">Allow subject teachers to mark attendance for individual periods throughout the school day.</div>
              </div>
              <input type="checkbox" name="enable_period_attendance" value="1" <?php echo ($settings->enable_period_attendance) ? 'checked' : ''; ?> class="w-5 h-5 rounded border-outline text-primary focus:ring-primary"/>
            </label>
          </div>
        </div>

        <!-- DIVISION 2: Notification Triggers & Timings -->
        <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-5">
          <div class="flex items-center gap-2.5 pb-3 border-b border-outline-variant/50">
            <span class="material-symbols-outlined text-primary text-[24px]">notifications_active</span>
            <h3 class="font-headline-md text-title-lg font-bold text-on-surface">Parent Notification Triggers</h3>
          </div>

          <p class="text-body-md text-on-surface-variant">Automatically queue notification alerts to parents when specific statuses are logged.</p>

          <div class="space-y-3">
            <label class="flex items-center justify-between p-3 rounded-xl bg-surface-container-low border border-outline-variant/40 cursor-pointer">
              <div>
                <div class="font-semibold text-on-surface text-body-md">Notify Parent on Absent</div>
                <div class="text-[12px] text-on-surface-variant">Create outbound alert record immediately when a student is marked Absent.</div>
              </div>
              <input type="checkbox" name="enable_absent_notification" value="1" <?php echo ($settings->enable_absent_notification) ? 'checked' : ''; ?> class="w-5 h-5 rounded border-outline text-primary focus:ring-primary"/>
            </label>

            <label class="flex items-center justify-between p-3 rounded-xl bg-surface-container-low border border-outline-variant/40 cursor-pointer">
              <div>
                <div class="font-semibold text-on-surface text-body-md">Notify Parent on Late Arrival</div>
                <div class="text-[12px] text-on-surface-variant">Create alert record when a student is logged as arriving late.</div>
              </div>
              <input type="checkbox" name="enable_late_notification" value="1" <?php echo ($settings->enable_late_notification) ? 'checked' : ''; ?> class="w-5 h-5 rounded border-outline text-primary focus:ring-primary"/>
            </label>

            <label class="flex items-center justify-between p-3 rounded-xl bg-surface-container-low border border-outline-variant/40 cursor-pointer">
              <div>
                <div class="font-semibold text-on-surface text-body-md">Periodic Attendance Summary</div>
                <div class="text-[12px] text-on-surface-variant">Enable periodic weekly/monthly attendance summary generation for parents.</div>
              </div>
              <input type="checkbox" name="enable_summary_notification" value="1" <?php echo ($settings->enable_summary_notification) ? 'checked' : ''; ?> class="w-5 h-5 rounded border-outline text-primary focus:ring-primary"/>
            </label>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Notification Trigger Timing</label>
            <select name="notification_timing" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
              <option value="On Marking" <?php echo ($settings->notification_timing === 'On Marking') ? 'selected' : ''; ?>>Instantly on Marking Attendance</option>
              <option value="End of Day" <?php echo ($settings->notification_timing === 'End of Day') ? 'selected' : ''; ?>>Batch Generation at End of School Day (04:00 PM)</option>
              <option value="Manual Dispatch" <?php echo ($settings->notification_timing === 'Manual Dispatch') ? 'selected' : ''; ?>>Manual Admin Approval Queue</option>
            </select>
          </div>
        </div>
      </div>

      <!-- DIVISION 3: Notification Templates & Dynamic Placeholders -->
      <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-5 mb-6">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <div class="flex items-center gap-2.5">
            <span class="material-symbols-outlined text-primary text-[24px]">drafts</span>
            <h3 class="font-headline-md text-title-lg font-bold text-on-surface">Notification Template Configuration</h3>
          </div>
          <div class="text-[12px] text-on-surface-variant font-medium">
            Placeholders: <span class="font-mono text-primary font-bold">{student_name}, {date}, {status}, {present_days}, {absent_days}, {late_days}</span>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Absent Notification Template</label>
            <textarea name="absent_template" rows="3" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary font-sans"><?php echo html_escape($settings->absent_template); ?></textarea>
            <p class="text-[11px] text-on-surface-variant mt-1">Sent to parent when student is marked Absent.</p>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Late Arrival Template</label>
            <textarea name="late_template" rows="3" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary font-sans"><?php echo html_escape($settings->late_template); ?></textarea>
            <p class="text-[11px] text-on-surface-variant mt-1">Sent to parent when student arrives late.</p>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Excused Absence Template</label>
            <textarea name="excused_template" rows="3" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary font-sans"><?php echo html_escape($settings->excused_template ?: 'Dear Parent, your child {student_name} has been excused on {date}.'); ?></textarea>
            <p class="text-[11px] text-on-surface-variant mt-1">Sent when approved leave or medical exemption is logged.</p>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Attendance Summary Template</label>
            <textarea name="summary_template" rows="3" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary font-sans"><?php echo html_escape($settings->summary_template); ?></textarea>
            <p class="text-[11px] text-on-surface-variant mt-1">Used for periodic summary statements sent to parents.</p>
          </div>
        </div>
      </div>

      <!-- Save Button -->
      <div class="flex items-center justify-end p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">save</span>Save Settings
        </button>
      </div>
    <?php echo form_close(); ?>
