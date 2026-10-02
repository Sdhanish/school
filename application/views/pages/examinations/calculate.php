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
        <h2 class="font-headline-md text-headline-md text-on-surface">Result & Rank Calculation</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Execute the backend result calculation engine to compute student totals, percentage, grade points, pass/fail status, and class ranks.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('examinations/settings'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">tune</span>Calculation Rules
        </a>
      </div>
    </div>

    <!-- Active Calculation Rules Banner -->
    <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 mb-6">
      <div class="flex items-center gap-2.5 pb-3 border-b border-outline-variant/50 mb-4">
        <span class="material-symbols-outlined text-primary text-[24px]">rule_settings</span>
        <h3 class="font-headline-md text-title-lg font-bold text-on-surface">Active Evaluation Rules</h3>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-body-md">
        <div class="p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/40">
          <span class="text-[12px] text-on-surface-variant block">Subject Pass Threshold</span>
          <strong class="text-on-surface"><?php echo ($settings->subject_pass_mark_rule) ? 'Required for all subjects' : 'Relaxed'; ?></strong>
        </div>
        <div class="p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/40">
          <span class="text-[12px] text-on-surface-variant block">Overall Pass Percentage</span>
          <strong class="text-on-surface font-mono"><?php echo $settings->overall_pass_percentage; ?>% Minimum</strong>
        </div>
        <div class="p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/40">
          <span class="text-[12px] text-on-surface-variant block">Single Subject Failure Rule</span>
          <strong class="text-on-surface"><?php echo ($settings->single_subject_fail_overall) ? 'Fails Overall Examination' : 'Compensated'; ?></strong>
        </div>
        <div class="p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/40">
          <span class="text-[12px] text-on-surface-variant block">Rank Criteria</span>
          <strong class="text-primary"><?php echo html_escape($settings->rank_criteria); ?> (Ties Handled: 1, 2, 2, 4)</strong>
        </div>
      </div>
    </div>

    <!-- Execution Form -->
    <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 max-w-2xl">
      <div class="flex items-center gap-2.5 pb-3 border-b border-outline-variant/50 mb-5">
        <span class="material-symbols-outlined text-secondary text-[24px]">play_circle</span>
        <h3 class="font-headline-md text-title-lg font-bold text-on-surface">Run Result Calculation</h3>
      </div>

      <?php echo form_open('examinations/calculate', array('onsubmit' => 'return confirm("Are you sure you want to calculate results for the selected scope? Existing un-published results will be refreshed.");')); ?>
        <div class="space-y-4">
          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Select Examination *</label>
            <select name="exam_id" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
              <option value="">-- Choose Exam --</option>
              <?php foreach ($exams as $e): ?>
                <option value="<?php echo $e->exam_id; ?>"><?php echo html_escape($e->exam_name); ?> (<?php echo html_escape($e->status); ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Class (Optional)</label>
              <select name="class_id" id="calc-class" onchange="loadCalcDivisions(this.value)" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="">All Applicable Classes</option>
                <?php foreach ($classes as $c): ?>
                  <option value="<?php echo $c->class_id; ?>"><?php echo html_escape($c->class_name); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Division (Optional)</label>
              <select name="division_id" id="calc-division" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="">All Divisions</option>
                <?php foreach ($divisions as $s): ?>
                  <option value="<?php echo $s->division_id; ?>"><?php echo html_escape($s->class_name . ' ' . $s->division_name); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="pt-4 border-t border-outline-variant/50 flex items-center justify-end gap-3">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
              <span class="material-symbols-outlined text-[18px]">calculate</span>Execute Calculation
            </button>
          </div>
        </div>
      <?php echo form_close(); ?>
    </div>

    <script>
      function loadCalcDivisions(classId) {
        var divSelect = document.getElementById('calc-division');
        if (!divSelect) return;
        divSelect.innerHTML = '<option value="">Loading divisions...</option>';
        if (!classId) {
          divSelect.innerHTML = '<option value="">All Divisions</option>';
          return;
        }
        fetch('<?php echo site_url('examinations/ajax_get_divisions'); ?>/' + classId)
          .then(function(res) { return res.json(); })
          .then(function(data) {
            divSelect.innerHTML = '<option value="">All Divisions</option>';
            if (data && data.length > 0) {
              data.forEach(function(d) {
                var opt = document.createElement('option');
                opt.value = d.division_id;
                opt.textContent = (d.class_name ? d.class_name + ' ' : '') + d.division_name;
                divSelect.appendChild(opt);
              });
            }
          })
          .catch(function(err) {
            console.error('Error fetching divisions:', err);
            divSelect.innerHTML = '<option value="">All Divisions</option>';
          });
      }
    </script>
