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
        <h2 class="font-headline-md text-headline-md text-on-surface">Result Publishing & Locking</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Publish finalized results for student/parent visibility and lock examination marksheets against tampering.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('examinations/results'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">verified</span>View All Results
        </a>
      </div>
    </div>

    <!-- Publishing Status Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Examination Publishing Queue</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Examination</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Academic Year</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Calculated Records</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Publishing Status</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($exams)): ?>
              <tr><td colspan="5" class="px-4 py-8 text-center text-on-surface-variant text-body-md">No examinations configured.</td></tr>
            <?php else: ?>
              <?php foreach ($exams as $e): ?>
                <?php $isPub = ($e->status === 'Published'); ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap">
                    <div class="font-bold text-on-surface"><?php echo html_escape($e->exam_name); ?></div>
                    <div class="text-[12px] text-on-surface-variant"><?php echo html_escape($e->type_name); ?></div>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-body-md text-on-surface-variant">
                    <?php echo html_escape($e->year_name); ?>
                  </td>
                  <td class="px-4 py-3 text-center font-mono font-bold text-body-md text-primary whitespace-nowrap">
                    <?php echo $e->result_count; ?> Students
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[12px] font-bold <?php echo $isPub ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-900'; ?>">
                      <span class="material-symbols-outlined text-[16px]"><?php echo $isPub ? 'lock' : 'lock_open'; ?></span>
                      <?php echo $isPub ? 'Published & Locked' : 'Unpublished / Editable'; ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <?php if ($isPub): ?>
                      <!-- Unlock for correction modal trigger -->
                      <button type="button" onclick="openUnlockModal(<?php echo $e->exam_id; ?>, '<?php echo html_escape($e->exam_name); ?>')" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-amber-100 text-amber-900 text-[12px] font-semibold hover:bg-amber-200 transition-colors cursor-pointer">
                        <span class="material-symbols-outlined text-[16px]">lock_open</span>Unlock for Correction
                      </button>
                    <?php else: ?>
                      <!-- Publish button -->
                      <?php echo form_open('examinations/publishing', array('class' => 'inline', 'onsubmit' => 'return confirm("Publish and lock results for ' . html_escape($e->exam_name) . '?");')); ?>
                        <input type="hidden" name="action" value="publish"/>
                        <input type="hidden" name="exam_id" value="<?php echo $e->exam_id; ?>"/>
                        <button type="submit" class="inline-flex items-center gap-1 px-4 py-1.5 rounded-lg bg-secondary text-on-secondary text-[12px] font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
                          <span class="material-symbols-outlined text-[16px]">publish</span>Publish & Lock
                        </button>
                      <?php echo form_close(); ?>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Audit History Logs -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
          <span class="material-symbols-outlined text-primary text-[20px]">history</span>Examination Audit Log Trail
        </h3>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Timestamp</th>
              <th class="text-left px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">User</th>
              <th class="text-left px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Action</th>
              <th class="text-left px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase">Details</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/30">
            <?php if (empty($logs)): ?>
              <tr><td colspan="4" class="px-4 py-4 text-center text-on-surface-variant">No audit logs recorded yet.</td></tr>
            <?php else: ?>
              <?php foreach ($logs as $l): ?>
                <tr>
                  <td class="px-4 py-2.5 font-mono text-[12px] text-on-surface-variant whitespace-nowrap"><?php echo date('d M Y, h:i A', strtotime($l->created_at)); ?></td>
                  <td class="px-4 py-2.5 font-medium text-on-surface whitespace-nowrap"><?php echo html_escape($l->user_name ?: 'System'); ?></td>
                  <td class="px-4 py-2.5 whitespace-nowrap">
                    <span class="px-2 py-0.5 rounded text-[11px] font-mono font-bold bg-surface-container-high text-primary"><?php echo html_escape($l->action); ?></span>
                  </td>
                  <td class="px-4 py-2.5 text-on-surface-variant"><?php echo html_escape($l->details); ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- UNLOCK MODAL -->
    <div id="unlock-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
      <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant max-w-md w-full p-6 elevation-3 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-lg text-amber-900 font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-[22px]">lock_open</span>Unlock Results for Correction
          </h3>
          <button onclick="closeUnlockModal()" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('examinations/publishing', array('class' => 'space-y-4')); ?>
          <input type="hidden" name="action" value="unlock"/>
          <input type="hidden" name="exam_id" id="unlock-exam-id" value="0"/>

          <p class="text-body-md text-on-surface">Unlocking will allow teachers and exam controllers to re-edit marksheets for <strong id="unlock-exam-name" class="text-primary">Exam</strong>.</p>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Reason for Unlocking *</label>
            <textarea name="unlock_reason" required rows="3" placeholder="Provide reason for unlocking published results (logged in audit trail)..." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500"></textarea>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-outline-variant/50">
            <button type="button" onclick="closeUnlockModal()" class="px-4 py-2 rounded-lg bg-surface-container-high text-on-surface text-label-md font-medium hover:bg-surface-container-highest cursor-pointer">
              Cancel
            </button>
            <button type="submit" class="px-5 py-2 rounded-lg bg-amber-600 text-white text-label-md font-semibold hover:bg-amber-700 cursor-pointer">
              Unlock Exam
            </button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <script>
      function openUnlockModal(id, name) {
        document.getElementById('unlock-exam-id').value = id;
        document.getElementById('unlock-exam-name').textContent = name;
        var modal = document.getElementById('unlock-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
      }

      function closeUnlockModal() {
        var modal = document.getElementById('unlock-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      }
    </script>
