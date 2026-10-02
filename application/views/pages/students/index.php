<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">All Students</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1"><?php echo count($students); ?> students found across all active sections.</p>
      </div>
      <div class="flex items-center gap-2 shrink-0 flex-wrap">
        <a href="<?php echo site_url('students/register'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm"><span class="material-symbols-outlined text-[18px]">person_add</span>Student Registration</a>
        <a href="<?php echo site_url('students/promotion'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors"><span class="material-symbols-outlined text-[18px]">upgrade</span>Promotion</a>
        <a href="<?php echo site_url('students/transfers'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors"><span class="material-symbols-outlined text-[18px]">move_up</span>Transfer / TC</a>
      </div>
    </div>
  
    <div class="flex flex-col lg:flex-row gap-3 mb-4 flex-wrap">
      <div class="relative flex-1 min-w-[220px]">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant/60 text-[20px]">search</span>
        <input type="text" placeholder="Search by name, admission no, roll no, parent..." value="<?php echo html_escape($this->input->get('search')); ?>" onkeydown="if(event.key==='Enter') window.location.href='<?php echo site_url('students'); ?>?search=' + encodeURIComponent(this.value)" class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary transition-colors"/>
      </div>
      <select onchange="applyFilter('academic_year_id', this.value)" class="px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md text-on-surface-variant">
        <option value="">All Academic Years</option>
        <?php foreach ($years as $yr): ?>
          <?php
            $isSel = ($this->input->get('academic_year_id') !== NULL)
              ? ($this->input->get('academic_year_id') == $yr->academic_year_id)
              : (!empty($yr->is_active));
          ?>
          <option value="<?php echo $yr->academic_year_id; ?>" <?php echo $isSel ? 'selected' : ''; ?>>
            <?php echo html_escape($yr->year_name); ?><?php echo (!empty($yr->is_active)) ? ' (Active)' : ''; ?>
          </option>
        <?php endforeach; ?>
      </select>
      <select onchange="applyFilter('class_id', this.value)" class="px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md text-on-surface-variant">
        <option value="">All Classes</option>
        <?php foreach ($classes as $cls): ?>
          <option value="<?php echo $cls->class_id; ?>" <?php echo ($this->input->get('class_id') == $cls->class_id) ? 'selected' : ''; ?>><?php echo html_escape($cls->class_name); ?></option>
        <?php endforeach; ?>
      </select>
      <select onchange="applyFilter('division_id', this.value)" class="px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md text-on-surface-variant">
        <option value="">All Divisions</option>
        <?php foreach ($sections as $sec): ?>
          <option value="<?php echo ($sec->division_id ?? $sec->section_id); ?>" <?php echo (($this->input->get('division_id') ?: $this->input->get('section_id')) == ($sec->division_id ?? $sec->section_id)) ? 'selected' : ''; ?>><?php echo html_escape(($sec->class_name ?? '') . ' Division ' . ($sec->division_name ?? $sec->section_name)); ?></option>
        <?php endforeach; ?>
      </select>
      <select onchange="applyFilter('gender', this.value)" class="px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md text-on-surface-variant">
        <option value="">All Genders</option>
        <option value="Male" <?php echo ($this->input->get('gender') === 'Male') ? 'selected' : ''; ?>>Male</option>
        <option value="Female" <?php echo ($this->input->get('gender') === 'Female') ? 'selected' : ''; ?>>Female</option>
      </select>
      <select onchange="applyFilter('status', this.value)" class="px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md text-on-surface-variant">
        <option value="">All Status</option>
        <option value="1" <?php echo ($this->input->get('status') === '1') ? 'selected' : ''; ?>>Active</option>
        <option value="0" <?php echo ($this->input->get('status') === '0') ? 'selected' : ''; ?>>Inactive / Transferred</option>
      </select>
      <a href="<?php echo site_url('students'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors"><span class="material-symbols-outlined text-[18px]">restart_alt</span>Reset</a>
    </div>
  
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
      <div class="table-scroll overflow-x-auto p-2">
        <table id="students-table" class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60">
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Admission No.</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Student</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Class & Division</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Gender</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Date of Birth</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Guardian</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Phone</th>
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
          School.DataTable.init('#students-table', {
            serverSide: true,
            processing: true,
            searching: false, // Page header has dedicated search input
            ajax: {
              url: '<?php echo site_url('students/ajax_list'); ?>',
              type: 'POST',
              data: function(d) {
                d.academic_year_id = '<?php echo html_escape($this->input->get('academic_year_id')); ?>';
                d.class_id         = '<?php echo html_escape($this->input->get('class_id')); ?>';
                d.division_id      = '<?php echo html_escape($this->input->get('division_id') ?: $this->input->get('section_id')); ?>';
                d.section_id       = '<?php echo html_escape($this->input->get('division_id') ?: $this->input->get('section_id')); ?>';
                d.gender           = '<?php echo html_escape($this->input->get('gender')); ?>';
                d.status           = '<?php echo html_escape($this->input->get('status')); ?>';
                d.search           = { value: '<?php echo html_escape($this->input->get('search')); ?>' };
              }
            },
            columns: [
              { data: 0, orderable: true },
              { data: 1, orderable: true },
              { data: 2, orderable: true },
              { data: 3, orderable: true },
              { data: 4, orderable: true },
              { data: 5, orderable: true },
              { data: 6, orderable: true },
              { data: 7, orderable: true },
              { data: 8, orderable: false, className: 'text-right' }
            ]
          });
        }
      });
    </script>

