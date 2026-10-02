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
        <h2 class="font-headline-md text-headline-md text-on-surface">Marks Entry</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Enter, draft, and submit subject examination marks with numeric validation, absent/exempted flags, and live grade calculations.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('examinations/verification'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">verified</span>Verification Queue
        </a>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form id="marks-filter-form" method="get" action="<?php echo site_url('examinations/marks_entry'); ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Exam *</label>
          <select name="exam_id" id="filter-exam-id" onchange="checkAndSubmitFilters()" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">-- Select Exam --</option>
            <?php foreach ($exams as $e): ?>
              <option value="<?php echo $e->exam_id; ?>" <?php echo ($selected_exam == $e->exam_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($e->exam_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Class *</label>
          <select name="class_id" id="filter-class-id" onchange="onFilterClassChange(this.value)" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">-- Select Class --</option>
            <?php foreach ($classes as $c): ?>
              <option value="<?php echo $c->class_id; ?>" <?php echo ($selected_class == $c->class_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($c->class_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Division *</label>
          <select name="division_id" id="filter-division-id" onchange="checkAndSubmitFilters()" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary" <?php echo empty($selected_class) ? 'disabled' : ''; ?>>
            <option value="">-- Select Division --</option>
            <?php foreach ($divisions as $s): ?>
              <?php if (!$selected_class || $s->class_id == $selected_class): ?>
                <option value="<?php echo $s->division_id; ?>" <?php echo (($selected_division ?? $selected_section) == $s->division_id) ? 'selected' : ''; ?>>
                  <?php echo html_escape($s->class_name . ' ' . $s->division_name); ?>
                </option>
              <?php endif; ?>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Subject *</label>
          <select name="subject_id" id="filter-subject-id" onchange="checkAndSubmitFilters()" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary" <?php echo empty($selected_class) ? 'disabled' : ''; ?>>
            <?php if (empty($selected_class)): ?>
              <option value="" disabled selected>-- Select Class First --</option>
            <?php elseif (empty($subjects)): ?>
              <option value="" disabled selected>No subjects are assigned to this class.</option>
            <?php else: ?>
              <option value="">-- Select Subject --</option>
              <?php foreach ($subjects as $sub): ?>
                <option value="<?php echo $sub->subject_id; ?>" <?php echo ($selected_subject == $sub->subject_id) ? 'selected' : ''; ?>>
                  <?php echo html_escape($sub->subject_name); ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>
      </form>
    </div>

    <?php if (!empty($schedule_not_found)): ?>
      <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-amber-300 p-8 text-center text-on-surface-variant mb-6">
        <span class="material-symbols-outlined text-[48px] text-amber-500 mb-2">event_busy</span>
        <h3 class="font-headline-md text-title-lg font-bold text-on-surface">No Exam Schedule Found</h3>
        <p class="text-body-md mt-1 max-w-lg mx-auto">An exam schedule has not yet been created for this Exam, Class, Division, and Subject. Please add this subject to the exam schedule under <a href="<?php echo site_url('examinations/allocations'); ?>" class="text-primary font-semibold hover:underline">Add Schedule</a> or <a href="<?php echo site_url('examinations/schedules'); ?>" class="text-primary font-semibold hover:underline">Exam Schedules</a>.</p>
      </div>
    <?php endif; ?>

    <!-- Marks Entry Sheet -->
    <?php if ($marksheet): ?>
      <div id="marksheet-container">
      <?php
        $totalStudents = count($marksheet->students);
        $completedStudents = 0;
        foreach ($marksheet->students as $stu) {
          if ($stu->mark_id && ($stu->marks_obtained !== null || $stu->is_absent || $stu->is_exempted)) {
            $completedStudents++;
          }
        }
        $compPct = ($totalStudents > 0) ? round(($completedStudents / $totalStudents) * 100, 1) : 0;
      ?>

      <!-- Header Info Banner -->
      <div class="p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-xl bg-primary text-white flex items-center justify-center font-bold text-xl shrink-0">
            <span class="material-symbols-outlined text-[28px]">description</span>
          </div>
          <div>
            <div class="font-headline-md text-title-lg font-bold text-on-surface">
              <?php echo html_escape($marksheet->subject_name); ?> — <?php echo html_escape($marksheet->class_name . ' ' . ($marksheet->division_name ?: '')); ?>
            </div>
            <div class="text-body-md text-on-surface-variant">
              Exam: <strong><?php echo html_escape($marksheet->exam_name); ?></strong> • Date: <?php echo date('d M Y', strtotime($marksheet->exam_date)); ?> • Max Marks: <strong class="text-primary font-mono"><?php echo (int)$marksheet->max_marks; ?></strong> • Passing: <strong class="text-secondary font-mono"><?php echo (int)$marksheet->passing_marks; ?></strong>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-4 shrink-0">
          <div class="text-right">
            <div class="text-body-md font-bold text-on-surface"><?php echo $completedStudents; ?> / <?php echo $totalStudents; ?> Completed</div>
            <div class="text-[12px] text-on-surface-variant"><?php echo $compPct; ?>% filled</div>
          </div>
          <div class="w-24 bg-surface-container-high rounded-full h-3 overflow-hidden">
            <div class="bg-secondary h-3 rounded-full" style="width: <?php echo $compPct; ?>%"></div>
          </div>
        </div>
      </div>

      <?php echo form_open('examinations/marks_entry', array('id' => 'marks-entry-form')); ?>
        <input type="hidden" name="schedule_id" value="<?php echo $marksheet->schedule_id; ?>"/>
        <input type="hidden" name="action" id="form-action" value="draft"/>

        <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
          <div class="table-scroll overflow-x-auto">
            <table class="w-full data-table zebra border-collapse">
              <thead>
                <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
                  <th class="text-center px-3 py-3 text-label-md font-semibold text-on-surface-variant uppercase w-16">Roll #</th>
                  <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Student</th>
                  <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Max</th>
                  <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap w-36">Marks Obtained</th>
                  <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Absent</th>
                  <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Exempted</th>
                  <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Grade</th>
                  <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Remarks</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-outline-variant/40">
                <?php if (empty($marksheet->students)): ?>
                  <tr><td colspan="8" class="px-4 py-8 text-center text-on-surface-variant text-body-md">No students enrolled in this division.</td></tr>
                <?php else: ?>
                  <?php $tabIndex = 1; foreach ($marksheet->students as $stu): ?>
                    <?php
                      $maxMarks = (float)$marksheet->max_marks;
                      $passMarks = (float)$marksheet->passing_marks;
                      $isAbsent = (bool)$stu->is_absent;
                      $isExempted = (bool)$stu->is_exempted;
                      $marksVal = ($stu->marks_obtained !== null && !$isAbsent) ? $stu->marks_obtained : '';
                    ?>
                    <tr class="hover:bg-surface-container-low transition-colors marks-row" data-student-id="<?php echo $stu->student_id; ?>">
                      <td class="px-3 py-3 text-center font-mono font-bold text-primary">
                        <?php echo html_escape($stu->roll_number ?: '—'); ?>
                      </td>
                      <td class="px-4 py-3 whitespace-nowrap">
                        <div class="font-bold text-on-surface"><?php echo html_escape($stu->first_name . ' ' . $stu->last_name); ?></div>
                        <div class="text-[12px] text-on-surface-variant font-mono"><?php echo html_escape($stu->admission_number); ?></div>
                      </td>
                      <td class="px-4 py-3 text-center font-mono text-body-md text-on-surface-variant">
                        <?php echo (int)$maxMarks; ?>
                      </td>
                      <td class="px-4 py-3 whitespace-nowrap">
                        <input type="number" step="0.5" min="0" max="<?php echo $maxMarks; ?>"
                          name="marks[<?php echo $stu->student_id; ?>][marks_obtained]"
                          value="<?php echo $marksVal; ?>"
                          tabindex="<?php echo $tabIndex++; ?>"
                          class="marks-input w-28 px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-right font-mono font-bold text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary <?php echo ($isAbsent || $isExempted) ? 'opacity-40 pointer-events-none' : ''; ?>"
                          oninput="calculateRowGrade(this, <?php echo $maxMarks; ?>, <?php echo $passMarks; ?>)"
                          placeholder="0.00"
                        />
                      </td>
                      <td class="px-4 py-3 text-center whitespace-nowrap">
                        <label class="inline-flex items-center gap-1 cursor-pointer">
                          <input type="checkbox" name="marks[<?php echo $stu->student_id; ?>][is_absent]" value="1" <?php echo $isAbsent ? 'checked' : ''; ?>
                            onchange="toggleSpecialStatus(this, 'absent')"
                            class="absent-check w-4 h-4 rounded text-error focus:ring-error"
                          />
                          <span class="text-[12px] font-semibold text-error">ABS</span>
                        </label>
                      </td>
                      <td class="px-4 py-3 text-center whitespace-nowrap">
                        <label class="inline-flex items-center gap-1 cursor-pointer">
                          <input type="checkbox" name="marks[<?php echo $stu->student_id; ?>][is_exempted]" value="1" <?php echo $isExempted ? 'checked' : ''; ?>
                            onchange="toggleSpecialStatus(this, 'exempted')"
                            class="exempted-check w-4 h-4 rounded text-primary focus:ring-primary"
                          />
                          <span class="text-[12px] font-semibold text-primary">EXM</span>
                        </label>
                      </td>
                      <td class="px-4 py-3 text-center whitespace-nowrap">
                        <span class="grade-badge px-2.5 py-1 rounded-full text-[11px] font-bold bg-surface-container-high text-on-surface">
                          <?php echo html_escape($stu->grade ?: '—'); ?>
                        </span>
                      </td>
                      <td class="px-4 py-3 whitespace-nowrap">
                        <input type="text" name="marks[<?php echo $stu->student_id; ?>][remarks]" value="<?php echo html_escape($stu->remarks ?? ''); ?>" placeholder="Optional remark..." class="w-44 px-2.5 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-[13px]"/>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Submit / Draft Action Bar -->
        <div class="flex items-center justify-between p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
          <div class="text-[13px] text-on-surface-variant">
            Status: <span class="font-semibold text-on-surface"><?php echo $marksheet->students[0]->mark_status ?? 'Not Saved'; ?></span>
          </div>
          <div class="flex items-center gap-2">
            <!-- Save Draft -->
            <button type="button" onclick="submitMarksForm('draft')" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-lowest text-label-md font-semibold hover:bg-surface-container-high transition-colors cursor-pointer">
              <span class="material-symbols-outlined text-[18px]">save</span>Save Draft
            </button>

            <!-- Submit for Verification -->
            <button type="button" onclick="submitMarksForm('submit')" class="inline-flex items-center gap-1.5 px-6 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
              <span class="material-symbols-outlined text-[18px]">send</span>Submit Marks
            </button>
          </div>
        </div>
      <?php echo form_close(); ?>

      <script>
        function calculateRowGrade(input, maxMarks, passMarks) {
          var val = parseFloat(input.value);
          var row = input.closest('.marks-row');
          var badge = row.querySelector('.grade-badge');

          if (isNaN(val)) {
            badge.textContent = '—';
            badge.className = 'grade-badge px-2.5 py-1 rounded-full text-[11px] font-bold bg-surface-container-high text-on-surface';
            return;
          }

          var pct = (maxMarks > 0) ? (val / maxMarks) * 100 : 0;
          var g = 'F';
          var cls = 'bg-error-container text-on-error-container';

          if (pct >= 90) { g = 'A+'; cls = 'bg-secondary-container text-on-secondary-container'; }
          else if (pct >= 80) { g = 'A'; cls = 'bg-secondary-container text-on-secondary-container'; }
          else if (pct >= 70) { g = 'B+'; cls = 'bg-primary text-white'; }
          else if (pct >= 60) { g = 'B'; cls = 'bg-primary text-white'; }
          else if (pct >= 50) { g = 'C'; cls = 'bg-amber-100 text-amber-900'; }
          else if (pct >= 40) { g = 'D'; cls = 'bg-amber-100 text-amber-900'; }

          badge.textContent = g;
          badge.className = 'grade-badge px-2.5 py-1 rounded-full text-[11px] font-bold ' + cls;
        }

        function toggleSpecialStatus(check, type) {
          var row = check.closest('.marks-row');
          var marksInput = row.querySelector('.marks-input');
          var badge = row.querySelector('.grade-badge');
          var absCheck = row.querySelector('.absent-check');
          var exmCheck = row.querySelector('.exempted-check');

          if (type === 'absent' && check.checked) {
            exmCheck.checked = false;
            marksInput.value = '';
            marksInput.classList.add('opacity-40', 'pointer-events-none');
            badge.textContent = 'ABS';
            badge.className = 'grade-badge px-2.5 py-1 rounded-full text-[11px] font-bold bg-error-container text-on-error-container';
          } else if (type === 'exempted' && check.checked) {
            absCheck.checked = false;
            marksInput.value = '';
            marksInput.classList.add('opacity-40', 'pointer-events-none');
            badge.textContent = 'EXM';
            badge.className = 'grade-badge px-2.5 py-1 rounded-full text-[11px] font-bold bg-primary text-white';
          } else {
            marksInput.classList.remove('opacity-40', 'pointer-events-none');
            badge.textContent = '—';
            badge.className = 'grade-badge px-2.5 py-1 rounded-full text-[11px] font-bold bg-surface-container-high text-on-surface';
          }
        }

        function submitMarksForm(actionType) {
          document.getElementById('form-action').value = actionType;
          if (actionType === 'submit') {
            if (!confirm("Are you sure you want to submit these marks? Once submitted, they will be sent to the Principal/Admin for verification.")) {
              return;
            }
          }
          document.getElementById('marks-entry-form').submit();
        }
      </script>
      </div><!-- /#marksheet-container -->
    <?php else: ?>
      <div id="empty-placeholder" class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-12 text-center text-on-surface-variant">
        <span class="material-symbols-outlined text-[48px] text-primary mb-3">fact_check</span>
        <h3 class="font-headline-md text-title-lg font-bold text-on-surface">Select Exam, Class, Division & Subject</h3>
        <p class="text-body-md mt-1 max-w-md mx-auto">Please choose an exam, class, division, and subject from the dropdown filters above to load the student marksheet.</p>
      </div>
    <?php endif; ?>

    <script>
      var ajaxDivisionsUrl = "<?php echo site_url('examinations/ajax_get_divisions'); ?>";
      var ajaxSubjectsUrl  = "<?php echo site_url('examinations/ajax_get_subjects'); ?>";

      function onFilterClassChange(classId) {
        var form = document.getElementById('marks-filter-form');
        var divSelect = document.getElementById('filter-division-id');
        var subSelect = document.getElementById('filter-subject-id');
        var marksheetBox = document.getElementById('marksheet-container');
        var placeholderBox = document.getElementById('empty-placeholder');

        // Hide stale marksheet view immediately
        if (marksheetBox) marksheetBox.style.display = 'none';
        if (placeholderBox) placeholderBox.style.display = 'block';

        // Clear existing options completely
        divSelect.innerHTML = '';
        subSelect.innerHTML = '';

        if (!classId) {
          divSelect.innerHTML = '<option value="" disabled selected>-- Select Class First --</option>';
          divSelect.disabled = true;
          subSelect.innerHTML = '<option value="" disabled selected>-- Select Class First --</option>';
          subSelect.disabled = true;
          return;
        }

        // Show loading state
        divSelect.disabled = true;
        divSelect.innerHTML = '<option value="">Loading divisions...</option>';
        subSelect.disabled = true;
        subSelect.innerHTML = '<option value="">Loading subjects...</option>';

        // Fetch divisions for selected class
        fetch(ajaxDivisionsUrl + '/' + encodeURIComponent(classId))
          .then(function(res) { return res.json(); })
          .then(function(divisions) {
            divSelect.innerHTML = '<option value="">-- Select Division --</option>';
            if (divisions && divisions.length > 0) {
              divisions.forEach(function(d) {
                var opt = document.createElement('option');
                opt.value = d.division_id;
                opt.textContent = (d.class_name ? d.class_name + ' ' : '') + d.division_name;
                divSelect.appendChild(opt);
              });
              divSelect.disabled = false;
            } else {
              divSelect.innerHTML = '<option value="" disabled selected>No divisions found for this class.</option>';
            }
          })
          .catch(function(err) {
            console.error('Error fetching divisions:', err);
            divSelect.innerHTML = '<option value="" disabled selected>Unable to load divisions.</option>';
          });

        // Fetch allocated subjects for selected class
        fetch(ajaxSubjectsUrl + '/' + encodeURIComponent(classId))
          .then(function(res) { return res.json(); })
          .then(function(subjects) {
            subSelect.innerHTML = '';
            if (subjects && subjects.length > 0) {
              var defaultOpt = document.createElement('option');
              defaultOpt.value = '';
              defaultOpt.textContent = '-- Select Subject --';
              subSelect.appendChild(defaultOpt);

              subjects.forEach(function(s) {
                var opt = document.createElement('option');
                opt.value = s.subject_id;
                opt.textContent = s.subject_name;
                subSelect.appendChild(opt);
              });
              subSelect.disabled = false;
            } else {
              subSelect.innerHTML = '<option value="" disabled selected>No subjects are assigned to this class.</option>';
              subSelect.disabled = true;
            }
          })
          .catch(function(err) {
            console.error('Error fetching subjects:', err);
            subSelect.innerHTML = '<option value="" disabled selected>Unable to load subjects. Please try again.</option>';
            subSelect.disabled = false;
          });
      }

      function checkAndSubmitFilters() {
        var examId = document.getElementById('filter-exam-id').value;
        var classId = document.getElementById('filter-class-id').value;
        var divisionId = document.getElementById('filter-division-id').value;
        var subjectId = document.getElementById('filter-subject-id').value;

        if (examId && classId && divisionId && subjectId) {
          document.getElementById('marks-filter-form').submit();
        }
      }
    </script>
