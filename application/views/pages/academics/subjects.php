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

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Subjects</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1"><span id="total-subjects-count"><?php echo (int)($total_subjects ?? 0); ?></span> academic subjects and course curriculum modules.</p>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <button onclick="openAddSubjectModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Add Subject
        </button>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="flex flex-col md:flex-row gap-3 mb-4 flex-wrap">
      <select id="filter_class_id" class="px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md text-on-surface-variant">
        <option value="">All Classes</option>
        <?php foreach ($classes as $cls): ?>
          <option value="<?php echo $cls->class_id; ?>" <?php echo ($this->input->get('class_id') == $cls->class_id) ? 'selected' : ''; ?>><?php echo html_escape($cls->class_name); ?></option>
        <?php endforeach; ?>
      </select>
      <button type="button" id="filter_reset_btn" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors cursor-pointer"><span class="material-symbols-outlined text-[18px]">restart_alt</span>Reset</button>
    </div>

    <!-- Subjects Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
      <div class="table-scroll overflow-x-auto p-2">
        <table id="subjects-table" class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60">
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Subject Name</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Code</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Applicable Class</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Subject Type</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Teachers</th>
              <th class="text-right px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/30 text-body-md">
            <!-- Loaded dynamically via server-side DataTables -->
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal: Add / Edit Subject -->
    <div id="modal-subject" class="fixed inset-0 z-50 flex items-start justify-center bg-black/50 p-4 overflow-y-auto hidden">
      <div class="elevation-3 rounded-2xl bg-surface-container-lowest border border-outline-variant w-full max-w-2xl my-6">
        <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant">
          <h3 class="font-headline-md text-headline-md text-on-surface" id="modal-subject-title">Add Subject</h3>
          <button onclick="closeSubjectModal()" class="p-1 rounded-lg text-on-surface-variant hover:bg-surface-container-high cursor-pointer"><span class="material-symbols-outlined">close</span></button>
        </div>
        <?php echo form_open('academics/subjects', array('id' => 'subject-form', 'class' => 'p-6 space-y-5', 'onsubmit' => 'return prepareSubjectFormSubmit()')); ?>
          <input type="hidden" name="action" id="subject_action" value="add"/>
          <input type="hidden" name="subject_id" id="modal_subject_id"/>
          <input type="hidden" name="teacher_assignments" id="modal_teacher_assignments_json" value="[]"/>

          <!-- Subject Details -->
          <div>
            <label class="block text-label-md mb-1">Subject Name *</label>
            <input type="text" name="subject_name" id="modal_subject_name" required placeholder="e.g. Mathematics, Physics" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest"/>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-label-md mb-1">Subject Code</label>
              <input type="text" name="subject_code" id="modal_subject_code" placeholder="e.g. MATH10" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest font-mono uppercase"/>
            </div>
            <div>
              <label class="block text-label-md mb-1">Subject Type *</label>
              <select name="subject_type" id="modal_subject_type" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest">
                <option value="Core">Core</option>
                <option value="Elective">Elective</option>
                <option value="Language">Language</option>
                <option value="Practical">Practical</option>
                <option value="Other">Other</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-label-md mb-1">Applicable Class (Optional)</label>
            <select name="class_id" id="modal_subject_class" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest">
              <option value="">General / All Classes</option>
              <?php foreach ($classes as $cls): ?>
                <option value="<?php echo $cls->class_id; ?>"><?php echo html_escape($cls->class_name); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="block text-label-md mb-1">Description / Notes</label>
            <textarea name="description" id="modal_subject_description" rows="2" placeholder="Optional curriculum notes or syllabus details..." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest"></textarea>
          </div>

          <!-- Subject Teacher Allocation -->
          <div class="border border-outline-variant/60 rounded-xl overflow-hidden">
            <div class="px-4 py-3 bg-surface-container-low border-b border-outline-variant/40 flex items-center justify-between">
              <div>
                <div class="text-label-md font-semibold text-on-surface flex items-center gap-1.5">
                  <span class="material-symbols-outlined text-secondary text-[18px]">school</span>
                  Subject Teacher Allocation
                </div>
                <div class="text-[11px] text-on-surface-variant mt-0.5">Assign teachers to this subject by class and division.</div>
              </div>
              <div id="st-loading-indicator" class="hidden">
                <span class="material-symbols-outlined text-secondary text-[18px] animate-spin">progress_activity</span>
              </div>
            </div>

            <!-- Assignment rows container -->
            <div id="st-assignments-container" class="divide-y divide-outline-variant/30 max-h-64 overflow-y-auto">
              <!-- Rows injected by JS -->
            </div>
            <div id="st-empty-state" class="px-4 py-5 text-center text-[13px] text-on-surface-variant hidden">
              No teacher assignments yet. Click "+ Add Assignment" to get started.
            </div>

            <div class="px-4 py-3 bg-surface-container-low border-t border-outline-variant/40">
              <button type="button" onclick="addAssignmentRow()" class="inline-flex items-center gap-1.5 text-secondary text-label-md font-semibold hover:underline cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">add_circle</span>Add Assignment
              </button>
              <div id="st-duplicate-warning" class="hidden mt-2 text-[12px] text-error flex items-center gap-1">
                <span class="material-symbols-outlined text-[15px]">warning</span>
                <span id="st-duplicate-warning-text"></span>
              </div>
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-2 border-t border-outline-variant">
            <button type="button" onclick="closeSubjectModal()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant cursor-pointer">Cancel</button>
            <button type="submit" onclick="return prepareSubjectFormSubmit()" class="px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant cursor-pointer">Save Subject</button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <script>
      var ACTIVE_YEAR_ID  = <?php echo (int)($active_year_id ?? 0); ?>;
      var AJAX_BASE       = '<?php echo site_url('academics/'); ?>';
      var CSRF_TOKEN_NAME = '<?php echo $this->security->get_csrf_token_name(); ?>';
      var CSRF_HASH       = '<?php echo $this->security->get_csrf_hash(); ?>';

      var ALL_TEACHERS = <?php
        $t_json = array();
        foreach ($teachers as $t) {
          $t_json[] = array(
            'staff_id'      => (int)$t->staff_id,
            'full_name'     => $t->full_name,
            'employee_code' => $t->employee_code ?? ''
          );
        }
        echo json_encode($t_json);
      ?>;

      // --- Modal open/close ---
      function openAddSubjectModal() {
        document.getElementById('subject_action').value = 'add';
        document.getElementById('modal-subject-title').textContent = 'Add Subject';
        document.getElementById('modal_subject_id').value = '';
        document.getElementById('modal_subject_name').value = '';
        document.getElementById('modal_subject_code').value = '';
        document.getElementById('modal_subject_type').value = 'Core';
        document.getElementById('modal_subject_class').value = '';
        document.getElementById('modal_subject_description').value = '';
        document.getElementById('modal_teacher_assignments_json').value = '[]';
        clearAssignmentRows();
        updateEmptyState();
        document.getElementById('modal-subject').classList.remove('hidden');
      }

      function openEditSubjectModal(id, name, code, type, classId, desc) {
        document.getElementById('subject_action').value = 'edit';
        document.getElementById('modal-subject-title').textContent = 'Edit Subject';
        document.getElementById('modal_subject_id').value = id;
        document.getElementById('modal_subject_name').value = name;
        document.getElementById('modal_subject_code').value = code;
        document.getElementById('modal_subject_type').value = type;
        document.getElementById('modal_subject_class').value = classId ? classId : '';
        document.getElementById('modal_subject_description').value = desc || '';
        document.getElementById('modal_teacher_assignments_json').value = '[]';
        clearAssignmentRows();
        document.getElementById('modal-subject').classList.remove('hidden');

        // Load existing assignments
        if (id && ACTIVE_YEAR_ID) {
          document.getElementById('st-loading-indicator').classList.remove('hidden');
          fetch(AJAX_BASE + 'ajax_get_subject_assignments?subject_id=' + id + '&academic_year_id=' + ACTIVE_YEAR_ID)
            .then(function(r) { return r.json(); })
            .then(function(rows) {
              document.getElementById('st-loading-indicator').classList.add('hidden');
              rows.forEach(function(row) {
                addAssignmentRow(row);
              });
              updateEmptyState();
            })
            .catch(function() {
              document.getElementById('st-loading-indicator').classList.add('hidden');
              updateEmptyState();
            });
        } else {
          updateEmptyState();
        }
      }

      function closeSubjectModal() {
        document.getElementById('modal-subject').classList.add('hidden');
        clearAssignmentRows();
      }

      // --- Assignment Row Management ---
      var rowIndex = 0;

      function clearAssignmentRows() {
        document.getElementById('st-assignments-container').innerHTML = '';
        rowIndex = 0;
        hideDuplicateWarning();
      }

      function updateEmptyState() {
        var container = document.getElementById('st-assignments-container');
        var empty = document.getElementById('st-empty-state');
        if (container.children.length === 0) {
          empty.classList.remove('hidden');
        } else {
          empty.classList.add('hidden');
        }
      }

      function buildTeacherOptions(selectedStaffId) {
        var opts = '<option value="">Select Teacher</option>';
        ALL_TEACHERS.forEach(function(t) {
          var selected = selectedStaffId && (String(t.staff_id) === String(selectedStaffId)) ? ' selected' : '';
          opts += '<option value="' + t.staff_id + '"' + selected + '>' +
            escHtml(t.full_name) + (t.employee_code ? ' [' + escHtml(t.employee_code) + ']' : '') + '</option>';
        });
        return opts;
      }

      function addAssignmentRow(existingData) {
        var idx = rowIndex++;
        var container = document.getElementById('st-assignments-container');

        var classId   = existingData ? existingData.class_id : (document.getElementById('modal_subject_class') ? document.getElementById('modal_subject_class').value : '');
        var divId     = existingData ? existingData.division_id : '';
        var staffId   = existingData ? existingData.staff_id : '';
        var stId      = existingData ? existingData.subject_teacher_id : '';
        var className = existingData ? existingData.class_name : '';
        var divName   = existingData ? existingData.division_name : '';

        var row = document.createElement('div');
        row.className = 'flex items-center gap-2 px-4 py-3';
        row.setAttribute('data-row-idx', idx);
        row.setAttribute('data-st-id', stId || '');

        // Class select
        var classOpts = '<?php echo addslashes('<option value="">Select Class</option>'); ?>';
        <?php foreach ($classes as $cls): ?>
        classOpts += '<option value="<?php echo $cls->class_id; ?>">' + escHtml('<?php echo html_escape(addslashes($cls->class_name)); ?>') + '</option>';
        <?php endforeach; ?>

        row.innerHTML =
          '<select class="st-class-sel flex-1 min-w-0 px-2 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface"' +
          ' onchange="onRowClassChange(this,' + idx + ')" data-row="' + idx + '">' + classOpts + '</select>' +
          '<select class="st-div-sel flex-1 min-w-0 px-2 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface"' +
          ' id="st-div-' + idx + '" data-row="' + idx + '">' +
          '<option value="">Select Division</option>' +
          '</select>' +
          '<select class="st-staff-sel flex-1 min-w-0 px-2 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface"' +
          ' id="st-staff-' + idx + '" data-row="' + idx + '">' + buildTeacherOptions(staffId) + '</select>' +
          '<button type="button" onclick="removeAssignmentRow(this,' + idx + ')"' +
          ' class="p-1.5 rounded-lg text-on-surface-variant hover:bg-error-container/20 hover:text-error transition-colors shrink-0 cursor-pointer" title="Remove">' +
          '<span class="material-symbols-outlined text-[18px]">delete</span></button>';

        container.appendChild(row);
        updateEmptyState();

        // Set class value and load divisions
        var classSelect = row.querySelector('.st-class-sel');
        if (classId) {
          classSelect.value = classId;
          loadDivisionsForRow(idx, classId, divId);
        }
      }

      function onRowClassChange(sel, idx) {
        var classId = sel.value;
        var divSel = document.getElementById('st-div-' + idx);
        divSel.innerHTML = '<option value="">Loading...</option>';
        if (!classId) {
          divSel.innerHTML = '<option value="">Select Division</option>';
          return;
        }
        loadDivisionsForRow(idx, classId, '');
      }

      function loadDivisionsForRow(idx, classId, selectedDivId) {
        var divSel = document.getElementById('st-div-' + idx);
        fetch(AJAX_BASE + 'ajax_get_divisions/' + classId)
          .then(function(r) { return r.json(); })
          .then(function(data) {
            var opts = '<option value="">Select Division</option>';
            data.forEach(function(div) {
              var dId = div.division_id || div.section_id;
              var dName = div.division_name || div.section_name;
              var sel2 = (selectedDivId && String(dId) === String(selectedDivId)) ? ' selected' : '';
              opts += '<option value="' + dId + '"' + sel2 + '>Division ' + escHtml(dName) + '</option>';
            });
            divSel.innerHTML = opts;
          })
          .catch(function() {
            divSel.innerHTML = '<option value="">No divisions found</option>';
          });
      }

      function removeAssignmentRow(btn, idx) {
        var row = btn.closest('[data-row-idx="' + idx + '"]');
        var stId = row ? row.getAttribute('data-st-id') : '';
        if (stId) {
          // AJAX delete the persisted assignment
          var fd = new FormData();
          fd.append(CSRF_TOKEN_NAME, CSRF_HASH);
          fd.append('subject_teacher_id', stId);
          fetch(AJAX_BASE + 'ajax_delete_subject_teacher', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(res) {
              if (res && res.csrf_hash) {
                CSRF_HASH = res.csrf_hash;
                if (window.CSRF_HASH) window.CSRF_HASH = res.csrf_hash;
              }
              if (!res.success) { alert('Could not remove assignment: ' + (res.error || 'Unknown error')); }
            })
            .catch(function() {});
        }
        if (row) { row.remove(); }
        updateEmptyState();
        hideDuplicateWarning();
      }

      // Collect assignment rows into JSON for hidden field
      function collectAssignments() {
        var container = document.getElementById('st-assignments-container');
        var rows = container.querySelectorAll('[data-row-idx]');
        var result = [];
        var seen = {};
        var hasDuplicate = false;
        var dupKey = '';

        rows.forEach(function(row) {
          var classId = row.querySelector('.st-class-sel').value;
          var divId   = row.querySelector('[id^="st-div-"]').value;
          var staffId = row.querySelector('[id^="st-staff-"]').value;
          if (!classId || !divId || !staffId) return; // Skip incomplete rows
          var key = classId + '_' + divId;
          if (seen[key]) {
            hasDuplicate = true;
            dupKey = key;
          }
          seen[key] = true;
          result.push({ class_id: classId, division_id: divId, staff_id: staffId });
        });

        if (hasDuplicate) {
          showDuplicateWarning('The same Class + Division is assigned more than once. Please fix before saving.');
          return null;
        }

        hideDuplicateWarning();
        return result;
      }

      function prepareSubjectFormSubmit() {
        var assignments = collectAssignments();
        if (assignments === null) return false; // Duplicate found
        document.getElementById('modal_teacher_assignments_json').value = JSON.stringify(assignments);
        // Refresh CSRF in case of long session
        var csrfInput = document.querySelector('#subject-form input[name="' + CSRF_TOKEN_NAME + '"]');
        if (csrfInput) csrfInput.value = CSRF_HASH;
        return true;
      }

      function showDuplicateWarning(msg) {
        var el = document.getElementById('st-duplicate-warning');
        document.getElementById('st-duplicate-warning-text').textContent = msg;
        el.classList.remove('hidden');
      }

      function hideDuplicateWarning() {
        document.getElementById('st-duplicate-warning').classList.add('hidden');
      }

      function escHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
      }

      // Initialize Server-Side DataTable
      document.addEventListener('DOMContentLoaded', function() {
        if (typeof School !== 'undefined' && School.DataTable) {
          var subjectsDt = School.DataTable.serverSide('#subjects-table', {
            ajaxUrl: '<?php echo site_url("academics/ajax_subjects_list"); ?>',
            ajax: {
              url: '<?php echo site_url("academics/ajax_subjects_list"); ?>',
              type: 'POST'
            },
            order: [[0, 'asc']],
            columns: [
              { orderable: true },
              { orderable: true },
              { orderable: true },
              { orderable: true },
              { orderable: false },
              { orderable: false, className: 'text-right' }
            ],
            drawCallback: function(settings) {
              if (settings && settings.json && typeof settings.json.recordsTotal !== 'undefined') {
                var countEl = document.getElementById('total-subjects-count');
                if (countEl) {
                  countEl.textContent = settings.json.recordsTotal;
                }
              }
            },
            extraData: function(d) {
              d.class_id = $('#filter_class_id').val();
            }
          });

          $('#filter_class_id').on('change', function() {
            if (subjectsDt) {
              subjectsDt.ajax.reload();
            }
          });

          $('#filter_reset_btn').on('click', function() {
            $('#filter_class_id').val('');
            if (subjectsDt) {
              subjectsDt.search('').ajax.reload();
            }
          });
        }
      });
    </script>
