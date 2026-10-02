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
        <h2 class="font-headline-md text-headline-md text-on-surface">Student Transport Allocations</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Allocate students to bus routes and stops with automated seating capacity enforcement and fee assignments.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('transport/bulk_assign'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">group_add</span>Bulk Assign
        </a>
        <button onclick="document.getElementById('assignModal').showModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Allocate Student
        </button>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form id="assignments-filter-form" onsubmit="return false;" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label for="filter-route" class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Route</label>
          <select id="filter-route" name="route_id" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Routes</option>
            <?php foreach ($routes as $r): ?>
              <option value="<?php echo $r->route_id; ?>" <?php echo (($filters['route_id'] ?? '') == $r->route_id) ? 'selected' : ''; ?>><?php echo html_escape($r->route_name); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label for="filter-class" class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Class</label>
          <select id="filter-class" name="class_id" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Classes</option>
            <?php foreach ($classes as $c): ?>
              <option value="<?php echo $c->class_id; ?>" <?php echo (($filters['class_id'] ?? '') == $c->class_id) ? 'selected' : ''; ?>><?php echo html_escape($c->class_name); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label for="filter-status" class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Status</label>
          <select id="filter-status" name="status" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Statuses</option>
            <option value="Active" <?php echo (($filters['status'] ?? 'Active') === 'Active') ? 'selected' : ''; ?>>Active Only</option>
            <option value="Cancelled" <?php echo (($filters['status'] ?? '') === 'Cancelled') ? 'selected' : ''; ?>>Cancelled / Past</option>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Reset</label>
          <button type="button" id="btn-reset-filters" class="w-full px-4 py-2 rounded-lg border border-outline-variant hover:bg-surface-container text-body-md font-medium text-on-surface transition-colors">
            Reset Filters
          </button>
        </div>
      </form>
    </div>

    <!-- Allocations Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Allocated Students</span>
      </div>

      <div class="table-scroll overflow-x-auto p-4">
        <table id="assignments-table" class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Student</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Class & Sec</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Route & Vehicle</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Pickup Stop & Time</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Transport Type</th>
              <th class="text-right px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Monthly Fee</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
          </tbody>
        </table>
      </div>
    </div>

    <!-- Allocate Student Modal Dialog -->
    <dialog id="assignModal" class="p-0 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-3 w-full max-w-lg backdrop:bg-scrim/40">
      <div class="p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface">Allocate Student to Transport</h3>
          <button onclick="document.getElementById('assignModal').close()" class="p-1 rounded-full hover:bg-surface-container-high text-on-surface-variant">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('transport/assignments'); ?>
          <div class="space-y-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Select Student *</label>
              <select name="student_id" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="">-- Choose Student --</option>
                <?php foreach ($students as $st): ?>
                  <option value="<?php echo $st->student_id; ?>">
                    <?php echo html_escape($st->first_name . ' ' . $st->last_name . ' (' . ($st->admission_number ?? $st->admission_no ?? '') . ' - ' . ($st->class_name ?? '') . ' ' . ($st->section_name ?? '') . ')'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Route *</label>
                <select name="route_id" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <?php foreach ($routes as $r): ?>
                    <option value="<?php echo $r->route_id; ?>"><?php echo html_escape($r->route_name . ' (' . $r->route_code . ')'); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Vehicle *</label>
                <select name="vehicle_id" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <?php foreach ($vehicles as $v): ?>
                    <option value="<?php echo $v->vehicle_id; ?>"><?php echo html_escape($v->vehicle_number . ' (' . $v->available_seats . ' seats free)'); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Pickup / Drop Stop *</label>
              <select name="pickup_stop_id" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <?php foreach ($stops as $s): ?>
                  <option value="<?php echo $s->stop_id; ?>"><?php echo html_escape($s->stop_name . ' - ' . $s->route_name . ' (₹' . $s->fare_amount . ')'); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Transport Type</label>
                <select name="transport_type" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <option value="Two Way">Two Way (Pickup & Drop)</option>
                  <option value="One Way">One Way</option>
                  <option value="Pickup Only">Pickup Only</option>
                  <option value="Drop Only">Drop Only</option>
                </select>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Monthly Fee (₹) *</label>
                <input type="number" step="50" name="monthly_fee" value="1500" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-outline-variant/50">
              <button type="button" onclick="document.getElementById('assignModal').close()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-label-md">Cancel</button>
              <button type="submit" class="px-5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">Allocate Transport</button>
            </div>
          </div>
        <?php echo form_close(); ?>
      </div>
    </dialog>

<script>
$(document).ready(function() {
    var table = School.DataTable.serverSide('#assignments-table', {
        ajax: {
            url: '<?php echo site_url("transport/ajax_assignments_list"); ?>',
            data: function(d) {
                d.route_id = $('#filter-route').val();
                d.class_id = $('#filter-class').val();
                d.status = $('#filter-status').val();
            }
        },
        columns: [
            { orderable: true, className: "px-4 py-3 whitespace-nowrap align-middle" },
            { orderable: true, className: "px-4 py-3 whitespace-nowrap font-medium text-on-surface text-[13px] align-middle" },
            { orderable: true, className: "px-4 py-3 whitespace-nowrap text-[13px] text-on-surface align-middle" },
            { orderable: true, className: "px-4 py-3 whitespace-nowrap text-[13px] text-on-surface align-middle" },
            { orderable: true, className: "px-4 py-3 whitespace-nowrap align-middle" },
            { orderable: true, className: "px-4 py-3 text-right whitespace-nowrap font-mono font-bold text-on-surface align-middle" },
            { orderable: true, className: "px-4 py-3 text-center whitespace-nowrap align-middle" },
            { orderable: false, className: "px-4 py-3 text-center whitespace-nowrap align-middle" }
        ],
        order: [[0, 'asc']]
    });

    $('#filter-route, #filter-class, #filter-status').on('change', function() {
        if (table) table.ajax.reload();
    });

    $('#btn-reset-filters').on('click', function() {
        $('#filter-route').val('');
        $('#filter-class').val('');
        $('#filter-status').val('Active');
        if (table) {
            table.search('').draw();
        }
    });
});
</script>
