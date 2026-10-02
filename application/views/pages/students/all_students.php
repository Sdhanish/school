<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-6">

  <!-- =========================================================================
       PAGE HEADER & ACADEMIC YEAR SELECTOR
       ========================================================================= -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2 border-b border-slate-200">
    
    <!-- Title & Breadcrumb -->
    <div class="flex items-center gap-3">
      <span class="w-10 h-10 rounded-xl bg-emerald-800 text-white flex items-center justify-center shadow-xs">
        <span class="material-symbols-outlined text-[22px]">groups</span>
      </span>
      <div>
        <h2 class="font-bold text-xl text-slate-900 leading-tight">All Students</h2>
        <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-0.5">
          <span>Student Management</span>
          <span class="material-symbols-outlined text-[12px]">chevron_right</span>
          <span class="text-slate-800 font-medium">All Students</span>
        </div>
      </div>
    </div>

    <!-- Top Right Controls: Academic Year Selector + Add Student Action -->
    <div class="flex flex-wrap items-center gap-3">
      
      <!-- Academic Year Selector -->
      <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-2xs">
        <span class="material-symbols-outlined text-[18px] text-emerald-700">calendar_month</span>
        <label for="all-students-academic-year" class="text-xs font-bold text-slate-700 whitespace-nowrap">Academic Year:</label>
        <select id="all-students-academic-year" onchange="onAcademicYearChanged(this.value)" class="text-xs font-bold text-emerald-900 bg-transparent border-none focus:ring-0 cursor-pointer pr-6 py-1">
          <?php foreach ($years as $y): ?>
            <?php $isActive = (!empty($y->is_active)); ?>
            <option value="<?php echo $y->academic_year_id; ?>" <?php echo ($selected_year == $y->academic_year_id) ? 'selected' : ''; ?>>
              <?php echo html_escape($y->year_name); ?><?php echo $isActive ? ' (Active)' : ''; ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Quick Add Student Button -->
      <a href="<?php echo site_url('students/register'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-button-primary text-white text-xs font-bold hover:bg-button-primary-hover transition-colors shadow-2xs">
        <span class="material-symbols-outlined text-[18px]">person_add</span>
        <span>Register Student</span>
      </a>

    </div>

  </div>

  <!-- =========================================================================
       CLASS CARDS RIBBON / GRID (DYNAMIC STUDENT COUNTS)
       ========================================================================= -->
  <div class="space-y-2.5">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Select Class / Grade</span>
        <span id="selected-year-display-badge" class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
          <?php
            $activeYearName = $active_academic_year->year_name ?? '';
            foreach ($years as $y) {
              if ($y->academic_year_id == $selected_year) {
                $activeYearName = $y->year_name;
                break;
              }
            }
            echo html_escape($activeYearName);
          ?>
        </span>
      </div>
      <span class="text-xs font-medium text-slate-500" id="classes-total-count-summary">
        <?php
          $totalCountAll = 0;
          foreach ($classes_with_counts as $cwc) {
            $totalCountAll += (int)$cwc->total_students;
          }
          echo $totalCountAll . ' Total Students';
        ?>
      </span>
    </div>

    <!-- Academic Group Filter Pills -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1">
      <button type="button" onclick="filterByAcademicGroup('all', this)" class="academic-group-pill px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all bg-button-primary text-white shadow-2xs cursor-pointer" data-group-id="all">
        All Department / Groups
      </button>
      <?php if (!empty($groups)): foreach ($groups as $grp): ?>
        <button type="button" onclick="filterByAcademicGroup('<?php echo $grp->academic_group_id; ?>', this)" class="academic-group-pill px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all border border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-50 cursor-pointer" data-group-id="<?php echo $grp->academic_group_id; ?>">
          <?php echo html_escape($grp->group_name); ?>
        </button>
      <?php endforeach; endif; ?>
    </div>

    <!-- Class Cards Horizontal Carousel / Grid -->
    <div id="class-cards-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
      
      <!-- Card: All Classes -->
      <div onclick="selectClassCard(null, this)" class="class-card p-3 rounded-xl border transition-all cursor-pointer bg-white shadow-2xs flex flex-col justify-between <?php echo ($selected_class === NULL) ? 'border-emerald-600 ring-2 ring-emerald-600/30 bg-emerald-50/40 text-emerald-900' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50/60 text-slate-800'; ?>" data-class-id="" data-group-id="all">
        <div class="flex items-center justify-between gap-1">
          <span class="font-bold text-xs truncate">All Classes</span>
          <span class="material-symbols-outlined text-[16px] text-emerald-700">select_all</span>
        </div>
        <div class="mt-2 flex items-baseline justify-between">
          <span id="card-count-all" class="font-extrabold text-base text-emerald-800"><?php echo $totalCountAll; ?></span>
          <span class="text-[11px] font-semibold text-slate-500">Students</span>
        </div>
      </div>

      <!-- Class Specific Cards -->
      <?php foreach ($classes_with_counts as $cwc): ?>
        <?php
          $isSelected = ($selected_class !== NULL && $selected_class == $cwc->class_id);
          $stCount = (int)$cwc->total_students;
        ?>
        <div onclick="selectClassCard(<?php echo $cwc->class_id; ?>, this)" class="class-card p-3 rounded-xl border transition-all cursor-pointer bg-white shadow-2xs flex flex-col justify-between <?php echo $isSelected ? 'border-emerald-600 ring-2 ring-emerald-600/30 bg-emerald-50/40 text-emerald-900' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50/60 text-slate-800'; ?>" data-class-id="<?php echo $cwc->class_id; ?>" data-group-id="<?php echo $cwc->academic_group_id ?? ''; ?>">
          <div class="flex items-center justify-between gap-1">
            <span class="font-bold text-xs truncate"><?php echo html_escape($cwc->class_name); ?></span>
            <span class="text-[10px] font-mono text-slate-400"><?php echo html_escape($cwc->class_code ?? ''); ?></span>
          </div>
          <div class="mt-2 flex items-baseline justify-between">
            <span class="font-extrabold text-base <?php echo $stCount > 0 ? 'text-emerald-800' : 'text-slate-400'; ?>"><?php echo $stCount; ?></span>
            <span class="text-[11px] font-semibold text-slate-500">Students</span>
          </div>
        </div>
      <?php endforeach; ?>

    </div>
  </div>

  <!-- =========================================================================
       FILTER & SEARCH TOOLBAR
       ========================================================================= -->
  <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-4">
    
    <!-- Active Selection Summary Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-100 text-xs">
      <div class="flex items-center gap-2 flex-wrap">
        <span class="font-bold text-slate-800" id="current-class-heading">
          <?php
            $currentClassName = 'All Classes';
            if ($selected_class !== NULL) {
              foreach ($classes_with_counts as $cwc) {
                if ($cwc->class_id == $selected_class) {
                  $currentClassName = $cwc->class_name;
                  break;
                }
              }
            }
            echo 'Students — ' . html_escape($currentClassName);
          ?>
        </span>
        <span class="text-slate-300">•</span>
        <span class="text-slate-600 font-medium" id="current-year-heading">
          Academic Year: <strong class="text-emerald-800"><?php echo html_escape($activeYearName); ?></strong>
        </span>
      </div>
      <div class="flex items-center gap-2">
        <span class="text-slate-500 font-medium">Showing:</span>
        <span id="table-total-count-badge" class="font-bold text-emerald-800 font-mono">0</span>
        <span class="text-slate-500">Students</span>
      </div>
    </div>

    <!-- Filters Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
      
      <!-- Search Input (4 cols) -->
      <div class="lg:col-span-4 relative">
        <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-[18px]">search</span>
        <input type="text" id="all-students-search-input" onkeyup="if(event.key === 'Enter') triggerDataTableReload()" placeholder="Search students by name, admission no., roll no..." class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-slate-300 bg-white text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600"/>
      </div>

      <!-- Academic Group Filter (2 cols) -->
      <div class="lg:col-span-2 relative">
        <select id="all-students-group-filter" onchange="onGroupFilterDropdownChanged(this.value)" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white text-slate-800 font-medium focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600">
          <option value="">All Department / Groups</option>
          <?php if (!empty($groups)): foreach ($groups as $grp): ?>
            <option value="<?php echo $grp->academic_group_id; ?>"><?php echo html_escape($grp->group_name); ?></option>
          <?php endforeach; endif; ?>
        </select>
      </div>

      <!-- Division Filter (3 cols) -->
      <div class="lg:col-span-3 relative">
        <select id="all-students-division-filter" name="division_id" onchange="triggerDataTableReload()" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white text-slate-800 font-medium focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600">
          <option value="">All Divisions</option>
          <?php foreach ($sections as $sec): ?>
            <option value="<?php echo ($sec->division_id ?? $sec->section_id); ?>">
              <?php echo html_escape(($sec->class_name ? $sec->class_name . ' - ' : '') . ($sec->division_name ?? $sec->section_name)); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Status Filter (1 col) -->
      <div class="lg:col-span-1 relative">
        <select id="all-students-status-filter" onchange="onStatusFilterChanged()" class="w-full px-2 py-2 text-xs rounded-xl border border-slate-300 bg-white text-slate-800 font-medium focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600">
          <option value="All">All</option>
          <option value="1" selected>Active</option>
          <option value="0">Inactive</option>
        </select>
      </div>

      <!-- Reset & Filter Button (2 cols) -->
      <div class="lg:col-span-2 flex gap-2">
        <button type="button" onclick="triggerDataTableReload()" class="flex-1 py-2 px-3 rounded-xl bg-button-primary text-white font-bold text-xs hover:bg-button-primary-hover transition-colors shadow-2xs flex items-center justify-center gap-1">
          <span class="material-symbols-outlined text-[16px]">filter_list</span>
          <span>Apply</span>
        </button>
        <button type="button" onclick="resetAllFilters()" title="Reset Filters" class="p-2 rounded-xl border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 transition-colors shadow-2xs flex items-center justify-center">
          <span class="material-symbols-outlined text-[17px]">restart_alt</span>
        </button>
      </div>

    </div>

  </div>

  <!-- =========================================================================
       STUDENT DIRECTORY TABLE
       ========================================================================= -->
  <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs overflow-hidden">
    
    <div class="overflow-x-auto">
      <table id="all-students-datatable" class="w-full text-left text-xs">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-50/80 text-slate-700 font-bold uppercase tracking-wider">
            <th class="p-3 pl-4">Student</th>
            <th class="p-3">Admission No</th>
            <th class="p-3">Roll No</th>
            <th class="p-3">Class</th>
            <th class="p-3">Division</th>
            <th class="p-3">DOB</th>
            <th class="p-3">Gender</th>
            <th class="p-3">Parent / Guardian</th>
            <th class="p-3">Contact</th>
            <th class="p-3">Status</th>
            <th class="p-3 pr-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <!-- Populated dynamically via DataTables AJAX -->
        </tbody>
      </table>
    </div>

  </div>

</div>

<!-- Client JavaScript Logic -->
<script>
  let currentAcademicYearId = <?php echo (int)$selected_year; ?>;
  let currentClassId        = <?php echo ($selected_class !== NULL) ? (int)$selected_class : 'null'; ?>;
  let allStudentsDataTable  = null;

  document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('status')) {
      const s = urlParams.get('status');
      if (s !== null && s !== '') {
        $('#all-students-status-filter').val(s);
      }
    }
    initAllStudentsDataTable();
  });

  // Initialize DataTables with Server-Side Pagination
  function initAllStudentsDataTable() {
    if ($.fn.DataTable.isDataTable('#all-students-datatable')) {
      $('#all-students-datatable').DataTable().destroy();
    }

    allStudentsDataTable = $('#all-students-datatable').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: '<?php echo site_url('students/all_students_ajax'); ?>',
        type: 'POST',
        data: function (d) {
          d.academic_year_id = currentAcademicYearId;
          d.class_id         = (currentClassId !== null && currentClassId !== '' && currentClassId > 0) ? currentClassId : '';
          d.division_id = $('#all-students-division-filter').val() || '';
          d.status           = $('#all-students-status-filter').val() || (new URLSearchParams(window.location.search)).get('status') || '';
          d.gender           = (new URLSearchParams(window.location.search)).get('gender') || '';
          d.custom_search    = $('#all-students-search-input').val() || '';
          if (window.CSRF_TOKEN_NAME && window.CSRF_HASH) {
            d[window.CSRF_TOKEN_NAME] = window.CSRF_HASH;
          }
        },
        dataSrc: function (json) {
          if (json && json.csrf_hash) window.CSRF_HASH = json.csrf_hash;
          if (json && json.recordsFiltered !== undefined) {
            $('#table-total-count-badge').text(json.recordsFiltered);
          }
          return (json && json.data) ? json.data : [];
        },
        error: function (xhr, textStatus, errorThrown) {
          console.error('All Students DataTables AJAX Error:', xhr.status, textStatus, errorThrown, xhr.responseText);
        }
      },
      pageLength: 10,
      order: [[1, 'asc']],
      columns: [
        { data: 0, orderable: true },
        { data: 1, orderable: true },
        { data: 2, orderable: true },
        { data: 3, orderable: true },
        { data: 4, orderable: true },
        { data: 5, orderable: true },
        { data: 6, orderable: true },
        { data: 7, orderable: false },
        { data: 8, orderable: false },
        { data: 9, orderable: true },
        { data: 10, orderable: false, className: 'p-3 pr-4 text-right align-middle' }
      ],
      columnDefs: [
        { targets: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9], className: 'p-3 text-xs align-middle' }
      ],
      language: {
        emptyTable: `
          <div class="py-12 text-center text-slate-400 space-y-2">
            <span class="material-symbols-outlined text-[42px] text-slate-300">school</span>
            <div class="font-bold text-slate-700 text-sm">No students found for this class and academic year.</div>
            <p class="text-xs text-slate-500">Try changing your search keywords, division filters, or switch academic session.</p>
          </div>
        `,
        processing: '<div class="flex items-center justify-center p-4 text-emerald-800 font-bold text-xs"><span class="material-symbols-outlined animate-spin mr-2">progress_activity</span> Loading students...</div>'
      }
    });
  }

  // Reload Class Counts via AJAX based on active Academic Year and Status filter
  function reloadClassCounts() {
    const statusVal = $('#all-students-status-filter').val() || '1';
    $.ajax({
      url: '<?php echo site_url('students/class_counts_ajax'); ?>',
      type: 'POST',
      data: {
        academic_year_id: currentAcademicYearId,
        status: statusVal,
        [window.CSRF_TOKEN_NAME]: window.CSRF_HASH
      },
      dataType: 'json',
      success: function (res) {
        if (res && res.csrf_hash) window.CSRF_HASH = res.csrf_hash;
        if (res && res.status && res.classes) {
          renderClassCardsGrid(res.classes);
        }
      }
    });
  }

  // Trigger DataTables Reload
  function triggerDataTableReload() {
    if (allStudentsDataTable) {
      allStudentsDataTable.ajax.reload();
    }
  }

  // Status Filter Changed
  function onStatusFilterChanged() {
    reloadClassCounts();
    triggerDataTableReload();
  }

  // Reset Filters
  function resetAllFilters() {
    $('#all-students-search-input').val('');
    $('#all-students-division-filter').val('');
    $('#all-students-status-filter').val('1');
    reloadClassCounts();
    triggerDataTableReload();
  }

  // Select a Class Card
  function selectClassCard(classId, el) {
    currentClassId = classId;

    // Reset active styling across all cards
    document.querySelectorAll('.class-card').forEach(card => {
      card.className = "class-card p-3 rounded-xl border border-slate-200 hover:border-slate-300 hover:bg-slate-50/60 text-slate-800 transition-all cursor-pointer bg-white shadow-2xs flex flex-col justify-between";
    });

    if (el) {
      el.className = "class-card p-3 rounded-xl border border-emerald-600 ring-2 ring-emerald-600/30 bg-emerald-50/40 text-emerald-900 transition-all cursor-pointer shadow-2xs flex flex-col justify-between";
    }

    // Update Heading
    const headingText = (classId === null) ? 'All Classes' : $(el).find('span.font-bold').text();
    $('#current-class-heading').text('Students — ' + headingText);

    triggerDataTableReload();
  }

  // Academic Year Changed
  function onAcademicYearChanged(newYearId) {
    currentAcademicYearId = parseInt(newYearId, 10);
    const selectedYearText = $('#all-students-academic-year option:selected').text().replace('(Active)', '').trim();
    
    $('#selected-year-display-badge').text(selectedYearText);
    $('#current-year-heading strong').text(selectedYearText);

    reloadClassCounts();
    triggerDataTableReload();
  }

  // Render Class Cards dynamically when academic year switches
  function renderClassCardsGrid(classes) {
    let totalAll = 0;
    classes.forEach(c => { totalAll += parseInt(c.total_students || 0, 10); });

    $('#classes-total-count-summary').text(totalAll + ' Total Students');

    let html = `
      <!-- Card: All Classes -->
      <div onclick="selectClassCard(null, this)" class="class-card p-3 rounded-xl border transition-all cursor-pointer bg-white shadow-2xs flex flex-col justify-between ${currentClassId === null ? 'border-emerald-600 ring-2 ring-emerald-600/30 bg-emerald-50/40 text-emerald-900' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50/60 text-slate-800'}" data-class-id="">
        <div class="flex items-center justify-between gap-1">
          <span class="font-bold text-xs truncate">All Classes</span>
          <span class="material-symbols-outlined text-[16px] text-emerald-700">select_all</span>
        </div>
        <div class="mt-2 flex items-baseline justify-between">
          <span id="card-count-all" class="font-extrabold text-base text-emerald-800">${totalAll}</span>
          <span class="text-[11px] font-semibold text-slate-500">Students</span>
        </div>
      </div>
    `;

    classes.forEach(c => {
      const isSelected = (currentClassId !== null && currentClassId === parseInt(c.class_id, 10));
      const stCount = parseInt(c.total_students || 0, 10);
      html += `
        <div onclick="selectClassCard(${c.class_id}, this)" class="class-card p-3 rounded-xl border transition-all cursor-pointer bg-white shadow-2xs flex flex-col justify-between ${isSelected ? 'border-emerald-600 ring-2 ring-emerald-600/30 bg-emerald-50/40 text-emerald-900' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50/60 text-slate-800'}" data-class-id="${c.class_id}">
          <div class="flex items-center justify-between gap-1">
            <span class="font-bold text-xs truncate">${$('<div>').text(c.class_name).html()}</span>
            <span class="text-[10px] font-mono text-slate-400">${$('<div>').text(c.class_code || '').html()}</span>
          </div>
          <div class="mt-2 flex items-baseline justify-between">
            <span class="font-extrabold text-base ${stCount > 0 ? 'text-emerald-800' : 'text-slate-400'}">${stCount}</span>
            <span class="text-[11px] font-semibold text-slate-500">Students</span>
          </div>
        </div>
      `;
    });

    $('#class-cards-grid').html(html);
  }

  // Filter Class Cards by Academic Group
  function filterByAcademicGroup(groupId, el) {
    $('.academic-group-pill').removeClass('bg-emerald-800 text-white shadow-2xs').addClass('border border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-50');
    if (el) {
      $(el).removeClass('border border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-50').addClass('bg-emerald-800 text-white shadow-2xs');
    }

    $('#all-students-group-filter').val(groupId === 'all' ? '' : groupId);

    if (groupId === 'all') {
      $('.class-card').show();
    } else {
      $('.class-card').each(function () {
        const cGroup = $(this).attr('data-group-id');
        if (cGroup === 'all' || cGroup == groupId) {
          $(this).show();
        } else {
          $(this).hide();
        }
      });
    }
  }

  function onGroupFilterDropdownChanged(groupId) {
    const pill = $(`.academic-group-pill[data-group-id="${groupId || 'all'}"]`);
    if (pill.length) {
      filterByAcademicGroup(groupId || 'all', pill[0]);
    } else {
      filterByAcademicGroup(groupId || 'all', null);
    }
  }
</script>
