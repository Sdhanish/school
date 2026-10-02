<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('error')): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-error-container text-on-error-container text-body-md font-medium flex items-center gap-2 border border-error/20">
        <span class="material-symbols-outlined text-[20px] text-error">error</span>
        <?php echo html_escape($this->session->flashdata('error')); ?>
      </div>
    <?php endif; ?>

    <!-- Warning if submissions exist -->
    <?php if (!empty($assignment->submission_stats) && $assignment->submission_stats->submitted > 0): ?>
      <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-body-md flex items-start gap-3">
        <span class="material-symbols-outlined text-amber-600 text-[24px] shrink-0">warning</span>
        <div>
          <strong class="font-bold block">Warning: <?php echo $assignment->submission_stats->submitted; ?> Students have already submitted!</strong>
          <p class="text-xs text-amber-800 mt-0.5">Modifying maximum marks, instructions, or due dates may impact existing student submissions and evaluations.</p>
        </div>
      </div>
    <?php endif; ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Edit Assignment</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Update assignment parameters, deadlines, instructions, and submission controls.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('homework/details/' . $assignment->assignment_id); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">visibility</span>View Details
        </a>
      </div>
    </div>

    <!-- Edit Form -->
    <?php echo form_open('homework/edit/' . $assignment->assignment_id); ?>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        
        <!-- Left: Basic Information (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
          <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
            <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2 pb-2 border-b border-outline-variant/50">
              <span class="material-symbols-outlined text-primary text-[22px]">info</span>Assignment Information
            </h3>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Assignment Title *</label>
              <input type="text" name="title" value="<?php echo html_escape($assignment->title); ?>" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary font-medium"/>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Assignment Type *</label>
                <select name="assignment_type_id" required class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <?php foreach ($types as $t): ?>
                    <option value="<?php echo $t->type_id; ?>" <?php echo ($assignment->assignment_type_id == $t->type_id) ? 'selected' : ''; ?>>
                      <?php echo html_escape($t->type_name); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Maximum Marks *</label>
                <input type="number" step="0.5" min="0" max="1000" name="max_marks" value="<?php echo $assignment->max_marks; ?>" required class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Description / Overview</label>
              <textarea name="description" rows="3" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"><?php echo html_escape($assignment->description); ?></textarea>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Detailed Instructions for Students</label>
              <textarea name="instructions" rows="4" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"><?php echo html_escape($assignment->instructions); ?></textarea>
            </div>
          </div>
        </div>

        <!-- Right: Targeting, Schedule, Toggles (1 Col) -->
        <div class="space-y-6">
          <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
            <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2 pb-2 border-b border-outline-variant/50">
              <span class="material-symbols-outlined text-primary text-[22px]">school</span>Academic Allocation
            </h3>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Class *</label>
              <select name="class_id" required class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <?php foreach ($classes as $c): ?>
                  <option value="<?php echo $c->class_id; ?>" <?php echo ($assignment->class_id == $c->class_id) ? 'selected' : ''; ?>>
                    <?php echo html_escape($c->class_name); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Division *</label>
              <select name="division_id" required class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <?php foreach (($divisions ?? []) as $d): ?>
                  <option value="<?php echo $d->division_id; ?>" <?php echo (($assignment->division_id ?? null) == $d->division_id) ? 'selected' : ''; ?>>
                    <?php echo html_escape($d->division_name); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Subject *</label>
              <select name="subject_id" required class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <?php foreach ($subjects as $sub): ?>
                  <option value="<?php echo $sub->subject_id; ?>" <?php echo ($assignment->subject_id == $sub->subject_id) ? 'selected' : ''; ?>>
                    <?php echo html_escape($sub->subject_name); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Faculty / Teacher *</label>
              <select name="teacher_id" required class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <?php foreach ($teachers as $t): ?>
                  <option value="<?php echo $t->staff_id; ?>" <?php echo ($assignment->teacher_id == $t->staff_id) ? 'selected' : ''; ?>>
                    <?php echo html_escape($t->full_name); ?>
                  </option>
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
              <input type="date" name="assigned_date" value="<?php echo html_escape($assignment->assigned_date); ?>" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Due Date *</label>
                <input type="date" name="due_date" value="<?php echo html_escape($assignment->due_date); ?>" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Due Time</label>
                <input type="time" name="due_time" value="<?php echo html_escape($assignment->due_time ?: '23:59'); ?>" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Status</label>
              <select name="status" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="Published" <?php echo ($assignment->status === 'Published') ? 'selected' : ''; ?>>Published</option>
                <option value="Draft" <?php echo ($assignment->status === 'Draft') ? 'selected' : ''; ?>>Draft</option>
                <option value="Archived" <?php echo ($assignment->status === 'Archived') ? 'selected' : ''; ?>>Archived</option>
              </select>
            </div>
          </div>

          <!-- Controls -->
          <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-3">
            <label class="flex items-center justify-between text-body-md text-on-surface cursor-pointer">
              <span>Allow Text Answer</span>
              <input type="checkbox" name="allow_text_submission" value="1" <?php echo $assignment->allow_text_submission ? 'checked' : ''; ?> class="w-4 h-4 rounded text-secondary focus:ring-secondary"/>
            </label>
            <label class="flex items-center justify-between text-body-md text-on-surface cursor-pointer">
              <span>Allow File Uploads</span>
              <input type="checkbox" name="allow_file_submission" value="1" <?php echo $assignment->allow_file_submission ? 'checked' : ''; ?> class="w-4 h-4 rounded text-secondary focus:ring-secondary"/>
            </label>
            <label class="flex items-center justify-between text-body-md text-on-surface cursor-pointer">
              <span>Allow Resubmission</span>
              <input type="checkbox" name="allow_resubmission" value="1" <?php echo $assignment->allow_resubmission ? 'checked' : ''; ?> class="w-4 h-4 rounded text-secondary focus:ring-secondary"/>
            </label>
            <label class="flex items-center justify-between text-body-md text-on-surface cursor-pointer">
              <span>Accept Late Submissions</span>
              <input type="checkbox" name="allow_late_submission" value="1" <?php echo $assignment->allow_late_submission ? 'checked' : ''; ?> class="w-4 h-4 rounded text-secondary focus:ring-secondary"/>
            </label>
          </div>

          <!-- Submit Buttons -->
          <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex items-center justify-end gap-2">
            <button type="submit" class="w-full px-5 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
              Save Changes
            </button>
          </div>
        </div>
      </div>
    <?php echo form_close(); ?>
