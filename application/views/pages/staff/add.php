<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <div class="flex items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Staff Registration</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Register a new faculty member or administrative staff employee.</p>
      </div>
      <a href="<?php echo site_url('staff'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span>Back to All Staff
      </a>
    </div>

    <?php if (validation_errors() || !empty($doc_errors) || !empty($photo_error)): ?>
      <div class="p-4 mb-5 rounded-xl bg-error-container/30 border border-error/30 text-error text-body-md space-y-1">
        <?php echo validation_errors(); ?>
        <?php if (!empty($photo_error)): ?>
          <div>• <?php echo html_escape($photo_error); ?></div>
        <?php endif; ?>
        <?php if (!empty($doc_errors)): ?>
          <?php foreach ($doc_errors as $err): ?>
            <div>• <?php echo html_escape($err); ?></div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div id="doc-validation-alert" class="hidden p-4 mb-5 rounded-xl bg-error-container/40 border border-error text-error text-body-md flex items-center gap-3">
      <span class="material-symbols-outlined text-[22px]">error</span>
      <div id="doc-validation-msg"></div>
    </div>

    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 p-6 max-w-4xl">
      <?php echo form_open_multipart('staff/register', array('class' => 'space-y-6', 'id' => 'add_staff_form')); ?>
        
        <!-- DIVISION 1: Personal Details -->
        <div>
          <h3 class="font-headline-md text-headline-md text-on-surface mb-3 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px]">person</span>1. Personal Details
          </h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-body-md">
            <div>
              <label class="block text-label-md text-on-surface mb-1">Full Name *</label>
              <input type="text" name="full_name" required value="<?php echo set_value('full_name'); ?>" placeholder="e.g. Ramesh Kumar" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest"/>
            </div>
            <div>
              <label class="block text-label-md text-on-surface mb-1">Gender *</label>
              <select name="gender" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest">
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div>
              <label class="block text-label-md text-on-surface mb-1">Date of Birth</label>
              <input type="date" name="date_of_birth" value="<?php echo set_value('date_of_birth', '1988-06-15'); ?>" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest"/>
            </div>
            <div>
              <label class="block text-label-md text-on-surface mb-1">Blood Group</label>
              <select name="blood_group" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest">
                <option value="">Select Blood Group</option>
                <option value="A+">A+</option><option value="A-">A-</option><option value="B+">B+</option><option value="B-">B-</option><option value="O+">O+</option><option value="O-">O-</option><option value="AB+">AB+</option><option value="AB-">AB-</option>
              </select>
            </div>
          </div>
        </div>

        <!-- DIVISION 2: Contact Information -->
        <div class="pt-4 border-t border-outline-variant/40">
          <h3 class="font-headline-md text-headline-md text-on-surface mb-3 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px]">call</span>2. Contact Details
          </h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-body-md">
            <div>
              <label class="block text-label-md text-on-surface mb-1">Primary Phone *</label>
              <input type="tel" id="staff_phone" name="phone" required data-phone-field="true" data-required="true" data-default-country="in" value="<?php echo set_value('phone'); ?>" placeholder="98470 11223" class="phone-input w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
              <?php if (!empty($phone_errors['phone'])): ?>
                <p class="phone-feedback text-danger small mt-1 text-error text-[11px]"><?php echo html_escape($phone_errors['phone']); ?></p>
              <?php endif; ?>
            </div>
            <div>
              <label class="block text-label-md text-on-surface mb-1">Alternate Phone <span class="text-xs text-on-surface-variant font-normal">(Optional)</span></label>
              <input type="tel" id="staff_alternate_phone" name="alternate_phone" data-phone-field="true" data-default-country="in" value="<?php echo set_value('alternate_phone'); ?>" placeholder="94470 99887" class="phone-input w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
              <?php if (!empty($phone_errors['alternate_phone'])): ?>
                <p class="phone-feedback text-danger small mt-1 text-error text-[11px]"><?php echo html_escape($phone_errors['alternate_phone']); ?></p>
              <?php endif; ?>
            </div>
            <div>
              <label class="block text-label-md text-on-surface mb-1">Email Address *</label>
              <input type="email" name="email" required value="<?php echo set_value('email'); ?>" placeholder="staff.name@gmail.com" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest"/>
            </div>
            <div class="sm:col-span-3">
              <label class="block text-label-md text-on-surface mb-1">Full Residential Address</label>
              <input type="text" name="address" value="<?php echo set_value('address'); ?>" placeholder="House Name, Street, City, District, PIN" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest"/>
            </div>
          </div>
        </div>

        <!-- DIVISION 3: Employment Information -->
        <div class="pt-4 border-t border-outline-variant/40">
          <h3 class="font-headline-md text-headline-md text-on-surface mb-3 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px]">badge</span>3. Employment Details
          </h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-body-md">
            <div>
              <label class="block text-label-md text-on-surface mb-1">Employee ID / Code *</label>
              <input type="text" name="employee_code" required value="<?php echo set_value('employee_code', 'EMP' . rand(1015, 1999)); ?>" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest font-mono text-primary font-medium"/>
            </div>
            <div>
              <label class="block text-label-md text-on-surface mb-1">Staff Type *</label>
              <select name="staff_type" id="staff_type_select" onchange="toggleTeacherFields(this.value)" required class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest font-medium">
                <option value="teacher" <?php echo set_select('staff_type', 'teacher', TRUE); ?>>Teacher (Teaching Faculty)</option>
                <option value="non_teaching" <?php echo set_select('staff_type', 'non_teaching'); ?>>Non-Teaching Staff</option>
              </select>
            </div>
            <div id="group_field_container">
              <label class="block text-label-md text-on-surface mb-1">Department / Group *</label>
              <select name="academic_group_id" id="academic_group_id" required class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest font-medium">
                <option value="">Select Department / Group</option>
                <?php if (!empty($academic_groups)): ?>
                  <?php foreach ($academic_groups as $grp): ?>
                    <option value="<?php echo $grp->academic_group_id; ?>" <?php echo set_select('academic_group_id', $grp->academic_group_id); ?>><?php echo html_escape($grp->group_name); ?></option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>
            
            <div>
              <label class="block text-label-md text-on-surface mb-1">Designation *</label>
              <select name="designation_id" required class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest">
                <?php foreach ($designations as $desig): ?>
                  <option value="<?php echo $desig->designation_id; ?>"><?php echo html_escape($desig->designation_name); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block text-label-md text-on-surface mb-1">Joining Date *</label>
              <input type="date" name="joining_date" required value="<?php echo date('Y-m-d'); ?>" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest"/>
            </div>
            <div>
              <label class="block text-label-md text-on-surface mb-1">Monthly Salary (₹)</label>
              <input type="number" step="100" name="salary" value="<?php echo set_value('salary', '40000'); ?>" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest"/>
            </div>
          </div>
        </div>

        <!-- DIVISION 4: Professional & Teacher Specific Fields -->
        <div class="pt-4 border-t border-outline-variant/40">
          <h3 class="font-headline-md text-headline-md text-on-surface mb-3 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px]">school</span>4. Professional Details
          </h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-body-md">
            <div>
              <label class="block text-label-md text-on-surface mb-1">Qualification</label>
              <input type="text" name="qualification" value="<?php echo set_value('qualification'); ?>" placeholder="e.g. M.Sc, B.Ed" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest"/>
            </div>
            <div>
              <label class="block text-label-md text-on-surface mb-1">Experience</label>
              <input type="text" name="experience" value="<?php echo set_value('experience'); ?>" placeholder="e.g. 5 Years" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest"/>
            </div>
            <div id="teacher_specialization_box">
              <label class="block text-label-md text-on-surface mb-1">Subject Specialization</label>
              <input type="text" name="specialization" value="<?php echo set_value('specialization'); ?>" placeholder="e.g. Mathematics, Science" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest"/>
            </div>
          </div>
        </div>

        <!-- DIVISION 5: Staff Profile Photo (Interactive Cropper) -->
        <div class="pt-4 border-t border-outline-variant/40">
          <div class="flex items-center justify-between gap-2 mb-3">
            <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
              <span class="material-symbols-outlined text-primary text-[20px]">account_box</span>5. Staff Profile Photo
            </h3>
            <span class="text-[12px] text-on-surface-variant">Optional · 3:4 Portrait Ratio · Max 3 MB</span>
          </div>

          <div class="p-5 rounded-2xl bg-surface-container-low border border-outline-variant/60 flex flex-col sm:flex-row items-center gap-6">
            <!-- Hidden inputs -->
            <input type="hidden" name="cropped_image_data" id="cropped_image_data" value=""/>
            <input type="file" id="staff_photo_file_input" accept="image/jpeg,image/png,image/jpg" class="hidden"/>

            <!-- Portrait Photo Preview Box (3:4 Ratio) -->
            <div class="relative w-32 h-40 rounded-xl overflow-hidden border-2 border-dashed border-outline-variant bg-surface-container-lowest flex items-center justify-center shrink-0 shadow-inner">
              <img id="staff-photo-preview-img" src="" alt="Staff Preview" class="w-full h-full object-cover hidden"/>
              <div id="staff-photo-placeholder" class="flex flex-col items-center justify-center text-on-surface-variant/60 p-2 text-center">
                <span class="material-symbols-outlined text-[36px] mb-1 text-on-surface-variant/40">account_circle</span>
                <span class="text-[11px] font-medium leading-tight">No Photo<br/>Selected</span>
              </div>
            </div>

            <!-- Controls & Instructions -->
            <div class="flex-1 space-y-2 text-center sm:text-left">
              <div>
                <h4 class="font-semibold text-on-surface text-body-md" id="photo-status-title">Staff Profile Photograph</h4>
                <p class="text-[12px] text-on-surface-variant mt-0.5">Upload a clean, portrait-oriented photograph for teacher profile, ID card, and directories.</p>
              </div>

              <div class="flex items-center justify-center sm:justify-start gap-2 pt-1 flex-wrap">
                <button type="button" id="btn-choose-photo" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors cursor-pointer shadow-sm">
                  <span class="material-symbols-outlined text-[18px]">photo_camera</span>
                  <span id="btn-photo-label">Choose Image</span>
                </button>
                <button type="button" id="btn-remove-photo" class="hidden inline-flex items-center gap-1 px-3 py-2 rounded-lg border border-error/30 text-error hover:bg-error-container/30 text-label-md transition-colors cursor-pointer">
                  <span class="material-symbols-outlined text-[16px]">delete</span>Remove
                </button>
              </div>

              <div class="text-[11px] text-on-surface-variant/80 flex items-center justify-center sm:justify-start gap-3 pt-1">
                <span>✓ JPG, JPEG, PNG</span>
                <span>✓ Maximum 3 MB</span>
                <span>✓ Interactive 3:4 Cropper</span>
              </div>
            </div>
          </div>
        </div>

        <!-- DIVISION 6: Staff Documents (Dynamic & Mandatory) -->
        <div class="pt-4 border-t border-outline-variant/40">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
            <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
              <span class="material-symbols-outlined text-primary text-[20px]">folder_shared</span>6. Staff Documents
            </h3>
            <span class="text-label-md text-error font-medium flex items-center gap-1">
              <span class="material-symbols-outlined text-[16px]">priority_high</span>All configured documents are mandatory
            </span>
          </div>

          <?php if (empty($document_types)): ?>
            <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
              <div>
                <div class="text-body-md font-medium text-on-surface">No staff documents configured.</div>
                <div class="text-[12px] text-on-surface-variant">Super Admin can add required staff documents from Settings → Staff Document.</div>
              </div>
              <?php if (!empty($is_super_admin)): ?>
                <a href="<?php echo site_url('settings/staff_documents'); ?>" target="_blank" class="px-3.5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shrink-0 inline-flex items-center gap-1">
                  <span class="material-symbols-outlined text-[16px]">add</span>+ Add Document
                </a>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-body-md">
              <?php foreach ($document_types as $dt): ?>
                <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant/60 hover:border-outline transition-colors">
                  <div class="flex items-center justify-between mb-1">
                    <label class="font-semibold text-on-surface text-label-md flex items-center gap-1">
                      <?php echo html_escape($dt->document_name); ?> <span class="text-error font-bold">*</span>
                    </label>
                    <span class="text-[10px] uppercase font-bold tracking-wider text-error bg-error-container/60 px-2 py-0.5 rounded-full">Required</span>
                  </div>
                  <?php if (!empty($dt->description)): ?>
                    <p class="text-[12px] text-on-surface-variant mb-2.5"><?php echo html_escape($dt->description); ?></p>
                  <?php else: ?>
                    <p class="text-[12px] text-on-surface-variant mb-2.5">Upload document copy (PDF, JPG, PNG, DOC)</p>
                  <?php endif; ?>
                  <input type="file" 
                    name="staff_doc_file[<?php echo $dt->id; ?>]" 
                    id="doc_input_<?php echo $dt->id; ?>"
                    data-doc-name="<?php echo html_escape($dt->document_name); ?>"
                    required 
                    class="staff-required-doc w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface file:mr-3 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-secondary/15 file:text-secondary hover:file:bg-secondary/25 cursor-pointer transition-colors"/>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Form Actions -->
        <div class="pt-6 border-t border-outline-variant/40 flex items-center justify-end gap-3">
          <a href="<?php echo site_url('staff'); ?>" class="px-5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors">Cancel</a>
          <button type="submit" class="px-6 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer inline-flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[18px]">check</span>Register Staff Member
          </button>
        </div>

      <?php echo form_close(); ?>
    </div>

    <!-- Image Cropper Modal -->
    <div id="cropper-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 p-4 hidden backdrop-blur-sm">
      <div class="elevation-3 rounded-2xl bg-surface-container-lowest border border-outline-variant w-full max-w-xl overflow-hidden flex flex-col max-h-[92vh] shadow-2xl">
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/60 bg-surface-container-low">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[22px]">crop</span>
            <h3 class="font-headline-md text-headline-md text-on-surface text-base font-bold">Crop Staff Profile Image</h3>
          </div>
          <button type="button" id="btn-cropper-close" class="p-1.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer" title="Close">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <!-- Modal Body / Cropper Area -->
        <div class="p-4 bg-slate-950/80 flex-1 overflow-hidden flex items-center justify-center relative select-none" style="min-height: 300px; max-height: 380px;">
          <div class="w-full h-full flex items-center justify-center overflow-hidden">
            <img id="cropper-image-target" src="" alt="Source Image" class="max-w-full block" style="max-height: 360px;"/>
          </div>
        </div>

        <!-- Modal Controls Toolbar -->
        <div class="px-6 py-3.5 bg-surface-container-lowest border-t border-outline-variant/40 flex flex-wrap items-center justify-between gap-4">
          
          <!-- Zoom Controls with Sync -->
          <div class="flex items-center gap-2 flex-1 min-w-[200px]">
            <button type="button" id="btn-cropper-zoom-out" title="Zoom Out" class="p-1.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors cursor-pointer shrink-0">
              <span class="material-symbols-outlined text-[18px]">zoom_out</span>
            </button>
            <input type="range" id="cropper-zoom-range" min="0.1" max="3" step="0.05" value="1" class="w-full h-1.5 bg-surface-container-highest rounded-lg appearance-none cursor-pointer accent-primary" title="Zoom Slider"/>
            <button type="button" id="btn-cropper-zoom-in" title="Zoom In" class="p-1.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors cursor-pointer shrink-0">
              <span class="material-symbols-outlined text-[18px]">zoom_in</span>
            </button>
          </div>

          <!-- Rotation & Reset Controls -->
          <div class="flex items-center gap-1.5 shrink-0">
            <button type="button" id="btn-cropper-rotate-left" title="Rotate Left (-90°)" class="px-2.5 py-1.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors text-xs font-medium cursor-pointer inline-flex items-center gap-1">
              <span class="material-symbols-outlined text-[16px]">rotate_left</span>-90°
            </button>
            <button type="button" id="btn-cropper-rotate-right" title="Rotate Right (+90°)" class="px-2.5 py-1.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors text-xs font-medium cursor-pointer inline-flex items-center gap-1">
              <span class="material-symbols-outlined text-[16px]">rotate_right</span>+90°
            </button>
            <button type="button" id="btn-cropper-reset" title="Reset View" class="px-2.5 py-1.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors text-xs font-medium cursor-pointer inline-flex items-center gap-1">
              <span class="material-symbols-outlined text-[16px]">restart_alt</span>Reset
            </button>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-3.5 border-t border-outline-variant/60 bg-surface-container-low flex items-center justify-between gap-3">
          <button type="button" id="btn-cropper-change-file" class="text-label-md text-primary hover:underline font-medium cursor-pointer flex items-center gap-1 text-[13px]">
            <span class="material-symbols-outlined text-[16px]">folder_open</span>Choose Another Image
          </button>
          <div class="flex items-center gap-2.5">
            <button type="button" id="btn-cropper-cancel" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-label-md cursor-pointer transition-colors">Cancel</button>
            <button type="button" id="btn-cropper-apply" class="px-5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors cursor-pointer shadow-sm flex items-center gap-1.5 font-medium">
              <span class="material-symbols-outlined text-[18px]">crop</span>Apply Crop
            </button>
          </div>
        </div>

      </div>
    </div>

    <script>
      function toggleTeacherFields(type) {
        var specBox = document.getElementById('teacher_specialization_box');
        var groupContainer = document.getElementById('group_field_container');
        var groupSelect = document.getElementById('academic_group_id');
        if (type === 'non_teaching') {
          if (specBox) specBox.style.display = 'none';
          if (groupContainer) groupContainer.style.display = 'none';
          if (groupSelect) {
            groupSelect.removeAttribute('required');
            groupSelect.value = '';
          }
        } else {
          if (specBox) specBox.style.display = 'block';
          if (groupContainer) groupContainer.style.display = 'block';
          if (groupSelect) {
            groupSelect.setAttribute('required', 'required');
          }
        }
      }

      document.addEventListener('DOMContentLoaded', function() {
        var staffTypeSelect = document.getElementById('staff_type_select');
        if (staffTypeSelect) {
          toggleTeacherFields(staffTypeSelect.value);
        }
      });

      // ==========================================
      // Staff Profile Image Cropper Implementation
      // ==========================================
      var cropperInstance = null;
      var fileInput = document.getElementById('staff_photo_file_input');
      var chooseBtn = document.getElementById('btn-choose-photo');
      var removeBtn = document.getElementById('btn-remove-photo');
      var photoLabel = document.getElementById('btn-photo-label');
      var previewImg = document.getElementById('staff-photo-preview-img');
      var placeholder = document.getElementById('staff-photo-placeholder');
      var hiddenInput = document.getElementById('cropped_image_data');

      var modal = document.getElementById('cropper-modal');
      var cropperImage = document.getElementById('cropper-image-target');
      var zoomRange = document.getElementById('cropper-zoom-range');
      var zoomInBtn = document.getElementById('btn-cropper-zoom-in');
      var zoomOutBtn = document.getElementById('btn-cropper-zoom-out');
      var rotateLeftBtn = document.getElementById('btn-cropper-rotate-left');
      var rotateRightBtn = document.getElementById('btn-cropper-rotate-right');
      var resetBtn = document.getElementById('btn-cropper-reset');
      var closeBtn = document.getElementById('btn-cropper-close');
      var cancelBtn = document.getElementById('btn-cropper-cancel');
      var applyBtn = document.getElementById('btn-cropper-apply');
      var changeFileBtn = document.getElementById('btn-cropper-change-file');

      if (chooseBtn) {
        chooseBtn.addEventListener('click', function() {
          fileInput.value = '';
          fileInput.click();
        });
      }

      if (changeFileBtn) {
        changeFileBtn.addEventListener('click', function() {
          fileInput.value = '';
          fileInput.click();
        });
      }

      if (fileInput) {
        fileInput.addEventListener('change', function(e) {
          var file = e.target.files[0];
          if (!file) return;

          // Frontend validation: format and max 3MB
          var allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
          var ext = file.name.split('.').pop().toLowerCase();
          if (!allowedTypes.includes(file.type) && !['jpg', 'jpeg', 'png', 'webp'].includes(ext)) {
            alert('Unsupported image format. Please select a JPG, JPEG, PNG, or WEBP image.');
            fileInput.value = '';
            return;
          }

          if (file.size > 3 * 1024 * 1024) {
            alert('Image file size exceeds the 3 MB limit. Please choose a smaller image.');
            fileInput.value = '';
            return;
          }

          var reader = new FileReader();
          reader.onload = function(evt) {
            openCropperModal(evt.target.result);
          };
          reader.onerror = function() {
            alert('Failed to read image file. Please try again.');
          };
          reader.readAsDataURL(file);
        });
      }

      function openCropperModal(imageSrc) {
        if (typeof Cropper === 'undefined') {
          alert('Image Cropper library is not ready. Please refresh the page.');
          return;
        }

        if (cropperInstance) {
          cropperInstance.destroy();
          cropperInstance = null;
        }

        cropperImage.src = imageSrc;
        zoomRange.value = 1;
        modal.classList.remove('hidden');

        cropperImage.onload = function() {
          if (cropperInstance) {
            cropperInstance.destroy();
            cropperInstance = null;
          }
          initCropper();
        };

        if (cropperImage.complete) {
          initCropper();
        }
      }

      function initCropper() {
        if (cropperInstance) return;
        cropperInstance = new Cropper(cropperImage, {
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
          ready: function() {
            zoomRange.value = 1;
          },
          zoom: function(e) {
            if (e.detail && typeof e.detail.ratio === 'number') {
              var r = Math.min(3, Math.max(0.1, parseFloat(e.detail.ratio.toFixed(2))));
              zoomRange.value = r;
            }
          }
        });
      }

      function closeCropperModal() {
        modal.classList.add('hidden');
        if (cropperInstance) {
          cropperInstance.destroy();
          cropperInstance = null;
        }
        cropperImage.src = '';
        fileInput.value = '';
      }

      if (closeBtn) closeBtn.addEventListener('click', closeCropperModal);
      if (cancelBtn) cancelBtn.addEventListener('click', closeCropperModal);

      // Zoom slider
      if (zoomRange) {
        zoomRange.addEventListener('input', function() {
          if (!cropperInstance) return;
          var val = parseFloat(this.value);
          if (!isNaN(val) && val >= 0.1 && val <= 3) {
            cropperInstance.zoomTo(val);
          }
        });
      }

      // Zoom In button
      if (zoomInBtn) {
        zoomInBtn.addEventListener('click', function() {
          if (!cropperInstance) return;
          cropperInstance.zoom(0.1);
        });
      }

      // Zoom Out button
      if (zoomOutBtn) {
        zoomOutBtn.addEventListener('click', function() {
          if (!cropperInstance) return;
          cropperInstance.zoom(-0.1);
        });
      }

      // Rotate Left (-90 deg)
      if (rotateLeftBtn) {
        rotateLeftBtn.addEventListener('click', function() {
          if (!cropperInstance) return;
          cropperInstance.rotate(-90);
        });
      }

      // Rotate Right (+90 deg)
      if (rotateRightBtn) {
        rotateRightBtn.addEventListener('click', function() {
          if (!cropperInstance) return;
          cropperInstance.rotate(90);
        });
      }

      // Reset
      if (resetBtn) {
        resetBtn.addEventListener('click', function() {
          if (!cropperInstance) return;
          cropperInstance.reset();
          zoomRange.value = 1;
        });
      }

      // Apply crop
      if (applyBtn) {
        applyBtn.addEventListener('click', function() {
          if (!cropperInstance) return;

          var canvas = cropperInstance.getCroppedCanvas({
            width: 600,
            height: 800,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
          });

          if (canvas) {
            var croppedBase64 = canvas.toDataURL('image/jpeg', 0.92);
            hiddenInput.value = croppedBase64;
            previewImg.src = croppedBase64;
            previewImg.classList.remove('hidden');
            placeholder.classList.add('hidden');
            removeBtn.classList.remove('hidden');
            photoLabel.textContent = 'Change Photo';
          }

          closeCropperModal();
        });
      }

      // Remove photo
      if (removeBtn) {
        removeBtn.addEventListener('click', function() {
          hiddenInput.value = '';
          fileInput.value = '';
          previewImg.src = '';
          previewImg.classList.add('hidden');
          placeholder.classList.remove('hidden');
          removeBtn.classList.add('hidden');
          photoLabel.textContent = 'Choose Image';
        });
      }

      // Initialize phone inputs
      if (window.PhoneManager) {
        window.PhoneManager.init('#add_staff_form');
      }

      // Client-side mandatory document & form verification
      document.getElementById('add_staff_form').addEventListener('submit', function(e) {
        // Phone validation
        if (window.PhoneManager && !window.PhoneManager.prepareSubmit(this)) {
          e.preventDefault();
          return;
        }

        var docInputs = document.querySelectorAll('.staff-required-doc');
        var missingDocs = [];

        docInputs.forEach(function(input) {
          if (!input.files || input.files.length === 0) {
            missingDocs.push(input.getAttribute('data-doc-name') || 'Document');
            input.classList.add('border-error');
          } else {
            input.classList.remove('border-error');
          }
        });

        if (missingDocs.length > 0) {
          e.preventDefault();
          var alertBox = document.getElementById('doc-validation-alert');
          var alertMsg = document.getElementById('doc-validation-msg');
          
          var msgHtml = '<strong>Please upload all required staff documents before registering:</strong><ul class="list-disc list-inside mt-1">';
          missingDocs.forEach(function(docName) {
            msgHtml += '<li>' + docName + ' is required.</li>';
          });
          msgHtml += '</ul>';

          alertMsg.innerHTML = msgHtml;
          alertBox.classList.remove('hidden');
          alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      });
    </script>
