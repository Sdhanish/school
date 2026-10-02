<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Teacher Workload Management</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Assign academic subjects, classes, divisions, and weekly period schedules to teaching faculty.</p>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <button onclick="openAddWorkloadModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Assign Workload
        </button>
      </div>
    </div>

    <!-- Filters Bar -->
    <div class="flex flex-col md:flex-row gap-3 mb-4 flex-wrap">
      <select onchange="applyFilter('staff_id', this.value)" class="px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md text-on-surface-variant">
        <option value="">All Teachers</option>
        <?php foreach ($teachers as $t): ?>
          <option value="<?php echo $t->staff_id; ?>" <?php echo ($this->input->get('staff_id') == $t->staff_id) ? 'selected' : ''; ?>><?php echo html_escape($t->full_name . ' (' . $t->employee_code . ')'); ?></option>
        <?php endforeach; ?>
      </select>
      <select onchange="applyFilter('class_id', this.value)" class="px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md text-on-surface-variant">
        <option value="">All Classes</option>
        <?php foreach ($classes as $cls): ?>
          <option value="<?php echo $cls->class_id; ?>" <?php echo ($this->input->get('class_id') == $cls->class_id) ? 'selected' : ''; ?>><?php echo html_escape($cls->class_name); ?></option>
        <?php endforeach; ?>
      </select>
      <select onchange="applyFilter('division_id', this.value)" class="px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md text-on-surface-variant">
        <option value="">All Divisions</option>
        <?php 
          $filterClass = $this->input->get('class_id');
          foreach ($divisions as $div): 
            if ($filterClass && $div->class_id != $filterClass) continue;
        ?>
          <option value="<?php echo $div->division_id; ?>" <?php echo (($this->input->get('division_id') ?: $this->input->get('section_id')) == $div->division_id) ? 'selected' : ''; ?>>
            <?php echo html_escape((!empty($div->division_name) && strpos($div->division_name, $div->class_name) !== false) ? $div->division_name : trim($div->class_name . ' ' . $div->division_name)); ?>
          </option>
        <?php endforeach; ?>
      </select>
      <select onchange="applyFilter('subject_id', this.value)" class="px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md text-on-surface-variant">
        <option value="">All Subjects</option>
        <?php foreach ($subjects as $sub): ?>
          <option value="<?php echo $sub->subject_id; ?>" <?php echo ($this->input->get('subject_id') == $sub->subject_id) ? 'selected' : ''; ?>><?php echo html_escape($sub->subject_name); ?></option>
        <?php endforeach; ?>
      </select>
      <a href="<?php echo site_url('staff/workload'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors"><span class="material-symbols-outlined text-[18px]">restart_alt</span>Reset</a>
    </div>

    <!-- Workloads Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60">
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Teacher Name</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Employee Code</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Subject</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Class & Division</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Periods/Week</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Working Days</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Remarks</th>
              <th class="text-right px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/30 text-body-md">
            <?php if (empty($workloads)): ?>
              <tr><td colspan="8" class="px-4 py-8 text-center text-on-surface-variant">No teacher workload allocations found.</td></tr>
            <?php endif; ?>
            <?php foreach ($workloads as $wl): ?>
              <?php 
                $formattedClassDiv = (!empty($wl->division_name) && strpos($wl->division_name, $wl->class_name) !== false)
                    ? $wl->division_name
                    : trim(($wl->class_name ?? '') . ' ' . ($wl->division_name ?? ''));
              ?>
              <tr class='hover:bg-surface-container-low transition-colors'>
                <td class="px-4 py-3 font-semibold text-on-surface whitespace-nowrap">
                  <a href="<?php echo site_url('staff/profile/' . $wl->staff_id); ?>" class="hover:underline text-primary"><?php echo html_escape($wl->full_name); ?></a>
                </td>
                <td class="px-4 py-3 font-mono text-on-surface-variant whitespace-nowrap"><?php echo html_escape($wl->employee_code); ?></td>
                <td class="px-4 py-3 text-on-surface font-medium whitespace-nowrap"><?php echo html_escape($wl->subject_name); ?></td>
                <td class="px-4 py-3 text-on-surface whitespace-nowrap"><?php echo html_escape($formattedClassDiv ?: '—'); ?></td>
                <td class="px-4 py-3 font-mono font-bold text-primary whitespace-nowrap"><?php echo $wl->periods; ?> periods</td>
                <td class="px-4 py-3 text-on-surface-variant whitespace-nowrap text-[13px]"><?php echo html_escape($wl->working_days); ?></td>
                <td class="px-4 py-3 text-on-surface-variant text-[13px]"><?php echo html_escape($wl->remarks ?: '—'); ?></td>
                <td class="px-4 py-3 text-right whitespace-nowrap space-x-1">
                  <button type="button" onclick='editWorkload(<?php echo json_encode($wl); ?>)' title="Edit Workload" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors inline-flex cursor-pointer"><span class="material-symbols-outlined text-[18px]">edit</span></button>
                  <a href="<?php echo site_url('staff/delete_workload/' . $wl->workload_id); ?>" onclick="return confirm('Remove this workload allocation?')" title="Remove Workload" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-error-container/20 hover:text-error transition-colors inline-flex"><span class="material-symbols-outlined text-[18px]">delete</span></a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="flex items-center justify-between px-4 py-3 border-t border-outline-variant/50 text-body-md font-body-md text-on-surface-variant">
        <span>Total <?php echo count($workloads); ?> workload assignment(s)</span>
      </div>
    </div>

    <!-- Modal: Assign / Edit Workload -->
    <div id="modal-add-workload" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden">
      <div class="elevation-3 rounded-2xl bg-surface-container-lowest border border-outline-variant w-full max-w-lg">
        <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant">
          <h3 id="modal-workload-title" class="font-headline-md text-headline-md text-on-surface">Assign Teacher Workload</h3>
          <button onclick="closeWorkloadModal()" class="p-1 rounded-lg text-on-surface-variant hover:bg-surface-container-high"><span class="material-symbols-outlined">close</span></button>
        </div>
        <?php echo form_open('staff/workload', array('id' => 'form-workload', 'class' => 'p-6 space-y-4')); ?>
          <input type="hidden" name="workload_id" id="workload_id" value=""/>
          
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-label-md mb-1 font-semibold text-on-surface">Academic Year *</label>
              <select name="academic_year_id" id="workload_academic_year_id" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest">
                <?php foreach ($years as $yr): ?>
                  <option value="<?php echo $yr->academic_year_id; ?>" <?php echo (!empty($yr->is_active)) ? 'selected' : ''; ?>>
                    <?php echo html_escape($yr->year_name); ?><?php echo (!empty($yr->is_active)) ? ' (Active)' : ''; ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block text-label-md mb-1 font-semibold text-on-surface">Department / Group *</label>
              <select name="academic_group_id" id="workload_academic_group_id" required onchange="onWorkloadGroupChange(this.value)" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest">
                <option value="">-- Select Department / Group --</option>
                <?php if (!empty($groups)): ?>
                  <?php foreach ($groups as $g): ?>
                    <option value="<?php echo $g->academic_group_id; ?>"><?php echo html_escape($g->group_name); ?></option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-label-md mb-1 font-semibold text-on-surface">Teacher / Faculty *</label>
              <select name="staff_id" id="workload_staff_id" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest disabled:opacity-50 disabled:cursor-not-allowed">
                <option value="">-- Select Department / Group first --</option>
              </select>
            </div>
            <div>
              <label class="block text-label-md mb-1 font-semibold text-on-surface">Class *</label>
              <select name="class_id" id="workload_class_id" required onchange="filterWorkloadDivisions()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest disabled:opacity-50 disabled:cursor-not-allowed">
                <option value="">-- Select Department / Group first --</option>
                <?php foreach ($classes as $cls): ?>
                  <option value="<?php echo $cls->class_id; ?>" data-group-id="<?php echo $cls->academic_group_id ?: ''; ?>">
                    <?php echo html_escape($cls->class_name); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
            <div>
              <label class="block text-label-md mb-1">Division</label>
              <select name="division_id" id="workload_division_id" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest">
                <option value="">-- Select Division (Optional) --</option>
                <?php foreach ($divisions as $div): ?>
                  <option value="<?php echo $div->division_id; ?>" data-class-id="<?php echo $div->class_id; ?>">
                    <?php echo html_escape((!empty($div->division_name) && strpos($div->division_name, $div->class_name) !== false) ? $div->division_name : trim($div->class_name . ' ' . $div->division_name)); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-label-md mb-1">Subject *</label>
            <select name="subject_id" id="workload_subject_id" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest">
              <option value="">-- Select Subject --</option>
              <?php foreach ($subjects as $sub): ?>
                <option value="<?php echo $sub->subject_id; ?>"><?php echo html_escape($sub->subject_name); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-label-md mb-1">Periods / Week *</label>
              <input type="number" name="periods" id="workload_periods" min="1" max="30" value="5" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest"/>
            </div>
            <div>
              <label class="block text-label-md mb-1">Working Days</label>
              <input type="text" name="working_days" id="workload_working_days" value="Mon,Tue,Wed,Thu,Fri" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-[13px]"/>
            </div>
          </div>

          <div>
            <label class="block text-label-md mb-1">Remarks</label>
            <input type="text" name="remarks" id="workload_remarks" placeholder="Optional notes (e.g. Core theory)" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest"/>
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t border-outline-variant">
            <button type="button" onclick="closeWorkloadModal()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant cursor-pointer">Cancel</button>
            <button type="submit" id="btn-submit-workload" class="px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant cursor-pointer">Save Assignment</button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <script>
      function applyFilter(key, val) {
        var url = new URL(window.location.href);
        if (val) { url.searchParams.set(key, val); } else { url.searchParams.delete(key); }
        // If class changed, reset division filter
        if (key === 'class_id') {
          url.searchParams.delete('division_id');
          url.searchParams.delete('section_id');
        }
        window.location.href = url.toString();
      }

      function onWorkloadGroupChange(groupId, targetTeacherId, targetClassId) {
        var teacherSelect = document.getElementById('workload_staff_id');
        var classSelect   = document.getElementById('workload_class_id');

        if (!groupId) {
          teacherSelect.innerHTML = '<option value="">-- Select Department / Group first --</option>';
          teacherSelect.value = '';
          teacherSelect.disabled = true;

          classSelect.value = '';
          classSelect.disabled = true;
          var classOptions = classSelect.querySelectorAll('option');
          classOptions.forEach(function(opt) {
            if (!opt.value) {
              opt.textContent = '-- Select Department / Group first --';
              opt.style.display = '';
            } else {
              opt.style.display = 'none';
            }
          });
          filterWorkloadDivisions();
          return;
        }

        // Immediately filter Classes belonging to this Department / Group
        classSelect.disabled = false;
        var classOptions = classSelect.querySelectorAll('option');
        classOptions.forEach(function(opt) {
          if (!opt.value) {
            opt.textContent = '-- Choose Class --';
            opt.style.display = '';
            return;
          }
          var optGroup = opt.getAttribute('data-group-id');
          if (String(optGroup) === String(groupId)) {
            opt.style.display = '';
          } else {
            opt.style.display = 'none';
            if (opt.selected && opt.value !== targetClassId) {
              classSelect.value = '';
            }
          }
        });
        if (targetClassId) {
          classSelect.value = targetClassId;
        }

        // Immediately request and load matching Teaching Staff (DO NOT WAIT FOR CLASS)
        teacherSelect.disabled = true;
        teacherSelect.innerHTML = '<option value="">Loading faculty...</option>';

        fetch('<?php echo site_url("staff/ajax_get_teachers_by_group"); ?>?academic_group_id=' + encodeURIComponent(groupId))
          .then(function(r) { return r.json(); })
          .then(function(data) {
            var teachers = (data && data.teachers) ? data.teachers : [];
            var html = '<option value="">-- Choose Teacher --</option>';
            if (teachers.length > 0) {
              teachers.forEach(function(t) {
                var isSel = (targetTeacherId && String(targetTeacherId) === String(t.staff_id)) ? ' selected' : '';
                html += '<option value="' + t.staff_id + '"' + isSel + '>' +
                  t.full_name + ' (' + (t.employee_code || '') + ')' +
                  '</option>';
              });
            } else {
              html = '<option value="">No teaching faculty in this Department / Group</option>';
            }
            teacherSelect.innerHTML = html;
            teacherSelect.disabled = false;
            if (targetTeacherId) {
              teacherSelect.value = targetTeacherId;
            }
          })
          .catch(function(err) {
            console.error('Error fetching teachers:', err);
            teacherSelect.disabled = false;
          });

        filterWorkloadDivisions();
      }

      function filterWorkloadDivisions(targetDivId) {
        var classSelect = document.getElementById('workload_class_id');
        var divSelect = document.getElementById('workload_division_id');
        if (!classSelect || !divSelect) return;

        var selectedClass = classSelect.value;
        var options = divSelect.querySelectorAll('option');

        options.forEach(function(opt) {
          if (!opt.value) {
            opt.style.display = '';
            return;
          }
          var optClass = opt.getAttribute('data-class-id');
          if (!selectedClass || optClass === selectedClass) {
            opt.style.display = '';
          } else {
            opt.style.display = 'none';
            if (opt.selected && opt.value !== targetDivId) {
              divSelect.value = '';
            }
          }
        });

        if (targetDivId !== undefined) {
          divSelect.value = targetDivId;
        }
      }

      function openAddWorkloadModal() {
        document.getElementById('modal-workload-title').innerText = 'Assign Teacher Workload';
        document.getElementById('btn-submit-workload').innerText = 'Save Assignment';
        document.getElementById('workload_id').value = '';
        document.getElementById('workload_academic_group_id').value = '';
        document.getElementById('workload_division_id').value = '';
        document.getElementById('workload_subject_id').value = '';
        document.getElementById('workload_periods').value = '5';
        document.getElementById('workload_working_days').value = 'Mon,Tue,Wed,Thu,Fri';
        document.getElementById('workload_remarks').value = '';
        onWorkloadGroupChange('');
        document.getElementById('modal-add-workload').classList.remove('hidden');
      }

      function editWorkload(wl) {
        document.getElementById('modal-workload-title').innerText = 'Edit Teacher Workload';
        document.getElementById('btn-submit-workload').innerText = 'Update Assignment';
        document.getElementById('workload_id').value = wl.workload_id || '';
        if (wl.academic_year_id) {
          document.getElementById('workload_academic_year_id').value = wl.academic_year_id;
        }

        var teacherGroupId = '';
        if (wl.academic_group_id) {
          teacherGroupId = wl.academic_group_id;
        } else {
          var classOpt = document.querySelector('#workload_class_id option[value="' + wl.class_id + '"]');
          if (classOpt) {
            teacherGroupId = classOpt.getAttribute('data-group-id') || '';
          }
        }

        document.getElementById('workload_academic_group_id').value = teacherGroupId;
        onWorkloadGroupChange(teacherGroupId, wl.staff_id, wl.class_id);
        filterWorkloadDivisions(wl.division_id || '');

        document.getElementById('workload_subject_id').value = wl.subject_id || '';
        document.getElementById('workload_periods').value = wl.periods || '5';
        document.getElementById('workload_working_days').value = wl.working_days || 'Mon,Tue,Wed,Thu,Fri';
        document.getElementById('workload_remarks').value = wl.remarks || '';
        document.getElementById('modal-add-workload').classList.remove('hidden');
      }

      function closeWorkloadModal() {
        document.getElementById('modal-add-workload').classList.add('hidden');
      }
    </script>
