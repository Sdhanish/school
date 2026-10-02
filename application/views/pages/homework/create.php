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
        <h2 class="font-headline-md text-headline-md text-on-surface">Create New Assignment</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Configure assignment details, instructions, maximum marks, submission options, and upload reference materials.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('homework/assignments'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>Back to List
        </a>
      </div>
    </div>

    <!-- Main Creation Form -->
    <?php echo form_open_multipart('homework/create'); ?>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        
        <!-- Left: Basic & Academic Information (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
          
          <!-- Division 1: Basic Information -->
          <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
            <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2 pb-2 border-b border-outline-variant/50">
              <span class="material-symbols-outlined text-primary text-[22px]">info</span>Assignment Information
            </h3>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Assignment Title *</label>
              <input type="text" name="title" required placeholder="e.g. Chapter 4 Quadratic Equations Problem Set" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary font-medium"/>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Assignment Type *</label>
                <select name="assignment_type_id" required class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <?php foreach ($types as $t): ?>
                    <option value="<?php echo $t->type_id; ?>"><?php echo html_escape($t->type_name); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Maximum Marks *</label>
                <input type="number" step="0.5" min="0" max="1000" name="max_marks" value="20.00" required class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Description / Overview</label>
              <textarea name="description" rows="3" placeholder="Brief summary of what this assignment covers..." class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Detailed Instructions for Students</label>
              <textarea name="instructions" rows="4" placeholder="Detailed step-by-step instructions, submission guidelines, reference page numbers..." class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
            </div>
          </div>

          <!-- Division 2: Attachment Materials -->
          <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
            <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2 pb-2 border-b border-outline-variant/50">
              <span class="material-symbols-outlined text-secondary text-[22px]">attach_file</span>Reference Attachments / Question Papers
            </h3>
            
            <p class="text-[13px] text-on-surface-variant">Upload worksheets, question papers, or reference PDFs (Allowed: PDF, DOC, DOCX, JPG, PNG, ZIP - Max 10MB each).</p>

            <input type="file" name="attachments[]" multiple class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-label-md file:font-semibold file:bg-secondary-container file:text-on-secondary-container hover:file:bg-secondary hover:file:text-on-secondary file:cursor-pointer"/>
          </div>
        </div>

        <!-- Right: Academic Targeting & Submission Settings (1 Col) -->
        <div class="space-y-6">
          
          <!-- Targeting & Class Allocation -->
          <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
            <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2 pb-2 border-b border-outline-variant/50">
              <span class="material-symbols-outlined text-primary text-[22px]">school</span>Academic Allocation
            </h3>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Target Class *</label>
              <select name="class_id" id="sel-class" required class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <?php foreach ($classes as $c): ?>
                  <option value="<?php echo $c->class_id; ?>"><?php echo html_escape($c->class_name); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Target Division *</label>
              <select name="division_id" id="sel-section" required class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="1">Division A</option>
                <option value="2">Division B</option>
              </select>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Subject *</label>
              <select name="subject_id" required class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <?php foreach ($subjects as $sub): ?>
                  <option value="<?php echo $sub->subject_id; ?>"><?php echo html_escape($sub->subject_name . ' (' . $sub->subject_code . ')'); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Faculty / Teacher *</label>
              <select name="teacher_id" required class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <?php foreach ($teachers as $t): ?>
                  <option value="<?php echo $t->staff_id; ?>"><?php echo html_escape($t->full_name); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Schedule & Deadlines -->
          <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
            <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2 pb-2 border-b border-outline-variant/50">
              <span class="material-symbols-outlined text-primary text-[22px]">calendar_today</span>Schedule & Deadline
            </h3>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Assigned Date *</label>
              <input type="date" name="assigned_date" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Due Date *</label>
                <input type="date" name="due_date" value="<?php echo date('Y-m-d', strtotime('+3 days')); ?>" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Due Time</label>
                <input type="time" name="due_time" value="23:59" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>
            </div>
          </div>

          <!-- Submission Options Toggles -->
          <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-3">
            <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2 pb-2 border-b border-outline-variant/50">
              <span class="material-symbols-outlined text-primary text-[22px]">tune</span>Submission Controls
            </h3>

            <label class="flex items-center justify-between text-body-md text-on-surface cursor-pointer">
              <span>Allow Text Answer</span>
              <input type="checkbox" name="allow_text_submission" value="1" checked class="w-4 h-4 rounded text-secondary focus:ring-secondary"/>
            </label>

            <label class="flex items-center justify-between text-body-md text-on-surface cursor-pointer">
              <span>Allow File Uploads</span>
              <input type="checkbox" name="allow_file_submission" value="1" checked class="w-4 h-4 rounded text-secondary focus:ring-secondary"/>
            </label>

            <label class="flex items-center justify-between text-body-md text-on-surface cursor-pointer">
              <span>Allow Multiple Files</span>
              <input type="checkbox" name="allow_multiple_files" value="1" class="w-4 h-4 rounded text-secondary focus:ring-secondary"/>
            </label>

            <label class="flex items-center justify-between text-body-md text-on-surface cursor-pointer">
              <span>Allow Resubmission</span>
              <input type="checkbox" name="allow_resubmission" value="1" checked class="w-4 h-4 rounded text-secondary focus:ring-secondary"/>
            </label>

            <label class="flex items-center justify-between text-body-md text-on-surface cursor-pointer">
              <span>Accept Late Submissions</span>
              <input type="checkbox" name="allow_late_submission" value="1" checked class="w-4 h-4 rounded text-secondary focus:ring-secondary"/>
            </label>
          </div>

          <!-- Submit Actions -->
          <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex items-center justify-between gap-3">
            <button type="submit" name="submit_action" value="draft" class="w-1/2 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-low text-label-md font-medium hover:bg-surface-container-high transition-colors cursor-pointer">
              Save as Draft
            </button>
            <button type="submit" name="submit_action" value="publish" class="w-1/2 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
              Publish Now
            </button>
          </div>

        </div>
      </div>
    <?php echo form_close(); ?>
