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
        <h2 class="font-headline-md text-headline-md text-on-surface">Assignment Directory</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Manage homework, classwork, projects, and track student completion across all classes.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('homework/types'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">category</span>Types
        </a>
        <a href="<?php echo site_url('homework/create'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Create Assignment
        </a>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('homework/assignments'); ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Academic Year</label>
          <select name="academic_year_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <?php foreach ($academic_years as $ay): ?>
              <option value="<?php echo $ay->academic_year_id; ?>" <?php echo (($filters['academic_year_id'] ?? '') == $ay->academic_year_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($ay->year_name); ?><?php echo (!empty($ay->is_active)) ? ' (Active)' : ''; ?>
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
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Subject</label>
          <select name="subject_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Subjects</option>
            <?php foreach ($subjects as $sub): ?>
              <option value="<?php echo $sub->subject_id; ?>" <?php echo (($filters['subject_id'] ?? '') == $sub->subject_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($sub->subject_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Status</label>
          <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Statuses</option>
            <option value="Published" <?php echo (($filters['status'] ?? '') === 'Published') ? 'selected' : ''; ?>>Published</option>
            <option value="Draft" <?php echo (($filters['status'] ?? '') === 'Draft') ? 'selected' : ''; ?>>Draft</option>
            <option value="Archived" <?php echo (($filters['status'] ?? '') === 'Archived') ? 'selected' : ''; ?>>Archived</option>
          </select>
        </div>

        <div class="sm:col-span-2 lg:col-span-3">
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Search Title, Subject, Teacher</label>
          <input type="text" name="search" value="<?php echo html_escape($filters['search'] ?? ''); ?>" placeholder="Search by title, subject, faculty..." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
        </div>

        <div class="flex items-end gap-2">
          <button type="submit" class="w-full px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors cursor-pointer">
            Filter
          </button>
          <a href="<?php echo site_url('homework/assignments'); ?>" class="px-3 py-2 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest hover:bg-surface-container-high transition-colors" title="Reset">
            <span class="material-symbols-outlined text-[18px]">restart_alt</span>
          </a>
        </div>
      </form>
    </div>

    <!-- Assignments Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Assignments (<?php echo count($assignments); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Assignment Title</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Class & Division</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Subject</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Teacher</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Due Date</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Submissions</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($assignments)): ?>
              <tr><td colspan="8" class="px-4 py-8 text-center text-on-surface-variant">No assignments found matching criteria.</td></tr>
            <?php else: ?>
              <?php foreach ($assignments as $a): ?>
                <?php
                  $st = $a->submission_stats;
                  $statusBadge = ($a->status === 'Published') ? 'bg-secondary-container text-on-secondary-container font-bold' : (($a->status === 'Draft') ? 'bg-surface-container-high text-on-surface-variant' : 'bg-amber-100 text-amber-900');
                ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap">
                    <a href="<?php echo site_url('homework/details/' . $a->assignment_id); ?>" class="font-bold text-primary hover:underline block">
                      <?php echo html_escape($a->title); ?>
                    </a>
                    <span class="text-[11px] text-on-surface-variant font-medium"><?php echo html_escape($a->type_name ?: 'Homework'); ?> • Max: <?php echo $a->max_marks; ?> Marks</span>
                  </td>
                  <td class="px-4 py-3 font-semibold text-on-surface whitespace-nowrap">
                    <?php echo html_escape($a->class_name . ' ' . $a->section_name); ?>
                  </td>
                  <td class="px-4 py-3 text-on-surface whitespace-nowrap">
                    <?php echo html_escape($a->subject_name); ?>
                  </td>
                  <td class="px-4 py-3 text-on-surface whitespace-nowrap">
                    <?php echo html_escape($a->teacher_name); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[13px] text-on-surface">
                    <?php echo date('d M Y', strtotime($a->due_date)); ?>
                    <?php if ($a->due_time): ?>
                      <span class="text-[11px] text-on-surface-variant block"><?php echo date('h:i A', strtotime($a->due_time)); ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <div class="inline-flex items-center gap-1.5">
                      <span class="font-bold text-secondary font-mono text-[13px]"><?php echo $st->submitted; ?>/<?php echo $st->total_students; ?></span>
                      <span class="text-[11px] text-on-surface-variant">(<?php echo $st->completion_pct; ?>%)</span>
                    </div>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] <?php echo $statusBadge; ?>">
                      <?php echo html_escape($a->status); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <div class="flex items-center justify-center gap-1">
                      <a href="<?php echo site_url('homework/details/' . $a->assignment_id); ?>" class="p-1.5 rounded-lg hover:bg-surface-container-high text-on-surface-variant" title="View Details">
                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                      </a>
                      <a href="<?php echo site_url('homework/edit/' . $a->assignment_id); ?>" class="p-1.5 rounded-lg hover:bg-surface-container-high text-primary" title="Edit">
                        <span class="material-symbols-outlined text-[18px]">edit</span>
                      </a>
                      <a href="<?php echo site_url('homework/duplicate/' . $a->assignment_id); ?>" class="p-1.5 rounded-lg hover:bg-surface-container-high text-secondary" title="Duplicate">
                        <span class="material-symbols-outlined text-[18px]">content_copy</span>
                      </a>
                      <?php if ($a->status === 'Draft'): ?>
                        <a href="<?php echo site_url('homework/publish/' . $a->assignment_id); ?>" class="p-1.5 rounded-lg hover:bg-secondary-container text-secondary" title="Publish">
                          <span class="material-symbols-outlined text-[18px]">send</span>
                        </a>
                      <?php endif; ?>
                      <a href="<?php echo site_url('homework/delete/' . $a->assignment_id); ?>" onclick="return confirm('Remove or archive this assignment?');" class="p-1.5 rounded-lg hover:bg-error-container text-error" title="Delete / Archive">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
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
