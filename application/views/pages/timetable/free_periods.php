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
        <h2 class="font-headline-md text-headline-md text-on-surface">Free Periods & Substitution Management</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Identify free faculty during specific periods and assign proxy teacher coverage for absent staff.</p>
      </div>
    </div>

    <!-- Date & Period Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('timetable/free_periods'); ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Target Date</label>
          <input type="date" name="date" value="<?php echo html_escape($selected_date); ?>" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Target Period</label>
          <select name="period_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <?php foreach ($periods as $p): ?>
              <option value="<?php echo $p->period_id; ?>" <?php echo ($selected_period == $p->period_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($p->period_name . ' (' . date('h:i A', strtotime($p->start_time)) . ' - ' . date('h:i A', strtotime($p->end_time)) . ')'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="flex items-end">
          <div class="p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/40 w-full text-[13px] text-on-surface flex items-center justify-between">
            <span>Day: <strong><?php echo $day_of_week; ?></strong></span>
            <span class="text-primary font-mono font-bold"><?php echo count($free_teachers); ?> Free Teachers</span>
          </div>
        </div>
      </form>
    </div>

    <!-- 2 Column Division: Free Teachers + Ongoing Classes -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      
      <!-- Free Faculty List -->
      <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-[22px]">person_check</span>Available Free Faculty (<?php echo count($free_teachers); ?>)
          </h3>
        </div>

        <div class="space-y-2.5 max-h-96 overflow-y-auto pr-1">
          <?php if (empty($free_teachers)): ?>
            <div class="p-4 text-center text-on-surface-variant text-body-md">All teaching faculty are scheduled during this period.</div>
          <?php else: ?>
            <?php foreach ($free_teachers as $ft): ?>
              <div class="p-3 rounded-xl bg-surface-container-low border border-outline-variant/40 flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-full bg-secondary-container text-on-secondary-container font-bold flex items-center justify-center text-sm">
                    <?php echo strtoupper(substr($ft->full_name, 0, 1)); ?>
                  </div>
                  <div>
                    <strong class="text-on-surface text-body-md block"><?php echo html_escape($ft->full_name); ?></strong>
                    <span class="text-[11px] text-on-surface-variant"><?php echo html_escape($ft->designation_name ?: 'Teacher'); ?> • <?php echo html_escape($ft->employee_code); ?></span>
                  </div>
                </div>
                <div class="text-right">
                  <span class="px-2 py-0.5 rounded text-[11px] font-mono bg-surface-container-high text-primary font-semibold block">
                    <?php echo $ft->classes_today; ?> classes today
                  </span>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Classes Scheduled During this Period (Available for Proxy Assignment) -->
      <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[22px]">class</span>Classes in this Period
          </h3>
        </div>

        <div class="space-y-2.5 max-h-96 overflow-y-auto pr-1">
          <?php if (empty($day_schedules)): ?>
            <div class="p-4 text-center text-on-surface-variant text-body-md">No classes scheduled during this period on <?php echo $day_of_week; ?>.</div>
          <?php else: ?>
            <?php foreach ($day_schedules as $ds): ?>
              <div class="p-3 rounded-xl bg-surface-container-low border border-outline-variant/40 flex items-center justify-between">
                <div>
                  <strong class="text-primary font-bold text-body-md block"><?php echo html_escape($ds->class_name . ' ' . ($ds->division_name ?: $ds->section_name)); ?> — <?php echo html_escape($ds->subject_name); ?></strong>
                  <span class="text-[12px] text-on-surface">Teacher: <strong><?php echo html_escape($ds->teacher_name); ?></strong></span>
                </div>
                <button type="button" onclick='openSubModal(<?php echo json_encode($ds); ?>)' class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-secondary text-on-secondary text-[12px] font-semibold hover:bg-on-secondary-fixed-variant transition-colors cursor-pointer shadow-sm">
                  <span class="material-symbols-outlined text-[15px]">swap_horiz</span>Assign Proxy
                </button>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Assigned Substitutions Log Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
          <span class="material-symbols-outlined text-primary text-[20px]">history</span>Proxy Substitutions on <?php echo date('d M Y', strtotime($selected_date)); ?> (<?php echo count($substitutions); ?>)
        </h3>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Period</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Class & Division</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Subject</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-error uppercase whitespace-nowrap">Absent / Regular Teacher</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-secondary uppercase whitespace-nowrap">Assigned Proxy Faculty</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Reason</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($substitutions)): ?>
              <tr><td colspan="7" class="px-4 py-6 text-center text-on-surface-variant">No teacher substitutions recorded for this date.</td></tr>
            <?php else: ?>
              <?php foreach ($substitutions as $sub): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 font-semibold text-primary whitespace-nowrap"><?php echo html_escape($sub->period_name); ?></td>
                  <td class="px-4 py-3 font-bold text-on-surface whitespace-nowrap"><?php echo html_escape($sub->class_name . ' ' . ($sub->division_name ?: $sub->section_name)); ?></td>
                  <td class="px-4 py-3 font-medium text-on-surface whitespace-nowrap"><?php echo html_escape($sub->subject_name); ?></td>
                  <td class="px-4 py-3 text-error whitespace-nowrap"><?php echo html_escape($sub->original_teacher); ?></td>
                  <td class="px-4 py-3 font-bold text-secondary whitespace-nowrap"><?php echo html_escape($sub->substitute_teacher); ?></td>
                  <td class="px-4 py-3 text-[13px] text-on-surface"><?php echo html_escape($sub->reason ?: 'Leave coverage'); ?></td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-secondary-container text-on-secondary-container">
                      <?php echo html_escape($sub->status); ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ASSIGN SUBSTITUTION MODAL -->
    <div id="sub-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
      <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant max-w-md w-full p-6 elevation-3 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-lg text-on-surface font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-[22px]">swap_horiz</span>Assign Proxy Teacher
          </h3>
          <button onclick="closeSubModal()" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('timetable/free_periods?date=' . $selected_date . '&period_id=' . $selected_period, array('class' => 'space-y-4')); ?>
          <input type="hidden" name="timetable_id" id="sub-tt-id" value="0"/>
          <input type="hidden" name="substitution_date" value="<?php echo html_escape($selected_date); ?>"/>
          <input type="hidden" name="original_teacher_id" id="sub-orig-id" value="0"/>

          <div class="p-3 rounded-xl bg-surface-container-low border border-outline-variant/50 space-y-1 text-body-md">
            <div class="flex justify-between"><span class="text-on-surface-variant">Class & Division:</span><strong id="sub-class-label" class="text-on-surface">Class 10 A</strong></div>
            <div class="flex justify-between"><span class="text-on-surface-variant">Subject:</span><strong id="sub-sub-label" class="text-primary">Mathematics</strong></div>
            <div class="flex justify-between"><span class="text-on-surface-variant">Regular Faculty:</span><span id="sub-orig-name" class="text-error font-medium">Teacher</span></div>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Select Proxy / Substitute Faculty *</label>
            <select name="substitute_teacher_id" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
              <option value="">-- Choose Free Teacher --</option>
              <?php foreach ($free_teachers as $ft): ?>
                <option value="<?php echo $ft->staff_id; ?>"><?php echo html_escape($ft->full_name . ' (' . $ft->classes_today . ' classes today)'); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Reason for Substitution</label>
            <input type="text" name="reason" placeholder="e.g. Medical leave, Training workshop" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-outline-variant/50">
            <button type="button" onclick="closeSubModal()" class="px-4 py-2 rounded-lg bg-surface-container-high text-on-surface text-label-md font-medium hover:bg-surface-container-highest cursor-pointer">
              Cancel
            </button>
            <button type="submit" class="px-5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant cursor-pointer">
              Confirm Substitution
            </button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <script>
      function openSubModal(item) {
        document.getElementById('sub-tt-id').value = item.timetable_id;
        document.getElementById('sub-orig-id').value = item.teacher_id;
        document.getElementById('sub-class-label').textContent = item.class_name + ' ' + (item.division_name || item.section_name);
        document.getElementById('sub-sub-label').textContent = item.subject_name;
        document.getElementById('sub-orig-name').textContent = item.teacher_name;

        var modal = document.getElementById('sub-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
      }

      function closeSubModal() {
        var modal = document.getElementById('sub-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      }
    </script>
