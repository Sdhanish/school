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
        <div class="flex items-center gap-2.5">
          <h2 class="font-headline-md text-headline-md text-on-surface">Class Attendance</h2>
          <?php if ($is_higher_sec): ?>
            <span class="px-3 py-1 rounded-full text-[12px] font-semibold bg-primary text-white border border-primary/30">
            </span>
          <?php else: ?>
            <span class="px-3 py-1 rounded-full text-[12px] font-semibold bg-secondary-container text-on-secondary-container border border-secondary/30">
              Daily Attendance (LKG – 10)
            </span>
          <?php endif; ?>
        </div>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">
          <?php if ($is_higher_sec): ?>
            Period-based attendance workflow for Higher Secondary (+1 & +2). Allowed: Present, Half Day, Absent, Late Coming.
          <?php else: ?>
            Daily morning attendance workflow for LKG through Class 10. Allowed: Present, Half Day, Absent.
          <?php endif; ?>
        </p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('attendance/mark_attendance?class_id=' . $class_id . '&date=' . $date . '&academic_year_id=' . $year_id); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:opacity-90 transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">edit_calendar</span>Mark Attendance
        </a>
        <a href="<?php echo site_url('student-attendance/view?class_id=' . $class_id . '&academic_year_id=' . $year_id); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">visibility</span>VIEW ATTENDANCE
        </a>
        <a href="<?php echo site_url('attendance'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">dashboard</span>Attendance Dashboard
        </a>
      </div>
    </div>

    <!-- Filter Bar (Academic Year, Date, Academic Group, Class) -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('attendance/class_attendance'); ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Academic Year *</label>
          <select name="academic_year_id" onchange="this.form.submit()" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <?php foreach ($years as $y): ?>
              <option value="<?php echo $y->academic_year_id; ?>" <?php echo ($year_id == $y->academic_year_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($y->year_name); ?><?php echo (!empty($y->is_active)) ? ' (Active)' : ''; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Date *</label>
          <input type="date" name="date" value="<?php echo html_escape($date); ?>" min="<?php echo html_escape($current_year->start_date ?? ''); ?>" max="<?php echo html_escape($current_year->end_date ?? ''); ?>" onchange="this.form.submit()" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Department / Group</label>
          <select id="class_att_group_select" onchange="onClassAttGroupChanged(this.value)" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Department / Groups</option>
            <?php if (!empty($groups)): foreach ($groups as $grp): ?>
              <option value="<?php echo $grp->academic_group_id; ?>" <?php echo (!empty($selected_group_id) && $selected_group_id == $grp->academic_group_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($grp->group_name); ?>
              </option>
            <?php endforeach; endif; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Class *</label>
          <select id="class_att_class_select" name="class_id" onchange="this.form.submit()" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <?php foreach ($classes as $cls): ?>
              <option value="<?php echo $cls->class_id; ?>" data-group="<?php echo (int)($cls->academic_group_id ?? 0); ?>" data-attendance-type="<?php echo html_escape($cls->attendance_type ?? 'daily'); ?>" <?php echo ($class_id == $cls->class_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($cls->class_name); ?>
                (<?php echo ucfirst($cls->attendance_type ?? 'daily'); ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </form>
    </div>

    <!-- Division Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
      <?php if (empty($sections_overview)): ?>
        <div class="col-span-full p-8 rounded-xl bg-surface-container-lowest border border-outline-variant/50 text-center elevation-1">
          <span class="material-symbols-outlined text-[48px] text-outline mb-2">school</span>
          <h4 class="font-title-md text-title-md text-on-surface font-semibold">No Divisions Found</h4>
          <p class="text-body-md text-on-surface-variant mt-1">Default Division A is automatically created for this class.</p>
        </div>
      <?php else: ?>
        <?php foreach ($sections_overview as $sec): ?>
          <?php
            $is_sec_active = ($section_id && (int)$section_id === (int)($sec->division_id ?? $sec->section_id));
          ?>
          <div class="p-5 rounded-2xl bg-surface-container-lowest border <?php echo $is_sec_active ? 'border-primary ring-2 ring-primary/20' : 'border-outline-variant/50'; ?> elevation-1 hover:border-primary/40 transition-all flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between pb-3 border-b border-outline-variant/40 mb-4">
                <div>
                  <h3 class="font-title-md text-title-md font-bold text-on-surface"><?php echo html_escape($sec->class_name . ' — Division ' . ($sec->division_name ?? $sec->section_name)); ?></h3>
                  <div class="text-[12px] text-on-surface-variant font-medium"><?php echo $sec->total_students; ?> Enrolled Students</div>
                </div>
                <div class="text-right">
                  <div class="text-xl font-bold <?php echo ($sec->percentage >= 90) ? 'text-secondary' : (($sec->percentage >= 75) ? 'text-amber-600' : 'text-error'); ?>">
                    <?php echo $sec->percentage; ?>%
                  </div>
                  <div class="text-[11px] text-on-surface-variant">Present</div>
                </div>
              </div>

              <!-- Stats Breakdown Grid: Dynamic based on Class Level -->
              <?php if ($is_higher_sec): ?>
                <!-- +1 and +2: Present, Half Day, Absent, Late Coming -->
                <div class="grid grid-cols-4 gap-2 text-center mb-4">
                  <div class="p-2.5 rounded-lg bg-secondary-container/20 border border-secondary/20">
                    <div class="font-bold text-secondary text-title-md"><?php echo $sec->present_count ?: 0; ?></div>
                    <div class="text-[11px] text-on-surface-variant">Present</div>
                  </div>
                  <div class="p-2.5 rounded-lg bg-amber-50/70 border border-amber-200">
                    <div class="font-bold text-amber-900 text-title-md"><?php echo $sec->half_day_count ?: 0; ?></div>
                    <div class="text-[11px] text-on-surface-variant">Half Day</div>
                  </div>
                  <div class="p-2.5 rounded-lg bg-indigo-50/70 border border-indigo-200">
                    <div class="font-bold text-indigo-900 text-title-md"><?php echo $sec->late_count ?: 0; ?></div>
                    <div class="text-[11px] text-on-surface-variant">Late</div>
                  </div>
                  <div class="p-2.5 rounded-lg bg-error-container/20 border border-error/20">
                    <div class="font-bold text-error text-title-md"><?php echo $sec->absent_count ?: 0; ?></div>
                    <div class="text-[11px] text-on-surface-variant">Absent</div>
                  </div>
                </div>
              <?php else: ?>
                <!-- LKG - 10: Present, Half Day, Absent (Late Coming, Leave, Excused Removed) -->
                <div class="grid grid-cols-3 gap-2.5 text-center mb-4">
                  <div class="p-2.5 rounded-lg bg-secondary-container/20 border border-secondary/20">
                    <div class="font-bold text-secondary text-title-md"><?php echo $sec->present_count ?: 0; ?></div>
                    <div class="text-[11px] text-on-surface-variant">Present</div>
                  </div>
                  <div class="p-2.5 rounded-lg bg-amber-50/70 border border-amber-200">
                    <div class="font-bold text-amber-900 text-title-md"><?php echo $sec->half_day_count ?: 0; ?></div>
                    <div class="text-[11px] text-on-surface-variant">Half Day</div>
                  </div>
                  <div class="p-2.5 rounded-lg bg-error-container/20 border border-error/20">
                    <div class="font-bold text-error text-title-md"><?php echo $sec->absent_count ?: 0; ?></div>
                    <div class="text-[11px] text-on-surface-variant">Absent</div>
                  </div>
                </div>
              <?php endif; ?>
            </div>

            <!-- Action Buttons: VIEW ATTENDANCE only -->
            <div class="pt-3 border-t border-outline-variant/40 mt-2">
              <a href="<?php echo site_url('student-attendance/view?class_id=' . $sec->class_id . '&division_id=' . ($sec->division_id ?? $sec->section_id) . '&academic_year_id=' . $year_id); ?>" class="w-full inline-flex items-center justify-center gap-1.5 text-center py-2.5 px-3 rounded-lg bg-secondary text-on-secondary hover:bg-on-secondary-fixed-variant text-label-md font-semibold transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[17px]">visibility</span>VIEW ATTENDANCE
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <script>
      function onClassAttGroupChanged(groupId) {
        var select = document.getElementById('class_att_class_select');
        if (!select) return;
        var firstMatch = null;
        for (var i = 0; i < select.options.length; i++) {
          var opt = select.options[i];
          var optGroup = opt.getAttribute('data-group');
          if (!groupId || optGroup == groupId) {
            opt.style.display = '';
            if (!firstMatch) firstMatch = opt.value;
          } else {
            opt.style.display = 'none';
          }
        }
        if (firstMatch && select.value !== firstMatch) {
          select.value = firstMatch;
          select.form.submit();
        }
      }
    </script>
  </div>
</div>
