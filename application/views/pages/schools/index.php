<div class="space-y-6">
  <!-- Flash Messages -->
  <?php if ($this->session->flashdata('success')): ?>
    <div class="p-4 rounded-xl bg-secondary-container/30 border border-secondary/30 text-secondary flex items-center justify-between shadow-sm">
      <div class="flex items-center gap-2.5">
        <span class="material-symbols-outlined text-[20px]">check_circle</span>
        <span class="text-body-md font-medium"><?php echo html_escape($this->session->flashdata('success')); ?></span>
      </div>
      <button onclick="this.parentElement.remove()" class="text-secondary/70 hover:text-secondary">
        <span class="material-symbols-outlined text-[18px]">close</span>
      </button>
    </div>
  <?php endif; ?>

  <?php if ($this->session->flashdata('error')): ?>
    <div class="p-4 rounded-xl bg-error-container/30 border border-error/30 text-error flex items-center justify-between shadow-sm">
      <div class="flex items-center gap-2.5">
        <span class="material-symbols-outlined text-[20px]">error</span>
        <span class="text-body-md font-medium"><?php echo html_escape($this->session->flashdata('error')); ?></span>
      </div>
      <button onclick="this.parentElement.remove()" class="text-error/70 hover:text-error">
        <span class="material-symbols-outlined text-[18px]">close</span>
      </button>
    </div>
  <?php endif; ?>

  <!-- Top Header Banner -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-surface-container-lowest p-6 rounded-2xl border border-outline-variant/60 shadow-sm">
    <div>
      <div class="flex items-center gap-2 text-label-md text-primary font-bold uppercase tracking-wider">
        <span class="material-symbols-outlined text-[20px] text-secondary">corporate_fare</span>
        Super Admin Portal
      </div>
      <h2 class="text-headline-md font-headline-md text-on-surface mt-1">Multi-School Management</h2>
      <p class="text-body-md font-body-md text-on-surface-variant mt-0.5">
        Configure institutional campuses, independent RBAC rules, academic calendars, and per-school Google Drive storage.
      </p>
    </div>
    <button onclick="openCreateSchoolModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition shadow-sm cursor-pointer shrink-0">
      <span class="material-symbols-outlined text-[18px]">add_circle</span>
      Register New School
    </button>
  </div>

  <!-- Summary Stats Grid -->
  <?php
    $total_schools = count($schools);
    $active_schools = 0;
    $total_students = 0;
    $total_teachers = 0;
    foreach ($schools as $s) {
        if ($s->status === 'Active') $active_schools++;
        $total_students += $s->stats->total_students ?? 0;
        $total_teachers += $s->stats->total_teachers ?? 0;
    }
    $inactive_schools = $total_schools - $active_schools;
  ?>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/60 shadow-sm flex items-center gap-3.5">
      <div class="w-12 h-12 rounded-xl bg-primary text-white flex items-center justify-center shrink-0">
        <span class="material-symbols-outlined text-[24px]">domain</span>
      </div>
      <div>
        <div class="text-[12px] font-semibold uppercase tracking-wider text-on-surface-variant">Total Schools</div>
        <div class="text-headline-md font-headline-md text-on-surface"><?php echo $total_schools; ?></div>
      </div>
    </div>
    <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/60 shadow-sm flex items-center gap-3.5">
      <div class="w-12 h-12 rounded-xl bg-secondary-container text-on-secondary-container flex items-center justify-center shrink-0">
        <span class="material-symbols-outlined text-[24px]">verified</span>
      </div>
      <div>
        <div class="text-[12px] font-semibold uppercase tracking-wider text-on-surface-variant">Active Campuses</div>
        <div class="text-headline-md font-headline-md text-secondary"><?php echo $active_schools; ?></div>
      </div>
    </div>
    <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/60 shadow-sm flex items-center gap-3.5">
      <div class="w-12 h-12 rounded-xl bg-surface-container-high text-on-surface-variant flex items-center justify-center shrink-0">
        <span class="material-symbols-outlined text-[24px]">group</span>
      </div>
      <div>
        <div class="text-[12px] font-semibold uppercase tracking-wider text-on-surface-variant">Total Students</div>
        <div class="text-headline-md font-headline-md text-on-surface"><?php echo number_format($total_students); ?></div>
      </div>
    </div>
    <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/60 shadow-sm flex items-center gap-3.5">
      <div class="w-12 h-12 rounded-xl bg-surface-container-high text-on-surface-variant flex items-center justify-center shrink-0">
        <span class="material-symbols-outlined text-[24px]">school</span>
      </div>
      <div>
        <div class="text-[12px] font-semibold uppercase tracking-wider text-on-surface-variant">Total Faculty</div>
        <div class="text-headline-md font-headline-md text-on-surface"><?php echo number_format($total_teachers); ?></div>
      </div>
    </div>
  </div>

  <!-- Schools Table Card -->
  <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/60 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-outline-variant/60 flex items-center justify-between">
      <div class="font-headline-md text-[16px] text-on-surface">Registered School Campuses</div>
      <span class="text-label-md font-bold px-2.5 py-1 rounded-full bg-surface-container-low text-on-surface-variant">
        <?php echo count($schools); ?> Campuses
      </span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-surface-container-low/60 border-b border-outline-variant/60">
            <th class="px-5 py-3.5 text-label-md font-semibold text-on-surface-variant uppercase tracking-wider">School / Code</th>
            <th class="px-5 py-3.5 text-label-md font-semibold text-on-surface-variant uppercase tracking-wider">Principal & Contact</th>
            <th class="px-5 py-3.5 text-label-md font-semibold text-on-surface-variant uppercase tracking-wider">Metrics</th>
            <th class="px-5 py-3.5 text-label-md font-semibold text-on-surface-variant uppercase tracking-wider">Storage Provider</th>
            <th class="px-5 py-3.5 text-label-md font-semibold text-on-surface-variant uppercase tracking-wider">Status</th>
            <th class="px-5 py-3.5 text-label-md font-semibold text-on-surface-variant uppercase tracking-wider text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-outline-variant/40">
          <?php if (empty($schools)): ?>
            <tr>
              <td colspan="6" class="px-5 py-8 text-center text-on-surface-variant text-body-md">
                No schools found in the system.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($schools as $s): 
              $is_active_context = ((int)$s->id === (int)$current_school_id);
            ?>
              <tr class="hover:bg-surface-container-low/40 transition-colors <?php echo $is_active_context ? 'bg-primary-fixed/20' : ''; ?>">
                <!-- School & Code -->
                <td class="px-5 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-surface-container border border-outline-variant/60 flex items-center justify-center font-bold text-primary shrink-0 overflow-hidden">
                      <?php if (!empty($s->logo) && file_exists(FCPATH . 'uploads/schools/' . $s->logo)): ?>
                        <img src="<?php echo base_url('uploads/schools/' . $s->logo); ?>" class="w-full h-full object-cover" alt="Logo" />
                      <?php else: ?>
                        <span><?php echo strtoupper(substr($s->school_name, 0, 2)); ?></span>
                      <?php endif; ?>
                    </div>
                    <div>
                      <div class="text-body-md font-bold text-on-surface flex items-center gap-1.5">
                        <?php echo html_escape($s->school_name); ?>
                        <?php if ($is_active_context): ?>
                          <span class="px-2 py-0.5 rounded-md bg-secondary text-on-secondary text-[10px] font-bold tracking-wide uppercase">Active Context</span>
                        <?php endif; ?>
                      </div>
                      <div class="text-[11px] text-on-surface-variant font-mono mt-0.5 flex items-center gap-2">
                        <span class="px-1.5 py-0.5 rounded bg-surface-container-high text-on-surface font-semibold"><?php echo html_escape($s->school_code); ?></span>
                        <?php if (!empty($s->established_year)): ?>
                          <span>Est. <?php echo (int)$s->established_year; ?></span>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                </td>

                <!-- Principal & Contact -->
                <td class="px-5 py-4">
                  <div class="text-body-md text-on-surface font-medium"><?php echo html_escape($s->principal_name ?: '—'); ?></div>
                  <div class="text-[12px] text-on-surface-variant mt-0.5">
                    <?php if (!empty($s->phone)): ?>
                      <div><?php echo html_escape($s->phone); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($s->email)): ?>
                      <div class="truncate max-w-[200px]"><?php echo html_escape($s->email); ?></div>
                    <?php endif; ?>
                  </div>
                </td>

                <!-- Metrics -->
                <td class="px-5 py-4">
                  <div class="flex items-center gap-3 text-[12px]">
                    <span title="Students" class="inline-flex items-center gap-1 font-semibold text-on-surface">
                      <span class="material-symbols-outlined text-[15px] text-on-surface-variant">person</span>
                      <?php echo (int)($s->stats->total_students ?? 0); ?>
                    </span>
                    <span title="Teachers" class="inline-flex items-center gap-1 font-semibold text-on-surface">
                      <span class="material-symbols-outlined text-[15px] text-on-surface-variant">school</span>
                      <?php echo (int)($s->stats->total_teachers ?? 0); ?>
                    </span>
                    <span title="Classes" class="inline-flex items-center gap-1 font-semibold text-on-surface">
                      <span class="material-symbols-outlined text-[15px] text-on-surface-variant">class</span>
                      <?php echo (int)($s->stats->total_classes ?? 0); ?>
                    </span>
                  </div>
                </td>

                <!-- Storage Provider -->
                <td class="px-5 py-4">
                  <?php 
                    $provider = $s->storage->provider ?? 'local';
                    $is_gdrive = ($provider === 'google_drive');
                  ?>
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold <?php echo $is_gdrive ? 'bg-primary text-white' : 'bg-surface-container-low text-on-surface-variant'; ?>">
                    <span class="material-symbols-outlined text-[15px]"><?php echo $is_gdrive ? 'cloud' : 'folder'; ?></span>
                    <?php echo $is_gdrive ? 'Google Drive' : 'Local Storage'; ?>
                  </span>
                </td>

                <!-- Status -->
                <td class="px-5 py-4">
                  <span class="px-2.5 py-1 rounded-full text-[11px] font-bold inline-flex items-center gap-1 <?php echo $s->status === 'Active' ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container-high text-on-surface-variant'; ?>">
                    <span class="w-1.5 h-1.5 rounded-full <?php echo $s->status === 'Active' ? 'bg-secondary' : 'bg-outline'; ?>"></span>
                    <?php echo html_escape($s->status); ?>
                  </span>
                </td>

                <!-- Actions -->
                <td class="px-5 py-4 text-right">
                  <div class="inline-flex items-center gap-1.5">
                    <?php if (!$is_active_context && $s->status === 'Active'): ?>
                      <button onclick="switchActiveSchool(<?php echo $s->id; ?>, '<?php echo html_escape(addslashes($s->school_name)); ?>')" class="px-3 py-1.5 rounded-lg bg-primary text-on-primary text-[12px] font-semibold hover:bg-primary-container transition flex items-center gap-1 shadow-sm" title="Switch active school context">
                        <span class="material-symbols-outlined text-[14px]">swap_horiz</span> Switch
                      </button>
                    <?php endif; ?>

                    <button onclick="openEditSchoolModal(<?php echo (int)$s->id; ?>)" class="p-1.5 rounded-lg border border-outline-variant/60 text-on-surface-variant hover:bg-surface-container-high transition" title="Edit School">
                      <span class="material-symbols-outlined text-[18px]">edit</span>
                    </button>

                    <button onclick="toggleSchoolStatus(<?php echo $s->id; ?>, '<?php echo html_escape(addslashes($s->school_name)); ?>', '<?php echo $s->status; ?>')" class="p-1.5 rounded-lg border border-outline-variant/60 <?php echo $s->status === 'Active' ? 'text-error hover:bg-error-container/30' : 'text-secondary hover:bg-secondary-container/30'; ?> transition" title="<?php echo $s->status === 'Active' ? 'Deactivate School' : 'Activate School'; ?>">
                      <span class="material-symbols-outlined text-[18px]"><?php echo $s->status === 'Active' ? 'toggle_on' : 'toggle_off'; ?></span>
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- =========================================================================
     Unified School Modal (Create & Edit Mode Single Source of Truth)
     ========================================================================= -->
<dialog id="schoolModal" class="rounded-2xl shadow-2xl p-0 w-[840px] max-w-[96vw] bg-surface-container-lowest backdrop:bg-on-surface/40 overflow-hidden border border-outline-variant/60">
  <form id="schoolForm" method="POST" action="<?php echo site_url('settings/schools/create'); ?>" enctype="multipart/form-data" class="flex flex-col max-h-[90vh]">
    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
    <input type="hidden" id="school_form_mode" name="form_mode" value="create" />
    <input type="hidden" id="school_form_id" name="school_id" value="" />
    <input type="hidden" id="school_admin_user_id" name="admin_user_id" value="" />

    <!-- Modal Header -->
    <div class="px-6 py-4 border-b border-outline-variant/60 flex items-center justify-between bg-surface-container-low/40 shrink-0">
      <div class="flex items-center gap-2.5">
        <div id="schoolModalIconContainer" class="w-9 h-9 rounded-xl bg-secondary-container text-on-secondary-container flex items-center justify-center">
          <span id="schoolModalIcon" class="material-symbols-outlined text-[22px]">domain_add</span>
        </div>
        <div>
          <h3 id="schoolModalTitle" class="font-headline-md text-headline-md text-on-surface">Register New School Campus</h3>
          <p id="schoolModalSubtitle" class="text-[12px] text-on-surface-variant">Configures campus metadata, dedicated School Admin account, and RBAC matrix in one transaction.</p>
        </div>
      </div>
      <button type="button" onclick="document.getElementById('schoolModal').close()" class="p-1 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>

    <!-- Modal Body -->
    <div class="p-6 space-y-6 overflow-y-auto">
      
      <!-- ===================================================================
           SECTION 1: Campus Profile & Information
           =================================================================== -->
      <div class="space-y-4">
        <div class="flex items-center gap-2 pb-2 border-b border-outline-variant/50">
          <span class="w-6 h-6 rounded-full bg-primary text-on-primary text-xs font-bold flex items-center justify-center">1</span>
          <h4 class="font-headline-md text-title-md font-bold text-on-surface">Campus Profile & Information</h4>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-label-md font-semibold text-on-surface mb-1">School Name <span class="text-error">*</span></label>
            <input type="text" id="school_name" name="school_name" required placeholder="e.g. Nalanda Gurukulam Public School" oninput="onSchoolNameInput(this.value)" class="w-full px-3.5 py-2.5 rounded-xl border border-outline-variant bg-surface-container-low text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary" />
          </div>
          <div>
            <div class="flex items-center justify-between mb-1">
              <label class="text-label-md font-semibold text-on-surface">School Code <span class="text-error">*</span></label>
              <button type="button" onclick="autoGenerateCode()" class="text-[11px] text-primary font-semibold hover:underline cursor-pointer flex items-center gap-1">
                <span class="material-symbols-outlined text-[13px]">auto_fix_high</span> Auto-Generate
              </button>
            </div>
            <div class="relative">
              <input type="text" id="school_code" name="school_code" required placeholder="e.g. NGPS001" class="w-full px-3.5 py-2.5 rounded-xl border border-outline-variant bg-surface-container-low text-body-md uppercase font-mono tracking-wider focus:ring-2 focus:ring-primary/20 focus:border-primary" />
            </div>
            <p class="text-[11px] text-on-surface-variant mt-1">Unique alphanumeric code (e.g. NGPM001, KHM001)</p>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-label-md font-semibold text-on-surface mb-1">Principal / Campus Head</label>
            <input type="text" id="principal_name" name="principal_name" placeholder="e.g. Dr. Rajesh Sharma" class="w-full px-3.5 py-2.5 rounded-xl border border-outline-variant bg-surface-container-low text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary" />
          </div>
          <div>
            <label class="block text-label-md font-semibold text-on-surface mb-1">Established Year</label>
            <input type="number" id="established_year" name="established_year" placeholder="<?php echo date('Y'); ?>" min="1800" max="2100" class="w-full px-3.5 py-2.5 rounded-xl border border-outline-variant bg-surface-container-low text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary" />
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-label-md font-semibold text-on-surface mb-1">Official Phone</label>
            <input type="text" id="school_phone" name="phone" placeholder="+91 484 000 0000" class="w-full px-3.5 py-2.5 rounded-xl border border-outline-variant bg-surface-container-low text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary" />
          </div>
          <div>
            <label class="block text-label-md font-semibold text-on-surface mb-1">Official Email</label>
            <input type="email" id="school_email" name="email" placeholder="contact@campus.edu" class="w-full px-3.5 py-2.5 rounded-xl border border-outline-variant bg-surface-container-low text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary" />
          </div>
        </div>

        <div>
          <label class="block text-label-md font-semibold text-on-surface mb-1">Website</label>
          <input type="text" id="school_website" name="website" placeholder="www.campus.edu" class="w-full px-3.5 py-2.5 rounded-xl border border-outline-variant bg-surface-container-low text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary" />
        </div>

        <div>
          <label class="block text-label-md font-semibold text-on-surface mb-1">Campus Address</label>
          <textarea id="school_address" name="address" rows="2" placeholder="Full postal campus address" class="w-full px-3.5 py-2 rounded-xl border border-outline-variant bg-surface-container-low text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
          <div>
            <label class="block text-label-md font-semibold text-on-surface mb-1">School Logo</label>
            <input type="file" id="school_logo" name="logo" accept="image/*" class="w-full text-xs text-on-surface-variant file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary file:text-white hover:file:bg-primary-dark cursor-pointer" />
            <span id="currentLogoIndicator" class="text-[11px] text-on-surface-variant mt-1 block" style="display:none;"></span>
          </div>
          <div>
            <label class="block text-label-md font-semibold text-on-surface mb-1">Status</label>
            <select id="school_status" name="status" class="w-full px-3.5 py-2.5 rounded-xl border border-outline-variant bg-surface-container-low text-body-md font-medium">
              <option value="Active" selected>Active</option>
              <option value="Inactive">Inactive</option>
            </select>
          </div>
        </div>

        <!-- Storage / Google Drive Settings -->
        <div class="p-4 rounded-xl border border-outline-variant/60 bg-surface-container-low/40 space-y-3">
          <div class="flex items-center gap-2 font-semibold text-[13px] text-on-surface">
            <span class="material-symbols-outlined text-primary text-[18px]">cloud</span>
            Document Storage / Google Drive Connection
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-semibold text-on-surface-variant mb-1">Storage Provider</label>
              <select id="storage_provider" name="storage_provider" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md">
                <option value="local" selected>Local File System</option>
                <option value="google_drive">Google Drive</option>
              </select>
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-on-surface-variant mb-1">Google Drive Folder ID</label>
              <input type="text" id="storage_folder_id" name="storage_folder_id" placeholder="Optional Folder ID" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono" />
            </div>
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-on-surface-variant mb-1">Credentials JSON (Google Drive Client Config)</label>
            <textarea id="storage_credentials_json" name="storage_credentials_json" rows="2" placeholder='{"client_id": "...", "client_secret": "..."}' class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-xs font-mono"></textarea>
          </div>
        </div>
      </div>

      <!-- ===================================================================
           SECTION 2: School Administrator Account
           =================================================================== -->
      <div class="space-y-4 pt-2">
        <div class="flex items-center gap-2 pb-2 border-b border-outline-variant/50">
          <span class="w-6 h-6 rounded-full bg-secondary text-on-secondary text-xs font-bold flex items-center justify-center">2</span>
          <div>
            <h4 class="font-headline-md text-title-md font-bold text-on-surface">Primary School Administrator Account</h4>
            <p class="text-[11px] text-on-surface-variant">This administrator manages students, teachers, classes, fees, and academic records for this campus.</p>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-label-md font-semibold text-on-surface mb-1">Admin Full Name <span class="text-error">*</span></label>
            <input type="text" id="admin_name" name="admin_name" required placeholder="e.g. Principal Rajesh Sharma" class="w-full px-3.5 py-2.5 rounded-xl border border-outline-variant bg-surface-container-low text-body-md focus:ring-2 focus:ring-secondary/20 focus:border-secondary" />
          </div>
          <div>
            <label class="block text-label-md font-semibold text-on-surface mb-1">Admin Username (Unique) <span class="text-error">*</span></label>
            <input type="text" id="admin_username" name="admin_username" required placeholder="e.g. admin_ngps" class="w-full px-3.5 py-2.5 rounded-xl border border-outline-variant bg-surface-container-low text-body-md font-mono focus:ring-2 focus:ring-secondary/20 focus:border-secondary" />
            <p class="text-[11px] text-on-surface-variant mt-1">Letters, numbers, underscores (used to log in)</p>
          </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-label-md font-semibold text-on-surface mb-1">
              Password <span id="admin_password_required_star" class="text-error">*</span>
            </label>
            <input type="password" id="admin_password" name="admin_password" required minlength="6" placeholder="Min. 6 characters" class="w-full px-3.5 py-2.5 rounded-xl border border-outline-variant bg-surface-container-low text-body-md focus:ring-2 focus:ring-secondary/20 focus:border-secondary" />
            <p id="admin_password_help" class="text-[11px] text-on-surface-variant mt-1" style="display:none;">Leave blank to keep existing password</p>
          </div>
          <div>
            <label class="block text-label-md font-semibold text-on-surface mb-1">Official Email <span class="text-error">*</span></label>
            <input type="email" id="admin_email" name="admin_email" required placeholder="admin@campus.edu" class="w-full px-3.5 py-2.5 rounded-xl border border-outline-variant bg-surface-container-low text-body-md focus:ring-2 focus:ring-secondary/20 focus:border-secondary" />
          </div>
          <div>
            <label class="block text-label-md font-semibold text-on-surface mb-1">Phone Number</label>
            <input type="text" id="admin_phone" name="admin_phone" placeholder="+91 98765 43210" class="w-full px-3.5 py-2.5 rounded-xl border border-outline-variant bg-surface-container-low text-body-md focus:ring-2 focus:ring-secondary/20 focus:border-secondary" />
          </div>
        </div>
      </div>

      <!-- ===================================================================
           SECTION 3: School Admin Permission Matrix (Reusable Component)
           =================================================================== -->
      <?php $this->load->view('pages/schools/_permission_matrix', [
        'matrix_tree'          => !empty($permission_matrix_tree) ? $permission_matrix_tree : [],
        'active_modules_count' => !empty($active_modules_count) ? $active_modules_count : 0
      ]); ?>


    </div>

    <!-- Modal Footer -->
    <div class="px-6 py-4 border-t border-outline-variant/60 flex items-center justify-between bg-surface-container-low/40 shrink-0">
      <div id="schoolModalFooterNote" class="text-[12px] text-on-surface-variant flex items-center gap-1">
      </div>
      <div class="flex items-center gap-3">
        <button type="button" onclick="document.getElementById('schoolModal').close()" class="px-4 py-2.5 rounded-xl border border-outline-variant text-on-surface-variant text-label-md font-semibold hover:bg-surface-container-high transition">Cancel</button>
        <button type="submit" id="schoolModalSubmitBtn" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition shadow-sm cursor-pointer">
          <span id="schoolModalSubmitIcon" class="material-symbols-outlined text-[18px]">check_circle</span>
          <span id="schoolModalSubmitText">Save & Initialize School</span>
        </button>
      </div>
    </div>
  </form>
</dialog>
<script>
let codeManuallyEdited = false;
let usernameManuallyEdited = false;

function openCreateSchoolModal() {
  const form = document.getElementById('schoolForm');
  form.reset();
  form.action = "<?php echo site_url('settings/schools/create'); ?>";
  document.getElementById('school_form_mode').value = 'create';
  document.getElementById('school_form_id').value = '';
  document.getElementById('school_admin_user_id').value = '';
  document.getElementById('schoolModalTitle').textContent = 'Register New School Campus';
  document.getElementById('schoolModalSubtitle').textContent = 'Configures campus metadata, dedicated School Admin account, and RBAC matrix in one transaction.';
  document.getElementById('schoolModalIcon').textContent = 'domain_add';
  document.getElementById('schoolModalIconContainer').className = 'w-9 h-9 rounded-xl bg-secondary-container text-on-secondary-container flex items-center justify-center';
  document.getElementById('schoolModalSubmitText').textContent = 'Save & Initialize School';
  document.getElementById('schoolModalFooterNote').innerHTML = '<span class="material-symbols-outlined text-[16px] text-secondary">verified_user</span> Transactional Creation (All-or-Nothing Rollback Protection)';              
  const pwdInput = document.getElementById('admin_password');
  pwdInput.required = true;
  pwdInput.value = '';
  pwdInput.placeholder = 'Min. 6 characters';
  document.getElementById('admin_password_required_star').style.display = 'inline';
  document.getElementById('admin_password_help').style.display = 'none';
  document.getElementById('currentLogoIndicator').style.display = 'none';

  document.getElementById('perm_mode_all').checked = true;
  selectAllPerms(true);

  codeManuallyEdited = false;
  usernameManuallyEdited = false;

  document.getElementById('schoolModal').showModal();
}

function openEditSchoolModal(schoolId) {
  const form = document.getElementById('schoolForm');
  form.reset();
  form.action = "<?php echo site_url('settings/schools/edit'); ?>/" + schoolId;

  document.getElementById('school_form_mode').value = 'edit';
  document.getElementById('school_form_id').value = schoolId;
  document.getElementById('schoolModalTitle').textContent = 'Edit School Campus';
  document.getElementById('schoolModalSubtitle').textContent = 'Update campus profile, storage configurations, administrator account, and RBAC permissions.';
  document.getElementById('schoolModalIcon').textContent = 'edit_note';
  document.getElementById('schoolModalIconContainer').className = 'w-9 h-9 rounded-xl bg-primary-container text-on-primary-container flex items-center justify-center';
  document.getElementById('schoolModalSubmitText').textContent = 'Update School';

  // Password is optional in edit mode
  const pwdInput = document.getElementById('admin_password');
  pwdInput.required = false;
  pwdInput.value = '';
  pwdInput.placeholder = 'Leave blank to keep existing password';
  document.getElementById('admin_password_required_star').style.display = 'none';
  document.getElementById('admin_password_help').style.display = 'block';

  codeManuallyEdited = true;
  usernameManuallyEdited = true;

  // Open modal immediately
  document.getElementById('schoolModal').showModal();

  // Load complete existing data via AJAX
  $.ajax({
    url: "<?php echo site_url('settings/schools/get_details'); ?>/" + schoolId,
    type: "GET",
    dataType: "json",
    success: function(res) {
      if (!res || !res.status) {
        alert(res ? res.message : 'Could not fetch school details.');
        return;
      }
      const s = res.school || {};
      const st = res.storage || {};
      const a = res.admin || {};
      const perms = res.permissions || [];
      const permMode = res.permission_mode || 'all';

      // Section 1: Campus
      document.getElementById('school_name').value = s.school_name || '';
      document.getElementById('school_code').value = s.school_code || '';
      document.getElementById('principal_name').value = s.principal_name || '';
      document.getElementById('established_year').value = s.established_year || '';
      document.getElementById('school_phone').value = s.phone || '';
      document.getElementById('school_email').value = s.email || '';
      document.getElementById('school_website').value = s.website || '';
      document.getElementById('school_address').value = s.address || '';
      document.getElementById('school_status').value = s.status || 'Active';

      const logoInd = document.getElementById('currentLogoIndicator');
      if (s.logo) {
        logoInd.textContent = 'Current Logo: ' + s.logo;
        logoInd.style.display = 'block';
      } else {
        logoInd.style.display = 'none';
      }

      // Storage
      document.getElementById('storage_provider').value = st.provider || 'local';
      document.getElementById('storage_folder_id').value = st.folder_id || '';
      document.getElementById('storage_credentials_json').value = st.credentials_json || '';

      // Section 2: Admin
      document.getElementById('school_admin_user_id').value = a.user_id || '';
      document.getElementById('admin_name').value = a.name || '';
      document.getElementById('admin_username').value = a.username || '';
      document.getElementById('admin_email').value = a.email || '';
      document.getElementById('admin_phone').value = a.phone || '';
      pwdInput.value = '';

      // Section 3: Permissions
      if (permMode === 'all') {
        const allRadio = document.getElementById('perm_mode_all');
        if (allRadio) allRadio.checked = true;
        selectAllPerms(true);
        if (typeof updateAllMatrixStates === 'function') {
          updateAllMatrixStates();
        } else if (typeof updateAllParentStates === 'function') {
          updateAllParentStates();
        }
      } else {
        const customRadio = document.getElementById('perm_mode_custom');
        if (customRadio) customRadio.checked = true;
        selectAllPerms(false);
        perms.forEach(function(pid) {
          document.querySelectorAll('.perm-chk[value="' + pid + '"]').forEach(cb => {
            cb.checked = true;
          });
        });
        if (typeof updateAllMatrixStates === 'function') {
          updateAllMatrixStates();
        } else if (typeof updateAllParentStates === 'function') {
          updateAllParentStates();
        }
      }
    },
    error: function(xhr) {
      alert('Error loading school details: ' + (xhr.responseJSON?.message || 'Server connection error.'));
    }
  });
}

function onSchoolNameInput(val) {
  if (document.getElementById('school_form_mode')?.value === 'edit') {
    return;
  }
  const clean = (val || '').trim();
  if (!clean) return;

  const codeInput = document.getElementById('school_code');
  if (codeInput && !codeManuallyEdited) {
    const words = clean.replace(/[^a-zA-Z0-9\s]/g, '').split(/\s+/).filter(Boolean);
    let prefix = '';
    if (words.length >= 2) {
      prefix = words.slice(0, 4).map(w => w[0]).join('').toUpperCase();
    } else if (words.length === 1 && words[0].length >= 3) {
      prefix = words[0].slice(0, 4).toUpperCase();
    }
    if (prefix.length < 3) prefix = 'SCH';
    codeInput.value = prefix + '001';
  }

  const userField = document.getElementById('admin_username');
  if (userField && !usernameManuallyEdited) {
    const words = clean.toLowerCase().replace(/[^a-z0-9\s]/g, '').split(/\s+/).filter(Boolean);
    const shortName = words.length > 0 ? (words[0].length > 8 ? words[0].slice(0, 6) : words[0]) : 'school';
    userField.value = 'admin_' + shortName;
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const codeInput = document.getElementById('school_code');
  if (codeInput) {
    codeInput.addEventListener('input', function() {
      codeManuallyEdited = (this.value.trim() !== '');
    });
  }
  const userField = document.getElementById('admin_username');
  if (userField) {
    userField.addEventListener('input', function() {
      usernameManuallyEdited = (this.value.trim() !== '');
    });
  }
  if (typeof updateAllMatrixStates === 'function') {
    updateAllMatrixStates();
  } else {
    updateAllParentStates();
  }
});

function autoGenerateCode() {
  const name = (document.getElementById('school_name')?.value || '').trim();
  $.ajax({
    url: "<?php echo site_url('settings/schools/generate_code'); ?>",
    type: "GET",
    data: { name: name },
    dataType: "json",
    success: function(res) {
      if (res && res.status && res.school_code) {
        document.getElementById('school_code').value = res.school_code;
        codeManuallyEdited = true;
      }
    }
  });
}

function togglePermMode(mode) {
  if (mode === 'all') {
    selectAllPerms(true);
    if (typeof updateAllMatrixStates === 'function') {
      updateAllMatrixStates();
    } else if (typeof updateAllParentStates === 'function') {
      updateAllParentStates();
    }
  }
}

function selectAllPerms(check) {
  document.querySelectorAll('.perm-chk').forEach(cb => cb.checked = check);
  document.querySelectorAll('.perm-parent-chk').forEach(cb => {
    cb.checked = check;
    cb.indeterminate = false;
  });
  document.querySelectorAll('.perm-mod-master-chk').forEach(cb => {
    cb.checked = check;
    cb.indeterminate = false;
  });
  document.querySelectorAll('.perm-submod-master-chk').forEach(cb => {
    cb.checked = check;
    cb.indeterminate = false;
  });
}

function toggleModuleCheckboxes(modId, check) {
  document.querySelectorAll('.perm-mod-' + modId).forEach(cb => cb.checked = check);
  const parent = document.getElementById('mod_parent_' + modId);
  if (parent) {
    parent.checked = check;
    parent.indeterminate = false;
  }
}

function onModuleParentToggle(modId, checked) {
  toggleModuleCheckboxes(modId, checked);
}

function onChildPermChange(modId) {
  const customRadio = document.getElementById('perm_mode_custom');
  if (customRadio && !customRadio.checked) {
    customRadio.checked = true;
  }
  updateModuleParentState(modId);
}

function updateModuleParentState(modId) {
  const parent = document.getElementById('mod_parent_' + modId);
  if (!parent) return;

  const children = document.querySelectorAll('.perm-mod-' + modId);
  if (!children.length) return;

  let checkedCount = 0;
  children.forEach(cb => {
    if (cb.checked) checkedCount++;
  });

  if (checkedCount === children.length) {
    parent.checked = true;
    parent.indeterminate = false;
  } else if (checkedCount === 0) {
    parent.checked = false;
    parent.indeterminate = false;
  } else {
    parent.checked = false;
    parent.indeterminate = true;
  }
}
 
function updateAllParentStates() {
  document.querySelectorAll('.perm-parent-chk').forEach(parent => {
    const modId = parent.getAttribute('data-mod-id');
    if (modId) {
      updateModuleParentState(modId);
    }
  });
}

function switchActiveSchool(schoolId, schoolName) {
  if (!confirm(`Switch active school context to "${schoolName}"?\n\nThe dashboard, students, staff, classes, and academic years will immediately change to this school.`)) {
    return;
  }

  const postData = { school_id: schoolId };
  if (window.CSRF_TOKEN_NAME && window.CSRF_HASH) {
    postData[window.CSRF_TOKEN_NAME] = window.CSRF_HASH;
  }

  $.ajax({
    url: "<?php echo site_url('settings/schools/switch'); ?>",
    type: "POST",
    dataType: "json",
    data: postData,
    success: function(res) {
      if (res && res.status) {
        window.location.reload();
      } else {
        alert(res.message || 'Failed to switch school.');
      }
    },
    error: function(xhr) {
      alert('Error switching school: ' + (xhr.responseJSON?.message || 'Server error.'));
    }
  });
}

function toggleSchoolStatus(schoolId, schoolName, currentStatus) {
  const action = (currentStatus === 'Active') ? 'Deactivate' : 'Activate';
  if (!confirm(`${action} school "${schoolName}"?\n\nDeactivated schools cannot be accessed by normal users.`)) {
    return;
  }

  window.location.href = "<?php echo site_url('settings/schools/toggle_status'); ?>/" + schoolId;
}
</script>
