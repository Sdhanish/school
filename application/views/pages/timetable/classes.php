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
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 print:hidden">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Class Timetable Matrix</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Configure class period schedules, assign teaching faculty, and review weekly class allocations.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <?php if (!$is_locked): ?>
          <button type="button" onclick="openScheduleModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">add_circle</span>Assign Period
          </button>
        <?php else: ?>
          <span class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-surface-container-high text-on-surface font-semibold text-label-md">
            <span class="material-symbols-outlined text-[18px] text-primary">lock</span>Schedule Locked
          </span>
        <?php endif; ?>
        <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">print</span>Print Timetable
        </button>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6 print:hidden">
      <form method="get" action="<?php echo site_url('timetable/classes'); ?>" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
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
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Class</label>
          <select name="class_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <?php foreach ($classes as $c): ?>
              <option value="<?php echo $c->class_id; ?>" <?php echo ($selected_class == $c->class_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($c->class_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Division</label>
          <select name="division_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <?php foreach ($divisions as $s): ?>
              <option value="<?php echo $s->division_id; ?>" <?php echo (($selected_division ?? $selected_section) == $s->division_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($s->division_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </form>
    </div>

    <!-- Print Header (Visible only on print) -->
    <div class="hidden print:block text-center pb-4 mb-4 border-b border-outline-variant">
      <h1 class="text-2xl font-bold text-on-surface uppercase">Login2</h1>
      <h2 class="text-lg font-semibold text-primary mt-1">
        Class Timetable — <?php 
          foreach ($classes as $c) if ($c->class_id == $selected_class) echo html_escape($c->class_name);
          foreach ($divisions as $s) if ($s->division_id == ($selected_division ?? $selected_section)) echo ' ' . html_escape($s->division_name);
        ?>
      </h2>
    </div>

    <!-- TIMETABLE GRID MATRIX -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table border-collapse text-body-md">
          <thead>
            <tr class="border-b-2 border-outline-variant bg-surface-container-low">
              <th class="text-left px-4 py-3 text-label-md font-bold text-on-surface-variant uppercase w-28 sticky left-0 bg-surface-container-low z-10">Day / Period</th>
              <?php foreach ($periods as $p): ?>
                <th class="text-center px-3 py-3 text-label-md font-semibold text-on-surface-variant uppercase border-l border-outline-variant/40 min-w-[150px]">
                  <span class="block text-primary font-bold"><?php echo html_escape($p->period_name); ?></span>
                  <span class="text-[11px] font-mono text-on-surface-variant font-normal block mt-0.5">
                    <?php echo date('h:i A', strtotime($p->start_time)) . ' - ' . date('h:i A', strtotime($p->end_time)); ?>
                  </span>
                </th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php foreach ($working_days as $day): ?>
              <tr class="hover:bg-surface-container-low/50 transition-colors">
                <!-- Day Header Column -->
                <td class="px-4 py-4 font-bold text-on-surface whitespace-nowrap bg-surface-container-low/30 sticky left-0 z-10 border-r border-outline-variant/40">
                  <?php echo html_escape($day); ?>
                </td>

                <!-- Period Columns -->
                <?php foreach ($periods as $p): ?>
                  <?php $entry = $matrix[$day][$p->period_id] ?? null; ?>
                  <td class="p-2 border-l border-outline-variant/40 align-top text-center">
                    <?php if ($entry): ?>
                      <div class="p-2.5 rounded-xl bg-surface-container border border-outline-variant/60 space-y-1 group relative transition-all hover:elevation-1 hover:border-primary/40">
                        <div class="font-bold text-primary text-[13px] line-clamp-1" title="<?php echo html_escape($entry->subject_name); ?>">
                          <?php echo html_escape($entry->subject_name); ?>
                        </div>
                        <div class="text-[12px] font-medium text-on-surface line-clamp-1" title="<?php echo html_escape($entry->teacher_name); ?>">
                          <?php echo html_escape($entry->teacher_name); ?>
                        </div>
                        <?php if ($entry->room_no): ?>
                          <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-mono bg-surface-container-high text-on-surface-variant">
                            Room: <?php echo html_escape($entry->room_no); ?>
                          </span>
                        <?php endif; ?>

                        <!-- Quick Actions on Hover (Hidden on print) -->
                        <?php if (!$is_locked): ?>
                          <div class="print:hidden flex items-center justify-center gap-1 pt-1 opacity-80 group-hover:opacity-100 transition-opacity">
                            <button type="button" onclick='editSlot(<?php echo json_encode($entry); ?>)' class="p-1 rounded text-primary hover:bg-primary hover:text-white transition-colors cursor-pointer" title="Edit">
                              <span class="material-symbols-outlined text-[15px]">edit</span>
                            </button>
                            <a href="<?php echo site_url('timetable/delete_slot/' . $entry->timetable_id); ?>" onclick="return confirm('Remove this timetable slot?');" class="p-1 rounded text-error hover:bg-error-container transition-colors" title="Delete">
                              <span class="material-symbols-outlined text-[15px]">delete</span>
                            </a>
                          </div>
                        <?php endif; ?>
                      </div>
                    <?php else: ?>
                      <?php if (!$is_locked): ?>
                        <button type="button" onclick="openScheduleModal('<?php echo $day; ?>', <?php echo $p->period_id; ?>)" class="print:hidden w-full h-20 rounded-xl border border-dashed border-outline-variant/60 hover:border-primary/60 hover:bg-primary-fixed/20 text-on-surface-variant/60 hover:text-primary transition-all flex flex-col items-center justify-center gap-1 text-[11px] font-semibold cursor-pointer">
                          <span class="material-symbols-outlined text-[18px]">add</span>Assign
                        </button>
                      <?php else: ?>
                        <div class="h-16 flex items-center justify-center text-on-surface-variant/40 text-[12px] italic">— Free —</div>
                      <?php endif; ?>
                    <?php endif; ?>
                  </td>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- SCHEDULE SLOT MODAL -->
    <div id="schedule-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
      <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant max-w-md w-full p-6 elevation-3 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 id="modal-tt-title" class="font-headline-md text-title-lg text-on-surface font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[22px]">calendar_add_on</span>Assign Period Slot
          </h3>
          <button onclick="closeScheduleModal()" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('timetable/classes?academic_year_id=' . $selected_year . '&class_id=' . $selected_class . '&division_id=' . ($selected_division ?? $selected_section), array('class' => 'space-y-4')); ?>
          <input type="hidden" name="timetable_id" id="modal-tt-id" value="0"/>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Day of Week *</label>
              <select name="day" id="modal-tt-day" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <?php foreach ($working_days as $wd): ?>
                  <option value="<?php echo $wd; ?>"><?php echo $wd; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Period *</label>
              <select name="period_id" id="modal-tt-period" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <?php foreach ($periods as $p): ?>
                  <option value="<?php echo $p->period_id; ?>"><?php echo html_escape($p->period_name . ' (' . date('h:i A', strtotime($p->start_time)) . ')'); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Subject *</label>
            <select name="subject_id" id="modal-tt-subject" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
              <option value="">-- Choose Subject --</option>
              <?php foreach ($subjects as $sub): ?>
                <option value="<?php echo $sub->subject_id; ?>"><?php echo html_escape($sub->subject_name . ' (' . $sub->subject_code . ')'); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Teacher / Faculty *</label>
            <select name="teacher_id" id="modal-tt-teacher" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
              <option value="">-- Choose Teacher --</option>
              <?php foreach ($teachers as $t): ?>
                <option value="<?php echo $t->staff_id; ?>"><?php echo html_escape($t->full_name . ' (' . $t->employee_code . ')'); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Classroom / Lab (Optional)</label>
            <input type="text" name="room_no" id="modal-tt-room" placeholder="e.g. Room 204, Science Lab 2" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-outline-variant/50">
            <button type="button" onclick="closeScheduleModal()" class="px-4 py-2 rounded-lg bg-surface-container-high text-on-surface text-label-md font-medium hover:bg-surface-container-highest cursor-pointer">
              Cancel
            </button>
            <button type="submit" class="px-5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant cursor-pointer">
              Save Slot
            </button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <script>
      function openScheduleModal(day, periodId) {
        document.getElementById('modal-tt-id').value = '0';
        document.getElementById('modal-tt-title').textContent = 'Assign Period Slot';
        if (day) document.getElementById('modal-tt-day').value = day;
        if (periodId) document.getElementById('modal-tt-period').value = periodId;
        document.getElementById('modal-tt-subject').value = '';
        document.getElementById('modal-tt-teacher').value = '';
        document.getElementById('modal-tt-room').value = '';

        var modal = document.getElementById('schedule-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
      }

      function editSlot(entry) {
        document.getElementById('modal-tt-id').value = entry.timetable_id;
        document.getElementById('modal-tt-title').textContent = 'Edit Period Slot';
        document.getElementById('modal-tt-day').value = entry.day;
        document.getElementById('modal-tt-period').value = entry.period_id;
        document.getElementById('modal-tt-subject').value = entry.subject_id;
        document.getElementById('modal-tt-teacher').value = entry.teacher_id;
        document.getElementById('modal-tt-room').value = entry.room_no || '';

        var modal = document.getElementById('schedule-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
      }

      function closeScheduleModal() {
        var modal = document.getElementById('schedule-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      }
    </script>
