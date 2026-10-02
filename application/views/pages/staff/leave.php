<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

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

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Staff Leave Management</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Review staff leave applications, approve/reject requests, and track leave balances.</p>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <button onclick="openApplyLeaveModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">event_busy</span>New Leave Request
        </button>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="flex flex-col md:flex-row gap-3 mb-4 flex-wrap">
      <select onchange="applyFilter('status', this.value)" class="px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md text-on-surface-variant">
        <option value="">All Statuses</option>
        <option value="Pending" <?php echo ($this->input->get('status') === 'Pending') ? 'selected' : ''; ?>>Pending</option>
        <option value="Approved" <?php echo ($this->input->get('status') === 'Approved') ? 'selected' : ''; ?>>Approved</option>
        <option value="Rejected" <?php echo ($this->input->get('status') === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
      </select>
      <a href="<?php echo site_url('staff/leave'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors"><span class="material-symbols-outlined text-[18px]">restart_alt</span>Reset</a>
    </div>

    <!-- Leaves Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60">
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Staff Member</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Employee Code</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Leave Type</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Date Duration</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Days</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Reason</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              <th class="text-right px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/30 text-body-md">
            <?php if (empty($leaves)): ?>
              <tr><td colspan="8" class="px-4 py-8 text-center text-on-surface-variant">No staff leave requests found.</td></tr>
            <?php endif; ?>
            <?php foreach ($leaves as $lv): ?>
              <?php
                $badge = 'bg-surface-container-high text-on-surface';
                if ($lv->status === 'Approved') $badge = 'bg-secondary-container text-on-secondary-container font-semibold';
                if ($lv->status === 'Pending') $badge = 'bg-tertiary-container/40 text-tertiary font-semibold';
                if ($lv->status === 'Rejected') $badge = 'bg-error-container/30 text-error font-semibold';
              ?>
              <tr class='hover:bg-surface-container-low transition-colors'>
                <td class="px-4 py-3 font-semibold text-on-surface whitespace-nowrap">
                  <a href="<?php echo site_url('staff/profile/' . $lv->staff_id); ?>" class="hover:underline text-primary"><?php echo html_escape($lv->full_name); ?></a>
                  <span class="text-[11px] text-on-surface-variant ml-1 font-normal">(<?php echo html_escape($lv->designation_name ?: ($lv->staff_type ? ucfirst($lv->staff_type) : '—')); ?>)</span>
                </td>
                <td class="px-4 py-3 font-mono text-on-surface-variant whitespace-nowrap"><?php echo html_escape($lv->employee_code); ?></td>
                <td class="px-4 py-3 font-medium text-on-surface whitespace-nowrap"><?php echo html_escape($lv->leave_type); ?></td>
                <td class="px-4 py-3 text-on-surface whitespace-nowrap"><?php echo date('d M Y', strtotime($lv->from_date)) . ' → ' . date('d M Y', strtotime($lv->to_date)); ?></td>
                <td class="px-4 py-3 font-bold text-primary whitespace-nowrap"><?php echo $lv->total_days; ?> day(s)</td>
                <td class="px-4 py-3 text-on-surface-variant text-[13px]"><?php echo html_escape($lv->reason); ?></td>
                <td class="px-4 py-3 whitespace-nowrap"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] <?php echo $badge; ?>"><?php echo html_escape($lv->status); ?></span></td>
                <td class="px-4 py-3 text-right whitespace-nowrap">
                  <?php if ($lv->status === 'Pending'): ?>
                    <?php echo form_open('staff/leave', array('class' => 'inline')); ?>
                      <input type="hidden" name="action" value="update_status"/>
                      <input type="hidden" name="leave_id" value="<?php echo $lv->leave_id; ?>"/>
                      <input type="hidden" name="status" value="Approved"/>
                      <button type="submit" class="px-2.5 py-1 rounded bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors cursor-pointer">Approve</button>
                    <?php echo form_close(); ?>
                    <?php echo form_open('staff/leave', array('class' => 'inline ml-1')); ?>
                      <input type="hidden" name="action" value="update_status"/>
                      <input type="hidden" name="leave_id" value="<?php echo $lv->leave_id; ?>"/>
                      <input type="hidden" name="status" value="Rejected"/>
                      <button type="submit" class="px-2.5 py-1 rounded border border-outline-variant text-error hover:bg-error-container/20 transition-colors cursor-pointer">Reject</button>
                    <?php echo form_close(); ?>
                  <?php else: ?>
                    <span class="text-on-surface-variant text-[12px]">—</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="flex items-center justify-between px-4 py-3 border-t border-outline-variant/50 text-body-md font-body-md text-on-surface-variant">
        <span>Total <?php echo count($leaves); ?> leave record(s)</span>
      </div>
    </div>

    <!-- Modal: New Leave Request -->
    <div id="modal-apply-leave" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden">
      <div class="elevation-3 rounded-2xl bg-surface-container-lowest border border-outline-variant w-full max-w-lg">
        <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant">
          <h3 class="font-headline-md text-headline-md text-on-surface">Submit Leave Application</h3>
          <button type="button" onclick="closeApplyLeaveModal()" class="p-1 rounded-lg text-on-surface-variant hover:bg-surface-container-high cursor-pointer"><span class="material-symbols-outlined">close</span></button>
        </div>
        <?php echo form_open('staff/leave', array('id' => 'form-apply-leave', 'class' => 'p-6 space-y-4', 'onsubmit' => 'return validateLeaveApplication(this);')); ?>
          <input type="hidden" name="action" value="apply"/>
          <div>
            <label class="block text-label-md mb-1 font-medium text-on-surface">Select Staff Member <span class="text-error">*</span></label>
            <select name="staff_id" id="apply-leave-staff-id" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
              <option value="">-- Select Staff Member --</option>
              <?php foreach ($staff_list as $st): ?>
                <option value="<?php echo $st->staff_id; ?>"><?php echo html_escape($st->full_name . ' (' . $st->employee_code . ')'); ?></option>
              <?php endforeach; ?>
            </select>
            <p class="text-error text-xs mt-1 hidden" id="err-apply-leave-staff">Please select a staff member.</p>
          </div>
          <div>
            <label class="block text-label-md mb-1 font-medium text-on-surface">Leave Type *</label>
            <select name="leave_type" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
              <option value="Casual Leave">Casual Leave</option>
              <option value="Medical Leave">Medical Leave</option>
              <option value="Earned Leave">Earned Leave</option>
              <option value="Duty Leave">Duty / Official Leave</option>
              <option value="Maternity / Paternity Leave">Maternity / Paternity Leave</option>
            </select>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-label-md mb-1 font-medium text-on-surface">From Date *</label>
              <input type="date" name="from_date" required value="<?php echo date('Y-m-d'); ?>" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>
            <div>
              <label class="block text-label-md mb-1 font-medium text-on-surface">To Date *</label>
              <input type="date" name="to_date" required value="<?php echo date('Y-m-d'); ?>" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>
          </div>
          <div>
            <label class="block text-label-md mb-1 font-medium text-on-surface">Reason for Leave *</label>
            <textarea name="reason" required rows="3" placeholder="State reason for absence..." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
          </div>
          <div class="flex justify-end gap-2 pt-4 border-t border-outline-variant">
            <button type="button" onclick="closeApplyLeaveModal()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer">Cancel</button>
            <button type="submit" class="px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors cursor-pointer">Submit Leave Request</button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <script>
      function applyFilter(key, val) {
        var url = new URL(window.location.href);
        if (val) { url.searchParams.set(key, val); } else { url.searchParams.delete(key); }
        window.location.href = url.toString();
      }

      function openApplyLeaveModal() {
        var modal = document.getElementById('modal-apply-leave');
        var form = document.getElementById('form-apply-leave');
        if (form) {
          form.reset();
        }
        var staffSelect = document.getElementById('apply-leave-staff-id');
        if (staffSelect) {
          staffSelect.value = '';
          staffSelect.classList.remove('!border-error');
        }
        var errText = document.getElementById('err-apply-leave-staff');
        if (errText) {
          errText.classList.add('hidden');
        }
        if (modal) {
          modal.classList.remove('hidden');
        }
      }

      function closeApplyLeaveModal() {
        var modal = document.getElementById('modal-apply-leave');
        var form = document.getElementById('form-apply-leave');
        if (form) {
          form.reset();
        }
        var staffSelect = document.getElementById('apply-leave-staff-id');
        if (staffSelect) {
          staffSelect.value = '';
          staffSelect.classList.remove('!border-error');
        }
        var errText = document.getElementById('err-apply-leave-staff');
        if (errText) {
          errText.classList.add('hidden');
        }
        if (modal) {
          modal.classList.add('hidden');
        }
      }

      function validateLeaveApplication(form) {
        var staffSelect = document.getElementById('apply-leave-staff-id');
        var errText = document.getElementById('err-apply-leave-staff');
        if (!staffSelect || !staffSelect.value || staffSelect.value.trim() === '') {
          if (errText) {
            errText.classList.remove('hidden');
          }
          if (staffSelect) {
            staffSelect.classList.add('!border-error');
            staffSelect.focus();
          }
          alert('Please select a staff member.');
          return false;
        }
        if (errText) {
          errText.classList.add('hidden');
        }
        if (staffSelect) {
          staffSelect.classList.remove('!border-error');
        }
        return true;
      }

      document.addEventListener('DOMContentLoaded', function () {
        var staffSelect = document.getElementById('apply-leave-staff-id');
        if (staffSelect) {
          staffSelect.value = '';
          staffSelect.addEventListener('change', function () {
            var errText = document.getElementById('err-apply-leave-staff');
            if (this.value) {
              if (errText) errText.classList.add('hidden');
              this.classList.remove('!border-error');
            }
          });
        }
      });
    </script>
