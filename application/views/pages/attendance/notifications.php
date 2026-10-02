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

    <!-- Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Parent Notifications</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Manage outbound attendance alerts generated for absent, late, or excused students.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('attendance/notification_history'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">history</span>Notification History
        </a>
        <a href="<?php echo site_url('attendance/settings'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">tune</span>Templates & Triggers
        </a>
      </div>
    </div>

    <!-- Status Alert Banner -->
    <div class="p-4 rounded-xl bg-primary-fixed/20 border border-primary/20 text-on-surface text-body-md flex items-center gap-3 mb-6">
      <span class="material-symbols-outlined text-primary text-[24px] shrink-0">mark_email_unread</span>
      <div>
        <div class="font-semibold text-primary">Parent Notification Queue Active</div>
        <div class="text-[13px] text-on-surface-variant">Notifications are queued as <strong>Pending</strong> upon marking Absent, Late, or Excused attendance. Real SMS/WhatsApp delivery gateways can be connected at any time.</div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('attendance/notifications'); ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Status</label>
          <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="Pending" <?php echo ($filters['status'] === 'Pending') ? 'selected' : ''; ?>>Pending Queue</option>
            <option value="Sent" <?php echo ($filters['status'] === 'Sent') ? 'selected' : ''; ?>>Sent</option>
            <option value="Failed" <?php echo ($filters['status'] === 'Failed') ? 'selected' : ''; ?>>Failed</option>
            <option value="" <?php echo ($filters['status'] === '') ? 'selected' : ''; ?>>All Statuses</option>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Type</label>
          <select name="type" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Types</option>
            <option value="Absent" <?php echo ($filters['notification_type'] === 'Absent') ? 'selected' : ''; ?>>Absent Alerts</option>
            <option value="Late" <?php echo ($filters['notification_type'] === 'Late') ? 'selected' : ''; ?>>Late Alerts</option>
            <option value="Excused" <?php echo ($filters['notification_type'] === 'Excused') ? 'selected' : ''; ?>>Excused Alerts</option>
            <option value="Attendance Summary" <?php echo ($filters['notification_type'] === 'Attendance Summary') ? 'selected' : ''; ?>>Attendance Summary</option>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Class</label>
          <select name="class_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Classes</option>
            <?php foreach ($classes as $cls): ?>
              <option value="<?php echo $cls->class_id; ?>" <?php echo ($filters['class_id'] == $cls->class_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($cls->class_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Date</label>
          <input type="date" name="date" value="<?php echo html_escape($filters['date']); ?>" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
        </div>
      </form>
    </div>

    <!-- Notifications Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Queue Records (<?php echo count($notifications); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Student</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Parent / Contact</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Type</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Date</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Message Preview</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($notifications)): ?>
              <tr><td colspan="7" class="px-4 py-8 text-center text-on-surface-variant text-body-md">No notifications found in this queue.</td></tr>
            <?php else: ?>
              <?php foreach ($notifications as $n): ?>
                <?php
                  $fullName = trim($n->first_name . ' ' . $n->last_name);
                  $statusBadge = 'bg-amber-100 text-amber-900';
                  if ($n->status === 'Sent') $statusBadge = 'bg-secondary-container text-on-secondary-container';
                  elseif ($n->status === 'Failed') $statusBadge = 'bg-error-container text-on-error-container';

                  $typeBadge = 'bg-error-container text-on-error-container';
                  if ($n->notification_type === 'Late') $typeBadge = 'bg-amber-100 text-amber-900';
                  elseif ($n->notification_type === 'Excused') $typeBadge = 'bg-primary text-white';
                  elseif ($n->notification_type === 'Attendance Summary') $typeBadge = 'bg-secondary-container text-on-secondary-container';
                ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap font-medium text-on-surface">
                    <?php echo html_escape($fullName); ?>
                    <span class="text-[12px] text-on-surface-variant block font-normal"><?php echo html_escape($n->class_name . ' ' . $n->section_name); ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-body-md">
                    <div class="font-medium text-on-surface"><?php echo html_escape($n->guardian_name ?: $n->parent_name ?: '—'); ?></div>
                    <div class="text-[12px] text-on-surface-variant font-mono"><?php echo html_escape($n->guardian_phone ?: $n->parent_phone ?: 'No phone'); ?></div>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold <?php echo $typeBadge; ?>">
                      <?php echo html_escape($n->notification_type); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 font-mono text-body-md text-on-surface whitespace-nowrap">
                    <?php echo date('d M Y', strtotime($n->attendance_date)); ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold <?php echo $statusBadge; ?>">
                      <?php if ($n->status === 'Pending'): ?>
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                      <?php elseif ($n->status === 'Sent'): ?>
                        <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
                      <?php endif; ?>
                      <?php echo html_escape($n->status); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-body-md text-on-surface-variant max-w-[240px] truncate">
                    <?php echo html_escape($n->message); ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <div class="flex items-center justify-center gap-1">
                      <!-- View Details Modal Trigger -->
                      <button type="button" onclick='openNotificationModal(<?php echo json_encode($n); ?>)' class="p-1.5 rounded-lg hover:bg-surface-container-high text-primary transition-colors cursor-pointer" title="View Details">
                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                      </button>

                      <!-- Mark Sent (Simulation / Status update) -->
                      <?php echo form_open('attendance/notifications', array('class' => 'inline')); ?>
                        <input type="hidden" name="action" value="update_status"/>
                        <input type="hidden" name="notification_id" value="<?php echo $n->notification_id; ?>"/>
                        <input type="hidden" name="status" value="<?php echo ($n->status === 'Pending') ? 'Sent' : 'Pending'; ?>"/>
                        <button type="submit" class="p-1.5 rounded-lg hover:bg-surface-container-high text-secondary transition-colors cursor-pointer" title="<?php echo ($n->status === 'Pending') ? 'Mark as Sent' : 'Revert to Pending'; ?>">
                          <span class="material-symbols-outlined text-[18px]"><?php echo ($n->status === 'Pending') ? 'send' : 'undo'; ?></span>
                        </button>
                      <?php echo form_close(); ?>

                      <!-- Delete -->
                      <?php echo form_open('attendance/notifications', array('class' => 'inline', 'onsubmit' => 'return confirm("Delete this notification record?");')); ?>
                        <input type="hidden" name="action" value="delete"/>
                        <input type="hidden" name="notification_id" value="<?php echo $n->notification_id; ?>"/>
                        <button type="submit" class="p-1.5 rounded-lg hover:bg-error-container text-error transition-colors cursor-pointer" title="Delete">
                          <span class="material-symbols-outlined text-[18px]">delete</span>
                        </button>
                      <?php echo form_close(); ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- NOTIFICATION DETAIL MODAL -->
    <div id="notification-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
      <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant max-w-lg w-full p-6 elevation-3 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-lg text-on-surface font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[22px]">notifications</span>Notification Details
          </h3>
          <button onclick="closeNotificationModal()" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <div class="space-y-3.5 text-body-md">
          <div class="grid grid-cols-2 gap-3 p-3 rounded-xl bg-surface-container-low border border-outline-variant/40">
            <div>
              <span class="text-[12px] text-on-surface-variant block">Student</span>
              <strong id="modal-notif-student" class="text-on-surface font-semibold">Student</strong>
            </div>
            <div>
              <span class="text-[12px] text-on-surface-variant block">Class & Division</span>
              <strong id="modal-notif-class" class="text-on-surface">Class</strong>
            </div>
            <div>
              <span class="text-[12px] text-on-surface-variant block">Parent / Guardian</span>
              <strong id="modal-notif-parent" class="text-on-surface">Parent</strong>
            </div>
            <div>
              <span class="text-[12px] text-on-surface-variant block">Phone / Contact</span>
              <strong id="modal-notif-phone" class="text-primary font-mono">Phone</strong>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3 text-[13px]">
            <div>
              <span class="text-on-surface-variant">Attendance Date: </span>
              <strong id="modal-notif-date" class="text-on-surface font-mono">Date</strong>
            </div>
            <div>
              <span class="text-on-surface-variant">Type: </span>
              <strong id="modal-notif-type" class="text-on-surface font-semibold">Type</strong>
            </div>
            <div>
              <span class="text-on-surface-variant">Status: </span>
              <strong id="modal-notif-status" class="text-on-surface">Status</strong>
            </div>
            <div>
              <span class="text-on-surface-variant">Created: </span>
              <span id="modal-notif-created" class="text-on-surface-variant">Created</span>
            </div>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Notification Message</label>
            <div id="modal-notif-message" class="p-3.5 rounded-xl bg-surface-container-lowest border border-outline-variant text-on-surface text-body-md leading-relaxed whitespace-pre-wrap font-sans">
              Message text...
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-4 border-t border-outline-variant/50 mt-5">
          <button type="button" onclick="closeNotificationModal()" class="px-4 py-2 rounded-lg bg-surface-container-high text-on-surface text-label-md font-medium hover:bg-surface-container-highest cursor-pointer">
            Close
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Script -->
    <script>
      function openNotificationModal(item) {
        document.getElementById('modal-notif-student').textContent = (item.first_name || '') + ' ' + (item.last_name || '');
        document.getElementById('modal-notif-class').textContent = (item.class_name || '') + ' ' + (item.section_name || '');
        document.getElementById('modal-notif-parent').textContent = item.guardian_name || item.parent_name || 'Guardian';
        document.getElementById('modal-notif-phone').textContent = item.guardian_phone || item.parent_phone || '—';
        document.getElementById('modal-notif-date').textContent = item.attendance_date;
        document.getElementById('modal-notif-type').textContent = item.notification_type;
        document.getElementById('modal-notif-status').textContent = item.status;
        document.getElementById('modal-notif-created').textContent = item.created_at;
        document.getElementById('modal-notif-message').textContent = item.message;

        var modal = document.getElementById('notification-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
      }

      function closeNotificationModal() {
        var modal = document.getElementById('notification-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      }
    </script>
