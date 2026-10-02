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
        <h2 class="font-headline-md text-headline-md text-on-surface">Generate Certificate</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Populate student records, dynamic template variables, and generate a new official certificate.</p>
      </div>
      <div class="flex items-center gap-2">
        <a href="<?php echo site_url('certificates/dashboard'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>Back to Dashboard
        </a>
      </div>
    </div>

    <!-- Certificate Generation Form -->
    <div class="max-w-3xl elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-6 mb-6">
      <?php echo form_open('certificates/generate'); ?>
        <?php if ($request): ?>
          <input type="hidden" name="request_id" value="<?php echo $request->request_id; ?>"/>
          <div class="mb-4 p-3 rounded-xl bg-primary-container text-on-primary-container text-body-md font-medium flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">info</span>
            Generating from Approved Request #<?php echo $request->request_id; ?> (<?php echo html_escape($request->reason); ?>)
          </div>
        <?php endif; ?>

        <div class="space-y-4">
          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Select Student *</label>
            <select name="student_id" id="selStudent" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
              <option value="">-- Choose Student --</option>
              <?php foreach ($students as $st): ?>
                <option value="<?php echo $st->student_id; ?>" <?php echo ($selected_student_id == $st->student_id) ? 'selected' : ''; ?>>
                  <?php echo html_escape($st->first_name . ' ' . $st->last_name . ' (' . $st->admission_number . ' - ' . $st->class_name . ' ' . $st->section_name . ')'); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Certificate Type *</label>
              <select name="certificate_type_id" id="selCertType" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <?php foreach ($types as $t): ?>
                  <option value="<?php echo $t->type_id; ?>" <?php echo ($selected_type_id == $t->type_id) ? 'selected' : ''; ?>>
                    <?php echo html_escape($t->type_name . ' (' . $t->prefix . ')'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Issue Date *</label>
              <input type="date" name="issue_date" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Select Template Design</label>
            <select name="template_id" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
              <option value="">-- Use Default Template for Type --</option>
              <?php foreach ($templates as $tmpl): ?>
                <option value="<?php echo $tmpl->template_id; ?>"><?php echo html_escape($tmpl->template_name . ' (' . $tmpl->type_code . ')'); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Transfer Certificate Specific Fields -->
          <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant/40 space-y-4">
            <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider block">Additional Details (For TC / Conduct / Study)</span>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Date of Leaving (TC)</label>
                <input type="date" name="date_of_leaving" value="<?php echo date('Y-m-d'); ?>" class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Reason for Leaving</label>
                <input type="text" name="reason_for_leaving" placeholder="e.g. Relocating / Course Completion" class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Attendance Summary (Days)</label>
                <input type="text" name="attendance_summary" value="192 / 210 Days (91.4%)" class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Conduct Appraisal</label>
                <input type="text" name="conduct_statement" value="Exemplary and Good" class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>
            </div>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Internal Remarks</label>
            <textarea name="remarks" rows="2" placeholder="Official remarks or reference notes..." class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
          </div>

          <div class="flex items-center justify-end gap-3 pt-3 border-t border-outline-variant/50">
            <a href="<?php echo site_url('certificates/dashboard'); ?>" class="px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-label-md">Cancel</a>
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
              <span class="material-symbols-outlined text-[18px]">verified</span>Generate & Preview Certificate
            </button>
          </div>
        </div>
      <?php echo form_close(); ?>
    </div>
