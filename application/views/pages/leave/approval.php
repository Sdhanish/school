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
        <h2 class="font-headline-md text-headline-md text-on-surface">Leave Approval Desk</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Review, approve, or reject student and staff leave applications with attendance exemption synchronization.</p>
      </div>
    </div>

    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 mb-4">
      <a href="<?php echo site_url('leave/approval?status=Pending'); ?>" class="px-4 py-2 rounded-lg font-semibold text-xs <?php echo (($filters['status'] ?? 'Pending') === 'Pending') ? 'bg-secondary text-on-secondary shadow-sm' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high'; ?>">
        Pending Review
      </a>
      <a href="<?php echo site_url('leave/approval?status=Clarification Required'); ?>" class="px-4 py-2 rounded-lg font-semibold text-xs <?php echo (($filters['status'] ?? '') === 'Clarification Required') ? 'bg-secondary text-on-secondary shadow-sm' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high'; ?>">
        Clarification Awaited
      </a>
      <a href="<?php echo site_url('leave/approval?status=Approved'); ?>" class="px-4 py-2 rounded-lg font-semibold text-xs <?php echo (($filters['status'] ?? '') === 'Approved') ? 'bg-secondary text-on-secondary shadow-sm' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high'; ?>">
        Approved Leaves
      </a>
    </div>

    <!-- Approvals Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Applications Awaiting Action (<?php echo count($applications); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Applicant</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Scope</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Leave Type</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Duration</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Reason & Doc</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Decision</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($applications)): ?>
              <tr><td colspan="7" class="px-4 py-8 text-center text-on-surface-variant">No leave requests currently under this view.</td></tr>
            <?php else: ?>
              <?php foreach ($applications as $a): ?>
                <?php
                  $name = ($a->applicant_type === 'Student') ? $a->first_name . ' ' . $a->last_name : $a->staff_name;
                  $scope = ($a->applicant_type === 'Student') ? ($a->class_name . ' - ' . $a->section_name) : ($a->designation_name ?: 'Faculty');
                ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap">
                    <strong class="text-on-surface block"><?php echo html_escape($name); ?></strong>
                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-primary-container text-on-primary-container"><?php echo $a->applicant_type; ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-[13px] text-on-surface font-medium">
                    <?php echo html_escape($scope); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-surface-container-high text-on-surface-variant font-mono">
                      <?php echo html_escape($a->type_name); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[12px] text-on-surface">
                    <?php echo date('d M', strtotime($a->from_date)) . ' to ' . date('d M Y', strtotime($a->to_date)); ?>
                    <span class="block text-[11px] font-bold text-primary"><?php echo $a->duration_days; ?> day(s)</span>
                  </td>
                  <td class="px-4 py-3 text-[13px] text-on-surface-variant max-w-[220px]">
                    <p class="truncate"><?php echo html_escape($a->reason); ?></p>
                    <?php if ($a->attachment): ?>
                      <a href="<?php echo base_url('uploads/leaves/' . $a->attachment); ?>" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-primary font-semibold hover:underline mt-0.5">
                        <span class="material-symbols-outlined text-[14px]">attach_file</span>View Doc
                      </a>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900">
                      <?php echo html_escape($a->status); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <?php if ($a->status === 'Pending' || $a->status === 'Clarification Required'): ?>
                      <div class="flex items-center justify-center gap-1">
                        <a href="<?php echo site_url('leave/approve/' . $a->application_id); ?>" class="px-2.5 py-1 rounded bg-secondary-container text-on-secondary-container hover:bg-secondary/30 text-xs font-semibold">
                          Approve
                        </a>
                        <button onclick="openRejectModal(<?php echo $a->application_id; ?>)" class="px-2.5 py-1 rounded bg-error-container text-error hover:bg-error/20 text-xs font-semibold cursor-pointer">
                          Reject
                        </button>
                      </div>
                    <?php else: ?>
                      <span class="text-xs text-on-surface-variant font-mono">Processed</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Reject Modal Dialog -->
    <dialog id="rejectModal" class="p-0 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-3 w-full max-w-md backdrop:bg-scrim/40">
      <div class="p-6 space-y-4">
        <h3 class="font-headline-md text-title-md font-bold text-on-surface">Reject Leave Request</h3>
        <p class="text-xs text-on-surface-variant">Please provide a mandatory reason for rejecting this leave application.</p>

        <form id="rejectForm" method="post" action="">
          <div class="space-y-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Rejection Reason *</label>
              <textarea name="rejection_reason" rows="3" required placeholder="e.g. Exam dates conflict / Insufficient medical proof..." class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-outline-variant/50">
              <button type="button" onclick="document.getElementById('rejectModal').close()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-label-md">Cancel</button>
              <button type="submit" class="px-5 py-2 rounded-lg bg-error text-white text-label-md font-semibold hover:bg-error/90 shadow-sm">Confirm Rejection</button>
            </div>
          </div>
        </form>
      </div>
    </dialog>

    <script>
      function openRejectModal(appId) {
        document.getElementById('rejectForm').action = "<?php echo site_url('leave/reject/'); ?>" + appId;
        document.getElementById('rejectModal').showModal();
      }
    </script>
