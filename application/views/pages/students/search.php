<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Student Search & Filtering</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Multi-criteria database search by name, admission number, roll number, guardian details, and class.</p>
      </div>
    </div>

    <!-- Search Filters Panel -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-5 mb-6">
      <?php echo form_open('students/search', array('method' => 'GET', 'class' => 'space-y-4')); ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 text-body-md">
          <div class="sm:col-span-2">
            <label class="block text-label-md text-on-surface mb-1">Keyword Search</label>
            <div class="relative">
              <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant/60 text-[20px]">search</span>
              <input type="text" name="search" value="<?php echo html_escape($this->input->get('search')); ?>" placeholder="Student name, admission no, roll no, parent phone..." class="w-full pl-10 pr-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest focus:ring-2 focus:ring-primary/10 focus:border-primary"/>
            </div>
          </div>
          <div>
            <label class="block text-label-md text-on-surface mb-1">Academic Session</label>
            <select name="academic_year_id" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest">
              <option value="">All Academic Years</option>
              <?php foreach ($years as $yr): ?>
                <?php
                  $isSel = ($this->input->get('academic_year_id') !== NULL)
                    ? ($this->input->get('academic_year_id') == $yr->academic_year_id)
                    : (!empty($yr->is_active));
                ?>
                <option value="<?php echo $yr->academic_year_id; ?>" <?php echo $isSel ? 'selected' : ''; ?>>
                  <?php echo html_escape($yr->year_name); ?><?php echo (!empty($yr->is_active)) ? ' (Active)' : ''; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="block text-label-md text-on-surface mb-1">Class</label>
            <select name="class_id" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest">
              <option value="">All Classes</option>
              <?php foreach ($classes as $cls): ?>
                <option value="<?php echo $cls->class_id; ?>" <?php echo ($this->input->get('class_id') == $cls->class_id) ? 'selected' : ''; ?>><?php echo html_escape($cls->class_name); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="block text-label-md text-on-surface mb-1">Division</label>
            <select name="division_id" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest">
              <option value="">All Divisions</option>
              <?php foreach ($sections as $sec): ?>
                <option value="<?php echo $sec->section_id; ?>" <?php echo ($this->input->get('section_id') == $sec->section_id) ? 'selected' : ''; ?>><?php echo html_escape($sec->class_name . ' - ' . $sec->section_name); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="block text-label-md text-on-surface mb-1">Gender</label>
            <select name="gender" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest">
              <option value="">All Genders</option>
              <option value="Male" <?php echo ($this->input->get('gender') === 'Male') ? 'selected' : ''; ?>>Male</option>
              <option value="Female" <?php echo ($this->input->get('gender') === 'Female') ? 'selected' : ''; ?>>Female</option>
            </select>
          </div>
          <div>
            <label class="block text-label-md text-on-surface mb-1">Enrollment Status</label>
            <select name="status" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest">
              <option value="">All Statuses</option>
              <option value="1" <?php echo ($this->input->get('status') === '1') ? 'selected' : ''; ?>>Active</option>
              <option value="0" <?php echo ($this->input->get('status') === '0') ? 'selected' : ''; ?>>Inactive / Transferred</option>
            </select>
          </div>
          <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors cursor-pointer flex items-center justify-center gap-1.5 shadow-sm"><span class="material-symbols-outlined text-[18px]">search</span>Search</button>
            <a href="<?php echo site_url('students/search'); ?>" class="px-3 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors" title="Reset Filters"><span class="material-symbols-outlined text-[18px]">restart_alt</span></a>
          </div>
        </div>
      <?php echo form_close(); ?>
    </div>

    <!-- Results Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
      <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/50">
        <h3 class="font-headline-md text-headline-md text-on-surface">Search Results</h3>
        <span class="text-label-md text-on-surface-variant font-medium"><?php echo count($students); ?> student(s) found</span>
      </div>
      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60">
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide">Admission No.</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide">Student Name</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide">Class & Division</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide">Gender</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide">Guardian & Contact</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide">Status</th>
              <th class="text-right px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($students)): ?>
              <tr><td colspan="7" class="px-4 py-8 text-center text-body-md text-on-surface-variant">No students found matching current query.</td></tr>
            <?php endif; ?>
            <?php foreach ($students as $st): ?>
              <?php
                $statusBadge = ($st->status == 1)
                  ? '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-secondary-container text-on-secondary-container">Active</span>'
                  : '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-surface-container-high text-on-surface-variant">Inactive</span>';
              ?>
              <tr class='hover:bg-surface-container-low transition-colors'>
                <td class="px-4 py-3 text-body-md font-mono text-primary font-medium">
                  <a href="<?php echo site_url('students/profile/' . $st->student_id); ?>" class="hover:underline"><?php echo html_escape($st->admission_number); ?></a>
                  <?php if (!empty($st->roll_number)): ?><span class="text-[11px] text-on-surface-variant font-mono">#<?php echo html_escape($st->roll_number); ?></span><?php endif; ?>
                </td>
                <td class="px-4 py-3 text-body-md font-medium text-on-surface"><?php echo html_escape($st->first_name . ' ' . $st->last_name); ?></td>
                <td class="px-4 py-3 text-body-md text-on-surface"><?php echo html_escape(($st->class_name ?: '—') . ' ' . ($st->section_name ?: '')); ?></td>
                <td class="px-4 py-3 text-body-md text-on-surface"><?php echo html_escape($st->gender); ?></td>
                <td class="px-4 py-3 text-body-md text-on-surface">
                  <div><?php echo html_escape($st->guardian_name); ?></div>
                  <div class="text-[12px] text-on-surface-variant"><?php echo html_escape($st->guardian_phone); ?></div>
                </td>
                <td class="px-4 py-3 text-body-md"><?php echo $statusBadge; ?></td>
                <td class="px-4 py-3 text-body-md text-right whitespace-nowrap">
                  <a href="<?php echo site_url('students/profile/' . $st->student_id); ?>" class="px-3 py-1 rounded-lg bg-surface-container-high text-on-surface text-label-md hover:bg-surface-container-highest transition-colors inline-flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">visibility</span>Profile</a>
                  <a href="<?php echo site_url('students/edit/' . $st->student_id); ?>" class="px-3 py-1 rounded-lg border border-outline-variant text-on-surface text-label-md hover:bg-surface-container-high transition-colors inline-flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">edit</span>Edit</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
