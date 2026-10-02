<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-6">

  <!-- =========================================================================
       PAGE HEADER
       ========================================================================= -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
    <div class="flex items-center gap-3">
      <span class="w-10 h-10 rounded-xl bg-primary text-white flex items-center justify-center shadow-xs">
        <span class="material-symbols-outlined text-[22px]">upgrade</span>
      </span>
      <div>
        <h2 class="font-bold text-xl text-slate-900 leading-tight">Student Promotion Engine</h2>
        <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-0.5">
          <span>Student Management</span>
          <span class="material-symbols-outlined text-[12px]">chevron_right</span>
          <span>Student Services</span>
          <span class="material-symbols-outlined text-[12px]">chevron_right</span>
          <span class="text-slate-800 font-medium">Student Promotion</span>
        </div>
      </div>
    </div>
    <div class="flex items-center gap-2">
      <a href="<?php echo site_url('students/all'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 transition-colors shadow-2xs">
        <span class="material-symbols-outlined text-[18px]">groups</span>
        <span>All Students Directory</span>
      </a>
    </div>
  </div>

  <!-- Flash Messages -->
  <?php if ($this->session->flashdata('success')): ?>
    <div class="p-4 rounded-xl bg-secondary-container border border-secondary/20 text-on-secondary-container text-xs font-bold flex items-center gap-2 shadow-2xs">
      <span class="material-symbols-outlined text-[20px] text-secondary">check_circle</span>
      <span><?php echo $this->session->flashdata('success'); ?></span>
    </div>
  <?php endif; ?>

  <?php if ($this->session->flashdata('error')): ?>
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center gap-2 shadow-2xs">
      <span class="material-symbols-outlined text-[20px] text-rose-700">error</span>
      <span><?php echo $this->session->flashdata('error'); ?></span>
    </div>
  <?php endif; ?>

  <!-- =========================================================================
       STEP 1: SOURCE CLASS & DIVISION SELECTION
       ========================================================================= -->
  <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-4">
    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
      <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
        <span class="w-6 h-6 rounded-lg bg-light-blue text-primary font-bold text-xs flex items-center justify-center">1</span>
        <span>Step 1: Select Source Class & Division</span>
      </h3>
      <span class="text-xs text-slate-500 font-medium">Filter current students to promote</span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
      
      <!-- Source Academic Year -->
      <div>
        <label for="src_year" class="block text-xs font-bold text-slate-700 mb-1.5">Source Academic Year</label>
        <select id="src_year" onchange="onSourceYearChanged(this.value)" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white text-slate-800 font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer">
          <?php foreach ($years as $yr): ?>
            <?php $isActive = (!empty($yr->is_active)); ?>
            <option value="<?php echo $yr->academic_year_id; ?>" <?php echo ($from_year == $yr->academic_year_id) ? 'selected' : ''; ?>>
              <?php echo html_escape($yr->year_name); ?><?php echo $isActive ? ' (Active)' : ''; ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Source Academic Group -->
      <div>
        <label for="src_group" class="block text-xs font-bold text-slate-700 mb-1.5">Source Department / Group</label>
        <select id="src_group" onchange="onSourceGroupChanged(this.value)" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white text-slate-800 font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer">
          <option value="">All Department / Groups</option>
          <?php if (!empty($groups)): foreach ($groups as $grp): ?>
            <option value="<?php echo $grp->academic_group_id; ?>"><?php echo html_escape($grp->group_name); ?></option>
          <?php endforeach; endif; ?>
        </select>
      </div>

      <!-- Source Class -->
      <div>
        <label for="src_class" class="block text-xs font-bold text-slate-700 mb-1.5">Source Class / Grade</label>
        <select id="src_class" onchange="onSourceClassChanged(this.value)" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white text-slate-800 font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer">
          <?php if (empty($classes)): ?>
            <option value="">No classes found for this year</option>
          <?php else: ?>
            <?php foreach ($classes as $cls): ?>
              <option value="<?php echo $cls->class_id; ?>" data-group="<?php echo (int)($cls->academic_group_id ?? 0); ?>" <?php echo ($from_class == $cls->class_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($cls->class_name); ?>
              </option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
      </div>

      <!-- Source Division (Class-Dependent) -->
      <div>
        <label for="src_division" class="block text-xs font-bold text-slate-700 mb-1.5">Source Division</label>
        <select id="src_division" onchange="onSourceDivisionChanged(this.value)" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white text-slate-800 font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer">
          <option value="">All Divisions</option>
          <?php foreach ($source_sections as $sec): ?>
            <option value="<?php echo ($sec->division_id ?? $sec->section_id); ?>" <?php echo ($from_sec !== NULL && $from_sec == ($sec->division_id ?? $sec->section_id)) ? 'selected' : ''; ?>>
              Division <?php echo html_escape(($sec->division_name ?? $sec->section_name)); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

    </div>
  </div>

  <!-- =========================================================================
       PROMOTION EXECUTION FORM (STEPS 2 & 3)
       ========================================================================= -->
  <?php echo form_open('students/promotion', array('id' => 'promotion-form')); ?>
    <input type="hidden" name="from_academic_year_id" value="<?php echo (int)$from_year; ?>"/>
    <input type="hidden" name="from_class_id" value="<?php echo (int)$from_class; ?>"/>
    <input type="hidden" name="from_division_id" value="<?php echo ($from_sec !== NULL) ? (int)$from_sec : ''; ?>"/>
    <input type="hidden" name="from_section_id" value="<?php echo ($from_sec !== NULL) ? (int)$from_sec : ''; ?>"/>

    <!-- =========================================================================
         STEP 2: TARGET CLASS, DIVISION & ACTION
         ========================================================================= -->
    <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-4 mb-6">
      <div class="flex items-center justify-between pb-2 border-b border-slate-100">
        <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
          <span class="w-6 h-6 rounded-lg bg-primary text-white flex items-center justify-center font-bold text-xs">2</span>
          <span>Step 2: Select Target Class & Action</span>
        </h3>
        <span class="text-xs text-slate-500 font-medium">Destination class and session for promoted students</span>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        
        <!-- Target Academic Year -->
        <div>
          <label for="to_academic_year_id" class="block text-xs font-bold text-slate-700 mb-1.5">Target Academic Year *</label>
          <select id="to_academic_year_id" name="to_academic_year_id" onchange="onTargetYearChanged(this.value)" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white text-slate-800 font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer">
            <?php foreach ($years as $yr): ?>
              <?php $isTargetDefault = ($from_year == $yr->academic_year_id); ?>
              <option value="<?php echo $yr->academic_year_id; ?>" <?php echo $isTargetDefault ? 'selected' : ''; ?>>
                <?php echo html_escape($yr->year_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Target Academic Group -->
        <div>
          <label for="to_academic_group_id" class="block text-xs font-bold text-slate-700 mb-1.5">Target Department / Group</label>
          <select id="to_academic_group_id" onchange="onTargetGroupChanged(this.value)" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white text-slate-800 font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer">
            <option value="">All Department / Groups</option>
            <?php if (!empty($groups)): foreach ($groups as $grp): ?>
              <option value="<?php echo $grp->academic_group_id; ?>"><?php echo html_escape($grp->group_name); ?></option>
            <?php endforeach; endif; ?>
          </select>
        </div>

        <!-- Target Class -->
        <div>
          <label for="to_class_id" class="block text-xs font-bold text-slate-700 mb-1.5">Target Class *</label>
          <select id="to_class_id" name="to_class_id" onchange="onTargetClassChanged(this.value)" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white text-slate-800 font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer">
            <?php if (empty($classes)): ?>
              <option value="">No target classes available</option>
            <?php else: ?>
              <?php foreach ($classes as $cls): ?>
                <option value="<?php echo $cls->class_id; ?>" data-group="<?php echo (int)($cls->academic_group_id ?? 0); ?>" <?php echo ($cls->class_id == $from_class) ? 'selected' : ''; ?>>
                  <?php echo html_escape($cls->class_name); ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>

        <!-- Target Division (Dynamically Loaded via AJAX based on Target Class) -->
        <div>
          <label for="to_division_id" class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
            <span>Target Division *</span>
            <span id="target-section-spinner" class="text-[10px] text-primary hidden flex items-center gap-1 font-normal">
              <span class="material-symbols-outlined text-[13px] animate-spin">progress_activity</span> Loading...
            </span>
          </label>
          <select id="to_division_id" name="to_division_id" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white text-slate-800 font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer">
            <option value="" disabled selected>Select Division</option>
            <!-- Populated dynamically via AJAX -->
          </select>
        </div>

        <!-- Promotion Action -->
        <div>
          <label for="promotion_type" class="block text-xs font-bold text-slate-700 mb-1.5">Promotion Action</label>
          <select id="promotion_type" name="promotion_type" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white text-slate-800 font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer">
            <option value="Promoted" selected>Promoted (Normal)</option>
            <option value="Retained">Retained in Same Class</option>
            <option value="Transferred">Transferred / Left</option>
          </select>
        </div>

      </div>

      <!-- Optional Remarks -->
      <div>
        <label for="promotion_remarks" class="block text-xs font-bold text-slate-700 mb-1">Remarks / Note (Optional)</label>
        <input type="text" id="promotion_remarks" name="remarks" placeholder="e.g., Annual exam promotion batch" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
      </div>
    </div>

    <!-- =========================================================================
         STEP 3: SELECT STUDENTS TO PROMOTE
         ========================================================================= -->
    <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-4 mb-6">
      
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-100">
        <div class="flex items-center gap-2">
          <span class="w-6 h-6 rounded-lg bg-primary text-white flex items-center justify-center font-bold text-xs">3</span>
          <h3 class="font-bold text-sm text-slate-900">
            Step 3: Select Students to Promote 
            <span class="font-normal text-slate-500">(<strong id="selected-students-count" class="text-primary"><?php echo count($students); ?></strong> of <?php echo count($students); ?> selected)</span>
          </h3>
        </div>
        
        <?php if (!empty($students)): ?>
          <div class="flex items-center gap-2 text-xs">
            <button type="button" onclick="toggleSelectAll(true)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition-colors">Select All</button>
            <button type="button" onclick="toggleSelectAll(false)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition-colors">Deselect All</button>
          </div>
        <?php endif; ?>
      </div>

      <!-- Students Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="border-b border-slate-200 bg-slate-50/80 text-slate-700 font-bold uppercase tracking-wider">
              <th class="w-10 p-3 pl-4 text-center">
                <input type="checkbox" id="chk-master" onclick="toggleSelectAll(this.checked)" checked class="rounded border-slate-300 text-secondary focus:ring-secondary cursor-pointer"/>
              </th>
              <th class="p-3">Admission No</th>
              <th class="p-3">Student Name</th>
              <th class="p-3">Current Class & Division</th>
              <th class="p-3">Roll No</th>
              <th class="p-3">Gender</th>
              <th class="p-3 pr-4">Parent / Guardian</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php if (empty($students)): ?>
              <tr>
                <td colspan="7" class="py-12 text-center text-slate-400 space-y-2">
                  <span class="material-symbols-outlined text-[42px] text-slate-300">group_off</span>
                  <div class="font-bold text-slate-700 text-sm">No active students found in selected source class & division.</div>
                  <p class="text-xs text-slate-500">Try choosing a different class, division, or academic session.</p>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($students as $st): ?>
                <?php
                  $fullName = trim($st->first_name . ' ' . ($st->middle_name ? $st->middle_name . ' ' : '') . $st->last_name);
                  $hasPhoto = !empty($st->photo) && file_exists(FCPATH . 'uploads/students/' . $st->photo);
                ?>
                <tr class="hover:bg-slate-50/60 transition-colors">
                  <td class="p-3 pl-4 text-center align-middle">
                    <input type="checkbox" name="student_ids[]" value="<?php echo $st->student_id; ?>" checked onchange="updateSelectedCount()" class="student-chk rounded border-slate-300 text-secondary focus:ring-secondary cursor-pointer"/>
                  </td>
                  <td class="p-3 font-mono font-bold text-primary align-middle"><?php echo html_escape($st->admission_number); ?></td>
                  <td class="p-3 align-middle font-bold text-slate-900">
                    <a href="<?php echo site_url('students/profile/' . $st->student_id); ?>" target="_blank" class="hover:text-primary transition-colors inline-flex items-center gap-1.5">
                      <span><?php echo html_escape($fullName); ?></span>
                      <span class="material-symbols-outlined text-[13px] text-slate-400">open_in_new</span>
                    </a>
                  </td>
                  <td class="p-3 align-middle font-medium text-slate-700"><?php echo html_escape($st->class_name . (!empty(($st->division_name ?? $st->section_name)) ? ' - ' . ($st->division_name ?? $st->section_name) : '')); ?></td>
                  <td class="p-3 align-middle font-mono text-slate-600"><?php echo html_escape($st->roll_number ?: '—'); ?></td>
                  <td class="p-3 align-middle text-slate-600"><?php echo html_escape($st->gender ?: '—'); ?></td>
                  <td class="p-3 pr-4 align-middle text-slate-600">
                    <div class="font-bold text-slate-800"><?php echo html_escape($st->guardian_name ?: '—'); ?></div>
                    <div class="text-[11px] font-mono text-slate-500"><?php echo html_escape($st->guardian_phone ?: ''); ?></div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Submit Footer -->
      <?php if (!empty($students)): ?>
        <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-4">
          <div class="text-xs text-slate-500">
            Clicking promote will transition all checked students to the target session & division, and log promotion history.
          </div>
          <button type="submit" id="btn-submit-promotion" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-secondary text-on-secondary text-xs font-bold hover:bg-on-secondary-fixed-variant transition-colors shadow-2xs cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">verified</span>
            <span>Execute Batch Promotion</span>
          </button>
        </div>
      <?php endif; ?>

    </div>
  <?php echo form_close(); ?>

  <!-- =========================================================================
       RECENT PROMOTION LOGS
       ========================================================================= -->
  <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-4">
    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
      <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-[20px]">history</span>
        <span>Recent Promotion Audit Logs</span>
      </h3>
      <span class="text-xs text-slate-500 font-medium">Last 8 promotion entries</span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-50/80 text-slate-700 font-bold uppercase tracking-wider">
            <th class="p-3 pl-4">Student</th>
            <th class="p-3">From (Year & Class)</th>
            <th class="p-3">To (Year & Class)</th>
            <th class="p-3">Action Type</th>
            <th class="p-3 pr-4">Date</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (empty($promotions_history)): ?>
            <tr>
              <td colspan="5" class="py-8 text-center text-slate-400 text-xs font-medium">No previous promotion logs recorded.</td>
            </tr>
          <?php else: ?>
            <?php foreach (array_slice($promotions_history, 0, 8) as $ph): ?>
              <?php $phName = trim($ph->first_name . ' ' . $ph->last_name); ?>
              <tr class="hover:bg-slate-50/60 transition-colors">
                <td class="p-3 pl-4 align-middle">
                  <div class="font-bold text-slate-900"><?php echo html_escape($phName); ?></div>
                  <div class="text-[11px] font-mono text-primary"><?php echo html_escape($ph->admission_number); ?></div>
                </td>
                <td class="p-3 align-middle text-slate-700">
                  <span class="font-bold"><?php echo html_escape($ph->from_class . (!empty(($ph->from_division ?? $ph->from_section)) ? ' - ' . ($ph->from_division ?? $ph->from_section) : '')); ?></span>
                  <span class="text-[11px] text-slate-500 block">[<?php echo html_escape($ph->from_year); ?>]</span>
                </td>
                <td class="p-3 align-middle text-slate-900">
                  <span class="font-bold text-on-surface"><?php echo html_escape($ph->to_class . (!empty(($ph->to_division ?? $ph->to_section)) ? ' - ' . ($ph->to_division ?? $ph->to_section) : '')); ?></span>
                  <span class="text-[11px] text-slate-500 block">[<?php echo html_escape($ph->to_year); ?>]</span>
                </td>
                <td class="p-3 align-middle">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-secondary-container text-on-secondary-container border border-secondary/20">
                    <?php echo html_escape($ph->promotion_type); ?>
                  </span>
                </td>
                <td class="p-3 pr-4 align-middle text-slate-600 font-mono">
                  <?php echo date('d M Y', strtotime($ph->promotion_date)); ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- Client JavaScript Logic -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Dynamically initialize Target Division dropdown for initially selected Target Class
    const initialTargetClassId = $('#to_class_id').val();
    if (initialTargetClassId) {
      loadTargetDivisions(initialTargetClassId);
    }
  });

  // Source Group Changed
  function onSourceGroupChanged(groupId) {
    if (!groupId) {
      $('#src_class option').show();
      return;
    }
    let firstVisibleClass = null;
    $('#src_class option').each(function () {
      const cGroup = $(this).attr('data-group');
      if (cGroup == groupId) {
        $(this).show();
        if (!firstVisibleClass) firstVisibleClass = $(this).val();
      } else {
        $(this).hide();
      }
    });
    if (firstVisibleClass) {
      onSourceClassChanged(firstVisibleClass);
    }
  }

  // Target Group Changed
  function onTargetGroupChanged(groupId) {
    if (!groupId) {
      $('#to_class_id option').show();
      return;
    }
    let firstVisibleClass = null;
    $('#to_class_id option').each(function () {
      const cGroup = $(this).attr('data-group');
      if (cGroup == groupId) {
        $(this).show();
        if (!firstVisibleClass) firstVisibleClass = $(this).val();
      } else {
        $(this).hide();
      }
    });
    if (firstVisibleClass) {
      $('#to_class_id').val(firstVisibleClass);
      onTargetClassChanged(firstVisibleClass);
    }
  }

  // Source Academic Year Changed
  function onSourceYearChanged(yearId) {
    window.location.href = '<?php echo site_url('students/promotion'); ?>?from_year=' + encodeURIComponent(yearId);
  }

  // Source Class Changed -> Reset section to "" so it never retains stale section!
  function onSourceClassChanged(classId) {
    const yr = $('#src_year').val();
    window.location.href = '<?php echo site_url('students/promotion'); ?>?from_year=' + encodeURIComponent(yr) + '&from_class=' + encodeURIComponent(classId) + '&from_division=';
  }

  // Source Division Changed
  function onSourceDivisionChanged(secId) {
    const yr  = $('#src_year').val();
    const cls = $('#src_class').val();
    window.location.href = '<?php echo site_url('students/promotion'); ?>?from_year=' + encodeURIComponent(yr) + '&from_class=' + encodeURIComponent(cls) + '&from_division=' + encodeURIComponent(secId);
  }

  // Target Academic Year Changed -> Fetch Target Classes
  function onTargetYearChanged(yearId) {
    if (!yearId) return;

    $('#to_class_id').html('<option value="">Loading classes...</option>').prop('disabled', true);
    $('#to_division_id, #to_section_id').html('<option value="">Select class first</option>').prop('disabled', true);

    $.ajax({
      url: '<?php echo site_url('students/get_classes_ajax'); ?>',
      type: 'POST',
      data: {
        academic_year_id: yearId,
        [window.CSRF_TOKEN_NAME]: window.CSRF_HASH
      },
      dataType: 'json',
      success: function (res) {
        if (res && res.csrf_hash) window.CSRF_HASH = res.csrf_hash;
        
        let classHtml = '';
        if (res && res.classes && res.classes.length > 0) {
          res.classes.forEach(function (c) {
            classHtml += '<option value="' + c.class_id + '" data-group="' + (c.academic_group_id || 0) + '">' + $('<div>').text(c.class_name).html() + '</option>';
          });
          $('#to_class_id').html(classHtml).prop('disabled', false);
          // Load divisions for first class
          loadTargetDivisions(res.classes[0].class_id);
        } else {
          $('#to_class_id').html('<option value="">No classes found for this year</option>').prop('disabled', true);
        }
      },
      error: function () {
        $('#to_class_id').html('<option value="">Failed to load classes</option>').prop('disabled', false);
      }
    });
  }

  // Target Class Changed -> Dynamically Fetch and Populate Divisions
  function onTargetClassChanged(classId) {
    loadTargetDivisions(classId);
  }

  function loadTargetDivisions(classId) {
    if (!classId) {
      $('#to_division_id, #to_section_id').html('<option value="12" selected>Division A</option>').prop('disabled', false);
      return;
    }

    $('#target-section-spinner').removeClass('hidden');

    $.ajax({
      url: '<?php echo site_url('students/get_divisions_ajax'); ?>',
      type: 'POST',
      data: {
        class_id: classId,
        [window.CSRF_TOKEN_NAME]: window.CSRF_HASH
      },
      dataType: 'json',
      success: function (res) {
        $('#target-section-spinner').addClass('hidden');
        if (res && res.csrf_hash) window.CSRF_HASH = res.csrf_hash;

        let secHtml = '';
        if (res && (res.divisions || res.sections) && (res.divisions || res.sections).length > 0) {
          if ((res.divisions || res.sections).length > 1) {
            secHtml += '<option value="" disabled selected>Select Division</option>';
          }
          (res.divisions || res.sections).forEach(function (sec, idx) {
            const isAutoSelected = ((res.divisions || res.sections).length === 1 || idx === 0) ? 'selected' : '';
            secHtml += '<option value="' + (sec.division_id || sec.section_id) + '" ' + isAutoSelected + '>Division ' + $('<div>').text((sec.division_name || sec.section_name)).html() + '</option>';
          });
          $('#to_division_id, #to_section_id').html(secHtml).prop('disabled', false);
        } else {
          // Fallback to default Division 'A'
          $('#to_division_id, #to_section_id').html('<option value="12" selected>Division A</option>').prop('disabled', false);
        }
      },
      error: function () {
        $('#target-section-spinner').addClass('hidden');
        $('#to_division_id, #to_section_id').html('<option value="12" selected>Division A</option>').prop('disabled', false);
      }
    });
  }

  // Toggle Select All / Deselect All
  function toggleSelectAll(checked) {
    document.querySelectorAll('.student-chk').forEach(function (chk) {
      chk.checked = checked;
    });
    const master = document.getElementById('chk-master');
    if (master) master.checked = checked;
    updateSelectedCount();
  }

  // Update live count of selected students
  function updateSelectedCount() {
    const total = document.querySelectorAll('.student-chk').length;
    const checked = document.querySelectorAll('.student-chk:checked').length;
    $('#selected-students-count').text(checked);

    const master = document.getElementById('chk-master');
    if (master) {
      master.checked = (total > 0 && checked === total);
      master.indeterminate = (checked > 0 && checked < total);
    }

    const btnSubmit = document.getElementById('btn-submit-promotion');
    if (btnSubmit) {
      btnSubmit.disabled = (checked === 0);
      if (checked === 0) {
        btnSubmit.classList.add('opacity-50', 'cursor-not-allowed');
      } else {
        btnSubmit.classList.remove('opacity-50', 'cursor-not-allowed');
      }
    }
  }
</script>
