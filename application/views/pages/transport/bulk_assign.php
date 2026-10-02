<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('error')): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-error-container text-on-error-container text-body-md font-medium flex items-center gap-2 border border-error/20">
        <span class="material-symbols-outlined text-[20px] text-error">error</span>
        <?php echo html_escape($this->session->flashdata('error')); ?>
      </div>
    <?php endif; ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Bulk Student Transport Assignment</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Batch allocate entire classrooms or student cohorts to a bus route and pickup stop with capacity checks.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('transport/assignments'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>Back to Allocations
        </a>
      </div>
    </div>

    <!-- 1. Class Selection Filter -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('transport/bulk_assign'); ?>" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Select Class *</label>
          <select name="class_id" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">-- Choose Class --</option>
            <?php foreach ($classes as $c): ?>
              <option value="<?php echo $c->class_id; ?>" <?php echo (($class_id ?? '') == $c->class_id) ? 'selected' : ''; ?>><?php echo html_escape($c->class_name); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <button type="submit" class="w-full py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant cursor-pointer">
            Load Students
          </button>
        </div>
      </form>
    </div>

    <?php if (!empty($students)): ?>
      <!-- 2. Assignment Form -->
      <?php echo form_open('transport/bulk_assign'); ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
          
          <!-- Left 2 Cols: Students Checkbox List -->
          <div class="lg:col-span-2 elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
            <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
              <span class="text-body-md font-semibold text-on-surface">Select Students (<?php echo count($students); ?> Available)</span>
              <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-primary">
                <input type="checkbox" onchange="toggleSelectAll(this.checked)" class="w-4 h-4 rounded text-primary focus:ring-primary"/>
                <span>Select All</span>
              </label>
            </div>

            <div class="divide-y divide-outline-variant/40 max-h-96 overflow-y-auto">
              <?php foreach ($students as $st): ?>
                <label class="p-3.5 flex items-center justify-between gap-3 hover:bg-surface-container-low cursor-pointer transition-colors">
                  <div class="flex items-center gap-3">
                    <input type="checkbox" name="student_ids[]" value="<?php echo $st->student_id; ?>" class="student-chk w-4 h-4 rounded text-secondary focus:ring-secondary"/>
                    <div>
                      <strong class="text-on-surface text-body-md block"><?php echo html_escape($st->first_name . ' ' . $st->last_name); ?></strong>
                      <span class="text-xs text-on-surface-variant font-mono"><?php echo html_escape($st->admission_number ?? $st->admission_no ?? ''); ?></span>
                    </div>
                  </div>
                  <span class="text-xs text-on-surface-variant"><?php echo html_escape($st->guardian_name); ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Right 1 Col: Destination & Target Vehicle -->
          <div class="space-y-6">
            <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
              <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2 pb-2 border-b border-outline-variant/50">
                <span class="material-symbols-outlined text-primary text-[22px]">alt_route</span>Transport Route
              </h3>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Select Route *</label>
                <select name="route_id" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <?php foreach ($routes as $r): ?>
                    <option value="<?php echo $r->route_id; ?>"><?php echo html_escape($r->route_name); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Select Vehicle *</label>
                <select name="vehicle_id" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <?php foreach ($vehicles as $v): ?>
                    <option value="<?php echo $v->vehicle_id; ?>"><?php echo html_escape($v->vehicle_number . ' (' . $v->available_seats . ' seats free)'); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Pickup Stop *</label>
                <select name="pickup_stop_id" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <?php foreach ($stops as $s): ?>
                    <option value="<?php echo $s->stop_id; ?>"><?php echo html_escape($s->stop_name . ' (' . $s->route_name . ')'); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Monthly Fee (₹)</label>
                <input type="number" step="50" name="monthly_fee" value="1500" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>

              <div class="pt-2">
                <button type="submit" onclick="return confirm('Confirm bulk transport allocation for selected students?');" class="w-full py-3 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer flex items-center justify-center gap-2">
                  <span class="material-symbols-outlined text-[18px]">group_add</span>Apply Bulk Allocation
                </button>
              </div>
            </div>
          </div>
        </div>
      <?php echo form_close(); ?>

      <script>
        function toggleSelectAll(checked) {
          document.querySelectorAll('.student-chk').forEach(el => el.checked = checked);
        }
      </script>
    <?php endif; ?>
