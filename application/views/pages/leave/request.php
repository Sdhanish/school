<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('error')): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-error-container text-on-error-container text-body-md font-medium flex items-center gap-2 border border-error/20">
        <span class="material-symbols-outlined text-[20px] text-error">error</span>
        <?php echo html_escape($this->session->flashdata('error')); ?>
      </div>
    <?php endif; ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Apply for Leave</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Submit student absence applications or staff leave requests with automated working days calculation.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('leave/dashboard'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>Back to Dashboard
        </a>
      </div>
    </div>

    <!-- Universal Leave Request Form -->
    <?php echo form_open_multipart('leave/request'); ?>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        
        <!-- Left 2 Cols: Applicant & Dates -->
        <div class="lg:col-span-2 space-y-6">
          <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
            <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2 pb-2 border-b border-outline-variant/50">
              <span class="material-symbols-outlined text-primary text-[22px]">person</span>Applicant Information
            </h3>

            <!-- Applicant Type Radio Switch -->
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-2 font-medium">Applicant Category *</label>
              <div class="grid grid-cols-2 gap-4">
                <label id="lblStudent" class="flex items-center gap-3 p-3.5 rounded-xl border border-primary bg-primary-container/20 cursor-pointer">
                  <input type="radio" name="applicant_type" value="Student" checked onchange="toggleApplicantType(this.value)" class="w-4 h-4 text-primary focus:ring-primary"/>
                  <div>
                    <strong class="text-on-surface text-body-md block font-bold">Student Absence</strong>
                    <span class="text-[11px] text-on-surface-variant">Class absence & medical exemptions</span>
                  </div>
                </label>

                <label id="lblStaff" class="flex items-center gap-3 p-3.5 rounded-xl border border-outline-variant bg-surface-container-low cursor-pointer">
                  <input type="radio" name="applicant_type" value="Staff" onchange="toggleApplicantType(this.value)" class="w-4 h-4 text-secondary focus:ring-secondary"/>
                  <div>
                    <strong class="text-on-surface text-body-md block font-bold">Staff / Faculty Leave</strong>
                    <span class="text-[11px] text-on-surface-variant">Casual, sick, or earned leave</span>
                  </div>
                </label>
              </div>
            </div>

            <!-- Student Selector -->
            <div id="secStudent">
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Select Student *</label>
              <select name="student_id" id="selectStudent" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="">-- Choose Student --</option>
                <?php foreach ($students as $st): ?>
                  <option value="<?php echo $st->student_id; ?>">
                    <?php echo html_escape($st->first_name . ' ' . $st->last_name . ' (' . ($st->admission_number ?? $st->admission_no ?? '') . ' - ' . ($st->class_name ?? '') . ' ' . ($st->section_name ?? '') . ')'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Staff Selector -->
            <div id="secStaff" class="hidden">
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Select Staff Member *</label>
              <select name="staff_id" id="selectStaff" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="">-- Choose Faculty / Staff --</option>
                <?php foreach ($staff as $s): ?>
                  <option value="<?php echo $s->staff_id; ?>">
                    <?php echo html_escape($s->full_name . ' (' . $s->employee_code . ' . ')'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Leave Type *</label>
              <select name="leave_type_id" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <?php foreach ($leave_types as $lt): ?>
                  <option value="<?php echo $lt->type_id; ?>">
                    <?php echo html_escape($lt->type_name . ' (' . $lt->type_code . ') - Max ' . (int)$lt->max_days . ' Days'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
            <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2 pb-2 border-b border-outline-variant/50">
              <span class="material-symbols-outlined text-primary text-[22px]">calendar_month</span>Schedule & Reason
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">From Date *</label>
                <input type="date" name="from_date" id="fromDate" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">To Date *</label>
                <input type="date" name="to_date" id="toDate" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>
            </div>

            <!-- Half Day Switch -->
            <div class="p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/40 space-y-2">
              <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_half_day" id="halfDayCheck" value="1" onchange="toggleHalfDay(this.checked)" class="w-4 h-4 rounded text-secondary focus:ring-secondary"/>
                <span class="text-body-md text-on-surface font-semibold">Half-Day Leave (0.5 Day)</span>
              </label>

              <div id="halfDayOptions" class="hidden grid grid-cols-2 gap-3 pt-2">
                <label class="flex items-center gap-2 p-2 rounded-lg border border-outline-variant bg-surface-container-lowest cursor-pointer text-xs">
                  <input type="radio" name="half_day_type" value="First Half" checked class="w-3.5 h-3.5 text-secondary"/>
                  <span>First Half (Morning Session)</span>
                </label>
                <label class="flex items-center gap-2 p-2 rounded-lg border border-outline-variant bg-surface-container-lowest cursor-pointer text-xs">
                  <input type="radio" name="half_day_type" value="Second Half" class="w-3.5 h-3.5 text-secondary"/>
                  <span>Second Half (Afternoon Session)</span>
                </label>
              </div>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Reason for Absence *</label>
              <textarea name="reason" rows="4" required placeholder="Detailed reason for leave request..." class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
            </div>
          </div>
        </div>

        <!-- Right 1 Col: Attachments & Submit -->
        <div class="space-y-6">
          <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
            <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2 pb-2 border-b border-outline-variant/50">
              <span class="material-symbols-outlined text-primary text-[22px]">attach_file</span>Supporting Material
            </h3>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Medical Certificate / Proof</label>
              <input type="file" name="attachment" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-secondary-container file:text-on-secondary-container hover:file:bg-secondary file:cursor-pointer"/>
              <span class="text-[11px] text-on-surface-variant block mt-1">Accepted: PDF, PNG, JPG (Max 10MB)</span>
            </div>
          </div>

          <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
            <button type="submit" class="w-full py-3 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer flex items-center justify-center gap-2">
              <span class="material-symbols-outlined text-[18px]">send</span>Submit Leave Request
            </button>
          </div>
        </div>
      </div>
    <?php echo form_close(); ?>

    <script>
      function toggleApplicantType(val) {
        const secSt = document.getElementById('secStudent');
        const secSf = document.getElementById('secStaff');
        const selSt = document.getElementById('selectStudent');
        const selSf = document.getElementById('selectStaff');
        const lblSt = document.getElementById('lblStudent');
        const lblSf = document.getElementById('lblStaff');

        if (val === 'Student') {
          secSt.classList.remove('hidden');
          secSf.classList.add('hidden');
          selSt.setAttribute('required', 'required');
          selSf.removeAttribute('required');
          lblSt.className = "flex items-center gap-3 p-3.5 rounded-xl border border-primary bg-primary-container/20 cursor-pointer";
          lblSf.className = "flex items-center gap-3 p-3.5 rounded-xl border border-outline-variant bg-surface-container-low cursor-pointer";
        } else {
          secSt.classList.add('hidden');
          secSf.classList.remove('hidden');
          selSf.setAttribute('required', 'required');
          selSt.removeAttribute('required');
          lblSf.className = "flex items-center gap-3 p-3.5 rounded-xl border border-secondary bg-secondary-container/20 cursor-pointer";
          lblSt.className = "flex items-center gap-3 p-3.5 rounded-xl border border-outline-variant bg-surface-container-low cursor-pointer";
        }
      }

      function toggleHalfDay(checked) {
        const opts = document.getElementById('halfDayOptions');
        const toD = document.getElementById('toDate');
        const fromD = document.getElementById('fromDate');
        if (checked) {
          opts.classList.remove('hidden');
          toD.value = fromD.value;
          toD.setAttribute('readonly', 'readonly');
        } else {
          opts.classList.add('hidden');
          toD.removeAttribute('readonly');
        }
      }
    </script>
