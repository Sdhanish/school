<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Teachers Directory</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1"><?php echo count($teachers); ?> teaching faculty members, subject specialists, and mentors.</p>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <a href="<?php echo site_url('staff/register'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm"><span class="material-symbols-outlined text-[18px]">person_add</span>Add Faculty</a>
        <a href="<?php echo site_url('staff/workload'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors"><span class="material-symbols-outlined text-[18px]">calendar_month</span>Workload</a>
      </div>
    </div>

    <!-- Filters Bar -->
    <div class="flex flex-col md:flex-row gap-3 mb-4 flex-wrap">
      <div class="relative flex-1 min-w-[220px]">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant/60 text-[20px]">search</span>
        <input type="text" placeholder="Search teacher name, employee ID, phone..." value="<?php echo html_escape($this->input->get('search')); ?>" onkeydown="if(event.key==='Enter') window.location.href='<?php echo site_url('staff/teachers'); ?>?search=' + encodeURIComponent(this.value)" class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary transition-colors"/>
      </div>
      <select onchange="applyFilter('academic_group_id', this.value)" class="px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md text-on-surface-variant">
        <option value="">All Department / Groups</option>
        <?php if (!empty($academic_groups)): ?>
          <?php foreach ($academic_groups as $grp): ?>
            <option value="<?php echo $grp->academic_group_id; ?>" <?php echo ($this->input->get('academic_group_id') == $grp->academic_group_id) ? 'selected' : ''; ?>><?php echo html_escape($grp->group_name); ?></option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
      <a href="<?php echo site_url('staff/teachers'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors"><span class="material-symbols-outlined text-[18px]">restart_alt</span>Reset</a>
    </div>

    <!-- Teachers Grid Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <?php if (empty($teachers)): ?>
        <div class="col-span-full p-8 text-center bg-surface-container-lowest rounded-xl border border-outline-variant text-on-surface-variant">
          No teachers found matching current search.
        </div>
      <?php endif; ?>
      <?php foreach ($teachers as $t): ?>
        <?php
          $nameParts = explode(' ', trim($t->full_name));
          $initials = '';
          foreach ($nameParts as $np) { if (!empty($np)) $initials .= strtoupper($np[0]); }
          if (strlen($initials) > 2) $initials = substr($initials, 0, 2);
          $hasPhoto = (!empty($t->photo) && file_exists(FCPATH . 'uploads/staff/' . $t->photo));
        ?>
        <div class="elevation-1 rounded-2xl overflow-hidden border border-outline-variant/60 bg-surface-container-lowest flex flex-col justify-between hover:shadow-md transition-shadow">
          <div class="p-5">
            <div class="flex items-start gap-4 mb-4">
              <?php if ($hasPhoto): ?>
                <img src="<?php echo base_url('uploads/staff/' . $t->photo); ?>" alt="<?php echo html_escape($t->full_name); ?>" class="w-14 h-16 rounded-xl object-cover border border-outline-variant/60 shadow-sm shrink-0"/>
              <?php else: ?>
                <div class="w-14 h-14 rounded-xl bg-primary text-white flex items-center justify-center font-bold text-xl shrink-0 shadow-sm">
                  <?php echo html_escape($initials); ?>
                </div>
              <?php endif; ?>
              <div class="min-w-0 flex-1">
                <a href="<?php echo site_url('staff/profile/' . $t->staff_id); ?>" class="font-headline-md text-headline-md text-on-surface hover:text-primary hover:underline block truncate font-bold" style="font-size:16px">
                  <?php echo html_escape($t->full_name); ?>
                </a>
                <div class="text-[12px] text-on-surface-variant font-medium mt-0.5">
                  <?php echo html_escape($t->designation_name ?: '—'); ?> · <span class="font-mono text-primary"><?php echo html_escape($t->employee_code); ?></span>
                  <?php if (!empty($t->group_name)): ?>
                    · <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-semibold bg-primary-container text-on-primary-container"><?php echo html_escape($t->group_name); ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <div class="space-y-2 border-t border-outline-variant/40 pt-3 text-body-md">
              <div class="flex items-center justify-between text-[13px]">
                <span class="text-on-surface-variant">Department / Group</span>
                <span class="font-semibold text-primary"><?php echo html_escape($t->group_name ?: '—'); ?></span>
              </div>
              <div class="flex items-center justify-between text-[13px]">
                <span class="text-on-surface-variant">Qualification</span>
                <span class="font-medium text-on-surface truncate ml-2"><?php echo html_escape($t->qualification ?: '—'); ?></span>
              </div>
              <div class="flex items-center justify-between text-[13px]">
                <span class="text-on-surface-variant">Experience</span>
                <span class="font-medium text-on-surface"><?php echo html_escape($t->experience ?: '—'); ?></span>
              </div>
              <div class="flex items-center justify-between text-[13px]">
                <span class="text-on-surface-variant">Specialization</span>
                <span class="font-medium text-primary truncate ml-2"><?php echo html_escape($t->specialization ?: '—'); ?></span>
              </div>
              <div class="flex items-center justify-between text-[13px]">
                <span class="text-on-surface-variant">Phone</span>
                <span class="text-on-surface"><?php echo html_escape($t->phone); ?></span>
              </div>
            </div>
          </div>

          <div class="flex border-t border-outline-variant/40 bg-surface-container-low">
            <a href="<?php echo site_url('staff/profile/' . $t->staff_id); ?>" class="flex-1 py-2.5 text-label-md text-on-surface-variant hover:bg-surface-container-high flex items-center justify-center gap-1"><span class="material-symbols-outlined text-[16px]">visibility</span>Profile</a>
            <div class="w-px bg-outline-variant/40"></div>
            <a href="<?php echo site_url('staff/edit/' . $t->staff_id); ?>" class="flex-1 py-2.5 text-label-md text-on-surface-variant hover:bg-surface-container-high flex items-center justify-center gap-1"><span class="material-symbols-outlined text-[16px]">edit</span>Edit</a>
            <div class="w-px bg-outline-variant/40"></div>
            <a href="<?php echo site_url('staff/workload?staff_id=' . $t->staff_id); ?>" class="flex-1 py-2.5 text-label-md text-primary hover:bg-surface-container-high flex items-center justify-center gap-1"><span class="material-symbols-outlined text-[16px]">calendar_month</span>Workload</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <script>
      function applyFilter(key, val) {
        var url = new URL(window.location.href);
        if (val) { url.searchParams.set(key, val); } else { url.searchParams.delete(key); }
        window.location.href = url.toString();
      }
    </script>
