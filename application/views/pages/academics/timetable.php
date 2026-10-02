<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('success')): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-secondary-container text-on-secondary-container text-body-md font-medium flex items-center gap-2 border border-secondary/20">
        <span class="material-symbols-outlined text-[20px] text-secondary">check_circle</span>
        <?php echo html_escape($this->session->flashdata('success')); ?>
      </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-error-container text-on-error-container text-body-md font-medium flex items-start gap-2 border border-error/20">
        <span class="material-symbols-outlined text-[20px] text-error shrink-0 mt-0.5">warning</span>
        <div>
          <div class="font-bold text-[14px]">Scheduling Collision Detected:</div>
          <div><?php echo html_escape($this->session->flashdata('error')); ?></div>
        </div>
      </div>
    <?php endif; ?>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Class Timetable Matrix</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Configure class period schedules, assign teachers, and prevent timetable scheduling conflicts.</p>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <button onclick="openAddEntryModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Schedule Period
        </button>
        <button onclick="document.getElementById('modal-period-manager').classList.remove('hidden')" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">timer</span>Manage Periods
        </button>
      </div>
    </div>

    <!-- Filter Bar (Academic Year, Class, Section) -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block text-label-md text-on-surface mb-1 font-semibold">Academic Year</label>
          <select id="tt_filter_year" onchange="reloadTimetable()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md">
            <?php foreach ($years as $yr): ?>
              <option value="<?php echo $yr->academic_year_id; ?>" <?php echo ($selected_year == $yr->academic_year_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($yr->year_name); ?><?php echo (!empty($yr->is_active)) ? ' (Active)' : ''; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-label-md text-on-surface mb-1 font-semibold">Select Class *</label>
          <select id="tt_filter_class" onchange="handleClassFilterChange(this.value)" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md">
            <?php foreach ($classes as $cls): ?>
              <option value="<?php echo $cls->class_id; ?>" <?php echo ($selected_class == $cls->class_id) ? 'selected' : ''; ?>><?php echo html_escape($cls->class_name); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-label-md text-on-surface mb-1 font-semibold">Select Division *</label>
          <select id="tt_filter_section" onchange="reloadTimetable()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md">
            <?php if (empty($class_sections)): ?>
              <option value="">No sections in this class</option>
            <?php endif; ?>
            <?php foreach ($class_sections as $sec): ?>
              <option value="<?php echo $sec->section_id; ?>" <?php echo ($selected_section == $sec->section_id) ? 'selected' : ''; ?>>Section <?php echo html_escape($sec->section_name); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>

    <!-- Day × Period Matrix Grid -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table border-collapse text-left">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low">
              <th class="p-3.5 text-label-md text-on-surface font-bold uppercase w-32 border-r border-outline-variant/40">Day</th>
              <?php foreach ($periods as $p): ?>
                <th class="p-3.5 text-label-md text-on-surface text-center min-w-[150px] border-r border-outline-variant/40">
                  <div class="font-bold"><?php echo html_escape($p->period_name); ?></div>
                  <div class="text-[11px] text-on-surface-variant font-normal"><?php echo date('h:i A', strtotime($p->start_time)) . ' - ' . date('h:i A', strtotime($p->end_time)); ?></div>
                </th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/30 text-body-md">
            <?php foreach ($days as $day): ?>
              <tr class="hover:bg-surface-container-low/50">
                <td class="p-3.5 font-bold text-on-surface bg-surface-container-low/30 border-r border-outline-variant/40 whitespace-nowrap">
                  <?php echo $day; ?>
                </td>
                <?php foreach ($periods as $p): ?>
                  <?php $entry = isset($grid[$day][$p->period_id]) ? $grid[$day][$p->period_id] : NULL; ?>
                  <td class="p-2 border-r border-outline-variant/40 align-top">
                    <?php if ($entry): ?>
                      <div class="p-2.5 rounded-xl bg-primary-fixed/20 border border-primary/20 space-y-1 relative group hover:shadow-xs transition-shadow">
                        <div class="font-bold text-primary text-[13px] truncate" title="<?php echo html_escape($entry->subject_name); ?>">
                          <?php echo html_escape($entry->subject_name); ?>
                        </div>
                        <div class="text-[11px] text-on-surface flex items-center gap-1 font-medium truncate" title="<?php echo html_escape($entry->teacher_name); ?>">
                          <span class="material-symbols-outlined text-[13px] text-secondary">person</span>
                          <span class="truncate"><?php echo html_escape($entry->teacher_name); ?></span>
                        </div>
                        <div class="pt-1 flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                          <button type="button" onclick="openEditEntryModal(<?php echo $entry->timetable_id; ?>)" class="p-1 rounded text-primary hover:bg-primary/10 cursor-pointer" title="Edit Schedule Slot"><span class="material-symbols-outlined text-[14px]">edit</span></button>
                          <a href="<?php echo site_url('academics/delete_timetable_entry/' . $entry->timetable_id . '?academic_year_id=' . $selected_year . '&class_id=' . $selected_class . '&section_id=' . $selected_section); ?>" onclick="return confirm('Remove this timetable period entry?')" class="p-1 rounded text-error hover:bg-error-container/20" title="Delete Schedule Slot"><span class="material-symbols-outlined text-[14px]">delete</span></a>
                        </div>
                      </div>
                    <?php else: ?>
                      <div class="h-16 rounded-xl border border-dashed border-outline-variant/40 flex items-center justify-center hover:bg-surface-container-high/40 transition-colors">
                        <button onclick="openAddEntryModalForCell('<?php echo $day; ?>', <?php echo $p->period_id; ?>)" class="text-[12px] text-on-surface-variant hover:text-primary flex items-center gap-0.5 cursor-pointer font-medium">
                          <span class="material-symbols-outlined text-[16px]">add</span>Assign
                        </button>
                      </div>
                    <?php endif; ?>
                  </td>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal 1: Schedule / Edit Period Entry -->
    <div id="modal-tt-entry" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden">
      <div class="elevation-3 rounded-2xl bg-surface-container-lowest border border-outline-variant w-full max-w-lg">
        <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant">
          <h3 class="font-headline-md text-headline-md text-on-surface" id="modal-tt-title">Schedule Timetable Period</h3>
          <button onclick="document.getElementById('modal-tt-entry').classList.add('hidden')" class="p-1 rounded-lg text-on-surface-variant hover:bg-surface-container-high cursor-pointer"><span class="material-symbols-outlined">close</span></button>
        </div>
        <?php echo form_open('academics/timetable', array('class' => 'p-6 space-y-4')); ?>
          <input type="hidden" name="action" value="save_entry"/>
          <input type="hidden" name="timetable_id" id="modal_tt_id"/>

          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="block text-label-md mb-1">Academic Year *</label>
              <select name="academic_year_id" id="modal_tt_year" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md">
                <?php foreach ($years as $yr): ?>
                  <option value="<?php echo $yr->academic_year_id; ?>" <?php echo ($selected_year == $yr->academic_year_id) ? 'selected' : ''; ?>>
                    <?php echo html_escape($yr->year_name); ?><?php echo (!empty($yr->is_active)) ? ' (Active)' : ''; ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block text-label-md mb-1">Class *</label>
              <select name="class_id" id="modal_tt_class" onchange="modalClassChanged(this.value)" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md">
                <?php foreach ($classes as $cls): ?>
                  <option value="<?php echo $cls->class_id; ?>" <?php echo ($selected_class == $cls->class_id) ? 'selected' : ''; ?>><?php echo html_escape($cls->class_name); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block text-label-md mb-1">Division *</label>
              <select name="division_id" id="modal_tt_section" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md">
                <?php foreach ($class_sections as $sec): ?>
                  <option value="<?php echo $sec->section_id; ?>" <?php echo ($selected_section == $sec->section_id) ? 'selected' : ''; ?>>Section <?php echo html_escape($sec->section_name); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-label-md mb-1">Day of Week *</label>
              <select name="day" id="modal_tt_day" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md">
                <?php foreach ($days as $d): ?>
                  <option value="<?php echo $d; ?>"><?php echo $d; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block text-label-md mb-1">Period Slot *</label>
              <select name="period_id" id="modal_tt_period" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md">
                <?php foreach ($periods as $p): ?>
                  <option value="<?php echo $p->period_id; ?>"><?php echo html_escape($p->period_name . ' (' . date('h:i A', strtotime($p->start_time)) . '-' . date('h:i A', strtotime($p->end_time)) . ')'); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-label-md mb-1">Subject *</label>
            <select name="subject_id" id="modal_tt_subject" onchange="modalSubjectChanged(this.value)" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md">
              <?php foreach ($subjects as $sub): ?>
                <?php if (empty($sub->class_id) || $sub->class_id == $selected_class): ?>
                  <option value="<?php echo $sub->subject_id; ?>"><?php echo html_escape($sub->subject_name . ' (' . $sub->subject_code . ')'); ?></option>
                <?php endif; ?>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <div class="flex items-center justify-between mb-1">
              <label class="block text-label-md font-semibold">Teacher / Faculty *</label>
              <span id="teacher_source_hint" class="text-[11px] text-secondary font-medium"></span>
            </div>
            <select name="teacher_id" id="modal_tt_teacher" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md">
              <?php foreach ($teachers as $t): ?>
                <option value="<?php echo $t->staff_id; ?>"><?php echo html_escape($t->full_name . ' (' . $t->employee_code . ')'); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t border-outline-variant">
            <button type="button" onclick="document.getElementById('modal-tt-entry').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant cursor-pointer">Cancel</button>
            <button type="submit" class="px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant cursor-pointer">Save Entry</button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <!-- Modal 2: Manage Periods -->
    <div id="modal-period-manager" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden">
      <div class="elevation-3 rounded-2xl bg-surface-container-lowest border border-outline-variant w-full max-w-lg">
        <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant">
          <h3 class="font-headline-md text-headline-md text-on-surface">Manage Period Timings</h3>
          <button onclick="document.getElementById('modal-period-manager').classList.add('hidden')" class="p-1 rounded-lg text-on-surface-variant hover:bg-surface-container-high cursor-pointer"><span class="material-symbols-outlined">close</span></button>
        </div>
        <div class="p-6 space-y-4">
          <div class="table-scroll overflow-x-auto max-h-56 border border-outline-variant/40 rounded-lg">
            <table class="w-full text-body-md text-left">
              <thead>
                <tr class="bg-surface-container-low text-label-md border-b border-outline-variant/40">
                  <th class="p-2.5">Period Name</th>
                  <th class="p-2.5">Start Time</th>
                  <th class="p-2.5">End Time</th>
                  <th class="p-2.5 text-right">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-outline-variant/30">
                <?php foreach ($periods as $p): ?>
                  <tr>
                    <td class="p-2.5 font-medium"><?php echo html_escape($p->period_name); ?></td>
                    <td class="p-2.5"><?php echo date('h:i A', strtotime($p->start_time)); ?></td>
                    <td class="p-2.5"><?php echo date('h:i A', strtotime($p->end_time)); ?></td>
                    <td class="p-2.5 text-right">
                      <a href="<?php echo site_url('academics/delete_period/' . $p->period_id); ?>" onclick="return confirm('Deactivate this period slot?')" class="p-1 rounded text-error hover:bg-error-container/20 inline-flex" title="Delete Period"><span class="material-symbols-outlined text-[16px]">delete</span></a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <?php echo form_open('academics/timetable', array('class' => 'space-y-3 pt-3 border-t border-outline-variant/40')); ?>
            <input type="hidden" name="action" value="add_period"/>
            <input type="hidden" name="academic_year_id" value="<?php echo $selected_year; ?>"/>
            <input type="hidden" name="class_id" value="<?php echo $selected_class; ?>"/>
            <input type="hidden" name="division_id" value="<?php echo $selected_section; ?>"/>
            <h4 class="font-headline-md text-[15px] font-bold text-on-surface">Add New Period Slot</h4>
            <div class="grid grid-cols-3 gap-2">
              <div>
                <label class="block text-[11px] font-medium mb-1">Period Name</label>
                <input type="text" name="period_name" required placeholder="e.g. Period 8" class="w-full px-2.5 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md"/>
              </div>
              <div>
                <label class="block text-[11px] font-medium mb-1">Start Time</label>
                <input type="time" name="start_time" required value="15:15" class="w-full px-2 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md"/>
              </div>
              <div>
                <label class="block text-[11px] font-medium mb-1">End Time</label>
                <input type="time" name="end_time" required value="16:00" class="w-full px-2 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md"/>
              </div>
            </div>
            <div class="flex justify-end pt-2">
              <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant cursor-pointer">Add Period</button>
            </div>
          <?php echo form_close(); ?>
        </div>
      </div>
    </div>

    <script>
      function reloadTimetable() {
        var yr = document.getElementById('tt_filter_year').value;
        var cls = document.getElementById('tt_filter_class').value;
        var sec = document.getElementById('tt_filter_section').value;
        window.location.href = '<?php echo site_url('academics/timetable'); ?>?academic_year_id=' + yr + '&class_id=' + cls + '&section_id=' + sec;
      }

      function handleClassFilterChange(classId) {
        // Fetch sections for newly selected class, select first section, and reload
        fetch('<?php echo site_url('academics/ajax_get_divisions/'); ?>' + classId)
          .then(res => res.json())
          .then(data => {
            var firstSecId = (data.length > 0) ? data[0].section_id : '';
            var yr = document.getElementById('tt_filter_year').value;
            window.location.href = '<?php echo site_url('academics/timetable'); ?>?academic_year_id=' + yr + '&class_id=' + classId + '&section_id=' + firstSecId;
          });
      }

      function openAddEntryModal() {
        document.getElementById('modal_tt_id').value = '';
        document.getElementById('modal-tt-title').innerText = 'Schedule Timetable Period';
        document.getElementById('modal_tt_year').value = '<?php echo $selected_year; ?>';
        document.getElementById('modal_tt_class').value = '<?php echo $selected_class; ?>';
        document.getElementById('modal_tt_section').value = '<?php echo $selected_section; ?>';
        document.getElementById('modal-tt-entry').classList.remove('hidden');
        modalClassChanged('<?php echo $selected_class; ?>');
      }

      function openAddEntryModalForCell(day, periodId) {
        document.getElementById('modal_tt_id').value = '';
        document.getElementById('modal-tt-title').innerText = 'Schedule Timetable Period';
        document.getElementById('modal_tt_year').value = '<?php echo $selected_year; ?>';
        document.getElementById('modal_tt_class').value = '<?php echo $selected_class; ?>';
        document.getElementById('modal_tt_section').value = '<?php echo $selected_section; ?>';
        document.getElementById('modal_tt_day').value = day;
        document.getElementById('modal_tt_period').value = periodId;
        document.getElementById('modal-tt-entry').classList.remove('hidden');
        modalClassChanged('<?php echo $selected_class; ?>');
      }

      function openEditEntryModal(id) {
        fetch('<?php echo site_url('academics/ajax_get_timetable_entry/'); ?>' + id)
          .then(res => res.json())
          .then(data => {
            if (data.success && data.entry) {
              var e = data.entry;
              document.getElementById('modal_tt_id').value = e.timetable_id;
              document.getElementById('modal-tt-title').innerText = 'Edit Timetable Schedule Entry';
              document.getElementById('modal_tt_year').value = e.academic_year_id;
              document.getElementById('modal_tt_class').value = e.class_id;
              document.getElementById('modal_tt_day').value = e.day;
              document.getElementById('modal_tt_period').value = e.period_id;

              // Populate sections & subjects for this class
              modalClassChanged(e.class_id, e.section_id, e.subject_id, e.teacher_id);
              document.getElementById('modal-tt-entry').classList.remove('hidden');
            }
          });
      }

      function modalClassChanged(classId, targetSectionId, targetSubjectId, targetTeacherId) {
        var secSelect = document.getElementById('modal_tt_section');
        var subSelect = document.getElementById('modal_tt_subject');

        if (!classId) return;

        // Fetch Sections
        fetch('<?php echo site_url('academics/ajax_get_divisions/'); ?>' + classId)
          .then(res => res.json())
          .then(data => {
            var opts = '';
            data.forEach(function(s) {
              var sel = (targetSectionId && s.section_id == targetSectionId) ? 'selected' : '';
              opts += '<option value="' + s.section_id + '" ' + sel + '>Section ' + s.section_name + '</option>';
            });
            secSelect.innerHTML = opts;
          });

        // Fetch Subjects
        fetch('<?php echo site_url('academics/ajax_get_subjects/'); ?>' + classId)
          .then(res => res.json())
          .then(data => {
            var opts = '';
            data.forEach(function(s) {
              var sel = (targetSubjectId && s.subject_id == targetSubjectId) ? 'selected' : '';
              opts += '<option value="' + s.subject_id + '" ' + sel + '>' + s.subject_name + ' (' + s.subject_code + ')</option>';
            });
            subSelect.innerHTML = opts;

            // Trigger subject teacher fetch for the first/selected subject
            var curSubjectId = targetSubjectId || (data.length > 0 ? data[0].subject_id : '');
            if (curSubjectId) {
              modalSubjectChanged(curSubjectId, targetTeacherId);
            }
          });
      }

      function modalSubjectChanged(subjectId, targetTeacherId) {
        var yr = document.getElementById('modal_tt_year').value;
        var cls = document.getElementById('modal_tt_class').value;
        var sec = document.getElementById('modal_tt_section').value;
        var teachSelect = document.getElementById('modal_tt_teacher');
        var hint = document.getElementById('teacher_source_hint');

        if (!subjectId) return;

        fetch('<?php echo site_url('academics/ajax_get_teachers_for_subject'); ?>?academic_year_id=' + yr + '&class_id=' + cls + '&section_id=' + sec + '&subject_id=' + subjectId)
          .then(res => res.json())
          .then(data => {
            if (data.source === 'assigned') {
              hint.innerText = '(Showing allocated subject teachers)';
            } else {
              hint.innerText = '(All teaching faculty)';
            }
            var opts = '';
            data.teachers.forEach(function(t) {
              var sel = (targetTeacherId && t.staff_id == targetTeacherId) ? 'selected' : '';
              opts += '<option value="' + t.staff_id + '" ' + sel + '>' + t.full_name + ' (' + t.employee_code + ')</option>';
            });
            teachSelect.innerHTML = opts;
          });
      }
    </script>
