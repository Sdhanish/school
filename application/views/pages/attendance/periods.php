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
        <h2 class="font-headline-md text-headline-md text-on-surface">Period Management</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Configure and manage school timetable periods, start/end timings, and active schedule blocks.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="openAddPeriodModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Add New Period
        </button>
      </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <div class="flex items-center justify-between mb-2">
          <span class="text-[12px] font-medium text-on-surface-variant uppercase tracking-wider">Total Periods</span>
          <span class="material-symbols-outlined text-[20px] text-primary">schedule</span>
        </div>
        <div class="text-2xl font-bold text-on-surface"><?php echo count($periods); ?></div>
        <div class="text-[11px] text-on-surface-variant mt-1">Configured in system</div>
      </div>

      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <div class="flex items-center justify-between mb-2">
          <span class="text-[12px] font-medium text-secondary uppercase tracking-wider">Active Periods</span>
          <span class="material-symbols-outlined text-[20px] text-secondary">check_circle</span>
        </div>
        <div class="text-2xl font-bold text-secondary">
          <?php echo count(array_filter($periods, function($p) { return $p->status == 1; })); ?>
        </div>
        <div class="text-[11px] text-on-surface-variant mt-1">Available for attendance & timetable</div>
      </div>

      <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1">
        <div class="flex items-center justify-between mb-2">
          <span class="text-[12px] font-medium text-amber-600 uppercase tracking-wider">Inactive Periods</span>
          <span class="material-symbols-outlined text-[20px] text-amber-600">pause_circle</span>
        </div>
        <div class="text-2xl font-bold text-amber-600">
          <?php echo count(array_filter($periods, function($p) { return $p->status == 0; })); ?>
        </div>
        <div class="text-[11px] text-on-surface-variant mt-1">Temporarily disabled</div>
      </div>
    </div>

    <!-- Periods Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase w-20">Period #</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Period Name</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Start Time</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">End Time</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Duration</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Status</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($periods)): ?>
              <tr><td colspan="7" class="px-4 py-8 text-center text-on-surface-variant text-body-md">No periods configured. Click "Add New Period" above to create one.</td></tr>
            <?php else: ?>
              <?php foreach ($periods as $p): ?>
                <?php
                  $sTime = strtotime($p->start_time);
                  $eTime = strtotime($p->end_time);
                  $durationMin = ($eTime && $sTime) ? round(($eTime - $sTime) / 60) : 0;
                ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 text-center font-mono font-bold text-primary whitespace-nowrap">
                    <span class="w-8 h-8 rounded-lg bg-primary-fixed/30 text-primary inline-flex items-center justify-center font-bold text-[13px]">
                      <?php echo html_escape($p->period_number); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 font-semibold text-on-surface whitespace-nowrap">
                    <?php echo html_escape($p->period_name); ?>
                  </td>
                  <td class="px-4 py-3 text-on-surface whitespace-nowrap">
                    <div class="flex items-center gap-1.5 font-mono text-[13px]">
                      <span class="material-symbols-outlined text-[16px] text-secondary">alarm</span>
                      <?php echo date('h:i A', $sTime); ?>
                    </div>
                  </td>
                  <td class="px-4 py-3 text-on-surface whitespace-nowrap">
                    <div class="flex items-center gap-1.5 font-mono text-[13px]">
                      <span class="material-symbols-outlined text-[16px] text-error">alarm_on</span>
                      <?php echo date('h:i A', $eTime); ?>
                    </div>
                  </td>
                  <td class="px-4 py-3 text-on-surface-variant whitespace-nowrap font-medium text-[13px]">
                    <?php echo $durationMin; ?> mins
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <?php if ($p->status == 1): ?>
                      <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-secondary-container text-on-secondary-container">
                        <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>Active
                      </span>
                    <?php else: ?>
                      <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-surface-container-high text-on-surface-variant">
                        <span class="w-1.5 h-1.5 rounded-full bg-outline"></span>Inactive
                      </span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <div class="flex items-center justify-center gap-1">
                      <!-- Edit Button -->
                      <button type="button" onclick='openEditPeriodModal(<?php echo json_encode($p); ?>)' class="p-1.5 rounded-lg hover:bg-surface-container-high text-primary transition-colors cursor-pointer" title="Edit Period">
                        <span class="material-symbols-outlined text-[18px]">edit</span>
                      </button>

                      <!-- Toggle Status Form -->
                      <?php echo form_open('attendance/periods', array('class' => 'inline')); ?>
                        <input type="hidden" name="action" value="toggle_status"/>
                        <input type="hidden" name="period_id" value="<?php echo $p->period_id; ?>"/>
                        <button type="submit" class="p-1.5 rounded-lg hover:bg-surface-container-high text-on-surface-variant transition-colors cursor-pointer" title="<?php echo ($p->status == 1) ? 'Deactivate Period' : 'Activate Period'; ?>">
                          <span class="material-symbols-outlined text-[18px]"><?php echo ($p->status == 1) ? 'toggle_on' : 'toggle_off'; ?></span>
                        </button>
                      <?php echo form_close(); ?>

                      <!-- Delete Form -->
                      <?php echo form_open('attendance/periods', array('class' => 'inline', 'onsubmit' => 'return confirm("Are you sure you want to delete this period?");')); ?>
                        <input type="hidden" name="action" value="delete"/>
                        <input type="hidden" name="period_id" value="<?php echo $p->period_id; ?>"/>
                        <button type="submit" class="p-1.5 rounded-lg hover:bg-error-container text-error transition-colors cursor-pointer" title="Delete Period">
                          <span class="material-symbols-outlined text-[18px]">delete</span>
                        </button>
                      <?php echo form_close(); ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ADD PERIOD MODAL -->
    <div id="add-period-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
      <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant max-w-md w-full p-6 elevation-3 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-lg text-on-surface font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[22px]">add_circle</span>Add School Period
          </h3>
          <button onclick="closeAddPeriodModal()" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('attendance/periods', array('id' => 'add-period-form', 'onsubmit' => 'return validatePeriodForm(this)')); ?>
          <input type="hidden" name="action" value="add"/>

          <div class="space-y-3.5">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Period Name *</label>
              <input type="text" name="period_name" placeholder="e.g. Period 1, Morning Assembly, Lunch" required class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Period Number *</label>
              <input type="number" name="period_number" min="1" max="20" placeholder="e.g. 1, 2, 3" required class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              <p class="text-[11px] text-on-surface-variant mt-0.5">Numeric sequence identifier for timetable ordering.</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Start Time *</label>
                <input type="time" name="start_time" required class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">End Time *</label>
                <input type="time" name="end_time" required class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Status</label>
              <select name="status" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
              </select>
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-outline-variant/50 mt-5">
            <button type="button" onclick="closeAddPeriodModal()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-label-md cursor-pointer">Cancel</button>
            <button type="submit" class="px-5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors cursor-pointer">Save Period</button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <!-- EDIT PERIOD MODAL -->
    <div id="edit-period-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
      <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant max-w-md w-full p-6 elevation-3 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-lg text-on-surface font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[22px]">edit</span>Edit Period Timings
          </h3>
          <button onclick="closeEditPeriodModal()" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('attendance/periods', array('id' => 'edit-period-form', 'onsubmit' => 'return validatePeriodForm(this)')); ?>
          <input type="hidden" name="action" value="edit"/>
          <input type="hidden" name="period_id" id="edit-period-id"/>

          <div class="space-y-3.5">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Period Name *</label>
              <input type="text" name="period_name" id="edit-period-name" required class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Period Number *</label>
              <input type="number" name="period_number" id="edit-period-number" min="1" max="20" required class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Start Time *</label>
                <input type="time" name="start_time" id="edit-start-time" required class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">End Time *</label>
                <input type="time" name="end_time" id="edit-end-time" required class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Status</label>
              <select name="status" id="edit-status" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
              </select>
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-outline-variant/50 mt-5">
            <button type="button" onclick="closeEditPeriodModal()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-label-md cursor-pointer">Cancel</button>
            <button type="submit" class="px-5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors cursor-pointer">Update Period</button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <!-- Modal Controls & Client-Side Validation Script -->
    <script>
      function openAddPeriodModal() {
        var modal = document.getElementById('add-period-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
      }

      function closeAddPeriodModal() {
        var modal = document.getElementById('add-period-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      }

      function openEditPeriodModal(period) {
        document.getElementById('edit-period-id').value = period.period_id;
        document.getElementById('edit-period-name').value = period.period_name;
        document.getElementById('edit-period-number').value = period.period_number;
        document.getElementById('edit-start-time').value = period.start_time.substring(0, 5);
        document.getElementById('edit-end-time').value = period.end_time.substring(0, 5);
        document.getElementById('edit-status').value = period.status;

        var modal = document.getElementById('edit-period-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
      }

      function closeEditPeriodModal() {
        var modal = document.getElementById('edit-period-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      }

      function validatePeriodForm(form) {
        var start = form.querySelector('[name="start_time"]').value;
        var end = form.querySelector('[name="end_time"]').value;
        if (start && end && start >= end) {
          alert('End Time must be later than Start Time.');
          return false;
        }
        return true;
      }
    </script>
