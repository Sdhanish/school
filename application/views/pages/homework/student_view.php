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
        <div class="flex items-center gap-2">
          <h2 class="font-headline-md text-headline-md text-on-surface"><?php echo html_escape($assignment->title); ?></h2>
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-primary-container text-on-primary-container">
            Student Portal
          </span>
        </div>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">
          <?php echo html_escape($assignment->subject_name); ?> • <?php echo html_escape($assignment->class_name . ' ' . $assignment->section_name); ?> • Due: <strong><?php echo date('d M Y', strtotime($assignment->due_date)); ?></strong>
        </p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('homework/details/' . $assignment->assignment_id); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>Back to Details
        </a>
      </div>
    </div>

    <!-- 2 Column Layout: Instructions vs Submission Form -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      
      <!-- Left: Assignment Instructions & Reference Materials -->
      <div class="space-y-6">
        <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2 pb-2 border-b border-outline-variant/50">
            <span class="material-symbols-outlined text-primary text-[22px]">assignment</span>Task Details & Instructions
          </h3>

          <?php if ($assignment->description): ?>
            <div>
              <span class="text-xs text-on-surface-variant uppercase font-semibold block mb-1">Description:</span>
              <p class="text-body-md text-on-surface"><?php echo nl2br(html_escape($assignment->description)); ?></p>
            </div>
          <?php endif; ?>

          <?php if ($assignment->instructions): ?>
            <div class="pt-2">
              <span class="text-xs text-on-surface-variant uppercase font-semibold block mb-1">Instructions:</span>
              <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant/40 text-body-md text-on-surface whitespace-pre-line">
                <?php echo html_escape($assignment->instructions); ?>
              </div>
            </div>
          <?php endif; ?>

          <!-- Reference Material -->
          <?php $asgn_files = $assignment->attachments ? json_decode($assignment->attachments, true) : []; ?>
          <?php if (!empty($asgn_files)): ?>
            <div class="pt-2">
              <span class="text-xs text-on-surface-variant uppercase font-semibold block mb-2">Teacher Question Papers & Reference:</span>
              <div class="space-y-2">
                <?php foreach ($asgn_files as $af): ?>
                  <a href="<?php echo base_url('uploads/homework/' . $af['file_name']); ?>" target="_blank" class="p-3 rounded-xl bg-surface-container-low border border-outline-variant/40 hover:bg-surface-container transition-colors flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary text-[22px]">download</span>
                    <div class="overflow-hidden">
                      <strong class="text-on-surface text-[13px] block truncate"><?php echo html_escape($af['orig_name']); ?></strong>
                      <span class="text-[11px] text-on-surface-variant font-mono"><?php echo round($af['file_size']); ?> KB</span>
                    </div>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Right: Submission Form & Status -->
      <div class="space-y-6">
        
        <!-- Submission Status Card -->
        <?php if ($submission): ?>
          <?php
            $statusBadge = ($submission->status === 'Reviewed') ? 'bg-secondary-container text-on-secondary-container font-bold' : (($submission->status === 'Returned') ? 'bg-error-container text-error font-bold' : 'bg-primary-container text-on-primary-container font-bold');
          ?>
          <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
              <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary text-[22px]">verified</span>Your Submission Status
              </h3>
              <span class="px-3 py-1 rounded-full text-xs <?php echo $statusBadge; ?>">
                <?php echo html_escape($submission->status); ?> (v<?php echo $submission->submission_version; ?>)
              </span>
            </div>

            <div class="space-y-2 text-body-md">
              <div class="flex justify-between"><span class="text-on-surface-variant">Submitted At:</span><strong class="text-on-surface font-mono"><?php echo date('d M Y, h:i A', strtotime($submission->submitted_at)); ?></strong></div>
              <?php if ($submission->marks_obtained !== NULL): ?>
                <div class="flex justify-between"><span class="text-on-surface-variant">Marks Scored:</span><strong class="text-primary font-mono font-bold"><?php echo $submission->marks_obtained; ?> / <?php echo $assignment->max_marks; ?> <?php if ($submission->grade) echo '(' . html_escape($submission->grade) . ')'; ?></strong></div>
              <?php endif; ?>
              <?php if ($submission->teacher_remarks): ?>
                <div class="pt-2">
                  <span class="text-xs text-on-surface-variant uppercase font-semibold block mb-1">Teacher Remarks:</span>
                  <p class="p-3 rounded-xl bg-surface-container-low text-on-surface italic text-[13px]">"<?php echo html_escape($submission->teacher_remarks); ?>"</p>
                </div>
              <?php endif; ?>
              <?php if ($submission->correction_reason): ?>
                <div class="p-3 rounded-xl bg-error-container/30 border border-error/20 text-error text-[13px]">
                  <strong class="block font-bold">Correction Requested:</strong>
                  <?php echo html_escape($submission->correction_reason); ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Student Submission Form -->
        <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2 pb-2 border-b border-outline-variant/50">
            <span class="material-symbols-outlined text-primary text-[22px]">upload</span><?php echo $submission ? 'Resubmit Assignment' : 'Submit Assignment'; ?>
          </h3>

          <?php echo form_open_multipart('homework/student_view/' . $assignment->assignment_id . '?student_id=' . ($student ? $student->student_id : 1), array('class' => 'space-y-4')); ?>
            
            <?php if ($assignment->allow_text_submission): ?>
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Your Text Answer / Notes</label>
                <textarea name="submitted_text" rows="5" placeholder="Type your answers or working notes here..." class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"><?php echo $submission ? html_escape($submission->submitted_text) : ''; ?></textarea>
              </div>
            <?php endif; ?>

            <?php if ($assignment->allow_file_submission): ?>
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Upload Assignment Files</label>
                <input type="file" name="submission_files[]" <?php echo $assignment->allow_multiple_files ? 'multiple' : ''; ?> class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-label-md file:font-semibold file:bg-secondary-container file:text-on-secondary-container hover:file:bg-secondary file:cursor-pointer"/>
                <span class="text-[11px] text-on-surface-variant block mt-1">Allowed: PDF, DOC, Images, ZIP (Max 10MB)</span>
              </div>
            <?php endif; ?>

            <div class="pt-3 border-t border-outline-variant/50">
              <button type="submit" class="w-full py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer flex items-center justify-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">send</span><?php echo $submission ? 'Upload & Resubmit' : 'Submit Assignment'; ?>
              </button>
            </div>
          <?php echo form_close(); ?>
        </div>

      </div>
    </div>
