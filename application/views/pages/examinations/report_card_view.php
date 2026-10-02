<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Action Toolbar (Hidden during Print) -->
    <div class="no-print print:hidden flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div class="flex items-center gap-2">
        <a href="<?php echo site_url('examinations/report_cards'); ?>" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant transition-colors">
          <span class="material-symbols-outlined text-[20px]">arrow_back</span>
        </a>
        <h2 class="font-headline-md text-headline-md text-on-surface">Academic Progress Report Card</h2>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">print</span>Print Report Card
        </button>
      </div>
    </div>

    <!-- REPORT CARD SHEET (Styled for Screen & Print) -->
    <div class="report-card-container bg-surface-container-lowest border border-outline-variant/60 rounded-2xl p-8 max-w-4xl mx-auto elevation-2 print:border-none print:shadow-none print:p-0 print:m-0 space-y-6 overflow-hidden">
      
      <!-- 1. School Header -->
      <?php if (!empty($document_design->has_header) && !empty($document_design->header_url)): ?>
        <div class="-mx-8 -mt-8 print:m-0 pb-4 border-b-2 border-outline-variant/80 overflow-hidden rounded-t-2xl print:rounded-none">
          <img src="<?php echo html_escape($document_design->header_url); ?>" alt="Report Card Header" class="w-full h-auto block" />
          <div class="text-center mt-3">
            <div class="inline-block px-4 py-1 rounded-full bg-primary text-white font-bold text-[13px] uppercase tracking-wider">
              <?php echo html_escape($settings->report_card_header ?: 'Official Academic Report Card'); ?>
            </div>
          </div>
        </div>
      <?php else: ?>
        <div class="text-center pb-6 border-b-2 border-outline-variant/80">
          <div class="flex items-center justify-center gap-3 mb-2">
            <span class="material-symbols-outlined text-primary text-[40px]">school</span>
            <h1 class="text-2xl sm:text-3xl font-extrabold uppercase tracking-wide text-on-surface"><?php echo html_escape($this->current_school->school_name ?? 'School Management'); ?></h1>
          </div>
          <p class="text-body-md text-on-surface-variant font-medium"><?php echo html_escape($this->current_school->address ?? 'Institutional Campus'); ?> • Phone: <?php echo html_escape($this->current_school->phone ?? ''); ?></p>
          <div class="mt-3 inline-block px-4 py-1 rounded-full bg-primary text-white font-bold text-[13px] uppercase tracking-wider">
            <?php echo html_escape($settings->report_card_header ?: 'Official Academic Report Card'); ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- 2. Student & Exam Information Card -->
      <div class="rounded-xl border border-slate-200 bg-white overflow-hidden text-sm shadow-xs print:shadow-none print:border-slate-300">
        <div class="bg-slate-50 px-4 py-2 border-b border-slate-200 flex items-center justify-between">
          <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider">Student Information</span>
          <span class="text-[11px] text-slate-500 font-medium">Session: <strong class="text-slate-800"><?php echo html_escape($result->year_name); ?></strong></span>
        </div>
        <div class="p-3.5">
          <table class="w-full border-collapse">
            <tr>
              <td class="w-1/3 py-1.5 px-2 align-top">
                <div class="text-[10px] uppercase font-semibold text-slate-500 tracking-wider">Student Name</div>
                <div class="text-[13px] font-bold text-slate-900 mt-0.5"><?php echo html_escape($result->first_name . ' ' . $result->last_name); ?></div>
                <div class="text-[11px] text-slate-500 font-mono mt-0.5">Adm No: <span class="font-semibold text-slate-700"><?php echo html_escape($result->admission_number); ?></span></div>
              </td>
              <td class="w-1/3 py-1.5 px-2 align-top border-l border-slate-100">
                <div class="text-[10px] uppercase font-semibold text-slate-500 tracking-wider">Class & Division</div>
                <div class="text-[13px] font-bold text-slate-900 mt-0.5"><?php echo html_escape($result->class_name . ' ' . ($result->division_name ?: '')); ?></div>
                <div class="text-[11px] text-slate-500 mt-0.5">Roll No: <strong class="text-slate-800 font-mono"><?php echo html_escape($result->roll_number ?: '—'); ?></strong></div>
              </td>
              <td class="w-1/3 py-1.5 px-2 align-top border-l border-slate-100">
                <div class="text-[10px] uppercase font-semibold text-slate-500 tracking-wider">Examination</div>
                <div class="text-[13px] font-bold text-sky-800 mt-0.5"><?php echo html_escape($result->exam_name); ?></div>
                <div class="text-[11px] text-slate-500 mt-0.5">Guardian: <span class="text-slate-700 font-medium"><?php echo html_escape($result->guardian_name ?: 'Parent'); ?></span></div>
              </td>
            </tr>
          </table>
        </div>
      </div>

      <!-- 3. Academic Performance Table -->
      <div>
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-[12px] font-bold uppercase tracking-wider text-slate-700">Academic Performance</h3>
          <span class="text-[11px] text-slate-500">Grading Scale: Standard Scholastic</span>
        </div>
        <div class="overflow-x-auto rounded-xl border border-slate-200 print:border-slate-300">
          <table class="w-full border-collapse text-xs">
            <thead>
              <tr class="bg-slate-100/80 border-b border-slate-200 text-slate-600">
                <th class="text-left py-2.5 px-3.5 font-bold uppercase tracking-wider text-[10.5px]">Subject</th>
                <th class="text-right py-2.5 px-3 font-bold uppercase tracking-wider text-[10.5px]">Max</th>
                <th class="text-right py-2.5 px-3 font-bold uppercase tracking-wider text-[10.5px]">Pass</th>
                <th class="text-right py-2.5 px-3 font-bold uppercase tracking-wider text-[10.5px] text-slate-800">Marks Obtained</th>
                <th class="text-right py-2.5 px-3 font-bold uppercase tracking-wider text-[10.5px]">%</th>
                <th class="text-center py-2.5 px-2.5 font-bold uppercase tracking-wider text-[10.5px]">Grade</th>
                <th class="text-right py-2.5 px-3 font-bold uppercase tracking-wider text-[10.5px]">Grade Point</th>
                <th class="text-center py-2.5 px-3 font-bold uppercase tracking-wider text-[10.5px]">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <?php foreach ($result->subject_marks as $idx => $sm): ?>
                <?php
                  $maxM = (float)$sm->max_marks ?: 100.00;
                  $passM = (float)$sm->passing_marks ?: 35.00;
                  $obtM = ($sm->marks_obtained !== null) ? (float)$sm->marks_obtained : 0.00;
                  $subPct = ($maxM > 0) ? round(($obtM / $maxM) * 100, 1) : 0;
                  $isSubPass = (!$sm->is_absent && $obtM >= $passM);
                  $rowBg = ($idx % 2 === 1) ? 'bg-slate-50/50' : 'bg-white';
                ?>
                <tr class="<?php echo $rowBg; ?> hover:bg-slate-50 transition-colors">
                  <td class="py-2.5 px-3.5 font-semibold text-slate-800 text-left"><?php echo html_escape($sm->subject_name); ?></td>
                  <td class="py-2.5 px-3 text-right font-mono text-slate-500"><?php echo (int)$maxM; ?></td>
                  <td class="py-2.5 px-3 text-right font-mono text-slate-500"><?php echo (int)$passM; ?></td>
                  <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900">
                    <?php if ($sm->is_absent): ?><span class="text-rose-600">ABS</span>
                    <?php elseif ($sm->is_exempted): ?><span class="text-sky-600">EXM</span>
                    <?php else: ?><?php echo number_format($obtM, 1); ?><?php endif; ?>
                  </td>
                  <td class="py-2.5 px-3 text-right font-mono text-slate-700"><?php echo ($sm->is_absent || $sm->is_exempted) ? '—' : $subPct . '%'; ?></td>
                  <td class="py-2.5 px-2.5 text-center font-bold text-slate-800"><?php echo html_escape($sm->grade ?: '—'); ?></td>
                  <td class="py-2.5 px-3 text-right font-mono text-slate-700"><?php echo number_format($sm->grade_point, 1); ?></td>
                  <td class="py-2.5 px-3 text-center">
                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold tracking-wide <?php echo $isSubPass ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'; ?>">
                      <?php echo $isSubPass ? 'PASS' : 'FAIL'; ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr class="border-t-2 border-slate-300 bg-slate-100/90 font-bold text-slate-800">
                <td class="py-2.5 px-3.5 text-left uppercase text-[11px] tracking-wider">Grand Total</td>
                <td class="py-2.5 px-3 text-right font-mono text-slate-600"><?php echo (int)$result->max_marks; ?></td>
                <td class="py-2.5 px-3 text-right font-mono text-slate-400">—</td>
                <td class="py-2.5 px-3 text-right font-mono text-slate-900 font-extrabold text-[13px]"><?php echo number_format($result->total_marks, 1); ?></td>
                <td class="py-2.5 px-3 text-right font-mono text-slate-800 text-[13px]"><?php echo number_format($result->percentage, 2); ?>%</td>
                <td class="py-2.5 px-2.5 text-center text-slate-900 text-[13px]"><?php echo html_escape($result->overall_grade); ?></td>
                <td class="py-2.5 px-3 text-right font-mono text-slate-800 text-[13px]"><?php echo number_format($result->gpa, 2); ?></td>
                <td class="py-2.5 px-3 text-center">
                  <span class="inline-block px-2.5 py-0.5 rounded text-[11px] font-bold tracking-wider <?php echo ($result->pass_status === 'Pass') ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'; ?>">
                    <?php echo strtoupper($result->pass_status); ?>
                  </span>
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <!-- 4. Result Evaluation & Academic Attendance Cards (Side-by-Side) -->
      <table class="w-full border-collapse" style="page-break-inside: avoid;">
        <tr>
          <!-- Result Evaluation Card -->
          <td class="w-1/2 align-top pr-2">
            <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-3.5 space-y-2 h-full print:border-slate-300">
              <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 border-b border-slate-200 pb-1.5 flex items-center justify-between">
                <span>Result Evaluation</span>
                <span class="text-[10px] text-slate-500 font-normal">Scholastic Status</span>
              </div>
              <table class="w-full text-xs">
                <tr>
                  <td class="py-1 text-slate-600">Overall Result:</td>
                  <td class="py-1 text-right font-bold <?php echo ($result->pass_status === 'Pass') ? 'text-emerald-700' : 'text-rose-700'; ?>">
                    <?php echo html_escape($result->pass_status); ?>
                  </td>
                </tr>
                <?php if ($settings->show_rank_on_report_card): ?>
                  <tr>
                    <td class="py-1 text-slate-600">Class Merit Position:</td>
                    <td class="py-1 text-right font-mono font-bold text-slate-800">
                      <?php echo $result->class_rank ? $result->class_rank . 'th Position' : 'N/A'; ?>
                    </td>
                  </tr>
                <?php endif; ?>
                <tr>
                  <td class="py-1 text-slate-600">Cumulative GPA:</td>
                  <td class="py-1 text-right font-mono font-bold text-slate-900">
                    <?php echo number_format($result->gpa, 2); ?> <span class="text-slate-500 font-normal">/ 10.0</span>
                  </td>
                </tr>
              </table>
            </div>
          </td>

          <!-- Academic Attendance Card -->
          <td class="w-1/2 align-top pl-2">
            <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-3.5 space-y-2 h-full print:border-slate-300">
              <div class="text-[11px] font-bold uppercase tracking-wider text-slate-700 border-b border-slate-200 pb-1.5 flex items-center justify-between">
                <span>Academic Attendance</span>
                <span class="text-[10px] text-slate-500 font-normal">Session Total</span>
              </div>
              <table class="w-full text-xs">
                <tr>
                  <td class="py-1 text-slate-600">Working Days:</td>
                  <td class="py-1 text-right font-mono text-slate-800 font-semibold"><?php echo (int)($attendance->total_days ?? 0); ?> Days</td>
                </tr>
                <tr>
                  <td class="py-1 text-slate-600">Days Present:</td>
                  <td class="py-1 text-right font-mono text-emerald-700 font-bold"><?php echo (int)($attendance->present ?? 0); ?> Days</td>
                </tr>
                <tr>
                  <td class="py-1 text-slate-600">Attendance Rate:</td>
                  <td class="py-1 text-right font-mono font-bold <?php echo ((float)($attendance->percentage ?? 0) >= 75) ? 'text-emerald-700' : 'text-amber-700'; ?>">
                    <?php echo number_format((float)($attendance->percentage ?? 0), 1); ?>%
                  </td>
                </tr>
              </table>
            </div>
          </td>
        </tr>
      </table>

      <!-- 5. Teacher Assessment & Remarks -->
      <div class="rounded-xl border border-slate-200 bg-slate-50/40 p-3.5 print:border-slate-300" style="page-break-inside: avoid;">
        <div class="text-[10.5px] uppercase font-bold text-slate-700 tracking-wider mb-1.5">Teacher Assessment & Remarks</div>
        <p class="text-xs text-slate-800 italic leading-relaxed pl-2 border-l-2 border-sky-500">
          "<?php echo html_escape($result->teacher_remarks ?: ($result->pass_status === 'Pass' ? 'Demonstrated commendable academic diligence and active participation throughout the term. Keep up the good work!' : 'Needs dedicated attention and remedial support in core subject areas. Regular practice is advised.')); ?>"
        </p>
      </div>

      <!-- 6. Institutional Signatures Area -->
      <div class="pt-8 print:pt-6" style="page-break-inside: avoid;">
        <table class="w-full text-center border-collapse">
          <tr>
            <td class="w-1/3 px-4 align-bottom" style="height: 18mm;">
              <div class="border-t border-slate-400 pt-1.5">
                <div class="text-xs font-bold text-slate-800"><?php echo html_escape($settings->teacher_signature_title ?: 'Class Teacher'); ?></div>
                <div class="text-[10px] text-slate-500 uppercase tracking-wider mt-0.5">Signature & Date</div>
              </div>
            </td>
            <td class="w-1/3 px-4 align-bottom" style="height: 18mm;">
              <div class="border-t border-slate-400 pt-1.5">
                <div class="text-xs font-bold text-slate-800">Parent / Guardian</div>
                <div class="text-[10px] text-slate-500 uppercase tracking-wider mt-0.5">Signature</div>
              </div>
            </td>
            <td class="w-1/3 px-4 align-bottom" style="height: 18mm;">
              <div class="border-t border-slate-400 pt-1.5">
                <div class="text-xs font-bold text-slate-800"><?php echo html_escape($settings->principal_signature_title ?: 'Principal'); ?></div>
                <div class="text-[10px] text-slate-500 uppercase tracking-wider mt-0.5">Seal & Signature</div>
              </div>
            </td>
          </tr>
        </table>
      </div>

      <!-- Footer Image (if configured) -->
      <?php if (!empty($document_design->has_footer) && !empty($document_design->footer_url)): ?>
        <div class="-mx-8 -mb-8 print:m-0 mt-8 pt-2 border-t border-outline-variant/60 overflow-hidden rounded-b-2xl print:rounded-none">
          <img src="<?php echo html_escape($document_design->footer_url); ?>" alt="Report Card Footer" class="w-full h-auto block" />
        </div>
      <?php endif; ?>
    </div>
