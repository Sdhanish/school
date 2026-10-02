<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
    $sd = isset($wizard['student_details'])  ? $wizard['student_details']  : array();
    $ad = isset($wizard['academic_details']) ? $wizard['academic_details'] : array();
    $pd = isset($wizard['parent_details'])   ? $wizard['parent_details']   : array();
    $ps = isset($ad['prev_school'])          ? $ad['prev_school']          : array();

    $cs = isset($current_step) ? (int)$current_step : 1;

    function wval($arr, $key, $default = '') {
        return isset($arr[$key]) ? htmlspecialchars((string)$arr[$key], ENT_QUOTES, 'UTF-8') : $default;
    }
    function wsel($arr, $key, $value, $default = '') {
        return (isset($arr[$key]) ? (string)$arr[$key] : $default) === (string)$value ? 'selected' : '';
    }

    $academic_types = ['Achievement','Olympiad','Competition','Scholarship','Project','Certification','Award','Other Academic'];
    $extra_types    = ['Sports','Arts','Music','Dance','Club','Cultural','Competition','Other'];
    $levels         = ['School','District','State','National','International'];

    $saved_activities     = isset($ad['activities'])      ? $ad['activities']      : array();
    $saved_extracurricular = isset($ad['extracurricular']) ? $ad['extracurricular'] : array();
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
  <div>
    <h2 class="font-headline-md text-headline-md text-on-surface">Student Registration</h2>
    <p class="text-body-md font-body-md text-on-surface-variant mt-1">Complete all three steps to register a new student.</p>
  </div>
  <a href="<?php echo site_url('students/wizard_cancel'); ?>" id="wizard-cancel-btn"
     class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors shrink-0">
    <span class="material-symbols-outlined text-[18px]">close</span>Cancel
  </a>
</div>

<!-- ═══ STEP INDICATOR ═══════════════════════════════════════════════════ -->
<div class="flex items-center gap-0 mb-6 overflow-x-auto pb-1">
  <?php for ($i = 1; $i <= 3; $i++):
    $labels = [1 => 'Student Details', 2 => 'Academic Details', 3 => 'Parent / Guardian'];
  ?>
  <?php if ($i > 1): ?>
    <div class="w-8 h-px bg-outline-variant mx-2 shrink-0"></div>
  <?php endif; ?>
  <div class="flex items-center gap-2 shrink-0">
    <div class="w-8 h-8 rounded-full flex items-center justify-center text-[13px] font-semibold transition-colors
                <?php echo ($cs > $i) ? 'bg-secondary text-on-secondary' : (($cs === $i) ? 'bg-secondary text-on-secondary' : 'bg-surface-container-high text-on-surface-variant'); ?>">
      <?php if ($cs > $i): ?>
        <span class="material-symbols-outlined text-[16px]">check</span>
      <?php else: echo $i; endif; ?>
    </div>
    <span class="text-body-md font-body-md <?php echo ($cs === $i) ? 'text-on-surface font-medium' : 'text-on-surface-variant'; ?>"><?php echo $labels[$i]; ?></span>
  </div>
  <?php endfor; ?>
</div>

<!-- ═══ ALERT BANNER ═════════════════════════════════════════════════════ -->
<div id="wizard-alert" class="hidden mb-4 px-4 py-3 rounded-lg border" role="alert"></div>

<!-- ═══════════════════════════════════════════════════════════════════════
     STEP 1 — STUDENT DETAILS
════════════════════════════════════════════════════════════════════════ -->
<?php if ($cs === 1): ?>
<div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
  <div class="px-5 py-4 border-b border-outline-variant/50">
    <h3 class="font-headline-md text-headline-md text-on-surface">Student Details</h3>
  </div>
  <div class="p-5">
    <form id="step1-form" novalidate>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Admission Number <span class="text-error">*</span></label>
          <input type="text" id="admission_number" name="admission_number"
                 value="<?php echo wval($sd, 'admission_number', 'EDU' . date('Y') . sprintf('%03d', rand(10,999))); ?>"
                 placeholder="e.g. EDU2026009"
                 class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
          <p class="field-error text-error text-[11px] mt-1 hidden" id="err-admission_number"></p>
        </div>
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Gender</label>
          <select name="gender" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary">
            <?php foreach (['Male','Female','Other'] as $g): ?>
              <option value="<?php echo $g; ?>" <?php echo wsel($sd,'gender',$g,'Male'); ?>><?php echo $g; ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Student Name: First, Middle, Last -->
        <div class="sm:col-span-2">
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1.5">First Name <span class="text-error">*</span></label>
              <input type="text" id="first_name" name="first_name" value="<?php echo wval($sd, 'first_name'); ?>" placeholder="e.g. Anandhu"
                     class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
              <p class="field-error text-error text-[11px] mt-1 hidden" id="err-first_name"></p>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1.5">Middle Name <span class="text-xs text-on-surface-variant font-normal">(Optional)</span></label>
              <input type="text" id="middle_name" name="middle_name" value="<?php echo wval($sd, 'middle_name'); ?>" placeholder="e.g. S"
                     class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
              <p class="field-error text-error text-[11px] mt-1 hidden" id="err-middle_name"></p>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1.5">Last Name <span class="text-xs text-on-surface-variant font-normal">(Optional)</span></label>
              <input type="text" id="last_name" name="last_name" value="<?php echo wval($sd, 'last_name'); ?>" placeholder="e.g. Uthaman"
                     class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
              <p class="field-error text-error text-[11px] mt-1 hidden" id="err-last_name"></p>
            </div>
          </div>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Date of Birth</label>
          <input type="date" id="date_of_birth" name="date_of_birth" max="<?php echo date('Y-m-d'); ?>" value="<?php echo wval($sd, 'date_of_birth'); ?>" autocomplete="off"
                 class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary"/>
          <p class="field-error text-error text-[11px] mt-1 hidden" id="err-date_of_birth"></p>
        </div>
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Blood Group</label>
          <select name="blood_group" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary">
            <?php foreach (['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bg): ?>
              <option value="<?php echo $bg; ?>" <?php echo wsel($sd,'blood_group',$bg,'A+'); ?>><?php echo $bg; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Student Phone <span class="text-xs text-on-surface-variant font-normal">(Optional)</span></label>
          <?php $sp_data = Phone_validator::parse(wval($sd, 'student_phone')); ?>
          <input type="tel" id="student_phone" name="student_phone" data-phone-field="true" data-default-country="<?php echo strtolower($sp_data['country'] ?: 'in'); ?>" value="<?php echo html_escape($sp_data['national_number']); ?>" placeholder="98470 11223"
                 class="phone-input w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
          <p class="field-error text-error text-[11px] mt-1 hidden" id="err-student_phone"></p>
        </div>
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Student Email <span class="text-xs text-on-surface-variant font-normal">(Optional)</span></label>
          <input type="text" id="student_email" name="student_email" value="<?php echo wval($sd, 'student_email'); ?>" placeholder="e.g. student@school.edu"
                 class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
          <p class="field-error text-error text-[11px] mt-1 hidden" id="err-student_email"></p>
        </div>

        <!-- Student Address Section -->
        <div class="sm:col-span-2 pt-3 border-t border-outline-variant/40 mt-1">
          <div class="flex items-center gap-2 mb-3">
            <span class="material-symbols-outlined text-secondary text-[20px]">home_pin</span>
            <h4 class="font-headline-md text-headline-md text-on-surface font-semibold text-sm">Student Address</h4>
            <span class="text-xs text-on-surface-variant font-normal">(Optional)</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1.5">House Name / Flat / Building</label>
              <input type="text" id="house_name" name="house_name" value="<?php echo wval($sd, 'house_name'); ?>" placeholder="e.g. Rose Villa, Flat 4B"
                     class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
              <p class="field-error text-error text-[11px] mt-1 hidden" id="err-house_name"></p>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1.5">Street / Locality</label>
              <input type="text" id="street" name="street" value="<?php echo wval($sd, 'street'); ?>" placeholder="e.g. MG Road, Near City Hospital"
                     class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
              <p class="field-error text-error text-[11px] mt-1 hidden" id="err-street"></p>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1.5">City / Town</label>
              <input type="text" id="city" name="city" value="<?php echo wval($sd, 'city'); ?>" placeholder="e.g. Kochi"
                     class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
              <p class="field-error text-error text-[11px] mt-1 hidden" id="err-city"></p>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1.5">District</label>
              <input type="text" id="district" name="district" value="<?php echo wval($sd, 'district'); ?>" placeholder="e.g. Ernakulam"
                     class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
              <p class="field-error text-error text-[11px] mt-1 hidden" id="err-district"></p>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1.5">State</label>
              <input type="text" id="state" name="state" value="<?php echo wval($sd, 'state', 'Kerala'); ?>" placeholder="e.g. Kerala"
                     class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
              <p class="field-error text-error text-[11px] mt-1 hidden" id="err-state"></p>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1.5">PIN Code</label>
              <input type="text" id="pin_code" name="pin_code" maxlength="6" value="<?php echo wval($sd, 'pin_code'); ?>" placeholder="e.g. 682001"
                     class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
              <p class="field-error text-error text-[11px] mt-1 hidden" id="err-pin_code"></p>
            </div>
          </div>
        </div>

        <!-- Student Image Upload Field -->
        <div class="sm:col-span-2">
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">
            Student Profile Image
            <span class="text-on-surface-variant font-normal text-xs">(3:4 Portrait · JPG, JPEG, PNG, WEBP · Max 3 MB)</span>
          </label>
          <input type="hidden" id="photo_temp_path" name="photo_temp_path" value="<?php echo wval($sd, 'photo_temp_path'); ?>"/>
          <input type="hidden" id="photo_display_name" name="photo_display_name" value="<?php echo wval($sd, 'photo_display_name'); ?>"/>
          <input type="hidden" id="cropped_image_data" name="cropped_image_data" value=""/>

          <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 p-4 rounded-xl border border-outline-variant/60 bg-surface-container-low">
            <!-- 3:4 Aspect Ratio Preview Box (80px x 107px) -->
            <div id="photo-preview-container" class="relative w-20 h-[107px] rounded-xl overflow-hidden border border-outline-variant/80 bg-surface-container-lowest flex items-center justify-center shrink-0 shadow-xs">
              <?php $has_photo = !empty($sd['photo_temp_path']) && file_exists(FCPATH . $sd['photo_temp_path']); ?>
              <img id="photo-preview-img" src="<?php echo $has_photo ? base_url($sd['photo_temp_path']) : ''; ?>"
                   alt="Student Preview" class="w-full h-full object-cover <?php echo $has_photo ? '' : 'hidden'; ?>"/>
              <div id="photo-placeholder-icon" class="flex flex-col items-center justify-center text-on-surface-variant/60 <?php echo $has_photo ? 'hidden' : ''; ?>">
                <span class="material-symbols-outlined text-[36px]">account_circle</span>
                <span class="text-[10px] font-semibold text-on-surface-variant/70 mt-0.5">3:4 Portrait</span>
              </div>
            </div>

            <!-- Upload Controls & Status -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 flex-wrap mb-1.5">
                <button type="button" id="btn-choose-photo" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-2xs cursor-pointer">
                  <span class="material-symbols-outlined text-[17px]">photo_camera</span>
                  <span id="photo-btn-label"><?php echo $has_photo ? 'Change Photo' : 'Choose Image'; ?></span>
                </button>
                <input type="file" id="student_image_file" name="student_image" accept="image/jpeg,image/png,image/jpg,image/webp,.jpg,.jpeg,.png,.webp" class="hidden"/>
                <button type="button" id="photo-remove-btn" class="inline-flex items-center gap-1 px-3 py-2 rounded-lg border border-error/30 text-error hover:bg-error-container/40 text-label-md transition-colors cursor-pointer <?php echo $has_photo ? '' : 'hidden'; ?>">
                  <span class="material-symbols-outlined text-[16px]">delete</span>
                  <span>Remove</span>
                </button>
              </div>
              <p id="photo-file-name" class="text-xs text-on-surface-variant truncate <?php echo $has_photo ? '' : 'hidden'; ?>">
                <?php echo html_escape(wval($sd, 'photo_display_name', 'Student Photo')); ?>
              </p>
              <div id="photo-upload-progress" class="hidden items-center gap-2 text-xs text-primary mt-1 font-medium">
                <span class="material-symbols-outlined text-[15px] animate-spin">autorenew</span>
                <span>Processing and uploading image…</span>
              </div>
              <p class="field-error text-error text-[11px] mt-1 hidden" id="err-student_image"></p>
            </div>
          </div>
        </div>
      </div>
      <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-outline-variant/50">
        <a href="<?php echo site_url('students/wizard_cancel'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant text-label-md hover:bg-surface-container-high transition-colors">Cancel</a>
        <button type="submit" id="btn-step1-next" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">arrow_forward</span>Next
        </button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════════════════════
     STEP 2 — ACADEMIC DETAILS (Enhanced)
════════════════════════════════════════════════════════════════════════ -->
<?php if ($cs === 2): ?>
<form id="step2-form" novalidate>
<input type="hidden" id="tc_temp_path" name="tc_temp_path" value="<?php echo wval($ps, 'tc_temp_path'); ?>"/>

<!-- ── 2A: Current Academic Information ─────────────────────────────── -->
<div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-4">
  <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/50">
    <h3 class="font-headline-md text-headline-md text-on-surface">Current Academic Information</h3>
    <?php if (!empty($current_academic_year)): ?>
    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary-container text-on-secondary-container text-[12px] font-semibold">
      <span class="material-symbols-outlined text-[14px]">school</span><?php echo html_escape($current_academic_year->year_name); ?>
    </span>
    <?php endif; ?>
  </div>
  <div class="p-5">
    <input type="hidden" name="academic_year_id" value="<?php echo (int)$current_academic_year_id; ?>"/>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
      <div>
        <label class="block font-label-md text-label-md text-on-surface mb-1.5">Academic Year</label>
        <div class="px-3 py-2.5 rounded-lg border border-outline-variant/40 bg-surface-container-low text-body-md text-on-surface-variant font-medium">
          <?php echo !empty($current_academic_year) ? html_escape($current_academic_year->year_name) : 'Current Year'; ?>
        </div>
      </div>
      <div>
        <label class="block font-label-md text-label-md text-on-surface mb-1.5">Department / Group</label>
        <select id="academic_group_id" name="academic_group_id" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary">
          <option value="">All Department / Groups</option>
          <?php if (!empty($groups)): foreach ($groups as $grp): ?>
            <option value="<?php echo (int)$grp->academic_group_id; ?>" <?php echo isset($ad['academic_group_id']) && (int)$ad['academic_group_id'] === (int)$grp->academic_group_id ? 'selected' : ''; ?>><?php echo html_escape($grp->group_name); ?></option>
          <?php endforeach; endif; ?>
        </select>
      </div>
      <div>
        <label class="block font-label-md text-label-md text-on-surface mb-1.5">Class <span class="text-error">*</span></label>
        <select id="class_id" name="class_id" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary">
          <option value="">Select Class</option>
          <?php foreach ($classes as $cls): ?>
            <option value="<?php echo (int)$cls->class_id; ?>" data-group="<?php echo (int)($cls->academic_group_id ?? 0); ?>" <?php echo isset($ad['class_id']) && (int)$ad['class_id'] === (int)$cls->class_id ? 'selected' : ''; ?>><?php echo html_escape($cls->class_name); ?></option>
          <?php endforeach; ?>
        </select>
        <p class="field-error text-error text-[11px] mt-1 hidden" id="err-class_id"></p>
      </div>
      <div>
        <label class="block font-label-md text-label-md text-on-surface mb-1.5">Division</label>
        <select id="division_id" name="division_id" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary">
          <option value="">Select Division</option>
          <?php foreach ($sections as $sec): ?>
            <option value="<?php echo (int)($sec->division_id ?? $sec->section_id); ?>" <?php echo (isset($ad['division_id']) ? (int)$ad['division_id'] : (int)($ad['section_id'] ?? 0)) === (int)($sec->division_id ?? $sec->section_id) ? 'selected' : ''; ?>><?php echo html_escape(($sec->division_name ?? $sec->section_name) ?? $sec->class_name); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block font-label-md text-label-md text-on-surface mb-1.5">Roll Number</label>
        <input type="text" name="roll_number" value="<?php echo wval($ad, 'roll_number'); ?>" placeholder="e.g. 15"
               class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
      </div>
    </div>
  </div>
</div>

<!-- ── 2B: Previous School Information ──────────────────────────────── -->
<div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-4">
  <div class="px-5 py-4 border-b border-outline-variant/50">
    <h3 class="font-headline-md text-headline-md text-on-surface">Previous School Information</h3>
  </div>
  <div class="p-5">
    <!-- No Previous School checkbox -->
    <label class="flex items-center gap-2.5 mb-5 cursor-pointer w-fit">
      <input type="checkbox" id="no_previous_school" name="no_previous_school" value="1"
             <?php echo !empty($ad['no_previous_school']) ? 'checked' : ''; ?>
             class="w-4 h-4 rounded border-outline-variant accent-secondary cursor-pointer"/>
      <span class="text-body-md font-body-md text-on-surface">No Previous School</span>
      <span class="text-body-md text-on-surface-variant">(e.g. LKG/UKG first admission)</span>
    </label>

    <!-- Previous school fields -->
    <div id="prev-school-fields" class="<?php echo !empty($ad['no_previous_school']) ? 'hidden' : ''; ?>">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Previous School Name <span class="text-error">*</span></label>
          <input type="text" id="prev_school_name" name="prev_school_name" value="<?php echo wval($ps, 'school_name'); ?>" placeholder="e.g. St. Mary's Higher Secondary School"
                 class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
          <p class="field-error text-error text-[11px] mt-1 hidden" id="err-prev_school_name"></p>
        </div>
        <div class="sm:col-span-2">
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Previous School Address</label>
          <input type="text" name="prev_school_address" value="<?php echo wval($ps, 'school_address'); ?>" placeholder="School address"
                 class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
        </div>
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Board / Affiliation</label>
          <input type="text" id="prev_school_board" name="prev_school_board" value="<?php echo wval($ps, 'school_board'); ?>" placeholder="e.g. CBSE, ICSE, State Board"
                 class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
        </div>
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Previous Class / Grade</label>
          <input type="text" id="prev_class" name="prev_class" value="<?php echo wval($ps, 'previous_class'); ?>" placeholder="e.g. Grade 9, Class X"
                 class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
        </div>
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Previous Academic Year</label>
          <input type="text" id="prev_academic_year" name="prev_academic_year" value="<?php echo wval($ps, 'previous_academic_year'); ?>" placeholder="e.g. 2025-2026"
                 class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
        </div>
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Date of Leaving</label>
          <input type="date" name="date_of_leaving" value="<?php echo wval($ps, 'date_of_leaving'); ?>"
                 class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary"/>
        </div>
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Previous Percentage (%)</label>
          <input type="number" id="prev_percentage" name="prev_percentage" min="0" max="100" step="0.01"
                 value="<?php echo wval($ps, 'previous_percentage'); ?>" placeholder="e.g. 82.50"
                 class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
          <p class="field-error text-error text-[11px] mt-1 hidden" id="err-prev_percentage"></p>
        </div>
        <div class="sm:col-span-2">
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Reason for Leaving</label>
          <input type="text" name="reason_for_leaving" value="<?php echo wval($ps, 'reason_for_leaving'); ?>" placeholder="e.g. Relocated, Seeking better opportunities"
                 class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
        </div>
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">TC Number <span class="text-error">*</span></label>
          <input type="text" id="tc_number" name="tc_number" value="<?php echo wval($ps, 'tc_number'); ?>" placeholder="e.g. TC/2025/0042"
                 class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
          <p class="field-error text-error text-[11px] mt-1 hidden" id="err-tc_number"></p>
        </div>
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">TC Document <span class="text-error">*</span>
            <span class="text-on-surface-variant font-normal">(PDF, JPG, JPEG, PNG · Max 10 MB)</span>
          </label>
          <div class="flex items-center gap-2 flex-wrap">
            <label for="tc_document_file" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-outline-variant text-on-surface-variant text-label-md hover:bg-surface-container-high cursor-pointer transition-colors bg-surface-container-lowest">
              <span class="material-symbols-outlined text-[16px]">upload_file</span>
              <span id="tc-btn-label"><?php echo !empty($ps['tc_temp_path']) ? 'Change File' : 'Choose File'; ?></span>
            </label>
            <input type="file" id="tc_document_file" name="tc_document" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" class="hidden"/>
            <?php if (!empty($ps['tc_temp_path'])): ?>
            <span id="tc-file-status" class="inline-flex items-center gap-1 text-[12px] text-on-secondary-container bg-secondary-container px-2 py-1 rounded-full">
              <span class="material-symbols-outlined text-[14px]">check_circle</span>
              <span id="tc-file-name">File uploaded</span>
              <button type="button" id="tc-file-remove" class="ml-1 text-on-surface-variant hover:text-error">
                <span class="material-symbols-outlined text-[14px]">close</span>
              </button>
            </span>
            <?php else: ?>
            <span id="tc-file-status" class="hidden inline-flex items-center gap-1 text-[12px] text-on-secondary-container bg-secondary-container px-2 py-1 rounded-full">
              <span class="material-symbols-outlined text-[14px]">check_circle</span>
              <span id="tc-file-name"></span>
              <button type="button" id="tc-file-remove" class="ml-1 text-on-surface-variant hover:text-error">
                <span class="material-symbols-outlined text-[14px]">close</span>
              </button>
            </span>
            <?php endif; ?>
            <span id="tc-upload-progress" class="hidden text-[12px] text-on-surface-variant">Uploading…</span>
          </div>
          <p class="field-error text-error text-[11px] mt-1 hidden" id="err-tc_document"></p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ── 2C: Academic Activities ───────────────────────────────────────── -->
<div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-4">
  <div class="px-5 py-4 border-b border-outline-variant/50">
    <h3 class="font-headline-md text-headline-md text-on-surface">Academic Achievements &amp; Activities</h3>
    <p class="text-body-md text-on-surface-variant mt-0.5">Achievements, Olympiads, Competitions, Scholarships, Projects, Certifications, Awards</p>
  </div>
  <div class="p-5">
    <div id="academic-activities-list" class="flex flex-col gap-3 mb-4">
      <?php
      $rows = !empty($saved_activities) ? $saved_activities : [[]];
      foreach ($rows as $i => $act):
      ?>
      <div class="academic-activity-row grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2 p-3 rounded-lg bg-surface-container-low border border-outline-variant/40 relative">
        <div>
          <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Type</label>
          <select name="academic_activities[<?php echo $i; ?>][activity_type]" class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary">
            <?php foreach ($academic_types as $t): ?>
              <option value="<?php echo $t; ?>" <?php echo wsel($act,'activity_type',$t,'Achievement'); ?>><?php echo $t; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Activity Name <span class="text-error">*</span></label>
          <input type="text" name="academic_activities[<?php echo $i; ?>][activity_name]" value="<?php echo wval($act,'activity_name'); ?>" placeholder="Name / Title"
                 class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
        </div>
        <div>
          <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Position / Result</label>
          <input type="text" name="academic_activities[<?php echo $i; ?>][position_result]" value="<?php echo wval($act,'position_result'); ?>" placeholder="e.g. 1st, Gold"
                 class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
        </div>
        <div>
          <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Year</label>
          <input type="number" name="academic_activities[<?php echo $i; ?>][year]" value="<?php echo wval($act,'year'); ?>" placeholder="<?php echo date('Y'); ?>" min="1990" max="<?php echo date('Y'); ?>"
                 class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
        </div>
        <div class="flex flex-col gap-1">
          <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Description</label>
          <div class="flex gap-1">
            <input type="text" name="academic_activities[<?php echo $i; ?>][description]" value="<?php echo wval($act,'description'); ?>" placeholder="Brief description"
                   class="flex-1 min-w-0 px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
            <button type="button" class="remove-activity shrink-0 w-8 h-9 flex items-center justify-center rounded-lg border border-error/30 text-error hover:bg-error-container transition-colors" title="Remove">
              <span class="material-symbols-outlined text-[16px]">remove</span>
            </button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <button type="button" id="add-academic-activity" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-secondary text-secondary text-label-md hover:bg-secondary-container transition-colors">
      <span class="material-symbols-outlined text-[16px]">add</span>Add Academic Activity
    </button>
  </div>
</div>

<!-- ── 2D: Extracurricular Activities ───────────────────────────────── -->
<div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-4">
  <div class="px-5 py-4 border-b border-outline-variant/50">
    <h3 class="font-headline-md text-headline-md text-on-surface">Co-curricular &amp; Extracurricular Activities</h3>
    <p class="text-body-md text-on-surface-variant mt-0.5">Sports, Arts, Music, Dance, Clubs, Cultural Activities</p>
  </div>
  <div class="p-5">
    <div id="extracurricular-list" class="flex flex-col gap-3 mb-4">
      <?php
      $xrows = !empty($saved_extracurricular) ? $saved_extracurricular : [[]];
      foreach ($xrows as $j => $xact):
      ?>
      <div class="extra-activity-row grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-2 p-3 rounded-lg bg-surface-container-low border border-outline-variant/40 relative">
        <div>
          <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Type</label>
          <select name="extracurricular[<?php echo $j; ?>][activity_type]" class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary">
            <?php foreach ($extra_types as $t): ?>
              <option value="<?php echo $t; ?>" <?php echo wsel($xact,'activity_type',$t,'Sports'); ?>><?php echo $t; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Activity Name <span class="text-error">*</span></label>
          <input type="text" name="extracurricular[<?php echo $j; ?>][activity_name]" value="<?php echo wval($xact,'activity_name'); ?>" placeholder="Name"
                 class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
        </div>
        <div>
          <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Level</label>
          <select name="extracurricular[<?php echo $j; ?>][level]" class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary">
            <option value="">Select</option>
            <?php foreach ($levels as $l): ?>
              <option value="<?php echo $l; ?>" <?php echo wsel($xact,'level',$l); ?>><?php echo $l; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Position / Achievement</label>
          <input type="text" name="extracurricular[<?php echo $j; ?>][position_result]" value="<?php echo wval($xact,'position_result'); ?>" placeholder="e.g. Winner"
                 class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
        </div>
        <div>
          <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Year</label>
          <input type="number" name="extracurricular[<?php echo $j; ?>][year]" value="<?php echo wval($xact,'year'); ?>" placeholder="<?php echo date('Y'); ?>" min="1990" max="<?php echo date('Y'); ?>"
                 class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
        </div>
        <div class="flex flex-col gap-1">
          <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Description</label>
          <div class="flex gap-1">
            <input type="text" name="extracurricular[<?php echo $j; ?>][description]" value="<?php echo wval($xact,'description'); ?>" placeholder="Optional"
                   class="flex-1 min-w-0 px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
            <button type="button" class="remove-extra shrink-0 w-8 h-9 flex items-center justify-center rounded-lg border border-error/30 text-error hover:bg-error-container transition-colors" title="Remove">
              <span class="material-symbols-outlined text-[16px]">remove</span>
            </button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <button type="button" id="add-extra-activity" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-secondary text-secondary text-label-md hover:bg-secondary-container transition-colors">
      <span class="material-symbols-outlined text-[16px]">add</span>Add Extracurricular Activity
    </button>
  </div>
</div>

<!-- ── Navigation ────────────────────────────────────────────────────── -->
<div class="flex justify-between gap-3 mt-2 mb-4">
  <a href="<?php echo site_url('students/add'); ?>" id="btn-step2-back" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant text-label-md hover:bg-surface-container-high transition-colors">
    <span class="material-symbols-outlined text-[18px]">arrow_back</span>Back
  </a>
  <div class="flex gap-3">
    <a href="<?php echo site_url('students/wizard_cancel'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant text-label-md hover:bg-surface-container-high transition-colors">Cancel</a>
    <button type="submit" id="btn-step2-next" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
      <span class="material-symbols-outlined text-[18px]">arrow_forward</span>Next
    </button>
  </div>
</div>
</form>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════════════════════
     STEP 3 — PARENT / GUARDIAN
════════════════════════════════════════════════════════════════════════ -->
<?php if ($cs === 3): ?>
<div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
  <div class="px-5 py-4 border-b border-outline-variant/50">
    <h3 class="font-headline-md text-headline-md text-on-surface">Parent / Guardian</h3>
  </div>
  <div class="p-5">
    <!-- Summary of previous steps -->
    <div class="mb-5 p-4 rounded-lg bg-surface-container-low border border-outline-variant/40">
      <p class="text-label-md text-on-surface-variant mb-2 font-semibold uppercase tracking-wide">Registration Summary</p>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-body-md text-on-surface">
        <?php if (!empty($wizard['student_details'])): $s = $wizard['student_details']; ?>
        <div class="sm:col-span-2 flex items-center gap-3 mb-1">
          <?php if (!empty($s['photo_temp_path']) && file_exists(FCPATH . $s['photo_temp_path'])): ?>
          <img src="<?php echo base_url($s['photo_temp_path']); ?>" alt="Student Photo" class="w-12 h-12 rounded-lg object-cover border border-outline-variant shrink-0"/>
          <?php else: ?>
          <div class="w-12 h-12 rounded-lg bg-surface-container-high text-on-surface-variant flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-[24px]">account_circle</span>
          </div>
          <?php endif; ?>
          <div>
            <p class="font-medium text-on-surface text-body-md"><?php echo html_escape(trim($s['first_name'] . ' ' . $s['last_name'])); ?></p>
            <p class="text-xs text-on-surface-variant">Adm No: <span class="font-medium text-on-surface"><?php echo html_escape($s['admission_number']); ?></span> · Gender: <?php echo html_escape($s['gender'] ?? '—'); ?></p>
          </div>
        </div>
        <?php endif; ?>
        <?php if (!empty($wizard['academic_details'])): $a = $wizard['academic_details']; ?>
        <span><span class="text-on-surface-variant">Class:</span> <?php echo html_escape($a['class_id'] ?? '—'); ?></span>
        <span><span class="text-on-surface-variant">Roll No:</span> <?php echo html_escape($a['roll_number'] ?: '—'); ?></span>
        <?php if (!empty($a['prev_school']['school_name'])): ?>
        <span class="sm:col-span-2"><span class="text-on-surface-variant">Previous School:</span> <?php echo html_escape($a['prev_school']['school_name']); ?><?php if (!empty($a['prev_school']['tc_number'])): ?> · TC: <?php echo html_escape($a['prev_school']['tc_number']); ?><?php endif; ?></span>
        <?php endif; ?>
        <?php $act_count = count($a['activities'] ?? []) + count($a['extracurricular'] ?? []); ?>
        <?php if ($act_count > 0): ?>
        <span class="sm:col-span-2"><span class="text-on-surface-variant">Activities:</span> <?php echo $act_count; ?> added</span>
        <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>

    <form id="step3-form" novalidate>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Guardian Name <span class="text-error">*</span></label>
          <input type="text" id="guardian_name" name="guardian_name" value="<?php echo wval($pd, 'guardian_name'); ?>" placeholder="e.g. Suresh Nair"
                 class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
          <p class="field-error text-error text-[11px] mt-1 hidden" id="err-guardian_name"></p>
        </div>
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Relation</label>
          <select name="guardian_relation" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary">
            <?php foreach (['Father','Mother','Grandfather','Grandmother','Uncle','Aunt','Guardian'] as $rel): ?>
              <option value="<?php echo $rel; ?>" <?php echo wsel($pd,'guardian_relation',$rel,'Father'); ?>><?php echo $rel; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Guardian Phone <span class="text-error">*</span></label>
          <?php $gp_data = Phone_validator::parse(wval($pd, 'guardian_phone')); ?>
          <input type="tel" id="guardian_phone" name="guardian_phone" required data-phone-field="true" data-required="true" data-default-country="<?php echo strtolower($gp_data['country'] ?: 'in'); ?>" value="<?php echo html_escape($gp_data['national_number']); ?>" placeholder="98470 11223"
                 class="phone-input w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
          <p class="field-error text-error text-[11px] mt-1 hidden" id="err-guardian_phone"></p>
        </div>
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Father Phone <span class="text-xs text-on-surface-variant font-normal">(Optional)</span></label>
          <?php $fp_data = Phone_validator::parse(wval($pd, 'father_phone')); ?>
          <input type="tel" id="father_phone" name="father_phone" data-phone-field="true" data-default-country="<?php echo strtolower($fp_data['country'] ?: 'in'); ?>" value="<?php echo html_escape($fp_data['national_number']); ?>" placeholder="98470 11223"
                 class="phone-input w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
          <p class="field-error text-error text-[11px] mt-1 hidden" id="err-father_phone"></p>
        </div>
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Mother Phone <span class="text-xs text-on-surface-variant font-normal">(Optional)</span></label>
          <?php $mp_data = Phone_validator::parse(wval($pd, 'mother_phone')); ?>
          <input type="tel" id="mother_phone" name="mother_phone" data-phone-field="true" data-default-country="<?php echo strtolower($mp_data['country'] ?: 'in'); ?>" value="<?php echo html_escape($mp_data['national_number']); ?>" placeholder="98470 11223"
                 class="phone-input w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
          <p class="field-error text-error text-[11px] mt-1 hidden" id="err-mother_phone"></p>
        </div>
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Emergency Contact <span class="text-xs text-on-surface-variant font-normal">(Optional)</span></label>
          <?php $ec_data = Phone_validator::parse(wval($pd, 'emergency_contact')); ?>
          <input type="tel" id="emergency_contact" name="emergency_contact" data-phone-field="true" data-default-country="<?php echo strtolower($ec_data['country'] ?: 'in'); ?>" value="<?php echo html_escape($ec_data['national_number']); ?>" placeholder="98470 11223"
                 class="phone-input w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
          <p class="field-error text-error text-[11px] mt-1 hidden" id="err-emergency_contact"></p>
        </div>
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Guardian Email</label>
          <input type="email" id="guardian_email" name="guardian_email" value="<?php echo wval($pd, 'guardian_email'); ?>" placeholder="parent@example.com"
                 class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
        </div>
        <div class="sm:col-span-2">
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Address</label>
          <input type="text" name="address" value="<?php echo wval($pd, 'address'); ?>" placeholder="House name, Place, District, PIN"
                 class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
        </div>
      </div>
      <div class="flex justify-between gap-3 mt-6 pt-5 border-t border-outline-variant/50">
        <a href="<?php echo site_url('students/add?step=2'); ?>" id="btn-step3-back" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>Back
        </a>
        <div class="flex gap-3">
          <a href="<?php echo site_url('students/wizard_cancel'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant text-label-md hover:bg-surface-container-high transition-colors">Cancel</a>
          <button type="submit" id="btn-save-student" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">check</span>Save Student
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════════════════════
     IMAGE CROPPER MODAL (FIXED 3:4 PORTRAIT)
════════════════════════════════════════════════════════════════════════ -->
<div id="student-cropper-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 p-4 hidden backdrop-blur-sm">
  <div class="elevation-3 rounded-2xl bg-surface-container-lowest border border-outline-variant w-full max-w-xl overflow-hidden flex flex-col max-h-[92vh] shadow-2xl">
    
    <!-- Modal Header -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/60 bg-surface-container-low">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-[22px]">crop</span>
        <h3 class="font-headline-md text-headline-md text-on-surface text-base font-bold">Crop Student Profile Image</h3>
      </div>
      <button type="button" id="btn-student-cropper-close" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer" title="Close">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>

    <!-- Modal Body / Cropper Area -->
    <div class="p-4 bg-slate-950/80 flex-1 overflow-hidden flex items-center justify-center relative select-none" style="min-height: 300px; max-height: 380px;">
      <div class="w-full h-full flex items-center justify-center overflow-hidden">
        <img id="student-cropper-image-target" src="" alt="Source Image" class="max-w-full block" style="max-height: 360px;"/>
      </div>
    </div>

    <!-- Modal Controls Toolbar -->
    <div class="px-6 py-3.5 bg-surface-container-lowest border-t border-outline-variant/40 flex flex-wrap items-center justify-between gap-4">
      
      <!-- Zoom Controls with Sync -->
      <div class="flex items-center gap-2 flex-1 min-w-[200px]">
        <button type="button" id="btn-student-cropper-zoom-out" title="Zoom Out" class="p-1.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors cursor-pointer shrink-0">
          <span class="material-symbols-outlined text-[18px]">zoom_out</span>
        </button>
        <input type="range" id="student-cropper-zoom-range" min="0.1" max="3" step="0.05" value="1" class="w-full h-1.5 bg-surface-container-highest rounded-lg appearance-none cursor-pointer accent-primary" title="Zoom Slider"/>
        <button type="button" id="btn-student-cropper-zoom-in" title="Zoom In" class="p-1.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors cursor-pointer shrink-0">
          <span class="material-symbols-outlined text-[18px]">zoom_in</span>
        </button>
      </div>

      <!-- Rotation & Reset Controls -->
      <div class="flex items-center gap-1.5 shrink-0">
        <button type="button" id="btn-student-cropper-rotate-left" title="Rotate Left (-90°)" class="px-2.5 py-1.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors text-xs font-medium cursor-pointer inline-flex items-center gap-1">
          <span class="material-symbols-outlined text-[16px]">rotate_left</span>-90°
        </button>
        <button type="button" id="btn-student-cropper-rotate-right" title="Rotate Right (+90°)" class="px-2.5 py-1.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors text-xs font-medium cursor-pointer inline-flex items-center gap-1">
          <span class="material-symbols-outlined text-[16px]">rotate_right</span>+90°
        </button>
        <button type="button" id="btn-student-cropper-reset" title="Reset View" class="px-2.5 py-1.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors text-xs font-medium cursor-pointer inline-flex items-center gap-1">
          <span class="material-symbols-outlined text-[16px]">restart_alt</span>Reset
        </button>
      </div>
    </div>

    <!-- Modal Footer -->
    <div class="px-6 py-3.5 border-t border-outline-variant/60 bg-surface-container-low flex items-center justify-between gap-3">
      <button type="button" id="btn-student-cropper-change-file" class="text-label-md text-primary hover:underline font-medium cursor-pointer flex items-center gap-1 text-[13px]">
        <span class="material-symbols-outlined text-[16px]">folder_open</span>Choose Another Image
      </button>
      <div class="flex items-center gap-2.5">
        <button type="button" id="btn-student-cropper-cancel" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-label-md cursor-pointer transition-colors">Cancel</button>
        <button type="button" id="btn-student-cropper-apply" class="px-5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors cursor-pointer shadow-sm flex items-center gap-1.5 font-medium">
          <span class="material-symbols-outlined text-[18px]">crop</span>Apply Crop
        </button>
      </div>
    </div>

  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════
     WIZARD JAVASCRIPT
════════════════════════════════════════════════════════════════════════ -->
<script>
(function ($) {
    'use strict';

    var BASE_URL  = window.APP_BASE_URL || '';
    var CSRF_NAME = window.CSRF_TOKEN_NAME || 'csrf_token';
    var CSRF_HASH = window.CSRF_HASH || '';
    var CURRENT_YEAR = <?php echo date('Y'); ?>;
    var SERVER_TODAY = "<?php echo date('Y-m-d'); ?>";

    function refreshCsrf(data) {
        if (data && data.csrf_token_name && data.csrf_hash) {
            CSRF_NAME = data.csrf_token_name; CSRF_HASH = data.csrf_hash;
            window.CSRF_TOKEN_NAME = CSRF_NAME; window.CSRF_HASH = CSRF_HASH;
        }
    }

    function csrfData() { return encodeURIComponent(CSRF_NAME) + '=' + encodeURIComponent(CSRF_HASH); }

    function clearErrors() { $('.field-error').addClass('hidden').text(''); $('input,select').removeClass('!border-error'); }

    function showFieldErrors(errors) {
        clearErrors();
        if (errors && typeof errors === 'object') {
            $.each(errors, function (f, msg) {
                $('#err-' + f).text(msg).removeClass('hidden');
                $('#' + f).addClass('!border-error');
            });
        }
    }

    function showAlert(type, message) {
        var $a = $('#wizard-alert');
        $a.removeClass('hidden bg-error-container border-error text-on-error-container bg-secondary-container border-secondary text-on-secondary-container');
        $a.addClass(type === 'error'
            ? 'bg-error-container border-error text-on-error-container'
            : 'bg-secondary-container border-secondary text-on-secondary-container');
        $a.text(message).removeClass('hidden');
        $('html,body').animate({ scrollTop: 0 }, 200);
    }

    function formDataWithCsrf($form) { return $form.serialize() + '&' + csrfData(); }

    /* ═══ STEP 1 ═══════════════════════════════════════════════════════════ */
    var $s1 = $('#step1-form');
    if ($s1.length) {

        /* ── Cropper Instance & Elements ────────────────────────────────── */
        var cropperInstance = null;
        var $fileInput      = $('#student_image_file');
        var $chooseBtn      = $('#btn-choose-photo');
        var $removeBtn      = $('#photo-remove-btn');
        var $photoBtnLabel  = $('#photo-btn-label');
        var $previewImg     = $('#photo-preview-img');
        var $placeholder    = $('#photo-placeholder-icon');
        var $fileNameText   = $('#photo-file-name');
        var $hiddenCropped  = $('#cropped_image_data');
        var $tempPathInput  = $('#photo_temp_path');
        var $dispNameInput  = $('#photo_display_name');

        var $modal          = $('#student-cropper-modal');
        var cropperImgEl    = document.getElementById('student-cropper-image-target');
        var $zoomRange      = $('#student-cropper-zoom-range');
        var $zoomInBtn      = $('#btn-student-cropper-zoom-in');
        var $zoomOutBtn     = $('#btn-student-cropper-zoom-out');
        var $rotateLeftBtn  = $('#btn-student-cropper-rotate-left');
        var $rotateRightBtn = $('#btn-student-cropper-rotate-right');
        var $resetBtn       = $('#btn-student-cropper-reset');
        var $closeBtn       = $('#btn-student-cropper-close');
        var $cancelBtn      = $('#btn-student-cropper-cancel');
        var $applyBtn       = $('#btn-student-cropper-apply');
        var $changeFileBtn  = $('#btn-student-cropper-change-file');

        $chooseBtn.on('click', function () {
            $fileInput.val('');
            $fileInput.trigger('click');
        });

        if ($changeFileBtn.length) {
            $changeFileBtn.on('click', function () {
                $fileInput.val('');
                $fileInput.trigger('click');
            });
        }

        $fileInput.on('change', function () {
            var file = this.files[0];
            if (!file) return;

            $('#err-student_image').addClass('hidden').text('');

            // Format validation
            var allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
            var allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            var ext = file.name.split('.').pop().toLowerCase();

            if (allowedExts.indexOf(ext) === -1 && allowedMimes.indexOf(file.type) === -1) {
                alert('Please select a valid JPG, JPEG, PNG, or WEBP image.');
                $('#err-student_image').text('Please select a valid JPG, JPEG, PNG, or WEBP image.').removeClass('hidden');
                this.value = '';
                return;
            }

            // Size validation: 3 MB
            if (file.size > 3 * 1024 * 1024) {
                alert('Image size must not exceed 3 MB.');
                $('#err-student_image').text('Image size must not exceed 3 MB.').removeClass('hidden');
                this.value = '';
                return;
            }

            if (window.FileReader) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    openStudentCropper(e.target.result, file.name);
                };
                reader.onerror = function () {
                    alert('Failed to read image file. Please try again.');
                };
                reader.readAsDataURL(file);
            }
        });

        function openStudentCropper(imgSrc, originalFileName) {
            if (typeof Cropper === 'undefined') {
                alert('Cropper library is loading. Please refresh the page.');
                return;
            }

            if (cropperInstance) {
                cropperInstance.destroy();
                cropperInstance = null;
            }

            $zoomRange.val(1);
            $modal.removeClass('hidden');

            var initialized = false;
            function triggerInit() {
                if (initialized) return;
                initialized = true;
                initCropperInstance(originalFileName);
            }

            cropperImgEl.onload = triggerInit;
            cropperImgEl.src = imgSrc;

            if (cropperImgEl.complete) {
                triggerInit();
            }
        }
        window.openStudentCropperModal = openStudentCropper;

        function initCropperInstance(originalFileName) {
            if (cropperInstance) return;
            try {
                cropperInstance = new Cropper(cropperImgEl, {
                    aspectRatio: 3 / 4,
                    viewMode: 1,
                    autoCropArea: 0.9,
                    responsive: true,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: false,
                    checkOrientation: true,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false,
                    ready: function () {
                        $zoomRange.val(1);
                    },
                    zoom: function (e) {
                        if (e.detail && typeof e.detail.ratio === 'number') {
                            var r = Math.min(3, Math.max(0.1, parseFloat(e.detail.ratio.toFixed(2))));
                            $zoomRange.val(r);
                        }
                    }
                });
            } catch (ce) {
                console.error('Cropper init error:', ce);
            }
        }

        function closeStudentCropper() {
            $modal.addClass('hidden');
            if (cropperInstance) {
                cropperInstance.destroy();
                cropperInstance = null;
            }
            cropperImgEl.src = '';
            $fileInput.val('');
        }

        $closeBtn.on('click', closeStudentCropper);
        $cancelBtn.on('click', closeStudentCropper);

        // Zoom Range Slider
        $zoomRange.on('input', function () {
            if (!cropperInstance) return;
            var val = parseFloat(this.value);
            if (!isNaN(val) && val >= 0.1 && val <= 3) {
                cropperInstance.zoomTo(val);
            }
        });

        // Zoom In & Out Buttons
        $zoomInBtn.on('click', function () {
            if (!cropperInstance) return;
            cropperInstance.zoom(0.1);
        });
        $zoomOutBtn.on('click', function () {
            if (!cropperInstance) return;
            cropperInstance.zoom(-0.1);
        });

        // Rotate Left & Right Buttons
        $rotateLeftBtn.on('click', function () {
            if (!cropperInstance) return;
            cropperInstance.rotate(-90);
        });
        $rotateRightBtn.on('click', function () {
            if (!cropperInstance) return;
            cropperInstance.rotate(90);
        });

        // Reset Button
        $resetBtn.on('click', function () {
            if (!cropperInstance) return;
            cropperInstance.reset();
            $zoomRange.val(1);
        });

        // Apply Crop Button
        $applyBtn.on('click', function () {
            if (cropperInstance) {
                var canvas = cropperInstance.getCroppedCanvas({
                    width: 600,
                    height: 800,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high'
                });

                if (canvas) {
                    var croppedBase64 = canvas.toDataURL('image/jpeg', 0.92);
                    $hiddenCropped.val(croppedBase64);

                    // Update preview immediately
                    $previewImg.attr('src', croppedBase64).removeClass('hidden');
                    $placeholder.addClass('hidden');
                    $removeBtn.removeClass('hidden');
                    $photoBtnLabel.text('Change Photo');
                    $fileNameText.text('Cropped Student Image').removeClass('hidden');

                    // Send to wizard_photo_upload via AJAX for temp staging
                    $('#photo-upload-progress').removeClass('hidden').addClass('flex');
                    $.ajax({
                        url: BASE_URL + 'students/wizard_photo_upload',
                        method: 'POST',
                        data: {
                            cropped_image_data: croppedBase64,
                            photo_display_name: 'student_profile.jpg',
                            [CSRF_NAME]: CSRF_HASH
                        },
                        dataType: 'json',
                        success: function (r) {
                            refreshCsrf(r);
                            $('#photo-upload-progress').addClass('hidden').removeClass('flex');
                            if (r.success) {
                                $tempPathInput.val(r.temp_path);
                                $dispNameInput.val(r.display_name);
                                if (r.preview_url) {
                                    $previewImg.attr('src', r.preview_url);
                                }
                                $('#err-student_image').addClass('hidden').text('');
                            } else {
                                showAlert('error', r.error || 'Failed to stage cropped image.');
                            }
                        },
                        error: function () {
                            $('#photo-upload-progress').addClass('hidden').removeClass('flex');
                        }
                    });
                }
            }

            closeStudentCropper();
        });

        /* Remove student photo */
        $removeBtn.on('click', function () {
            $tempPathInput.val('');
            $dispNameInput.val('');
            $hiddenCropped.val('');
            $fileInput.val('');
            $previewImg.attr('src', '').addClass('hidden');
            $placeholder.removeClass('hidden');
            $fileNameText.text('').addClass('hidden');
            $removeBtn.addClass('hidden');
            $photoBtnLabel.text('Choose Image');
            $('#err-student_image').addClass('hidden').text('');
        });

        function validateStep1() {
            var ok = true;
            clearErrors();

            if (!$.trim($('#admission_number').val())) {
                $('#err-admission_number').text('Admission Number is required.').removeClass('hidden');
                $('#admission_number').addClass('!border-error');
                ok = false;
            }

            if (!$.trim($('#first_name').val())) {
                $('#err-first_name').text('First Name is required.').removeClass('hidden');
                $('#first_name').addClass('!border-error');
                ok = false;
            }

            var dob = $('#date_of_birth').val();
            if (dob && dob > SERVER_TODAY) {
                $('#err-date_of_birth').text('Date of Birth cannot be a future date.').removeClass('hidden');
                $('#date_of_birth').addClass('!border-error');
                ok = false;
            }

            var pin = $.trim($('#pin_code').val());
            if (pin) {
                var cleanPin = pin.replace(/\s+/g, '');
                if (!/^[1-9][0-9]{5}$/.test(cleanPin)) {
                    $('#err-pin_code').text('Please enter a valid 6-digit Indian PIN code (e.g. 682001).').removeClass('hidden');
                    $('#pin_code').addClass('!border-error');
                    ok = false;
                }
            }

            if (window.PhoneManager && !window.PhoneManager.validateForm($s1)) {
                ok = false;
            }

            return ok;
        }

        $('#pin_code').on('input', function () {
            var pin = $.trim($(this).val());
            if (!pin || /^[1-9][0-9]{5}$/.test(pin.replace(/\s+/g, ''))) {
                $('#err-pin_code').addClass('hidden').text('');
                $(this).removeClass('!border-error');
            }
        });

        $('#date_of_birth').on('change input', function () {
            var dob = $(this).val();
            if (dob && dob > SERVER_TODAY) {
                $('#err-date_of_birth').text('Date of Birth cannot be a future date.').removeClass('hidden');
                $(this).addClass('!border-error');
            } else {
                $('#err-date_of_birth').addClass('hidden').text('');
                $(this).removeClass('!border-error');
            }
        });

        // Initialize phone inputs for Step 1
        if (window.PhoneManager) {
            window.PhoneManager.init($s1);
        }

        $s1.on('submit', function (e) {
            e.preventDefault();
            if (!validateStep1()) {
                showAlert('error', 'Please fix the errors below.');
                return;
            }
            if (window.PhoneManager) {
                window.PhoneManager.prepareSubmit($s1);
            }
            var $btn = $('#btn-step1-next').prop('disabled', true)
                         .html('<span class="material-symbols-outlined text-[18px] animate-spin">autorenew</span>Saving…');
            $('#wizard-alert').addClass('hidden');
            $.ajax({ url: BASE_URL + 'students/wizard_step1', method: 'POST', data: formDataWithCsrf($s1), dataType: 'json',
                success: function (r) {
                    refreshCsrf(r);
                    if (r.success) { window.location.href = r.redirect; }
                    else {
                        $btn.prop('disabled', false).html('<span class="material-symbols-outlined text-[18px]">arrow_forward</span>Next');
                        if (r.errors) { showFieldErrors(r.errors); showAlert('error', 'Please fix the errors below.'); }
                        else { showAlert('error', r.message || 'Validation failed.'); }
                    }
                },
                error: function () {
                    $btn.prop('disabled', false).html('<span class="material-symbols-outlined text-[18px]">arrow_forward</span>Next');
                    showAlert('error', 'Server error. Please try again.');
                }
            });
        });
    }

    /* ═══ STEP 2 ═══════════════════════════════════════════════════════════ */
    var $s2 = $('#step2-form');
    if ($s2.length) {

        /* ── Dynamic Group -> Class Loading ───────────────────────────── */
        $('#academic_group_id').on('change', function () {
            var groupId = $(this).val();
            $('#class_id').val('');
            $('#division_id').html('<option value="">Select Division</option>');
            $('#section_id').html('<option value="">Select Division</option>');

            if (!groupId) {
                $('#class_id option').show();
                return;
            }

            $('#class_id option').each(function () {
                var cGroup = $(this).attr('data-group');
                if (!$(this).val() || cGroup === groupId) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        /* ── Dynamic Class -> Division Loading ─────────────────────────── */
        $('#class_id').on('change', function () {
            var classId = $(this).val();
            var selectedOpt = $(this).find('option:selected');
            var group = selectedOpt.attr('data-group');

            if (group && !$('#academic_group_id').val()) {
                $('#academic_group_id').val(group);
            }

            if (!classId) {
                $('#division_id, #section_id').html('<option value="">Select Division</option>');
                return;
            }

            $.ajax({
                url: BASE_URL + 'students/get_divisions_ajax',
                method: 'POST',
                data: { class_id: classId, [CSRF_NAME]: CSRF_HASH },
                dataType: 'json',
                success: function (r) {
                    refreshCsrf(r);
                    if (r && (r.divisions || r.sections) && (r.divisions || r.sections).length > 0) {
                        var opts = '<option value="">Select Division</option>';
                        (r.divisions || r.sections).forEach(function (sec, idx) {
                            opts += '<option value="' + (sec.division_id || sec.section_id) + '" ' + (idx === 0 ? 'selected' : '') + '>Division ' + $('<div>').text((sec.division_name || sec.section_name)).html() + '</option>';
                        });
                        $('#division_id, #section_id').html(opts);
                    } else {
                        $('#division_id, #section_id').html('<option value="12" selected>Division A</option>');
                    }
                },
                error: function () {
                    $('#division_id, #section_id').html('<option value="12" selected>Division A</option>');
                }
            });
        });

        /* ── No Previous School toggle ─────────────────────────────────── */
        $('#no_previous_school').on('change', function () {
            var $fields = $('#prev-school-fields');
            if (this.checked) {
                $fields.slideUp(200);
            } else {
                $fields.slideDown(200);
            }
        });

        /* ── TC File Upload (fire-and-forget before main submit) ────────── */
        $('#tc_document_file').on('change', function () {
            var file = this.files[0];
            if (!file) return;

            $('#err-tc_document').addClass('hidden').text('');

            // Client-side pre-check
            var ext = file.name.split('.').pop().toLowerCase();
            if (['pdf','jpg','jpeg','png'].indexOf(ext) === -1) {
                showAlert('error', 'TC Document must be a PDF, JPG, JPEG, or PNG file.');
                $('#err-tc_document').text('TC Document must be a PDF, JPG, JPEG, or PNG file.').removeClass('hidden');
                this.value = '';
                return;
            }
            if (file.size > 3 * 1024 * 1024) {
                showAlert('error', 'TC Document must not exceed 3 MB.');
                $('#err-tc_document').text('TC Document must not exceed 3 MB.').removeClass('hidden');
                this.value = '';
                return;
            }

            var fd = new FormData();
            fd.append('tc_document', file);
            fd.append(CSRF_NAME, CSRF_HASH);

            $('#tc-btn-label').text('Uploading…');
            $('#tc-upload-progress').removeClass('hidden');
            $('#tc-file-status').addClass('hidden');

            $.ajax({
                url: BASE_URL + 'students/wizard_tc_upload',
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (r) {
                    refreshCsrf(r);
                    $('#tc-btn-label').text('Change File');
                    $('#tc-upload-progress').addClass('hidden');
                    if (r.success) {
                        $('#tc_temp_path').val(r.temp_path);
                        $('#tc-file-name').text(r.display_name);
                        $('#tc-file-status').removeClass('hidden');
                        $('#err-tc_document').addClass('hidden').text('');
                    } else {
                        showAlert('error', r.error || 'TC Document must be a PDF, JPG, JPEG, or PNG file.');
                        $('#err-tc_document').text(r.error || 'TC Document must be a PDF, JPG, JPEG, or PNG file.').removeClass('hidden');
                        $('#tc-btn-label').text('Choose File');
                        $('#tc_temp_path').val('');
                    }
                },
                error: function () {
                    $('#tc-btn-label').text('Choose File');
                    $('#tc-upload-progress').addClass('hidden');
                    showAlert('error', 'Upload failed. Please check your connection.');
                }
            });
        });

        /* Remove TC file */
        $(document).on('click', '#tc-file-remove', function () {
            $('#tc_temp_path').val('');
            $('#tc-file-status').addClass('hidden');
            $('#tc-btn-label').text('Choose File');
            $('#tc_document_file').val('');
            $('#err-tc_document').addClass('hidden').text('');
        });

        /* ── Dynamic Academic Activity Rows ────────────────────────────── */
        var ACADEMIC_TYPES = <?php echo json_encode($academic_types); ?>;

        function academicRowHtml(idx) {
            var typeOpts = ACADEMIC_TYPES.map(function (t) {
                return '<option value="' + t + '">' + t + '</option>';
            }).join('');
            return '<div class="academic-activity-row grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2 p-3 rounded-lg bg-surface-container-low border border-outline-variant/40">'
                + '<div><label class="block font-label-md text-label-md text-on-surface-variant mb-1">Type</label>'
                + '<select name="academic_activities[' + idx + '][activity_type]" class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary">' + typeOpts + '</select></div>'
                + '<div><label class="block font-label-md text-label-md text-on-surface-variant mb-1">Activity Name <span class="text-error">*</span></label>'
                + '<input type="text" name="academic_activities[' + idx + '][activity_name]" placeholder="Name / Title" class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/></div>'
                + '<div><label class="block font-label-md text-label-md text-on-surface-variant mb-1">Position / Result</label>'
                + '<input type="text" name="academic_activities[' + idx + '][position_result]" placeholder="e.g. 1st" class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/></div>'
                + '<div><label class="block font-label-md text-label-md text-on-surface-variant mb-1">Year</label>'
                + '<input type="number" name="academic_activities[' + idx + '][year]" placeholder="' + CURRENT_YEAR + '" min="1990" max="' + CURRENT_YEAR + '" class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/></div>'
                + '<div class="flex flex-col gap-1"><label class="block font-label-md text-label-md text-on-surface-variant mb-1">Description</label>'
                + '<div class="flex gap-1"><input type="text" name="academic_activities[' + idx + '][description]" placeholder="Brief description" class="flex-1 min-w-0 px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>'
                + '<button type="button" class="remove-activity shrink-0 w-8 h-9 flex items-center justify-center rounded-lg border border-error/30 text-error hover:bg-error-container transition-colors"><span class="material-symbols-outlined text-[16px]">remove</span></button></div></div>'
                + '</div>';
        }

        $('#add-academic-activity').on('click', function () {
            var idx = $('#academic-activities-list .academic-activity-row').length;
            $('#academic-activities-list').append(academicRowHtml(idx));
        });

        $(document).on('click', '.remove-activity', function () {
            var $list = $('#academic-activities-list');
            $(this).closest('.academic-activity-row').remove();
            // Re-index names
            $list.find('.academic-activity-row').each(function (i) {
                $(this).find('[name]').each(function () {
                    this.name = this.name.replace(/academic_activities\[\d+\]/, 'academic_activities[' + i + ']');
                });
            });
        });

        /* ── Dynamic Extracurricular Activity Rows ──────────────────────── */
        var EXTRA_TYPES = <?php echo json_encode($extra_types); ?>;
        var LEVELS      = <?php echo json_encode($levels); ?>;

        function extraRowHtml(idx) {
            var typeOpts  = EXTRA_TYPES.map(function (t) { return '<option value="' + t + '">' + t + '</option>'; }).join('');
            var levelOpts = '<option value="">Select</option>' + LEVELS.map(function (l) { return '<option value="' + l + '">' + l + '</option>'; }).join('');
            return '<div class="extra-activity-row grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-2 p-3 rounded-lg bg-surface-container-low border border-outline-variant/40">'
                + '<div><label class="block font-label-md text-label-md text-on-surface-variant mb-1">Type</label>'
                + '<select name="extracurricular[' + idx + '][activity_type]" class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary">' + typeOpts + '</select></div>'
                + '<div><label class="block font-label-md text-label-md text-on-surface-variant mb-1">Activity Name <span class="text-error">*</span></label>'
                + '<input type="text" name="extracurricular[' + idx + '][activity_name]" placeholder="Name" class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/></div>'
                + '<div><label class="block font-label-md text-label-md text-on-surface-variant mb-1">Level</label>'
                + '<select name="extracurricular[' + idx + '][level]" class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary">' + levelOpts + '</select></div>'
                + '<div><label class="block font-label-md text-label-md text-on-surface-variant mb-1">Position</label>'
                + '<input type="text" name="extracurricular[' + idx + '][position_result]" placeholder="e.g. Winner" class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/></div>'
                + '<div><label class="block font-label-md text-label-md text-on-surface-variant mb-1">Year</label>'
                + '<input type="number" name="extracurricular[' + idx + '][year]" placeholder="' + CURRENT_YEAR + '" min="1990" max="' + CURRENT_YEAR + '" class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/></div>'
                + '<div class="flex flex-col gap-1"><label class="block font-label-md text-label-md text-on-surface-variant mb-1">Description</label>'
                + '<div class="flex gap-1"><input type="text" name="extracurricular[' + idx + '][description]" placeholder="Optional" class="flex-1 min-w-0 px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>'
                + '<button type="button" class="remove-extra shrink-0 w-8 h-9 flex items-center justify-center rounded-lg border border-error/30 text-error hover:bg-error-container transition-colors"><span class="material-symbols-outlined text-[16px]">remove</span></button></div></div>'
                + '</div>';
        }

        $('#add-extra-activity').on('click', function () {
            var idx = $('#extracurricular-list .extra-activity-row').length;
            $('#extracurricular-list').append(extraRowHtml(idx));
        });

        $(document).on('click', '.remove-extra', function () {
            var $list = $('#extracurricular-list');
            $(this).closest('.extra-activity-row').remove();
            $list.find('.extra-activity-row').each(function (i) {
                $(this).find('[name]').each(function () {
                    this.name = this.name.replace(/extracurricular\[\d+\]/, 'extracurricular[' + i + ']');
                });
            });
        });

        /* ── Client-side validation helpers ────────────────────────────── */
        function validateStep2() {
            var ok = true;
            clearErrors();

            if (!$('#class_id').val()) {
                $('#err-class_id').text('Please select a Class.').removeClass('hidden');
                $('#class_id').addClass('!border-error');
                ok = false;
            }

            var noPrev = $('#no_previous_school').is(':checked');
            if (!noPrev) {
                if (!$.trim($('#prev_school_name').val())) {
                    $('#err-prev_school_name').text('Previous School Name is required.').removeClass('hidden');
                    $('#prev_school_name').addClass('!border-error');
                    ok = false;
                }
                if (!$.trim($('#tc_number').val())) {
                    $('#err-tc_number').text('TC Number is required.').removeClass('hidden');
                    $('#tc_number').addClass('!border-error');
                    ok = false;
                }
                if (!$('#tc_temp_path').val()) {
                    $('#err-tc_document').text('TC Document is required.').removeClass('hidden');
                    ok = false;
                }
                var pct = $('#prev_percentage').val();
                if (pct !== '' && (isNaN(parseFloat(pct)) || parseFloat(pct) < 0 || parseFloat(pct) > 100)) {
                    $('#err-prev_percentage').text('Percentage must be between 0 and 100.').removeClass('hidden');
                    $('#prev_percentage').addClass('!border-error');
                    ok = false;
                }
            }
            return ok;
        }

        /* ── Step 2 Submit ─────────────────────────────────────────────── */
        $s2.on('submit', function (e) {
            e.preventDefault();
            if (!validateStep2()) { showAlert('error', 'Please fix the errors below.'); return; }

            var $btn = $('#btn-step2-next').prop('disabled', true)
                         .html('<span class="material-symbols-outlined text-[18px] animate-spin">autorenew</span>Saving…');
            $('#wizard-alert').addClass('hidden');

            $.ajax({ url: BASE_URL + 'students/wizard_step2', method: 'POST', data: formDataWithCsrf($s2), dataType: 'json',
                success: function (r) {
                    refreshCsrf(r);
                    if (r.success) { window.location.href = r.redirect; }
                    else if (r.redirect) { window.location.href = r.redirect; }
                    else {
                        $btn.prop('disabled', false).html('<span class="material-symbols-outlined text-[18px]">arrow_forward</span>Next');
                        if (r.errors) { showFieldErrors(r.errors); showAlert('error', 'Please fix the errors below.'); }
                        else { showAlert('error', r.message || 'Validation failed.'); }
                    }
                },
                error: function () {
                    $btn.prop('disabled', false).html('<span class="material-symbols-outlined text-[18px]">arrow_forward</span>Next');
                    showAlert('error', 'Server error. Please try again.');
                }
            });
        });
    } // end step 2

    /* ═══ STEP 3 ═══════════════════════════════════════════════════════════ */
    var $s3      = $('#step3-form');
    var _saved3  = false;

    if ($s3.length) {
        // Initialize phone inputs for Step 3
        if (window.PhoneManager) {
            window.PhoneManager.init($s3);
        }

        function validateStep3() {
            var ok = true;
            clearErrors();

            if (!$.trim($('#guardian_name').val())) {
                $('#err-guardian_name').text('Guardian name is required.').removeClass('hidden');
                $('#guardian_name').addClass('!border-error');
                ok = false;
            }

            if (window.PhoneManager && !window.PhoneManager.validateForm($s3)) {
                ok = false;
            }

            return ok;
        }

        $s3.off('submit').on('submit', function (e) {
            e.preventDefault();
            if (_saved3) return;
            if (!validateStep3()) { showAlert('error', 'Please fix the errors below.'); return; }

            // Normalize all phone fields before POST
            if (window.PhoneManager) {
                window.PhoneManager.prepareSubmit($s3);
            }

            _saved3 = true;
            var $btn = $('#btn-save-student').prop('disabled', true)
                         .html('<span class="material-symbols-outlined text-[18px] animate-spin">autorenew</span>Saving…');
            $('#wizard-alert').addClass('hidden');
            $.ajax({ url: BASE_URL + 'students/wizard_save', method: 'POST', data: formDataWithCsrf($s3), dataType: 'json',
                success: function (r) {
                    refreshCsrf(r);
                    if (r.success && r.redirect) {
                        window.location.href = r.redirect;
                    } else if (r.redirect) {
                        window.location.href = r.redirect;
                    } else {
                        _saved3 = false;
                        $btn.prop('disabled', false).html('<span class="material-symbols-outlined text-[18px]">check</span>Save Student');
                        if (r.errors) { showFieldErrors(r.errors); showAlert('error', 'Please fix the errors below.'); }
                        else { showAlert('error', r.message || 'Failed to save. Please try again.'); }
                    }
                },
                error: function () {
                    _saved3 = false;
                    $btn.prop('disabled', false).html('<span class="material-symbols-outlined text-[18px]">check</span>Save Student');
                    showAlert('error', 'Server error. Please try again.');
                }
            });
        });
    }

}(jQuery));
</script>
