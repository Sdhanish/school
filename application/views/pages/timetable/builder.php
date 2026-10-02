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
        <h2 class="font-headline-md text-headline-md text-on-surface">Interactive Timetable Builder</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Design and schedule class periods in real time with automated teacher collision and class clash prevention.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('timetable/classes?academic_year_id=' . $selected_year . '&class_id=' . $selected_class . '&division_id=' . ($selected_division ?? $selected_section)); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">view_timeline</span>View Timetable
        </a>
      </div>
    </div>

    <!-- Filter & Class Selector -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('timetable/builder'); ?>" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
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
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Target Class</label>
          <select name="class_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <?php foreach ($classes as $c): ?>
              <option value="<?php echo $c->class_id; ?>" <?php echo ($selected_class == $c->class_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($c->class_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Target Division</label>
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

    <!-- BUILDER GRID -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Weekly Timetable Grid Layout</span>
        <span class="text-[12px] text-on-surface-variant">Click on any slot to assign or replace schedule</span>
      </div>

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
              <tr class="hover:bg-surface-container-low/40 transition-colors">
                <td class="px-4 py-4 font-bold text-on-surface whitespace-nowrap bg-surface-container-low/30 sticky left-0 z-10 border-r border-outline-variant/40">
                  <?php echo html_escape($day); ?>
                </td>

                <?php foreach ($periods as $p): ?>
                  <?php $entry = $matrix[$day][$p->period_id] ?? null; ?>
                  <td class="p-2 border-l border-outline-variant/40 align-top text-center">
                    <?php if ($entry): ?>
                      <div class="p-2.5 rounded-xl bg-surface-container border border-outline-variant/60 space-y-1 relative group hover:border-primary transition-all">
                        <div class="font-bold text-primary text-[13px] line-clamp-1"><?php echo html_escape($entry->subject_name); ?></div>
                        <div class="text-[12px] text-on-surface line-clamp-1"><?php echo html_escape($entry->teacher_name); ?></div>
                        <?php if ($entry->room_no): ?>
                          <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-mono bg-surface-container-high text-on-surface-variant">
                            <?php echo html_escape($entry->room_no); ?>
                          </span>
                        <?php endif; ?>

                        <div class="flex items-center justify-center gap-1 pt-1">
                          <button type="button" onclick='openBuilderModal("<?php echo $day; ?>", <?php echo $p->period_id; ?>, <?php echo json_encode($entry); ?>)' class="p-1 rounded text-primary hover:bg-primary hover:text-white cursor-pointer" title="Edit">
                            <span class="material-symbols-outlined text-[15px]">edit</span>
                          </button>
                          <a href="<?php echo site_url('timetable/delete_slot/' . $entry->timetable_id); ?>" onclick="return confirm('Clear this slot?');" class="p-1 rounded text-error hover:bg-error-container" title="Delete">
                            <span class="material-symbols-outlined text-[15px]">close</span>
                          </a>
                        </div>
                      </div>
                    <?php else: ?>
                      <button type="button" onclick='openBuilderModal("<?php echo $day; ?>", <?php echo $p->period_id; ?>)' class="w-full h-20 rounded-xl border-2 border-dashed border-outline-variant/50 hover:border-secondary hover:bg-secondary-container/10 text-on-surface-variant hover:text-secondary transition-all flex flex-col items-center justify-center gap-1 cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">add_circle</span>
                        <span class="text-[11px] font-semibold">Assign</span>
                      </button>
                    <?php endif; ?>
                  </td>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- QUICK BUILDER MODAL -->
    <div id="builder-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
      <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant max-w-md w-full p-6 elevation-3 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 id="modal-builder-title" class="font-headline-md text-title-lg text-on-surface font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-[22px]">calendar_add_on</span>Schedule Period Slot
          </h3>
          <button onclick="closeBuilderModal()" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('timetable/builder?academic_year_id=' . $selected_year . '&class_id=' . $selected_class . '&division_id=' . ($selected_division ?? $selected_section), array('class' => 'space-y-4')); ?>
          <input type="hidden" name="timetable_id" id="b-tt-id" value="0"/>
          <input type="hidden" name="day" id="b-tt-day" value=""/>
          <input type="hidden" name="period_id" id="b-tt-period" value=""/>

          <div class="p-3 rounded-xl bg-surface-container-low border border-outline-variant/50 text-body-md text-on-surface flex items-center justify-between">
            <div>
              <span class="text-[11px] text-on-surface-variant uppercase font-semibold block">Target Slot</span>
              <strong id="b-slot-label" class="text-primary font-mono text-[14px]">Monday - Period 1</strong>
            </div>
            <span class="text-xs text-on-surface-variant font-medium">Class <?php foreach ($classes as $c) if ($c->class_id == $selected_class) echo html_escape($c->class_name); ?></span>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Subject *</label>
            <select name="subject_id" id="b-tt-subject" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
              <?php if (empty($subjects)): ?>
                <option value="">-- No subjects assigned to this class --</option>
              <?php else: ?>
                <option value="">-- Choose Subject --</option>
                <?php foreach ($subjects as $sub): ?>
                  <option value="<?php echo $sub->subject_id; ?>"><?php echo html_escape($sub->subject_name . ' (' . $sub->subject_code . ')'); ?></option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
            <?php if (empty($subjects)): ?>
              <p class="mt-1 text-[12px] text-error flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">warning</span>
                No subjects allocated for this class. Go to
                <a href="<?php echo site_url('timetable/allocations?academic_year_id=' . $selected_year . '&class_id=' . $selected_class . '&division_id=' . ($selected_division ?? $selected_section)); ?>" class="underline text-primary ml-1">Subject Allocation</a> first.
              </p>
            <?php endif; ?>
          </div>


          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Teacher / Faculty *</label>
            <select name="teacher_id" id="b-tt-teacher" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
              <option value="">-- Choose Teacher --</option>
              <?php foreach ($teachers as $t): ?>
                <option value="<?php echo $t->staff_id; ?>"><?php echo html_escape($t->full_name); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Room / Laboratory</label>
            <input type="text" name="room_no" id="b-tt-room" placeholder="Optional room tag" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-outline-variant/50">
            <button type="button" onclick="closeBuilderModal()" class="px-4 py-2 rounded-lg bg-surface-container-high text-on-surface text-label-md font-medium hover:bg-surface-container-highest cursor-pointer">
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
      function openBuilderModal(day, periodId, entry) {
        document.getElementById('b-tt-day').value = day;
        document.getElementById('b-tt-period').value = periodId;
        document.getElementById('b-slot-label').textContent = day + ' — Period #' + periodId;

        if (entry) {
          document.getElementById('b-tt-id').value = entry.timetable_id;
          document.getElementById('b-tt-subject').value = entry.subject_id;
          document.getElementById('b-tt-teacher').value = entry.teacher_id;
          document.getElementById('b-tt-room').value = entry.room_no || '';
        } else {
          document.getElementById('b-tt-id').value = '0';
          document.getElementById('b-tt-subject').value = '';
          document.getElementById('b-tt-teacher').value = '';
          document.getElementById('b-tt-room').value = '';
        }

        var modal = document.getElementById('builder-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
      }

      function closeBuilderModal() {
        var modal = document.getElementById('builder-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      }
    </script>
