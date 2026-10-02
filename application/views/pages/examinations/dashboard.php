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
        <h2 class="font-headline-md text-headline-md text-on-surface">Examination Dashboard</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Live tracking of examination schedules, marks submissions, verification queue, and published results.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('examinations/exams'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Create Exam
        </a>
        <a href="<?php echo site_url('examinations/marks_entry'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">edit_note</span>Enter Marks
        </a>
      </div>
    </div>

    <!-- 1. Statistics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
      <!-- Total Exams -->
      <div class="p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex flex-col justify-between">
        <div class="flex items-center justify-between">
          <span class="text-[12px] font-semibold text-on-surface-variant uppercase tracking-wider">Total Exams</span>
          <span class="p-2 rounded-xl bg-primary text-white"><span class="material-symbols-outlined text-[20px]">quiz</span></span>
        </div>
        <div class="mt-3">
          <div class="text-2xl font-bold text-on-surface"><?php echo $stats->total_exams; ?></div>
          <div class="text-[11px] text-on-surface-variant mt-0.5">Configured in system</div>
        </div>
      </div>

      <!-- Upcoming Exams -->
      <div class="p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex flex-col justify-between">
        <div class="flex items-center justify-between">
          <span class="text-[12px] font-semibold text-primary uppercase tracking-wider">Upcoming</span>
          <span class="p-2 rounded-xl bg-primary/10 text-primary"><span class="material-symbols-outlined text-[20px]">schedule</span></span>
        </div>
        <div class="mt-3">
          <div class="text-2xl font-bold text-primary"><?php echo $stats->upcoming_exams; ?></div>
          <div class="text-[11px] text-on-surface-variant mt-0.5">Scheduled forward</div>
        </div>
      </div>

      <!-- Completed Exams -->
      <div class="p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex flex-col justify-between">
        <div class="flex items-center justify-between">
          <span class="text-[12px] font-semibold text-secondary uppercase tracking-wider">Completed</span>
          <span class="p-2 rounded-xl bg-secondary-container text-on-secondary-container"><span class="material-symbols-outlined text-[20px]">task_alt</span></span>
        </div>
        <div class="mt-3">
          <div class="text-2xl font-bold text-secondary"><?php echo $stats->completed_exams; ?></div>
          <div class="text-[11px] text-on-surface-variant mt-0.5">Dates passed</div>
        </div>
      </div>

      <!-- Published Results -->
      <div class="p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex flex-col justify-between">
        <div class="flex items-center justify-between">
          <span class="text-[12px] font-semibold text-emerald-700 uppercase tracking-wider">Published</span>
          <span class="p-2 rounded-xl bg-emerald-100 text-emerald-800"><span class="material-symbols-outlined text-[20px]">verified</span></span>
        </div>
        <div class="mt-3">
          <div class="text-2xl font-bold text-emerald-700"><?php echo $stats->published_results; ?></div>
          <div class="text-[11px] text-on-surface-variant mt-0.5">Student result slips</div>
        </div>
      </div>

      <!-- Pending Marks Entry -->
      <div class="p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex flex-col justify-between">
        <div class="flex items-center justify-between">
          <span class="text-[12px] font-semibold text-amber-600 uppercase tracking-wider">Pending Marks</span>
          <span class="p-2 rounded-xl bg-amber-100 text-amber-900"><span class="material-symbols-outlined text-[20px]">pending_actions</span></span>
        </div>
        <div class="mt-3">
          <div class="text-2xl font-bold text-amber-600"><?php echo $stats->pending_marks; ?></div>
          <div class="text-[11px] text-on-surface-variant mt-0.5">Draft / Unsubmitted</div>
        </div>
      </div>

      <!-- Pending Verification -->
      <div class="p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex flex-col justify-between">
        <div class="flex items-center justify-between">
          <span class="text-[12px] font-semibold text-error uppercase tracking-wider">Verification</span>
          <span class="p-2 rounded-xl bg-error-container text-on-error-container"><span class="material-symbols-outlined text-[20px]">rule</span></span>
        </div>
        <div class="mt-3">
          <div class="text-2xl font-bold text-error"><?php echo $stats->pending_verification; ?></div>
          <div class="text-[11px] text-on-surface-variant mt-0.5">Awaiting Admin/Principal</div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      
      <!-- 2. Upcoming Exams Schedule -->
      <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50 mb-4">
            <h3 class="font-headline-md text-title-lg font-bold text-on-surface flex items-center gap-2">
              <span class="material-symbols-outlined text-primary text-[22px]">event_upcoming</span>Upcoming Examinations
            </h3>
            <a href="<?php echo site_url('examinations/schedules'); ?>" class="text-[13px] text-primary font-medium hover:underline">View All Schedule</a>
          </div>

          <div class="space-y-3">
            <?php if (empty($upcoming_exams)): ?>
              <div class="p-6 text-center text-on-surface-variant text-body-md">No upcoming exams scheduled.</div>
            <?php else: ?>
              <?php foreach ($upcoming_exams as $ue): ?>
                <div class="p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/40 flex items-center justify-between gap-3">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary text-white flex flex-col items-center justify-center font-bold leading-none shrink-0">
                      <span class="text-[14px]"><?php echo date('d', strtotime($ue->exam_date)); ?></span>
                      <span class="text-[10px] uppercase"><?php echo date('M', strtotime($ue->exam_date)); ?></span>
                    </div>
                    <div>
                      <div class="font-semibold text-on-surface text-body-md"><?php echo html_escape($ue->subject_name); ?></div>
                      <div class="text-[12px] text-on-surface-variant"><?php echo html_escape($ue->exam_name); ?> • <span class="font-medium text-on-surface"><?php echo html_escape($ue->class_name . ' ' . ($ue->division_name ?: '')); ?></span></div>
                    </div>
                  </div>
                  <div class="text-right whitespace-nowrap">
                    <div class="text-[12px] font-mono font-medium text-on-surface"><?php echo date('h:i A', strtotime($ue->start_time)) . ' - ' . date('h:i A', strtotime($ue->end_time)); ?></div>
                    <span class="text-[11px] text-on-surface-variant"><?php echo html_escape($ue->room_no ?: 'Hall 1'); ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- 3. Recent Published Results -->
      <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50 mb-4">
            <h3 class="font-headline-md text-title-lg font-bold text-on-surface flex items-center gap-2">
              <span class="material-symbols-outlined text-secondary text-[22px]">verified</span>Recent Published Results
            </h3>
            <a href="<?php echo site_url('examinations/results'); ?>" class="text-[13px] text-primary font-medium hover:underline">View All Results</a>
          </div>

          <div class="space-y-3">
            <?php if (empty($recent_results)): ?>
              <div class="p-6 text-center text-on-surface-variant text-body-md">No published examination results yet.</div>
            <?php else: ?>
              <?php foreach ($recent_results as $rr): ?>
                <?php
                  $total = (int)$rr->total_students;
                  $passed = (int)$rr->passed_count;
                  $pass_pct = ($total > 0) ? round(($passed / $total) * 100, 1) : 0;
                ?>
                <div class="p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/40 flex items-center justify-between gap-3">
                  <div>
                    <div class="font-semibold text-on-surface text-body-md"><?php echo html_escape($rr->exam_name); ?></div>
                    <div class="text-[12px] text-on-surface-variant"><?php echo html_escape($rr->class_name . ' ' . ($rr->division_name ?: '')); ?> • Published: <?php echo date('d M Y', strtotime($rr->published_at)); ?></div>
                  </div>
                  <div class="text-right">
                    <div class="font-bold text-body-md text-secondary"><?php echo $pass_pct; ?>% Pass</div>
                    <div class="text-[11px] text-on-surface-variant"><?php echo $passed; ?> / <?php echo $total; ?> Passed</div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- 4. Marks Entry Progress Overview -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
          <span class="material-symbols-outlined text-primary text-[20px]">percent</span>Marks Entry Progress by Subject & Class
        </h3>
        <span class="text-label-md text-on-surface-variant"><?php echo count($progress_summary); ?> subjects tracked</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Class & Division</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Subject</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Exam</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Progress</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Status</th>
              <th class="text-right px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($progress_summary)): ?>
              <tr><td colspan="6" class="px-4 py-8 text-center text-on-surface-variant text-body-md">No exam subject schedules found.</td></tr>
            <?php else: ?>
              <?php foreach ($progress_summary as $ps): ?>
                <?php
                  $badgeClass = 'bg-surface-container-high text-on-surface-variant';
                  if ($ps->status_label === 'Approved') $badgeClass = 'bg-secondary-container text-on-secondary-container';
                  elseif ($ps->status_label === 'Under Verification') $badgeClass = 'bg-primary text-white';
                  elseif ($ps->status_label === 'Draft in Progress') $badgeClass = 'bg-amber-100 text-amber-900';
                ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap font-medium text-on-surface">
                    <?php echo html_escape($ps->class_name . ' ' . ($ps->division_name ?: '')); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-semibold text-on-surface">
                    <?php echo html_escape($ps->subject_name); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-body-md text-on-surface-variant">
                    <?php echo html_escape($ps->exam_name); ?>
                  </td>
                  <td class="px-4 py-3 text-center">
                    <div class="flex items-center justify-center gap-2">
                      <div class="w-28 bg-surface-container-high rounded-full h-2 overflow-hidden">
                        <div class="bg-secondary h-2 rounded-full" style="width: <?php echo $ps->progress_pct; ?>%"></div>
                      </div>
                      <span class="text-[12px] font-mono font-medium text-on-surface"><?php echo $ps->entered_marks_count; ?>/<?php echo $ps->total_students; ?></span>
                    </div>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold <?php echo $badgeClass; ?>">
                      <?php echo html_escape($ps->status_label); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-right whitespace-nowrap">
                    <a href="<?php echo site_url('examinations/marks_entry?schedule_id=' . $ps->schedule_id); ?>" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-surface-container-high hover:bg-primary hover:text-on-primary text-body-md transition-colors text-[13px]">
                      <span class="material-symbols-outlined text-[16px]">edit</span>Marks
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
