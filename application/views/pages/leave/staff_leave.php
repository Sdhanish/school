<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('success')): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-secondary-container text-on-secondary-container text-body-md font-medium flex items-center gap-2 border border-secondary/20">
        <span class="material-symbols-outlined text-[20px] text-secondary">check_circle</span>
        <?php echo html_escape($this->session->flashdata('success')); ?>
      </div>
    <?php endif; ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Staff & Faculty Leave Management</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Review teacher leave applications, casual/earned leave requests, and track faculty staffing coverage.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('leave/request?type=Staff'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Apply Staff Leave
        </a>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form id="staff-leave-filter-form" onsubmit="return false;" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        

        <div>
          <label for="filter-leave-type" class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Leave Type</label>
          <select id="filter-leave-type" name="leave_type_id" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Types</option>
            <?php foreach ($leave_types as $lt): ?>
              <option value="<?php echo $lt->type_id; ?>" <?php echo (($filters['leave_type_id'] ?? '') == $lt->type_id) ? 'selected' : ''; ?>><?php echo html_escape($lt->type_name); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label for="filter-status" class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Status</label>
          <select id="filter-status" name="status" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Statuses</option>
            <option value="Pending" <?php echo (($filters['status'] ?? '') === 'Pending') ? 'selected' : ''; ?>>Pending</option>
            <option value="Approved" <?php echo (($filters['status'] ?? '') === 'Approved') ? 'selected' : ''; ?>>Approved</option>
            <option value="Rejected" <?php echo (($filters['status'] ?? '') === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
            <option value="Clarification Required" <?php echo (($filters['status'] ?? '') === 'Clarification Required') ? 'selected' : ''; ?>>Clarification Required</option>
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

    <!-- Staff Leaves Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Staff Applications</span>
      </div>

      <div class="table-scroll overflow-x-auto p-4">
        <table id="staff-leave-table" class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Staff Member</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Leave Type</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Dates</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Days</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Reason</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
          </tbody>
        </table>
      </div>
    </div>

<script>
$(document).ready(function() {
    var table = School.DataTable.serverSide('#staff-leave-table', {
        ajax: {
            url: '<?php echo site_url("leave/ajax_staff_leave_list"); ?>',
            data: function(d) {
                d.leave_type_id = $('#filter-leave-type').val();
                d.status = $('#filter-status').val();
            }
        },
        columns: [
            { orderable: true, className: "px-4 py-3 whitespace-nowrap align-middle" },
            { orderable: true, className: "px-4 py-3 whitespace-nowrap font-medium text-on-surface text-[13px] align-middle" },
            { orderable: true, className: "px-4 py-3 whitespace-nowrap align-middle" },
            { orderable: true, className: "px-4 py-3 whitespace-nowrap font-mono text-[12px] text-on-surface align-middle" },
            { orderable: true, className: "px-4 py-3 text-center whitespace-nowrap align-middle" },
            { orderable: false, className: "px-4 py-3 text-[13px] align-middle" },
            { orderable: true, className: "px-4 py-3 text-center whitespace-nowrap align-middle" },
            { orderable: false, className: "px-4 py-3 text-center whitespace-nowrap align-middle" }
        ],
        order: [[3, 'desc']]
    });

    $('#filter-leave-type, #filter-status').on('change', function() {
        if (table) table.ajax.reload();
    });

    $('#btn-reset-filters').on('click', function() {
        
        $('#filter-leave-type').val('');
        $('#filter-status').val('');
        if (table) {
            table.search('').draw();
        }
    });
});
</script>
