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
        <h2 class="font-headline-md text-headline-md text-on-surface">Subject Weekly Quota Allocation</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Configure weekly period quotas for each subject and monitor scheduled vs remaining slots.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button type="button" onclick="openAllocModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Set Subject Quota
        </button>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('timetable/allocations'); ?>" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Academic Year</label>
          <select name="academic_year_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <?php foreach ($academic_years as $ay): ?>
              <option value="<?php echo $ay->academic_year_id; ?>" <?php echo ($selected_year == $ay->academic_year_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($ay->year_name); ?><?php echo (!empty($ay->is_active)) ? ' (Active)' : ''; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Filter Class</label>
          <select name="class_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Classes</option>
            <?php foreach ($classes as $c): ?>
              <option value="<?php echo $c->class_id; ?>" <?php echo ($selected_class == $c->class_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($c->class_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Filter Division</label>
          <select name="division_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Divisions</option>
            <?php foreach ($divisions as $d): ?>
              <option value="<?php echo $d->division_id; ?>" <?php echo (($selected_division ?? $selected_section) == $d->division_id) ? 'selected' : ''; ?>>
                <?php echo html_escape(($selected_class ? '' : ($d->class_name . ' - ')) . $d->division_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </form>
    </div>

    <!-- Allocations Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Configured Subject Quotas (<?php echo count($allocations); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Class & Division</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Subject</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Primary Faculty</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-primary uppercase whitespace-nowrap">Target Quota</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-secondary uppercase whitespace-nowrap">Scheduled</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase min-w-[180px]">Fulfillment</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($allocations)): ?>
              <tr><td colspan="7" class="px-4 py-8 text-center text-on-surface-variant">No subject allocations configured yet.</td></tr>
            <?php else: ?>
              <?php foreach ($allocations as $a): ?>
                <?php
                  $pct = ($a->weekly_periods_target > 0) ? min(100, round(($a->actual_allocated / $a->weekly_periods_target) * 100)) : 0;
                  $barColor = ($pct >= 100) ? 'bg-secondary' : (($pct >= 50) ? 'bg-primary' : 'bg-amber-500');
                ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 font-bold text-on-surface whitespace-nowrap">
                    <?php echo html_escape($a->class_name . ' ' . ($a->division_name ?: '')); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <strong class="text-primary"><?php echo html_escape($a->subject_name); ?></strong>
                    <span class="text-[11px] font-mono text-on-surface-variant block"><?php echo html_escape($a->subject_code); ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-on-surface">
                    <?php echo html_escape($a->teacher_name ?: '— Unassigned —'); ?>
                  </td>
                  <td class="px-4 py-3 text-center font-mono font-bold text-primary whitespace-nowrap">
                    <?php echo $a->weekly_periods_target; ?> / wk
                  </td>
                  <td class="px-4 py-3 text-center font-mono font-bold text-secondary whitespace-nowrap">
                    <?php echo $a->actual_allocated; ?> / wk
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                      <div class="w-full bg-surface-container-high rounded-full h-2 overflow-hidden">
                        <div class="<?php echo $barColor; ?> h-2 rounded-full" style="width: <?php echo $pct; ?>%"></div>
                      </div>
                      <span class="text-[11px] font-mono font-semibold text-on-surface-variant shrink-0"><?php echo $pct; ?>%</span>
                    </div>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <div class="flex items-center justify-center gap-1">
                      <button type="button" onclick='editAlloc(<?php echo json_encode($a); ?>)' class="p-1.5 rounded-lg hover:bg-surface-container-high text-on-surface-variant transition-colors cursor-pointer" title="Edit">
                        <span class="material-symbols-outlined text-[18px]">edit</span>
                      </button>
                      <?php echo form_open('timetable/allocations', array('class' => 'inline', 'onsubmit' => 'return confirm("Remove this subject quota allocation?");')); ?>
                        <input type="hidden" name="action" value="delete"/>
                        <input type="hidden" name="allocation_id" value="<?php echo $a->allocation_id; ?>"/>
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

    <!-- SET SUBJECT QUOTA MODAL -->
    <div id="alloc-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
      <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant max-w-md w-full p-6 elevation-3 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 id="modal-alloc-title" class="font-headline-md text-title-lg text-on-surface font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[22px]">tune</span>Set Subject Weekly Quota
          </h3>
          <button onclick="closeAllocModal()" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('timetable/allocations', array('class' => 'space-y-4')); ?>
          <input type="hidden" name="allocation_id" id="modal-alloc-id" value="0"/>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-semibold">Department / Group *</label>
            <select id="modal-alloc-group" onchange="onAllocGroupChange(this.value)" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
              <option value="">-- Select Department / Group --</option>
              <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $g): ?>
                  <option value="<?php echo $g->academic_group_id; ?>"><?php echo html_escape($g->group_name); ?></option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-semibold">Class *</label>
              <select name="class_id" id="modal-alloc-class" onchange="loadModalDivisions(this.value)" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary disabled:opacity-50 disabled:cursor-not-allowed">
                <option value="">-- Select Department / Group first --</option>
                <?php foreach ($classes as $c): ?>
                  <option value="<?php echo $c->class_id; ?>" data-group-id="<?php echo $c->academic_group_id ?: ''; ?>"><?php echo html_escape($c->class_name); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-semibold">Division *</label>
              <select name="division_id" id="modal-alloc-div" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="">-- Select Division --</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-semibold">Subject *</label>
            <select name="subject_id" id="modal-alloc-sub" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
              <option value="">-- Choose Subject --</option>
              <?php foreach ($subjects as $sub): ?>
                <option value="<?php echo $sub->subject_id; ?>"><?php echo html_escape($sub->subject_name . ' (' . $sub->subject_code . ')'); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-semibold">Assigned Faculty (Optional)</label>
            <select name="teacher_id" id="modal-alloc-teacher" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary disabled:opacity-50 disabled:cursor-not-allowed">
              <option value="">-- Select Department / Group first --</option>
            </select>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Weekly Target Periods *</label>
            <input type="number" min="1" max="20" name="weekly_periods_target" id="modal-alloc-target" value="6" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-outline-variant/50">
            <button type="button" onclick="closeAllocModal()" class="px-4 py-2 rounded-lg bg-surface-container-high text-on-surface text-label-md font-medium hover:bg-surface-container-highest cursor-pointer">
              Cancel
            </button>
            <button type="submit" class="px-5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant cursor-pointer">
              Save Quota
            </button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <script>
      function loadModalDivisions(classId, selectedDivId) {
        var divSelect = document.getElementById('modal-alloc-div');
        if (!classId) {
          divSelect.innerHTML = '<option value="">-- Select Division --</option>';
          return;
        }
        fetch('<?php echo site_url("timetable/ajax_get_divisions"); ?>?class_id=' + classId)
          .then(function(r) { return r.json(); })
          .then(function(data) {
            var html = '<option value="">-- Select Division --</option>';
            if (data && data.length > 0) {
              data.forEach(function(d) {
                var id = d.division_id || d.section_id;
                var name = d.division_name || d.section_name;
                var sel = (selectedDivId && selectedDivId == id) ? 'selected' : '';
                html += '<option value="' + id + '" ' + sel + '>' + name + '</option>';
              });
            } else {
              html = '<option value="">No divisions available</option>';
            }
            divSelect.innerHTML = html;
          })
          .catch(function(err) {
            console.error("Error loading divisions:", err);
          });
      }

      function onAllocGroupChange(groupId, targetTeacherId, targetClassId) {
        var teacherSelect = document.getElementById('modal-alloc-teacher');
        var classSelect   = document.getElementById('modal-alloc-class');
        var divSelect     = document.getElementById('modal-alloc-div');

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
          divSelect.innerHTML = '<option value="">-- Select Division --</option>';
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
          loadModalDivisions(targetClassId);
        } else {
          classSelect.value = '';
          divSelect.innerHTML = '<option value="">-- Select Division --</option>';
        }

        // Immediately request and load matching Teaching Staff (DO NOT WAIT FOR CLASS)
        teacherSelect.disabled = true;
        teacherSelect.innerHTML = '<option value="">Loading faculty...</option>';

        fetch('<?php echo site_url("staff/ajax_get_teachers_by_group"); ?>?academic_group_id=' + encodeURIComponent(groupId))
          .then(function(r) { return r.json(); })
          .then(function(data) {
            var teachers = (data && data.teachers) ? data.teachers : [];
            var html = '<option value="">-- Select Teacher --</option>';
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
      }

      function openAllocModal() {
        document.getElementById('modal-alloc-id').value = '0';
        document.getElementById('modal-alloc-title').textContent = 'Set Subject Weekly Quota';
        document.getElementById('modal-alloc-group').value = '';
        document.getElementById('modal-alloc-sub').value = '';
        document.getElementById('modal-alloc-target').value = '6';

        onAllocGroupChange('');

        var modal = document.getElementById('alloc-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
      }

      function editAlloc(a) {
        document.getElementById('modal-alloc-id').value = a.allocation_id;
        document.getElementById('modal-alloc-title').textContent = 'Edit Subject Weekly Quota';

        // Detect group from class option
        var targetGroupId = '';
        var classOpt = document.querySelector('#modal-alloc-class option[value="' + a.class_id + '"]');
        if (classOpt) {
          targetGroupId = classOpt.getAttribute('data-group-id') || '';
        }

        document.getElementById('modal-alloc-group').value = targetGroupId;
        onAllocGroupChange(targetGroupId, a.teacher_id, a.class_id);

        var divId = a.division_id || a.section_id;
        loadModalDivisions(a.class_id, divId);
        document.getElementById('modal-alloc-sub').value = a.subject_id;
        document.getElementById('modal-alloc-target').value = a.weekly_periods_target;

        var modal = document.getElementById('alloc-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
      }

      function closeAllocModal() {
        var modal = document.getElementById('alloc-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      }
    </script>
