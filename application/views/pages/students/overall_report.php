<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
  $fullName = ($student) ? school_student_name($student->first_name, $student->middle_name ?? '', $student->last_name) : 'Student Report';
  $divName = !empty($student->division_name) ? $student->division_name : (!empty($student->section_name) ? $student->section_name : '—');
  $className = trim(($student->class_name ?? '') . ' ' . $divName);
  $groupName = !empty($student->academic_group_name) ? $student->academic_group_name : (!empty($student->group_name) ? $student->group_name : '—');
  $academicYearName = !empty($academic_year->year_name) ? $academic_year->year_name : (isset($student->year_name) && $student->year_name !== '' ? $student->year_name : ($active_academic_year->year_name ?? '—'));

  $nameParts = explode(' ', trim($fullName));
  $initials = '';
  foreach ($nameParts as $np) { if (!empty($np)) $initials .= strtoupper($np[0]); }
  if (strlen($initials) > 2) $initials = substr($initials, 0, 2);

  $status = (isset($student->status) && $student->status == 1) ? 'Active' : 'Inactive / Transferred';
  $is_ss = !empty($is_ss);
?>

<div class="max-w-6xl mx-auto pb-12">

  <!-- Top Action Bar -->
  <div class="no-print print:hidden flex flex-col sm:flex-row items-center justify-between gap-4 mb-6 bg-surface-container-lowest p-4 rounded-xl border border-outline-variant/50 shadow-sm">
    <div class="flex items-center gap-2">
      <a href="<?php echo site_url('students/profile/' . (int)$student->student_id); ?>" data-testid="btn-back-profile" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors text-label-md font-medium">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span>Back to Student Profile
      </a>
    </div>
    <div class="flex items-center gap-3">
      <span class="text-xs text-on-surface-variant hidden md:inline-block">Academic Year: <strong class="text-on-surface"><?php echo html_escape($academicYearName); ?></strong></span>
      <a href="<?php echo site_url('students/overall_report_pdf/' . (int)$student->student_id); ?>" data-testid="btn-download-pdf" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary/90 transition-all shadow-sm">
        <span class="material-symbols-outlined text-[20px]">download</span>Download PDF
      </a>
    </div>
  </div>

  <!-- Document Sheet Preview Container -->
  <div class="bg-white rounded-2xl border border-outline-variant/60 shadow-lg overflow-hidden print:shadow-none print:border-none" id="report-sheet">

    <!-- 1. Report Header -->
    <div class="p-6 sm:p-8 border-b border-outline-variant/40 bg-[#F7F7F7]">
      <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-4 text-center sm:text-left">
          <?php if (!empty($school_logo_src)): ?>
            <img src="<?php echo $school_logo_src; ?>" alt="School Logo" class="h-16 w-auto object-contain shrink-0"/>
          <?php else: ?>
            <div class="w-16 h-16 rounded-xl bg-[#258CC9] text-white flex items-center justify-center font-bold text-2xl shrink-0 shadow-sm">
              L2
            </div>
          <?php endif; ?>
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-on-surface tracking-tight" data-testid="school-name"><?php echo html_escape($school_name); ?></h1>
            <p class="text-sm text-on-surface-variant mt-0.5 flex items-center justify-center sm:justify-start gap-1" data-testid="school-address">
              <span class="material-symbols-outlined text-[16px] text-primary">location_on</span>
              <?php echo html_escape($school_address); ?>
            </p>
            <?php if (!empty($school_contact)): ?>
              <p class="text-xs text-on-surface-variant/80 mt-1" data-testid="school-contact"><?php echo html_escape($school_contact); ?></p>
            <?php endif; ?>
          </div>
        </div>
        <div class="text-center sm:text-right shrink-0 border-t sm:border-t-0 pt-3 sm:pt-0 border-outline-variant/40">
          <div class="inline-block px-3 py-1 rounded-full bg-primary/10 text-primary font-bold text-xs uppercase tracking-wider mb-1.5">
            Student Overall Report
          </div>
          <div class="text-xs text-on-surface-variant">Session: <span class="font-semibold text-on-surface"><?php echo html_escape($academicYearName); ?></span></div>
          <div class="text-[11px] text-on-surface-variant/70 mt-1" data-testid="report-generated-at">Generated: <?php echo html_escape($generated_at); ?> IST</div>
        </div>
      </div>
    </div>

    <!-- 2. Student & Academic Information Card -->
    <div class="p-6 sm:p-8 border-b border-outline-variant/40">
      <div class="flex flex-col md:flex-row items-start gap-6">
        <!-- Student Photo -->
        <div class="shrink-0 flex flex-col items-center mx-auto md:mx-0">
          <?php if (!empty($student_photo_src)): ?>
            <img src="<?php echo $student_photo_src; ?>" alt="<?php echo html_escape($fullName); ?>" class="w-28 h-28 rounded-xl object-cover border-2 border-outline-variant shadow-sm" data-testid="student-photo"/>
          <?php elseif (!empty($student->photo) && file_exists(FCPATH . 'uploads/students/' . $student->photo)): ?>
            <img src="<?php echo base_url('uploads/students/' . $student->photo); ?>" alt="<?php echo html_escape($fullName); ?>" class="w-28 h-28 rounded-xl object-cover border-2 border-outline-variant shadow-sm" data-testid="student-photo"/>
          <?php else: ?>
            <div class="w-28 h-28 rounded-xl bg-primary text-white flex items-center justify-center text-3xl font-bold border-2 border-outline-variant/50 shadow-sm" data-testid="student-photo-placeholder">
              <?php echo html_escape($initials); ?>
            </div>
          <?php endif; ?>
          <span class="mt-2 text-[11px] font-semibold px-2.5 py-0.5 rounded-full <?php echo ($status === 'Active') ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container-high text-on-surface-variant'; ?>">
            <?php echo $status; ?>
          </span>
        </div>

        <!-- Student Basic & Admission Details Grid -->
        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-y-3.5 gap-x-6 w-full text-body-md">
          <div class="sm:col-span-2 lg:col-span-3 pb-2 border-b border-outline-variant/30 flex items-center justify-between">
            <h2 class="text-xl font-bold text-on-surface" data-testid="student-full-name"><?php echo html_escape($fullName); ?></h2>
            <span class="text-xs font-mono bg-surface-container-low px-2.5 py-1 rounded text-on-surface-variant font-medium">Adm: <strong class="text-on-surface" data-testid="student-adm-no"><?php echo html_escape($student->admission_number ?? '—'); ?></strong></span>
          </div>

          <div>
            <div class="text-[11px] uppercase tracking-wide text-on-surface-variant font-medium">Admission Date</div>
            <div class="font-medium text-on-surface mt-0.5"><?php echo !empty($student->created_at) ? date('d M Y', strtotime($student->created_at)) : '—'; ?></div>
          </div>
          <div>
            <div class="text-[11px] uppercase tracking-wide text-on-surface-variant font-medium">Roll Number</div>
            <div class="font-medium text-on-surface mt-0.5" data-testid="student-roll-no"><?php echo html_escape($student->roll_number ?: '—'); ?></div>
          </div>
          <div>
            <div class="text-[11px] uppercase tracking-wide text-on-surface-variant font-medium">Class & Division</div>
            <div class="font-medium text-on-surface mt-0.5" data-testid="student-class-division"><?php echo html_escape($className ?: '—'); ?></div>
          </div>

          <div>
            <div class="text-[11px] uppercase tracking-wide text-on-surface-variant font-medium">Group / Section</div>
            <div class="font-medium text-on-surface mt-0.5" data-testid="student-group-name"><?php echo html_escape($groupName ?: '—'); ?></div>
          </div>
          <div>
            <div class="text-[11px] uppercase tracking-wide text-on-surface-variant font-medium">Gender</div>
            <div class="font-medium text-on-surface mt-0.5"><?php echo html_escape($student->gender ?: '—'); ?></div>
          </div>
          <div>
            <div class="text-[11px] uppercase tracking-wide text-on-surface-variant font-medium">Date of Birth</div>
            <div class="font-medium text-on-surface mt-0.5"><?php echo !empty($student->date_of_birth) ? date('d M Y', strtotime($student->date_of_birth)) : '—'; ?></div>
          </div>

          <div>
            <div class="text-[11px] uppercase tracking-wide text-on-surface-variant font-medium">Blood Group</div>
            <div class="font-medium text-on-surface mt-0.5"><?php echo html_escape($student->blood_group ?: '—'); ?></div>
          </div>
          <div>
            <div class="text-[11px] uppercase tracking-wide text-on-surface-variant font-medium">Student Phone</div>
            <div class="font-medium text-on-surface mt-0.5"><?php echo html_escape($student->student_phone ?: '—'); ?></div>
          </div>
          <div>
            <div class="text-[11px] uppercase tracking-wide text-on-surface-variant font-medium">Student Email</div>
            <div class="font-medium text-on-surface mt-0.5 truncate"><?php echo html_escape($student->student_email ?: '—'); ?></div>
          </div>

          <?php if (!empty($student->address)): ?>
            <div class="sm:col-span-2 lg:col-span-3">
              <div class="text-[11px] uppercase tracking-wide text-on-surface-variant font-medium">Residential Address</div>
              <div class="font-medium text-on-surface mt-0.5"><?php echo html_escape($student->address); ?></div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- 3. Parent / Guardian Details Card -->
    <div class="p-6 sm:p-8 border-b border-outline-variant/40 bg-surface-container-lowest">
      <h3 class="text-sm font-bold uppercase tracking-wider text-primary mb-4 flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">family_restroom</span>Parent / Guardian Information
      </h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-body-md">
        <div>
          <div class="text-[11px] uppercase tracking-wide text-on-surface-variant font-medium">Guardian Name</div>
          <div class="font-medium text-on-surface mt-0.5"><?php echo html_escape($student->guardian_name ?: '—'); ?></div>
        </div>
        <div>
          <div class="text-[11px] uppercase tracking-wide text-on-surface-variant font-medium">Relationship</div>
          <div class="font-medium text-on-surface mt-0.5"><?php echo html_escape($student->guardian_relation ?: '—'); ?></div>
        </div>
        <div>
          <div class="text-[11px] uppercase tracking-wide text-on-surface-variant font-medium">Guardian Phone</div>
          <div class="font-medium text-on-surface mt-0.5"><?php echo html_escape($student->guardian_phone ?: ($student->father_phone ?: ($student->mother_phone ?: '—'))); ?></div>
        </div>
        <div>
          <div class="text-[11px] uppercase tracking-wide text-on-surface-variant font-medium">Guardian Email</div>
          <div class="font-medium text-on-surface mt-0.5 truncate"><?php echo html_escape($student->guardian_email ?: '—'); ?></div>
        </div>
      </div>
    </div>

    <!-- 4. Examination Results -->
    <div class="p-6 sm:p-8 border-b border-outline-variant/40" data-testid="section-exam-results">
      <div class="flex items-center justify-between mb-5">
        <h3 class="text-base font-bold text-on-surface flex items-center gap-2">
          <span class="material-symbols-outlined text-primary text-[22px]">assignment</span>Academic Examination Results
        </h3>
        <span class="text-xs text-on-surface-variant">Academic Year: <strong><?php echo html_escape($academicYearName); ?></strong></span>
      </div>

      <?php if (empty($exam_reports)): ?>
        <div class="p-6 rounded-xl bg-surface-container-low text-center text-on-surface-variant border border-outline-variant/40" data-testid="no-exams-notice">
          <span class="material-symbols-outlined text-[32px] text-on-surface-variant/60 block mb-1">quiz</span>
          <p class="font-medium">No examination results available for this academic year.</p>
        </div>
      <?php else: ?>
        <div class="space-y-6">
          <?php foreach ($exam_reports as $ex): ?>
            <div class="rounded-xl border border-outline-variant/60 overflow-hidden shadow-xs" data-testid="exam-card-<?php echo (int)$ex->exam_id; ?>">
              <!-- Exam Subheader -->
              <div class="bg-surface-container-low px-4 py-3 border-b border-outline-variant/50 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                  <h4 class="font-bold text-on-surface text-sm sm:text-base"><?php echo html_escape($ex->exam_name); ?></h4>
                  <?php if (!empty($ex->exam_date)): ?>
                    <span class="text-[12px] text-on-surface-variant">Date: <?php echo date('d M Y', strtotime($ex->exam_date)); ?></span>
                  <?php endif; ?>
                </div>
                <div class="flex items-center gap-3 text-xs">
                  <?php if (!empty($ex->pass_status)): ?>
                    <span class="px-2.5 py-0.5 rounded-full font-semibold <?php echo ($ex->pass_status === 'Pass') ? 'bg-secondary-container text-on-secondary-container' : 'bg-error-container text-on-error-container'; ?>">
                      <?php echo html_escape($ex->pass_status); ?>
                    </span>
                  <?php endif; ?>
                  <?php if (!empty($ex->class_rank)): ?>
                    <span class="px-2 py-0.5 rounded bg-primary text-white font-bold">
                      Rank: #<?php echo (int)$ex->class_rank; ?>
                    </span>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Subject Marks Table -->
              <div class="overflow-x-auto">
                <table class="w-full text-body-sm text-left border-collapse">
                  <thead>
                    <tr class="bg-surface-container-lowest border-b border-outline-variant/40 text-[11px] uppercase tracking-wider text-on-surface-variant">
                      <th class="px-4 py-2.5 font-semibold">Subject</th>
                      <th class="px-4 py-2.5 font-semibold text-center">Marks Obtained</th>
                      <th class="px-4 py-2.5 font-semibold text-center">Maximum Marks</th>
                      <th class="px-4 py-2.5 font-semibold text-center">Grade</th>
                      <th class="px-4 py-2.5 font-semibold text-center">Result</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-outline-variant/30">
                    <?php if (empty($ex->subject_marks)): ?>
                      <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-on-surface-variant">No subject marks recorded for this exam.</td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($ex->subject_marks as $m): ?>
                        <tr class="hover:bg-surface-container-lowest/70">
                          <td class="px-4 py-2.5 font-medium text-on-surface"><?php echo html_escape($m->subject_name); ?></td>
                          <td class="px-4 py-2.5 text-center font-mono font-medium">
                            <?php
                              if (!empty($m->is_absent)) {
                                echo '<span class="text-error font-bold">ABS</span>';
                              } elseif (!empty($m->is_exempted)) {
                                echo '<span class="text-on-surface-variant font-bold">EXM</span>';
                              } else {
                                echo number_format((float)$m->marks_obtained, 2);
                              }
                            ?>
                          </td>
                          <td class="px-4 py-2.5 text-center font-mono text-on-surface-variant"><?php echo number_format((float)$m->max_marks, 2); ?></td>
                          <td class="px-4 py-2.5 text-center">
                            <span class="px-2 py-0.5 rounded font-bold text-xs bg-surface-container-high text-on-surface">
                              <?php echo html_escape($m->grade ?: '—'); ?>
                            </span>
                          </td>
                          <td class="px-4 py-2.5 text-center">
                            <?php
                              $sub_pass = !empty($m->passing_marks) ? (float)$m->passing_marks : 35.0;
                              $obtained = (float)($m->marks_obtained ?? 0);
                              $is_sub_pass = empty($m->is_absent) && ($obtained >= $sub_pass);
                            ?>
                            <span class="text-xs font-semibold <?php echo $is_sub_pass ? 'text-secondary' : 'text-error'; ?>">
                              <?php echo $is_sub_pass ? 'Pass' : 'Fail'; ?>
                            </span>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                  <!-- Exam Total Footer -->
                  <tfoot class="bg-surface-container-low/70 border-t border-outline-variant/60 font-medium text-on-surface text-xs sm:text-body-sm">
                    <tr>
                      <td class="px-4 py-2.5 font-bold">Total / Overall</td>
                      <td class="px-4 py-2.5 text-center font-mono font-bold text-primary"><?php echo number_format((float)$ex->total_marks, 2); ?></td>
                      <td class="px-4 py-2.5 text-center font-mono font-bold"><?php echo number_format((float)$ex->max_marks, 2); ?></td>
                      <td class="px-4 py-2.5 text-center font-bold">
                        <span class="px-2 py-0.5 rounded bg-primary/10 text-primary font-bold">
                          <?php echo html_escape($ex->overall_grade ?: '—'); ?>
                        </span>
                      </td>
                      <td class="px-4 py-2.5 text-center font-bold">
                        <?php echo number_format((float)$ex->percentage, 1); ?>%
                      </td>
                    </tr>
                  </tfoot>
                </table>
              </div>

              <!-- Exam Metrics Summary Bar -->
              <div class="bg-surface-container-lowest px-4 py-2.5 border-t border-outline-variant/30 flex flex-wrap items-center justify-between text-xs text-on-surface-variant gap-2">
                <div>Percentage: <strong class="text-on-surface"><?php echo number_format((float)$ex->percentage, 1); ?>%</strong></div>
                <div>Grade: <strong class="text-on-surface"><?php echo html_escape($ex->overall_grade ?: '—'); ?></strong></div>
                <div>Overall Result: <strong class="<?php echo ($ex->pass_status === 'Pass') ? 'text-secondary' : 'text-error'; ?>"><?php echo html_escape($ex->pass_status ?: '—'); ?></strong></div>
                <div>Rank: <strong class="text-on-surface"><?php echo !empty($ex->class_rank) ? '#' . (int)$ex->class_rank : 'Not Available'; ?></strong></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- 5. Attendance Summary -->
    <div class="p-6 sm:p-8 border-b border-outline-variant/40" data-testid="section-attendance">
      <div class="flex items-center justify-between mb-5">
        <h3 class="text-base font-bold text-on-surface flex items-center gap-2">
          <span class="material-symbols-outlined text-secondary text-[22px]">calendar_month</span>Attendance Summary
        </h3>
        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-secondary-container text-on-secondary-container">
          <?php echo $is_ss ? 'SS Period-wise Tracking' : 'Monthly Academic Tracking'; ?>
        </span>
      </div>

      <?php if ($is_ss): ?>
        <!-- ================= SS ATTENDANCE MODEL ================= -->
        <div class="space-y-5">
          <!-- Overall SS Attendance Summary KPI Card -->
          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3" data-testid="ss-attendance-summary-cards">
            <div class="p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/40 text-center">
              <div class="text-xl font-bold text-on-surface"><?php echo (int)($attendance_data->period_summary->total_periods ?? 0); ?></div>
              <div class="text-[11px] text-on-surface-variant mt-1">Total Scheduled Periods</div>
            </div>
            <div class="p-3.5 rounded-xl bg-secondary-container/20 border border-secondary/20 text-center">
              <div class="text-xl font-bold text-secondary"><?php echo (int)($attendance_data->period_summary->present ?? 0); ?></div>
              <div class="text-[11px] text-on-secondary-container mt-1">Periods Present</div>
            </div>
            <div class="p-3.5 rounded-xl bg-error-container/20 border border-error/20 text-center">
              <div class="text-xl font-bold text-error"><?php echo (int)($attendance_data->period_summary->absent ?? 0); ?></div>
              <div class="text-[11px] text-on-error-container mt-1">Periods Absent</div>
            </div>
            <div class="p-3.5 rounded-xl bg-amber-100 dark:bg-amber-950/30 border border-amber-300 text-center">
              <div class="text-xl font-bold text-amber-900 dark:text-amber-300"><?php echo (int)($attendance_data->period_summary->late ?? 0); ?></div>
              <div class="text-[11px] text-on-surface-variant mt-1">Late Periods</div>
            </div>
            <div class="p-3.5 rounded-xl bg-primary-fixed/20 border border-primary/20 text-center">
              <div class="text-xl font-bold text-primary"><?php echo (int)($attendance_data->period_summary->excused ?? 0); ?></div>
              <div class="text-[11px] text-on-surface-variant mt-1">Leave / Excused</div>
            </div>
            <div class="p-3.5 rounded-xl bg-secondary-container/30 border border-secondary/40 text-center">
              <div class="text-xl font-bold text-secondary" data-testid="ss-overall-percentage"><?php echo number_format((float)($attendance_data->period_summary->percentage ?? 0), 1); ?>%</div>
              <div class="text-[11px] text-on-surface-variant mt-1">Overall Period %</div>
            </div>
          </div>

          <!-- SS Subject-wise Attendance Table -->
          <div class="overflow-x-auto rounded-xl border border-outline-variant/60">
            <table class="w-full text-body-sm text-left border-collapse" data-testid="ss-subject-attendance-table">
              <thead>
                <tr class="bg-surface-container-low border-b border-outline-variant/50 text-[11px] uppercase tracking-wider text-on-surface-variant">
                  <th class="px-4 py-2.5 font-semibold">Subject</th>
                  <th class="px-4 py-2.5 font-semibold text-center">Total Periods</th>
                  <th class="px-4 py-2.5 font-semibold text-center">Present</th>
                  <th class="px-4 py-2.5 font-semibold text-center">Absent</th>
                  <th class="px-4 py-2.5 font-semibold text-center">Late</th>
                  <th class="px-4 py-2.5 font-semibold text-center">Leave / Excused</th>
                  <th class="px-4 py-2.5 font-semibold text-center">Attendance %</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-outline-variant/30">
                <?php if (empty($attendance_data->subject_summary)): ?>
                  <tr>
                    <td colspan="7" class="px-4 py-5 text-center text-on-surface-variant">No period attendance records logged.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($attendance_data->subject_summary as $ssub): ?>
                    <tr class="hover:bg-surface-container-lowest/60">
                      <td class="px-4 py-2.5 font-medium text-on-surface"><?php echo html_escape($ssub->subject_name); ?></td>
                      <td class="px-4 py-2.5 text-center font-mono"><?php echo (int)$ssub->total_classes; ?></td>
                      <td class="px-4 py-2.5 text-center font-mono font-medium text-secondary"><?php echo (int)$ssub->present; ?></td>
                      <td class="px-4 py-2.5 text-center font-mono font-medium text-error"><?php echo (int)$ssub->absent; ?></td>
                      <td class="px-4 py-2.5 text-center font-mono text-amber-700"><?php echo (int)$ssub->late; ?></td>
                      <td class="px-4 py-2.5 text-center font-mono text-primary"><?php echo (int)$ssub->excused; ?></td>
                      <td class="px-4 py-2.5 text-center font-bold text-on-surface">
                        <?php echo number_format((float)$ssub->percentage, 1); ?>%
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      <?php else: ?>
        <!-- ================= NON-SS ATTENDANCE MODEL ================= -->
        <div class="space-y-5">
          <!-- Non-SS Day Attendance KPI Summary -->
          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3" data-testid="non-ss-attendance-summary-cards">
            <div class="p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/40 text-center">
              <div class="text-xl font-bold text-on-surface"><?php echo (int)($attendance_data->day_summary->working_days ?? 0); ?></div>
              <div class="text-[11px] text-on-surface-variant mt-1">Total Academic Days</div>
            </div>
            <div class="p-3.5 rounded-xl bg-secondary-container/20 border border-secondary/20 text-center">
              <div class="text-xl font-bold text-secondary"><?php echo (int)($attendance_data->day_summary->present ?? 0); ?></div>
              <div class="text-[11px] text-on-secondary-container mt-1">Days Present</div>
            </div>
            <div class="p-3.5 rounded-xl bg-error-container/20 border border-error/20 text-center">
              <div class="text-xl font-bold text-error"><?php echo (int)($attendance_data->day_summary->absent ?? 0); ?></div>
              <div class="text-[11px] text-on-error-container mt-1">Days Absent</div>
            </div>
            <div class="p-3.5 rounded-xl bg-amber-100 dark:bg-amber-950/30 border border-amber-300 text-center">
              <div class="text-xl font-bold text-amber-900 dark:text-amber-300"><?php echo (int)($attendance_data->day_summary->late ?? 0); ?></div>
              <div class="text-[11px] text-on-surface-variant mt-1">Late Arrivals</div>
            </div>
            <div class="p-3.5 rounded-xl bg-primary-fixed/20 border border-primary/20 text-center">
              <div class="text-xl font-bold text-primary"><?php echo (int)($attendance_data->day_summary->excused ?? 0); ?></div>
              <div class="text-[11px] text-on-surface-variant mt-1">Leave / Excused</div>
            </div>
            <div class="p-3.5 rounded-xl bg-secondary-container/30 border border-secondary/40 text-center">
              <div class="text-xl font-bold text-secondary" data-testid="non-ss-overall-percentage"><?php echo number_format((float)($attendance_data->day_summary->percentage ?? 0), 1); ?>%</div>
              <div class="text-[11px] text-on-surface-variant mt-1">Overall Day %</div>
            </div>
          </div>

          <!-- Month-based Attendance Table -->
          <div class="overflow-x-auto rounded-xl border border-outline-variant/60">
            <table class="w-full text-body-sm text-left border-collapse" data-testid="non-ss-monthly-attendance-table">
              <thead>
                <tr class="bg-surface-container-low border-b border-outline-variant/50 text-[11px] uppercase tracking-wider text-on-surface-variant">
                  <th class="px-4 py-2.5 font-semibold">Month</th>
                  <th class="px-4 py-2.5 font-semibold text-center">Present</th>
                  <th class="px-4 py-2.5 font-semibold text-center">Absent</th>
                  <th class="px-4 py-2.5 font-semibold text-center">Late</th>
                  <th class="px-4 py-2.5 font-semibold text-center">Leave / Excused</th>
                  <th class="px-4 py-2.5 font-semibold text-center">Total Days</th>
                  <th class="px-4 py-2.5 font-semibold text-center">Attendance %</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-outline-variant/30">
                <?php if (empty($attendance_data->day_monthly)): ?>
                  <tr>
                    <td colspan="7" class="px-4 py-5 text-center text-on-surface-variant">No attendance records logged for this academic year.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($attendance_data->day_monthly as $dm): ?>
                    <tr class="hover:bg-surface-container-lowest/60">
                      <td class="px-4 py-2.5 font-medium text-on-surface"><?php echo html_escape($dm->month_name); ?></td>
                      <td class="px-4 py-2.5 text-center font-mono font-medium text-secondary"><?php echo (int)$dm->present_count; ?></td>
                      <td class="px-4 py-2.5 text-center font-mono font-medium text-error"><?php echo (int)$dm->absent_count; ?></td>
                      <td class="px-4 py-2.5 text-center font-mono text-amber-700"><?php echo (int)$dm->late_count; ?></td>
                      <td class="px-4 py-2.5 text-center font-mono text-primary"><?php echo (int)$dm->excused_count; ?></td>
                      <td class="px-4 py-2.5 text-center font-mono text-on-surface-variant"><?php echo (int)$dm->total_days; ?></td>
                      <td class="px-4 py-2.5 text-center font-bold text-on-surface">
                        <?php echo number_format((float)$dm->percentage, 1); ?>%
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

    <!-- 6. Fees & Finance Summary -->
    <div class="p-6 sm:p-8 border-b border-outline-variant/40" data-testid="section-fees">
      <div class="flex items-center justify-between mb-5">
        <h3 class="text-base font-bold text-on-surface flex items-center gap-2">
          <span class="material-symbols-outlined text-primary text-[22px]">payments</span>Fees & Finance Summary
        </h3>
        <span class="text-xs text-on-surface-variant">Academic Year: <strong><?php echo html_escape($academicYearName); ?></strong></span>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4" data-testid="fee-summary-cards">
        <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 shadow-xs text-center">
          <span class="text-[11px] text-on-surface-variant uppercase font-semibold block tracking-wider">Total Fee</span>
          <div class="text-xl font-bold font-mono text-on-surface mt-1.5" data-testid="fee-total-assigned">₹<?php echo number_format((float)($fee_summary->total_assigned ?? 0), 2); ?></div>
        </div>
        <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 shadow-xs text-center">
          <span class="text-[11px] text-on-surface-variant uppercase font-semibold block tracking-wider">Paid Amount</span>
          <div class="text-xl font-bold font-mono text-secondary mt-1.5" data-testid="fee-total-paid">₹<?php echo number_format((float)($fee_summary->total_paid ?? 0), 2); ?></div>
        </div>
        <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 shadow-xs text-center">
          <span class="text-[11px] text-on-surface-variant uppercase font-semibold block tracking-wider">Pending Amount</span>
          <div class="text-xl font-bold font-mono text-amber-900 mt-1.5" data-testid="fee-total-due">₹<?php echo number_format((float)($fee_summary->total_due ?? 0), 2); ?></div>
        </div>
        <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 shadow-xs text-center">
          <span class="text-[11px] text-on-surface-variant uppercase font-semibold block tracking-wider">Overdue Amount</span>
          <div class="text-xl font-bold font-mono text-error mt-1.5" data-testid="fee-total-overdue">₹<?php echo number_format((float)($fee_summary->total_overdue ?? 0), 2); ?></div>
        </div>
      </div>
    </div>

    <!-- 7. Signatures Area -->
    <div class="p-8 sm:p-10 bg-[#F7F7F7]" data-testid="section-signatures">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-12 sm:gap-24 pt-6">
        <div class="text-center">
          <div class="h-14 flex items-end justify-center mb-2">
            <!-- Signature placeholder/line -->
          </div>
          <div class="w-48 sm:w-56 mx-auto border-t-2 border-dashed border-outline-variant mb-2"></div>
          <div class="font-bold text-on-surface text-sm">Class Teacher</div>
          <?php if (!empty($teacher_name)): ?>
            <div class="text-xs text-on-surface-variant mt-0.5" data-testid="teacher-name"><?php echo html_escape($teacher_name); ?></div>
          <?php endif; ?>
        </div>

        <div class="text-center">
          <div class="h-14 flex items-end justify-center mb-2">
            <!-- Signature placeholder/line -->
          </div>
          <div class="w-48 sm:w-56 mx-auto border-t-2 border-dashed border-outline-variant mb-2"></div>
          <div class="font-bold text-on-surface text-sm">Principal / Head</div>
          <div class="text-xs text-on-surface-variant mt-0.5" data-testid="principal-name"><?php echo html_escape($principal_name ?: 'Principal'); ?></div>
        </div>
      </div>

      <div class="mt-8 pt-4 border-t border-outline-variant/30 text-center text-xs text-on-surface-variant/70">
        This is an official computer-generated student performance report issued by <?php echo html_escape($school_name); ?>.
      </div>
    </div>

  </div>

  <!-- Bottom Back Navigation -->
  <div class="mt-6 text-center">
    <a href="<?php echo site_url('students/profile/' . (int)$student->student_id); ?>" class="inline-flex items-center gap-1.5 text-label-md text-primary font-semibold hover:underline">
      <span class="material-symbols-outlined text-[18px]">arrow_back</span>Back to Student Profile Overview
    </a>
  </div>

</div>
