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
        <h2 class="font-headline-md text-headline-md text-on-surface">Teacher Evaluation & Grading Desk</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Review student answers, grade assignment performance, award letter grades, and add feedback remarks.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('homework/submission_detail/' . $submission->submission_id); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">visibility</span>View Submission
        </a>
      </div>
    </div>

    <!-- Review Form -->
    <?php echo form_open('homework/review/' . $submission->submission_id); ?>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        
        <!-- Left: Student Response & Attachments (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
          <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
              <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[22px]">description</span>Student Work (v<?php echo $submission->submission_version; ?>)
              </h3>
              <span class="text-xs font-mono text-on-surface-variant">Submitted: <?php echo date('d M Y, h:i A', strtotime($submission->submitted_at)); ?></span>
            </div>

            <!-- Student Answer -->
            <div>
              <span class="text-xs text-on-surface-variant uppercase font-semibold block mb-1">Answer / Working Notes:</span>
              <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant/40 text-body-md text-on-surface whitespace-pre-line leading-relaxed min-h-[140px]">
                <?php echo html_escape($submission->submitted_text ?: '— No text answer provided —'); ?>
              </div>
            </div>

            <!-- Uploaded Files -->
            <?php $files = $submission->submitted_files ? json_decode($submission->submitted_files, true) : []; ?>
            <?php if (!empty($files)): ?>
              <div class="pt-2">
                <span class="text-xs text-on-surface-variant uppercase font-semibold block mb-2">Student File Uploads:</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <?php foreach ($files as $f): ?>
                    <a href="<?php echo base_url('uploads/submissions/' . $f['file_name']); ?>" target="_blank" class="p-3 rounded-xl bg-surface-container-low border border-outline-variant/40 hover:bg-surface-container transition-colors flex items-center gap-2.5">
                      <span class="material-symbols-outlined text-secondary text-[22px]">file_open</span>
                      <div class="overflow-hidden">
                        <strong class="text-on-surface text-[13px] block truncate"><?php echo html_escape($f['orig_name']); ?></strong>
                        <span class="text-[11px] text-on-surface-variant font-mono"><?php echo round($f['file_size']); ?> KB</span>
                      </div>
                    </a>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Right: Evaluation & Grading Controls (1 Col) -->
        <div class="space-y-6">
          <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
            <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2 pb-2 border-b border-outline-variant/50">
              <span class="material-symbols-outlined text-secondary text-[22px]">grade</span>Evaluation & Marks
            </h3>

            <!-- Student Summary -->
            <div class="p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/40 text-body-md space-y-1">
              <div class="font-bold text-on-surface"><?php echo html_escape($submission->first_name . ' ' . $submission->last_name); ?></div>
              <div class="text-[12px] text-on-surface-variant"><?php echo html_escape($submission->class_name . ' ' . $submission->section_name); ?> • Adm: <?php echo html_escape($submission->admission_number); ?></div>
            </div>

            <!-- Marks Input -->
            <div>
              <div class="flex items-center justify-between mb-1">
                <label class="font-label-md text-label-md text-on-surface font-medium">Marks Obtained</label>
                <span class="text-xs font-mono font-bold text-on-surface-variant">Max: <?php echo $submission->max_marks; ?></span>
              </div>
              <input type="number" step="0.5" min="0" max="<?php echo $submission->max_marks; ?>" name="marks_obtained" id="marks-input" value="<?php echo $submission->marks_obtained !== NULL ? $submission->marks_obtained : ''; ?>" placeholder="Score (0 to <?php echo $submission->max_marks; ?>)" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono font-bold text-primary focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>

            <!-- Remarks -->
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Teacher Feedback Remarks</label>
              <textarea name="teacher_remarks" rows="3" placeholder="e.g. Excellent solution steps and clear diagrams..." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"><?php echo html_escape($submission->teacher_remarks); ?></textarea>
            </div>

            <!-- Return for correction box (hidden or shown based on action) -->
            <div id="correction-box" class="space-y-1">
              <label class="block font-label-md text-label-md text-error mb-1 font-medium">Correction Reason (if returning)</label>
              <input type="text" name="correction_reason" value="<?php echo html_escape($submission->correction_reason); ?>" placeholder="e.g. Incomplete proof for question 3" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-error/20 focus:border-error"/>
            </div>

            <!-- Action Buttons -->
            <div class="pt-3 border-t border-outline-variant/50 space-y-2">
              <button type="submit" name="review_action" value="complete" class="w-full py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer flex items-center justify-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">verified</span>Save & Mark as Reviewed
              </button>

              <button type="submit" name="review_action" value="return" onclick="return confirm('Return this submission to student for correction?');" class="w-full py-2.5 rounded-lg border border-error/40 text-error bg-error-container/20 text-label-md font-semibold hover:bg-error-container/40 transition-colors cursor-pointer flex items-center justify-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">undo</span>Return for Correction
              </button>
            </div>

          </div>
        </div>
      </div>
    <?php echo form_close(); ?>
