<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- Include Client-Side Export Libraries -->
<script src="<?php echo base_url('assets/vendor/html2canvas.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendor/jspdf.umd.min.js'); ?>"></script>

<div class="space-y-5">

  <!-- Flash Messages -->
  <?php if ($this->session->flashdata('success')): ?>
    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-primary text-sm flex items-center gap-2 shadow-xs">
      <span class="material-symbols-outlined text-[20px] text-emerald-600">check_circle</span>
      <span class="font-medium"><?php echo html_escape($this->session->flashdata('success')); ?></span>
    </div>
  <?php endif; ?>
  <?php if ($this->session->flashdata('error')): ?>
    <div class="p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm flex items-center gap-2 shadow-xs">
      <span class="material-symbols-outlined text-[20px] text-red-600">error</span>
      <span class="font-medium"><?php echo html_escape($this->session->flashdata('error')); ?></span>
    </div>
  <?php endif; ?>

  <!-- Top Title & Navigation Bar -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-1 no-print">
    <div class="flex items-center gap-3">
      <span class="w-10 h-10 rounded-xl bg-primary text-white flex items-center justify-center shadow-xs">
        <span class="material-symbols-outlined text-[22px]">badge</span>
      </span>
      <div>
        <h2 class="font-bold text-xl text-slate-900 leading-tight">Student ID Card</h2>
        <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-0.5">
          <span>Student Management</span>
          <span class="material-symbols-outlined text-[12px]">chevron_right</span>
          <span>Student Services</span>
          <span class="material-symbols-outlined text-[12px]">chevron_right</span>
          <span class="text-slate-800 font-medium">Student ID Card</span>
        </div>
      </div>
    </div>

    <!-- Top Right Action Buttons -->
    <div class="flex items-center gap-2.5">
      <button type="button" onclick="openSettingsModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition-colors shadow-2xs cursor-pointer">
        <span class="material-symbols-outlined text-[18px] text-slate-500">settings</span>
        <span>ID Card Settings</span>
      </button>
      <button type="button" onclick="openHistoryModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition-colors shadow-2xs cursor-pointer">
        <span class="material-symbols-outlined text-[18px] text-slate-500">history</span>
        <span>History</span>
      </button>
    </div>
  </div>

  <!-- Main Content Grid -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

    <!-- =========================================================================
         LEFT COLUMN: STUDENT SELECTION (4 COLS)
         ========================================================================= -->
    <div class="lg:col-span-4 xl:col-span-4 space-y-4">
      
      <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-4">
        
        <!-- Segmented Tab: Single Student / Bulk Students -->
        <div class="grid grid-cols-2 gap-1 p-1 bg-slate-100 rounded-xl border border-slate-200">
          <button type="button" id="tab-single-btn" onclick="toggleSelectionMode('single')" class="py-2 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5 bg-white text-primary shadow-xs">
            <span class="material-symbols-outlined text-[17px] text-emerald-700">person</span>
            <span>Single Student</span>
          </button>
          <button type="button" id="tab-bulk-btn" onclick="toggleSelectionMode('bulk')" class="py-2 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5 text-slate-600 hover:text-slate-900">
            <span class="material-symbols-outlined text-[17px]">groups</span>
            <span>Bulk Students</span>
          </button>
        </div>

        <!-- Single Student Search & List Mode -->
        <div id="single-select-wrapper" class="space-y-4">
          
          <!-- Search & Filter Controls -->
          <div class="space-y-2.5">
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Search Student</label>
            
            <!-- Class Dropdown -->
            <div class="relative">
              <select id="left-class-filter" onchange="filterStudentCards()" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 bg-white text-slate-800 focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600 appearance-none font-medium pr-8">
                <option value="">All Classes</option>
                <?php foreach ($classes as $c): ?>
                  <option value="<?php echo $c->class_id; ?>" <?php echo ($selected_class == $c->class_id) ? 'selected' : ''; ?>><?php echo html_escape($c->class_name); ?></option>
                <?php endforeach; ?>
              </select>
              <span class="material-symbols-outlined absolute right-3 top-3 text-slate-400 text-[18px] pointer-events-none">expand_more</span>
            </div>

            <!-- Keyword Search Input + Button -->
            <div class="flex gap-2">
              <div class="relative flex-1">
                <input type="text" id="left-search-input" onkeyup="if(event.key === 'Enter') filterStudentCards()" placeholder="Search by name, admission no., roll no..." class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 bg-white text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600"/>
              </div>
              <button type="button" onclick="filterStudentCards()" class="p-2.5 rounded-xl bg-primary text-white hover:bg-button-primary-hover transition-colors shadow-2xs flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[19px]">search</span>
              </button>
            </div>
          </div>

          <!-- Student List Header -->
          <div class="flex items-center justify-between pt-1">
            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Select Student</span>
            <span id="filtered-count-badge" class="text-xs font-medium text-slate-500"><?php echo count($students); ?> found</span>
          </div>

          <!-- Student Cards List -->
          <div id="student-cards-container" class="space-y-2 max-h-[380px] overflow-y-auto pr-1">
            <?php if (empty($students)): ?>
              <div class="p-8 text-center text-slate-400 text-sm bg-slate-50 rounded-xl border border-slate-200">
                No students found matching selection.
              </div>
            <?php else: ?>
              <?php foreach ($students as $idx => $st): ?>
                <?php
                  $fullName = trim($st->first_name . ' ' . ($st->middle_name ? $st->middle_name . ' ' : '') . $st->last_name);
                  $nameParts = explode(' ', $fullName);
                  $initials = '';
                  foreach ($nameParts as $np) { if (!empty($np)) $initials .= strtoupper($np[0]); }
                  $initials = substr($initials, 0, 2) ?: 'ST';
                  $classDisplay = trim(($st->class_name ?? '') . (($st->division_name ?? $st->section_name) ? ' - ' . ($st->division_name ?? $st->section_name) : ''));
                  $isSelected = ($selected_student && $selected_student->student_id == $st->student_id) || (!$selected_student && $idx === 0);
                ?>
                <div onclick="selectStudentCard(<?php echo $st->student_id; ?>, this)" class="student-select-card p-3 rounded-xl border transition-all cursor-pointer flex items-center gap-3.5 <?php echo $isSelected ? 'border-emerald-600 bg-emerald-50/40 ring-1 ring-emerald-600 shadow-2xs' : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/60'; ?>" data-student-id="<?php echo $st->student_id; ?>" data-name="<?php echo strtolower(html_escape($fullName)); ?>" data-adm="<?php echo strtolower(html_escape($st->admission_number)); ?>" data-roll="<?php echo strtolower(html_escape($st->roll_number ?? '')); ?>" data-class="<?php echo $st->class_id; ?>" data-division="<?php echo $st->division_id ?? $st->section_id; ?>" data-section="<?php echo $st->division_id ?? $st->section_id; ?>">
                  
                  <!-- Custom Radio Dot Indicator -->
                  <div class="radio-indicator w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0 <?php echo $isSelected ? 'border-emerald-600 bg-white' : 'border-slate-300 bg-white'; ?>">
                    <div class="radio-dot w-2 h-2 rounded-full bg-emerald-600 <?php echo $isSelected ? '' : 'hidden'; ?>"></div>
                  </div>

                  <!-- Thumbnail Avatar -->
                  <div class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0 overflow-hidden shadow-2xs font-bold text-xs text-primary">
                    <?php if (!empty($st->photo) && file_exists(FCPATH . 'uploads/students/' . $st->photo)): ?>
                      <img src="<?php echo base_url('uploads/students/' . $st->photo); ?>" alt="<?php echo html_escape($fullName); ?>" class="w-full h-full object-cover"/>
                    <?php else: ?>
                      <span><?php echo html_escape($initials); ?></span>
                    <?php endif; ?>
                  </div>

                  <!-- Details -->
                  <div class="min-w-0 flex-1">
                    <div class="font-bold text-slate-900 text-sm truncate leading-snug"><?php echo html_escape($fullName); ?></div>
                    <div class="text-xs text-slate-500 font-mono mt-0.5 truncate">
                      <span>Adm No: <?php echo html_escape($st->admission_number); ?></span>
                    </div>
                    <div class="text-xs text-slate-600 font-medium mt-0.5 truncate">
                      <span><?php echo html_escape($classDisplay ?: 'Grade 10 - A'); ?></span>
                    </div>
                  </div>

                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <!-- Pagination Bar -->
          <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs">
            <button type="button" id="prev-page-btn" onclick="paginateStudents(-1)" class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors font-medium">
              &laquo; Prev
            </button>
            <div id="pagination-pills" class="flex items-center gap-1 font-semibold">
              <!-- Rendered via JS -->
            </div>
            <button type="button" id="next-page-btn" onclick="paginateStudents(1)" class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors font-medium">
              Next &raquo;
            </button>
          </div>

        </div>

        <!-- Bulk Students Mode -->
        <div id="bulk-select-wrapper" class="space-y-4 hidden">
          <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-xs text-emerald-900">
            <span class="font-bold">Bulk Mode Active:</span> Select students using the checkboxes below to generate, print, or download batch cards.
          </div>

          <div class="flex items-center justify-between text-xs">
            <div class="flex items-center gap-2">
              <input type="checkbox" id="bulk-master-cb" onchange="toggleAllBulkCbs(this)" class="w-4 h-4 rounded text-emerald-600 border-slate-300 focus:ring-emerald-600"/>
              <label for="bulk-master-cb" class="font-bold text-slate-700 cursor-pointer">Select All</label>
            </div>
            <span class="text-slate-500 font-medium"><span id="bulk-checked-count">0</span> selected</span>
          </div>

          <div class="space-y-2 max-h-[360px] overflow-y-auto pr-1">
            <?php foreach ($students as $st): ?>
              <?php
                $fullName = trim($st->first_name . ' ' . ($st->middle_name ? $st->middle_name . ' ' : '') . $st->last_name);
                $classDisplay = trim(($st->class_name ?? '') . (($st->division_name ?? $st->section_name) ? ' - ' . ($st->division_name ?? $st->section_name) : ''));
              ?>
              <div class="p-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition-colors flex items-center gap-3 text-xs">
                <input type="checkbox" value="<?php echo $st->student_id; ?>" class="bulk-cb w-4 h-4 rounded text-emerald-600 border-slate-300 focus:ring-emerald-600" onchange="updateBulkCount()"/>
                <div class="min-w-0 flex-1">
                  <div class="font-bold text-slate-900 truncate"><?php echo html_escape($fullName); ?></div>
                  <div class="text-[11px] text-slate-500 font-mono truncate"><?php echo html_escape($st->admission_number); ?> · <?php echo html_escape($classDisplay); ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="flex gap-2 pt-2">
            <button type="button" onclick="executeBulkPrint()" class="flex-1 py-2.5 rounded-xl bg-primary text-white font-bold text-xs hover:bg-button-primary-hover transition-colors flex items-center justify-center gap-1.5 shadow-2xs">
              <span class="material-symbols-outlined text-[17px]">print</span>
              <span>Print Selected</span>
            </button>
            <button type="button" onclick="executeBulkPdf()" class="flex-1 py-2.5 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition-colors flex items-center justify-center gap-1.5 shadow-2xs">
              <span class="material-symbols-outlined text-[17px]">picture_as_pdf</span>
              <span>Bulk PDF</span>
            </button>
          </div>
        </div>

      </div>

    </div>

    <!-- =========================================================================
         RIGHT COLUMN: LIVE CR80 PORTRAIT CARD PREVIEW (FRONT & BACK SIDE-BY-SIDE)
         ========================================================================= -->
    <div class="lg:col-span-8 xl:col-span-8 space-y-4">
      
      <?php
        $curStudent = $selected_student ?: (!empty($students) ? $students[0] : null);
        $curFullName = '';
        $curInitials = 'ST';
        if ($curStudent) {
          $curFullName = trim($curStudent->first_name . ' ' . ($curStudent->middle_name ? $curStudent->middle_name . ' ' : '') . $curStudent->last_name);
          $nameParts = explode(' ', $curFullName);
          $curInitials = '';
          foreach ($nameParts as $np) { if (!empty($np)) $curInitials .= strtoupper($np[0]); }
          $curInitials = substr($curInitials, 0, 2) ?: 'ST';
        }
        $fc = !empty($id_card_design->field_config) ? $id_card_design->field_config : [];
      ?>

      <!-- ID Card Preview Box -->
      <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-5">
        
        <!-- Header with Front/Back Switch -->
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <div class="flex items-center gap-2">
            <h3 class="font-bold text-base text-slate-900">ID Card Preview</h3>
          </div>

          <!-- Front / Back Toggle Buttons -->
          <div class="flex items-center p-1 bg-slate-100 rounded-xl border border-slate-200">
            <button type="button" id="btn-toggle-front" onclick="filterCardView('front')" class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all bg-primary text-white shadow-xs">
              Front
            </button>
            <button type="button" id="btn-toggle-back" onclick="filterCardView('back')" class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-600 hover:text-slate-900">
              Back
            </button>
            <button type="button" id="btn-toggle-both" onclick="filterCardView('both')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-600 hover:text-slate-900 hidden sm:inline-block">
              Both
            </button>
          </div>
        </div>

        <!-- Preview Stage: Side-by-Side Front & Back with Dimension Line Indicators -->
        <div class="py-6 px-3 flex flex-wrap items-center justify-center gap-10 bg-slate-50/70 rounded-2xl border border-dashed border-slate-200/90 overflow-hidden">
          
          <!-- ================= FRONT SIDE CR80 (2.125" x 3.375") ================= -->
          <div id="front-card-stage-wrapper" class="flex flex-col items-center">
            
            <!-- Top Dimension Label (2.125 inch / 54 mm) -->
            <div class="w-[280px] flex items-center justify-between text-[11px] font-semibold text-slate-600 mb-2 px-1">
              <span class="text-slate-400">&bull;</span>
              <span class="border-b border-dashed border-slate-400 flex-1 mx-2 text-center pb-0.5">2.125 inch (54 mm)</span>
              <span class="text-slate-400">&bull;</span>
            </div>

            <!-- Card + Left Height Dimension Wrapper -->
            <div class="flex items-center gap-3">
              
              <!-- Left Dimension Indicator (3.375 inch / 86 mm) -->
              <div class="h-[445px] flex flex-col items-center justify-between text-[11px] font-semibold text-slate-600 py-1 select-none">
                <span class="text-slate-400">&bull;</span>
                <span class="writing-mode-vertical border-l border-dashed border-slate-400 h-full mx-1 flex items-center justify-center text-center pl-1 text-[10px]" style="writing-mode: vertical-rl; transform: rotate(180deg);">3.375 inch (86 mm)</span>
                <span class="text-slate-400">&bull;</span>
              </div>

              <!-- FRONT CARD CONTAINER -->
              <div id="portrait-cr80-front" class="relative select-none shadow-xl transition-all duration-300 bg-white" style="width: 280px; height: 445px; aspect-ratio: 2.125 / 3.375; border-radius: 18px; overflow: hidden; border: 1px solid #cbd5e1;">
                
                <!-- Lanyard Slot Accent Hole -->
                <div class="absolute top-2 left-1/2 -translate-x-1/2 w-12 h-2 bg-white/90 border border-slate-300/80 rounded-full z-20 shadow-inner"></div>

                <!-- Custom Front Design Background Image -->
                <img id="dom-front-bg" src="<?php echo html_escape(!empty($id_card_design->has_front) ? $id_card_design->front_url : ''); ?>" alt="Front Design" class="<?php echo (!empty($id_card_design->has_front)) ? '' : 'hidden'; ?> absolute inset-0 w-full h-full object-cover z-0" />

                <!-- Geometric Teal / Charcoal / Silver Background (SVG Fallback) -->
                <svg id="dom-front-svg" class="<?php echo (!empty($id_card_design->has_front)) ? 'hidden' : ''; ?> absolute inset-0 w-full h-full pointer-events-none z-1" viewBox="0 0 280 445" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <defs>
                    <!-- Deep Rich Teal Header Gradient -->
                    <linearGradient id="frontTealGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                      <stop offset="0%" stop-color="#00656e" />
                      <stop offset="45%" stop-color="#087f8c" />
                      <stop offset="100%" stop-color="#023b42" />
                    </linearGradient>
                    <!-- Dark Charcoal Diagonal Slice -->
                    <linearGradient id="frontDarkGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                      <stop offset="0%" stop-color="#1e293b" />
                      <stop offset="100%" stop-color="#0a0f1d" />
                    </linearGradient>
                    <!-- Diagonal Silver Divider Stripe -->
                    <linearGradient id="frontSilverGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                      <stop offset="0%" stop-color="#f8fafc" />
                      <stop offset="50%" stop-color="#cbd5e1" />
                      <stop offset="100%" stop-color="#94a3b8" />
                    </linearGradient>
                  </defs>

                  <!-- Card Base Surface -->
                  <rect width="280" height="445" fill="#ffffff" />

                  <!-- Main Upper Teal Diagonal Polygon -->
                  <path d="M0 0 H280 V118 L0 262 Z" fill="url(#frontTealGrad)" />

                  <!-- Upper-Left Overlapping Charcoal Wedge -->
                  <path d="M0 0 H100 L0 246 Z" fill="url(#frontDarkGrad)" />

                  <!-- Sleek Silver Accent Divider Stripe -->
                  <polygon points="0,262 280,118 280,124 0,268" fill="url(#frontSilverGrad)" />

                  <!-- Subtle Bottom-Right Corner Accent -->
                  <path d="M280 445 H245 L280 410 Z" fill="url(#frontTealGrad)" opacity="0.25" />
                </svg>

                <!-- Front Content Layer (Fallback built-in layout) -->
                <div id="dom-front-fallback-layout" class="<?php echo (!empty($id_card_design->has_front)) ? 'hidden' : ''; ?> relative z-10 h-full flex flex-col justify-between p-3.5 pt-5 pb-3">
                  
                  <!-- Top School Header (Logo + Title) -->
                  <div class="flex items-center justify-between px-2 pt-1">
                    <div class="h-8 max-w-[95px] flex items-center">
                      <img id="dom-school-logo" src="<?php echo base_url('assets/logo.png'); ?>" alt="Logo" class="max-h-7 max-w-[90px] object-contain drop-shadow-xs brightness-110"/>
                    </div>
                    <div class="text-right min-w-0 flex-1 pl-2">
                      <div class="font-black text-[11px] text-white tracking-wider uppercase truncate leading-tight drop-shadow-xs">
                        <?php echo html_escape($settings->school_name ?? 'LOGIN2'); ?>
                      </div>
                      <div id="dom-school-subtitle" class="font-bold text-[8px] text-teal-100/90 tracking-widest uppercase truncate leading-tight">
                        <?php echo html_escape($settings->card_title ?? 'PUBLIC SCHOOL'); ?>
                      </div>
                    </div>
                  </div>

                  <!-- Central Portrait Photo Medallion -->
                  <div class="my-0.5 mx-auto relative z-10">
                    <div class="w-[96px] h-[96px] rounded-full p-1 bg-white shadow-lg flex items-center justify-center" style="box-shadow: 0 6px 16px rgba(0,0,0,0.2), 0 0 0 3px #cbd5e1;">
                      <div class="w-full h-full rounded-full overflow-hidden bg-slate-100 flex items-center justify-center border border-slate-200">
                        <img id="dom-student-photo" src="<?php echo (!empty($curStudent->photo) && file_exists(FCPATH . 'uploads/students/' . $curStudent->photo)) ? base_url('uploads/students/' . $curStudent->photo) : ''; ?>" alt="Student Photo" class="w-full h-full object-cover rounded-full <?php echo (!empty($curStudent->photo) && file_exists(FCPATH . 'uploads/students/' . $curStudent->photo)) ? '' : 'hidden'; ?>"/>
                        <div id="dom-student-photo-fallback" class="w-full h-full rounded-full bg-gradient-to-br from-teal-700 to-teal-900 text-white flex items-center justify-center font-black text-xl tracking-wider <?php echo (!empty($curStudent->photo) && file_exists(FCPATH . 'uploads/students/' . $curStudent->photo)) ? 'hidden' : ''; ?>">
                          <?php echo html_escape($curInitials); ?>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Student Name & Title Badge -->
                  <div class="text-center px-2 -mt-1">
                    <div id="dom-student-name" class="font-black text-[14px] text-teal-900 tracking-wide uppercase leading-tight truncate">
                      <?php echo html_escape($curFullName ?: 'STUDENT NAME'); ?>
                    </div>
                    <div class="font-bold text-[10px] text-teal-600 tracking-wider uppercase mt-0.5">
                      STUDENT
                    </div>
                  </div>

                  <!-- Aligned Details Grid Table -->
                  <div class="mx-2 bg-slate-50/70 rounded-xl py-2 px-3 border border-slate-200/60 shadow-2xs" style="padding-left: 25px;">
                    <table class="w-full text-[9px] leading-tight">
                      <tr>
                        <td class="w-20 font-bold text-teal-700 py-0.5 uppercase">ID</td>
                        <td class="w-2 text-center text-slate-400 font-bold">:</td>
                        <td id="dom-student-adm" class="font-black text-slate-900 py-0.5 font-mono truncate pl-1"><?php echo html_escape($curStudent->admission_number ?? ''); ?></td>
                      </tr>
                      <tr id="row-guardian-name">
                        <td class="font-bold text-teal-700 py-0.5 uppercase">Father's Name</td>
                        <td class="text-center text-slate-400 font-bold">:</td>
                        <td id="dom-student-father" class="font-bold text-slate-800 py-0.5 truncate pl-1"><?php echo html_escape($curStudent->guardian_name ?? ''); ?></td>
                      </tr>
                      <tr>
                        <td class="font-bold text-teal-700 py-0.5 uppercase">Class & Div</td>
                        <td class="text-center text-slate-400 font-bold">:</td>
                        <td class="font-bold text-slate-800 py-0.5 truncate pl-1">
                          <span id="dom-student-class"><?php echo html_escape($curStudent->class_name ?? ''); ?></span> - <span id="dom-student-division" class="dom-student-division"><?php echo html_escape($curStudent->division_name ?? $curStudent->section_name ?? ''); ?></span>
                        </td>
                      </tr>
                      <tr id="row-roll-no" class="<?php echo !empty($curStudent->roll_number) ? '' : 'hidden'; ?>">
                        <td class="font-bold text-teal-700 py-0.5 uppercase">Roll No.</td>
                        <td class="text-center text-slate-400 font-bold">:</td>
                        <td id="dom-student-roll" class="font-bold text-slate-800 py-0.5 font-mono truncate pl-1"><?php echo html_escape($curStudent->roll_number ?? ''); ?></td>
                      </tr>
                      <tr>
                        <td class="font-bold text-teal-700 py-0.5 uppercase">D.O.B</td>
                        <td class="text-center text-slate-400 font-bold">:</td>
                        <td id="dom-student-dob" class="font-bold text-slate-800 py-0.5 truncate pl-1"><?php echo !empty($curStudent->date_of_birth) ? date('d-m-Y', strtotime($curStudent->date_of_birth)) : ''; ?></td>
                      </tr>
                      <tr id="row-blood-group" class="<?php echo !empty($curStudent->blood_group) ? '' : 'hidden'; ?>">
                        <td class="font-bold text-teal-700 py-0.5 uppercase">Blood Group</td>
                        <td class="text-center text-slate-400 font-bold">:</td>
                        <td id="dom-student-blood" class="font-black text-rose-600 py-0.5 pl-1"><?php echo html_escape($curStudent->blood_group ?? ''); ?></td>
                      </tr>
                    </table>
                  </div>

                  <!-- Subtle Bottom Padding -->
                  <div class="h-0.5"></div>

                </div>

                <!-- Custom Front Dynamic Overlay Layer (Static School Details are inside the uploaded design) -->
                <div id="dom-front-custom-overlay" class="<?php echo (!empty($id_card_design->has_front)) ? '' : 'hidden'; ?> absolute inset-0 w-full h-full pointer-events-none z-10">
                  <!-- Photo -->
                  <div id="df-front-photo" style="position: absolute; top: <?php echo floatval($fc['photo']['top'] ?? 24); ?>%; left: <?php echo floatval($fc['photo']['left'] ?? 30); ?>%; width: <?php echo floatval($fc['photo']['width'] ?? 40); ?>%; height: <?php echo floatval($fc['photo']['height'] ?? 25); ?>%; border-radius: <?php echo html_escape($fc['photo']['radius'] ?? '8px'); ?>; overflow: hidden; border: <?php echo html_escape($fc['photo']['border'] ?? '1px solid #cbd5e1'); ?>; background: #ffffff; <?php echo (isset($fc['photo']['enabled']) && empty($fc['photo']['enabled'])) ? 'display: none;' : ''; ?>">
                    <img id="dom-df-photo-img" src="<?php echo (!empty($curStudent->photo) && file_exists(FCPATH . 'uploads/students/' . $curStudent->photo)) ? base_url('uploads/students/' . $curStudent->photo) : ''; ?>" alt="Student" class="<?php echo (!empty($curStudent->photo) && file_exists(FCPATH . 'uploads/students/' . $curStudent->photo)) ? '' : 'hidden'; ?> w-full h-full object-cover" />
                    <div id="dom-df-photo-initials" class="<?php echo (!empty($curStudent->photo) && file_exists(FCPATH . 'uploads/students/' . $curStudent->photo)) ? 'hidden' : ''; ?> w-full h-full flex items-center justify-center bg-slate-200 font-bold text-slate-600 text-sm">
                      <?php echo html_escape($curInitials); ?>
                    </div>
                  </div>
                  <!-- Student Name & Role -->
                  <div id="df-front-student_name" style="position: absolute; top: <?php echo floatval($fc['student_name']['top'] ?? 52); ?>%; left: <?php echo floatval($fc['student_name']['left'] ?? 10); ?>%; width: <?php echo floatval($fc['student_name']['width'] ?? 80); ?>%; font-size: <?php echo html_escape($fc['student_name']['font_size'] ?? '14px'); ?>; font-weight: <?php echo html_escape($fc['student_name']['weight'] ?? 'bold'); ?>; color: <?php echo html_escape($fc['student_name']['color'] ?? '#0f172a'); ?>; text-align: <?php echo html_escape($fc['student_name']['align'] ?? 'center'); ?>; text-transform: uppercase; <?php echo (isset($fc['student_name']['enabled']) && empty($fc['student_name']['enabled'])) ? 'display: none;' : ''; ?>">
                    <div id="dom-df-student_name-val" class="truncate leading-tight"><?php echo html_escape($curFullName ?: 'STUDENT NAME'); ?></div>
                    <div class="font-bold text-[10px] tracking-wider uppercase mt-0.5" style="color: <?php echo html_escape($fc['student_name']['color'] ?? '#0d9488'); ?>; opacity: 0.85;">STUDENT</div>
                  </div>
                  <!-- Structured Aligned Student Details Block -->
                  <?php echo $this->document_design_service->render_student_details_table($curStudent, $fc, ['context' => 'id_card_view']); ?>
                </div>

              </div>

            </div>

          </div>

          <!-- ================= BACK SIDE CR80 (2.125" x 3.375") ================= -->
          <div id="back-card-stage-wrapper" class="flex flex-col items-center">
            
            <!-- Top Dimension Label (2.125 inch / 54 mm) -->
            <div class="w-[280px] flex items-center justify-between text-[11px] font-semibold text-slate-600 mb-2 px-1">
              <span class="text-slate-400">&bull;</span>
              <span class="border-b border-dashed border-slate-400 flex-1 mx-2 text-center pb-0.5">2.125 inch (54 mm)</span>
              <span class="text-slate-400">&bull;</span>
            </div>

            <!-- Card + Right Height Dimension Wrapper -->
            <div class="flex items-center gap-3">
              
              <!-- BACK CARD CONTAINER -->
              <div id="portrait-cr80-back" class="relative select-none shadow-xl transition-all duration-300 bg-white" style="width: 280px; height: 445px; aspect-ratio: 2.125 / 3.375; border-radius: 18px; overflow: hidden; border: 1px solid #cbd5e1;">
                
                <!-- Lanyard Slot Accent Hole -->
                <div class="absolute top-2 left-1/2 -translate-x-1/2 w-12 h-2 bg-white/90 border border-slate-300/80 rounded-full z-20 shadow-inner"></div>

                <!-- Custom Back Design Background Image -->
                <img id="dom-back-bg" src="<?php echo html_escape(!empty($id_card_design->has_back) ? $id_card_design->back_url : ''); ?>" alt="Back Design" class="<?php echo (!empty($id_card_design->has_back)) ? '' : 'hidden'; ?> absolute inset-0 w-full h-full object-cover z-0" />

                <!-- Geometric Teal / Charcoal / Silver Background (SVG Fallback) -->
                <svg id="dom-back-svg" class="<?php echo (!empty($id_card_design->has_back)) ? 'hidden' : ''; ?> absolute inset-0 w-full h-full pointer-events-none z-1" viewBox="0 0 280 445" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <defs>
                    <linearGradient id="backTealGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                      <stop offset="0%" stop-color="#00656e" />
                      <stop offset="45%" stop-color="#087f8c" />
                      <stop offset="100%" stop-color="#023b42" />
                    </linearGradient>
                    <linearGradient id="backDarkGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                      <stop offset="0%" stop-color="#1e293b" />
                      <stop offset="100%" stop-color="#0a0f1d" />
                    </linearGradient>
                    <linearGradient id="backSilverGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                      <stop offset="0%" stop-color="#f8fafc" />
                      <stop offset="50%" stop-color="#cbd5e1" />
                      <stop offset="100%" stop-color="#94a3b8" />
                    </linearGradient>
                  </defs>

                  <!-- Card Base Surface -->
                  <rect width="280" height="445" fill="#ffffff" />

                  <!-- Top-Left Teal Geometric Diagonal -->
                  <path d="M0 0 H185 L0 98 Z" fill="url(#backTealGrad)" />
                  <!-- Top-Left Dark Accent Wedge -->
                  <path d="M0 0 H65 L0 92 Z" fill="url(#backDarkGrad)" />
                  <!-- Top-Left Silver Stripe -->
                  <polygon points="185,0 191,0 0,104 0,98" fill="url(#backSilverGrad)" />

                  <!-- Bottom-Right Teal Geometric Diagonal -->
                  <path d="M95 445 L280 348 V445 Z" fill="url(#backTealGrad)" />
                  <!-- Bottom-Right Dark Accent Wedge -->
                  <path d="M215 445 L280 353 V445 Z" fill="url(#backDarkGrad)" />
                  <!-- Bottom-Right Silver Stripe -->
                  <polygon points="95,445 89,445 280,342 280,348" fill="url(#backSilverGrad)" />
                </svg>

                <!-- Back Content Layer (Fallback built-in layout) -->
                <div id="dom-back-fallback-layout" class="<?php echo (!empty($id_card_design->has_back)) ? 'hidden' : ''; ?> relative z-10 h-full flex flex-col justify-between p-3.5 pt-5 pb-3">
                  
                  <!-- Top Branding Area -->
                  <div class="text-right pr-1 pt-1">
                    <div class="font-black text-[11px] text-teal-800 tracking-wider uppercase leading-tight">
                      <?php echo html_escape($settings->school_name ?? 'LOGIN2'); ?>
                    </div>
                    <div class="text-[8px] font-bold text-slate-500 tracking-widest uppercase">
                      <?php echo html_escape($settings->card_title ?? 'PUBLIC SCHOOL'); ?>
                    </div>
                  </div>

                  <!-- Terms and Conditions Block -->
                  <div class="px-2 space-y-1.5">
                    <div class="font-extrabold text-[10px] text-teal-800 tracking-wider uppercase flex items-center gap-1.5">
                      <span class="w-1.5 h-1.5 rounded-full bg-teal-600"></span>
                      <span>Terms and conditions</span>
                    </div>

                    <div class="space-y-1 text-[8.5px] text-slate-700 leading-snug font-medium pl-0.5">
                      <div class="flex items-start gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-teal-600/80 mt-1 shrink-0"></span>
                        <span>Students are required to carry this card while on campus.</span>
                      </div>
                      <div class="flex items-start gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-teal-600/80 mt-1 shrink-0"></span>
                        <span>If the card is lost or damaged, a duplicate will be issued per school regulations.</span>
                      </div>
                      <div class="flex items-start gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-teal-600/80 mt-1 shrink-0"></span>
                        <span>If you find this card, please return it to the school address below.</span>
                      </div>
                    </div>
                  </div>

                  <!-- Principal Signature Block -->
                  <div class="text-center pt-0.5">
                    <div class="h-7 flex items-center justify-center">
                      <img id="dom-back-sig-img" src="" alt="Signature" class="max-h-6 max-w-[100px] object-contain hidden"/>
                      <div id="dom-back-sig-script" class="font-serif italic text-sm text-slate-800 tracking-wider">
                        John
                      </div>
                    </div>
                    <div class="w-24 h-px bg-slate-300 mx-auto my-0.5"></div>
                    <div class="text-[8.5px] font-bold text-slate-500 uppercase tracking-wider">
                      Authorized Signature
                    </div>
                  </div>

                  <!-- Dates & Validity Pill Bar -->
                  <div class="flex items-center justify-between text-[8px] font-bold text-slate-700 px-2.5 py-1 bg-slate-50 rounded-lg border border-slate-200/60 mx-1">
                    <div>Issue Date : <span class="text-slate-900 font-extrabold font-mono">01/06/2026</span></div>
                    <div>Valid : <span class="text-slate-900 font-extrabold">Academic Session</span></div>
                  </div>

                  <!-- School Contact List (Icons + Text) -->
                  <div class="px-2 space-y-0.5 text-[8.5px] text-slate-800 font-semibold">
                    <div class="flex items-center gap-1.5">
                      <span class="material-symbols-outlined text-[13px] text-teal-700 shrink-0">call</span>
                      <span id="dom-back-phone-list" class="font-mono"><?php echo html_escape($settings->phone ?? '001 123 456 789'); ?></span>
                    </div>
                    <div class="flex items-center gap-1.5">
                      <span class="material-symbols-outlined text-[13px] text-teal-700 shrink-0">mail</span>
                      <span id="dom-back-email-list" class="truncate"><?php echo html_escape($settings->email ?? 'info@login2school.com'); ?></span>
                    </div>
                    <div class="flex items-center gap-1.5">
                      <span class="material-symbols-outlined text-[13px] text-teal-700 shrink-0">language</span>
                      <span id="dom-back-web-list" class="truncate"><?php echo html_escape($settings->website ?? 'www.login2school.com'); ?></span>
                    </div>
                    <div class="flex items-center gap-1.5">
                      <span class="material-symbols-outlined text-[13px] text-teal-700 shrink-0">location_on</span>
                      <span id="dom-back-address" class="truncate text-[8px]"><?php echo html_escape($settings->school_address ?? '100/1 Bryant Lane, Manor, Orla land, New York'); ?></span>
                    </div>
                  </div>

                  <!-- Return Notice Disclaimer (Bottom Text) -->
                  <div id="dom-back-return-text" class="text-[8px] font-bold text-teal-700 leading-tight text-center px-1">
                    If found, please return this card to the school.
                  </div>

                </div>

              </div>

              <!-- Right Dimension Indicator (3.375 inch / 86 mm) -->
              <div class="h-[445px] flex flex-col items-center justify-between text-[11px] font-semibold text-slate-600 py-1 select-none">
                <span class="text-slate-400">&bull;</span>
                <span class="writing-mode-vertical border-r border-dashed border-slate-400 h-full mx-1 flex items-center justify-center text-center pr-1 text-[10px]" style="writing-mode: vertical-rl; transform: rotate(180deg);">3.375 inch (86 mm)</span>
                <span class="text-slate-400">&bull;</span>
              </div>

            </div>

          </div>

        </div>

        <!-- Bottom Info Banner (CR80 Portrait Dimensions) -->
        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs font-semibold flex items-center justify-center gap-2 text-center">
          <span class="material-symbols-outlined text-[18px] text-amber-700">info</span>
          <span>Card Size: 2.125 &times; 3.375 inch (CR80 Portrait)</span>
        </div>

        <!-- Action Toolbar -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
          
          <div class="flex flex-wrap items-center gap-2.5">
            <!-- Print Button (Dark Green) -->
            <button type="button" onclick="printActiveCard()" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-primary text-white font-bold text-xs hover:bg-button-primary-hover transition-colors shadow-xs cursor-pointer">
              <span class="material-symbols-outlined text-[18px]">print</span>
              <span>Print Card</span>
            </button>

            <!-- Download PDF (Navy) -->
            <button type="button" onclick="downloadActivePdf()" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition-colors shadow-xs cursor-pointer">
              <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
              <span>Download PDF</span>
            </button>

            <!-- Download PNG (Outlined) -->
            <button type="button" onclick="downloadActiveImage('png')" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-50 transition-colors shadow-2xs cursor-pointer">
              <span class="material-symbols-outlined text-[18px] text-slate-500">image</span>
              <span>Download PNG</span>
            </button>

            <!-- Download JPG (Outlined) -->
            <button type="button" onclick="downloadActiveImage('jpeg')" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-50 transition-colors shadow-2xs cursor-pointer">
              <span class="material-symbols-outlined text-[18px] text-slate-500">photo</span>
              <span>Download JPG</span>
            </button>
          </div>

          <!-- Regenerate Card (Outlined Green) -->
          <button type="button" onclick="regenerateActiveCard()" title="Fetch latest student details from database and increment version" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-white border border-emerald-600 text-primary font-bold text-xs hover:bg-emerald-50 transition-colors shadow-2xs cursor-pointer">
            <span class="material-symbols-outlined text-[18px] text-emerald-700">sync</span>
            <span>Regenerate Card</span>
          </button>

        </div>

      </div>

    </div>

  </div>

</div>

<!-- =========================================================================
     MODAL 1: ID CARD SETTINGS CONFIGURATION
     ========================================================================= -->
<div id="settings-modal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
  <div class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[90vh]">
    
    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
      <div class="flex items-center gap-2.5">
        <span class="p-2 rounded-xl bg-primary text-white flex items-center justify-center shadow-xs">
          <span class="material-symbols-outlined text-[20px]">settings</span>
        </span>
        <div>
          <h3 class="font-bold text-base text-slate-900">ID Card Configuration Settings</h3>
          <p class="text-xs text-slate-500">Configure institution branding, principal signature, and return text.</p>
        </div>
      </div>
      <button type="button" onclick="closeSettingsModal()" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-200 transition-colors">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>

    <?php echo form_open_multipart('students/id_card_settings_save', array('id' => 'modal-settings-form', 'class' => 'overflow-y-auto p-6 space-y-4')); ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">School / Institution Name</label>
          <input type="text" name="school_name" value="<?php echo html_escape($settings->school_name ?? 'Login2'); ?>" placeholder="Login2" class="w-full px-3.5 py-2 text-sm rounded-lg border border-slate-300"/>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">School Motto / Subtitle</label>
          <input type="text" name="card_title" value="<?php echo html_escape($settings->card_title ?? 'PUBLIC SCHOOL'); ?>" placeholder="PUBLIC SCHOOL" class="w-full px-3.5 py-2 text-sm rounded-lg border border-slate-300"/>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Phone Number</label>
          <input type="text" name="phone" value="<?php echo html_escape($settings->phone ?? '001 123 456 789'); ?>" placeholder="001 123 456 789" class="w-full px-3.5 py-2 text-sm rounded-lg border border-slate-300"/>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
          <input type="text" name="email" value="<?php echo html_escape($settings->email ?? 'info@login2school.com'); ?>" placeholder="info@login2school.com" class="w-full px-3.5 py-2 text-sm rounded-lg border border-slate-300"/>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Website URL</label>
          <input type="text" name="website" value="<?php echo html_escape($settings->website ?? 'www.login2school.com'); ?>" placeholder="www.login2school.com" class="w-full px-3.5 py-2 text-sm rounded-lg border border-slate-300"/>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Emergency Contact Number</label>
          <input type="text" name="emergency_contact" value="<?php echo html_escape($settings->emergency_contact ?? '001 987 654 321'); ?>" placeholder="001 987 654 321" class="w-full px-3.5 py-2 text-sm rounded-lg border border-slate-300"/>
        </div>

        <div class="sm:col-span-2">
          <label class="block text-xs font-bold text-slate-700 mb-1">School Campus Address</label>
          <input type="text" name="school_address" value="<?php echo html_escape($settings->school_address ?? '100/1 Bryant Lane, Manor, Orla land, New York'); ?>" placeholder="100/1 Bryant Lane, Manor, Orla land, New York" class="w-full px-3.5 py-2 text-sm rounded-lg border border-slate-300"/>
        </div>

        <div class="sm:col-span-2">
          <label class="block text-xs font-bold text-slate-700 mb-1">Return Notice Text</label>
          <input type="text" name="return_text" value="<?php echo html_escape($settings->return_text ?? 'If found, please return this card to the school.'); ?>" placeholder="If found, please return this card to the school." class="w-full px-3.5 py-2 text-sm rounded-lg border border-slate-300"/>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">School Logo</label>
          <input type="file" name="school_logo" accept="image/*" class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300"/>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Principal Signature</label>
          <input type="file" name="principal_signature" accept="image/*" class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300"/>
        </div>

      </div>

      <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-2">
        <button type="button" onclick="closeSettingsModal()" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-100">Cancel</button>
        <button type="submit" class="px-5 py-2 rounded-lg bg-primary text-white text-xs font-bold hover:bg-button-primary-hover shadow-xs">Save Changes</button>
      </div>
    <?php echo form_close(); ?>

  </div>
</div>

<!-- =========================================================================
     MODAL 2: ID CARD GENERATION HISTORY
     ========================================================================= -->
<div id="history-modal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
  <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[90vh]">
    
    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
      <div class="flex items-center gap-2.5">
        <span class="p-2 rounded-xl bg-primary text-white flex items-center justify-center shadow-xs">
          <span class="material-symbols-outlined text-[20px]">history</span>
        </span>
        <div>
          <h3 class="font-bold text-base text-slate-900">ID Card Generation History</h3>
          <p class="text-xs text-slate-500">Audit trail of issued ID cards with direct reprint & regeneration.</p>
        </div>
      </div>
      <button type="button" onclick="closeHistoryModal()" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-200 transition-colors">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>
    </div>

    <div class="p-6 overflow-y-auto space-y-4">
      <table id="modal-history-datatable" class="w-full text-left text-xs">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-50 text-slate-700 font-bold uppercase">
            <th class="p-2.5">Card Number</th>
            <th class="p-2.5">Student</th>
            <th class="p-2.5">Class</th>
            <th class="p-2.5">Version</th>
            <th class="p-2.5">Generated Date</th>
            <th class="p-2.5">Status</th>
            <th class="p-2.5 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <!-- Populated via DataTables AJAX -->
        </tbody>
      </table>
    </div>

  </div>
</div>

<!-- Client JavaScript Logic -->
<script>
  let activeStudentId = <?php echo ($selected_student ? $selected_student->student_id : (empty($students) ? 0 : $students[0]->student_id)); ?>;
  let activeStudentData = null;
  let activeSettingsData = <?php echo json_encode($settings); ?>;
  let historyDtInstance = null;

  // Pagination state for left list
  let currentPage = 1;
  const pageSize = 5;

  document.addEventListener('DOMContentLoaded', () => {
    if (activeStudentId > 0) {
      loadCardData(activeStudentId);
    }
    initClientPagination();
  });

  // Toggle Single vs Bulk Mode
  function toggleSelectionMode(mode) {
    const singleWrap = document.getElementById('single-select-wrapper');
    const bulkWrap   = document.getElementById('bulk-select-wrapper');
    const singleBtn  = document.getElementById('tab-single-btn');
    const bulkBtn    = document.getElementById('tab-bulk-btn');

    if (mode === 'single') {
      singleWrap.classList.remove('hidden');
      bulkWrap.classList.add('hidden');
      singleBtn.className = "py-2 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5 bg-white text-primary shadow-xs";
      bulkBtn.className   = "py-2 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5 text-slate-600 hover:text-slate-900";
    } else {
      singleWrap.classList.add('hidden');
      bulkWrap.classList.remove('hidden');
      bulkBtn.className   = "py-2 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5 bg-white text-primary shadow-xs";
      singleBtn.className = "py-2 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5 text-slate-600 hover:text-slate-900";
    }
  }

  // Filter Card View (Front / Back / Both)
  function filterCardView(view) {
    const frontStage = document.getElementById('front-card-stage-wrapper');
    const backStage  = document.getElementById('back-card-stage-wrapper');
    const frontBtn   = document.getElementById('btn-toggle-front');
    const backBtn    = document.getElementById('btn-toggle-back');
    const bothBtn    = document.getElementById('btn-toggle-both');

    const activeBtnClass   = "px-4 py-1.5 rounded-lg text-xs font-bold transition-all bg-primary text-white shadow-xs";
    const inactiveBtnClass = "px-4 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-600 hover:text-slate-900";

    if (view === 'front') {
      frontStage.classList.remove('hidden');
      backStage.classList.add('hidden');
      frontBtn.className = activeBtnClass;
      backBtn.className  = inactiveBtnClass;
      if (bothBtn) bothBtn.className = inactiveBtnClass;
    } else if (view === 'back') {
      frontStage.classList.add('hidden');
      backStage.classList.remove('hidden');
      frontBtn.className = inactiveBtnClass;
      backBtn.className  = activeBtnClass;
      if (bothBtn) bothBtn.className = inactiveBtnClass;
    } else {
      frontStage.classList.remove('hidden');
      backStage.classList.remove('hidden');
      frontBtn.className = inactiveBtnClass;
      backBtn.className  = inactiveBtnClass;
      if (bothBtn) bothBtn.className = activeBtnClass;
    }
  }

  // Select a student from list
  function selectStudentCard(studentId, el) {
    activeStudentId = studentId;

    // Reset and set active card style
    document.querySelectorAll('.student-select-card').forEach(card => {
      card.className = "student-select-card p-3 rounded-xl border border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/60 transition-all cursor-pointer flex items-center gap-3.5";
      const radio = card.querySelector('.radio-indicator');
      const dot = card.querySelector('.radio-dot');
      if (radio) radio.className = "radio-indicator w-4 h-4 rounded-full border-2 border-slate-300 bg-white flex items-center justify-center shrink-0";
      if (dot) dot.classList.add('hidden');
    });

    if (el) {
      el.className = "student-select-card p-3 rounded-xl border border-emerald-600 bg-emerald-50/40 ring-1 ring-emerald-600 shadow-2xs transition-all cursor-pointer flex items-center gap-3.5";
      const activeRadio = el.querySelector('.radio-indicator');
      const activeDot = el.querySelector('.radio-dot');
      if (activeRadio) activeRadio.className = "radio-indicator w-4 h-4 rounded-full border-2 border-emerald-600 bg-white flex items-center justify-center shrink-0";
      if (activeDot) activeDot.classList.remove('hidden');
    }

    loadCardData(studentId);
  }

  // Fetch Student data via AJAX
  function loadCardData(studentId) {
    $.ajax({
      url: window.APP_BASE_URL + 'students/id_card_preview_ajax',
      type: 'GET',
      data: { student_id: studentId },
      dataType: 'json',
      success: function (res) {
        if (res.status && res.student) {
          activeStudentData = res.student;
          activeSettingsData = res.settings;
          populateCardDOM(res);
        }
      }
    });
  }

  // Populate Card Preview DOM
  function populateCardDOM(data) {
    const st  = data.student;
    const set = data.settings;

    // Front Side (Fallback Elements)
    $('#dom-school-subtitle').text(set.card_title || 'PUBLIC SCHOOL');
    $('#dom-student-name').text(data.full_name || 'STUDENT NAME');
    $('#dom-student-adm').text(st.admission_number || 'EDU2026015');
    $('#dom-student-father').text(st.guardian_name || 'test');
    $('#dom-student-class').text(st.class_name || 'Grade 10');
    $('#dom-student-division, #dom-student-section').text(st.division_name || st.section_name || 'A');
    
    if (st.roll_number) {
      $('#dom-student-roll').text(st.roll_number);
      $('#row-roll-no').removeClass('hidden');
    } else {
      $('#row-roll-no').addClass('hidden');
    }

    $('#dom-student-dob').text(data.dob_formatted || '15-06-2012');
    $('#dom-student-blood').text(st.blood_group || 'A+');

    // Photo (Fallback)
    if (data.has_photo && data.photo_url) {
      $('#dom-student-photo').attr('src', data.photo_url).removeClass('hidden');
      $('#dom-student-photo-fallback').addClass('hidden');
    } else {
      $('#dom-student-photo').addClass('hidden');
      $('#dom-student-photo-fallback').text(data.initials || 'ST').removeClass('hidden');
    }

    // Logo (Fallback)
    if (data.logo_url) {
      $('#dom-school-logo').attr('src', data.logo_url).removeClass('hidden');
    } else {
      $('#dom-school-logo').attr('src', window.APP_BASE_URL + 'assets/logo.png').removeClass('hidden');
    }

    // Version
    const ver = (st.card_history && st.card_history.card_version) ? st.card_history.card_version : 1;
    $('#preview-ver-pill').text('v' + ver);

    // Back Side (Fallback)
    $('#dom-back-main-phone').text(set.phone || '001 123 456 789');
    $('#dom-back-address').html(escapeAddressHtml(set.school_address || '100/1 Bryant Lane, Manor, Orla land, New York'));
    $('#dom-back-phone-list').text(set.phone || '001 123 456 789');
    $('#dom-back-email-list').text(set.email || 'info@login2school.com');
    $('#dom-back-web-list').text(set.website || 'www.login2school.com');
    $('#dom-back-emergency-list').text(set.emergency_contact || '001 987 654 321');
    $('#dom-back-return-text').html(escapeAddressHtml(set.return_text || 'If found, please return this card to the school.'));

    if (data.signature_url) {
      $('#dom-back-sig-img').attr('src', data.signature_url).removeClass('hidden');
      $('#dom-back-sig-script').addClass('hidden');
    } else {
      $('#dom-back-sig-img').addClass('hidden');
      $('#dom-back-sig-script').removeClass('hidden');
    }

    // Handle Custom Design vs Fallback Layout
    const des = data.id_card_design;
    const hasCustomFront = (des && des.has_front && des.front_url);
    const hasCustomBack  = (des && des.has_back && des.back_url);

    if (hasCustomFront) {
      $('#dom-front-bg').attr('src', des.front_url).removeClass('hidden');
      $('#dom-front-svg').addClass('hidden');
      $('#dom-front-fallback-layout').addClass('hidden');
      $('#dom-front-custom-overlay').removeClass('hidden');
      updateCustomOverlayValues(data, des.field_config);
    } else {
      $('#dom-front-bg').addClass('hidden');
      $('#dom-front-svg').removeClass('hidden');
      $('#dom-front-fallback-layout').removeClass('hidden');
      $('#dom-front-custom-overlay').addClass('hidden');
    }

    if (hasCustomBack) {
      $('#dom-back-bg').attr('src', des.back_url).removeClass('hidden');
      $('#dom-back-svg').addClass('hidden');
      $('#dom-back-fallback-layout').addClass('hidden');
    } else {
      $('#dom-back-bg').addClass('hidden');
      $('#dom-back-svg').removeClass('hidden');
      $('#dom-back-fallback-layout').removeClass('hidden');
    }
  }

  // Populate dynamic student data into custom overlay
  function updateCustomOverlayValues(data, fc) {
    const st = data.student;

    if (fc) {
      applyFieldStylesToDOM(fc);
    }

    // Photo
    if (data.has_photo && data.photo_url) {
      $('#dom-df-photo-img').attr('src', data.photo_url).removeClass('hidden');
      $('#dom-df-photo-initials').addClass('hidden');
    } else {
      $('#dom-df-photo-img').addClass('hidden');
      $('#dom-df-photo-initials').text(data.initials || 'ST').removeClass('hidden');
    }

    // Name
    $('#dom-df-student_name-val').text(data.full_name || 'STUDENT NAME');

    // Structured Details Table Fields
    // Admission Number / ID
    $('#df-val-admission_number').text(st.admission_number || '—');

    // Father / Guardian Name
    $('#df-val-guardian_name').text(st.guardian_name || '—');

    // Class & Division
    const cName = st.class_name || '';
    const dName = st.division_name || st.section_name || '';
    let classDiv = (cName + (dName ? ' - ' + dName : '')).trim();
    if (!classDiv) {
      classDiv = (data.class_display || '').trim();
    }
    $('#df-val-class_division').text(classDiv || '—');

    // Roll Number
    $('#df-val-roll_number').text(st.roll_number || '—');

    // DOB
    const dobFormatted = (data.dob_formatted && data.dob_formatted !== 'N/A') ? data.dob_formatted : (st.date_of_birth && st.date_of_birth !== '0000-00-00' ? st.date_of_birth : '—');
    $('#df-val-date_of_birth').text(dobFormatted);

    // Blood Group
    const bloodVal = (st.blood_group && st.blood_group !== 'N/A') ? st.blood_group : '—';
    const bloodEl = $('#df-val-blood_group');
    bloodEl.text(bloodVal);
    if (bloodVal !== '—') {
      bloodEl.css('color', '#e11d48');
    }
  }

  // Apply field coordinates and styles dynamically
  function applyFieldStylesToDOM(fc) {
    if (!fc) return;

    // 1. Photo
    if (fc.photo) {
      const pEl = document.getElementById('df-front-photo');
      if (pEl) {
        pEl.style.display = (fc.photo.enabled !== false) ? '' : 'none';
        if (fc.photo.top !== undefined) pEl.style.top = fc.photo.top + '%';
        if (fc.photo.left !== undefined) pEl.style.left = fc.photo.left + '%';
        if (fc.photo.width !== undefined) pEl.style.width = fc.photo.width + '%';
        if (fc.photo.height !== undefined) pEl.style.height = fc.photo.height + '%';
        if (fc.photo.radius) pEl.style.borderRadius = fc.photo.radius;
        if (fc.photo.border) pEl.style.border = fc.photo.border;
      }
    }

    // 2. Student Name
    if (fc.student_name) {
      const nEl = document.getElementById('df-front-student_name');
      if (nEl) {
        nEl.style.display = (fc.student_name.enabled !== false) ? '' : 'none';
        if (fc.student_name.top !== undefined) nEl.style.top = fc.student_name.top + '%';
        if (fc.student_name.left !== undefined) nEl.style.left = fc.student_name.left + '%';
        if (fc.student_name.width !== undefined) nEl.style.width = fc.student_name.width + '%';
        if (fc.student_name.font_size) nEl.style.fontSize = fc.student_name.font_size;
        if (fc.student_name.color) nEl.style.color = fc.student_name.color;
        if (fc.student_name.align) nEl.style.textAlign = fc.student_name.align;
        if (fc.student_name.weight) nEl.style.fontWeight = fc.student_name.weight;
      }
    }

    // 3. Student Details Table Block
    const sdt = fc.student_details;
    const dEl = document.getElementById('df-front-student_details');
    if (dEl && sdt) {
      dEl.style.display = (sdt.enabled !== false) ? '' : 'none';
      if (sdt.top !== undefined) dEl.style.top = sdt.top + '%';
      if (sdt.left !== undefined) dEl.style.left = sdt.left + '%';
      if (sdt.width !== undefined) dEl.style.width = sdt.width + '%';

      const tbl = dEl.querySelector('.student-details-table');
      if (tbl) {
        if (sdt.font_size) tbl.style.fontSize = sdt.font_size;
        if (sdt.line_height) tbl.style.lineHeight = sdt.line_height;
      }
      if (sdt.label_width) {
        $(dEl).find('.df-lbl-col').css('width', sdt.label_width);
      }
      if (sdt.label_color) {
        $(dEl).find('.df-lbl-col').css('color', sdt.label_color);
      }
      if (sdt.value_color) {
        $(dEl).find('.df-val-col').not('.df-val-blood_group').css('color', sdt.value_color);
      }
    }

    // 4. Sub-row visibility and custom labels
    const rowKeys = ['admission_number', 'guardian_name', 'class_division', 'roll_number', 'date_of_birth', 'blood_group'];
    rowKeys.forEach(function (key) {
      if (fc[key]) {
        const rowEl = document.getElementById('df-row-' + key);
        if (rowEl) {
          rowEl.style.display = (fc[key].enabled !== false) ? '' : 'none';
        }
        const lblEl = document.getElementById('df-lbl-' + key);
        if (lblEl && fc[key].label !== undefined) {
          lblEl.textContent = fc[key].label;
        }
      }
    });
  }

  function escapeAddressHtml(str) {
    if (!str) return '';
    const safe = $('<div>').text(str).html();
    return safe.replace(/,\s*/g, '<br>');
  }

  // Filter left list
  function filterStudentCards() {
    const classVal  = $('#left-class-filter').val();
    const searchVal = $('#left-search-input').val().toLowerCase().trim();

    let visibleCards = [];
    $('.student-select-card').each(function () {
      const card = $(this);
      const cardClass = card.attr('data-class');
      const cardName  = card.attr('data-name');
      const cardAdm   = card.attr('data-adm');
      const cardRoll  = card.attr('data-roll');

      let match = true;
      if (classVal && cardClass !== classVal) match = false;
      if (searchVal) {
        if (!cardName.includes(searchVal) && !cardAdm.includes(searchVal) && !cardRoll.includes(searchVal)) {
          match = false;
        }
      }

      if (match) {
        visibleCards.push(card);
      } else {
        card.addClass('hidden');
      }
    });

    $('#filtered-count-badge').text(visibleCards.length + ' found');
    if (resetPage) {
      currentPage = 1;
    }
    renderPagedCards(visibleCards);
  }

  // Client-side pagination
  function initClientPagination() {
    if (activeStudentId > 0) {
      const allCards = Array.from(document.querySelectorAll('.student-select-card'));
      const activeIdx = allCards.findIndex(c => parseInt(c.dataset.studentId, 10) === activeStudentId);
      if (activeIdx >= 0) {
        currentPage = Math.floor(activeIdx / pageSize) + 1;
      }
    }
    filterStudentCards(false);
  }

  function renderPagedCards(cardsList) {
    const cards = cardsList || Array.from(document.querySelectorAll('.student-select-card:not(.filtered-out)'));
    const totalPages = Math.ceil(cards.length / pageSize) || 1;

    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    cards.forEach((card, idx) => {
      const el = card.jquery ? card[0] : card;
      const startIdx = (currentPage - 1) * pageSize;
      const endIdx   = startIdx + pageSize;
      if (idx >= startIdx && idx < endIdx) {
        el.classList.remove('hidden');
      } else {
        el.classList.add('hidden');
      }
    });

    // Render Pills
    const pillContainer = document.getElementById('pagination-pills');
    if (pillContainer) {
      let html = '';
      for (let i = 1; i <= Math.min(totalPages, 5); i++) {
        const isActive = (i === currentPage);
        html += `<button type="button" onclick="goToPage(${i})" class="w-6 h-6 rounded flex items-center justify-center ${isActive ? 'bg-primary text-white' : 'text-slate-600 hover:bg-slate-100'}">${i}</button>`;
      }
      pillContainer.innerHTML = html;
    }

    $('#prev-page-btn').prop('disabled', currentPage === 1).toggleClass('opacity-50', currentPage === 1);
    $('#next-page-btn').prop('disabled', currentPage === totalPages).toggleClass('opacity-50', currentPage === totalPages);
  }

  function goToPage(page) {
    currentPage = page;
    filterStudentCards();
  }

  function paginateStudents(direction) {
    currentPage += direction;
    filterStudentCards();
  }

  // =========================================================================
  // ACTIONS: PRINT, PDF, PNG, JPG, REGENERATE
  // =========================================================================
  function printActiveCard() {
    if (!activeStudentId) return;
    recordEvent(activeStudentId, 'Printed');
    const printUrl = window.APP_BASE_URL + 'students/id_card_print?student_id=' + activeStudentId + '&autoprint=1';
    window.open(printUrl, '_blank', 'width=900,height=750');
  }

  async function downloadActivePdf() {
    if (!activeStudentId) return;
    const { jsPDF } = window.jspdf;

    // Standard Portrait CR80 (54mm x 86mm / 2.125" x 3.375")
    const pdf = new jsPDF({
      orientation: 'portrait',
      unit: 'mm',
      format: [54, 86]
    });

    // 1. Render Front
    const frontEl = document.getElementById('portrait-cr80-front');
    const frontCanvas = await html2canvas(frontEl, { scale: 3.5, useCORS: true, logging: false });
    pdf.addImage(frontCanvas.toDataURL('image/jpeg', 0.95), 'JPEG', 0, 0, 54, 86);

    // 2. Render Back
    pdf.addPage([54, 86], 'portrait');
    const backEl = document.getElementById('portrait-cr80-back');
    const backCanvas = await html2canvas(backEl, { scale: 3.5, useCORS: true, logging: false });
    pdf.addImage(backCanvas.toDataURL('image/jpeg', 0.95), 'JPEG', 0, 0, 54, 86);

    const adm = $('#dom-student-adm').text() || 'student';
    pdf.save('ID_Card_' + adm + '.pdf');

    recordEvent(activeStudentId, 'Generated');
  }

  async function downloadActiveImage(format) {
    if (!activeStudentId) return;

    // Export both Front and Back or active face
    const frontEl = document.getElementById('portrait-cr80-front');
    const frontCanvas = await html2canvas(frontEl, { scale: 3.5, useCORS: true, logging: false });

    const mime = (format === 'png') ? 'image/png' : 'image/jpeg';
    const ext  = (format === 'png') ? 'png' : 'jpg';
    const adm = $('#dom-student-adm').text() || 'student';

    // Front image download
    const linkFront = document.createElement('a');
    linkFront.download = 'ID_Card_' + adm + '_front.' + ext;
    linkFront.href = frontCanvas.toDataURL(mime, 0.95);
    linkFront.click();

    // Back image download
    setTimeout(async () => {
      const backEl = document.getElementById('portrait-cr80-back');
      const backCanvas = await html2canvas(backEl, { scale: 3.5, useCORS: true, logging: false });
      const linkBack = document.createElement('a');
      linkBack.download = 'ID_Card_' + adm + '_back.' + ext;
      linkBack.href = backCanvas.toDataURL(mime, 0.95);
      linkBack.click();
    }, 400);

    recordEvent(activeStudentId, 'Generated');
  }

  function regenerateActiveCard() {
    if (!activeStudentId) return;

    $.ajax({
      url: window.APP_BASE_URL + 'students/id_card_regenerate_ajax',
      type: 'POST',
      data: {
        student_id: activeStudentId,
        [window.CSRF_TOKEN_NAME]: window.CSRF_HASH
      },
      dataType: 'json',
      success: function (res) {
        if (res.csrf_hash) window.CSRF_HASH = res.csrf_hash;
        if (res.status) {
          loadCardData(activeStudentId);
          if (historyDtInstance) {
            historyDtInstance.ajax.reload(null, false);
          }
        }
      }
    });
  }

  function recordEvent(studentId, action) {
    $.ajax({
      url: window.APP_BASE_URL + 'students/id_card_record_ajax',
      type: 'POST',
      data: {
        student_id: studentId,
        action: action,
        [window.CSRF_TOKEN_NAME]: window.CSRF_HASH
      },
      dataType: 'json',
      success: function (res) {
        if (res.csrf_hash) window.CSRF_HASH = res.csrf_hash;
      }
    });
  }

  // =========================================================================
  // BULK ACTIONS
  // =========================================================================
  function updateBulkCount() {
    const cnt = $('.bulk-cb:checked').length;
    $('#bulk-checked-count').text(cnt);
  }

  function toggleAllBulkCbs(master) {
    $('.bulk-cb').prop('checked', master.checked);
    updateBulkCount();
  }

  function executeBulkPrint() {
    const ids = [];
    $('.bulk-cb:checked').each(function () { ids.push($(this).val()); });

    if (ids.length === 0) {
      alert('Please check at least one student for bulk print.');
      return;
    }

    const printUrl = window.APP_BASE_URL + 'students/id_card_print?student_ids=' + ids.join(',') + '&autoprint=1';
    window.open(printUrl, '_blank', 'width=950,height=750');
  }

  function executeBulkPdf() {
    const ids = [];
    $('.bulk-cb:checked').each(function () { ids.push($(this).val()); });

    if (ids.length === 0) {
      alert('Please check at least one student for bulk PDF export.');
      return;
    }

    const printUrl = window.APP_BASE_URL + 'students/id_card_print?student_ids=' + ids.join(',');
    window.open(printUrl, '_blank', 'width=950,height=750');
  }

  // =========================================================================
  // MODALS: SETTINGS & HISTORY
  // =========================================================================
  function openSettingsModal() {
    document.getElementById('settings-modal').classList.remove('hidden');
  }

  function closeSettingsModal() {
    document.getElementById('settings-modal').classList.add('hidden');
  }

  function openHistoryModal() {
    document.getElementById('history-modal').classList.remove('hidden');
    initHistoryDataTable();
  }

  function closeHistoryModal() {
    document.getElementById('history-modal').classList.add('hidden');
  }

  function initHistoryDataTable() {
    if ($.fn.DataTable.isDataTable('#modal-history-datatable')) {
      historyDtInstance.ajax.reload(null, false);
      return;
    }

    historyDtInstance = $('#modal-history-datatable').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: window.APP_BASE_URL + 'students/id_card_history_ajax',
        type: 'POST',
        data: function (d) {
          d[window.CSRF_TOKEN_NAME] = window.CSRF_HASH;
        },
        dataSrc: function (json) {
          if (json.csrf_hash) window.CSRF_HASH = json.csrf_hash;
          return json.data;
        }
      },
      pageLength: 10,
      order: [[4, 'desc']],
      columns: [
        { data: 0, orderable: true },
        { data: 1, orderable: true },
        { data: 2, orderable: false },
        { data: 3, orderable: true },
        { data: 4, orderable: true },
        { data: 5, orderable: false },
        { data: 6, orderable: false, className: 'text-right' }
      ]
    });
  }

  function previewHistoryCard(studentId) {
    closeHistoryModal();
    const el = document.querySelector(`.student-select-card[data-student-id="${studentId}"]`);
    selectStudentCard(studentId, el);
  }

  function printSingleCard(studentId) {
    const printUrl = window.APP_BASE_URL + 'students/id_card_print?student_id=' + studentId + '&autoprint=1';
    window.open(printUrl, '_blank', 'width=900,height=750');
  }

  function downloadSinglePdf(studentId) {
    activeStudentId = studentId;
    loadCardData(studentId);
    setTimeout(() => { downloadActivePdf(); }, 400);
  }

  function downloadSingleImage(studentId, format) {
    if (typeof studentId === 'string') {
      downloadActiveImage(studentId);
      return;
    }
    activeStudentId = studentId;
    loadCardData(studentId);
    setTimeout(() => { downloadActiveImage(format || 'png'); }, 400);
  }

  function regenerateCard(studentId) {
    activeStudentId = studentId;
    regenerateActiveCard();
  }
</script>
