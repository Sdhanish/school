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
        <h2 class="font-headline-md text-headline-md text-on-surface">Timetable Publish & Lock Control</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Manage institutional publication states (Draft, Published, Locked) to prevent accidental modifications to finalized schedules.</p>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('timetable/publish_lock'); ?>" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
      </form>
    </div>

    <!-- Publish & Lock Records Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Class Schedule Publishing States</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Class & Division</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Current Status</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Published Timestamp</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Published By</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Change State</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php
              $pub_map = [];
              foreach ($publish_records as $pr) {
                  $pub_map[$pr->class_id . '_' . ($pr->division_id ?? $pr->section_id)] = $pr;
              }
            ?>
            <?php foreach ($classes as $c): ?>
              <?php
                // Default Division 1
                $key = $c->class_id . '_1';
                $rec = $pub_map[$key] ?? null;
                $status = $rec ? $rec->status : 'Draft';
                $badgeClass = 'bg-surface-container-high text-on-surface-variant';
                if ($status === 'Published') $badgeClass = 'bg-secondary-container text-on-secondary-container font-bold';
                elseif ($status === 'Locked') $badgeClass = 'bg-amber-100 text-amber-900 font-bold';
              ?>
              <tr class="hover:bg-surface-container-low transition-colors">
                <td class="px-4 py-3 font-bold text-on-surface whitespace-nowrap">
                  <?php echo html_escape($c->class_name); ?> — Division A
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                  <span class="px-3 py-1 rounded-full text-[12px] <?php echo $badgeClass; ?>">
                    <?php echo html_escape($status); ?>
                  </span>
                </td>
                <td class="px-4 py-3 text-center font-mono text-[12px] text-on-surface-variant whitespace-nowrap">
                  <?php echo ($rec && $rec->published_at) ? date('d M Y, h:i A', strtotime($rec->published_at)) : '—'; ?>
                </td>
                <td class="px-4 py-3 text-center text-on-surface text-[13px] whitespace-nowrap">
                  <?php echo ($rec && $rec->publisher_name) ? html_escape($rec->publisher_name) : '—'; ?>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                  <div class="flex items-center justify-center gap-1.5">
                    <!-- Draft -->
                    <?php echo form_open('timetable/publish_lock?academic_year_id=' . $selected_year, array('class' => 'inline')); ?>
                      <input type="hidden" name="class_id" value="<?php echo $c->class_id; ?>"/>
                      <input type="hidden" name="division_id" value="1"/>
                      <input type="hidden" name="status" value="Draft"/>
                      <button type="submit" class="px-2.5 py-1 rounded-lg border border-outline-variant text-[11px] font-semibold <?php echo ($status === 'Draft') ? 'bg-primary text-on-primary' : 'bg-surface-container-low text-on-surface hover:bg-surface-container-high'; ?> cursor-pointer">
                        Draft
                      </button>
                    <?php echo form_close(); ?>

                    <!-- Published -->
                    <?php echo form_open('timetable/publish_lock?academic_year_id=' . $selected_year, array('class' => 'inline')); ?>
                      <input type="hidden" name="class_id" value="<?php echo $c->class_id; ?>"/>
                      <input type="hidden" name="division_id" value="1"/>
                      <input type="hidden" name="status" value="Published"/>
                      <button type="submit" class="px-2.5 py-1 rounded-lg border border-outline-variant text-[11px] font-semibold <?php echo ($status === 'Published') ? 'bg-secondary text-on-secondary' : 'bg-surface-container-low text-on-surface hover:bg-surface-container-high'; ?> cursor-pointer">
                        Publish
                      </button>
                    <?php echo form_close(); ?>

                    <!-- Locked -->
                    <?php echo form_open('timetable/publish_lock?academic_year_id=' . $selected_year, array('class' => 'inline')); ?>
                      <input type="hidden" name="class_id" value="<?php echo $c->class_id; ?>"/>
                      <input type="hidden" name="division_id" value="1"/>
                      <input type="hidden" name="status" value="Locked"/>
                      <button type="submit" class="px-2.5 py-1 rounded-lg border border-outline-variant text-[11px] font-semibold <?php echo ($status === 'Locked') ? 'bg-amber-600 text-white' : 'bg-surface-container-low text-on-surface hover:bg-surface-container-high'; ?> cursor-pointer">
                        Lock
                      </button>
                    <?php echo form_close(); ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
