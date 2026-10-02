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
        <h2 class="font-headline-md text-headline-md text-on-surface">Student Submission Tracking</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Master submission log across all assignments, review incoming submissions, grade work, and monitor late turn-ins.</p>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('homework/submissions'); ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Assignment</label>
          <select name="assignment_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Assignments</option>
            <?php foreach ($assignments as $a): ?>
              <option value="<?php echo $a->assignment_id; ?>" <?php echo (($filters['assignment_id'] ?? '') == $a->assignment_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($a->title . ' (' . $a->class_name . ')'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Class</label>
          <select name="class_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Classes</option>
            <?php foreach ($classes as $c): ?>
              <option value="<?php echo $c->class_id; ?>" <?php echo (($filters['class_id'] ?? '') == $c->class_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($c->class_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Status</label>
          <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Statuses</option>
            <option value="Submitted" <?php echo (($filters['status'] ?? '') === 'Submitted') ? 'selected' : ''; ?>>Submitted</option>
            <option value="Late" <?php echo (($filters['status'] ?? '') === 'Late') ? 'selected' : ''; ?>>Late</option>
            <option value="Reviewed" <?php echo (($filters['status'] ?? '') === 'Reviewed') ? 'selected' : ''; ?>>Reviewed</option>
            <option value="Returned" <?php echo (($filters['status'] ?? '') === 'Returned') ? 'selected' : ''; ?>>Returned</option>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Search Student / Admission No</label>
          <div class="flex gap-2">
            <input type="text" name="search" value="<?php echo html_escape($filters['search'] ?? ''); ?>" placeholder="Name, Roll, Admission #..." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            <button type="submit" class="px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant cursor-pointer">
              Go
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Submissions Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Logged Submissions (<?php echo count($submissions); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Student</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Class & Division</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Assignment</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Subject</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Submitted Date</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-primary uppercase whitespace-nowrap">Marks / Grade</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($submissions)): ?>
              <tr><td colspan="8" class="px-4 py-8 text-center text-on-surface-variant">No submission records found.</td></tr>
            <?php else: ?>
              <?php foreach ($submissions as $sub): ?>
                <?php
                  $badgeClass = 'bg-surface-container-high text-on-surface-variant';
                  if ($sub->status === 'Submitted') $badgeClass = 'bg-secondary-container text-on-secondary-container font-bold';
                  elseif ($sub->status === 'Reviewed') $badgeClass = 'bg-primary-container text-on-primary-container font-bold';
                  elseif ($sub->status === 'Late') $badgeClass = 'bg-amber-100 text-amber-900 font-bold';
                  elseif ($sub->status === 'Returned') $badgeClass = 'bg-error-container text-error font-bold';
                ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap">
                    <strong class="text-on-surface block"><?php echo html_escape($sub->first_name . ' ' . $sub->last_name); ?></strong>
                    <span class="text-[11px] font-mono text-on-surface-variant">Adm: <?php echo html_escape($sub->admission_number); ?> • v<?php echo $sub->submission_version; ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-semibold text-on-surface">
                    <?php echo html_escape($sub->class_name . ' ' . $sub->section_name); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <a href="<?php echo site_url('homework/details/' . $sub->assignment_id); ?>" class="font-bold text-primary hover:underline">
                      <?php echo html_escape($sub->assignment_title); ?>
                    </a>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-on-surface">
                    <?php echo html_escape($sub->subject_name); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[12px] text-on-surface">
                    <?php echo date('d M Y, h:i A', strtotime($sub->submitted_at)); ?>
                    <?php if ($sub->is_late): ?>
                      <span class="text-[10px] text-amber-600 font-bold block">Late (<?php echo $sub->late_duration_minutes; ?> mins)</span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] <?php echo $badgeClass; ?>">
                      <?php echo html_escape($sub->status); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-mono font-bold">
                    <?php if ($sub->marks_obtained !== NULL): ?>
                      <span class="text-primary"><?php echo $sub->marks_obtained; ?>/<?php echo $sub->max_marks; ?></span>
                      <?php if ($sub->grade): ?>
                        <span class="ml-1 px-1.5 py-0.2 rounded text-[10px] bg-secondary-container text-on-secondary-container"><?php echo html_escape($sub->grade); ?></span>
                      <?php endif; ?>
                    <?php else: ?>
                      <span class="text-on-surface-variant font-normal">—</span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <div class="flex items-center justify-center gap-1.5">
                      <a href="<?php echo site_url('homework/submission_detail/' . $sub->submission_id); ?>" class="p-1 rounded hover:bg-surface-container-high text-on-surface-variant" title="View Submission">
                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                      </a>
                      <a href="<?php echo site_url('homework/review/' . $sub->submission_id); ?>" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-secondary text-on-secondary text-[11px] font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
                        <span class="material-symbols-outlined text-[14px]">rate_review</span>Review
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
