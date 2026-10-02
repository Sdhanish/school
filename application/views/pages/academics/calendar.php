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
        <h2 class="font-headline-md text-headline-md text-on-surface">Academic Calendar & Events</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Schedule school terms, examinations, national holidays, vacations, and academic meetings.</p>
      </div>
      <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
        <!-- Download Academic Calendar PDF Button -->
        <a id="btn_download_pdf" href="<?php echo site_url('academics/calendar_pdf?academic_year_id=' . $selected_year); ?>" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-surface-container-high hover:bg-surface-container-highest text-on-surface border border-outline-variant text-label-md font-semibold transition-colors shadow-xs cursor-pointer" title="Download Official Academic Calendar as PDF">
          <span class="material-symbols-outlined text-[18px] text-error">picture_as_pdf</span>Download Academic Calendar
        </a>

        <!-- Add Event / Holiday Button -->
        <button onclick="openAddEventModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Add Event / Holiday
        </button>
      </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-primary-fixed/30 text-primary flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-[22px]">calendar_month</span>
          </div>
          <div>
            <div class="text-[12px] text-on-surface-variant font-medium">Total Events</div>
            <div class="text-[20px] font-bold text-on-surface"><?php echo count($events); ?></div>
          </div>
        </div>
      </div>
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-error-container/40 text-error flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-[22px]">beach_access</span>
          </div>
          <div>
            <div class="text-[12px] text-on-surface-variant font-medium">Holidays & Breaks</div>
            <div class="text-[20px] font-bold text-error">
              <?php
                $holidays = array_filter($events, function($e) { return in_array($e->event_type, array('Holiday', 'Term Break')); });
                echo count($holidays);
              ?>
            </div>
          </div>
        </div>
      </div>
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-[22px]">quiz</span>
          </div>
          <div>
            <div class="text-[12px] text-on-surface-variant font-medium">Exams & Tests</div>
            <div class="text-[20px] font-bold text-on-surface">
              <?php
                $exams = array_filter($events, function($e) { return $e->event_type === 'Exam'; });
                echo count($exams);
              ?>
            </div>
          </div>
        </div>
      </div>
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-secondary-container text-on-secondary-container flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-[22px]">groups</span>
          </div>
          <div>
            <div class="text-[12px] text-on-surface-variant font-medium">Upcoming in 30 Days</div>
            <div class="text-[20px] font-bold text-on-surface"><?php echo count($upcoming); ?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters & View Toggle Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3">
          <!-- Academic Year Selector -->
          <div class="flex items-center gap-1.5">
            <label for="filter_year" class="text-[12px] font-medium text-on-surface-variant">Session:</label>
            <select id="filter_year" onchange="applyFilters()" class="px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-medium text-on-surface focus:ring-2 focus:ring-primary/20">
              <?php foreach ($years as $yr): ?>
                <option value="<?php echo $yr->academic_year_id; ?>" <?php echo ($selected_year == $yr->academic_year_id) ? 'selected' : ''; ?>>
                  <?php echo html_escape($yr->year_name); ?><?php echo (!empty($yr->is_active)) ? ' (Active)' : ''; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Category Filter -->
          <div class="flex items-center gap-1.5">
            <label for="filter_type" class="text-[12px] font-medium text-on-surface-variant">Category:</label>
            <select id="filter_type" onchange="applyFilters()" class="px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:ring-2 focus:ring-primary/20">
              <option value="">All Categories</option>
              <option value="Holiday" <?php echo ($selected_type === 'Holiday') ? 'selected' : ''; ?>>Holiday (Red)</option>
              <option value="Exam" <?php echo ($selected_type === 'Exam') ? 'selected' : ''; ?>>Exam</option>
              <option value="Event" <?php echo ($selected_type === 'Event') ? 'selected' : ''; ?>>Event</option>
              <option value="Activity" <?php echo ($selected_type === 'Activity') ? 'selected' : ''; ?>>Activity</option>
              <option value="Meeting" <?php echo ($selected_type === 'Meeting') ? 'selected' : ''; ?>>Meeting</option>
              <option value="Term Break" <?php echo ($selected_type === 'Term Break') ? 'selected' : ''; ?>>Term Break</option>
              <option value="Other" <?php echo ($selected_type === 'Other') ? 'selected' : ''; ?>>Other</option>
            </select>
          </div>

          <!-- Reset Filter -->
          <a href="<?php echo site_url('academics/calendar?academic_year_id=' . $selected_year); ?>" class="inline-flex items-center gap-1 px-3 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-body-md transition-colors">
            <span class="material-symbols-outlined text-[16px]">restart_alt</span>Reset
          </a>
        </div>

        <!-- View Mode Switcher -->
        <div class="flex items-center p-1 rounded-lg bg-surface-container-high border border-outline-variant/40 self-start md:self-auto">
          <button type="button" id="btn-view-list" onclick="setViewMode('list')" class="px-3.5 py-1.5 rounded-md text-label-md transition-all flex items-center gap-1.5 cursor-pointer bg-surface-container-lowest text-primary shadow-xs font-semibold">
            <span class="material-symbols-outlined text-[18px]">list</span>List Agenda
          </button>
          <button type="button" id="btn-view-grid" onclick="setViewMode('grid')" class="px-3.5 py-1.5 rounded-md text-label-md transition-all flex items-center gap-1.5 cursor-pointer text-on-surface-variant hover:text-on-surface font-medium">
            <span class="material-symbols-outlined text-[18px]">calendar_view_month</span>Monthly Grid
          </button>
        </div>
      </div>
    </div>

    <!-- 1. List / Agenda View -->
    <div id="view-list" class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Date & Duration</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Event Title</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Category / Type</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Audience</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Venue</th>
              <th class="text-right px-4 py-3 text-label-md text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/30 text-body-md">
            <?php if (empty($events)): ?>
              <tr>
                <td colspan="6" class="px-4 py-12 text-center text-on-surface-variant">
                  <div class="flex flex-col items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[40px] text-outline">event_busy</span>
                    <p class="font-medium text-[15px]">No academic calendar events found for this filter.</p>
                    <button onclick="openAddEventModal()" class="mt-2 text-primary hover:underline text-body-md font-semibold">+ Add your first event</button>
                  </div>
                </td>
              </tr>
            <?php endif; ?>
            <?php foreach ($events as $evt): ?>
              <?php
                $isHoliday = ($evt->event_type === 'Holiday');
                // Row class and badge styling: Holidays marked clearly in RED
                $rowClass = $isHoliday ? 'bg-red-50/40 hover:bg-red-50/70 border-l-4 border-l-red-600' : 'hover:bg-surface-container-low';
                
                $typeBadge = 'bg-surface-container-high text-on-surface border border-outline-variant/50';
                if ($isHoliday) {
                  $typeBadge = 'bg-red-600 text-white font-bold shadow-xs';
                } elseif ($evt->event_type === 'Exam') {
                  $typeBadge = 'bg-purple-100 text-purple-700 font-semibold border border-purple-200';
                } elseif ($evt->event_type === 'Event') {
                  $typeBadge = 'bg-blue-100 text-blue-700 font-semibold border border-blue-200';
                } elseif ($evt->event_type === 'Activity') {
                  $typeBadge = 'bg-emerald-100 text-emerald-700 font-semibold border border-emerald-200';
                } elseif ($evt->event_type === 'Meeting') {
                  $typeBadge = 'bg-amber-100 text-amber-800 font-semibold border border-amber-200';
                } elseif ($evt->event_type === 'Term Break') {
                  $typeBadge = 'bg-orange-100 text-orange-700 font-semibold border border-orange-200';
                }
              ?>
              <tr class="<?php echo $rowClass; ?> transition-colors">
                <td class="px-4 py-3 font-semibold text-on-surface whitespace-nowrap">
                  <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-lg <?php echo $isHoliday ? 'bg-red-100 border-red-300' : 'bg-surface-container-high border-outline-variant/40'; ?> flex flex-col items-center justify-center shrink-0 border">
                      <span class="text-[10px] font-bold uppercase <?php echo $isHoliday ? 'text-red-700' : 'text-on-surface-variant'; ?> leading-none"><?php echo date('M', strtotime($evt->start_date)); ?></span>
                      <span class="text-[13px] font-extrabold <?php echo $isHoliday ? 'text-red-600' : 'text-primary'; ?> leading-none mt-0.5"><?php echo date('d', strtotime($evt->start_date)); ?></span>
                    </div>
                    <div>
                      <div class="text-[13px] <?php echo $isHoliday ? 'text-red-950 font-bold' : 'text-on-surface'; ?>">
                        <?php echo date('D, d M Y', strtotime($evt->start_date)); ?>
                        <?php if ($evt->end_date && $evt->end_date !== $evt->start_date): ?>
                          <span class="text-on-surface-variant font-normal text-[12px]">to <?php echo date('d M Y', strtotime($evt->end_date)); ?></span>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3 font-semibold text-on-surface">
                  <div class="flex items-center gap-1.5">
                    <?php if ($isHoliday): ?>
                      <span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-red-600 text-white text-[10px] font-bold shrink-0" title="Holiday">★</span>
                    <?php endif; ?>
                    <span class="<?php echo $isHoliday ? 'text-red-900 font-bold' : ''; ?>"><?php echo html_escape($evt->title); ?></span>
                  </div>
                  <?php if (!empty($evt->description)): ?>
                    <div class="text-[12px] text-on-surface-variant font-normal mt-0.5 line-clamp-1"><?php echo html_escape($evt->description); ?></div>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] <?php echo $typeBadge; ?>">
                    <?php echo html_escape($evt->event_type); ?>
                  </span>
                </td>
                <td class="px-4 py-3 text-on-surface whitespace-nowrap">
                  <span class="inline-flex items-center gap-1 text-[12px] font-medium text-on-surface-variant">
                    <span class="material-symbols-outlined text-[14px]">group</span>
                    <?php echo html_escape($evt->audience); ?>
                  </span>
                </td>
                <td class="px-4 py-3 text-on-surface whitespace-nowrap">
                  <?php if ($evt->venue): ?>
                    <span class="inline-flex items-center gap-1 text-[12px] text-on-surface">
                      <span class="material-symbols-outlined text-[14px] text-on-surface-variant">location_on</span>
                      <?php echo html_escape($evt->venue); ?>
                    </span>
                  <?php else: ?>
                    <span class="text-on-surface-variant text-[12px]">—</span>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-1.5">
                    <button onclick="openEditEventModal(<?php echo $evt->calendar_id; ?>)" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-colors cursor-pointer" title="Edit Event"><span class="material-symbols-outlined text-[18px]">edit</span></button>
                    <a href="<?php echo site_url('academics/delete_calendar_event/' . $evt->calendar_id . '?academic_year_id=' . $selected_year . '&month=' . $selected_month . '&year=' . $selected_cal_year . '&view_mode=' . ($view_mode ?: 'list')); ?>" onclick="return confirm('Remove this calendar event?')" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-error-container/20 hover:text-error transition-colors" title="Delete Event"><span class="material-symbols-outlined text-[18px]">delete</span></a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 2. Monthly Visual Calendar Grid View -->
    <div id="view-grid" class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6 hidden">
      <?php
        $curMonth = (int)$selected_month;
        $curYear = (int)$selected_cal_year;
        $firstDayOfMonth = mktime(0, 0, 0, $curMonth, 1, $curYear);
        $numberDays = (int)date('t', $firstDayOfMonth);
        $dateComponents = getdate($firstDayOfMonth);
        $monthName = $dateComponents['month'];
        $dayOfWeek = (int)$dateComponents['wday']; // 0 for Sunday

        $prevMonth = ($curMonth == 1) ? 12 : $curMonth - 1;
        $prevYear = ($curMonth == 1) ? $curYear - 1 : $curYear;
        $nextMonth = ($curMonth == 12) ? 1 : $curMonth + 1;
        $nextYear = ($curMonth == 12) ? $curYear + 1 : $curYear;
      ?>

      <!-- Calendar Header & Navigation Controls -->
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-4 pb-3 border-b border-outline-variant/40">
        <div class="flex items-center gap-2.5">
          <div class="w-9 h-9 rounded-lg bg-primary-fixed/30 text-primary flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-[22px]">calendar_month</span>
          </div>
          <div>
            <h3 class="text-title-lg font-bold text-on-surface flex items-center gap-2">
              <?php echo $monthName . ' ' . $curYear; ?>
            </h3>
            <p class="text-[12px] text-on-surface-variant font-medium">Session: <?php echo html_escape($selected_year_obj ? $selected_year_obj->year_name : ''); ?></p>
          </div>
        </div>

        <!-- Interactive Month & Year Selector Controls -->
        <div class="flex flex-wrap items-center gap-2">
          <!-- Previous Month Button -->
          <a href="<?php echo site_url('academics/calendar?academic_year_id=' . $selected_year . '&event_type=' . urlencode($selected_type) . '&month=' . $prevMonth . '&year=' . $prevYear . '&view_mode=grid'); ?>" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-outline-variant hover:bg-surface-container-high text-on-surface text-label-md font-medium transition-colors cursor-pointer" title="Previous Month">
            <span class="material-symbols-outlined text-[18px]">chevron_left</span>
            <span class="hidden sm:inline">Prev</span>
          </a>

          <!-- Month Selector Dropdown -->
          <select id="cal_select_month" onchange="navigateCalMonthYear()" class="px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-label-md font-semibold text-on-surface focus:ring-2 focus:ring-primary/20 cursor-pointer">
            <?php for ($m = 1; $m <= 12; $m++): ?>
              <option value="<?php echo $m; ?>" <?php echo ($curMonth == $m) ? 'selected' : ''; ?>>
                <?php echo date('F', mktime(0, 0, 0, $m, 1, 2000)); ?>
              </option>
            <?php endfor; ?>
          </select>

          <!-- Year Selector Dropdown -->
          <select id="cal_select_year" onchange="navigateCalMonthYear()" class="px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-label-md font-semibold text-on-surface focus:ring-2 focus:ring-primary/20 cursor-pointer">
            <?php
              $startYearRange = min(intval(date('Y')) - 3, intval($curYear) - 2);
              $endYearRange = max(intval(date('Y')) + 5, intval($curYear) + 3);
              for ($yrOpt = $startYearRange; $yrOpt <= $endYearRange; $yrOpt++):
            ?>
              <option value="<?php echo $yrOpt; ?>" <?php echo ($curYear == $yrOpt) ? 'selected' : ''; ?>>
                <?php echo $yrOpt; ?>
              </option>
            <?php endfor; ?>
          </select>

          <!-- Today Button -->
          <a href="<?php echo site_url('academics/calendar?academic_year_id=' . $selected_year . '&event_type=' . urlencode($selected_type) . '&month=' . date('n') . '&year=' . date('Y') . '&view_mode=grid'); ?>" class="px-3 py-1.5 text-label-md font-semibold rounded-lg border border-outline-variant hover:bg-surface-container-high text-on-surface transition-colors cursor-pointer" title="Return to current month">
            Today
          </a>

          <!-- Next Month Button -->
          <a href="<?php echo site_url('academics/calendar?academic_year_id=' . $selected_year . '&event_type=' . urlencode($selected_type) . '&month=' . $nextMonth . '&year=' . $nextYear . '&view_mode=grid'); ?>" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-outline-variant hover:bg-surface-container-high text-on-surface text-label-md font-medium transition-colors cursor-pointer" title="Next Month">
            <span class="hidden sm:inline">Next</span>
            <span class="material-symbols-outlined text-[18px]">chevron_right</span>
          </a>
        </div>
      </div>

      <!-- Calendar Weekday Header -->
      <div class="grid grid-cols-7 gap-1 text-center font-bold text-label-md text-on-surface-variant mb-2">
        <div class="py-2 bg-surface-container-low rounded-lg text-red-600">Sun</div>
        <div class="py-2 bg-surface-container-low rounded-lg">Mon</div>
        <div class="py-2 bg-surface-container-low rounded-lg">Tue</div>
        <div class="py-2 bg-surface-container-low rounded-lg">Wed</div>
        <div class="py-2 bg-surface-container-low rounded-lg">Thu</div>
        <div class="py-2 bg-surface-container-low rounded-lg">Fri</div>
        <div class="py-2 bg-surface-container-low rounded-lg">Sat</div>
      </div>

      <!-- Calendar Days Grid -->
      <div class="grid grid-cols-7 gap-1">
        <?php
          // Empty cells before first day
          for ($i = 0; $i < $dayOfWeek; $i++) {
            echo '<div class="min-h-[95px] p-2 bg-surface-container-low/20 rounded-lg border border-outline-variant/20 opacity-40"></div>';
          }

          // Days of the month
          for ($day = 1; $day <= $numberDays; $day++) {
            $currentDateStr = sprintf('%04d-%02d-%02d', $curYear, $curMonth, $day);
            $isToday = ($currentDateStr === date('Y-m-d'));

            // Find matching events for this day
            $dayEvents = array();
            $hasHoliday = false;
            foreach ($events as $ev) {
              if ($currentDateStr >= $ev->start_date && $currentDateStr <= ($ev->end_date ?: $ev->start_date)) {
                $dayEvents[] = $ev;
                if ($ev->event_type === 'Holiday') {
                  $hasHoliday = true;
                }
              }
            }

            // Cell styling: holidays get soft red background accent
            $cellBg = 'bg-surface-container-lowest border-outline-variant/40';
            if ($isToday) {
              $cellBg = 'bg-primary-fixed/20 border-primary/50';
            } elseif ($hasHoliday) {
              $cellBg = 'bg-red-50/40 border-red-200';
            }

            echo '<div class="min-h-[95px] p-2 rounded-lg border ' . $cellBg . ' flex flex-col justify-between transition-colors">';
            echo '  <div class="flex items-center justify-between">';
            if ($isToday) {
              echo '    <span class="w-6 h-6 rounded-full bg-primary text-on-primary flex items-center justify-center text-[12px] font-bold shadow-xs">' . $day . '</span>';
            } elseif ($hasHoliday) {
              echo '    <span class="text-[13px] font-extrabold text-red-600 flex items-center gap-1">' . $day . '<span class="text-[9px] text-red-500">★</span></span>';
            } else {
              echo '    <span class="text-[13px] text-on-surface font-semibold">' . $day . '</span>';
            }
            if ($hasHoliday) {
              echo '    <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.2 rounded bg-red-100 text-red-700 border border-red-200">Holiday</span>';
            }
            echo '  </div>';

            echo '  <div class="space-y-1 mt-1 flex-1 overflow-hidden">';
            foreach ($dayEvents as $dev) {
              $chipClass = 'bg-blue-100 text-blue-800 border border-blue-200';
              $isDevHoliday = ($dev->event_type === 'Holiday');

              // Prominent RED for Holidays
              if ($isDevHoliday) {
                $chipClass = 'bg-red-600 text-white font-bold shadow-xs hover:bg-red-700';
              } elseif ($dev->event_type === 'Exam') {
                $chipClass = 'bg-purple-100 text-purple-800 border border-purple-200 font-medium';
              } elseif ($dev->event_type === 'Activity') {
                $chipClass = 'bg-emerald-100 text-emerald-800 border border-emerald-200 font-medium';
              } elseif ($dev->event_type === 'Meeting') {
                $chipClass = 'bg-amber-100 text-amber-900 border border-amber-200 font-medium';
              } elseif ($dev->event_type === 'Term Break') {
                $chipClass = 'bg-orange-100 text-orange-800 border border-orange-200 font-medium';
              }

              echo '    <div onclick="openEditEventModal(' . $dev->calendar_id . ')" class="text-[10px] px-1.5 py-0.5 rounded ' . $chipClass . ' truncate cursor-pointer transition-colors" title="' . html_escape($dev->title . ' (' . $dev->event_type . ')') . '">';
              if ($isDevHoliday) {
                echo '★ ';
              }
              echo html_escape($dev->title);
              echo '    </div>';
            }
            echo '  </div>';
            echo '</div>';
          }
        ?>
      </div>
    </div>

    <!-- Modal: Add / Edit Academic Calendar Event -->
    <div id="modal-event" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 hidden">
      <div class="elevation-3 rounded-2xl bg-surface-container-lowest border border-outline-variant w-full max-w-lg overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant bg-surface-container-low/50">
          <h3 class="font-headline-md text-headline-md text-on-surface font-bold" id="modal-event-title">Add Calendar Event / Holiday</h3>
          <button type="button" onclick="document.getElementById('modal-event').classList.add('hidden')" class="p-1 rounded-lg text-on-surface-variant hover:bg-surface-container-high cursor-pointer"><span class="material-symbols-outlined">close</span></button>
        </div>
        <?php echo form_open('academics/calendar', array('class' => 'p-6 space-y-4')); ?>
          <input type="hidden" name="action" id="event_action" value="add"/>
          <input type="hidden" name="calendar_id" id="modal_calendar_id"/>
          <input type="hidden" name="redirect_academic_year" value="<?php echo $selected_year; ?>"/>
          <input type="hidden" name="redirect_month" value="<?php echo $selected_month; ?>"/>
          <input type="hidden" name="redirect_year" value="<?php echo $selected_cal_year; ?>"/>
          <input type="hidden" name="redirect_view" id="modal_redirect_view" value="<?php echo html_escape($view_mode ?: 'list'); ?>"/>

          <div>
            <label class="block text-label-md mb-1 font-medium text-on-surface">Academic Session *</label>
            <select name="academic_year_id" id="modal_event_year" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md">
              <?php foreach ($years as $yr): ?>
                <option value="<?php echo $yr->academic_year_id; ?>" <?php echo ($selected_year == $yr->academic_year_id) ? 'selected' : ''; ?>>
                  <?php echo html_escape($yr->year_name); ?><?php echo (!empty($yr->is_active)) ? ' (Active)' : ''; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label class="block text-label-md mb-1 font-medium text-on-surface">Event / Holiday Title *</label>
            <input type="text" name="title" id="modal_event_title_input" required placeholder="e.g. Independence Day, Annual Sports Meet, Mid-Term Exam" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md"/>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-label-md mb-1 font-medium text-on-surface">Category / Type *</label>
              <select name="event_type" id="modal_event_type" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md">
                <option value="Holiday">Holiday (Marked in Red)</option>
                <option value="Exam">Exam / Assessment</option>
                <option value="Event">Event / Celebration</option>
                <option value="Activity">Activity / Sports</option>
                <option value="Meeting">Meeting / PTM</option>
                <option value="Term Break">Term Break / Vacation</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div>
              <label class="block text-label-md mb-1 font-medium text-on-surface">Audience *</label>
              <select name="audience" id="modal_event_audience" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md">
                <option value="Whole School">Whole School</option>
                <option value="Students">Students Only</option>
                <option value="Teachers">Teachers Only</option>
                <option value="Parents">Parents & Guardians</option>
                <option value="Staff">All Staff</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-label-md mb-1 font-medium text-on-surface">Start Date *</label>
              <input type="date" name="start_date" id="modal_event_start_date" required value="<?php echo date('Y-m-d'); ?>" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md"/>
            </div>
            <div>
              <label class="block text-label-md mb-1 font-medium text-on-surface">End Date</label>
              <input type="date" name="end_date" id="modal_event_end_date" value="<?php echo date('Y-m-d'); ?>" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md"/>
            </div>
          </div>

          <div>
            <label class="block text-label-md mb-1 font-medium text-on-surface">Venue / Location</label>
            <input type="text" name="venue" id="modal_event_venue" placeholder="e.g. Auditorium, Main Ground, Online" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md"/>
          </div>

          <div>
            <label class="block text-label-md mb-1 font-medium text-on-surface">Description / Notes</label>
            <textarea name="description" id="modal_event_description" rows="2" placeholder="Provide event schedule details, instructions, or agenda notes..." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md"></textarea>
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t border-outline-variant">
            <button type="button" onclick="document.getElementById('modal-event').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant cursor-pointer text-body-md">Cancel</button>
            <button type="submit" class="px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant cursor-pointer">Save Event</button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <script>
      var currentAcademicYear = '<?php echo $selected_year; ?>';
      var currentEventType = '<?php echo html_escape($selected_type); ?>';
      var currentMonth = '<?php echo $selected_month; ?>';
      var currentCalYear = '<?php echo $selected_cal_year; ?>';
      var currentViewMode = '<?php echo html_escape($view_mode ?: ""); ?>';

      function applyFilters() {
        var yr = document.getElementById('filter_year').value;
        var type = document.getElementById('filter_type').value;
        var vMode = currentViewMode || (document.getElementById('view-grid').classList.contains('hidden') ? 'list' : 'grid');
        
        var url = '<?php echo site_url('academics/calendar'); ?>?academic_year_id=' + yr +
                  '&event_type=' + encodeURIComponent(type) +
                  '&month=' + currentMonth +
                  '&year=' + currentCalYear +
                  '&view_mode=' + vMode;
        window.location.href = url;
      }

      function navigateCalMonthYear() {
        var selMonth = document.getElementById('cal_select_month').value;
        var selYear = document.getElementById('cal_select_year').value;
        var yr = document.getElementById('filter_year') ? document.getElementById('filter_year').value : currentAcademicYear;
        var type = document.getElementById('filter_type') ? document.getElementById('filter_type').value : currentEventType;

        var url = '<?php echo site_url('academics/calendar'); ?>?academic_year_id=' + yr +
                  '&event_type=' + encodeURIComponent(type) +
                  '&month=' + selMonth +
                  '&year=' + selYear +
                  '&view_mode=grid';
        window.location.href = url;
      }

      function setViewMode(mode) {
        currentViewMode = mode;
        try {
          localStorage.setItem('academic_calendar_view_mode', mode);
        } catch (e) {}

        var listV = document.getElementById('view-list');
        var gridV = document.getElementById('view-grid');
        var btnList = document.getElementById('btn-view-list');
        var btnGrid = document.getElementById('btn-view-grid');
        var redirectViewInput = document.getElementById('modal_redirect_view');
        if (redirectViewInput) {
          redirectViewInput.value = mode;
        }

        if (mode === 'grid') {
          listV.classList.add('hidden');
          gridV.classList.remove('hidden');
          btnGrid.className = "px-3.5 py-1.5 rounded-md text-label-md transition-all flex items-center gap-1.5 cursor-pointer bg-surface-container-lowest text-primary shadow-xs font-semibold";
          btnList.className = "px-3.5 py-1.5 rounded-md text-label-md transition-all flex items-center gap-1.5 cursor-pointer text-on-surface-variant hover:text-on-surface font-medium";
        } else {
          gridV.classList.add('hidden');
          listV.classList.remove('hidden');
          btnList.className = "px-3.5 py-1.5 rounded-md text-label-md transition-all flex items-center gap-1.5 cursor-pointer bg-surface-container-lowest text-primary shadow-xs font-semibold";
          btnGrid.className = "px-3.5 py-1.5 rounded-md text-label-md transition-all flex items-center gap-1.5 cursor-pointer text-on-surface-variant hover:text-on-surface font-medium";
        }
      }

      function openAddEventModal() {
        document.getElementById('event_action').value = 'add';
        document.getElementById('modal_calendar_id').value = '';
        document.getElementById('modal-event-title').innerText = 'Add Calendar Event / Holiday';
        document.getElementById('modal_event_title_input').value = '';
        document.getElementById('modal_event_type').value = 'Holiday';
        document.getElementById('modal_event_audience').value = 'Whole School';
        document.getElementById('modal_event_start_date').value = '<?php echo date('Y-m-d'); ?>';
        document.getElementById('modal_event_end_date').value = '<?php echo date('Y-m-d'); ?>';
        var activeYr = window.ACTIVE_ACADEMIC_YEAR_ID || window.CURRENT_ACADEMIC_YEAR_ID;
        if (activeYr && document.getElementById('modal_event_year')) {
          document.getElementById('modal_event_year').value = activeYr;
        }
        document.getElementById('modal_event_venue').value = '';
        document.getElementById('modal_event_description').value = '';
        document.getElementById('modal-event').classList.remove('hidden');
      }

      function openEditEventModal(id) {
        fetch('<?php echo site_url('academics/ajax_get_calendar_event/'); ?>' + id)
          .then(res => res.json())
          .then(data => {
            if (data.success && data.event) {
              var ev = data.event;
              document.getElementById('event_action').value = 'edit';
              document.getElementById('modal_calendar_id').value = ev.calendar_id;
              document.getElementById('modal-event-title').innerText = 'Edit Calendar Event / Holiday';
              document.getElementById('modal_event_year').value = ev.academic_year_id;
              document.getElementById('modal_event_title_input').value = ev.title;
              document.getElementById('modal_event_type').value = ev.event_type;
              document.getElementById('modal_event_audience').value = ev.audience;
              document.getElementById('modal_event_start_date').value = ev.start_date;
              document.getElementById('modal_event_end_date').value = ev.end_date || ev.start_date;
              document.getElementById('modal_event_venue').value = ev.venue || '';
              document.getElementById('modal_event_description').value = ev.description || '';
              document.getElementById('modal-event').classList.remove('hidden');
            }
          });
      }

      // Initialize view mode from URL param or localStorage
      document.addEventListener('DOMContentLoaded', function() {
        var preferredMode = '<?php echo html_escape($view_mode ?: ""); ?>';
        if (!preferredMode) {
          try {
            preferredMode = localStorage.getItem('academic_calendar_view_mode');
          } catch (e) {}
        }
        if (preferredMode === 'grid') {
          setViewMode('grid');
        } else {
          setViewMode('list');
        }
      });
    </script>
