<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Admission Management</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Review new admission applications, approve candidates, and assign classes and sections.</p>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <button onclick="document.getElementById('new-admission-modal').classList.remove('hidden')" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">how_to_reg</span>New Application
        </button>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="flex flex-col md:flex-row gap-3 mb-4 flex-wrap">
      <div class="relative flex-1 min-w-[220px]">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant/60 text-[20px]">search</span>
        <input type="text" placeholder="Search applicant, app no, parent..." value="<?php echo html_escape($this->input->get('search')); ?>" onkeydown="if(event.key==='Enter') window.location.href='<?php echo site_url('students/admissions'); ?>?search=' + encodeURIComponent(this.value)" class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary transition-colors"/>
      </div>
      <select onchange="applyFilter('status', this.value)" class="px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md text-on-surface-variant">
        <option value="">All Statuses</option>
        <option value="Pending" <?php echo ($this->input->get('status') === 'Pending') ? 'selected' : ''; ?>>Pending</option>
        <option value="Approved" <?php echo ($this->input->get('status') === 'Approved') ? 'selected' : ''; ?>>Approved</option>
        <option value="Admitted" <?php echo ($this->input->get('status') === 'Admitted') ? 'selected' : ''; ?>>Admitted</option>
        <option value="Rejected" <?php echo ($this->input->get('status') === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
      </select>
      <select onchange="applyFilter('class_id', this.value)" class="px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md text-on-surface-variant">
        <option value="">All Classes</option>
        <?php foreach ($classes as $cls): ?>
          <option value="<?php echo $cls->class_id; ?>" <?php echo ($this->input->get('class_id') == $cls->class_id) ? 'selected' : ''; ?>><?php echo html_escape($cls->class_name); ?></option>
        <?php endforeach; ?>
      </select>
      <a href="<?php echo site_url('students/admissions'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors"><span class="material-symbols-outlined text-[18px]">restart_alt</span>Reset</a>
    </div>

    <!-- Admissions Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60">
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">App No.</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Applicant</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Applied Class</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Gender</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Guardian & Contact</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Date</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Status</th>
              <th class="text-right px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($admissions)): ?>
              <tr>
                <td colspan="8" class="px-4 py-8 text-center text-body-md text-on-surface-variant">No admission applications found.</td>
              </tr>
            <?php endif; ?>
            <?php foreach ($admissions as $adm): ?>
              <?php
                $badge = 'bg-surface-container-high text-on-surface-variant';
                if ($adm->status === 'Pending') $badge = 'bg-tertiary-container/30 text-tertiary font-semibold';
                if ($adm->status === 'Approved') $badge = 'bg-primary-fixed/40 text-primary font-semibold';
                if ($adm->status === 'Admitted') $badge = 'bg-secondary-container text-on-secondary-container font-semibold';
                if ($adm->status === 'Rejected') $badge = 'bg-error-container/30 text-error font-semibold';
              ?>
              <tr class='hover:bg-surface-container-low transition-colors'>
                <td class="px-4 py-3 text-body-md font-mono text-primary font-medium whitespace-nowrap"><?php echo html_escape($adm->application_number); ?></td>
                <td class="px-4 py-3 text-body-md text-on-surface font-medium whitespace-nowrap">
                  <div><?php echo html_escape($adm->first_name . ' ' . $adm->last_name); ?></div>
                  <div class="text-[12px] text-on-surface-variant">DOB: <?php echo date('d M Y', strtotime($adm->date_of_birth)); ?></div>
                </td>
                <td class="px-4 py-3 text-body-md text-on-surface whitespace-nowrap"><?php echo html_escape($adm->class_name); ?></td>
                <td class="px-4 py-3 text-body-md text-on-surface whitespace-nowrap"><?php echo html_escape($adm->gender); ?></td>
                <td class="px-4 py-3 text-body-md text-on-surface whitespace-nowrap">
                  <div><?php echo html_escape($adm->guardian_name); ?></div>
                  <div class="text-[12px] text-on-surface-variant"><?php echo html_escape($adm->guardian_phone); ?></div>
                </td>
                <td class="px-4 py-3 text-body-md text-on-surface whitespace-nowrap"><?php echo date('d M Y', strtotime($adm->application_date)); ?></td>
                <td class="px-4 py-3 text-body-md whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] <?php echo $badge; ?>"><?php echo html_escape($adm->status); ?></span>
                </td>
                <td class="px-4 py-3 text-body-md text-right whitespace-nowrap">
                  <?php if ($adm->status === 'Pending'): ?>
                    <?php echo form_open('students/admissions', array('class' => 'inline')); ?>
                      <input type="hidden" name="action" value="update_status"/>
                      <input type="hidden" name="admission_id" value="<?php echo $adm->admission_id; ?>"/>
                      <input type="hidden" name="status" value="Approved"/>
                      <button type="submit" class="px-2.5 py-1 rounded bg-primary text-on-primary text-label-md hover:bg-primary/90 transition-colors cursor-pointer">Approve</button>
                    <?php echo form_close(); ?>
                    <?php echo form_open('students/admissions', array('class' => 'inline ml-1')); ?>
                      <input type="hidden" name="action" value="update_status"/>
                      <input type="hidden" name="admission_id" value="<?php echo $adm->admission_id; ?>"/>
                      <input type="hidden" name="status" value="Rejected"/>
                      <button type="submit" class="px-2.5 py-1 rounded border border-outline-variant text-error hover:bg-error-container/20 transition-colors cursor-pointer">Reject</button>
                    <?php echo form_close(); ?>
                  <?php elseif ($adm->status === 'Approved'): ?>
                    <button onclick="openAdmitModal(<?php echo $adm->admission_id; ?>, '<?php echo html_escape(addslashes($adm->first_name . ' ' . $adm->last_name)); ?>', <?php echo $adm->class_id; ?>)" class="px-3 py-1 rounded bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors cursor-pointer inline-flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">check_circle</span>Admit Student</button>
                  <?php elseif ($adm->status === 'Admitted' && !empty($adm->student_id)): ?>
                    <a href="<?php echo site_url('students/profile/' . $adm->student_id); ?>" class="text-primary font-medium hover:underline text-label-md inline-flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">person</span>View Student</a>
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
        <span>Total <?php echo count($admissions); ?> application(s)</span>
      </div>
    </div>

    <!-- Modal 1: New Admission Application -->
    <div id="new-admission-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden">
      <div class="elevation-3 rounded-2xl bg-surface-container-lowest border border-outline-variant w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant">
          <h3 class="font-headline-md text-headline-md text-on-surface">New Admission Application</h3>
          <button onclick="document.getElementById('new-admission-modal').classList.add('hidden')" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant"><span class="material-symbols-outlined">close</span></button>
        </div>
        <?php echo form_open('students/admissions', array('class' => 'p-6')); ?>
          <input type="hidden" name="action" value="new_admission"/>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-body-md mb-4">
            <div>
              <label class="block text-label-md mb-1">First Name *</label>
              <input type="text" name="first_name" required placeholder="e.g. Rahul" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest"/>
            </div>
            <div>
              <label class="block text-label-md mb-1">Last Name *</label>
              <input type="text" name="last_name" required placeholder="e.g. Varma" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest"/>
            </div>
            <div>
              <label class="block text-label-md mb-1">Gender</label>
              <select name="gender" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest">
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div>
              <label class="block text-label-md mb-1">Date of Birth</label>
              <input type="date" name="date_of_birth" value="2014-05-10" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest"/>
            </div>
            <div>
              <label class="block text-label-md mb-1">Blood Group</label>
              <select name="blood_group" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest">
                <option value="A+">A+</option><option value="A-">A-</option><option value="B+">B+</option><option value="B-">B-</option><option value="O+">O+</option><option value="O-">O-</option><option value="AB+">AB+</option><option value="AB-">AB-</option>
              </select>
            </div>
            <div>
              <label class="block text-label-md mb-1">Applying for Class *</label>
              <select name="class_id" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest">
                <?php foreach ($classes as $cls): ?>
                  <option value="<?php echo $cls->class_id; ?>"><?php echo html_escape($cls->class_name); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block text-label-md mb-1">Guardian Name *</label>
              <input type="text" name="guardian_name" required placeholder="Parent Name" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest"/>
            </div>
            <div>
              <label class="block text-label-md mb-1">Guardian Phone *</label>
              <input type="tel" id="adm_guardian_phone" name="guardian_phone" required data-phone-field="true" data-required="true" data-default-country="in" placeholder="98470 11223" class="phone-input w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest"/>
            </div>
            <div class="sm:col-span-2">
              <label class="block text-label-md mb-1">Residential Address</label>
              <input type="text" name="address" placeholder="City, District, PIN" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest"/>
            </div>
          </div>
          <div class="flex justify-end gap-2 pt-4 border-t border-outline-variant">
            <button type="button" onclick="document.getElementById('new-admission-modal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high">Cancel</button>
            <button type="submit" class="px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant cursor-pointer">Submit Application</button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <!-- Modal 2: Finalize Admission & Assign Section -->
    <div id="admit-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden">
      <div class="elevation-3 rounded-2xl bg-surface-container-lowest border border-outline-variant w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant">
          <h3 class="font-headline-md text-headline-md text-on-surface">Admit & Register Student</h3>
          <button onclick="document.getElementById('admit-modal').classList.add('hidden')" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant"><span class="material-symbols-outlined">close</span></button>
        </div>
        <?php echo form_open('students/admissions', array('class' => 'p-6')); ?>
          <input type="hidden" name="action" value="admit"/>
          <input type="hidden" name="admission_id" id="admit_modal_id"/>
          <p class="text-body-md text-on-surface mb-4">Assign section and roll number to finalize admission for <strong id="admit_student_name"></strong>.</p>
          <div class="space-y-4 mb-4">
            <div>
              <label class="block text-label-md mb-1">Assign Division *</label>
              <select name="division_id" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest">
                <?php foreach ($sections as $sec): ?>
                  <option value="<?php echo $sec->section_id; ?>"><?php echo html_escape($sec->class_name . ' - Division ' . $sec->section_name); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block text-label-md mb-1">Roll Number (Optional)</label>
              <input type="text" name="roll_number" placeholder="e.g. 18" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest"/>
            </div>
          </div>
          <div class="flex justify-end gap-2 pt-4 border-t border-outline-variant">
            <button type="button" onclick="document.getElementById('admit-modal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high">Cancel</button>
            <button type="submit" class="px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant cursor-pointer">Admit & Create Profile</button>
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
      function openAdmitModal(id, name, classId) {
        document.getElementById('admit_modal_id').value = id;
        document.getElementById('admit_student_name').textContent = name;
        document.getElementById('admit-modal').classList.remove('hidden');
      }
      document.addEventListener('DOMContentLoaded', function() {
        if (window.PhoneManager) {
          window.PhoneManager.init('#new-admission-modal');
        }
      });
    </script>
