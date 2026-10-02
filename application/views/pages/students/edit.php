<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
  $fullName = school_student_name($student->first_name, $student->middle_name ?? '', $student->last_name);
  $nameParts = explode(' ', $fullName);
  $initials = '';
  foreach ($nameParts as $np) { if (!empty($np)) $initials .= strtoupper($np[0]); }
  if (strlen($initials) > 2) $initials = substr($initials, 0, 2);
  if (empty($initials)) $initials = 'ST';
  $hasPhoto = (!empty($student->photo) && file_exists(FCPATH . 'uploads/students/' . $student->photo));

  $academic_types = ['Achievement','Olympiad','Competition','Scholarship','Project','Certification','Award','Other Academic'];
  $extra_types    = ['Sports','Arts','Music','Dance','Club','Cultural','Competition','Other'];
  $levels         = ['School','District','State','National','International'];

  // Helper to extract value prioritizing POST, then DB student/record, then fallback
  function ev($val, $fallback = '') {
      return htmlspecialchars((string)($val !== NULL && $val !== '' ? $val : $fallback), ENT_QUOTES, 'UTF-8');
  }

  $has_prev_school = !empty($prev_school) && ($prev_school->status == 1);
  $no_prev_school_checked = ($this->input->post('no_previous_school') !== NULL)
      ? ($this->input->post('no_previous_school') == '1')
      : (!$has_prev_school);
?>

<?php echo form_open_multipart('students/edit/' . $student_id, array('id' => 'student-edit-form')); ?>
  <!-- ═══ HEADER ══════════════════════════════════════════════════════════ -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
      <h2 class="font-headline-md text-headline-md text-on-surface">Edit Student</h2>
      <p class="text-body-md font-body-md text-on-surface-variant mt-1">Update student personal, profile image, academic, parent, previous school, and activity details.</p>
    </div>
    <div class="flex items-center gap-2 shrink-0">
      <a href="<?php echo site_url('students/profile/' . $student_id); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
        <span class="material-symbols-outlined text-[18px]">close</span>Cancel
      </a>
      <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer font-medium">
        <span class="material-symbols-outlined text-[18px]">check</span>Update Student
      </button>
    </div>
  </div>

  <!-- ═══ ALERT / ERROR MESSAGES ══════════════════════════════════════════ -->
  <?php if (validation_errors() || !empty($photo_error) || !empty($tc_error)): ?>
    <div class="p-4 mb-5 rounded-xl bg-error-container/30 border border-error/30 text-error text-body-md space-y-1">
      <?php echo validation_errors(); ?>
      <?php if (!empty($photo_error)): ?>
        <div>• <?php echo html_escape($photo_error); ?></div>
      <?php endif; ?>
      <?php if (!empty($tc_error)): ?>
        <div>• <?php echo html_escape($tc_error); ?></div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- ══════════════════════════════════════════════════════════════════════
       SECTION 1: STUDENT DETAILS
  ═════════════════════════════════════════════════════════════════════════ -->
  <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-5">
    <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/50">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-secondary text-[22px]">person</span>
        <h3 class="font-headline-md text-headline-md text-on-surface font-semibold text-base">Student Details</h3>
      </div>
    </div>
    <div class="p-5">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

        <!-- Student Profile Image (3:4 Fixed Ratio) -->
        <div class="sm:col-span-2 lg:col-span-3">
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">
            Student Profile Image
            <span class="text-on-surface-variant font-normal text-xs">(3:4 Portrait · JPG, JPEG, PNG, WEBP · Max 3 MB)</span>
          </label>

          <input type="hidden" name="cropped_image_data" id="cropped_image_data" value=""/>
          <input type="hidden" name="remove_photo" id="remove_photo" value="0"/>

          <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 p-4 rounded-xl border border-outline-variant/60 bg-surface-container-low">
            <!-- 3:4 Aspect Ratio Preview Box (80px x 107px) -->
            <div id="photo-preview-container" class="relative w-20 h-[107px] rounded-xl overflow-hidden border border-outline-variant/80 bg-surface-container-lowest flex items-center justify-center shrink-0 shadow-xs">
              <img id="photo-preview-img" 
                   src="<?php echo $hasPhoto ? base_url('uploads/students/' . $student->photo) : ''; ?>" 
                   alt="<?php echo html_escape($fullName); ?>" 
                   class="w-full h-full object-cover <?php echo $hasPhoto ? '' : 'hidden'; ?>"/>
              
              <div id="photo-placeholder-avatar" class="w-full h-full bg-primary text-white flex flex-col items-center justify-center font-bold <?php echo $hasPhoto ? 'hidden' : ''; ?>">
                <span class="text-xl"><?php echo html_escape($initials); ?></span>
                <span class="text-[9px] font-medium opacity-70">3:4</span>
              </div>
            </div>

            <!-- Upload Controls & Status -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 flex-wrap mb-1.5">
                <button type="button" id="btn-choose-photo" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-2xs cursor-pointer">
                  <span class="material-symbols-outlined text-[17px]">photo_camera</span>
                  <span id="photo-btn-label"><?php echo $hasPhoto ? 'Change Photo' : 'Upload Photo'; ?></span>
                </button>
                <input type="file" id="student_image_file" name="student_image" accept="image/jpeg,image/png,image/jpg,image/webp,.jpg,.jpeg,.png,.webp" class="hidden"/>
                
                <button type="button" id="photo-remove-btn" class="inline-flex items-center gap-1 px-3 py-2 rounded-lg border border-error/30 text-error hover:bg-error-container/40 text-label-md transition-colors cursor-pointer <?php echo $hasPhoto ? '' : 'hidden'; ?>">
                  <span class="material-symbols-outlined text-[16px]">delete</span>
                  <span>Remove</span>
                </button>
              </div>
              <p id="photo-file-status" class="text-xs text-on-surface-variant">
                <?php echo $hasPhoto ? 'Current photo loaded. You can change or remove it.' : 'No photo uploaded. Select an image to crop.'; ?>
              </p>
              <p class="field-error text-error text-[11px] mt-1 hidden" id="err-student_image"></p>
            </div>
          </div>
        </div>

        <!-- Admission Number (READ ONLY) -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">
            Admission Number <span class="text-error">*</span>
          </label>
          <input type="text" name="admission_number" value="<?php echo html_escape($student->admission_number); ?>" readonly class="w-full px-3 py-2.5 rounded-lg border border-outline-variant/60 bg-surface-container-low text-on-surface-variant font-medium cursor-not-allowed select-none shadow-2xs"/>
          <p class="text-[11px] text-on-surface-variant/80 mt-1">Admission number cannot be modified in edit mode.</p>
        </div>

        <!-- First Name -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">First Name <span class="text-error">*</span></label>
          <input type="text" id="first_name" name="first_name" required value="<?php echo ev($this->input->post('first_name'), $student->first_name); ?>" placeholder="e.g. Aarav" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
        </div>

        <!-- Middle Name -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Middle Name</label>
          <input type="text" name="middle_name" value="<?php echo ev($this->input->post('middle_name'), $student->middle_name ?? ''); ?>" placeholder="e.g. Kumar" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
        </div>

        <!-- Last Name -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Last Name</label>
          <input type="text" name="last_name" value="<?php echo ev($this->input->post('last_name'), $student->last_name); ?>" placeholder="e.g. Nair" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
        </div>

        <!-- Gender -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Gender</label>
          <?php $cur_gender = $this->input->post('gender') ?: $student->gender; ?>
          <select name="gender" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary">
            <option value="Male" <?php echo ($cur_gender === 'Male') ? 'selected' : ''; ?>>Male</option>
            <option value="Female" <?php echo ($cur_gender === 'Female') ? 'selected' : ''; ?>>Female</option>
            <option value="Other" <?php echo ($cur_gender === 'Other') ? 'selected' : ''; ?>>Other</option>
          </select>
        </div>

        <!-- Date of Birth -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Date of Birth</label>
          <input type="date" id="date_of_birth" name="date_of_birth" max="<?php echo date('Y-m-d'); ?>" value="<?php echo ev($this->input->post('date_of_birth'), $student->date_of_birth); ?>" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
          <p class="field-error text-error text-[11px] mt-1 hidden" id="err-date_of_birth"></p>
        </div>

        <!-- Blood Group -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Blood Group</label>
          <?php $cur_bg = $this->input->post('blood_group') ?: $student->blood_group; ?>
          <select name="blood_group" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary">
            <option value="">Select Blood Group</option>
            <?php foreach (array('A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-') as $bg): ?>
              <option value="<?php echo $bg; ?>" <?php echo ($cur_bg === $bg) ? 'selected' : ''; ?>><?php echo $bg; ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Nationality -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Nationality</label>
          <input type="text" name="nationality" value="<?php echo ev($this->input->post('nationality'), $student->nationality ?: 'Indian'); ?>" placeholder="e.g. Indian" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
        </div>

        <!-- Religion -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Religion</label>
          <input type="text" name="religion" value="<?php echo ev($this->input->post('religion'), $student->religion ?? ''); ?>" placeholder="e.g. Hindu, Christian, Muslim" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
        </div>

        <!-- Student Phone -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Student Phone <span class="text-xs text-on-surface-variant font-normal">(Optional)</span></label>
          <?php $edit_sp = Phone_validator::parse(ev($this->input->post('student_phone'), $student->student_phone ?? '')); ?>
          <input type="tel" id="student_phone" name="student_phone" data-phone-field="true" data-default-country="<?php echo strtolower($edit_sp['country'] ?: 'in'); ?>" value="<?php echo html_escape($edit_sp['national_number']); ?>" placeholder="98470 11223" class="phone-input w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
          <p class="field-error text-error text-[11px] mt-1 hidden" id="err-student_phone"></p>
        </div>

        <!-- Student Email -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Student Email <span class="text-xs text-on-surface-variant font-normal">(Optional)</span></label>
          <input type="text" id="student_email" name="student_email" value="<?php echo ev($this->input->post('student_email'), $student->student_email ?? ''); ?>" placeholder="e.g. student@school.edu" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
          <p class="field-error text-error text-[11px] mt-1 hidden" id="err-student_email"></p>
        </div>

        <!-- Student Address Section -->
        <div class="sm:col-span-2 lg:col-span-3 pt-4 border-t border-outline-variant/40 mt-1">
          <div class="flex items-center gap-2 mb-3">
            <span class="material-symbols-outlined text-secondary text-[20px]">home_pin</span>
            <h4 class="font-headline-md text-headline-md text-on-surface font-semibold text-sm">Student Address</h4>
            <span class="text-xs text-on-surface-variant font-normal">(Optional)</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1.5">House Name / Flat / Building</label>
              <input type="text" id="house_name" name="house_name" value="<?php echo ev($this->input->post('house_name'), $student->house_name ?? ''); ?>" placeholder="e.g. Rose Villa, Flat 4B" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1.5">Street / Locality</label>
              <input type="text" id="street" name="street" value="<?php echo ev($this->input->post('street'), $student->street ?? ''); ?>" placeholder="e.g. MG Road, Near City Hospital" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1.5">City / Town</label>
              <input type="text" id="city" name="city" value="<?php echo ev($this->input->post('city'), $student->city ?? ''); ?>" placeholder="e.g. Kochi" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1.5">District</label>
              <input type="text" id="district" name="district" value="<?php echo ev($this->input->post('district'), $student->district ?? ''); ?>" placeholder="e.g. Ernakulam" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1.5">State</label>
              <input type="text" id="state" name="state" value="<?php echo ev($this->input->post('state'), $student->state ?: 'Kerala'); ?>" placeholder="e.g. Kerala" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
            </div>
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1.5">PIN Code</label>
              <input type="text" id="pin_code" name="pin_code" maxlength="6" value="<?php echo ev($this->input->post('pin_code'), $student->pin_code ?? ''); ?>" placeholder="e.g. 682001" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- ══════════════════════════════════════════════════════════════════════
       SECTION 2: ACADEMIC DETAILS
  ═════════════════════════════════════════════════════════════════════════ -->
  <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-5">
    <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/50">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-secondary text-[22px]">school</span>
        <h3 class="font-headline-md text-headline-md text-on-surface font-semibold text-base">Academic Details</h3>
      </div>
      <?php if (!empty($student->year_name)): ?>
      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary-container text-on-secondary-container text-[12px] font-semibold">
        <span class="material-symbols-outlined text-[14px]">calendar_today</span><?php echo html_escape($student->year_name); ?>
      </span>
      <?php endif; ?>
    </div>
    <div class="p-5">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">

        <!-- Academic Year -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Academic Year</label>
          <input type="hidden" name="academic_year_id" value="<?php echo (int)($this->input->post('academic_year_id') ?: $student->academic_year_id); ?>"/>
          <div class="px-3 py-2.5 rounded-lg border border-outline-variant/40 bg-surface-container-low text-body-md text-on-surface-variant font-medium">
            <?php echo !empty($student->year_name) ? html_escape($student->year_name) : 'Current Year'; ?>
          </div>
        </div>

        <!-- Academic Group -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Department / Group</label>
          <?php $cur_grp = $this->input->post('academic_group_id') ?: ($student->academic_group_id ?? ''); ?>
          <select id="edit_academic_group_id" name="academic_group_id" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary">
            <option value="">All Department / Groups</option>
            <?php if (!empty($groups)): foreach ($groups as $grp): ?>
              <option value="<?php echo $grp->academic_group_id; ?>" <?php echo (!empty($cur_grp) && $cur_grp == $grp->academic_group_id) ? 'selected' : ''; ?>><?php echo html_escape($grp->group_name); ?></option>
            <?php endforeach; endif; ?>
          </select>
        </div>

        <!-- Class * -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Class <span class="text-error">*</span></label>
          <?php $cur_cls = $this->input->post('class_id') ?: $student->class_id; ?>
          <select id="edit_class_id" name="class_id" required class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary">
            <option value="">Select Class</option>
            <?php foreach ($classes as $cls): ?>
              <option value="<?php echo $cls->class_id; ?>" data-group="<?php echo (int)($cls->academic_group_id ?? 0); ?>" <?php echo ($cur_cls == $cls->class_id) ? 'selected' : ''; ?>><?php echo html_escape($cls->class_name); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Division -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Division</label>
          <?php $cur_div = $this->input->post('division_id') !== NULL ? $this->input->post('division_id') : ($student->division_id ?? $student->section_id ?? 0); ?>
          <select id="edit_division_id" name="division_id" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary">
            <option value="">Select Division</option>
            <?php 
              $div_list = !empty($divisions) ? $divisions : (!empty($sections) ? $sections : []);
              foreach ($div_list as $sec): 
                $sec_cls_id = (int)($sec->class_id ?? 0);
                if ($sec_cls_id && $sec_cls_id !== (int)$cur_cls) continue;
                $div_id = $sec->division_id ?? $sec->section_id;
                $div_name = $sec->division_name ?? $sec->section_name ?? '';
                $display_name = (stripos($div_name, 'division') === false) ? 'Division ' . $div_name : $div_name;
            ?>
              <option value="<?php echo $div_id; ?>" <?php echo ($cur_div == $div_id) ? 'selected' : ''; ?>><?php echo html_escape($display_name); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Roll Number -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Roll Number</label>
          <input type="text" name="roll_number" value="<?php echo ev($this->input->post('roll_number'), $student->roll_number); ?>" placeholder="e.g. 15" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
        </div>

      </div>
    </div>
  </div>

  <!-- ══════════════════════════════════════════════════════════════════════
       SECTION 3: PARENT / GUARDIAN
  ═════════════════════════════════════════════════════════════════════════ -->
  <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-5">
    <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/50">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-secondary text-[22px]">family_restroom</span>
        <h3 class="font-headline-md text-headline-md text-on-surface font-semibold text-base">Parent / Guardian</h3>
      </div>
    </div>
    <div class="p-5">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        <!-- Guardian Name * -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Guardian Name <span class="text-error">*</span></label>
          <input type="text" id="guardian_name" name="guardian_name" required value="<?php echo ev($this->input->post('guardian_name'), $student->guardian_name); ?>" placeholder="e.g. Suresh Nair" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
        </div>

        <!-- Guardian Relation -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Relation</label>
          <?php $cur_rel = $this->input->post('guardian_relation') ?: ($student->guardian_relation ?: 'Father'); ?>
          <select name="guardian_relation" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary">
            <?php foreach (['Father','Mother','Grandfather','Grandmother','Uncle','Aunt','Guardian'] as $rel): ?>
              <option value="<?php echo $rel; ?>" <?php echo ($cur_rel === $rel) ? 'selected' : ''; ?>><?php echo $rel; ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Guardian Phone * (Mandatory contact number) -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Guardian Phone <span class="text-error">*</span></label>
          <?php $edit_gp = Phone_validator::parse(ev($this->input->post('guardian_phone'), $student->guardian_phone)); ?>
          <input type="tel" id="guardian_phone" name="guardian_phone" required data-phone-field="true" data-required="true" data-default-country="<?php echo strtolower($edit_gp['country'] ?: 'in'); ?>" value="<?php echo html_escape($edit_gp['national_number']); ?>" placeholder="98470 11223" class="phone-input w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
          <p class="field-error text-error text-[11px] mt-1 hidden" id="err-guardian_phone"></p>
        </div>

        <!-- Father Phone -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Father Phone <span class="text-xs text-on-surface-variant font-normal">(Optional)</span></label>
          <?php $edit_fp = Phone_validator::parse(ev($this->input->post('father_phone'), $student->father_phone ?? '')); ?>
          <input type="tel" id="father_phone" name="father_phone" data-phone-field="true" data-default-country="<?php echo strtolower($edit_fp['country'] ?: 'in'); ?>" value="<?php echo html_escape($edit_fp['national_number']); ?>" placeholder="98470 11223" class="phone-input w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
          <p class="field-error text-error text-[11px] mt-1 hidden" id="err-father_phone"></p>
        </div>

        <!-- Mother Phone -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Mother Phone <span class="text-xs text-on-surface-variant font-normal">(Optional)</span></label>
          <?php $edit_mp = Phone_validator::parse(ev($this->input->post('mother_phone'), $student->mother_phone ?? '')); ?>
          <input type="tel" id="mother_phone" name="mother_phone" data-phone-field="true" data-default-country="<?php echo strtolower($edit_mp['country'] ?: 'in'); ?>" value="<?php echo html_escape($edit_mp['national_number']); ?>" placeholder="98470 11223" class="phone-input w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
          <p class="field-error text-error text-[11px] mt-1 hidden" id="err-mother_phone"></p>
        </div>

        <!-- Emergency Contact -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Emergency Contact <span class="text-xs text-on-surface-variant font-normal">(Optional)</span></label>
          <?php $edit_ec = Phone_validator::parse(ev($this->input->post('emergency_contact'), $student->emergency_contact ?? '')); ?>
          <input type="tel" id="emergency_contact" name="emergency_contact" data-phone-field="true" data-default-country="<?php echo strtolower($edit_ec['country'] ?: 'in'); ?>" value="<?php echo html_escape($edit_ec['national_number']); ?>" placeholder="98470 11223" class="phone-input w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
          <p class="field-error text-error text-[11px] mt-1 hidden" id="err-emergency_contact"></p>
        </div>

        <!-- Guardian Email -->
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Guardian Email</label>
          <input type="email" id="guardian_email" name="guardian_email" value="<?php echo ev($this->input->post('guardian_email'), $student->guardian_email ?? ''); ?>" placeholder="parent@example.com" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
        </div>

        <!-- Address -->
        <div class="sm:col-span-2">
          <label class="block font-label-md text-label-md text-on-surface mb-1.5">Address</label>
          <input type="text" name="address" value="<?php echo ev($this->input->post('address'), $student->address ?? ''); ?>" placeholder="House name, Place, District, PIN" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
        </div>

      </div>
    </div>
  </div>

  <!-- ══════════════════════════════════════════════════════════════════════
       SECTION 4: PREVIOUS SCHOOL INFORMATION & TC
  ═════════════════════════════════════════════════════════════════════════ -->
  <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-5">
    <div class="px-5 py-4 border-b border-outline-variant/50 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-secondary text-[22px]">history_edu</span>
        <h3 class="font-headline-md text-headline-md text-on-surface font-semibold text-base">Previous School Information</h3>
      </div>
    </div>
    <div class="p-5">
      <!-- No Previous School checkbox -->
      <label class="flex items-center gap-2.5 mb-5 cursor-pointer w-fit">
        <input type="checkbox" id="no_previous_school" name="no_previous_school" value="1"
               <?php echo $no_prev_school_checked ? 'checked' : ''; ?>
               class="w-4 h-4 rounded border-outline-variant accent-secondary cursor-pointer"/>
        <span class="text-body-md font-body-md text-on-surface font-medium">No Previous School</span>
        <span class="text-body-md text-on-surface-variant">(e.g. LKG/UKG first admission)</span>
      </label>

      <!-- Previous School Fields -->
      <div id="prev-school-fields" class="<?php echo $no_prev_school_checked ? 'hidden' : ''; ?>">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

          <div class="sm:col-span-2">
            <label class="block font-label-md text-label-md text-on-surface mb-1.5">Previous School Name <span class="text-error">*</span></label>
            <input type="text" id="prev_school_name" name="prev_school_name" value="<?php echo ev($this->input->post('prev_school_name'), $prev_school->school_name ?? ''); ?>" placeholder="e.g. St. Mary's Higher Secondary School"
                   class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
            <p class="field-error text-error text-[11px] mt-1 hidden" id="err-prev_school_name"></p>
          </div>

          <div class="sm:col-span-2">
            <label class="block font-label-md text-label-md text-on-surface mb-1.5">Previous School Address</label>
            <input type="text" name="prev_school_address" value="<?php echo ev($this->input->post('prev_school_address'), $prev_school->school_address ?? ''); ?>" placeholder="School address"
                   class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1.5">Board / Affiliation</label>
            <input type="text" id="prev_school_board" name="prev_school_board" value="<?php echo ev($this->input->post('prev_school_board'), $prev_school->school_board ?? ''); ?>" placeholder="e.g. CBSE, ICSE, State Board"
                   class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1.5">Previous Class / Grade</label>
            <input type="text" id="prev_class" name="prev_class" value="<?php echo ev($this->input->post('prev_class'), $prev_school->previous_class ?? ''); ?>" placeholder="e.g. Grade 9, Class X"
                   class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1.5">Previous Academic Year</label>
            <input type="text" id="prev_academic_year" name="prev_academic_year" value="<?php echo ev($this->input->post('prev_academic_year'), $prev_school->previous_academic_year ?? ''); ?>" placeholder="e.g. 2025-2026"
                   class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1.5">Date of Leaving</label>
            <input type="date" name="date_of_leaving" value="<?php echo ev($this->input->post('date_of_leaving'), $prev_school->date_of_leaving ?? ''); ?>"
                   class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary"/>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1.5">Previous Percentage (%)</label>
            <input type="number" id="prev_percentage" name="prev_percentage" min="0" max="100" step="0.01"
                   value="<?php echo ev($this->input->post('prev_percentage'), $prev_school->previous_percentage ?? ''); ?>" placeholder="e.g. 82.50"
                   class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
            <p class="field-error text-error text-[11px] mt-1 hidden" id="err-prev_percentage"></p>
          </div>

          <div>
            <label class="block font-label-md text-label-md text-on-surface mb-1.5">TC Number <span class="text-error">*</span></label>
            <input type="text" id="tc_number" name="tc_number" value="<?php echo ev($this->input->post('tc_number'), $prev_school->tc_number ?? ''); ?>" placeholder="e.g. TC/2025/0042"
                   class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
            <p class="field-error text-error text-[11px] mt-1 hidden" id="err-tc_number"></p>
          </div>

          <div class="sm:col-span-2">
            <label class="block font-label-md text-label-md text-on-surface mb-1.5">Reason for Leaving</label>
            <input type="text" name="reason_for_leaving" value="<?php echo ev($this->input->post('reason_for_leaving'), $prev_school->reason_for_leaving ?? ''); ?>" placeholder="e.g. Relocated, Seeking better opportunities"
                   class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
          </div>

          <!-- TC Document Upload & Preview -->
          <div class="sm:col-span-2">
            <label class="block font-label-md text-label-md text-on-surface mb-1.5">
              TC Document
              <span class="text-on-surface-variant font-normal text-xs">(PDF, JPG, JPEG, PNG · Max 10 MB)</span>
            </label>

            <div class="p-4 rounded-xl border border-outline-variant/60 bg-surface-container-low flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-surface-container-highest flex items-center justify-center text-on-surface-variant shrink-0">
                  <span class="material-symbols-outlined text-[24px]">description</span>
                </div>
                <div>
                  <?php if (!empty($tc_document) && !empty($tc_document->file_path)): ?>
                    <p class="font-medium text-body-md text-on-surface"><?php echo html_escape($tc_document->document_name ?: 'Transfer Certificate'); ?></p>
                    <p class="text-xs text-on-surface-variant mt-0.5">
                      <a href="<?php echo base_url($tc_document->file_path); ?>" target="_blank" class="inline-flex items-center gap-1 text-primary hover:underline">
                        <span class="material-symbols-outlined text-[14px]">visibility</span>View / Download Existing Document
                      </a>
                    </p>
                  <?php else: ?>
                    <p class="font-medium text-body-md text-on-surface">No TC Document Uploaded</p>
                    <p class="text-xs text-on-surface-variant mt-0.5">Attach the student's transfer certificate if available.</p>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Upload / Replace controls -->
              <div class="flex items-center gap-2 flex-wrap">
                <label for="tc_document_file" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-outline-variant text-on-surface-variant text-label-md hover:bg-surface-container-high cursor-pointer transition-colors bg-surface-container-lowest shadow-2xs">
                  <span class="material-symbols-outlined text-[16px]">upload_file</span>
                  <span id="tc-btn-label"><?php echo !empty($tc_document) ? 'Replace File' : 'Choose File'; ?></span>
                </label>
                <input type="file" id="tc_document_file" name="tc_document" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" class="hidden"/>
                <span id="tc-selected-name" class="text-xs text-on-surface font-medium hidden"></span>
              </div>
            </div>
            <p class="field-error text-error text-[11px] mt-1 hidden" id="err-tc_document"></p>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- ══════════════════════════════════════════════════════════════════════
       SECTION 5: ACADEMIC ACHIEVEMENTS & ACTIVITIES
  ═════════════════════════════════════════════════════════════════════════ -->
  <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-5">
    <div class="px-5 py-4 border-b border-outline-variant/50">
      <h3 class="font-headline-md text-headline-md text-on-surface font-semibold text-base">Academic Achievements &amp; Activities</h3>
      <p class="text-body-md text-on-surface-variant mt-0.5 text-xs">Achievements, Olympiads, Competitions, Scholarships, Projects, Certifications, Awards</p>
    </div>
    <div class="p-5">
      <div id="academic-activities-list" class="flex flex-col gap-3 mb-4">
        <?php
        $rows = !empty($saved_activities) ? $saved_activities : array();
        if (empty($rows)) {
            $rows = array((object)array('activity_type' => 'Achievement', 'activity_name' => '', 'position_result' => '', 'year' => date('Y'), 'description' => ''));
        }
        foreach ($rows as $i => $act):
          $a_type = is_object($act) ? ($act->activity_type ?? 'Achievement') : ($act['activity_type'] ?? 'Achievement');
          $a_name = is_object($act) ? ($act->activity_name ?? '') : ($act['activity_name'] ?? '');
          $a_pos  = is_object($act) ? ($act->position_result ?? '') : ($act['position_result'] ?? '');
          $a_year = is_object($act) ? ($act->year ?? '') : ($act['year'] ?? '');
          $a_desc = is_object($act) ? ($act->description ?? '') : ($act['description'] ?? '');
        ?>
        <div class="academic-activity-row grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2 p-3 rounded-lg bg-surface-container-low border border-outline-variant/40 relative">
          <div>
            <label class="block font-label-md text-label-md text-on-surface-variant mb-1 text-xs">Type</label>
            <select name="academic_activities[<?php echo $i; ?>][activity_type]" class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary">
              <?php foreach ($academic_types as $t): ?>
                <option value="<?php echo $t; ?>" <?php echo ($a_type === $t) ? 'selected' : ''; ?>><?php echo $t; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="block font-label-md text-label-md text-on-surface-variant mb-1 text-xs">Activity Name</label>
            <input type="text" name="academic_activities[<?php echo $i; ?>][activity_name]" value="<?php echo html_escape($a_name); ?>" placeholder="Name / Title"
                   class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
          </div>
          <div>
            <label class="block font-label-md text-label-md text-on-surface-variant mb-1 text-xs">Position / Result</label>
            <input type="text" name="academic_activities[<?php echo $i; ?>][position_result]" value="<?php echo html_escape($a_pos); ?>" placeholder="e.g. 1st, Gold"
                   class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
          </div>
          <div>
            <label class="block font-label-md text-label-md text-on-surface-variant mb-1 text-xs">Year</label>
            <input type="number" name="academic_activities[<?php echo $i; ?>][year]" value="<?php echo html_escape($a_year); ?>" placeholder="<?php echo date('Y'); ?>" min="1990" max="<?php echo date('Y'); ?>"
                   class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
          </div>
          <div class="flex flex-col gap-1">
            <label class="block font-label-md text-label-md text-on-surface-variant mb-1 text-xs">Description</label>
            <div class="flex gap-1">
              <input type="text" name="academic_activities[<?php echo $i; ?>][description]" value="<?php echo html_escape($a_desc); ?>" placeholder="Brief description"
                     class="flex-1 min-w-0 px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
              <button type="button" class="remove-activity shrink-0 w-8 h-9 flex items-center justify-center rounded-lg border border-error/30 text-error hover:bg-error-container transition-colors cursor-pointer" title="Remove">
                <span class="material-symbols-outlined text-[16px]">remove</span>
              </button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <button type="button" id="add-academic-activity" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-secondary text-secondary text-label-md hover:bg-secondary-container transition-colors cursor-pointer font-medium text-xs">
        <span class="material-symbols-outlined text-[16px]">add</span>Add Academic Activity
      </button>
    </div>
  </div>

  <!-- ══════════════════════════════════════════════════════════════════════
       SECTION 6: CO-CURRICULAR & EXTRACURRICULAR ACTIVITIES
  ═════════════════════════════════════════════════════════════════════════ -->
  <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-5">
    <div class="px-5 py-4 border-b border-outline-variant/50">
      <h3 class="font-headline-md text-headline-md text-on-surface font-semibold text-base">Co-curricular &amp; Extracurricular Activities</h3>
      <p class="text-body-md text-on-surface-variant mt-0.5 text-xs">Sports, Arts, Music, Dance, Clubs, Cultural Activities</p>
    </div>
    <div class="p-5">
      <div id="extracurricular-list" class="flex flex-col gap-3 mb-4">
        <?php
        $xrows = !empty($saved_extracurricular) ? $saved_extracurricular : array();
        if (empty($xrows)) {
            $xrows = array((object)array('activity_type' => 'Sports', 'activity_name' => '', 'level' => '', 'position_result' => '', 'year' => date('Y'), 'description' => ''));
        }
        foreach ($xrows as $j => $xact):
          $x_type  = is_object($xact) ? ($xact->activity_type ?? 'Sports') : ($xact['activity_type'] ?? 'Sports');
          $x_name  = is_object($xact) ? ($xact->activity_name ?? '') : ($xact['activity_name'] ?? '');
          $x_level = is_object($xact) ? ($xact->level ?? '') : ($xact['level'] ?? '');
          $x_pos   = is_object($xact) ? ($xact->position_result ?? '') : ($xact['position_result'] ?? '');
          $x_year  = is_object($xact) ? ($xact->year ?? '') : ($xact['year'] ?? '');
          $x_desc  = is_object($xact) ? ($xact->description ?? '') : ($xact['description'] ?? '');
        ?>
        <div class="extra-activity-row grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-2 p-3 rounded-lg bg-surface-container-low border border-outline-variant/40 relative">
          <div>
            <label class="block font-label-md text-label-md text-on-surface-variant mb-1 text-xs">Type</label>
            <select name="extracurricular[<?php echo $j; ?>][activity_type]" class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary">
              <?php foreach ($extra_types as $t): ?>
                <option value="<?php echo $t; ?>" <?php echo ($x_type === $t) ? 'selected' : ''; ?>><?php echo $t; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="block font-label-md text-label-md text-on-surface-variant mb-1 text-xs">Activity Name</label>
            <input type="text" name="extracurricular[<?php echo $j; ?>][activity_name]" value="<?php echo html_escape($x_name); ?>" placeholder="Name"
                   class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
          </div>
          <div>
            <label class="block font-label-md text-label-md text-on-surface-variant mb-1 text-xs">Level</label>
            <select name="extracurricular[<?php echo $j; ?>][level]" class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary">
              <option value="">Select</option>
              <?php foreach ($levels as $l): ?>
                <option value="<?php echo $l; ?>" <?php echo ($x_level === $l) ? 'selected' : ''; ?>><?php echo $l; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="block font-label-md text-label-md text-on-surface-variant mb-1 text-xs">Position / Result</label>
            <input type="text" name="extracurricular[<?php echo $j; ?>][position_result]" value="<?php echo html_escape($x_pos); ?>" placeholder="e.g. Winner"
                   class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
          </div>
          <div>
            <label class="block font-label-md text-label-md text-on-surface-variant mb-1 text-xs">Year</label>
            <input type="number" name="extracurricular[<?php echo $j; ?>][year]" value="<?php echo html_escape($x_year); ?>" placeholder="<?php echo date('Y'); ?>" min="1990" max="<?php echo date('Y'); ?>"
                   class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
          </div>
          <div class="flex flex-col gap-1">
            <label class="block font-label-md text-label-md text-on-surface-variant mb-1 text-xs">Description</label>
            <div class="flex gap-1">
              <input type="text" name="extracurricular[<?php echo $j; ?>][description]" value="<?php echo html_escape($x_desc); ?>" placeholder="Optional"
                     class="flex-1 min-w-0 px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
              <button type="button" class="remove-extra shrink-0 w-8 h-9 flex items-center justify-center rounded-lg border border-error/30 text-error hover:bg-error-container transition-colors cursor-pointer" title="Remove">
                <span class="material-symbols-outlined text-[16px]">remove</span>
              </button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <button type="button" id="add-extra-activity" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-secondary text-secondary text-label-md hover:bg-secondary-container transition-colors cursor-pointer font-medium text-xs">
        <span class="material-symbols-outlined text-[16px]">add</span>Add Extracurricular Activity
      </button>
    </div>
  </div>

  <!-- ══════════════════════════════════════════════════════════════════════
       SECTION 7: STUDENT DOCUMENTS ON RECORD
  ═════════════════════════════════════════════════════════════════════════ -->
  <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-outline-variant/50 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-secondary text-[22px]">folder_shared</span>
        <h3 class="font-headline-md text-headline-md text-on-surface font-semibold text-base">Student Documents</h3>
      </div>
      <?php if (!empty($documents)): ?>
      <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-surface-container-high text-on-surface-variant">
        <?php echo count($documents); ?> Document(s)
      </span>
      <?php endif; ?>
    </div>
    <div class="p-5">
      <?php if (!empty($documents)): ?>
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-on-surface">
            <thead>
              <tr class="border-b border-outline-variant/60 text-xs font-semibold text-on-surface-variant uppercase tracking-wider bg-surface-container-low/50">
                <th class="py-2.5 px-3">Type</th>
                <th class="py-2.5 px-3">Document Name</th>
                <th class="py-2.5 px-3">Document Number</th>
                <th class="py-2.5 px-3">Status</th>
                <th class="py-2.5 px-3 text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/30">
              <?php foreach ($documents as $doc): ?>
              <tr>
                <td class="py-2.5 px-3 font-medium"><?php echo html_escape($doc->document_type); ?></td>
                <td class="py-2.5 px-3"><?php echo html_escape($doc->document_name ?: '—'); ?></td>
                <td class="py-2.5 px-3"><?php echo html_escape($doc->document_number ?: '—'); ?></td>
                <td class="py-2.5 px-3">
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-800 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>Active
                  </span>
                </td>
                <td class="py-2.5 px-3 text-right">
                  <?php if (!empty($doc->file_path) && file_exists(FCPATH . $doc->file_path)): ?>
                  <a href="<?php echo base_url($doc->file_path); ?>" target="_blank" class="inline-flex items-center gap-1 text-primary hover:underline text-xs font-medium">
                    <span class="material-symbols-outlined text-[15px]">download</span>View / Download
                  </a>
                  <?php else: ?>
                  <span class="text-on-surface-variant text-xs">File unavailable</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="text-body-md text-on-surface-variant text-sm py-2">No documents currently uploaded for this student.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- ══════════════════════════════════════════════════════════════════════
       BOTTOM ACTIONS
  ═════════════════════════════════════════════════════════════════════════ -->
  <div class="flex items-center justify-end gap-3 pb-8">
    <a href="<?php echo site_url('students/profile/' . $student_id); ?>" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors font-medium">
      Cancel
    </a>
    <button type="submit" class="inline-flex items-center gap-1.5 px-6 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer font-medium">
      <span class="material-symbols-outlined text-[18px]">check</span>Update Student
    </button>
  </div>

<?php echo form_close(); ?>

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

<script>
(function ($) {
    'use strict';

    var SERVER_TODAY    = "<?php echo date('Y-m-d'); ?>";
    var ACADEMIC_TYPES  = <?php echo json_encode($academic_types); ?>;
    var EXTRA_TYPES     = <?php echo json_encode($extra_types); ?>;
    var LEVELS          = <?php echo json_encode($levels); ?>;

    var cropperInstance = null;
    var $fileInput      = $('#student_image_file');
    var $chooseBtn      = $('#btn-choose-photo');
    var $removeBtn      = $('#photo-remove-btn');
    var $photoBtnLabel  = $('#photo-btn-label');
    var $previewImg     = $('#photo-preview-img');
    var $placeholder    = $('#photo-placeholder-avatar');
    var $fileStatus     = $('#photo-file-status');
    var $hiddenCropped  = $('#cropped_image_data');
    var $removePhotoInp = $('#remove_photo');

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

    /* ── Student Profile Image & Cropper ────────────────────────────── */
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

        var allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        var allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        var ext = file.name.split('.').pop().toLowerCase();

        if (allowedExts.indexOf(ext) === -1 && allowedMimes.indexOf(file.type) === -1) {
            alert('Please select a valid JPG, JPEG, PNG, or WEBP image.');
            $('#err-student_image').text('Please select a valid JPG, JPEG, PNG, or WEBP image.').removeClass('hidden');
            this.value = '';
            return;
        }

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

    $zoomRange.on('input', function () {
        if (!cropperInstance) return;
        var val = parseFloat(this.value);
        if (!isNaN(val) && val >= 0.1 && val <= 3) {
            cropperInstance.zoomTo(val);
        }
    });

    $zoomInBtn.on('click', function () {
        if (!cropperInstance) return;
        cropperInstance.zoom(0.1);
    });
    $zoomOutBtn.on('click', function () {
        if (!cropperInstance) return;
        cropperInstance.zoom(-0.1);
    });

    $rotateLeftBtn.on('click', function () {
        if (!cropperInstance) return;
        cropperInstance.rotate(-90);
    });
    $rotateRightBtn.on('click', function () {
        if (!cropperInstance) return;
        cropperInstance.rotate(90);
    });

    $resetBtn.on('click', function () {
        if (!cropperInstance) return;
        cropperInstance.reset();
        $zoomRange.val(1);
    });

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
                $removePhotoInp.val('0');

                $previewImg.attr('src', croppedBase64).removeClass('hidden');
                $placeholder.addClass('hidden');
                $removeBtn.removeClass('hidden');
                $photoBtnLabel.text('Change Photo');
                $fileStatus.text('New cropped photo ready to save.');
                $('#err-student_image').addClass('hidden').text('');
            }
        }
        closeStudentCropper();
    });

    $removeBtn.on('click', function () {
        $hiddenCropped.val('');
        $removePhotoInp.val('1');
        $fileInput.val('');
        $previewImg.attr('src', '').addClass('hidden');
        $placeholder.removeClass('hidden');
        $removeBtn.addClass('hidden');
        $photoBtnLabel.text('Upload Photo');
        $fileStatus.text('Photo marked for removal. Save changes to update.');
        $('#err-student_image').addClass('hidden').text('');
    });

    /* ── Class & Division Dynamic Cascading ─────────────────────────── */
    $('#edit_academic_group_id').on('change', function () {
        var groupId = $(this).val();
        if (!groupId) {
            $('#edit_class_id option').show();
            return;
        }
        $('#edit_class_id option').each(function () {
            var cGroup = $(this).attr('data-group');
            if (!$(this).val() || cGroup === groupId) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    $('#edit_class_id').on('change', function () {
        var classId = $(this).val();
        var selectedOpt = $(this).find('option:selected');
        var group = selectedOpt.attr('data-group');
        if (group && !$('#edit_academic_group_id').val()) {
            $('#edit_academic_group_id').val(group);
        }
        if (!classId) {
            $('#edit_division_id').html('<option value="">Select Division</option>');
            return;
        }
        $.ajax({
            url: '<?php echo site_url("students/get_divisions_ajax"); ?>',
            method: 'POST',
            data: { class_id: classId, [window.CSRF_TOKEN_NAME]: window.CSRF_HASH },
            dataType: 'json',
            success: function (r) {
                if (r && (r.divisions || r.sections) && (r.divisions || r.sections).length > 0) {
                    var list = r.divisions || r.sections;
                    var opts = '';
                    list.forEach(function (d, idx) {
                        var name = (d.division_name || d.section_name || '').toString();
                        var label = (name.toLowerCase().indexOf('division') === -1) ? 'Division ' + name : name;
                        opts += '<option value="' + (d.division_id || d.section_id) + '" ' + (idx === 0 ? 'selected' : '') + '>' + $('<div>').text(label).html() + '</option>';
                    });
                    $('#edit_division_id').html(opts);
                } else {
                    $('#edit_division_id').html('<option value="">No divisions available</option>');
                }
            },
            error: function () {
                $('#edit_division_id').html('<option value="">Failed to load divisions</option>');
            }
        });
    });

    /* ── Date of Birth validation ───────────────────────────────────── */
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

    /* ── No Previous School toggle ──────────────────────────────────── */
    $('#no_previous_school').on('change', function () {
        if ($(this).is(':checked')) {
            $('#prev-school-fields').addClass('hidden');
        } else {
            $('#prev-school-fields').removeClass('hidden');
        }
    });

    /* ── TC Document selection feedback ─────────────────────────────── */
    $('#tc_document_file').on('change', function () {
        var f = this.files[0];
        if (f) {
            if (f.size > 10 * 1024 * 1024) {
                alert('TC Document must not exceed 10 MB.');
                this.value = '';
                $('#tc-selected-name').addClass('hidden').text('');
                return;
            }
            $('#tc-selected-name').text('Selected: ' + f.name).removeClass('hidden');
            $('#tc-btn-label').text('Change Selected File');
        }
    });

    /* ── Dynamic Repeater: Academic Activities ──────────────────────── */
    var nextAcademicIndex = <?php echo count($rows); ?>;
    $('#add-academic-activity').on('click', function () {
        var i = nextAcademicIndex++;
        var opts = '';
        ACADEMIC_TYPES.forEach(function (t) {
            opts += '<option value="' + t + '">' + t + '</option>';
        });

        var html = '<div class="academic-activity-row grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2 p-3 rounded-lg bg-surface-container-low border border-outline-variant/40 relative">' +
            '<div>' +
              '<label class="block font-label-md text-label-md text-on-surface-variant mb-1 text-xs">Type</label>' +
              '<select name="academic_activities[' + i + '][activity_type]" class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary">' + opts + '</select>' +
            '</div>' +
            '<div>' +
              '<label class="block font-label-md text-label-md text-on-surface-variant mb-1 text-xs">Activity Name</label>' +
              '<input type="text" name="academic_activities[' + i + '][activity_name]" placeholder="Name / Title" class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>' +
            '</div>' +
            '<div>' +
              '<label class="block font-label-md text-label-md text-on-surface-variant mb-1 text-xs">Position / Result</label>' +
              '<input type="text" name="academic_activities[' + i + '][position_result]" placeholder="e.g. 1st, Gold" class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>' +
            '</div>' +
            '<div>' +
              '<label class="block font-label-md text-label-md text-on-surface-variant mb-1 text-xs">Year</label>' +
              '<input type="number" name="academic_activities[' + i + '][year]" value="' + (new Date().getFullYear()) + '" min="1990" max="' + (new Date().getFullYear()) + '" class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>' +
            '</div>' +
            '<div class="flex flex-col gap-1">' +
              '<label class="block font-label-md text-label-md text-on-surface-variant mb-1 text-xs">Description</label>' +
              '<div class="flex gap-1">' +
                '<input type="text" name="academic_activities[' + i + '][description]" placeholder="Brief description" class="flex-1 min-w-0 px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>' +
                '<button type="button" class="remove-activity shrink-0 w-8 h-9 flex items-center justify-center rounded-lg border border-error/30 text-error hover:bg-error-container transition-colors cursor-pointer" title="Remove"><span class="material-symbols-outlined text-[16px]">remove</span></button>' +
              '</div>' +
            '</div>' +
        '</div>';

        $('#academic-activities-list').append(html);
    });

    $(document).on('click', '.remove-activity', function () {
        var $rows = $('#academic-activities-list .academic-activity-row');
        if ($rows.length > 1) {
            $(this).closest('.academic-activity-row').remove();
        } else {
            // If only 1 row left, just clear inputs
            $(this).closest('.academic-activity-row').find('input').val('');
        }
    });

    /* ── Dynamic Repeater: Extracurricular Activities ───────────────── */
    var nextExtraIndex = <?php echo count($xrows); ?>;
    $('#add-extra-activity').on('click', function () {
        var j = nextExtraIndex++;
        var tOpts = '';
        EXTRA_TYPES.forEach(function (t) {
            tOpts += '<option value="' + t + '">' + t + '</option>';
        });

        var lOpts = '<option value="">Select</option>';
        LEVELS.forEach(function (l) {
            lOpts += '<option value="' + l + '">' + l + '</option>';
        });

        var html = '<div class="extra-activity-row grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-2 p-3 rounded-lg bg-surface-container-low border border-outline-variant/40 relative">' +
            '<div>' +
              '<label class="block font-label-md text-label-md text-on-surface-variant mb-1 text-xs">Type</label>' +
              '<select name="extracurricular[' + j + '][activity_type]" class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary">' + tOpts + '</select>' +
            '</div>' +
            '<div>' +
              '<label class="block font-label-md text-label-md text-on-surface-variant mb-1 text-xs">Activity Name</label>' +
              '<input type="text" name="extracurricular[' + j + '][activity_name]" placeholder="Name" class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>' +
            '</div>' +
            '<div>' +
              '<label class="block font-label-md text-label-md text-on-surface-variant mb-1 text-xs">Level</label>' +
              '<select name="extracurricular[' + j + '][level]" class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary">' + lOpts + '</select>' +
            '</div>' +
            '<div>' +
              '<label class="block font-label-md text-label-md text-on-surface-variant mb-1 text-xs">Position / Result</label>' +
              '<input type="text" name="extracurricular[' + j + '][position_result]" placeholder="e.g. Winner" class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>' +
            '</div>' +
            '<div>' +
              '<label class="block font-label-md text-label-md text-on-surface-variant mb-1 text-xs">Year</label>' +
              '<input type="number" name="extracurricular[' + j + '][year]" value="' + (new Date().getFullYear()) + '" min="1990" max="' + (new Date().getFullYear()) + '" class="w-full px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>' +
            '</div>' +
            '<div class="flex flex-col gap-1">' +
              '<label class="block font-label-md text-label-md text-on-surface-variant mb-1 text-xs">Description</label>' +
              '<div class="flex gap-1">' +
                '<input type="text" name="extracurricular[' + j + '][description]" placeholder="Optional" class="flex-1 min-w-0 px-2 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-sm focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>' +
                '<button type="button" class="remove-extra shrink-0 w-8 h-9 flex items-center justify-center rounded-lg border border-error/30 text-error hover:bg-error-container transition-colors cursor-pointer" title="Remove"><span class="material-symbols-outlined text-[16px]">remove</span></button>' +
              '</div>' +
            '</div>' +
        '</div>';

        $('#extracurricular-list').append(html);
    });

    $(document).on('click', '.remove-extra', function () {
        var $rows = $('#extracurricular-list .extra-activity-row');
        if ($rows.length > 1) {
            $(this).closest('.extra-activity-row').remove();
        } else {
            $(this).closest('.extra-activity-row').find('input').val('');
        }
    });

    /* ── Phone Inputs Initialization ───────────────────────────────── */
    if (window.PhoneManager) {
        window.PhoneManager.init('#student-edit-form');
    }

    /* ── Form Submit Guard ──────────────────────────────────────────── */
    $('#student-edit-form').on('submit', function (e) {
        var dob = $('#date_of_birth').val();
        if (dob && dob > SERVER_TODAY) {
            e.preventDefault();
            $('#err-date_of_birth').text('Date of Birth cannot be a future date.').removeClass('hidden');
            $('#date_of_birth').addClass('!border-error').focus();
            alert('Date of Birth cannot be a future date.');
            return false;
        }

        var pin = $.trim($('#pin_code').val());
        if (pin) {
            var cleanPin = pin.replace(/\s+/g, '');
            if (!/^[1-9][0-9]{5}$/.test(cleanPin)) {
                e.preventDefault();
                alert('Please enter a valid 6-digit Indian PIN code (e.g. 682001).');
                $('#pin_code').focus();
                return false;
            }
        }
    });

})(jQuery);
</script>
