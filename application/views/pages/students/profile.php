<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
  $fullName = ($student) ? school_student_name($student->first_name, $student->middle_name ?? '', $student->last_name) : 'Student Profile';
  $nameParts = explode(' ', $fullName);
  $initials = '';
  foreach ($nameParts as $np) { if (!empty($np)) $initials .= strtoupper($np[0]); }
  if (strlen($initials) > 2) $initials = substr($initials, 0, 2);
  $status = (isset($student->status) && $student->status == 1) ? 'Active' : 'Inactive / Transferred';
  $statusBadge = ($status == 'Active')
    ? '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-secondary-container text-on-secondary-container">Active</span>'
    : '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-surface-container-high text-on-surface-variant">Inactive / Transferred</span>';
  $divisionDisplay = !empty($student->division_name) ? $student->division_name : (!empty($student->section_name) ? $student->section_name : '');
  $classDisplay = trim((isset($student->class_name) ? $student->class_name : '') . ' ' . $divisionDisplay);
?>

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

  <!-- Header Card -->
  <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-5 mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="flex items-center gap-4">
      <?php if (!empty($student->photo) && file_exists(FCPATH . 'uploads/students/' . $student->photo)): ?>
        <img src="<?php echo base_url('uploads/students/' . $student->photo); ?>" alt="<?php echo html_escape($fullName); ?>" class="w-16 h-16 rounded-xl object-cover shrink-0 border border-outline-variant/60 shadow-sm"/>
      <?php else: ?>
        <div class="w-16 h-16 rounded-xl bg-primary text-white flex items-center justify-center text-2xl font-bold shrink-0"><?php echo html_escape($initials); ?></div>
      <?php endif; ?>
      <div class="min-w-0">
        <div class="flex items-center gap-2.5 flex-wrap">
          <h2 class="font-headline-md text-headline-md text-on-surface"><?php echo html_escape($fullName); ?></h2>
          <?php echo $statusBadge; ?>
        </div>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">
          <span class="font-medium text-on-surface"><?php echo html_escape($student->admission_number); ?></span>
          <?php if (!empty($student->roll_number)): ?> · Roll No. <span class="font-medium text-on-surface"><?php echo html_escape($student->roll_number); ?></span><?php endif; ?>
          · Class <span class="font-medium text-on-surface"><?php echo html_escape($classDisplay ?: 'Not assigned'); ?></span>
          · Session <span class="font-medium text-on-surface"><?php echo html_escape(isset($student->year_name) && $student->year_name !== '' ? $student->year_name : ($active_academic_year->year_name ?? '—')); ?></span>
        </p>
      </div>
    </div>
    <div class="flex items-center gap-2 flex-wrap shrink-0">
      <a href="<?php echo site_url('students/edit/' . $student_id); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors"><span class="material-symbols-outlined text-[18px]">edit</span>Edit</a>
      <a href="<?php echo site_url('students/id_cards?student_id=' . (int)$student->student_id); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm"><span class="material-symbols-outlined text-[18px]">badge</span>ID Card</a>
      <a href="<?php echo site_url('students/overall_report/' . (int)$student->student_id); ?>" data-testid="btn-overall-report" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-primary text-on-primary text-label-md hover:bg-primary/90 transition-colors shadow-sm"><span class="material-symbols-outlined text-[18px]">summarize</span>Overall Report</a>
      <?php if (!empty($student->transfer)): ?>
        <a href="<?php echo site_url('students/tc/' . $student->transfer->transfer_id); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-tertiary text-on-tertiary text-label-md hover:opacity-90 transition-opacity"><span class="material-symbols-outlined text-[18px]">description</span>Print TC</a>
      <?php endif; ?>
      <a href="<?php echo site_url('students'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors"><span class="material-symbols-outlined text-[18px]">arrow_back</span>Back</a>
    </div>
  </div>

  <?php
    $activeTab = !empty($_GET['tab']) ? $_GET['tab'] : 'overview';
  ?>

  <!-- Interactive Profile Tabs -->
  <div class="flex gap-2 border-b border-outline-variant/60 mb-6 overflow-x-auto" id="profile-tabs">
    <button onclick="switchTab('overview')" class="tab-btn px-4 py-2.5 text-body-md <?php echo ($activeTab === 'overview') ? 'font-medium border-b-2 border-secondary text-primary' : 'text-on-surface-variant hover:text-on-surface border-b-2 border-transparent'; ?> cursor-pointer" data-tab="overview">Overview</button>
    <button onclick="switchTab('personal')" class="tab-btn px-4 py-2.5 text-body-md <?php echo ($activeTab === 'personal') ? 'font-medium border-b-2 border-secondary text-primary' : 'text-on-surface-variant hover:text-on-surface border-b-2 border-transparent'; ?> cursor-pointer" data-tab="personal">Personal Info</button>
    <button onclick="switchTab('guardian')" class="tab-btn px-4 py-2.5 text-body-md <?php echo ($activeTab === 'guardian') ? 'font-medium border-b-2 border-secondary text-primary' : 'text-on-surface-variant hover:text-on-surface border-b-2 border-transparent'; ?> cursor-pointer" data-tab="guardian">Guardian & Address</button>
    <button onclick="switchTab('academic')" class="tab-btn px-4 py-2.5 text-body-md <?php echo ($activeTab === 'academic') ? 'font-medium border-b-2 border-secondary text-primary' : 'text-on-surface-variant hover:text-on-surface border-b-2 border-transparent'; ?> cursor-pointer" data-tab="academic">Academic & Promotion History</button>
    <button onclick="switchTab('documents')" class="tab-btn px-4 py-2.5 text-body-md <?php echo ($activeTab === 'documents') ? 'font-medium border-b-2 border-secondary text-primary' : 'text-on-surface-variant hover:text-on-surface border-b-2 border-transparent'; ?> cursor-pointer" data-tab="documents">Documents (<?php echo count($student->documents); ?>)</button>
    <button onclick="switchTab('attendance')" class="tab-btn px-4 py-2.5 text-body-md <?php echo ($activeTab === 'attendance') ? 'font-medium border-b-2 border-secondary text-primary' : 'text-on-surface-variant hover:text-on-surface border-b-2 border-transparent'; ?> cursor-pointer" data-tab="attendance">Attendance Summary</button>
    <button onclick="switchTab('fees')" class="tab-btn px-4 py-2.5 text-body-md <?php echo ($activeTab === 'fees') ? 'font-medium border-b-2 border-secondary text-primary' : 'text-on-surface-variant hover:text-on-surface border-b-2 border-transparent'; ?> cursor-pointer" data-tab="fees">Fees & Finance</button>
    <button onclick="switchTab('transfer')" class="tab-btn px-4 py-2.5 text-body-md <?php echo ($activeTab === 'transfer') ? 'font-medium border-b-2 border-secondary text-primary' : 'text-on-surface-variant hover:text-on-surface border-b-2 border-transparent'; ?> cursor-pointer" data-tab="transfer">Transfer / TC</button>
  </div>

  <!-- TAB 1: OVERVIEW -->
  <div id="tab-overview" class="tab-pane <?php echo ($activeTab === 'overview') ? '' : 'hidden'; ?> space-y-5">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      
      <!-- Left Column: Personal & Academic Quick Summary -->
      <div class="lg:col-span-2 space-y-5">
        
        <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
          <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/50">
            <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
              <span class="material-symbols-outlined text-primary text-[20px]">person</span>Personal & Admission Details
            </h3>
            <a href="<?php echo site_url('students/edit/' . $student_id); ?>" class="text-label-md text-primary hover:underline">Edit</a>
          </div>
          <div class="p-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-body-md">
              <div><div class="text-on-surface-variant text-[12px]">Admission Number</div><div class="font-medium text-on-surface"><?php echo html_escape($student->admission_number); ?></div></div>
              <div><div class="text-on-surface-variant text-[12px]">Roll Number</div><div class="font-medium text-on-surface"><?php echo html_escape($student->roll_number ?: '—'); ?></div></div>
              <div><div class="text-on-surface-variant text-[12px]">Gender</div><div class="font-medium text-on-surface"><?php echo html_escape($student->gender); ?></div></div>
              <div><div class="text-on-surface-variant text-[12px]">Date of Birth</div><div class="font-medium text-on-surface"><?php echo date('d M Y', strtotime($student->date_of_birth)); ?></div></div>
              <div><div class="text-on-surface-variant text-[12px]">Blood Group</div><div class="font-medium text-on-surface"><?php echo html_escape($student->blood_group ?: '—'); ?></div></div>
              <div><div class="text-on-surface-variant text-[12px]">Nationality</div><div class="font-medium text-on-surface"><?php echo html_escape($student->nationality ?: 'Indian'); ?></div></div>
              <div><div class="text-on-surface-variant text-[12px]">Class & Division</div><div class="font-medium text-on-surface"><?php echo html_escape($classDisplay ?: '—'); ?></div></div>
              <div><div class="text-on-surface-variant text-[12px]">Academic Session</div><div class="font-medium text-on-surface"><?php echo html_escape($student->year_name ?: ($active_academic_year->year_name ?? '—')); ?></div></div>
              <div><div class="text-on-surface-variant text-[12px]">Admission Date</div><div class="font-medium text-on-surface"><?php echo date('d M Y', strtotime($student->created_at)); ?></div></div>
            </div>
          </div>
        </div>

        <!-- Attendance Stats Card -->
        <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
          <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/50">
            <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
              <span class="material-symbols-outlined text-secondary text-[20px]">fact_check</span>Attendance Overview
            </h3>
            <span class="text-label-md font-semibold text-secondary"><?php echo $student->attendance->percentage; ?>% Attendance</span>
          </div>
          <div class="p-5">
            <div class="grid grid-cols-3 gap-4 text-center">
              <div class="p-3.5 rounded-lg bg-surface-container-low border border-outline-variant/30">
                <div class="text-title-lg font-bold text-on-surface"><?php echo $student->attendance->total_days; ?></div>
                <div class="text-[12px] text-on-surface-variant mt-0.5">Total Working Days</div>
              </div>
              <div class="p-3.5 rounded-lg bg-secondary-container/20 border border-secondary/20">
                <div class="text-title-lg font-bold text-secondary"><?php echo $student->attendance->present; ?></div>
                <div class="text-[12px] text-on-secondary-container mt-0.5">Days Present</div>
              </div>
              <div class="p-3.5 rounded-lg bg-error-container/20 border border-error/20">
                <div class="text-title-lg font-bold text-error"><?php echo $student->attendance->absent; ?></div>
                <div class="text-[12px] text-on-error-container mt-0.5">Days Absent</div>
              </div>
            </div>
            <div class="w-full bg-surface-container-high h-2 rounded-full overflow-hidden mt-4">
              <div class="bg-secondary h-full rounded-full transition-all" style="width: <?php echo min(100, max(0, $student->attendance->percentage)); ?>%"></div>
            </div>
          </div>
        </div>

      </div>

      <!-- Right Column: Guardian, Address & Documents -->
      <div class="space-y-5">
        
        <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
          <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/50">
            <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
              <span class="material-symbols-outlined text-primary text-[20px]">family_restroom</span>Parent / Guardian
            </h3>
          </div>
          <div class="p-5 space-y-3 text-body-md">
            <div>
              <div class="text-[12px] text-on-surface-variant">Guardian Name & Relation</div>
              <div class="font-medium text-on-surface"><?php echo html_escape($student->guardian_name); ?> (<?php echo html_escape($student->guardian_relation ?: 'Father'); ?>)</div>
            </div>
            <div>
              <div class="text-[12px] text-on-surface-variant">Contact Phone</div>
              <div class="font-medium text-on-surface"><?php echo html_escape($student->guardian_phone); ?></div>
            </div>
            <div>
              <div class="text-[12px] text-on-surface-variant">Email Address</div>
              <div class="font-medium text-on-surface"><?php echo html_escape($student->guardian_email ?: '—'); ?></div>
            </div>
            <div>
              <div class="text-[12px] text-on-surface-variant">Residential Address</div>
              <div class="text-on-surface text-[13px] leading-relaxed"><?php echo html_escape($student->address ?: '—'); ?></div>
            </div>
          </div>
        </div>

        <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
          <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/50">
            <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
              <span class="material-symbols-outlined text-primary text-[20px]">folder</span>Documents
            </h3>
            <button onclick="switchTab('documents')" class="text-label-md text-primary hover:underline">Manage</button>
          </div>
          <div class="p-5">
            <?php if (!empty($student->documents)): ?>
              <ul class="divide-y divide-outline-variant/30 text-body-md">
                <?php foreach ($student->documents as $doc): ?>
                  <li class="py-2.5 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                      <span class="material-symbols-outlined text-primary text-[18px]">description</span>
                      <div>
                        <div class="font-medium text-on-surface text-[13px]"><?php echo html_escape($doc->document_name); ?></div>
                        <div class="text-[11px] text-on-surface-variant"><?php echo html_escape($doc->document_type); ?></div>
                      </div>
                    </div>
                    <span class="material-symbols-outlined text-secondary text-[18px]">verified</span>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <p class="text-body-md text-on-surface-variant text-center py-2">No documents uploaded yet.</p>
            <?php endif; ?>
          </div>
        </div>

      </div>

    </div>
  </div>

  <!-- TAB 2: PERSONAL INFO -->
  <div id="tab-personal" class="tab-pane hidden space-y-5">
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-6">
      <h3 class="font-headline-md text-headline-md text-on-surface mb-4">Complete Personal Information</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-body-md">
        <div class="space-y-4">
          <div><span class="text-on-surface-variant text-sm block">Full Name</span><span class="font-medium text-on-surface"><?php echo html_escape(school_student_name($student->first_name, $student->middle_name ?? '', $student->last_name)); ?></span></div>
          <div><span class="text-on-surface-variant text-sm block">First Name</span><span class="font-medium text-on-surface"><?php echo html_escape($student->first_name); ?></span></div>
          <?php if (!empty($student->middle_name)): ?>
          <div><span class="text-on-surface-variant text-sm block">Middle Name</span><span class="font-medium text-on-surface"><?php echo html_escape($student->middle_name); ?></span></div>
          <?php endif; ?>
          <div><span class="text-on-surface-variant text-sm block">Last Name</span><span class="font-medium text-on-surface"><?php echo html_escape($student->last_name); ?></span></div>
          <div><span class="text-on-surface-variant text-sm block">Gender</span><span class="font-medium text-on-surface"><?php echo html_escape($student->gender); ?></span></div>
          <div><span class="text-on-surface-variant text-sm block">Date of Birth</span><span class="font-medium text-on-surface"><?php echo date('d M Y', strtotime($student->date_of_birth)); ?></span></div>
          <div><span class="text-on-surface-variant text-sm block">Student Phone</span><span class="font-medium text-on-surface"><?php echo html_escape($student->student_phone ?: '—'); ?></span></div>
          <div><span class="text-on-surface-variant text-sm block">Student Email</span><span class="font-medium text-on-surface"><?php echo html_escape($student->student_email ?: '—'); ?></span></div>
        </div>
        <div class="space-y-4">
          <?php $formattedStudentAddress = school_format_address($student); ?>
          <?php if (!empty($formattedStudentAddress)): ?>
          <div><span class="text-on-surface-variant text-sm block">Student Address</span><span class="font-medium text-on-surface leading-relaxed"><?php echo html_escape($formattedStudentAddress); ?></span></div>
          <?php endif; ?>
          <div><span class="text-on-surface-variant text-sm block">Blood Group</span><span class="font-medium text-on-surface"><?php echo html_escape($student->blood_group ?: '—'); ?></span></div>
          <div><span class="text-on-surface-variant text-sm block">Nationality</span><span class="font-medium text-on-surface"><?php echo html_escape($student->nationality ?: 'Indian'); ?></span></div>
          <div><span class="text-on-surface-variant text-sm block">Religion</span><span class="font-medium text-on-surface"><?php echo html_escape($student->religion ?: '—'); ?></span></div>
          <div><span class="text-on-surface-variant text-sm block">Account Status</span><span class="font-medium text-on-surface"><?php echo $status; ?></span></div>
          <div><span class="text-on-surface-variant text-sm block">Registered In System Since</span><span class="font-medium text-on-surface"><?php echo date('d M Y, h:i A', strtotime($student->created_at)); ?></span></div>
        </div>
      </div>
    </div>
  </div>

  <!-- TAB 3: GUARDIAN & ADDRESS -->
  <div id="tab-guardian" class="tab-pane hidden space-y-5">
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-6">
      <h3 class="font-headline-md text-headline-md text-on-surface mb-4">Guardian & Residential Information</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-body-md">
        <div class="space-y-4">
          <div><span class="text-on-surface-variant text-sm block">Guardian Full Name</span><span class="font-medium text-on-surface"><?php echo html_escape($student->guardian_name); ?></span></div>
          <div><span class="text-on-surface-variant text-sm block">Relationship to Student</span><span class="font-medium text-on-surface"><?php echo html_escape($student->guardian_relation ?: 'Father'); ?></span></div>
          <div><span class="text-on-surface-variant text-sm block">Guardian Phone (Primary)</span><span class="font-medium text-on-surface"><?php echo html_escape($student->guardian_phone); ?></span></div>
          <?php if (!empty($student->father_phone)): ?>
          <div><span class="text-on-surface-variant text-sm block">Father Phone</span><span class="font-medium text-on-surface"><?php echo html_escape($student->father_phone); ?></span></div>
          <?php endif; ?>
          <?php if (!empty($student->mother_phone)): ?>
          <div><span class="text-on-surface-variant text-sm block">Mother Phone</span><span class="font-medium text-on-surface"><?php echo html_escape($student->mother_phone); ?></span></div>
          <?php endif; ?>
          <?php if (!empty($student->emergency_contact)): ?>
          <div><span class="text-on-surface-variant text-sm block">Emergency Contact</span><span class="font-medium text-on-surface"><?php echo html_escape($student->emergency_contact); ?></span></div>
          <?php endif; ?>
          <div><span class="text-on-surface-variant text-sm block">Email Address</span><span class="font-medium text-on-surface"><?php echo html_escape($student->guardian_email ?: '—'); ?></span></div>
        </div>
        <div class="space-y-4">
          <?php if (!empty($formattedStudentAddress)): ?>
          <div><span class="text-on-surface-variant text-sm block">Student Residential Address</span><span class="font-medium text-on-surface leading-relaxed"><?php echo html_escape($formattedStudentAddress); ?></span></div>
          <?php endif; ?>
          <div><span class="text-on-surface-variant text-sm block">Guardian / Permanent Address</span><span class="font-medium text-on-surface leading-relaxed"><?php echo nl2br(html_escape($student->address ?: ($formattedStudentAddress ?: 'No address provided.'))); ?></span></div>
        </div>
      </div>
    </div>
  </div>

  <!-- TAB 4: ACADEMIC & PROMOTION HISTORY -->
  <div id="tab-academic" class="tab-pane hidden space-y-5">
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-headline-md text-headline-md text-on-surface">Current Academic Enrollment</h3>
        <a href="<?php echo site_url('students/promotion'); ?>" class="text-label-md text-primary hover:underline flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">upgrade</span>Promote Student</a>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="p-4 rounded-lg bg-surface-container-low border border-outline-variant/40">
          <div class="text-[12px] text-on-surface-variant">Class</div>
          <div class="text-title-md font-semibold text-on-surface"><?php echo html_escape($student->class_name ?: '—'); ?></div>
        </div>
        <div class="p-4 rounded-lg bg-surface-container-low border border-outline-variant/40">
          <div class="text-[12px] text-on-surface-variant">Division</div>
          <div class="text-title-md font-semibold text-on-surface"><?php
            $currentDiv = !empty($student->division_name) ? $student->division_name : (!empty($student->section_name) ? $student->section_name : '');
            echo html_escape($currentDiv ? 'Division ' . $currentDiv : 'Not Assigned');
          ?></div>
        </div>
        <div class="p-4 rounded-lg bg-surface-container-low border border-outline-variant/40">
          <div class="text-[12px] text-on-surface-variant">Current Academic Year</div>
          <div class="text-title-md font-semibold text-on-surface"><?php echo html_escape($student->year_name ?: ($active_academic_year->year_name ?? '—')); ?></div>
        </div>
      </div>

      <h4 class="font-title-md text-title-md text-on-surface mb-3">Promotion & Academic History</h4>
      <div class="table-scroll overflow-x-auto border border-outline-variant/40 rounded-lg">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low">
              <th class="text-left px-4 py-2.5 text-label-md text-on-surface-variant">Date</th>
              <th class="text-left px-4 py-2.5 text-label-md text-on-surface-variant">From Class</th>
              <th class="text-left px-4 py-2.5 text-label-md text-on-surface-variant">To Class</th>
              <th class="text-left px-4 py-2.5 text-label-md text-on-surface-variant">Action Type</th>
              <th class="text-left px-4 py-2.5 text-label-md text-on-surface-variant">Remarks</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($student->promotions)): ?>
              <tr>
                <td colspan="5" class="px-4 py-6 text-center text-on-surface-variant text-body-md">Initial registration enrollment record. No prior promotions recorded.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($student->promotions as $p): ?>
                <tr>
                  <td class="px-4 py-3 text-body-md text-on-surface whitespace-nowrap"><?php echo date('d M Y', strtotime($p->promotion_date)); ?></td>
                  <td class="px-4 py-3 text-body-md text-on-surface whitespace-nowrap"><?php
                    $fromDiv = !empty($p->from_division) ? $p->from_division : (!empty($p->from_section) ? $p->from_section : '');
                    echo html_escape($p->from_class . ($fromDiv ? ' ' . $fromDiv : '') . ' (' . $p->from_year . ')');
                  ?></td>
                  <td class="px-4 py-3 text-body-md text-on-surface whitespace-nowrap font-medium"><?php
                    $toDiv = !empty($p->to_division) ? $p->to_division : (!empty($p->to_section) ? $p->to_section : '');
                    echo html_escape($p->to_class . ($toDiv ? ' ' . $toDiv : '') . ' (' . $p->to_year . ')');
                  ?></td>
                  <td class="px-4 py-3 text-body-md whitespace-nowrap">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-secondary-container text-on-secondary-container"><?php echo html_escape($p->promotion_type); ?></span>
                  </td>
                  <td class="px-4 py-3 text-body-md text-on-surface-variant"><?php echo html_escape($p->remarks ?: '—'); ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- TAB 5: DOCUMENTS -->
  <div id="tab-documents" class="tab-pane hidden space-y-5">
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
          <h3 class="font-headline-md text-headline-md text-on-surface">Student Document Repository</h3>
          <p class="text-body-md text-on-surface-variant mt-0.5">Upload and manage official certificates, identity proof, and academic documents.</p>
        </div>
      </div>

      <!-- Flash Messages for Documents Tab -->
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

      <!-- Upload Document Form -->
      <?php echo form_open_multipart('students/upload_document', array('class' => 'p-4 rounded-xl bg-surface-container-low border border-outline-variant/40 mb-6', 'id' => 'student-doc-upload-form')); ?>
        <input type="hidden" name="student_id" value="<?php echo $student_id; ?>"/>
        <input type="hidden" name="redirect_to" value="<?php echo current_url(); ?>#documents"/>
        <h4 class="font-title-md text-title-md text-on-surface mb-3 flex items-center gap-1.5"><span class="material-symbols-outlined text-[18px]">cloud_upload</span>Upload New Document</h4>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
          <div>
            <label class="block text-label-md text-on-surface mb-1">Document Type *</label>
            <select name="document_type" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md">
              <option value="Birth Certificate">Birth Certificate</option>
              <option value="Aadhaar Card / ID Proof">Aadhaar Card / ID Proof</option>
              <option value="Previous School TC">Previous School TC</option>
              <option value="Transfer Certificate">Transfer Certificate</option>
              <option value="Medical Fitness Certificate">Medical Fitness Certificate</option>
              <option value="Passport Photo">Passport Photo</option>
              <option value="Other">Other</option>
            </select>
          </div>
          <div>
            <label class="block text-label-md text-on-surface mb-1">Document Title / Name *</label>
            <input type="text" name="document_name" required placeholder="e.g. Birth Certificate Original" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md"/>
          </div>
          <div>
            <label for="document_file" class="block text-label-md text-on-surface mb-1">Select File</label>
            <input type="file" id="document_file" name="document_file" accept="<?php echo student_document_accept_attribute(); ?>" class="w-full px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface-variant"/>
            <p class="text-xs text-on-surface-variant mt-1.5" id="document_file_hint"><?php echo student_document_format_notice(); ?></p>
            <p class="text-xs text-error mt-1 hidden font-medium flex items-center gap-1" id="document_file_error" role="alert"></p>
          </div>
        </div>
        <button type="submit" id="btn-upload-document" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors cursor-pointer"><span class="material-symbols-outlined text-[18px]">upload</span>Upload Document</button>
      <?php echo form_close(); ?>

      <!-- Documents List Table -->
      <div class="table-scroll overflow-x-auto border border-outline-variant/40 rounded-lg">
        <table class="w-full data-table zebra border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low">
              <th class="text-left px-4 py-2.5 text-label-md text-on-surface-variant">Document Name</th>
              <th class="text-left px-4 py-2.5 text-label-md text-on-surface-variant">Document Type</th>
              <th class="text-left px-4 py-2.5 text-label-md text-on-surface-variant">Uploaded On</th>
              <th class="text-right px-4 py-2.5 text-label-md text-on-surface-variant">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($student->documents)): ?>
              <tr>
                <td colspan="4" class="px-4 py-6 text-center text-on-surface-variant text-body-md">No documents currently uploaded for this student.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($student->documents as $doc): ?>
                <tr>
                  <td class="px-4 py-3 text-body-md text-on-surface font-medium whitespace-nowrap">
                    <div class="flex items-center gap-2">
                      <span class="material-symbols-outlined text-primary text-[18px]">description</span>
                      <?php echo html_escape($doc->document_name); ?>
                    </div>
                  </td>
                  <td class="px-4 py-3 text-body-md text-on-surface-variant whitespace-nowrap"><?php echo html_escape($doc->document_type); ?></td>
                  <td class="px-4 py-3 text-body-md text-on-surface-variant whitespace-nowrap"><?php echo date('d M Y', strtotime($doc->created_at)); ?></td>
                  <td class="px-4 py-3 text-body-md text-right whitespace-nowrap">
                    <a href="<?php echo base_url($doc->file_path); ?>" target="_blank" class="px-2.5 py-1 rounded bg-surface-container-high text-on-surface text-label-md hover:bg-surface-container-highest transition-colors inline-flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">visibility</span>View</a>
                    <a href="<?php echo site_url('students/delete_document/' . $doc->document_id . '?redirect_to=' . urlencode(current_url())); ?>" onclick="return confirm('Remove this document record?')" class="px-2.5 py-1 rounded text-error hover:bg-error-container/20 transition-colors inline-flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">delete</span>Delete</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- TAB 6: ATTENDANCE SUMMARY -->
  <div id="tab-attendance" class="tab-pane <?php echo ($activeTab === 'attendance') ? '' : 'hidden'; ?> space-y-6">
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-6 space-y-6">
      
      <!-- Top Title Bar -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-outline-variant/50 pb-4">
        <div>
          <div class="flex items-center gap-2">
            <h3 class="font-headline-md text-headline-md text-on-surface">Student Attendance Profile</h3>
            <?php if (!empty($student->academic_group_name)): ?>
              <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-surface-container-high text-primary border border-outline-variant/40">
                Group: <?php echo html_escape($student->academic_group_name); ?>
              </span>
            <?php endif; ?>
          </div>
          <p class="text-body-md text-on-surface-variant mt-0.5">Summary, monthly trend, and historical logs for <?php echo html_escape($fullName); ?>.</p>
        </div>
        <div class="flex items-center gap-2">
          <?php
            $calendarUrlParams = array('student_id' => (int)$student->student_id);
            if (!empty($student->class_id)) {
              $calendarUrlParams['class_id'] = (int)$student->class_id;
            }
            if (!empty($student->division_id)) {
              $calendarUrlParams['division_id'] = (int)$student->division_id;
            }
            if (!empty($student->academic_year_id)) {
              $calendarUrlParams['academic_year_id'] = (int)$student->academic_year_id;
            }

            $pdfUrlParams = array(
              'attendance_year_id' => isset($attendance_year_id) ? $attendance_year_id : $student->academic_year_id,
              'from_date'          => $student->attendance->from_date ?? '',
              'to_date'            => $student->attendance->to_date ?? '',
            );
          ?>
          <a href="<?php echo site_url('attendance/calendar?' . http_build_query($calendarUrlParams)); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
            <span class="material-symbols-outlined text-[18px]">calendar_month</span>View Interactive Calendar
          </a>
          <a href="<?php echo site_url('attendance/student_attendance?student_id=' . (int)$student->student_id); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-outline-variant text-on-surface hover:bg-surface-container-high text-label-md font-semibold transition-colors">
            <span class="material-symbols-outlined text-[18px]">visibility</span>Detailed View
          </a>
          <a href="<?php echo site_url('students/attendance_pdf/' . (int)$student->student_id . '?' . http_build_query($pdfUrlParams)); ?>" id="btn-download-attendance-pdf" data-testid="btn-download-attendance-pdf" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm cursor-pointer" title="Download Complete Attendance Report as PDF">
            <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>Download PDF
          </a>
        </div>
      </div>

      <!-- Interactive Date Range Filter Bar -->
      <form method="get" action="<?php echo site_url('students/profile/' . $student_id); ?>#attendance" id="attendance-filter-form" data-testid="attendance-filter-form" class="p-4 rounded-xl bg-surface-container-low border border-outline-variant/40 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
        <input type="hidden" name="tab" value="attendance" />
        <div>
          <label class="block text-label-md text-on-surface mb-1 font-medium">Academic Year</label>
          <select name="attendance_year_id" data-testid="attendance-filter-year" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20">
            <?php foreach ($years as $y): ?>
              <option value="<?php echo $y->academic_year_id; ?>" <?php echo ((isset($attendance_year_id) ? $attendance_year_id : $student->academic_year_id) == $y->academic_year_id) ? 'selected' : ''; ?>>
                <?php echo html_escape($y->year_name); ?><?php echo (!empty($y->is_active)) ? ' (Active)' : ''; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-label-md text-on-surface mb-1 font-medium">From Date</label>
          <input type="date" name="from_date" data-testid="attendance-from-date" value="<?php echo html_escape($student->attendance->from_date ?? ''); ?>" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20" />
        </div>
        <div>
          <label class="block text-label-md text-on-surface mb-1 font-medium">To Date</label>
          <input type="date" name="to_date" data-testid="attendance-to-date" value="<?php echo html_escape($student->attendance->to_date ?? ''); ?>" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20" />
        </div>
        <div class="flex items-center gap-2">
          <button type="submit" data-testid="attendance-filter-submit" class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-colors shadow-sm cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">filter_alt</span>Apply
          </button>
          <a href="<?php echo site_url('students/profile/' . $student_id . '?tab=attendance'); ?>#attendance" data-testid="attendance-filter-reset" class="inline-flex items-center justify-center gap-1 px-3 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-label-md font-semibold transition-colors">
            <span class="material-symbols-outlined text-[18px]">refresh</span>Reset
          </a>
        </div>
      </form>

      <?php
        $att = $student->attendance;
        $hasDaily = !empty($att->has_daily);
        $hasPeriod = !empty($att->has_period);
        $mode = $att->attendance_mode ?? ($hasPeriod ? 'period' : 'daily');
      ?>

      <!-- 1. Attendance Summary Metrics -->
      <?php if ($mode === 'both' || ($hasDaily && $hasPeriod)): ?>
        <!-- Dual Attendance Cards: Both Day-wise and Period-wise exist -->
        <div class="space-y-6">
          <!-- Day-wise Section -->
          <div>
            <div class="flex items-center justify-between mb-3">
              <h4 class="font-title-md text-title-md font-bold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[20px]">calendar_today</span>Day-wise Attendance Summary
              </h4>
              <span class="text-label-md font-bold text-secondary bg-secondary-container/40 px-2.5 py-1 rounded-lg">Day Attendance: <?php echo $att->day_summary->percentage; ?>%</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
              <div class="p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/30 text-center">
                <div class="text-2xl font-bold text-on-surface"><?php echo $att->day_summary->working_days; ?></div>
                <div class="text-[12px] text-on-surface-variant mt-1">Total Academic Days</div>
              </div>
              <div class="p-3.5 rounded-xl bg-secondary-container/20 border border-secondary/20 text-center">
                <div class="text-2xl font-bold text-secondary"><?php echo $att->day_summary->present; ?></div>
                <div class="text-[12px] text-on-secondary-container mt-1">Days Present</div>
              </div>
              <div class="p-3.5 rounded-xl bg-error-container/20 border border-error/20 text-center">
                <div class="text-2xl font-bold text-error"><?php echo $att->day_summary->absent; ?></div>
                <div class="text-[12px] text-on-error-container mt-1">Days Absent</div>
              </div>
              <div class="p-3.5 rounded-xl bg-amber-100 dark:bg-amber-950/30 border border-amber-300 text-center">
                <div class="text-2xl font-bold text-amber-900 dark:text-amber-300"><?php echo $att->day_summary->late; ?></div>
                <div class="text-[12px] text-on-surface-variant mt-1">Late Arrivals</div>
              </div>
              <div class="p-3.5 rounded-xl bg-primary-fixed/20 border border-primary/20 text-center">
                <div class="text-2xl font-bold text-primary"><?php echo $att->day_summary->excused; ?></div>
                <div class="text-[12px] text-on-surface-variant mt-1">Excused / Leave</div>
              </div>
              <div class="p-3.5 rounded-xl bg-secondary-container/30 border border-secondary/40 text-center">
                <div class="text-2xl font-bold text-secondary"><?php echo $att->day_summary->percentage; ?>%</div>
                <div class="text-[12px] text-on-surface-variant mt-1">Overall Day %</div>
              </div>
            </div>
          </div>

          <!-- Period-wise Section -->
          <div>
            <div class="flex items-center justify-between mb-3">
              <h4 class="font-title-md text-title-md font-bold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[20px]">schedule</span>Period-wise Attendance Summary
              </h4>
              <span class="text-label-md font-bold text-secondary bg-secondary-container/40 px-2.5 py-1 rounded-lg">Period Attendance: <?php echo $att->period_summary->percentage; ?>%</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
              <div class="p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/30 text-center">
                <div class="text-2xl font-bold text-on-surface"><?php echo $att->period_summary->total_periods; ?></div>
                <div class="text-[12px] text-on-surface-variant mt-1">Scheduled Periods</div>
              </div>
              <div class="p-3.5 rounded-xl bg-secondary-container/20 border border-secondary/20 text-center">
                <div class="text-2xl font-bold text-secondary"><?php echo $att->period_summary->present; ?></div>
                <div class="text-[12px] text-on-secondary-container mt-1">Periods Present</div>
              </div>
              <div class="p-3.5 rounded-xl bg-error-container/20 border border-error/20 text-center">
                <div class="text-2xl font-bold text-error"><?php echo $att->period_summary->absent; ?></div>
                <div class="text-[12px] text-on-error-container mt-1">Periods Absent</div>
              </div>
              <div class="p-3.5 rounded-xl bg-amber-100 dark:bg-amber-950/30 border border-amber-300 text-center">
                <div class="text-2xl font-bold text-amber-900 dark:text-amber-300"><?php echo $att->period_summary->late; ?></div>
                <div class="text-[12px] text-on-surface-variant mt-1">Late Periods</div>
              </div>
              <div class="p-3.5 rounded-xl bg-primary-fixed/20 border border-primary/20 text-center">
                <div class="text-2xl font-bold text-primary"><?php echo $att->period_summary->excused; ?></div>
                <div class="text-[12px] text-on-surface-variant mt-1">Excused / Leave</div>
              </div>
              <div class="p-3.5 rounded-xl bg-secondary-container/30 border border-secondary/40 text-center">
                <div class="text-2xl font-bold text-secondary"><?php echo $att->period_summary->percentage; ?>%</div>
                <div class="text-[12px] text-on-surface-variant mt-1">Overall Period %</div>
              </div>
            </div>
          </div>
        </div>

      <?php elseif ($hasPeriod || $mode === 'period' || !empty($att->is_higher_sec)): ?>
        <!-- Period-wise Single Attendance Cards -->
        <div>
          <h4 class="font-title-md text-title-md font-bold text-on-surface mb-3 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px]">schedule</span>Period-wise Attendance Summary
          </h4>
          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant/30 text-center">
              <div class="text-2xl font-bold text-on-surface"><?php echo $att->period_summary->total_periods; ?></div>
              <div class="text-[12px] text-on-surface-variant mt-1">Scheduled Periods</div>
            </div>
            <div class="p-4 rounded-xl bg-secondary-container/20 border border-secondary/20 text-center">
              <div class="text-2xl font-bold text-secondary"><?php echo $att->period_summary->present; ?></div>
              <div class="text-[12px] text-on-secondary-container mt-1">Periods Present</div>
            </div>
            <div class="p-4 rounded-xl bg-error-container/20 border border-error/20 text-center">
              <div class="text-2xl font-bold text-error"><?php echo $att->period_summary->absent; ?></div>
              <div class="text-[12px] text-on-error-container mt-1">Periods Absent</div>
            </div>
            <div class="p-4 rounded-xl bg-amber-100 dark:bg-amber-950/30 border border-amber-300 text-center">
              <div class="text-2xl font-bold text-amber-900 dark:text-amber-300"><?php echo $att->period_summary->late; ?></div>
              <div class="text-[12px] text-on-surface-variant mt-1">Late Periods</div>
            </div>
            <div class="p-4 rounded-xl bg-primary-fixed/20 border border-primary/20 text-center">
              <div class="text-2xl font-bold text-primary"><?php echo $att->period_summary->excused; ?></div>
              <div class="text-[12px] text-on-surface-variant mt-1">Excused / Leave</div>
            </div>
            <div class="p-4 rounded-xl bg-secondary-container/30 border border-secondary/40 text-center">
              <div class="text-2xl font-bold text-secondary"><?php echo $att->period_summary->percentage; ?>%</div>
              <div class="text-[12px] text-on-surface-variant mt-1">Overall Period %</div>
            </div>
          </div>
        </div>

      <?php else: ?>
        <!-- Day-wise Single Attendance Cards -->
        <div>
          <h4 class="font-title-md text-title-md font-bold text-on-surface mb-3 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px]">calendar_today</span>Day-wise Attendance Summary
          </h4>
          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant/30 text-center">
              <div class="text-2xl font-bold text-on-surface"><?php echo $att->day_summary->working_days; ?></div>
              <div class="text-[12px] text-on-surface-variant mt-1">Total Academic Days</div>
            </div>
            <div class="p-4 rounded-xl bg-secondary-container/20 border border-secondary/20 text-center">
              <div class="text-2xl font-bold text-secondary"><?php echo $att->day_summary->present; ?></div>
              <div class="text-[12px] text-on-secondary-container mt-1">Days Present</div>
            </div>
            <div class="p-4 rounded-xl bg-error-container/20 border border-error/20 text-center">
              <div class="text-2xl font-bold text-error"><?php echo $att->day_summary->absent; ?></div>
              <div class="text-[12px] text-on-error-container mt-1">Days Absent</div>
            </div>
            <div class="p-4 rounded-xl bg-amber-100 dark:bg-amber-950/30 border border-amber-300 text-center">
              <div class="text-2xl font-bold text-amber-900 dark:text-amber-300"><?php echo $att->day_summary->late; ?></div>
              <div class="text-[12px] text-on-surface-variant mt-1">Late Arrivals</div>
            </div>
            <div class="p-4 rounded-xl bg-primary-fixed/20 border border-primary/20 text-center">
              <div class="text-2xl font-bold text-primary"><?php echo $att->day_summary->excused; ?></div>
              <div class="text-[12px] text-on-surface-variant mt-1">Excused / Leave</div>
            </div>
            <div class="p-4 rounded-xl bg-secondary-container/30 border border-secondary/40 text-center">
              <div class="text-2xl font-bold text-secondary"><?php echo $att->day_summary->percentage; ?>%</div>
              <div class="text-[12px] text-on-surface-variant mt-1">Overall Percentage</div>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- 2. Subject-wise Attendance Table (for period-wise students or when subjects exist) -->
      <?php if ($hasPeriod || $mode === 'period' || !empty($att->is_higher_sec) || !empty($att->subject_summary)): ?>
        <div>
          <h4 class="font-title-md text-title-md font-bold text-on-surface mb-3 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px]">menu_book</span>Subject-wise Attendance
          </h4>
          <div class="overflow-x-auto rounded-xl border border-outline-variant/50">
            <table class="w-full data-table zebra border-collapse text-body-md">
              <thead>
                <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
                  <th class="text-left px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase">Subject</th>
                  <th class="text-right px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase">Total Classes</th>
                  <th class="text-right px-4 py-2.5 text-label-md font-semibold text-secondary uppercase">Present</th>
                  <th class="text-right px-4 py-2.5 text-label-md font-semibold text-error uppercase">Absent</th>
                  <th class="text-right px-4 py-2.5 text-label-md font-semibold text-amber-600 uppercase">Late</th>
                  <th class="text-right px-4 py-2.5 text-label-md font-semibold text-primary uppercase">Excused</th>
                  <th class="text-right px-4 py-2.5 text-label-md font-semibold text-on-surface uppercase">Attendance %</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-outline-variant/30">
                <?php
                  $hasSubjectRows = false;
                  if (!empty($att->subject_summary)) {
                    foreach ($att->subject_summary as $sub) {
                      if ($sub->total_classes > 0) { $hasSubjectRows = true; break; }
                    }
                  }
                ?>
                <?php if (!$hasSubjectRows): ?>
                  <tr><td colspan="7" class="px-4 py-6 text-center text-on-surface-variant">No subject attendance records found.</td></tr>
                <?php else: ?>
                  <?php foreach ($att->subject_summary as $sub): ?>
                    <?php if ($sub->total_classes > 0): ?>
                      <tr>
                        <td class="px-4 py-3 font-semibold text-on-surface">
                          <div class="flex items-center gap-2">
                            <span><?php echo html_escape($sub->subject_name); ?></span>
                            <?php if (!empty($sub->subject_code)): ?>
                              <span class="px-2 py-0.5 rounded text-[11px] font-mono bg-surface-container-high text-on-surface-variant"><?php echo html_escape($sub->subject_code); ?></span>
                            <?php endif; ?>
                          </div>
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-on-surface"><?php echo $sub->total_classes; ?></td>
                        <td class="px-4 py-3 text-right font-semibold text-secondary"><?php echo $sub->present; ?></td>
                        <td class="px-4 py-3 text-right font-semibold text-error"><?php echo $sub->absent; ?></td>
                        <td class="px-4 py-3 text-right font-medium text-amber-600"><?php echo $sub->late; ?></td>
                        <td class="px-4 py-3 text-right font-medium text-primary"><?php echo $sub->excused; ?></td>
                        <td class="px-4 py-3 text-right">
                          <div class="flex items-center justify-end gap-2">
                            <div class="w-16 bg-surface-container-high h-2 rounded-full overflow-hidden">
                              <div class="<?php echo ($sub->percentage >= 75) ? 'bg-secondary' : 'bg-error'; ?> h-full rounded-full" style="width: <?php echo min(100, max(0, $sub->percentage)); ?>%"></div>
                            </div>
                            <span class="font-bold <?php echo ($sub->percentage >= 75) ? 'text-secondary' : 'text-error'; ?>">
                              <?php echo $sub->percentage; ?>%
                            </span>
                          </div>
                        </td>
                      </tr>
                    <?php endif; ?>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>

      <!-- 3. Month-wise Attendance Breakdown Table -->
      <div>
        <h4 class="font-title-md text-title-md font-bold text-on-surface mb-3 flex items-center gap-2">
          <span class="material-symbols-outlined text-primary text-[20px]">date_range</span>Month-wise Attendance Breakdown
        </h4>
        <div class="overflow-x-auto rounded-xl border border-outline-variant/50">
          <table class="w-full data-table zebra border-collapse text-body-md">
            <thead>
              <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
                <th class="text-left px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase">Month</th>
                <?php if ($hasPeriod || $mode === 'period'): ?>
                  <th class="text-right px-4 py-2.5 text-label-md font-semibold text-secondary uppercase">Present Periods</th>
                  <th class="text-right px-4 py-2.5 text-label-md font-semibold text-error uppercase">Absent Periods</th>
                  <th class="text-right px-4 py-2.5 text-label-md font-semibold text-amber-600 uppercase">Late</th>
                  <th class="text-right px-4 py-2.5 text-label-md font-semibold text-primary uppercase">Excused</th>
                  <th class="text-right px-4 py-2.5 text-label-md font-semibold text-on-surface uppercase">Total Periods</th>
                <?php else: ?>
                  <th class="text-right px-4 py-2.5 text-label-md font-semibold text-secondary uppercase">Present</th>
                  <th class="text-right px-4 py-2.5 text-label-md font-semibold text-error uppercase">Absent</th>
                  <th class="text-right px-4 py-2.5 text-label-md font-semibold text-amber-600 uppercase">Late</th>
                  <th class="text-right px-4 py-2.5 text-label-md font-semibold text-primary uppercase">Excused</th>
                  <th class="text-right px-4 py-2.5 text-label-md font-semibold text-on-surface uppercase">Total Days</th>
                <?php endif; ?>
                <th class="text-right px-4 py-2.5 text-label-md font-semibold text-on-surface uppercase">Percentage</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/30">
              <?php
                $monthlyList = ($hasPeriod || $mode === 'period') ? $att->period_monthly : $att->day_monthly;
              ?>
              <?php if (empty($monthlyList)): ?>
                <tr><td colspan="7" class="px-4 py-6 text-center text-on-surface-variant">No monthly attendance logged yet.</td></tr>
              <?php else: ?>
                <?php foreach ($monthlyList as $m): ?>
                  <tr>
                    <td class="px-4 py-2.5 font-semibold text-on-surface"><?php echo html_escape($m->month_name); ?></td>
                    <td class="px-4 py-2.5 text-right font-semibold text-secondary"><?php echo ($hasPeriod || $mode === 'period') ? ($m->present_periods ?? 0) : ($m->present_count ?? 0); ?></td>
                    <td class="px-4 py-2.5 text-right font-semibold text-error"><?php echo ($hasPeriod || $mode === 'period') ? ($m->absent_periods ?? 0) : ($m->absent_count ?? 0); ?></td>
                    <td class="px-4 py-2.5 text-right font-semibold text-amber-600"><?php echo ($hasPeriod || $mode === 'period') ? ($m->late_periods ?? 0) : ($m->late_count ?? 0); ?></td>
                    <td class="px-4 py-2.5 text-right font-semibold text-primary"><?php echo ($hasPeriod || $mode === 'period') ? ($m->excused_periods ?? 0) : ($m->excused_count ?? 0); ?></td>
                    <td class="px-4 py-2.5 text-right font-semibold text-on-surface"><?php echo ($hasPeriod || $mode === 'period') ? ($m->total_periods ?? 0) : ($m->total_days ?? 0); ?></td>
                    <td class="px-4 py-2.5 text-right font-bold <?php echo ($m->percentage >= 75) ? 'text-secondary' : 'text-amber-600'; ?>"><?php echo $m->percentage; ?>%</td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 4. Period-wise Detail Table (when period records exist) -->
      <?php if (!empty($att->period_records)): ?>
        <div>
          <h4 class="font-title-md text-title-md font-bold text-on-surface mb-3 flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-[20px]">view_timeline</span>Period-wise Attendance Logs
          </h4>
          <div class="overflow-x-auto rounded-xl border border-outline-variant/50 max-h-[350px]">
            <table class="w-full data-table zebra border-collapse text-body-md">
              <thead>
                <tr class="border-b border-outline-variant/60 bg-surface-container-low/50 sticky top-0">
                  <th class="text-left px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase">Date</th>
                  <th class="text-left px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase">Period</th>
                  <th class="text-left px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase">Subject</th>
                  <th class="text-center px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase">Status</th>
                  <th class="text-left px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase">Marked By</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-outline-variant/30">
                <?php foreach ($att->period_records as $rec): ?>
                  <?php
                    $badgeClass = 'bg-secondary-container text-on-secondary-container';
                    if ($rec->attendance_status === 'Absent') $badgeClass = 'bg-error-container text-on-error-container';
                    elseif (in_array($rec->attendance_status, array('Late', 'Late Coming'))) $badgeClass = 'bg-amber-100 text-amber-900';
                    elseif (in_array($rec->attendance_status, array('Half Day', 'Late / Half Day', 'Half-day'))) $badgeClass = 'bg-amber-100 text-amber-900';
                    elseif (in_array($rec->attendance_status, array('Excused', 'Leave'))) $badgeClass = 'bg-primary text-white';
                  ?>
                  <tr>
                    <td class="px-4 py-2.5 font-mono text-on-surface whitespace-nowrap">
                      <?php echo date('d M Y', strtotime($rec->attendance_date)); ?>
                      <span class="text-xs text-on-surface-variant block"><?php echo html_escape($rec->day_name ?? date('l', strtotime($rec->attendance_date))); ?></span>
                    </td>
                    <td class="px-4 py-2.5 text-on-surface font-medium whitespace-nowrap">
                      <?php echo html_escape($rec->period_name ?: 'Period ' . ($rec->period_number ?: $rec->period_id)); ?>
                    </td>
                    <td class="px-4 py-2.5 text-on-surface font-medium whitespace-nowrap">
                      <?php echo html_escape($rec->subject_name ?: '—'); ?>
                    </td>
                    <td class="px-4 py-2.5 text-center whitespace-nowrap">
                      <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold <?php echo $badgeClass; ?>">
                        <?php echo html_escape($rec->attendance_status); ?>
                      </span>
                    </td>
                    <td class="px-4 py-2.5 text-on-surface-variant whitespace-nowrap text-body-sm">
                      <?php if (!empty($rec->marked_by_display) && $rec->marked_by_display !== 'Not Available'): ?>
                        <div class="font-medium text-on-surface" title="<?php echo !empty($rec->created_at) ? 'Marked on ' . date('d M Y, h:i A', strtotime($rec->created_at)) : ''; ?>">
                          <?php echo html_escape($rec->marked_by_display); ?>
                        </div>
                        <?php if (!empty($rec->updated_by_display)): ?>
                          <div class="text-[11px] text-on-surface-variant/80 mt-0.5" title="Updated on <?php echo !empty($rec->updated_at) ? date('d M Y, h:i A', strtotime($rec->updated_at)) : ''; ?>">
                            Edited by: <?php echo html_escape($rec->updated_by_display); ?>
                          </div>
                        <?php endif; ?>
                      <?php else: ?>
                        <span class="text-on-surface-variant/70 italic">Not Available</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>

      <!-- 5. Recent Attendance Logs -->
      <div>
        <div class="flex items-center justify-between mb-3">
          <h4 class="font-title-md text-title-md font-bold text-on-surface mb-3 flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-[20px]">history</span>Recent Attendance Logs
          </h4>
          <a href="<?php echo site_url('attendance/student_attendance?student_id=' . (int)$student->student_id); ?>" class="text-label-md text-primary hover:underline font-semibold flex items-center gap-1">
            View All <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
          </a>
        </div>
        <div class="overflow-x-auto rounded-xl border border-outline-variant/50 max-h-[300px]">
          <table class="w-full data-table zebra border-collapse text-body-md">
            <thead>
              <tr class="border-b border-outline-variant/60 bg-surface-container-low/50 sticky top-0">
                <th class="text-left px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase">Date</th>
                <?php if ($hasPeriod || $mode === 'period'): ?>
                  <th class="text-left px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase">Period</th>
                  <th class="text-left px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase">Subject</th>
                <?php endif; ?>
                <th class="text-center px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase">Status</th>
                <th class="text-left px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase">Remarks</th>
                <th class="text-left px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase">Marked By</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/30">
              <?php if (empty($att->recent_records)): ?>
                <tr><td colspan="<?php echo ($hasPeriod || $mode === 'period') ? 6 : 4; ?>" class="px-4 py-6 text-center text-on-surface-variant">No attendance records logged.</td></tr>
              <?php else: ?>
                <?php foreach ($att->recent_records as $rec): ?>
                  <?php
                    $badgeClass = 'bg-secondary-container text-on-secondary-container';
                    if ($rec->attendance_status === 'Absent') $badgeClass = 'bg-error-container text-on-error-container';
                    elseif (in_array($rec->attendance_status, array('Late', 'Late Coming'))) $badgeClass = 'bg-amber-100 text-amber-900';
                    elseif (in_array($rec->attendance_status, array('Half Day', 'Late / Half Day', 'Half-day'))) $badgeClass = 'bg-amber-100 text-amber-900';
                    elseif (in_array($rec->attendance_status, array('Excused', 'Leave'))) $badgeClass = 'bg-primary text-white';
                  ?>
                  <tr>
                    <td class="px-4 py-2.5 font-mono text-on-surface whitespace-nowrap"><?php echo date('d M Y', strtotime($rec->attendance_date)); ?></td>
                    <?php if ($hasPeriod || $mode === 'period'): ?>
                      <td class="px-4 py-2.5 text-on-surface font-medium whitespace-nowrap"><?php echo html_escape($rec->period_name ?: (!empty($rec->period_id) ? 'Period ' . ($rec->period_number ?: $rec->period_id) : '—')); ?></td>
                      <td class="px-4 py-2.5 text-on-surface font-medium whitespace-nowrap"><?php echo html_escape($rec->subject_name ?: '—'); ?></td>
                    <?php endif; ?>
                    <td class="px-4 py-2.5 text-center whitespace-nowrap">
                      <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold <?php echo $badgeClass; ?>">
                        <?php echo html_escape($rec->attendance_status); ?>
                      </span>
                    </td>
                    <td class="px-4 py-2.5 text-on-surface-variant"><?php echo html_escape($rec->remarks ?: '—'); ?></td>
                    <td class="px-4 py-2.5 text-on-surface-variant whitespace-nowrap text-body-sm">
                      <?php if (!empty($rec->marked_by_display) && $rec->marked_by_display !== 'Not Available'): ?>
                        <div class="font-medium text-on-surface" title="<?php echo !empty($rec->created_at) ? 'Marked on ' . date('d M Y, h:i A', strtotime($rec->created_at)) : ''; ?>">
                          <?php echo html_escape($rec->marked_by_display); ?>
                        </div>
                        <?php if (!empty($rec->updated_by_display)): ?>
                          <div class="text-[11px] text-on-surface-variant/80 mt-0.5" title="Updated on <?php echo !empty($rec->updated_at) ? date('d M Y, h:i A', strtotime($rec->updated_at)) : ''; ?>">
                            Edited by: <?php echo html_escape($rec->updated_by_display); ?>
                          </div>
                        <?php endif; ?>
                      <?php else: ?>
                        <span class="text-on-surface-variant/70 italic">Not Available</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- TAB: FEES & FINANCE -->
  <div id="tab-fees" class="tab-pane hidden space-y-5">
    <?php if (isset($student->fee_profile)): ?>
      <?php $fp = $student->fee_profile; ?>
      
      <!-- Fee Summary KPI Grid -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="p-3.5 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 text-center">
          <span class="text-[11px] text-on-surface-variant uppercase font-semibold block">Total Assigned</span>
          <div class="text-lg font-bold font-mono text-on-surface mt-1">₹<?php echo number_format($fp->summary->total_assigned, 2); ?></div>
        </div>
        <div class="p-3.5 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 text-center">
          <span class="text-[11px] text-on-surface-variant uppercase font-semibold block">Total Paid</span>
          <div class="text-lg font-bold font-mono text-secondary mt-1">₹<?php echo number_format($fp->summary->total_paid, 2); ?></div>
        </div>
        <div class="p-3.5 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 text-center">
          <span class="text-[11px] text-on-surface-variant uppercase font-semibold block">Discounts</span>
          <div class="text-lg font-bold font-mono text-primary mt-1">₹<?php echo number_format($fp->summary->total_discount, 2); ?></div>
        </div>
        <div class="p-3.5 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 text-center">
          <span class="text-[11px] text-on-surface-variant uppercase font-semibold block">Concessions</span>
          <div class="text-lg font-bold font-mono text-amber-700 mt-1">₹<?php echo number_format($fp->summary->total_concession, 2); ?></div>
        </div>
        <div class="p-3.5 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 text-center">
          <span class="text-[11px] text-on-surface-variant uppercase font-semibold block">Total Due</span>
          <div class="text-lg font-bold font-mono text-amber-900 mt-1">₹<?php echo number_format($fp->summary->total_due, 2); ?></div>
        </div>
        <div class="p-3.5 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 text-center">
          <span class="text-[11px] text-on-surface-variant uppercase font-semibold block">Overdue Amount</span>
          <div class="text-lg font-bold font-mono text-error mt-1">₹<?php echo number_format($fp->summary->total_overdue, 2); ?></div>
        </div>
      </div>

      <!-- Itemized Invoices Table -->
      <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px]">receipt_long</span>Assigned Fee Invoices
          </h3>
          <a href="<?php echo site_url('finance/student_statement/' . $student_id); ?>" class="inline-flex items-center gap-1 px-3.5 py-1.5 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm font-semibold">
            <span class="material-symbols-outlined text-[16px]">account_balance_wallet</span>Student Statement
          </a>
        </div>
        <div class="table-scroll overflow-x-auto">
          <table class="w-full data-table zebra border-collapse text-body-md">
            <thead>
              <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
                <th class="text-left px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Invoice #</th>
                <th class="text-left px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Fee Particulars</th>
                <th class="text-right px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Original</th>
                <th class="text-right px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Discount</th>
                <th class="text-right px-4 py-2.5 text-label-md font-semibold text-secondary uppercase whitespace-nowrap">Paid</th>
                <th class="text-right px-4 py-2.5 text-label-md font-semibold text-error uppercase whitespace-nowrap">Due</th>
                <th class="text-center px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Due Date</th>
                <th class="text-center px-4 py-2.5 text-label-md font-semibold text-on-surface uppercase whitespace-nowrap">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/40">
              <?php if (empty($fp->fees)): ?>
                <tr><td colspan="8" class="px-4 py-6 text-center text-on-surface-variant">No fee invoices assigned to this student.</td></tr>
              <?php else: ?>
                <?php foreach ($fp->fees as $f): ?>
                  <?php
                    $badgeClass = 'bg-surface-container-high text-on-surface-variant';
                    if ($f->payment_status === 'Paid') $badgeClass = 'bg-secondary-container text-on-secondary-container';
                    elseif ($f->payment_status === 'Partially Paid') $badgeClass = 'bg-amber-100 text-amber-900 font-semibold';
                    elseif ($f->payment_status === 'Overdue') $badgeClass = 'bg-error-container text-on-error-container font-semibold';
                  ?>
                  <tr>
                    <td class="px-4 py-2.5 font-mono font-bold text-primary whitespace-nowrap"><?php echo html_escape($f->invoice_no); ?></td>
                    <td class="px-4 py-2.5 font-bold text-on-surface whitespace-nowrap"><?php echo html_escape($f->fee_name ?? 'School Fee'); ?></td>
                    <td class="px-4 py-2.5 text-right font-mono whitespace-nowrap">₹<?php echo number_format($f->original_amount, 2); ?></td>
                    <td class="px-4 py-2.5 text-right font-mono text-on-surface-variant whitespace-nowrap">
                      <?php echo ($f->discount_amount > 0) ? '₹' . number_format($f->discount_amount, 2) : '—'; ?>
                    </td>
                    <td class="px-4 py-2.5 text-right font-mono font-bold text-secondary whitespace-nowrap">₹<?php echo number_format($f->paid_amount, 2); ?></td>
                    <td class="px-4 py-2.5 text-right font-mono font-bold <?php echo ($f->due_amount > 0) ? 'text-error' : 'text-on-surface-variant'; ?> whitespace-nowrap">₹<?php echo number_format($f->due_amount, 2); ?></td>
                    <td class="px-4 py-2.5 text-center font-mono text-[12px] whitespace-nowrap"><?php echo !empty($f->due_date) ? date('d M Y', strtotime($f->due_date)) : '—'; ?></td>
                    <td class="px-4 py-2.5 text-center whitespace-nowrap">
                      <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold <?php echo $badgeClass; ?>">
                        <?php echo html_escape($f->payment_status); ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Payment Receipts History Table -->
      <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
        <div class="px-5 py-4 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-[20px]">payments</span>Payment Transactions & Receipts
          </h3>
        </div>
        <div class="table-scroll overflow-x-auto">
          <table class="w-full data-table zebra border-collapse text-body-md">
            <thead>
              <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
                <th class="text-left px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Receipt #</th>
                <th class="text-left px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Particulars</th>
                <th class="text-right px-4 py-2.5 text-label-md font-semibold text-secondary uppercase whitespace-nowrap">Amount Paid</th>
                <th class="text-center px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Mode</th>
                <th class="text-center px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Date</th>
                <th class="text-center px-4 py-2.5 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Receipt</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/40">
              <?php if (empty($fp->payments)): ?>
                <tr><td colspan="6" class="px-4 py-6 text-center text-on-surface-variant">No payment receipts logged yet.</td></tr>
              <?php else: ?>
                <?php foreach ($fp->payments as $p): ?>
                  <tr>
                    <td class="px-4 py-2.5 font-mono font-bold text-primary whitespace-nowrap"><?php echo html_escape($p->receipt_no); ?></td>
                    <td class="px-4 py-2.5 font-medium text-on-surface whitespace-nowrap"><?php echo html_escape($p->fee_name ?? 'Fee Payment'); ?></td>
                    <td class="px-4 py-2.5 text-right font-mono font-bold text-secondary whitespace-nowrap">₹<?php echo number_format($p->amount_paid, 2); ?></td>
                    <td class="px-4 py-2.5 text-center whitespace-nowrap">
                      <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-surface-container-high text-on-surface"><?php echo html_escape($p->payment_method ?? 'Cash'); ?></span>
                    </td>
                    <td class="px-4 py-2.5 text-center font-mono text-[12px] whitespace-nowrap"><?php echo !empty($p->payment_date) ? date('d M Y', strtotime($p->payment_date)) : '—'; ?></td>
                    <td class="px-4 py-2.5 text-center whitespace-nowrap">
                      <a href="<?php echo site_url('finance/student_statement/' . $student_id); ?>" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-surface-container-high text-primary hover:bg-primary hover:text-white transition-colors text-[12px] font-semibold">
                        <span class="material-symbols-outlined text-[15px]">visibility</span>Statement
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- TAB 7: TRANSFER / TC -->
  <div id="tab-transfer" class="tab-pane hidden space-y-5">
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-headline-md text-headline-md text-on-surface">Transfer / School Leaving Certificate</h3>
        <?php if (empty($student->transfer)): ?>
          <a href="<?php echo site_url('students/transfers'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors"><span class="material-symbols-outlined text-[18px]">post_add</span>Issue TC</a>
        <?php endif; ?>
      </div>

      <?php if (!empty($student->transfer)): ?>
        <div class="p-5 rounded-xl bg-surface-container-low border border-outline-variant/40 space-y-4">
          <div class="flex items-center justify-between border-b border-outline-variant/40 pb-3">
            <div>
              <div class="text-[12px] text-on-surface-variant">Transfer Certificate Number</div>
              <div class="text-title-md font-bold text-primary font-mono"><?php echo html_escape($student->transfer->tc_number); ?></div>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-label-md font-semibold bg-secondary-container text-on-secondary-container">Issued</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-body-md">
            <div><span class="text-on-surface-variant text-[12px] block">Date of Issue</span><span class="font-medium text-on-surface"><?php echo date('d M Y', strtotime($student->transfer->transfer_date)); ?></span></div>
            <div><span class="text-on-surface-variant text-[12px] block">Reason for Leaving</span><span class="font-medium text-on-surface"><?php echo html_escape($student->transfer->reason); ?></span></div>
            <div><span class="text-on-surface-variant text-[12px] block">Conduct & Behavior</span><span class="font-medium text-on-surface"><?php echo html_escape($student->transfer->conduct); ?></span></div>
            <div class="sm:col-span-3"><span class="text-on-surface-variant text-[12px] block">Remarks</span><span class="text-on-surface"><?php echo html_escape($student->transfer->remarks ?: 'All school dues cleared.'); ?></span></div>
          </div>
          <div class="pt-3 border-t border-outline-variant/40">
            <a href="<?php echo site_url('students/tc/' . $student->transfer->transfer_id); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-primary text-on-primary text-label-md hover:bg-primary/90 transition-colors shadow-sm"><span class="material-symbols-outlined text-[18px]">print</span>Print Official Transfer Certificate</a>
          </div>
        </div>
      <?php else: ?>
        <div class="p-6 rounded-xl bg-surface-container-low border border-outline-variant/40 text-center">
          <span class="material-symbols-outlined text-[48px] text-on-surface-variant/50 mb-2">school</span>
          <h4 class="font-title-md text-title-md text-on-surface">No Transfer Certificate Issued</h4>
          <p class="text-body-md text-on-surface-variant mt-1 mb-4">This student is currently actively enrolled or has not requested a Transfer Certificate.</p>
          <a href="<?php echo site_url('students/transfers'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm"><span class="material-symbols-outlined text-[18px]">add_circle</span>Issue Transfer Certificate</a>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Tab Switching & Document Upload Script -->
  <script>
    function switchTab(tabName) {
      // Hide all panes
      document.querySelectorAll('.tab-pane').forEach(function(pane) {
        pane.classList.add('hidden');
      });
      // Deactivate all buttons
      document.querySelectorAll('.tab-btn').forEach(function(btn) {
        btn.classList.remove('border-secondary', 'text-primary', 'font-medium');
        btn.classList.add('border-transparent', 'text-on-surface-variant');
      });
      // Show selected pane
      var targetPane = document.getElementById('tab-' + tabName);
      if (targetPane) {
        targetPane.classList.remove('hidden');
      }
      // Activate button
      var targetBtn = document.querySelector('.tab-btn[data-tab="' + tabName + '"]');
      if (targetBtn) {
        targetBtn.classList.remove('border-transparent', 'text-on-surface-variant');
        targetBtn.classList.add('border-secondary', 'text-primary', 'font-medium');
      }
    }

    function initActiveTab() {
      var urlParams = new URLSearchParams(window.location.search);
      var queryTab = urlParams.get('tab');
      var hash = window.location.hash ? window.location.hash.replace('#', '').replace('tab-', '') : '';
      var activeTab = queryTab || hash;

      if (activeTab) {
        switchTab(activeTab);
      }
    }

    function initDocUploadValidation() {
      // Student Document Repository file-upload client-side validation
      var docFileInput = document.getElementById('document_file');
      var docFileError = document.getElementById('document_file_error');
      var docUploadForm = document.getElementById('student-doc-upload-form');
      var allowedExtensions = ['pdf', 'png', 'jpg', 'jpeg', 'doc', 'docx'];

      function clearDocFileError() {
        if (docFileError) {
          docFileError.textContent = '';
          docFileError.classList.add('hidden');
        }
        if (docFileInput) {
          docFileInput.classList.remove('!border-error');
        }
      }

      function showDocFileError(msg) {
        if (docFileError) {
          docFileError.textContent = msg;
          docFileError.classList.remove('hidden');
        }
        if (docFileInput) {
          docFileInput.classList.add('!border-error');
        }
      }

      if (docFileInput) {
        docFileInput.addEventListener('change', function() {
          clearDocFileError();
          if (!docFileInput.files || docFileInput.files.length === 0) {
            return;
          }

          var file = docFileInput.files[0];
          var fileName = file.name || '';
          var ext = fileName.split('.').pop().toLowerCase();

          if (allowedExtensions.indexOf(ext) === -1) {
            showDocFileError('Unsupported file format. Please upload PDF, PNG, JPG/JPEG, DOC, or DOCX.');
            docFileInput.value = ''; // Reset invalid file
          }
        });
      }

      if (docUploadForm) {
        docUploadForm.addEventListener('submit', function(e) {
          clearDocFileError();
          if (!docFileInput || !docFileInput.files || docFileInput.files.length === 0) {
            return;
          }
          var file = docFileInput.files[0];
          var fileName = file.name || '';
          var ext = fileName.split('.').pop().toLowerCase();

          if (allowedExtensions.indexOf(ext) === -1) {
            e.preventDefault();
            showDocFileError('Unsupported file format. Please upload PDF, PNG, JPG/JPEG, DOC, or DOCX.');
            docFileInput.value = '';
          }
        });
      }
    }

    function initPdfDownloadLink() {
      var pdfBtn = document.getElementById('btn-download-attendance-pdf');
      if (pdfBtn) {
        pdfBtn.addEventListener('click', function(e) {
          var form = document.getElementById('attendance-filter-form');
          if (form) {
            var yearSelect = form.querySelector('select[name="attendance_year_id"]');
            var fromInput = form.querySelector('input[name="from_date"]');
            var toInput = form.querySelector('input[name="to_date"]');
            var baseHref = pdfBtn.getAttribute('href').split('?')[0];
            var params = new URLSearchParams();
            if (yearSelect && yearSelect.value) params.set('attendance_year_id', yearSelect.value);
            if (fromInput && fromInput.value) params.set('from_date', fromInput.value);
            if (toInput && toInput.value) params.set('to_date', toInput.value);
            pdfBtn.setAttribute('href', baseHref + '?' + params.toString());
          }
        });
      }
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', function() {
        initActiveTab();
        initDocUploadValidation();
        initPdfDownloadLink();
      });
    } else {
      initActiveTab();
      initDocUploadValidation();
      initPdfDownloadLink();
    }
    window.addEventListener('hashchange', initActiveTab);
  </script>
