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
        <h2 class="font-headline-md text-headline-md text-on-surface">Automated Notification Rules</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Configure event triggers across Attendance, Fees, Homework, Exams, Leave, and Transport.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="document.getElementById('ruleModal').showModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Create Rule
        </button>
      </div>
    </div>

    <!-- Supported Automation Events Guide -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <div class="flex items-start gap-3">
        <span class="material-symbols-outlined text-primary text-[24px] shrink-0 mt-0.5">bolt</span>
        <div class="text-xs text-on-surface-variant space-y-1">
          <strong class="text-on-surface text-body-md block">Supported Real-Time Automation Triggers:</strong>
          <p>
            • <strong>Attendance:</strong> Student Absent, Student Late, Attendance Corrected<br/>
            • <strong>Fees:</strong> Fee Assigned, Fee Due Soon, Fee Overdue, Payment Received<br/>
            • <strong>Homework:</strong> Homework Published, Assignment Due Soon, Assignment Reviewed<br/>
            • <strong>Examination:</strong> Exam Scheduled, Result Published<br/>
            • <strong>Leave & Transport:</strong> Leave Approved, Leave Rejected, Route / Timing Update<br/>
            • <strong>Certificates:</strong> Certificate Request Approved, Certificate Ready for Collection
          </p>
        </div>
      </div>
    </div>

    <!-- Rules Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Configured Automation Rules (<?php echo count($rules); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Rule Name</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Trigger Event</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Module</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Channel</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Audience</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Priority</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php foreach ($rules as $r): ?>
              <tr class="hover:bg-surface-container-low transition-colors">
                <td class="px-4 py-3 whitespace-nowrap">
                  <strong class="text-on-surface block"><?php echo html_escape($r->rule_name); ?></strong>
                  <span class="text-[11px] text-on-surface-variant font-mono"><?php echo html_escape($r->template_code); ?></span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-[13px] text-on-surface font-semibold">
                  <?php echo html_escape($r->event_name); ?>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-[13px] text-on-surface">
                  <?php echo html_escape($r->source_module); ?>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                  <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-primary-container text-on-primary-container font-mono">
                    <?php echo html_escape($r->channel); ?>
                  </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-[13px] text-on-surface font-medium">
                  <?php echo html_escape($r->recipient_type); ?>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                  <span class="px-2 py-0.5 rounded text-[10px] font-bold <?php echo ($r->priority === 'Urgent') ? 'bg-error-container text-error' : (($r->priority === 'Important') ? 'bg-amber-100 text-amber-900' : 'bg-surface-container-high text-on-surface-variant'); ?>">
                    <?php echo html_escape($r->priority); ?>
                  </span>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                  <a href="<?php echo site_url('communication/toggle_rule/' . $r->rule_id); ?>" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold <?php echo ($r->status === 'Active') ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container-high text-on-surface-variant'; ?>">
                    <?php echo html_escape($r->status); ?>
                  </a>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                  <div class="flex items-center justify-center gap-1.5">
                    <a href="<?php echo site_url('communication/test_rule/' . $r->rule_id); ?>" onclick="return confirm('Trigger test notification for this rule?');" class="p-1 rounded hover:bg-secondary-container text-secondary font-semibold text-xs inline-flex items-center gap-1">
                      <span class="material-symbols-outlined text-[16px]">play_arrow</span>Test
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Rule Modal Dialog -->
    <dialog id="ruleModal" class="p-0 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-3 w-full max-w-xl backdrop:bg-scrim/40">
      <div class="p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface">Create Automated Notification Rule</h3>
          <button onclick="document.getElementById('ruleModal').close()" class="p-1 rounded-full hover:bg-surface-container-high text-on-surface-variant">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('communication/automated_notifications'); ?>
          <input type="hidden" name="action" value="create_rule"/>
          <div class="space-y-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Rule Name *</label>
              <input type="text" name="rule_name" required placeholder="e.g. Daily Student Absent Alert to Parents" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Trigger Event *</label>
                <input type="text" name="event_name" required placeholder="e.g. Attendance Absent" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Source Module *</label>
                <select name="source_module" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <option value="Attendance">Attendance</option>
                  <option value="Fees">Fees</option>
                  <option value="Homework">Homework</option>
                  <option value="Examination">Examination</option>
                  <option value="Leave">Leave</option>
                  <option value="Transport">Transport</option>
                  <option value="Certificates">Certificates</option>
                  <option value="General">General</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Notification Template *</label>
                <select name="template_id" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <?php foreach ($templates as $t): ?>
                    <option value="<?php echo $t->template_id; ?>"><?php echo html_escape($t->template_name . ' (' . $t->template_code . ' - ' . $t->channel . ')'); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Dispatch Channel *</label>
                <select name="channel" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <option value="In-App">In-App</option>
                  <option value="SMS">SMS</option>
                  <option value="WhatsApp">WhatsApp</option>
                  <option value="Email">Email</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Recipient Type *</label>
                <select name="recipient_type" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <option value="Parent">Parent</option>
                  <option value="Student">Student</option>
                  <option value="Teacher">Teacher</option>
                  <option value="Staff">Staff</option>
                  <option value="Principal">Principal</option>
                  <option value="Admin">Admin</option>
                </select>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Cooldown (Mins)</label>
                <input type="number" name="cooldown_minutes" value="60" min="5" max="1440" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Priority</label>
                <select name="priority" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <option value="Normal">Normal</option>
                  <option value="Important">Important</option>
                  <option value="Urgent">Urgent</option>
                </select>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-outline-variant/50">
              <button type="button" onclick="document.getElementById('ruleModal').close()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-label-md">Cancel</button>
              <button type="submit" class="px-5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">Save Rule</button>
            </div>
          </div>
        <?php echo form_close(); ?>
      </div>
    </dialog>
