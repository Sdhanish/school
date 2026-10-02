<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
  $fullName = ($staff) ? trim($staff->full_name) : 'Staff Profile';
  $nameParts = explode(' ', $fullName);
  $initials = '';
  foreach ($nameParts as $np) { if (!empty($np)) $initials .= strtoupper($np[0]); }
  if (strlen($initials) > 2) $initials = substr($initials, 0, 2);
  $isTeacher = ($staff->staff_type === 'teacher');
  $hasPhoto = (!empty($staff->photo) && file_exists(FCPATH . 'uploads/staff/' . $staff->photo));
  $statusBadge = ($staff->status == 1)
    ? '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-secondary-container text-on-secondary-container">Active</span>'
    : '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-surface-container-high text-on-surface-variant">Inactive</span>';
  $typeBadge = $isTeacher
    ? '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-primary-fixed/30 text-primary">Teaching Faculty</span>'
    : '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-surface-container-high text-on-surface-variant">Non-Teaching Staff</span>';
?>

  <!-- Header Card -->
  <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-5 mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="flex items-center gap-4">
      <?php if ($hasPhoto): ?>
        <img src="<?php echo base_url('uploads/staff/' . $staff->photo); ?>" alt="<?php echo html_escape($fullName); ?>" class="w-16 h-20 rounded-xl object-cover border border-outline-variant/60 shadow-sm shrink-0"/>
      <?php else: ?>
        <div class="w-16 h-16 rounded-xl bg-primary text-white flex items-center justify-center text-2xl font-bold shrink-0 shadow-sm"><?php echo html_escape($initials); ?></div>
      <?php endif; ?>
      <div class="min-w-0">
        <div class="flex items-center gap-2.5 flex-wrap">
          <h2 class="font-headline-md text-headline-md text-on-surface"><?php echo html_escape($fullName); ?></h2>
          <?php echo $typeBadge; ?>
          <?php if ($isTeacher): ?>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-secondary-container text-on-secondary-container">Department / Group: <?php echo html_escape($staff->group_name ?: 'Not Assigned'); ?></span>
          <?php endif; ?>
          <?php echo $statusBadge; ?>
        </div>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">
          <span class="font-mono font-medium text-primary"><?php echo html_escape($staff->employee_code); ?></span>
          · <span class="font-medium text-on-surface"><?php echo html_escape($staff->designation_name ?: 'Staff'); ?></span>
          · Joined <span class="font-medium text-on-surface"><?php echo date('d M Y', strtotime($staff->joining_date)); ?></span>
        </p>
      </div>
    </div>
    <div class="flex items-center gap-2 flex-wrap shrink-0">
      <a href="<?php echo site_url('staff/edit/' . $staff_id); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors"><span class="material-symbols-outlined text-[18px]">edit</span>Edit</a>
      <?php if ($isTeacher): ?>
        <a href="<?php echo site_url('staff/workload?staff_id=' . $staff_id); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm"><span class="material-symbols-outlined text-[18px]">calendar_month</span>Workload</a>
      <?php endif; ?>
      <a href="<?php echo site_url('staff'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors"><span class="material-symbols-outlined text-[18px]">arrow_back</span>Back</a>
    </div>
  </div>

  <!-- Interactive Profile Tabs -->
  <div class="flex gap-2 border-b border-outline-variant/60 mb-6 overflow-x-auto" id="profile-tabs">
    <button onclick="switchTab('overview')" class="tab-btn px-4 py-2.5 text-body-md font-medium border-b-2 border-secondary text-primary cursor-pointer" data-tab="overview">Overview</button>
    <button onclick="switchTab('personal')" class="tab-btn px-4 py-2.5 text-body-md text-on-surface-variant hover:text-on-surface border-b-2 border-transparent cursor-pointer" data-tab="personal">Personal & Contact</button>
    <button onclick="switchTab('employment')" class="tab-btn px-4 py-2.5 text-body-md text-on-surface-variant hover:text-on-surface border-b-2 border-transparent cursor-pointer" data-tab="employment">Employment & Salary</button>
    <?php if ($isTeacher): ?>
      <button onclick="switchTab('professional')" class="tab-btn px-4 py-2.5 text-body-md text-on-surface-variant hover:text-on-surface border-b-2 border-transparent cursor-pointer" data-tab="professional">Professional & Specialization</button>
      <button onclick="switchTab('workload')" class="tab-btn px-4 py-2.5 text-body-md text-on-surface-variant hover:text-on-surface border-b-2 border-transparent cursor-pointer" data-tab="workload">Teacher Workload (<?php echo count($staff->workload); ?>)</button>
    <?php endif; ?>
    <button onclick="switchTab('documents')" class="tab-btn px-4 py-2.5 text-body-md text-on-surface-variant hover:text-on-surface border-b-2 border-transparent cursor-pointer" data-tab="documents">Documents (<?php echo count($staff->documents); ?>)</button>
    <button onclick="switchTab('attendance')" class="tab-btn px-4 py-2.5 text-body-md text-on-surface-variant hover:text-on-surface border-b-2 border-transparent cursor-pointer" data-tab="attendance">Attendance Summary</button>
    <button onclick="switchTab('leave')" class="tab-btn px-4 py-2.5 text-body-md text-on-surface-variant hover:text-on-surface border-b-2 border-transparent cursor-pointer" data-tab="leave">Leave History (<?php echo count($staff->leaves); ?>)</button>
  </div>

  <!-- TAB 1: OVERVIEW -->
  <div id="tab-overview" class="tab-pane space-y-5">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      
      <div class="lg:col-span-2 space-y-5">
        <!-- Quick Details -->
        <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-5">
          <h3 class="font-headline-md text-headline-md text-on-surface mb-3 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px]">badge</span>Staff Details
          </h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-body-md">
            <div><div class="text-on-surface-variant text-[12px]">Employee Code</div><div class="font-mono font-bold text-primary"><?php echo html_escape($staff->employee_code); ?></div></div>
            <div><div class="text-on-surface-variant text-[12px]">Full Name</div><div class="font-medium text-on-surface"><?php echo html_escape($staff->full_name); ?></div></div>
            <div><div class="text-on-surface-variant text-[12px]">Staff Category</div><div class="font-medium text-on-surface"><?php echo html_escape($staff->staff_type === 'teacher' ? 'Teaching Faculty' : 'Non-Teaching'); ?></div></div>
            <?php if ($isTeacher): ?>
              <div><div class="text-on-surface-variant text-[12px]">Department / Group</div><div class="font-bold text-primary"><?php echo html_escape($staff->group_name ?: '—'); ?></div></div>
            <?php endif; ?>
            <div><div class="text-on-surface-variant text-[12px]">Designation</div><div class="font-medium text-on-surface"><?php echo html_escape($staff->designation_name ?: '—'); ?></div></div>
            <div><div class="text-on-surface-variant text-[12px]">Joining Date</div><div class="font-medium text-on-surface"><?php echo date('d M Y', strtotime($staff->joining_date)); ?></div></div>
            <div><div class="text-on-surface-variant text-[12px]">Phone</div><div class="font-medium text-on-surface"><?php echo html_escape($staff->phone); ?></div></div>
            <div><div class="text-on-surface-variant text-[12px]">Email</div><div class="font-medium text-on-surface truncate"><?php echo html_escape($staff->email); ?></div></div>
            <div><div class="text-on-surface-variant text-[12px]">Employment Status</div><div class="font-medium text-secondary"><?php echo html_escape($staff->employment_status ?: 'Active'); ?></div></div>
          </div>
        </div>

        <!-- Attendance Stats -->
        <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-5">
          <div class="flex items-center justify-between mb-3">
            <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
              <span class="material-symbols-outlined text-secondary text-[20px]">fact_check</span>Attendance Overview
            </h3>
            <span class="text-label-md font-bold text-secondary"><?php echo $staff->attendance->percentage; ?>% Present</span>
          </div>
          <div class="grid grid-cols-4 gap-3 text-center">
            <div class="p-3 rounded-lg bg-surface-container-low border border-outline-variant/30">
              <div class="text-title-lg font-bold text-on-surface"><?php echo $staff->attendance->total_days; ?></div>
              <div class="text-[11px] text-on-surface-variant">Recorded Days</div>
            </div>
            <div class="p-3 rounded-lg bg-secondary-container/20 border border-secondary/20">
              <div class="text-title-lg font-bold text-secondary"><?php echo $staff->attendance->present; ?></div>
              <div class="text-[11px] text-on-secondary-container">Present</div>
            </div>
            <div class="p-3 rounded-lg bg-tertiary-container/20 border border-tertiary/20">
              <div class="text-title-lg font-bold text-tertiary"><?php echo $staff->attendance->leave; ?></div>
              <div class="text-[11px] text-on-tertiary-container">On Leave</div>
            </div>
            <div class="p-3 rounded-lg bg-error-container/20 border border-error/20">
              <div class="text-title-lg font-bold text-error"><?php echo $staff->attendance->absent; ?></div>
              <div class="text-[11px] text-on-error-container">Absent</div>
            </div>
          </div>
        </div>

        <?php if ($isTeacher && !empty($staff->workload)): ?>
          <!-- Teacher Workload Quick List -->
          <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-5">
            <div class="flex items-center justify-between mb-3">
              <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[20px]">calendar_month</span>Class & Subject Allocations
              </h3>
              <a href="<?php echo site_url('staff/workload?staff_id=' . $staff_id); ?>" class="text-label-md text-primary hover:underline">Manage</a>
            </div>
            <div class="table-scroll overflow-x-auto border border-outline-variant/40 rounded-lg">
              <table class="w-full data-table zebra border-collapse">
                <thead>
                  <tr class="border-b border-outline-variant/60 bg-surface-container-low">
                    <th class="text-left px-3.5 py-2 text-label-md text-on-surface-variant">Subject</th>
                    <th class="text-left px-3.5 py-2 text-label-md text-on-surface-variant">Class & Division</th>
                    <th class="text-left px-3.5 py-2 text-label-md text-on-surface-variant">Periods/Wk</th>
                    <th class="text-left px-3.5 py-2 text-label-md text-on-surface-variant">Days</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30 text-body-md">
                  <?php foreach ($staff->workload as $wl): ?>
                    <tr>
                      <td class="px-3.5 py-2 font-medium text-on-surface"><?php echo html_escape($wl->subject_name); ?></td>
                      <td class="px-3.5 py-2 text-on-surface"><?php echo html_escape($wl->class_name . (!empty($wl->division_name) ? ' ' . $wl->division_name : '')); ?></td>
                      <td class="px-3.5 py-2 font-mono font-semibold text-primary"><?php echo $wl->periods; ?></td>
                      <td class="px-3.5 py-2 text-on-surface-variant text-[12px]"><?php echo html_escape($wl->working_days); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php endif; ?>

      </div>

      <!-- Right Column: Documents & Quick Actions -->
      <div class="space-y-5">
        <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-5">
          <div class="flex items-center justify-between mb-3">
            <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
              <span class="material-symbols-outlined text-primary text-[20px]">folder</span>Documents
            </h3>
            <button onclick="switchTab('documents')" class="text-label-md text-primary hover:underline">Manage All</button>
          </div>
          <?php if (!empty($staff->documents)): ?>
            <ul class="divide-y divide-outline-variant/30 text-body-md">
              <?php foreach ($staff->documents as $doc): ?>
                <li class="py-2.5 flex items-center justify-between gap-2">
                  <div class="flex items-center gap-2 min-w-0">
                    <span class="material-symbols-outlined text-primary text-[18px] shrink-0">description</span>
                    <div class="min-w-0 truncate">
                      <div class="font-medium text-on-surface text-[13px] truncate"><?php echo html_escape($doc->document_name); ?></div>
                      <div class="text-[11px] text-on-surface-variant truncate"><?php echo html_escape($doc->document_type); ?> • <?php echo date('d M Y', strtotime($doc->created_at)); ?></div>
                    </div>
                  </div>
                  <div class="flex items-center gap-1 shrink-0">
                    <a href="<?php echo site_url('staff/view_document/' . $doc->document_id); ?>" target="_blank" title="View Document" class="p-1 text-on-surface-variant hover:text-primary rounded hover:bg-surface-container-high transition-colors">
                      <span class="material-symbols-outlined text-[16px]">visibility</span>
                    </a>
                    <a href="<?php echo site_url('staff/download_document/' . $doc->document_id); ?>" title="Download Document" class="p-1 text-on-surface-variant hover:text-secondary rounded hover:bg-surface-container-high transition-colors">
                      <span class="material-symbols-outlined text-[16px]">download</span>
                    </a>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="text-body-md text-on-surface-variant py-2">No documents uploaded.</p>
          <?php endif; ?>
        </div>

        <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-5">
          <h3 class="font-headline-md text-headline-md text-on-surface mb-3 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px]">home</span>Address
          </h3>
          <p class="text-body-md text-on-surface leading-relaxed"><?php echo nl2br(html_escape($staff->address ?: 'No address provided.')); ?></p>
        </div>
      </div>

    </div>
  </div>

  <!-- TAB 2: PERSONAL & CONTACT -->
  <div id="tab-personal" class="tab-pane hidden space-y-5">
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-6">
      <h3 class="font-headline-md text-headline-md text-on-surface mb-4">Complete Personal & Contact Information</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-body-md">
        <div class="space-y-4">
          <div><span class="text-on-surface-variant text-sm block">Full Name</span><span class="font-medium text-on-surface"><?php echo html_escape($staff->full_name); ?></span></div>
          <div><span class="text-on-surface-variant text-sm block">Gender</span><span class="font-medium text-on-surface"><?php echo html_escape($staff->gender); ?></span></div>
          <div><span class="text-on-surface-variant text-sm block">Date of Birth</span><span class="font-medium text-on-surface"><?php echo $staff->date_of_birth ? date('d M Y', strtotime($staff->date_of_birth)) : '—'; ?></span></div>
          <div><span class="text-on-surface-variant text-sm block">Blood Group</span><span class="font-medium text-on-surface"><?php echo html_escape($staff->blood_group ?: '—'); ?></span></div>
        </div>
        <div class="space-y-4">
          <div><span class="text-on-surface-variant text-sm block">Primary Phone</span><span class="font-medium text-on-surface"><?php echo html_escape($staff->phone); ?></span></div>
          <div><span class="text-on-surface-variant text-sm block">Alternate Phone</span><span class="font-medium text-on-surface"><?php echo html_escape($staff->alternate_phone ?: '—'); ?></span></div>
          <div><span class="text-on-surface-variant text-sm block">Email Address</span><span class="font-medium text-on-surface"><?php echo html_escape($staff->email); ?></span></div>
          <div><span class="text-on-surface-variant text-sm block">Residential Address</span><span class="font-medium text-on-surface leading-relaxed"><?php echo nl2br(html_escape($staff->address ?: '—')); ?></span></div>
        </div>
      </div>
    </div>
  </div>

  <!-- TAB 3: EMPLOYMENT & SALARY -->
  <div id="tab-employment" class="tab-pane hidden space-y-5">
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-6">
      <h3 class="font-headline-md text-headline-md text-on-surface mb-4">Employment Record & Compensation</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-body-md">
        <div class="space-y-4">
          <div><span class="text-on-surface-variant text-sm block">Employee ID</span><span class="font-mono font-bold text-primary"><?php echo html_escape($staff->employee_code); ?></span></div>
          <div><span class="text-on-surface-variant text-sm block">Staff Type</span><span class="font-medium text-on-surface"><?php echo html_escape($staff->staff_type === 'teacher' ? 'Teaching Staff' : 'Non-Teaching Staff'); ?></span></div>
          <?php if ($isTeacher): ?>
            <div><span class="text-on-surface-variant text-sm block">Department / Group</span><span class="font-bold text-primary"><?php echo html_escape($staff->group_name ?: '—'); ?></span></div>
          <?php endif; ?>
          <div><span class="text-on-surface-variant text-sm block">Designation</span><span class="font-medium text-on-surface"><?php echo html_escape($staff->designation_name ?: '—'); ?></span></div>
        </div>
        <div class="space-y-4">
          <div><span class="text-on-surface-variant text-sm block">Date of Joining</span><span class="font-medium text-on-surface"><?php echo date('d M Y', strtotime($staff->joining_date)); ?></span></div>
          <div><span class="text-on-surface-variant text-sm block">Monthly Salary</span><span class="font-bold text-on-surface text-title-md">₹<?php echo number_format($staff->salary, 2); ?></span></div>
          <div><span class="text-on-surface-variant text-sm block">Employment Status</span><span class="font-medium text-secondary"><?php echo html_escape($staff->employment_status ?: 'Active'); ?></span></div>
        </div>
      </div>
    </div>
  </div>

  <?php if ($isTeacher): ?>
    <!-- TAB 4: PROFESSIONAL (TEACHERS ONLY) -->
    <div id="tab-professional" class="tab-pane hidden space-y-5">
      <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-6">
        <h3 class="font-headline-md text-headline-md text-on-surface mb-4">Professional Qualifications & Academic Specialization</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-body-md">
          <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant/40">
            <span class="text-on-surface-variant text-[12px] block">Educational Qualification</span>
            <span class="font-semibold text-on-surface text-title-sm mt-1 block"><?php echo html_escape($staff->qualification ?: '—'); ?></span>
          </div>
          <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant/40">
            <span class="text-on-surface-variant text-[12px] block">Total Teaching Experience</span>
            <span class="font-semibold text-on-surface text-title-sm mt-1 block"><?php echo html_escape($staff->experience ?: '—'); ?></span>
          </div>
          <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant/40">
            <span class="text-on-surface-variant text-[12px] block">Subject Specialization</span>
            <span class="font-semibold text-primary text-title-sm mt-1 block"><?php echo html_escape($staff->specialization ?: '—'); ?></span>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 5: WORKLOAD (TEACHERS ONLY) -->
    <div id="tab-workload" class="tab-pane hidden space-y-5">
      <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-headline-md text-headline-md text-on-surface">Teacher Workload Allocations</h3>
          <a href="<?php echo site_url('staff/workload?staff_id=' . $staff_id); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors"><span class="material-symbols-outlined text-[18px]">add</span>Assign Workload</a>
        </div>
        <div class="table-scroll overflow-x-auto border border-outline-variant/40 rounded-lg">
          <table class="w-full data-table zebra border-collapse">
            <thead>
              <tr class="border-b border-outline-variant/60 bg-surface-container-low">
                <th class="text-left px-4 py-2.5 text-label-md text-on-surface-variant">Subject</th>
                <th class="text-left px-4 py-2.5 text-label-md text-on-surface-variant">Class & Division</th>
                <th class="text-left px-4 py-2.5 text-label-md text-on-surface-variant">Periods/Week</th>
                <th class="text-left px-4 py-2.5 text-label-md text-on-surface-variant">Working Days</th>
                <th class="text-left px-4 py-2.5 text-label-md text-on-surface-variant">Remarks</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/30 text-body-md">
              <?php if (empty($staff->workload)): ?>
                <tr><td colspan="5" class="px-4 py-6 text-center text-on-surface-variant">No workload periods assigned yet.</td></tr>
              <?php else: ?>
                <?php foreach ($staff->workload as $wl): ?>
                  <tr>
                    <td class="px-4 py-3 font-semibold text-on-surface"><?php echo html_escape($wl->subject_name); ?></td>
                    <td class="px-4 py-3 text-on-surface"><?php echo html_escape($wl->class_name . (!empty($wl->division_name) ? ' ' . $wl->division_name : '')); ?></td>
                    <td class="px-4 py-3 font-mono font-bold text-primary"><?php echo $wl->periods; ?> periods</td>
                    <td class="px-4 py-3 text-on-surface-variant text-[13px]"><?php echo html_escape($wl->working_days); ?></td>
                    <td class="px-4 py-3 text-on-surface-variant text-[13px]"><?php echo html_escape($wl->remarks ?: '—'); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- TAB: DOCUMENTS -->
  <div id="tab-documents" class="tab-pane hidden space-y-5">
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-headline-md text-headline-md text-on-surface">Staff Documents Repository</h3>
      </div>

      <!-- Upload Form -->
      <?php echo form_open_multipart('staff/upload_document', array('class' => 'p-4 rounded-xl bg-surface-container-low border border-outline-variant/40 mb-6')); ?>
        <input type="hidden" name="staff_id" value="<?php echo $staff_id; ?>"/>
        <input type="hidden" name="redirect_to" value="<?php echo current_url(); ?>"/>
        <h4 class="font-title-md text-title-md text-on-surface mb-3 flex items-center gap-1.5"><span class="material-symbols-outlined text-[18px]">cloud_upload</span>Upload / Attach Staff Document</h4>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
          <div>
            <label class="block text-label-md text-on-surface mb-1">Document Type *</label>
            <select name="document_type_id" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-medium text-on-surface">
              <?php if (!empty($document_types)): ?>
                <?php foreach ($document_types as $dt): ?>
                  <option value="<?php echo $dt->id; ?>"><?php echo html_escape($dt->document_name); ?></option>
                <?php endforeach; ?>
              <?php endif; ?>
              <option value="0">Other Document</option>
            </select>
          </div>
          <div>
            <label class="block text-label-md text-on-surface mb-1">Document Title (Optional)</label>
            <input type="text" name="document_name" placeholder="Leave empty to use document type" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md"/>
          </div>
          <div>
            <label class="block text-label-md text-on-surface mb-1">Select File *</label>
            <input type="file" name="document_file" required class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface-variant file:mr-2.5 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-secondary/15 file:text-secondary"/>
          </div>
        </div>
        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors cursor-pointer"><span class="material-symbols-outlined text-[18px]">upload</span>Upload Document</button>
      <?php echo form_close(); ?>

      <!-- Documents Table -->
      <div class="table-scroll overflow-x-auto border border-outline-variant/40 rounded-lg">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low">
              <th class="text-left px-4 py-2.5 text-label-md text-on-surface-variant font-semibold">Document Name</th>
              <th class="text-left px-4 py-2.5 text-label-md text-on-surface-variant font-semibold">Type</th>
              <th class="text-left px-4 py-2.5 text-label-md text-on-surface-variant font-semibold">Uploaded On</th>
              <th class="text-right px-4 py-2.5 text-label-md text-on-surface-variant font-semibold">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/30">
            <?php if (empty($staff->documents)): ?>
              <tr><td colspan="4" class="px-4 py-6 text-center text-on-surface-variant">No documents attached to this staff profile.</td></tr>
            <?php else: ?>
              <?php foreach ($staff->documents as $doc): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 text-body-md font-medium text-on-surface">
                    <div class="flex items-center gap-2">
                      <span class="material-symbols-outlined text-primary text-[18px]">description</span>
                      <span><?php echo html_escape($doc->document_name); ?></span>
                    </div>
                  </td>
                  <td class="px-4 py-3 text-body-md text-on-surface-variant">
                    <span class="inline-block px-2 py-0.5 rounded bg-surface-container-high text-on-surface text-[11px] font-semibold">
                      <?php echo html_escape($doc->document_type); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-body-md text-on-surface-variant text-sm"><?php echo date('d M Y', strtotime($doc->created_at)); ?></td>
                  <td class="px-4 py-3 text-body-md text-right whitespace-nowrap">
                    <div class="flex items-center justify-end gap-1.5">
                      <a href="<?php echo site_url('staff/view_document/' . $doc->document_id); ?>" target="_blank" class="px-2.5 py-1 rounded bg-surface-container-high text-on-surface text-label-md hover:bg-surface-container-highest transition-colors inline-flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">visibility</span>View
                      </a>
                      <a href="<?php echo site_url('staff/download_document/' . $doc->document_id); ?>" class="px-2.5 py-1 rounded bg-surface-container-high text-secondary text-label-md hover:bg-surface-container-highest transition-colors inline-flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">download</span>Download
                      </a>
                      <a href="<?php echo site_url('staff/delete_document/' . $doc->document_id . '?redirect_to=' . urlencode(current_url())); ?>" onclick="return confirm('Delete this document?')" class="px-2.5 py-1 rounded text-error hover:bg-error-container/20 transition-colors inline-flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">delete</span>Delete
                      </a>
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

  <!-- TAB: ATTENDANCE -->
  <div id="tab-attendance" class="tab-pane hidden space-y-5">
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-headline-md text-headline-md text-on-surface">Attendance Performance</h3>
        <a href="<?php echo site_url('staff/attendance'); ?>" class="text-label-md text-primary hover:underline">Daily Attendance Sheet</a>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6 text-center">
        <div class="p-4 rounded-lg bg-surface-container-low border border-outline-variant/30">
          <div class="text-headline-md font-bold text-on-surface"><?php echo $staff->attendance->total_days; ?></div>
          <div class="text-body-md text-on-surface-variant mt-1">Total Recorded Days</div>
        </div>
        <div class="p-4 rounded-lg bg-secondary-container/20 border border-secondary/20">
          <div class="text-headline-md font-bold text-secondary"><?php echo $staff->attendance->present; ?></div>
          <div class="text-body-md text-on-secondary-container mt-1">Days Present</div>
        </div>
        <div class="p-4 rounded-lg bg-tertiary-container/20 border border-tertiary/20">
          <div class="text-headline-md font-bold text-tertiary"><?php echo $staff->attendance->leave; ?></div>
          <div class="text-body-md text-on-tertiary-container mt-1">Approved Leave</div>
        </div>
        <div class="p-4 rounded-lg bg-primary-fixed/20 border border-primary/20">
          <div class="text-headline-md font-bold text-primary"><?php echo $staff->attendance->percentage; ?>%</div>
          <div class="text-body-md text-on-surface-variant mt-1">Present Ratio</div>
        </div>
      </div>
    </div>
  </div>

  <!-- TAB: LEAVE HISTORY -->
  <div id="tab-leave" class="tab-pane hidden space-y-5">
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-headline-md text-headline-md text-on-surface">Staff Leave History</h3>
        <a href="<?php echo site_url('staff/leave'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors"><span class="material-symbols-outlined text-[18px]">add</span>Apply Leave</a>
      </div>
      <div class="table-scroll overflow-x-auto border border-outline-variant/40 rounded-lg">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low">
              <th class="text-left px-4 py-2.5 text-label-md text-on-surface-variant">Leave Type</th>
              <th class="text-left px-4 py-2.5 text-label-md text-on-surface-variant">From - To Date</th>
              <th class="text-left px-4 py-2.5 text-label-md text-on-surface-variant">Days</th>
              <th class="text-left px-4 py-2.5 text-label-md text-on-surface-variant">Reason</th>
              <th class="text-left px-4 py-2.5 text-label-md text-on-surface-variant">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/30 text-body-md">
            <?php if (empty($staff->leaves)): ?>
              <tr><td colspan="5" class="px-4 py-6 text-center text-on-surface-variant">No leave requests on record.</td></tr>
            <?php else: ?>
              <?php foreach ($staff->leaves as $lv): ?>
                <?php
                  $badge = 'bg-surface-container-high text-on-surface';
                  if ($lv->status === 'Approved') $badge = 'bg-secondary-container text-on-secondary-container';
                  if ($lv->status === 'Pending') $badge = 'bg-tertiary-container/30 text-tertiary';
                  if ($lv->status === 'Rejected') $badge = 'bg-error-container/30 text-error';
                ?>
                <tr>
                  <td class="px-4 py-3 font-medium text-on-surface"><?php echo html_escape($lv->leave_type); ?></td>
                  <td class="px-4 py-3 text-on-surface"><?php echo date('d M Y', strtotime($lv->from_date)) . ' to ' . date('d M Y', strtotime($lv->to_date)); ?></td>
                  <td class="px-4 py-3 font-semibold text-primary"><?php echo $lv->total_days; ?> day(s)</td>
                  <td class="px-4 py-3 text-on-surface-variant text-[13px]"><?php echo html_escape($lv->reason); ?></td>
                  <td class="px-4 py-3"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold <?php echo $badge; ?>"><?php echo html_escape($lv->status); ?></span></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    function switchTab(tabName) {
      document.querySelectorAll('.tab-pane').forEach(function(pane) { pane.classList.add('hidden'); });
      document.querySelectorAll('.tab-btn').forEach(function(btn) {
        btn.classList.remove('border-secondary', 'text-primary', 'font-medium');
        btn.classList.add('border-transparent', 'text-on-surface-variant');
      });
      var targetPane = document.getElementById('tab-' + tabName);
      if (targetPane) { targetPane.classList.remove('hidden'); }
      var targetBtn = document.querySelector('.tab-btn[data-tab="' + tabName + '"]');
      if (targetBtn) {
        targetBtn.classList.remove('border-transparent', 'text-on-surface-variant');
        targetBtn.classList.add('border-secondary', 'text-primary', 'font-medium');
      }
    }
  </script>
