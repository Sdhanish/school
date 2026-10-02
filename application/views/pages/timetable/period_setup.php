<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- Flash Messages -->
<?php if ($this->session->flashdata('success')): ?>
  <div class="mb-5 p-4 rounded-xl bg-secondary-container text-on-secondary-container text-body-md font-medium flex items-center gap-2.5 border border-secondary/20 shadow-sm">
    <span class="material-symbols-outlined text-[22px] text-secondary">check_circle</span>
    <span><?php echo html_escape($this->session->flashdata('success')); ?></span>
  </div>
<?php endif; ?>
<?php if ($this->session->flashdata('error')): ?>
  <div class="mb-5 p-4 rounded-xl bg-error-container text-on-error-container text-body-md font-medium flex items-center gap-2.5 border border-error/20 shadow-sm">
    <span class="material-symbols-outlined text-[22px] text-error">error</span>
    <span><?php echo html_escape($this->session->flashdata('error')); ?></span>
  </div>
<?php endif; ?>

<!-- Header Area -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
  <div>
    <div class="flex items-center gap-2.5">
      <span class="material-symbols-outlined text-secondary text-[28px]">schedule</span>
      <h2 class="font-headline-md text-headline-md text-on-surface font-bold">Period Setup</h2>
    </div>
    <p class="text-body-md font-body-md text-on-surface-variant mt-1">
      Configure group-level daily timetable periods, intervals, and lunch breaks. Settings automatically apply to all classes within the selected Department / Group.
    </p>
  </div>
  <div class="flex items-center gap-2 shrink-0">
    <a href="<?php echo site_url('timetable/builder'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors font-semibold">
      <span class="material-symbols-outlined text-[18px]">calendar_month</span>Timetable Matrix
    </a>
  </div>
</div>

<!-- Academic Group Selector & Scope Card -->
<div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/60 p-5 mb-6">
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div class="flex-1 max-w-md">
      <label class="block font-label-md text-label-md text-on-surface font-semibold mb-1.5 flex items-center gap-1.5">
        <span class="material-symbols-outlined text-secondary text-[18px]">hub</span>
        <span>Select Department / Group</span>
      </label>
      <div class="relative">
        <select id="group_select" onchange="onGroupChanged(this.value)" class="w-full px-3.5 py-2.5 rounded-xl border border-outline-variant bg-surface-container-low text-body-md font-semibold text-on-surface focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all cursor-pointer">
          <?php foreach ($groups as $g): ?>
            <option value="<?php echo $g->academic_group_id; ?>" <?php echo ((int)$selected_group_id === (int)$g->academic_group_id) ? 'selected' : ''; ?>>
              <?php echo html_escape($g->group_name); ?><?php echo !empty($g->description) ? ' — ' . html_escape($g->description) : ''; ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <!-- Active Group Badge & Target Classes Info -->
    <?php
      $current_group = null;
      foreach ($groups as $g) {
        if ((int)$g->academic_group_id === (int)$selected_group_id) {
          $current_group = $g;
          break;
        }
      }
      $group_classes_map = [
        "KG's" => "LKG, UKG",
        "LP"   => "Class 1, Class 2, Class 3, Class 4",
        "UP"   => "Class 5, Class 6, Class 7",
        "HS"   => "Class 8, Class 9, Class 10",
        "SS"   => "Class 11 (+1), Class 12 (+2)"
      ];
      $applies_to = $current_group ? ($group_classes_map[$current_group->group_name] ?? 'All mapped classes') : '';
    ?>
    <div class="flex items-center gap-3 p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/50">
      <div class="w-10 h-10 rounded-xl bg-secondary-container text-on-secondary-container flex items-center justify-center font-bold text-lg shrink-0">
        <?php echo html_escape(substr($current_group->group_name ?? 'G', 0, 2)); ?>
      </div>
      <div>
        <div class="flex items-center gap-2">
          <span class="font-headline-sm text-label-lg font-bold text-on-surface"><?php echo html_escape($current_group->group_name ?? 'Group'); ?></span>
          <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-secondary/10 text-secondary border border-secondary/20">Group Schedule</span>
        </div>
        <div class="text-[12px] text-on-surface-variant mt-0.5">
          Inherited by: <span class="font-semibold text-on-surface"><?php echo html_escape($applies_to); ?></span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Period Setup Form -->
<form id="periodSetupForm" method="post" action="<?php echo site_url('timetable/period_setup'); ?>">
  <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" id="csrf_token_field">
  <input type="hidden" name="academic_group_id" value="<?php echo (int)$selected_group_id; ?>" id="form_group_id">
  <input type="hidden" name="slots" id="slots_json_field">

  <!-- Schedule Header & Summary Strip -->
  <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/60 overflow-hidden mb-6">
    <div class="p-5 border-b border-outline-variant/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-surface-container-low/40">
      <div>
        <h3 class="font-headline-sm text-headline-sm font-bold text-on-surface flex items-center gap-2">
          <span class="material-symbols-outlined text-secondary text-[20px]">view_timeline</span>
          <span>Daily Time Schedule</span>
        </h3>
        <p class="text-body-md text-on-surface-variant text-[13px] mt-0.5">
          Define sequential teaching periods, short breaks, and lunch break for this group.
        </p>
      </div>

      <!-- Quick Stats Counters -->
      <div class="flex items-center gap-2.5 flex-wrap">
        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-surface-container-lowest border border-outline-variant/60 text-[12px] font-medium text-on-surface">
          <span class="w-2.5 h-2.5 rounded-full bg-primary"></span>
          <span id="stat_periods_count">0</span> Periods
        </div>
        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-surface-container-lowest border border-outline-variant/60 text-[12px] font-medium text-on-surface">
          <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
          <span id="stat_breaks_count">0</span> Breaks
        </div>
        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-surface-container-lowest border border-outline-variant/60 text-[12px] font-medium text-on-surface">
          <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
          <span id="stat_lunch_count">0</span> Lunch
        </div>
      </div>
    </div>

    <!-- Live Validation Alert Banner (Hidden by default) -->
    <div id="validation_alert" class="hidden m-4 p-3.5 rounded-xl bg-error-container text-on-error-container text-body-md font-semibold flex items-center gap-2.5 border border-error/30">
      <span class="material-symbols-outlined text-[20px] text-error shrink-0">error</span>
      <span id="validation_message">Please correct the highlighted time overlaps.</span>
    </div>

    <!-- Slots Table -->
    <div class="table-scroll overflow-x-auto">
      <table class="w-full text-left border-collapse" id="slots_table">
        <thead>
          <tr class="border-b border-outline-variant/60 bg-surface-container-low text-[12px] uppercase font-bold text-on-surface-variant tracking-wider">
            <th class="py-3 px-4 w-16 text-center">#</th>
            <th class="py-3 px-4 w-36">Type</th>
            <th class="py-3 px-4 min-w-[180px]">Slot Name</th>
            <th class="py-3 px-4 w-40">Start Time</th>
            <th class="py-3 px-4 w-40">End Time</th>
            <th class="py-3 px-4 w-32 text-center">Duration</th>
            <th class="py-3 px-4 w-28 text-center">Reorder</th>
            <th class="py-3 px-4 w-20 text-center">Remove</th>
          </tr>
        </thead>
        <tbody id="slots_tbody" class="divide-y divide-outline-variant/30 text-body-md font-medium">
          <!-- Populated by JavaScript -->
        </tbody>
      </table>
    </div>

    <!-- Table Action Toolbar -->
    <div class="p-4 border-t border-outline-variant/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-surface-container-low/20">
      <div class="flex items-center gap-2 flex-wrap">
        <button type="button" onclick="addSlot('Period')" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-surface-container-lowest border border-primary/40 text-primary hover:bg-primary/5 text-label-md font-bold transition-colors cursor-pointer shadow-sm">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>+ Add Period
        </button>
        <button type="button" onclick="addSlot('Break')" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-surface-container-lowest border border-amber-500/40 text-amber-800 hover:bg-amber-50 text-label-md font-bold transition-colors cursor-pointer shadow-sm">
          <span class="material-symbols-outlined text-[18px]">coffee</span>+ Add Break
        </button>
        <button type="button" onclick="addSlot('Lunch Break')" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-surface-container-lowest border border-emerald-600/40 text-emerald-800 hover:bg-emerald-50 text-label-md font-bold transition-colors cursor-pointer shadow-sm">
          <span class="material-symbols-outlined text-[18px]">restaurant</span>+ Add Lunch Break
        </button>
      </div>

      <div class="flex items-center gap-3">
        <button type="button" onclick="resetToInitial()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-label-md font-semibold transition-colors">
          <span class="material-symbols-outlined text-[18px]">restart_alt</span>Reset
        </button>
        <button type="submit" id="save_button" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-secondary text-on-secondary hover:bg-on-secondary-fixed-variant text-label-md font-bold transition-all shadow-md cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">save</span>Save Period Setup
        </button>
      </div>
    </div>
  </div>
</form>

<script>
  // Initial server data
  var initialSlots = <?php echo json_encode($slots ?? []); ?>;
  var currentSlots = JSON.parse(JSON.stringify(initialSlots));

  function onGroupChanged(groupId) {
    if (!groupId) return;
    window.location.href = '<?php echo site_url("timetable/period_setup"); ?>?academic_group_id=' + groupId;
  }

  function formatTimeHHMM(timeStr) {
    if (!timeStr) return '';
    var parts = timeStr.split(':');
    return parts[0].padStart(2, '0') + ':' + parts[1].padStart(2, '0');
  }

  function calculateDuration(startStr, endStr) {
    if (!startStr || !endStr) return '—';
    var sParts = startStr.split(':');
    var eParts = endStr.split(':');
    var sMinutes = parseInt(sParts[0], 10) * 60 + parseInt(sParts[1], 10);
    var eMinutes = parseInt(eParts[0], 10) * 60 + parseInt(eParts[1], 10);
    var diff = eMinutes - sMinutes;
    if (diff <= 0) return '<span class="text-error font-bold">Invalid</span>';
    return diff + ' min';
  }

  function renderSlots() {
    var tbody = document.getElementById('slots_tbody');
    tbody.innerHTML = '';

    var periodCount = 0;
    var breakCount = 0;
    var lunchCount = 0;

    if (currentSlots.length === 0) {
      tbody.innerHTML = '<tr><td colspan="8" class="p-8 text-center text-on-surface-variant text-body-md font-medium">No periods configured for this Department / Group yet. Click "+ Add Period" to begin.</td></tr>';
      updateStats(0, 0, 0);
      return;
    }

    currentSlots.forEach(function (slot, idx) {
      var isPeriod = (slot.period_type === 'Period');
      var isLunch  = (slot.period_type === 'Lunch Break');
      var isBreak  = (slot.period_type === 'Break');

      if (isPeriod) periodCount++;
      if (isBreak) breakCount++;
      if (isLunch) lunchCount++;

      var rowBg = isLunch 
        ? 'bg-emerald-50/40 hover:bg-emerald-50/80' 
        : (isBreak ? 'bg-amber-50/40 hover:bg-amber-50/80' : 'bg-surface-container-lowest hover:bg-surface-container-low/60');

      var badgeHtml = '';
      if (isPeriod) {
        badgeHtml = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[12px] font-bold bg-primary/10 text-primary border border-primary/20"><span class="material-symbols-outlined text-[14px]">school</span>Period</span>';
      } else if (isLunch) {
        badgeHtml = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[12px] font-bold bg-emerald-100 text-emerald-900 border border-emerald-300"><span class="material-symbols-outlined text-[14px]">restaurant</span>Lunch</span>';
      } else {
        badgeHtml = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[12px] font-bold bg-amber-100 text-amber-900 border border-amber-300"><span class="material-symbols-outlined text-[14px]">coffee</span>Break</span>';
      }

      var startTime = formatTimeHHMM(slot.start_time);
      var endTime   = formatTimeHHMM(slot.end_time);
      var duration  = calculateDuration(startTime, endTime);

      var tr = document.createElement('tr');
      tr.className = rowBg + ' transition-colors group';
      tr.id = 'slot_row_' + idx;

      tr.innerHTML = 
        '<td class="py-3 px-4 text-center text-label-md font-bold text-on-surface-variant">' + (idx + 1) + '</td>' +
        '<td class="py-3 px-4 whitespace-nowrap">' + badgeHtml + '</td>' +
        '<td class="py-3 px-4">' +
          '<input type="text" value="' + htmlEscape(slot.period_name || '') + '" onchange="onNameChange(' + idx + ', this.value)" class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-semibold text-on-surface focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all" placeholder="Slot Name">' +
        '</td>' +
        '<td class="py-3 px-4">' +
          '<input type="time" value="' + startTime + '" onchange="onTimeChange(' + idx + ', \'start\', this.value)" class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-semibold text-on-surface focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all">' +
        '</td>' +
        '<td class="py-3 px-4">' +
          '<input type="time" value="' + endTime + '" onchange="onTimeChange(' + idx + ', \'end\', this.value)" class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-semibold text-on-surface focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all">' +
        '</td>' +
        '<td class="py-3 px-4 text-center font-bold text-[13px] text-on-surface" id="duration_' + idx + '">' + duration + '</td>' +
        '<td class="py-3 px-4 text-center whitespace-nowrap">' +
          '<div class="inline-flex items-center gap-1">' +
            '<button type="button" onclick="moveSlot(' + idx + ', -1)" ' + (idx === 0 ? 'disabled class="opacity-30 cursor-not-allowed p-1 text-on-surface-variant"' : 'class="p-1 rounded hover:bg-surface-container-high text-on-surface transition-colors cursor-pointer"') + ' title="Move Up"><span class="material-symbols-outlined text-[18px]">arrow_upward</span></button>' +
            '<button type="button" onclick="moveSlot(' + idx + ', 1)" ' + (idx === currentSlots.length - 1 ? 'disabled class="opacity-30 cursor-not-allowed p-1 text-on-surface-variant"' : 'class="p-1 rounded hover:bg-surface-container-high text-on-surface transition-colors cursor-pointer"') + ' title="Move Down"><span class="material-symbols-outlined text-[18px]">arrow_downward</span></button>' +
          '</div>' +
        '</td>' +
        '<td class="py-3 px-4 text-center">' +
          '<button type="button" onclick="removeSlot(' + idx + ')" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-error/10 hover:text-error transition-colors cursor-pointer" title="Delete slot"><span class="material-symbols-outlined text-[20px]">delete</span></button>' +
        '</td>';

      tbody.appendChild(tr);
    });

    updateStats(periodCount, breakCount, lunchCount);
    validateSchedule();
  }

  function updateStats(periods, breaks, lunch) {
    document.getElementById('stat_periods_count').innerText = periods;
    document.getElementById('stat_breaks_count').innerText = breaks;
    document.getElementById('stat_lunch_count').innerText = lunch;
  }

  function addSlot(type) {
    var lastSlot = currentSlots.length > 0 ? currentSlots[currentSlots.length - 1] : null;
    var nextStart = '09:00';
    if (lastSlot && lastSlot.end_time) {
      nextStart = formatTimeHHMM(lastSlot.end_time);
    }

    // Default duration: Period = 45m, Break = 15m, Lunch = 45m
    var durMinutes = (type === 'Period' || type === 'Lunch Break') ? 45 : 15;
    var sParts = nextStart.split(':');
    var totalMin = parseInt(sParts[0], 10) * 60 + parseInt(sParts[1], 10) + durMinutes;
    var eHour = Math.floor(totalMin / 60) % 24;
    var eMin  = totalMin % 60;
    var nextEnd = String(eHour).padStart(2, '0') + ':' + String(eMin).padStart(2, '0');

    // Auto-name
    var name = 'Period ' + (currentSlots.filter(function(s){ return s.period_type === 'Period'; }).length + 1);
    if (type === 'Break') {
      name = 'Break ' + (currentSlots.filter(function(s){ return s.period_type === 'Break'; }).length + 1);
    } else if (type === 'Lunch Break') {
      name = 'Lunch Break';
    }

    currentSlots.push({
      period_id: null,
      period_name: name,
      period_type: type,
      start_time: nextStart + ':00',
      end_time: nextEnd + ':00',
      status: 1
    });

    renderSlots();
  }

  function removeSlot(idx) {
    if (confirm('Are you sure you want to remove "' + (currentSlots[idx].period_name || 'this slot') + '"?')) {
      currentSlots.splice(idx, 1);
      renderSlots();
    }
  }

  function moveSlot(idx, dir) {
    var target = idx + dir;
    if (target < 0 || target >= currentSlots.length) return;
    var temp = currentSlots[idx];
    currentSlots[idx] = currentSlots[target];
    currentSlots[target] = temp;
    renderSlots();
  }

  function onNameChange(idx, val) {
    if (currentSlots[idx]) {
      currentSlots[idx].period_name = val;
    }
  }

  function onTimeChange(idx, which, val) {
    if (!currentSlots[idx]) return;
    if (which === 'start') {
      currentSlots[idx].start_time = val ? (val + ':00') : '';
    } else {
      currentSlots[idx].end_time = val ? (val + ':00') : '';
    }
    var st = formatTimeHHMM(currentSlots[idx].start_time);
    var et = formatTimeHHMM(currentSlots[idx].end_time);
    var durCell = document.getElementById('duration_' + idx);
    if (durCell) {
      durCell.innerHTML = calculateDuration(st, et);
    }
    validateSchedule();
  }

  function validateSchedule() {
    var alertBox = document.getElementById('validation_alert');
    var msgBox   = document.getElementById('validation_message');
    var saveBtn  = document.getElementById('save_button');

    alertBox.classList.add('hidden');
    saveBtn.disabled = false;
    saveBtn.classList.remove('opacity-50', 'cursor-not-allowed');

    if (currentSlots.length === 0) return true;

    // 1. Check end > start for each
    for (var i = 0; i < currentSlots.length; i++) {
      var s = currentSlots[i];
      var row = document.getElementById('slot_row_' + i);
      if (row) row.classList.remove('ring-2', 'ring-error');

      if (!s.start_time || !s.end_time) {
        showError('Start time and End time are required for slot #' + (i + 1), row);
        return false;
      }
      var sMin = toMinutes(s.start_time);
      var eMin = toMinutes(s.end_time);
      if (eMin <= sMin) {
        showError('End time must be after Start time for "' + s.period_name + '"', row);
        return false;
      }
    }

    // 2. Check overlap between any pair
    for (var i = 0; i < currentSlots.length; i++) {
      var sA = currentSlots[i];
      var aStart = toMinutes(sA.start_time);
      var aEnd   = toMinutes(sA.end_time);

      for (var j = i + 1; j < currentSlots.length; j++) {
        var sB = currentSlots[j];
        var bStart = toMinutes(sB.start_time);
        var bEnd   = toMinutes(sB.end_time);

        if (aStart < bEnd && aEnd > bStart) {
          var rowA = document.getElementById('slot_row_' + i);
          var rowB = document.getElementById('slot_row_' + j);
          showError('Time overlap detected between "' + sA.period_name + '" (' + formatTimeHHMM(sA.start_time) + '–' + formatTimeHHMM(sA.end_time) + ') and "' + sB.period_name + '" (' + formatTimeHHMM(sB.start_time) + '–' + formatTimeHHMM(sB.end_time) + ')', rowA, rowB);
          return false;
        }
      }
    }

    return true;
  }

  function toMinutes(timeStr) {
    if (!timeStr) return 0;
    var p = timeStr.split(':');
    return parseInt(p[0], 10) * 60 + parseInt(p[1], 10);
  }

  function showError(msg, rowA, rowB) {
    var alertBox = document.getElementById('validation_alert');
    var msgBox   = document.getElementById('validation_message');
    var saveBtn  = document.getElementById('save_button');

    msgBox.innerText = msg;
    alertBox.classList.remove('hidden');
    saveBtn.disabled = true;
    saveBtn.classList.add('opacity-50', 'cursor-not-allowed');

    if (rowA) rowA.classList.add('ring-2', 'ring-error');
    if (rowB) rowB.classList.add('ring-2', 'ring-error');
  }

  function resetToInitial() {
    currentSlots = JSON.parse(JSON.stringify(initialSlots));
    renderSlots();
  }

  function htmlEscape(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }

  // Intercept form submission to bundle JSON slots
  document.getElementById('periodSetupForm').addEventListener('submit', function (e) {
    if (!validateSchedule()) {
      e.preventDefault();
      return false;
    }

    var payload = currentSlots.map(function (s) {
      return {
        period_id: s.period_id || null,
        name: s.period_name,
        type: s.period_type,
        start_time: s.start_time,
        end_time: s.end_time
      };
    });

    document.getElementById('slots_json_field').value = JSON.stringify(payload);
  });

  // Initial render
  renderSlots();
</script>
