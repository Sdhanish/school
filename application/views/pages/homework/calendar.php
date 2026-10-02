<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Homework & Assignment Calendar</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Visual deadline calendar displaying active assignments, submission deadlines, and upcoming tasks.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('homework/create'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Create Assignment
        </a>
      </div>
    </div>

    <!-- Calendar Legend -->
    <div class="flex items-center gap-4 flex-wrap mb-4 text-xs font-semibold text-on-surface-variant">
      <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-secondary"></span><span>Upcoming Deadline</span></div>
      <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-500"></span><span>Due Today</span></div>
      <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-error"></span><span>Overdue</span></div>
    </div>

    <!-- CALENDAR GRID -->
    <?php
      $currentYear = (int)date('Y');
      $currentMonth = (int)date('m');
      $monthName = date('F Y');
      $firstDayOfMonth = date('N', strtotime("{$currentYear}-{$currentMonth}-01")); // 1 (Mon) to 7 (Sun)
      $daysInMonth = (int)date('t', strtotime("{$currentYear}-{$currentMonth}-01"));
      
      // Index assignments by due_date
      $asgn_by_date = [];
      foreach ($assignments as $a) {
          $d = date('Y-m-d', strtotime($a->due_date));
          if (!isset($asgn_by_date[$d])) $asgn_by_date[$d] = [];
          $asgn_by_date[$d][] = $a;
      }
    ?>

    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 bg-surface-container-low border-b border-outline-variant/50 flex items-center justify-between">
        <h3 class="font-headline-md text-title-lg font-bold text-on-surface"><?php echo $monthName; ?></h3>
        <span class="text-[12px] font-mono text-on-surface-variant">Active Assignments Calendar</span>
      </div>

      <div class="grid grid-cols-7 border-b border-outline-variant/40 bg-surface-container-low/60 text-center text-label-md font-bold text-on-surface-variant py-2">
        <div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div><div>Sun</div>
      </div>

      <div class="grid grid-cols-7 divide-x divide-y divide-outline-variant/30 text-body-md">
        <!-- Offset empty days -->
        <?php for ($i = 1; $i < $firstDayOfMonth; $i++): ?>
          <div class="min-h-[110px] p-2 bg-surface-container-low/20"></div>
        <?php endfor; ?>

        <!-- Month Days -->
        <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
          <?php
            $dateStr = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $day);
            $isToday = ($dateStr === date('Y-m-d'));
            $dayAsgns = $asgn_by_date[$dateStr] ?? [];
          ?>
          <div class="min-h-[110px] p-2 <?php echo $isToday ? 'bg-primary-container/10 border-primary' : 'bg-surface-container-lowest'; ?> flex flex-col justify-between hover:bg-surface-container-low/40 transition-colors">
            <div class="flex items-center justify-between mb-1">
              <span class="font-mono text-xs font-bold <?php echo $isToday ? 'w-6 h-6 rounded-full bg-primary text-on-primary flex items-center justify-center' : 'text-on-surface'; ?>">
                <?php echo $day; ?>
              </span>
              <?php if (!empty($dayAsgns)): ?>
                <span class="text-[10px] font-mono font-bold text-primary"><?php echo count($dayAsgns); ?> due</span>
              <?php endif; ?>
            </div>

            <!-- Assignments on this day -->
            <div class="space-y-1 overflow-y-auto max-h-24 pr-0.5">
              <?php foreach ($dayAsgns as $da): ?>
                <?php
                  $isOverdue = (strtotime($da->due_date) < strtotime(date('Y-m-d')));
                  $badgeBg = $isToday ? 'bg-amber-100 text-amber-900 border-amber-300' : ($isOverdue ? 'bg-error-container text-on-error-container border-error/30' : 'bg-secondary-container text-on-secondary-container border-secondary/30');
                ?>
                <a href="<?php echo site_url('homework/details/' . $da->assignment_id); ?>" class="block p-1.5 rounded-lg border <?php echo $badgeBg; ?> text-[11px] leading-tight hover:opacity-90 transition-opacity">
                  <div class="font-bold line-clamp-1"><?php echo html_escape($da->title); ?></div>
                  <div class="text-[10px] opacity-80 line-clamp-1"><?php echo html_escape($da->subject_name . ' (' . $da->class_name . ')'); ?></div>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endfor; ?>
      </div>
    </div>
