<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Non-Teaching Staff</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1"><?php echo count($staff); ?> administrative, accounting, library, and support staff members.</p>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <a href="<?php echo site_url('staff/register'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm"><span class="material-symbols-outlined text-[18px]">person_add</span>Add Non-Teaching Staff</a>
      </div>
    </div>

    <!-- Filters Bar -->
    <div class="flex flex-col md:flex-row gap-3 mb-4 flex-wrap">
      <div class="relative flex-1 min-w-[220px]">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant/60 text-[20px]">search</span>
        <input type="text" placeholder="Search staff name, employee ID, phone..." value="<?php echo html_escape($this->input->get('search')); ?>" onkeydown="if(event.key==='Enter') window.location.href='<?php echo site_url('staff/non_teaching'); ?>?search=' + encodeURIComponent(this.value)" class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary transition-colors"/>
      </div>
      <select onchange="applyFilter('designation_id', this.value)" class="px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md text-on-surface-variant">
        <option value="">All Designations</option>
        <?php foreach ($designations as $desig): ?>
          <option value="<?php echo $desig->designation_id; ?>" <?php echo ($this->input->get('designation_id') == $desig->designation_id) ? 'selected' : ''; ?>><?php echo html_escape($desig->designation_name); ?></option>
        <?php endforeach; ?>
      </select>
      <a href="<?php echo site_url('staff/non_teaching'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors"><span class="material-symbols-outlined text-[18px]">restart_alt</span>Reset</a>
    </div>

    <!-- Non-Teaching Staff Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60">
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Employee ID</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Staff Member</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Designation</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Contact Phone</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Email Address</th>
              <th class="text-left px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Status</th>
              <th class="text-right px-4 py-3 text-label-md text-on-surface-variant uppercase tracking-wide whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($staff)): ?>
              <tr>
                <td colspan="8" class="px-4 py-8 text-center text-body-md text-on-surface-variant">No non-teaching staff found.</td>
              </tr>
            <?php endif; ?>
            <?php foreach ($staff as $st): ?>
              <?php
                $nameParts = explode(' ', trim($st->full_name));
                $initials = '';
                foreach ($nameParts as $np) { if (!empty($np)) $initials .= strtoupper($np[0]); }
                if (strlen($initials) > 2) $initials = substr($initials, 0, 2);
                $hasPhoto = (!empty($st->photo) && file_exists(FCPATH . 'uploads/staff/' . $st->photo));
              ?>
              <tr class='hover:bg-surface-container-low transition-colors'>
                <td class="px-4 py-3 text-body-md font-mono text-primary font-medium whitespace-nowrap">
                  <a href="<?php echo site_url('staff/profile/' . $st->staff_id); ?>" class="hover:underline"><?php echo html_escape($st->employee_code); ?></a>
                </td>
                <td class="px-4 py-3 text-body-md font-body-md text-on-surface whitespace-nowrap">
                  <div class="flex items-center gap-2.5">
                    <?php if ($hasPhoto): ?>
                      <img src="<?php echo base_url('uploads/staff/' . $st->photo); ?>" alt="<?php echo html_escape($st->full_name); ?>" class="w-8 h-8 rounded-full object-cover shrink-0 border border-outline-variant/60 shadow-sm"/>
                    <?php else: ?>
                      <div class="w-8 h-8 rounded-full bg-surface-container-high text-on-surface flex items-center justify-center text-[11px] font-semibold shrink-0"><?php echo html_escape($initials); ?></div>
                    <?php endif; ?>
                    <div>
                      <div class="font-medium text-on-surface"><?php echo html_escape($st->full_name); ?></div>
                      <div class="text-[12px] text-on-surface-variant"><?php echo html_escape($st->gender . ($st->qualification ? ' · ' . $st->qualification : '')); ?></div>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3 text-body-md text-on-surface whitespace-nowrap"><?php echo html_escape($st->designation_name ?: '—'); ?></td>
                <td class="px-4 py-3 text-body-md text-on-surface whitespace-nowrap"><?php echo html_escape($st->phone); ?></td>
                <td class="px-4 py-3 text-body-md text-on-surface whitespace-nowrap"><?php echo html_escape($st->email); ?></td>
                <td class="px-4 py-3 text-body-md whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-secondary-container text-on-secondary-container">Active</span>
                </td>
                <td class="px-4 py-3 text-body-md text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-1.5">
                    <a href="<?php echo site_url('staff/profile/' . $st->staff_id); ?>" title="View Profile" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors"><span class="material-symbols-outlined text-[18px]">visibility</span></a>
                    <a href="<?php echo site_url('staff/edit/' . $st->staff_id); ?>" title="Edit Staff" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-colors"><span class="material-symbols-outlined text-[18px]">edit</span></a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="flex items-center justify-between px-4 py-3 border-t border-outline-variant/50 text-body-md font-body-md text-on-surface-variant">
        <span>Showing <?php echo count($staff); ?> non-teaching staff</span>
      </div>
    </div>

    <script>
      function applyFilter(key, val) {
        var url = new URL(window.location.href);
        if (val) { url.searchParams.set(key, val); } else { url.searchParams.delete(key); }
        window.location.href = url.toString();
      }
    </script>
