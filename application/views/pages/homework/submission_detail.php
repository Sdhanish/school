<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Submission Details</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Review student answer, submitted attachments, submission version history, and teacher feedback.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('homework/details/' . $submission->assignment_id); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>Back to Assignment
        </a>
        <a href="<?php echo site_url('homework/review/' . $submission->submission_id); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">rate_review</span>Review & Grade
        </a>
      </div>
    </div>

    <!-- Student & Assignment Header Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <!-- Student Info -->
      <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-3">
        <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2 pb-2 border-b border-outline-variant/50">
          <span class="material-symbols-outlined text-primary text-[22px]">person</span>Student Profile
        </h3>
        <div class="divide-y divide-outline-variant/40 text-body-md">
          <div class="py-2 flex justify-between"><span class="text-on-surface-variant">Name:</span><strong class="text-on-surface"><?php echo html_escape($submission->first_name . ' ' . $submission->last_name); ?></strong></div>
          <div class="py-2 flex justify-between"><span class="text-on-surface-variant">Admission No:</span><span class="font-mono text-on-surface"><?php echo html_escape($submission->admission_number); ?></span></div>
          <div class="py-2 flex justify-between"><span class="text-on-surface-variant">Class & Division:</span><span class="text-on-surface"><?php echo html_escape($submission->class_name . ' ' . $submission->section_name); ?></span></div>
          <div class="py-2 flex justify-between"><span class="text-on-surface-variant">Roll Number:</span><span class="font-mono text-on-surface">#<?php echo html_escape($submission->roll_number ?: '—'); ?></span></div>
        </div>
      </div>

      <!-- Assignment Info -->
      <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-3">
        <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2 pb-2 border-b border-outline-variant/50">
          <span class="material-symbols-outlined text-primary text-[22px]">assignment</span>Assignment Metadata
        </h3>
        <div class="divide-y divide-outline-variant/40 text-body-md">
          <div class="py-2 flex justify-between"><span class="text-on-surface-variant">Title:</span><strong class="text-primary"><?php echo html_escape($submission->assignment_title); ?></strong></div>
          <div class="py-2 flex justify-between"><span class="text-on-surface-variant">Subject:</span><span class="text-on-surface"><?php echo html_escape($submission->subject_name); ?></span></div>
          <div class="py-2 flex justify-between"><span class="text-on-surface-variant">Due Date:</span><span class="text-error font-mono font-bold"><?php echo date('d M Y', strtotime($submission->due_date)); ?></span></div>
          <div class="py-2 flex justify-between"><span class="text-on-surface-variant">Max Marks:</span><span class="font-mono font-bold text-primary"><?php echo $submission->max_marks; ?></span></div>
        </div>
      </div>
    </div>

    <!-- Student Submission Content -->
    <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4 mb-6">
      <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
        <h3 class="font-headline-md text-title-lg font-bold text-on-surface flex items-center gap-2">
          <span class="material-symbols-outlined text-secondary text-[24px]">drafts</span>Student Submission (v<?php echo $submission->submission_version; ?>)
        </h3>
        <div class="flex items-center gap-2">
          <span class="text-xs text-on-surface-variant font-mono"><?php echo date('d M Y, h:i A', strtotime($submission->submitted_at)); ?></span>
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold <?php echo ($submission->status === 'Reviewed') ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container-high text-on-surface-variant'; ?>">
            <?php echo html_escape($submission->status); ?>
          </span>
        </div>
      </div>

      <!-- Text Response -->
      <?php if ($submission->submitted_text): ?>
        <div>
          <span class="text-xs text-on-surface-variant uppercase font-semibold block mb-1">Student Text Answer:</span>
          <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant/40 text-body-md text-on-surface whitespace-pre-line leading-relaxed">
            <?php echo html_escape($submission->submitted_text); ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Attached Files -->
      <?php
        $files = $submission->submitted_files ? json_decode($submission->submitted_files, true) : [];
      ?>
      <?php if (!empty($files)): ?>
        <div class="pt-2">
          <span class="text-xs text-on-surface-variant uppercase font-semibold block mb-2">Submitted File Attachments:</span>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <?php foreach ($files as $f): ?>
              <a href="<?php echo base_url('uploads/submissions/' . $f['file_name']); ?>" target="_blank" class="p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/40 hover:bg-surface-container transition-colors flex items-center gap-3">
                <span class="material-symbols-outlined text-secondary text-[24px]">file_present</span>
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

    <!-- Teacher Evaluation & Remarks -->
    <?php if ($submission->status === 'Reviewed' || $submission->status === 'Returned'): ?>
      <div class="p-6 rounded-2xl bg-secondary-container/20 border border-secondary/30 elevation-1 space-y-4 mb-6">
        <h3 class="font-headline-md text-title-md font-bold text-secondary flex items-center gap-2 pb-2 border-b border-secondary/20">
          <span class="material-symbols-outlined text-secondary text-[22px]">grade</span>Teacher Evaluation & Remarks
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/40">
            <span class="text-xs text-on-surface-variant uppercase font-semibold block">Marks Scored</span>
            <strong class="text-headline-sm text-primary font-mono"><?php echo $submission->marks_obtained !== NULL ? $submission->marks_obtained : '—'; ?> / <?php echo $submission->max_marks; ?></strong>
          </div>
          <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/40">
            <span class="text-xs text-on-surface-variant uppercase font-semibold block">Grade Awarded</span>
            <strong class="text-headline-sm text-secondary font-mono"><?php echo $submission->grade ?: '—'; ?></strong>
          </div>
          <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/40">
            <span class="text-xs text-on-surface-variant uppercase font-semibold block">Evaluated By</span>
            <strong class="text-title-md text-on-surface block"><?php echo html_escape($submission->reviewer_name ?: 'Faculty'); ?></strong>
            <span class="text-[11px] text-on-surface-variant font-mono"><?php echo date('d M Y, h:i A', strtotime($submission->reviewed_at)); ?></span>
          </div>
        </div>

        <?php if ($submission->teacher_remarks): ?>
          <div>
            <span class="text-xs text-on-surface-variant uppercase font-semibold block mb-1">Teacher Remarks:</span>
            <p class="p-3.5 rounded-xl bg-surface-container-lowest border border-outline-variant/40 text-body-md text-on-surface italic">
              "<?php echo html_escape($submission->teacher_remarks); ?>"
            </p>
          </div>
        <?php endif; ?>

        <?php if ($submission->correction_reason): ?>
          <div class="p-3.5 rounded-xl bg-error-container/40 border border-error/30 text-error">
            <strong class="font-bold text-xs uppercase block">Correction Request Note:</strong>
            <p class="text-body-md mt-1"><?php echo html_escape($submission->correction_reason); ?></p>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <!-- Resubmission History Audit -->
    <?php if (!empty($history)): ?>
      <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
        <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
          <span class="text-body-md font-semibold text-on-surface">Resubmission History Trail (<?php echo count($history); ?> Prior Versions)</span>
        </div>
        <div class="table-scroll overflow-x-auto">
          <table class="w-full data-table zebra border-collapse text-body-md">
            <thead>
              <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Version</th>
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Submitted Timestamp</th>
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Summary</th>
                <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Prior Marks</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/40">
              <?php foreach ($history as $h): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 font-mono font-bold text-primary whitespace-nowrap">v<?php echo $h->version; ?></td>
                  <td class="px-4 py-3 font-mono text-[12px] text-on-surface whitespace-nowrap"><?php echo date('d M Y, h:i A', strtotime($h->submitted_at)); ?></td>
                  <td class="px-4 py-3 text-on-surface text-[13px] line-clamp-1"><?php echo html_escape(substr($h->submitted_text ?: 'Uploaded files', 0, 80)); ?></td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-mono"><?php echo $h->marks_obtained !== NULL ? $h->marks_obtained : '—'; ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>
