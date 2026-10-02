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
        <h2 class="font-headline-md text-headline-md text-on-surface">Marks Verification</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Review and approve submitted marksheets before result calculation and publishing.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('examinations/calculate'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">calculate</span>Proceed to Result Calculation
        </a>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('examinations/verification'); ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Exam</label>
          <select name="exam_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Exams</option>
            <?php foreach ($exams as $e): ?>
              <option value="<?php echo $e->exam_id; ?>" <?php echo ($filters['exam_id'] == $e->exam_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($e->exam_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Class</label>
          <select name="class_id" onchange="if(this.form.querySelector('[name=division_id]')) this.form.querySelector('[name=division_id]').value=''; this.form.submit();" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Classes</option>
            <?php foreach ($classes as $c): ?>
              <option value="<?php echo $c->class_id; ?>" <?php echo ($filters['class_id'] == $c->class_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($c->class_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Division</label>
          <select name="division_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Divisions</option>
            <?php foreach ($divisions as $s): ?>
              <option value="<?php echo $s->division_id; ?>" <?php echo (($filters['division_id'] ?? '') == $s->division_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($s->class_name . ' ' . $s->division_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Verification Status</label>
          <select name="status_filter" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Statuses</option>
            <option value="Submitted (Pending Verification)" <?php echo ($filters['status_filter'] === 'Submitted (Pending Verification)') ? 'selected' : ''; ?>>Submitted (Pending Verification)</option>
            <option value="Approved" <?php echo ($filters['status_filter'] === 'Approved') ? 'selected' : ''; ?>>Approved</option>
            <option value="Rejected / Correction Required" <?php echo ($filters['status_filter'] === 'Rejected / Correction Required') ? 'selected' : ''; ?>>Rejected / Correction Required</option>
            <option value="Draft (Incomplete)" <?php echo ($filters['status_filter'] === 'Draft (Incomplete)') ? 'selected' : ''; ?>>Draft (Incomplete)</option>
          </select>
        </div>
      </form>
    </div>

    <!-- Verification Queue Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Marksheets for Verification (<?php echo count($marksheets); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Class & Division</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Subject</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Exam</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Entered / Total</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Submitted By</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($marksheets)): ?>
              <tr><td colspan="7" class="px-4 py-8 text-center text-on-surface-variant text-body-md">No marksheets found matching verification criteria.</td></tr>
            <?php else: ?>
              <?php foreach ($marksheets as $m): ?>
                <?php
                  $badgeClass = 'bg-surface-container-high text-on-surface-variant';
                  if ($m->verification_status === 'Approved') $badgeClass = 'bg-secondary-container text-on-secondary-container';
                  elseif ($m->verification_status === 'Submitted (Pending Verification)') $badgeClass = 'bg-primary text-white';
                  elseif ($m->verification_status === 'Rejected / Correction Required') $badgeClass = 'bg-error-container text-on-error-container';
                ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap font-medium text-on-surface">
                    <?php echo html_escape($m->class_name . ' ' . ($m->division_name ?: '')); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-bold text-on-surface">
                    <?php echo html_escape($m->subject_name); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-body-md text-on-surface-variant">
                    <?php echo html_escape($m->exam_name); ?>
                  </td>
                  <td class="px-4 py-3 text-center font-mono font-medium text-body-md text-on-surface">
                    <?php echo $m->marks_entered_count; ?> / <?php echo $m->total_students; ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-body-md text-on-surface-variant">
                    <?php echo html_escape($m->entered_by_name ?: 'Teacher'); ?>
                    <?php if ($m->latest_submitted_at): ?>
                      <span class="text-[11px] block font-mono"><?php echo date('d M, h:i A', strtotime($m->latest_submitted_at)); ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold <?php echo $badgeClass; ?>">
                      <?php echo html_escape($m->verification_status); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <div class="flex items-center justify-center gap-1.5">
                      <!-- View / Edit Marks -->
                      <a href="<?php echo site_url('examinations/marks_entry?schedule_id=' . $m->schedule_id); ?>" class="px-3 py-1.5 rounded-lg bg-surface-container-high hover:bg-primary hover:text-on-primary text-[12px] font-medium transition-colors" title="Review Marks">
                        Review
                      </a>

                      <!-- Quick Approve -->
                      <?php echo form_open('examinations/verification', array('class' => 'inline', 'onsubmit' => 'return confirm("Approve marksheet for ' . html_escape($m->subject_name) . '?");')); ?>
                        <input type="hidden" name="action" value="approve"/>
                        <input type="hidden" name="schedule_id" value="<?php echo $m->schedule_id; ?>"/>
                        <button type="submit" class="p-1.5 rounded-lg hover:bg-secondary-container text-secondary transition-colors cursor-pointer" title="Approve">
                          <span class="material-symbols-outlined text-[18px]">check_circle</span>
                        </button>
                      <?php echo form_close(); ?>

                      <!-- Reject / Request Correction Modal Trigger -->
                      <button type="button" onclick="openRejectModal(<?php echo $m->schedule_id; ?>, '<?php echo html_escape($m->subject_name); ?>')" class="p-1.5 rounded-lg hover:bg-error-container text-error transition-colors cursor-pointer" title="Reject / Request Correction">
                        <span class="material-symbols-outlined text-[18px]">cancel</span>
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- REJECT / CORRECTION MODAL -->
    <div id="reject-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
      <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant max-w-md w-full p-6 elevation-3 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-lg text-error font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-[22px]">warning</span>Request Marksheet Correction
          </h3>
          <button onclick="closeRejectModal()" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('examinations/verification', array('class' => 'space-y-4')); ?>
          <input type="hidden" name="action" value="reject"/>
          <input type="hidden" name="schedule_id" id="reject-sched-id" value="0"/>

          <p class="text-body-md text-on-surface">You are rejecting marks for <strong id="reject-subj-name" class="text-primary">Subject</strong>. Please specify the feedback for the subject teacher:</p>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Rejection / Correction Reason *</label>
            <textarea name="rejection_reason" required rows="3" placeholder="e.g. Please re-check roll # 12 maths marks or recalculate practicals..." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-error/20 focus:border-error"></textarea>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-outline-variant/50">
            <button type="button" onclick="closeRejectModal()" class="px-4 py-2 rounded-lg bg-surface-container-high text-on-surface text-label-md font-medium hover:bg-surface-container-highest cursor-pointer">
              Cancel
            </button>
            <button type="submit" class="px-5 py-2 rounded-lg bg-error text-on-error text-label-md font-semibold hover:bg-error/90 cursor-pointer">
              Send Correction Request
            </button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <script>
      function openRejectModal(id, subj) {
        document.getElementById('reject-sched-id').value = id;
        document.getElementById('reject-subj-name').textContent = subj;
        var modal = document.getElementById('reject-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
      }

      function closeRejectModal() {
        var modal = document.getElementById('reject-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      }
    </script>
