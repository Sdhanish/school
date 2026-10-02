<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Transfer / TC Management</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Issue official School Transfer Certificates (TC), manage student leaving records, and print certificates.</p>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <button onclick="document.getElementById('issue-tc-modal').classList.remove('hidden')" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">post_add</span>Issue New TC
        </button>
      </div>
    </div>

    <!-- Issued Transfer Certificates Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/50">
        <h3 class="font-headline-md text-headline-md text-on-surface">Issued Transfer Certificates</h3>
        <span class="text-label-md text-on-surface-variant font-medium"><?php echo count($transfers); ?> certificate(s) issued</span>
      </div>
      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60">
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">TC Number</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Student Name</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Admission No.</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Last Class</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Issue Date</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Reason for Leaving</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Status</th>
              <th class="text-right px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($transfers)): ?>
              <tr><td colspan="8" class="px-4 py-8 text-center text-body-md text-on-surface-variant">No Transfer Certificates issued yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($transfers as $tr): ?>
              <tr class='hover:bg-surface-container-low transition-colors'>
                <td class="px-4 py-3 text-body-md font-mono text-primary font-bold whitespace-nowrap"><?php echo html_escape($tr->tc_number); ?></td>
                <td class="px-4 py-3 text-body-md text-on-surface font-medium whitespace-nowrap">
                  <a href="<?php echo site_url('students/profile/' . $tr->student_id); ?>" class="hover:underline"><?php echo html_escape($tr->first_name . ' ' . $tr->last_name); ?></a>
                </td>
                <td class="px-4 py-3 text-body-md font-mono text-on-surface-variant whitespace-nowrap"><?php echo html_escape($tr->admission_number); ?></td>
                <td class="px-4 py-3 text-body-md text-on-surface whitespace-nowrap"><?php echo html_escape($tr->prev_class ?: '—'); ?></td>
                <td class="px-4 py-3 text-body-md text-on-surface whitespace-nowrap"><?php echo date('d M Y', strtotime($tr->transfer_date)); ?></td>
                <td class="px-4 py-3 text-body-md text-on-surface-variant"><?php echo html_escape($tr->reason); ?></td>
                <td class="px-4 py-3 text-body-md whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-secondary-container text-on-secondary-container">Issued</span>
                </td>
                <td class="px-4 py-3 text-body-md text-right whitespace-nowrap">
                  <a href="<?php echo site_url('students/tc/' . $tr->transfer_id); ?>" class="px-3 py-1 rounded-lg bg-primary text-on-primary text-label-md hover:bg-primary/90 transition-colors inline-flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">print</span>Print TC</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Issue TC Modal -->
    <div id="issue-tc-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden">
      <div class="elevation-3 rounded-2xl bg-surface-container-lowest border border-outline-variant w-full max-w-lg">
        <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant">
          <h3 class="font-headline-md text-headline-md text-on-surface">Issue Transfer Certificate</h3>
          <button onclick="document.getElementById('issue-tc-modal').classList.add('hidden')" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant"><span class="material-symbols-outlined">close</span></button>
        </div>
        <?php echo form_open('students/transfers', array('class' => 'p-6 space-y-4')); ?>
          <div>
            <label class="block text-label-md mb-1">Select Student *</label>
            <select name="student_id" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest">
              <?php foreach ($students as $st): ?>
                <option value="<?php echo $st->student_id; ?>"><?php echo html_escape($st->first_name . ' ' . $st->last_name . ' (' . $st->admission_number . ' - ' . $st->class_name . ')'); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="block text-label-md mb-1">Transfer / Leaving Date *</label>
            <input type="date" name="transfer_date" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest"/>
          </div>
          <div>
            <label class="block text-label-md mb-1">Reason for Leaving *</label>
            <select name="reason" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest">
              <option value="Parent Relocation / Job Transfer">Parent Relocation / Job Transfer</option>
              <option value="Completed Course / Passed Highest Grade">Completed Course / Passed Highest Grade</option>
              <option value="Parent Request">Parent Request</option>
              <option value="Admission to Higher Educational Institution">Admission to Higher Educational Institution</option>
              <option value="Other">Other</option>
            </select>
          </div>
          <div>
            <label class="block text-label-md mb-1">General Conduct & Character</label>
            <select name="conduct" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest">
              <option value="Exemplary">Exemplary</option>
              <option value="Good">Good</option>
              <option value="Satisfactory">Satisfactory</option>
            </select>
          </div>
          <div>
            <label class="block text-label-md mb-1">Remarks / Note</label>
            <input type="text" name="remarks" placeholder="e.g. All library and laboratory dues cleared." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest"/>
          </div>
          <div class="flex justify-end gap-2 pt-4 border-t border-outline-variant">
            <button type="button" onclick="document.getElementById('issue-tc-modal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high">Cancel</button>
            <button type="submit" class="px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant cursor-pointer">Generate & Issue TC</button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>
