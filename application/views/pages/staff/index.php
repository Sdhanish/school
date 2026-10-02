<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">All Staff</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1"><?php echo count($staff); ?> faculty and administrative staff members registered.</p>
      </div>
      <div class="flex items-center gap-2 shrink-0 flex-wrap">
        <a href="<?php echo site_url('staff/register'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm"><span class="material-symbols-outlined text-[18px]">person_add</span>Add Staff</a>
        <a href="<?php echo site_url('staff/attendance'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors"><span class="material-symbols-outlined text-[18px]">fact_check</span>Attendance</a>
        <a href="<?php echo site_url('staff/leave'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors"><span class="material-symbols-outlined text-[18px]">event_busy</span>Leave Requests</a>
      </div>
    </div>

    <!-- Filters Bar -->
    <div class="flex flex-col lg:flex-row gap-3 mb-4 flex-wrap">
      <div class="relative flex-1 min-w-[220px]">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant/60 text-[20px]">search</span>
        <input type="text" placeholder="Search staff name, employee ID, phone, email..." value="<?php echo html_escape($this->input->get('search')); ?>" onkeydown="if(event.key==='Enter') window.location.href='<?php echo site_url('staff'); ?>?search=' + encodeURIComponent(this.value)" class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary transition-colors"/>
      </div>
      <select onchange="applyFilter('staff_type', this.value)" class="px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md text-on-surface-variant">
        <option value="">All Staff Types</option>
        <option value="teacher" <?php echo ($this->input->get('staff_type') === 'teacher') ? 'selected' : ''; ?>>Teaching Faculty</option>
        <option value="non_teaching" <?php echo ($this->input->get('staff_type') === 'non_teaching') ? 'selected' : ''; ?>>Non-Teaching Staff</option>
      </select>
      <select onchange="applyFilter('academic_group_id', this.value)" class="px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md text-on-surface-variant">
        <option value="">All Department / Groups</option>
        <?php if (!empty($academic_groups)): ?>
          <?php foreach ($academic_groups as $grp): ?>
            <option value="<?php echo $grp->academic_group_id; ?>" <?php echo ($this->input->get('academic_group_id') == $grp->academic_group_id) ? 'selected' : ''; ?>><?php echo html_escape($grp->group_name); ?></option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
      <select onchange="applyFilter('designation_id', this.value)" class="px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md text-on-surface-variant">
        <option value="">All Designations</option>
        <?php foreach ($designations as $desig): ?>
          <option value="<?php echo $desig->designation_id; ?>" <?php echo ($this->input->get('designation_id') == $desig->designation_id) ? 'selected' : ''; ?>><?php echo html_escape($desig->designation_name); ?></option>
        <?php endforeach; ?>
      </select>
      <select onchange="applyFilter('status', this.value)" class="px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md text-on-surface-variant">
        <option value="">All Statuses</option>
        <option value="1" <?php echo ($this->input->get('status') === '1' || $this->input->get('status') === NULL) ? 'selected' : ''; ?>>Active</option>
        <option value="0" <?php echo ($this->input->get('status') === '0') ? 'selected' : ''; ?>>Inactive / Resigned</option>
      </select>
      <a href="<?php echo site_url('staff'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors"><span class="material-symbols-outlined text-[18px]">restart_alt</span>Reset</a>
    </div>

    <!-- Staff Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
      <div class="table-scroll overflow-x-auto p-2">
        <table id="staff-table" class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60">
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Employee ID</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Staff Member</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Department / Group</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Designation</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Contact</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Status</th>
              <th class="text-right px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <!-- DataTables Server-Side Populated -->
          </tbody>
        </table>
      </div>
    </div>

    <script>
      function applyFilter(key, val) {
        var url = new URL(window.location.href);
        if (val) { url.searchParams.set(key, val); } else { url.searchParams.delete(key); }
        window.location.href = url.toString();
      }

      document.addEventListener("DOMContentLoaded", function() {
        if (typeof jQuery !== 'undefined' && typeof School !== 'undefined') {
          School.DataTable.init('#staff-table', {
            serverSide: true,
            processing: true,
            searching: false,
            ajax: {
              url: '<?php echo site_url('staff/ajax_list'); ?>',
              type: 'POST',
              data: function(d) {
                d.designation_id    = '<?php echo html_escape($this->input->get('designation_id')); ?>';
                d.staff_type        = '<?php echo html_escape($this->input->get('staff_type')); ?>';
                d.academic_group_id = '<?php echo html_escape($this->input->get('academic_group_id')); ?>';
                d.status            = '<?php echo html_escape($this->input->get('status')); ?>';
                d.search            = { value: '<?php echo html_escape($this->input->get('search')); ?>' };
              }
            },
            columns: [
              { data: 0, orderable: true },
              { data: 1, orderable: true },
              { data: 2, orderable: true },
              { data: 3, orderable: true },
              { data: 4, orderable: false },
              { data: 5, orderable: true },
              { data: 6, orderable: false, className: 'text-right' }
            ]
          });
        }
      });
    </script>

