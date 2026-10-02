<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('success')): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-secondary-container text-on-secondary-container text-body-md font-medium flex items-center gap-2 border border-secondary/20">
        <span class="material-symbols-outlined text-[20px] text-secondary">check_circle</span>
        <?php echo html_escape($this->session->flashdata('success')); ?>
      </div>
    <?php endif; ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <div class="flex items-center gap-2">
          <h2 class="font-headline-md text-headline-md text-on-surface"><?php echo html_escape($assignment->title); ?></h2>
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold <?php echo ($assignment->status === 'Published') ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container-high text-on-surface-variant'; ?>">
            <?php echo html_escape($assignment->status); ?>
          </span>
        </div>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">
          <?php echo html_escape($assignment->subject_name); ?> • <?php echo html_escape($assignment->class_name . ' ' . $assignment->section_name); ?> • Faculty: <?php echo html_escape($assignment->teacher_name); ?>
        </p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('homework/student_view/' . $assignment->assignment_id); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-primary bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">account_circle</span>Student Portal Preview
        </a>
        <a href="<?php echo site_url('homework/edit/' . $assignment->assignment_id); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">edit</span>Edit Assignment
        </a>
      </div>
    </div>

    <!-- Summary Stats Bar -->
    <?php $st = $assignment->submission_stats; ?>
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/40 text-center">
        <span class="text-xs text-on-surface-variant uppercase font-semibold block">Total Students</span>
        <strong class="text-title-md font-bold text-on-surface font-mono"><?php echo $st->total_students; ?></strong>
      </div>
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/40 text-center">
        <span class="text-xs text-on-surface-variant uppercase font-semibold block">Submitted</span>
        <strong class="text-title-md font-bold text-secondary font-mono"><?php echo $st->submitted; ?></strong>
      </div>
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/40 text-center">
        <span class="text-xs text-on-surface-variant uppercase font-semibold block">Reviewed & Graded</span>
        <strong class="text-title-md font-bold text-primary font-mono"><?php echo $st->reviewed; ?></strong>
      </div>
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/40 text-center">
        <span class="text-xs text-on-surface-variant uppercase font-semibold block">Late Submissions</span>
        <strong class="text-title-md font-bold text-amber-600 font-mono"><?php echo $st->late; ?></strong>
      </div>
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/40 text-center col-span-2 sm:col-span-1">
        <span class="text-xs text-on-surface-variant uppercase font-semibold block">Completion</span>
        <strong class="text-title-md font-bold text-primary font-mono"><?php echo $st->completion_pct; ?>%</strong>
      </div>
    </div>

    <!-- 2 Column Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
      
      <!-- Instructions & Details (2 Cols) -->
      <div class="lg:col-span-2 space-y-6">
        <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2 pb-2 border-b border-outline-variant/50">
            <span class="material-symbols-outlined text-primary text-[22px]">description</span>Assignment Instructions
          </h3>

          <?php if ($assignment->description): ?>
            <div>
              <span class="text-xs text-on-surface-variant uppercase font-semibold block mb-1">Overview:</span>
              <p class="text-body-md text-on-surface leading-relaxed"><?php echo nl2br(html_escape($assignment->description)); ?></p>
            </div>
          <?php endif; ?>

          <?php if ($assignment->instructions): ?>
            <div class="pt-2">
              <span class="text-xs text-on-surface-variant uppercase font-semibold block mb-1">Student Instructions:</span>
              <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant/40 text-body-md text-on-surface whitespace-pre-line">
                <?php echo html_escape($assignment->instructions); ?>
              </div>
            </div>
          <?php endif; ?>

          <!-- Reference Attachments -->
          <?php 
            $files = $assignment->attachments ? json_decode($assignment->attachments, true) : [];
          ?>
          <?php if (!empty($files)): ?>
            <div class="pt-3">
              <span class="text-xs text-on-surface-variant uppercase font-semibold block mb-2">Teacher Reference Materials:</span>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <?php foreach ($files as $f): ?>
                  <a href="<?php echo base_url('uploads/homework/' . $f['file_name']); ?>" target="_blank" class="p-3 rounded-xl bg-surface-container-low border border-outline-variant/40 hover:bg-surface-container transition-colors flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-secondary text-[22px]">attach_file</span>
                    <div class="overflow-hidden">
                      <span class="font-bold text-on-surface text-[13px] block truncate"><?php echo html_escape($f['orig_name']); ?></span>
                      <span class="text-[11px] text-on-surface-variant font-mono"><?php echo round($f['file_size']); ?> KB</span>
                    </div>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Metadata Card (1 Col) -->
      <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
        <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2 pb-2 border-b border-outline-variant/50">
          <span class="material-symbols-outlined text-primary text-[22px]">tune</span>Parameters & Rules
        </h3>

        <div class="divide-y divide-outline-variant/40 text-body-md">
          <div class="py-2.5 flex justify-between"><span class="text-on-surface-variant">Type:</span><strong class="text-on-surface"><?php echo html_escape($assignment->type_name ?: 'Homework'); ?></strong></div>
          <div class="py-2.5 flex justify-between"><span class="text-on-surface-variant">Max Marks:</span><strong class="text-primary font-mono"><?php echo $assignment->max_marks; ?> Marks</strong></div>
          <div class="py-2.5 flex justify-between"><span class="text-on-surface-variant">Assigned Date:</span><span class="text-on-surface font-mono"><?php echo date('d M Y', strtotime($assignment->assigned_date)); ?></span></div>
          <div class="py-2.5 flex justify-between"><span class="text-on-surface-variant">Due Date:</span><strong class="text-error font-mono"><?php echo date('d M Y', strtotime($assignment->due_date)); ?> <?php if ($assignment->due_time) echo date('h:i A', strtotime($assignment->due_time)); ?></strong></div>
          <div class="py-2.5 flex justify-between"><span class="text-on-surface-variant">Text Submission:</span><span><?php echo $assignment->allow_text_submission ? 'Allowed' : 'Disabled'; ?></span></div>
          <div class="py-2.5 flex justify-between"><span class="text-on-surface-variant">File Submission:</span><span><?php echo $assignment->allow_file_submission ? 'Allowed' : 'Disabled'; ?></span></div>
          <div class="py-2.5 flex justify-between"><span class="text-on-surface-variant">Resubmissions:</span><span><?php echo $assignment->allow_resubmission ? 'Allowed' : 'Disabled'; ?></span></div>
          <div class="py-2.5 flex justify-between"><span class="text-on-surface-variant">Late Submissions:</span><span><?php echo $assignment->allow_late_submission ? 'Accepted' : 'Blocked'; ?></span></div>
        </div>
      </div>
    </div>

    <!-- STUDENT ENROLLMENT & SUBMISSION ROSTER -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Class Roster & Submissions Status (<?php echo count($students); ?> Students)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Roll No</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Student Name</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Admission No</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Submitted Timestamp</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-primary uppercase whitespace-nowrap">Marks / Grade</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php foreach ($students as $stu): ?>
              <?php
                $sub = $sub_map[$stu->student_id] ?? null;
                $status = $sub ? $sub->status : 'Pending';
                $badgeClass = 'bg-surface-container-high text-on-surface-variant';
                if ($status === 'Submitted') $badgeClass = 'bg-secondary-container text-on-secondary-container font-bold';
                elseif ($status === 'Reviewed') $badgeClass = 'bg-primary-container text-on-primary-container font-bold';
                elseif ($status === 'Late') $badgeClass = 'bg-amber-100 text-amber-900 font-bold';
                elseif ($status === 'Returned') $badgeClass = 'bg-error-container text-error font-bold';
              ?>
              <tr class="hover:bg-surface-container-low transition-colors">
                <td class="px-4 py-3 font-mono text-on-surface whitespace-nowrap">#<?php echo html_escape($stu->roll_number ?: '—'); ?></td>
                <td class="px-4 py-3 font-bold text-on-surface whitespace-nowrap">
                  <?php echo html_escape($stu->first_name . ' ' . $stu->last_name); ?>
                </td>
                <td class="px-4 py-3 font-mono text-[12px] text-on-surface-variant whitespace-nowrap">
                  <?php echo html_escape($stu->admission_number); ?>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                  <span class="px-2.5 py-0.5 rounded-full text-[11px] <?php echo $badgeClass; ?>">
                    <?php echo html_escape($status); ?>
                  </span>
                </td>
                <td class="px-4 py-3 font-mono text-[12px] text-on-surface whitespace-nowrap">
                  <?php echo $sub ? date('d M Y, h:i A', strtotime($sub->submitted_at)) : '—'; ?>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap font-mono font-bold">
                  <?php if ($sub && $sub->marks_obtained !== NULL): ?>
                    <span class="text-primary"><?php echo $sub->marks_obtained; ?>/<?php echo $assignment->max_marks; ?></span>
                    <?php if ($sub->grade): ?>
                      <span class="ml-1 px-1.5 py-0.2 rounded text-[10px] bg-secondary-container text-on-secondary-container"><?php echo html_escape($sub->grade); ?></span>
                    <?php endif; ?>
                  <?php else: ?>
                    <span class="text-on-surface-variant font-normal">—</span>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                  <?php if ($sub): ?>
                    <div class="flex items-center justify-center gap-1.5">
                      <a href="<?php echo site_url('homework/submission_detail/' . $sub->submission_id); ?>" class="p-1 rounded hover:bg-surface-container-high text-on-surface-variant" title="View Submission">
                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                      </a>
                      <a href="<?php echo site_url('homework/review/' . $sub->submission_id); ?>" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-secondary text-on-secondary text-[11px] font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
                        <span class="material-symbols-outlined text-[14px]">rate_review</span>Review
                      </a>
                    </div>
                  <?php else: ?>
                    <a href="<?php echo site_url('homework/student_view/' . $assignment->assignment_id . '?student_id=' . $stu->student_id); ?>" class="text-[12px] text-primary hover:underline font-medium">
                      + Submit for Student
                    </a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
