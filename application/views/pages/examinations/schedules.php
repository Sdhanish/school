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
        <h2 class="font-headline-md text-headline-md text-on-surface">Exam Schedules</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Schedule date, timings, room halls, invigilators, and passing marks for each exam subject.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('examinations/allocations'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Add Schedule
        </a>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('examinations/schedules'); ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Exam</label>
          <select name="exam_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Exams</option>
            <?php foreach ($exams as $e): ?>
              <option value="<?php echo $e->exam_id; ?>" <?php echo ($filters['exam_id'] == $e->exam_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($e->exam_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Class</label>
          <select name="class_id" id="filter-class-id" onchange="onFilterClassChange(this)" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Classes</option>
            <?php foreach ($classes as $c): ?>
              <option value="<?php echo $c->class_id; ?>" <?php echo ($filters['class_id'] == $c->class_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($c->class_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Division</label>
          <select name="division_id" id="filter-division-id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Divisions</option>
            <?php foreach ($divisions as $s): ?>
              <option value="<?php echo $s->division_id; ?>" <?php echo (($filters['division_id'] ?? '') == $s->division_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($s->class_name . ' ' . $s->division_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Subject</label>
          <select name="subject_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Subjects</option>
            <?php foreach ($subjects as $sub): ?>
              <option value="<?php echo $sub->subject_id; ?>" <?php echo ($filters['subject_id'] == $sub->subject_id) ? 'selected' : ''; ?>>
                <?php echo html_escape((empty($filters['class_id']) && !empty($sub->class_name) ? $sub->class_name . ' - ' : '') . $sub->subject_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </form>
    </div>

    <!-- Schedules Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Scheduled Papers (<?php echo count($schedules); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Date & Time</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Class & Division</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Subject</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Exam</th>
              <th class="text-right px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Max / Pass</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Room & Invigilator</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($schedules)): ?>
              <tr><td colspan="7" class="px-4 py-8 text-center text-on-surface-variant text-body-md">No exam papers scheduled matching criteria.</td></tr>
            <?php else: ?>
              <?php foreach ($schedules as $s): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap">
                    <div class="font-bold text-on-surface font-mono"><?php echo date('d M Y', strtotime($s->exam_date)); ?></div>
                    <div class="text-[12px] font-mono text-on-surface-variant"><?php echo date('h:i A', strtotime($s->start_time)) . ' - ' . date('h:i A', strtotime($s->end_time)); ?></div>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-medium text-on-surface">
                    <?php echo html_escape($s->class_name . ' ' . ($s->division_name ?: '')); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-semibold text-on-surface">
                    <?php echo html_escape($s->subject_name); ?>
                    <span class="text-[11px] text-on-surface-variant block font-normal font-mono"><?php echo html_escape($s->subject_code ?: ''); ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-body-md text-on-surface-variant">
                    <?php echo html_escape($s->exam_name); ?>
                  </td>
                  <td class="px-4 py-3 text-right whitespace-nowrap font-mono text-body-md">
                    <span class="font-bold text-on-surface"><?php echo (int)$s->max_marks; ?></span>
                    <span class="text-on-surface-variant">/ <?php echo (int)$s->passing_marks; ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-body-md">
                    <div class="font-medium text-on-surface"><?php echo html_escape($s->room_no ?: 'Hall 1'); ?></div>
                    <div class="text-[12px] text-on-surface-variant"><?php echo html_escape($s->teacher_name ?: 'Unassigned'); ?></div>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <div class="flex items-center justify-center gap-1">
                      <!-- Enter Marks -->
                      <a href="<?php echo site_url('examinations/marks_entry?schedule_id=' . $s->schedule_id); ?>" class="p-1.5 rounded-lg hover:bg-surface-container-high text-secondary transition-colors cursor-pointer" title="Enter Marks">
                        <span class="material-symbols-outlined text-[18px]">edit_note</span>
                      </a>

                      <!-- Edit Modal -->
                      <button type="button" onclick='editScheduleModal(<?php echo json_encode($s); ?>)' class="p-1.5 rounded-lg hover:bg-surface-container-high text-on-surface-variant transition-colors cursor-pointer" title="Edit">
                        <span class="material-symbols-outlined text-[18px]">edit</span>
                      </button>

                      <!-- Delete -->
                      <?php echo form_open('examinations/schedules', array('class' => 'inline', 'onsubmit' => 'return confirm("Delete this schedule entry?");')); ?>
                        <input type="hidden" name="action" value="delete"/>
                        <input type="hidden" name="schedule_id" value="<?php echo $s->schedule_id; ?>"/>
                        <button type="submit" class="p-1.5 rounded-lg hover:bg-error-container text-error transition-colors cursor-pointer" title="Delete">
                          <span class="material-symbols-outlined text-[18px]">delete</span>
                        </button>
                      <?php echo form_close(); ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- EDIT SCHEDULE MODAL -->
    <div id="schedule-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
      <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant max-w-xl w-full p-6 elevation-3 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 id="modal-sched-title" class="font-headline-md text-title-lg text-on-surface font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[22px]">edit_calendar</span>Edit Exam Schedule
          </h3>
          <button onclick="closeScheduleModal()" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('examinations/schedules', array('class' => 'space-y-4')); ?>
          <input type="hidden" name="schedule_id" id="modal-sched-id" value="0"/>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Exam *</label>
              <select name="exam_id" id="modal-sched-exam" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <?php foreach ($exams as $e): ?>
                  <option value="<?php echo $e->exam_id; ?>"><?php echo html_escape($e->exam_name); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Academic Year *</label>
              <select name="academic_year_id" id="modal-sched-year" required onchange="onModalYearChange(this.value)" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <?php foreach ($academic_years as $ay): ?>
                  <option value="<?php echo $ay->academic_year_id; ?>" <?php echo (!empty($ay->is_active)) ? 'selected' : ''; ?>>
                    <?php echo html_escape($ay->year_name); ?><?php echo (!empty($ay->is_active)) ? ' (Active)' : ''; ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Class *</label>
              <select name="class_id" id="modal-sched-class" required onchange="onModalClassChange(this.value)" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <?php foreach ($classes as $c): ?>
                  <option value="<?php echo $c->class_id; ?>"><?php echo html_escape($c->class_name); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Division</label>
              <select name="division_id" id="modal-sched-division" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="">All Divisions</option>
              </select>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Subject *</label>
              <select name="subject_id" id="modal-sched-subject" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="">-- Select Subject --</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Exam Date *</label>
              <input type="date" name="exam_date" id="modal-sched-date" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Start Time *</label>
              <input type="time" name="start_time" id="modal-sched-start" required value="09:30" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">End Time *</label>
              <input type="time" name="end_time" id="modal-sched-end" required value="12:30" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Maximum Marks *</label>
              <input type="number" step="0.5" min="1" name="max_marks" id="modal-sched-max" required value="100" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Passing Marks *</label>
              <input type="number" step="0.5" min="0" name="passing_marks" id="modal-sched-pass" required value="35" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Room / Examination Hall</label>
              <input type="text" name="room_no" id="modal-sched-room" placeholder="e.g. Hall A-101" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Invigilator / Teacher</label>
              <select name="teacher_id" id="modal-sched-teacher" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="">Unassigned</option>
                <?php foreach ($teachers as $t): ?>
                  <option value="<?php echo $t->staff_id; ?>"><?php echo html_escape($t->full_name); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Instructions</label>
            <textarea name="instructions" id="modal-sched-instructions" rows="2" placeholder="Specific paper instructions..." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-outline-variant/50">
            <button type="button" onclick="closeScheduleModal()" class="px-4 py-2 rounded-lg bg-surface-container-high text-on-surface text-label-md font-medium hover:bg-surface-container-highest cursor-pointer">
              Cancel
            </button>
            <button type="submit" class="px-5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant cursor-pointer">
              Save Schedule
            </button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <!-- Modal Scripts -->
    <script>
      var divisionsAjaxUrl = "<?php echo site_url('examinations/ajax_get_divisions'); ?>";
      var subjectsAjaxUrl  = "<?php echo site_url('examinations/ajax_get_subjects'); ?>";
      var subjectAbortController = null;

      function onFilterClassChange(classSelect) {
        var divSelect = classSelect.form.querySelector('select[name="division_id"]');
        if (divSelect) {
          divSelect.value = '';
        }
        var subSelect = classSelect.form.querySelector('select[name="subject_id"]');
        if (subSelect) {
          subSelect.value = '';
        }
        classSelect.form.submit();
      }

      function loadModalDivisions(classId, selectedDivisionId) {
        var divSelect = document.getElementById('modal-sched-division');
        if (!divSelect) return;
        divSelect.innerHTML = '<option value="">Loading divisions...</option>';

        if (!classId) {
          divSelect.innerHTML = '<option value="">All Divisions</option>';
          return;
        }

        fetch(divisionsAjaxUrl + '/' + classId)
          .then(function(response) { return response.json(); })
          .then(function(data) {
            divSelect.innerHTML = '<option value="">All Divisions</option>';
            if (data && data.length > 0) {
              data.forEach(function(d) {
                var opt = document.createElement('option');
                opt.value = d.division_id;
                opt.textContent = (d.class_name ? d.class_name + ' ' : '') + d.division_name;
                if (selectedDivisionId && (String(d.division_id) === String(selectedDivisionId))) {
                  opt.selected = true;
                }
                divSelect.appendChild(opt);
              });
            }
            if (selectedDivisionId) {
              divSelect.value = selectedDivisionId;
            }
          })
          .catch(function(err) {
            console.error('Error fetching divisions:', err);
            divSelect.innerHTML = '<option value="">All Divisions</option>';
          });
      }

      function loadModalSubjects(classId, selectedSubjectId, academicYearId, fallbackSubjectName) {
        var subjSelect = document.getElementById('modal-sched-subject');
        if (!subjSelect) return;

        // Immediately clear selection and show placeholder
        subjSelect.innerHTML = '<option value="">-- Select Subject --</option>';
        subjSelect.value = '';

        if (!classId) {
          return;
        }

        if (subjectAbortController) {
          try { subjectAbortController.abort(); } catch(e) {}
        }
        if (window.AbortController) {
          subjectAbortController = new AbortController();
        }

        var yearId = academicYearId || (document.getElementById('modal-sched-year') ? document.getElementById('modal-sched-year').value : '');
        var url = subjectsAjaxUrl + '/' + encodeURIComponent(classId);
        if (yearId) {
          url += '?academic_year_id=' + encodeURIComponent(yearId);
        }

        subjSelect.innerHTML = '<option value="">Loading subjects...</option>';
        subjSelect.disabled = true;

        var fetchOptions = subjectAbortController ? { signal: subjectAbortController.signal } : {};

        fetch(url, fetchOptions)
          .then(function(response) {
            if (!response.ok) {
              throw new Error('HTTP ' + response.status);
            }
            return response.json();
          })
          .then(function(data) {
            subjSelect.disabled = false;
            subjSelect.innerHTML = '<option value="">-- Select Subject --</option>';

            var foundSelected = false;
            var addedIds = {};

            if (Array.isArray(data) && data.length > 0) {
              data.forEach(function(s) {
                var sId = String(s.subject_id);
                if (addedIds[sId]) return;
                addedIds[sId] = true;

                var opt = document.createElement('option');
                opt.value = s.subject_id;
                opt.textContent = s.subject_name + (s.subject_code ? ' (' + s.subject_code + ')' : '');
                if (selectedSubjectId && String(s.subject_id) === String(selectedSubjectId)) {
                  opt.selected = true;
                  foundSelected = true;
                }
                subjSelect.appendChild(opt);
              });
            }

            // If an existing schedule had a subject that is no longer in current allocations, retain safely
            if (selectedSubjectId && !foundSelected) {
              var legacyOpt = document.createElement('option');
              legacyOpt.value = selectedSubjectId;
              legacyOpt.textContent = (fallbackSubjectName ? fallbackSubjectName : 'Subject #' + selectedSubjectId) + ' (Unallocated)';
              legacyOpt.selected = true;
              subjSelect.appendChild(legacyOpt);
            }

            if (!selectedSubjectId) {
              subjSelect.value = '';
            }
          })
          .catch(function(err) {
            if (err.name === 'AbortError') {
              return;
            }
            console.error('Error fetching class subjects:', err);
            subjSelect.disabled = false;
            subjSelect.innerHTML = '<option value="">-- Select Subject --</option>';
            subjSelect.value = '';
          });
      }

      function onModalClassChange(classId) {
        // 1. Immediately clear currently selected Subject and any stale value
        var subjSelect = document.getElementById('modal-sched-subject');
        if (subjSelect) {
          subjSelect.innerHTML = '<option value="">-- Select Subject --</option>';
          subjSelect.value = '';
        }

        // 2. Load divisions
        loadModalDivisions(classId, '');

        // 3. Request subjects for newly selected class (previous selection is NOT retained)
        var yearId = document.getElementById('modal-sched-year') ? document.getElementById('modal-sched-year').value : '';
        loadModalSubjects(classId, '', yearId, '');
      }

      function onModalYearChange(yearId) {
        var classId = document.getElementById('modal-sched-class') ? document.getElementById('modal-sched-class').value : '';
        if (classId) {
          var subjSelect = document.getElementById('modal-sched-subject');
          if (subjSelect) {
            subjSelect.innerHTML = '<option value="">-- Select Subject --</option>';
            subjSelect.value = '';
          }
          loadModalSubjects(classId, '', yearId, '');
        }
      }

      function openScheduleModal() {
        window.location.href = "<?php echo site_url('examinations/allocations'); ?>";
      }

      function editScheduleModal(item) {
        document.getElementById('modal-sched-id').value = item.schedule_id;
        document.getElementById('modal-sched-title').textContent = 'Edit Exam Schedule';
        document.getElementById('modal-sched-exam').value = item.exam_id;
        document.getElementById('modal-sched-year').value = item.academic_year_id;
        document.getElementById('modal-sched-class').value = item.class_id;

        loadModalDivisions(item.class_id, item.division_id || item.section_id || '');
        loadModalSubjects(item.class_id, item.subject_id, item.academic_year_id, item.subject_name || '');

        document.getElementById('modal-sched-date').value = item.exam_date;
        document.getElementById('modal-sched-start').value = item.start_time;
        document.getElementById('modal-sched-end').value = item.end_time;
        document.getElementById('modal-sched-max').value = item.max_marks;
        document.getElementById('modal-sched-pass').value = item.passing_marks;
        document.getElementById('modal-sched-room').value = item.room_no || '';
        document.getElementById('modal-sched-teacher').value = item.teacher_id || '';
        document.getElementById('modal-sched-instructions').value = item.instructions || '';

        var modal = document.getElementById('schedule-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
      }

      function closeScheduleModal() {
        var modal = document.getElementById('schedule-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      }
    </script>
