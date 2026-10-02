<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Staff Attendance</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Mark and manage daily attendance records for all teaching and administrative staff members.</p>
      </div>
      <div class="flex items-center gap-2">
        <a href="<?php echo site_url('staff/leave'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors"><span class="material-symbols-outlined text-[18px]">event_busy</span>Leave Management</a>
      </div>
    </div>

    <!-- Date and Attendance Actions -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-5 mb-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-label-md text-on-surface mb-1">Attendance Date *</label>
          <input type="date" id="att_date_picker" value="<?php echo $date; ?>" onchange="changeAttendanceFilter()" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md"/>
        </div>
        
        <div class="flex items-end">
          <button type="button" onclick="markAllStatus('Present')" class="w-full px-3 py-2.5 rounded-lg border border-secondary text-secondary hover:bg-secondary/10 text-label-md flex items-center justify-center gap-1.5 transition-colors">
            <span class="material-symbols-outlined text-[18px]">done_all</span>Mark All Present
          </button>
        </div>
      </div>
    </div>

    <!-- Attendance Form & Sheet -->
    <?php echo form_open('staff/attendance?date=' . $date); ?>
      <input type="hidden" name="attendance_date" value="<?php echo $date; ?>"/>

      <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
        <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px]">fact_check</span>Daily Roll Sheet — <?php echo date('l, d F Y', strtotime($date)); ?>
          </h3>
          <span class="text-label-md text-on-surface-variant font-medium"><?php echo count($attendance_list); ?> staff member(s)</span>
        </div>
        <div class="table-scroll overflow-x-auto">
          <table class="w-full data-table zebra border-collapse">
            <thead>
              <tr class="border-b border-outline-variant/60">
                <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Employee Code</th>
                <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Staff Name</th>
                <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Designation</th>
                <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Attendance Status</th>
                <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Remarks / Notes</th>
                <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Marked By</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/30 text-body-md">
              <?php if (empty($attendance_list)): ?>
                <tr><td colspan="6" class="px-4 py-8 text-center text-on-surface-variant">No active staff records found.</td></tr>
              <?php endif; ?>
              <?php foreach ($attendance_list as $st): ?>
                <?php
                  $currentStatus = $st->attendance_status ?: 'Present';
                ?>
                <tr class='hover:bg-surface-container-low transition-colors'>
                  <td class="px-4 py-3 font-mono font-bold text-primary whitespace-nowrap"><?php echo html_escape($st->employee_code); ?></td>
                  <td class="px-4 py-3 font-semibold text-on-surface whitespace-nowrap">
                    <a href="<?php echo site_url('staff/profile/' . $st->staff_id); ?>" class="hover:underline"><?php echo html_escape($st->full_name); ?></a>
                    <span class="text-[11px] text-on-surface-variant ml-1 font-normal">(<?php echo html_escape($st->designation_name ?: ($st->staff_type ? ucfirst($st->staff_type) : '—')); ?>)</span>
                  </td>
                  <td class="px-4 py-3 text-on-surface whitespace-nowrap"><?php echo html_escape($st->designation_name ?: "—"); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <div class="flex items-center gap-3">
                      <label class="inline-flex items-center gap-1 cursor-pointer">
                        <input type="radio" name="attendance[<?php echo $st->staff_id; ?>][status]" value="Present" <?php echo ($currentStatus === 'Present') ? 'checked' : ''; ?> class="att-radio-present text-secondary accent-secondary focus:ring-secondary"/>
                        <span class="text-[13px] font-medium text-secondary">Present</span>
                      </label>
                      <label class="inline-flex items-center gap-1 cursor-pointer">
                        <input type="radio" name="attendance[<?php echo $st->staff_id; ?>][status]" value="Late / Half Day" <?php echo ($currentStatus === 'Half Day' || $currentStatus === 'Late / Half Day') ? 'checked' : ''; ?> class="att-radio-half text-tertiary accent-tertiary focus:ring-tertiary"/>
                        <span class="text-[13px] font-medium text-tertiary">Half Day</span>
                      </label>
                      <label class="inline-flex items-center gap-1 cursor-pointer">
                        <input type="radio" name="attendance[<?php echo $st->staff_id; ?>][status]" value="Leave" <?php echo ($currentStatus === 'Leave') ? 'checked' : ''; ?> class="att-radio-leave text-error accent-error focus:ring-error"/>
                        <span class="text-[13px] font-medium text-error">Leave</span>
                      </label>
                    </div>
                  </td>
                  <td class="px-4 py-3">
                    <input type="text" name="attendance[<?php echo $st->staff_id; ?>][remarks]" value="<?php echo html_escape($st->remarks); ?>" placeholder="Notes (e.g. On official duty)" class="w-full px-2.5 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-[13px]"/>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-body-sm text-on-surface-variant">
                    <?php if (!empty($st->attendance_id)): ?>
                      <?php if (!empty($st->marked_by_display) && $st->marked_by_display !== 'Not Available'): ?>
                        <div class="font-medium text-on-surface"><?php echo html_escape($st->marked_by_display); ?></div>
                        <?php if (!empty($st->updated_by_display)): ?>
                          <div class="text-[11px] text-on-surface-variant/80">Edited: <?php echo html_escape($st->updated_by_display); ?></div>
                        <?php endif; ?>
                      <?php else: ?>
                        <span class="text-on-surface-variant/70 italic">Not Available</span>
                      <?php endif; ?>
                    <?php else: ?>
                      <span class="text-on-surface-variant/50 text-[11px]">—</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="px-5 py-4 bg-surface-container-low border-t border-outline-variant/50 flex justify-end">
          <button type="submit" class="inline-flex items-center gap-1.5 px-6 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">save</span>Save Daily Attendance
          </button>
        </div>
      </div>
    <?php echo form_close(); ?>

    <script>
      function changeAttendanceFilter() {
        var d = document.getElementById('att_date_picker').value;
                var url = new URL('<?php echo site_url('staff/attendance'); ?>', window.location.origin);
        if (d) url.searchParams.set('date', d);
        window.location.href = url.toString();
      }
      function markAllStatus(status) {
        if (status === 'Present') {
          document.querySelectorAll('.att-radio-present').forEach(function(r) { r.checked = true; });
        }
      }
    </script>
