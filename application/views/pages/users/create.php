<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('error')): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-error-container text-on-error-container text-body-md font-medium flex items-center gap-2 border border-error/20">
        <span class="material-symbols-outlined text-[20px] text-error">error</span>
        <?php echo html_escape($this->session->flashdata('error')); ?>
      </div>
    <?php endif; ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Create New User Account</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Register a new login account with role assignment and profile linking.</p>
      </div>
      <div class="flex items-center gap-2">
        <a href="<?php echo site_url('users/list'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>Back to Users
        </a>
      </div>
    </div>

    <!-- Create User Form Card -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-6 max-w-3xl mb-6">
      <?php echo form_open('users/create'); ?>
        <div class="space-y-6">
          
          <!-- Basic Information -->
          <div>
            <h3 class="font-headline-md text-title-md font-bold text-on-surface pb-2 border-b border-outline-variant/50 mb-4 flex items-center gap-2">
              <span class="material-symbols-outlined text-primary text-[20px]">badge</span>Account Details
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Full Name *</label>
                <input type="text" name="name" required placeholder="e.g. Ramesh Kumar" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Username * (Unique)</label>
                <input type="text" name="username" required placeholder="e.g. ramesh.kumar" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Email Address *</label>
                <input type="email" name="email" required placeholder="ramesh@school.com" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Phone Number</label>
                <input type="tel" name="phone" placeholder="+91 98470 12345" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>
            </div>
          </div>

          <!-- Designation & Role Selection -->
          <div>
            <h3 class="font-headline-md text-title-md font-bold text-on-surface pb-2 border-b border-outline-variant/50 mb-4 flex items-center gap-2">
              <span class="material-symbols-outlined text-secondary text-[20px]">badge</span>Designation & Classification
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Assigned Designation *</label>
                <select name="designation_id" id="selDesignationId" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <?php if (!empty($designations)): ?>
                    <?php foreach ($designations as $d): ?>
                      <option value="<?php echo $d->designation_id ?? $d->id; ?>"><?php echo html_escape($d->designation_name . ' (' . $d->designation_code . ')'); ?></option>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <?php foreach ($roles as $r): ?>
                      <option value="<?php echo $r->role_id; ?>"><?php echo html_escape($r->role_name . ' (' . $r->role_code . ')'); ?></option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
                <input type="hidden" name="role_id" id="hiddenRoleId" value="<?php echo !empty($roles[0]) ? $roles[0]->role_id : 1; ?>">
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">User Classification *</label>
                <select name="user_type" id="selUserType" required onchange="handleTypeChange(this.value)" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <option value="Admin">Admin</option>
                  <option value="Principal">Principal</option>
                  <option value="Teacher">Teacher</option>
                  <option value="Accountant">Accountant</option>
                  <option value="Transport Manager">Transport Manager</option>
                  <option value="Receptionist">Receptionist</option>
                  <option value="Parent">Parent</option>
                  <option value="Student">Student</option>
                  <option value="Staff">Staff</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Profile Linkage (Dynamic) -->
          <div id="profileLinkSection" class="p-4 rounded-xl bg-surface-container-low border border-outline-variant/40 space-y-4">
            <span class="text-xs font-bold text-on-surface uppercase tracking-wider block">Link Existing Profile (Optional)</span>
            
            <!-- Link Staff -->
            <div id="divLinkStaff" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Link Staff Profile</label>
                <select name="staff_id" class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md">
                  <option value="">-- Do Not Link Staff --</option>
                  <?php foreach ($staff as $st): ?>
                    <option value="<?php echo $st->staff_id; ?>"><?php echo html_escape($st->full_name . ' (' . $st->employee_code . ')'); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <!-- Link Student / Child -->
            <div id="divLinkStudent" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Link Student Profile / Child</label>
                <select name="student_id" class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md">
                  <option value="">-- Do Not Link Student --</option>
                  <?php foreach ($students as $stu): ?>
                    <option value="<?php echo $stu->student_id; ?>"><?php echo html_escape($stu->first_name . ' ' . $stu->last_name . ' (' . $stu->admission_number . ')'); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>

          <!-- Password Security -->
          <div>
            <h3 class="font-headline-md text-title-md font-bold text-on-surface pb-2 border-b border-outline-variant/50 mb-4 flex items-center gap-2">
              <span class="material-symbols-outlined text-amber-600 text-[20px]">key</span>Security Credentials
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Password *</label>
                <input type="password" name="password" required minlength="6" placeholder="Min 6 characters" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>
            </div>
          </div>

          <div class="flex items-center justify-end gap-3 pt-4 border-t border-outline-variant/50">
            <a href="<?php echo site_url('users/list'); ?>" class="px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-label-md font-medium">Cancel</a>
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
              Create User Account
            </button>
          </div>
        </div>
      <?php echo form_close(); ?>
    </div>

    <script>
      function handleTypeChange(val) {
        // Toggle helper visibility
      }
    </script>
