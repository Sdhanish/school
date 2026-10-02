<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('success')): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-secondary-container text-on-secondary-container text-body-md font-medium flex items-center gap-2 border border-secondary/20">
        <span class="material-symbols-outlined text-[20px] text-secondary">check_circle</span>
        <?php echo html_escape($this->session->flashdata('success')); ?>
      </div>
    <?php endif; ?>

    <!-- Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Academic Management Overview</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Curriculum structure, active school calendar, classes, divisions, and faculty allocations.</p>
      </div>
      <div class="flex items-center gap-2.5 flex-wrap shrink-0">
        <a href="<?php echo site_url('academics/classes'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_box</span>Manage Classes
        </a>
        <a href="<?php echo site_url('academics/years'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">calendar_today</span>Academic Years
        </a>
      </div>
    </div>

    <!-- Top Stats Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
      <!-- Active Academic Year -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Current Session</span>
          <span class="material-symbols-outlined text-primary text-[22px]">calendar_month</span>
        </div>
        <div class="text-headline-md font-bold text-on-surface truncate"><?php echo html_escape($active_year->year_name ?? '2025-2026'); ?></div>
        <div class="text-xs text-on-surface-variant flex items-center gap-1">
          <span class="font-semibold text-secondary">Active Academic Year</span>
        </div>
      </div>

      <!-- Classes & Divisions -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Classes & Divisions</span>
          <span class="material-symbols-outlined text-secondary text-[22px]">class</span>
        </div>
        <div class="text-headline-md font-bold text-secondary"><?php echo (int)$total_classes; ?> Classes</div>
        <div class="text-xs text-on-surface-variant">
          <span><?php echo (int)($total_divisions ?? $total_sections); ?> active divisions</span>
        </div>
      </div>

      <!-- Subjects -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Subjects</span>
          <span class="material-symbols-outlined text-blue-600 text-[22px]">menu_book</span>
        </div>
        <div class="text-headline-md font-bold text-on-surface"><?php echo (int)$total_subjects; ?></div>
        <div class="text-xs text-on-surface-variant">
          <span>Curriculum subjects</span>
        </div>
      </div>

      <!-- Class Teachers Assigned -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Class In-Charges</span>
          <span class="material-symbols-outlined text-purple-600 text-[22px]">supervisor_account</span>
        </div>
        <div class="text-headline-md font-bold text-on-surface"><?php echo (int)$assigned_teachers; ?></div>
        <div class="text-xs text-on-surface-variant">
          <span>Assigned class mentors</span>
        </div>
      </div>
    </div>

    <!-- Quick Shortcuts Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
      <a href="<?php echo site_url('academics/classes'); ?>" class="p-3.5 rounded-xl bg-surface-container-lowest border border-outline-variant/50 hover:bg-surface-container-high transition-all flex items-center gap-3 group shadow-xs">
        <div class="w-9 h-9 rounded-lg bg-primary-container text-on-primary-container flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-[20px]">class</span>
        </div>
        <div class="min-w-0">
          <div class="text-xs font-bold text-on-surface group-hover:text-primary">Classes & Divisions</div>
          <div class="text-[11px] text-on-surface-variant truncate">Configure grades & divisions</div>
        </div>
      </a>
      <?php if (!isset($can_view_academic_groups) || $can_view_academic_groups): ?>
      <a href="<?php echo site_url('academics/academic_groups'); ?>" class="p-3.5 rounded-xl bg-surface-container-lowest border border-outline-variant/50 hover:bg-surface-container-high transition-all flex items-center gap-3 group shadow-xs">
        <div class="w-9 h-9 rounded-lg bg-secondary-container text-on-secondary-container flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-[20px]">category</span>
        </div>
        <div class="min-w-0">
          <div class="text-xs font-bold text-on-surface group-hover:text-secondary">Department / Groups</div>
          <div class="text-[11px] text-on-surface-variant truncate">Organize students and subjects into department / groups</div>
        </div>
      </a>
      <?php endif; ?>
      <a href="<?php echo site_url('academics/subjects'); ?>" class="p-3.5 rounded-xl bg-surface-container-lowest border border-outline-variant/50 hover:bg-surface-container-high transition-all flex items-center gap-3 group shadow-xs">
        <div class="w-9 h-9 rounded-lg bg-tertiary-container text-on-tertiary-container flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-[20px]">menu_book</span>
        </div>
        <div class="min-w-0">
          <div class="text-xs font-bold text-on-surface group-hover:text-tertiary">Subjects</div>
          <div class="text-[11px] text-on-surface-variant truncate">Manage subjects &amp; teachers</div>
        </div>
      </a>
      <a href="<?php echo site_url('academics/calendar'); ?>" class="p-3.5 rounded-xl bg-surface-container-lowest border border-outline-variant/50 hover:bg-surface-container-high transition-all flex items-center gap-3 group shadow-xs">
        <div class="w-9 h-9 rounded-lg bg-secondary-container text-on-secondary-container flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-[20px]">event_available</span>
        </div>
        <div class="min-w-0">
          <div class="text-xs font-bold text-on-surface group-hover:text-secondary">Academic Calendar</div>
          <div class="text-[11px] text-on-surface-variant truncate">Holidays & working days</div>
        </div>
      </a>
    </div>

    <!-- Main Two Column Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
      <!-- Left 2 Cols: Class Hierarchy & Division Breakdown -->
      <div class="lg:col-span-2 elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-5 space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px]">table_chart</span>Class & Division Configuration
          </h3>
          <a href="<?php echo site_url('academics/classes'); ?>" class="text-xs font-semibold text-primary hover:underline">Manage All Classes</a>
        </div>

        <?php if (!empty($classes_summary)): ?>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <?php foreach ($classes_summary as $c): ?>
              <div class="p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/30 flex items-center justify-between">
                <div>
                  <h4 class="font-bold text-xs text-on-surface"><?php echo html_escape($c->class_name); ?></h4>
                  <p class="text-[11px] text-on-surface-variant mt-0.5"><?php echo (int)($c->division_count ?? $c->section_count ?? 0); ?> Divisions • <?php echo (int)($c->student_count ?? 0); ?> Students</p>
                </div>
                <div class="text-right">
                  <a href="<?php echo site_url('academics/divisions?class_id=' . $c->class_id); ?>" class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-surface-container-high text-on-surface hover:bg-surface-container-highest">
                    Divisions
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="text-center py-8 text-on-surface-variant text-xs">No classes configured yet.</div>
        <?php endif; ?>
      </div>

      <!-- Right 1 Col: Academic Calendar Highlights -->
      <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-5 space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-[20px]">event_note</span>Calendar Highlights
          </h3>
          <a href="<?php echo site_url('academics/calendar'); ?>" class="text-xs font-semibold text-secondary hover:underline">Full Calendar</a>
        </div>

        <?php if (!empty($calendar_events)): ?>
          <div class="space-y-3">
            <?php foreach ($calendar_events as $ev): ?>
              <div class="p-3 rounded-xl bg-surface-container-low border border-outline-variant/30 flex items-center justify-between">
                <div class="min-w-0 pr-2">
                  <div class="font-bold text-xs text-on-surface truncate"><?php echo html_escape($ev->title); ?></div>
                  <div class="text-[11px] text-on-surface-variant flex items-center gap-1 font-mono">
                    <span><?php echo date('M d, Y', strtotime($ev->start_date)); ?></span> • <span><?php echo html_escape($ev->event_type ?? 'Event'); ?></span>
                  </div>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-primary-container text-on-primary-container">
                  <?php echo html_escape($ev->event_type ?? 'Event'); ?>
                </span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="text-center py-8 text-on-surface-variant text-xs">No scheduled calendar events.</div>
        <?php endif; ?>
      </div>
    </div>
