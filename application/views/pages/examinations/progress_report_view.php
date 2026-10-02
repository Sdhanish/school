<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Action Toolbar (Hidden during Print) -->
    <div class="no-print print:hidden flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div class="flex items-center gap-2">
        <a href="<?php echo site_url('examinations/progress_reports'); ?>" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant transition-colors">
          <span class="material-symbols-outlined text-[20px]">arrow_back</span>
        </a>
        <h2 class="font-headline-md text-headline-md text-on-surface">Multi-Exam Progress Report</h2>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">print</span>Print Progress Report
        </button>
      </div>
    </div>

    <!-- REPORT SHEET -->
    <div class="progress-report-container bg-surface-container-lowest border border-outline-variant/60 rounded-2xl p-8 max-w-4xl mx-auto elevation-2 print:border-none print:shadow-none print:p-0 print:m-0 space-y-6 overflow-hidden">
      
      <!-- School Header -->
      <?php if (!empty($document_design->has_header) && !empty($document_design->header_url)): ?>
        <div class="-mx-8 -mt-8 print:m-0 pb-4 border-b-2 border-outline-variant/80 overflow-hidden rounded-t-2xl print:rounded-none">
          <img src="<?php echo html_escape($document_design->header_url); ?>" alt="Progress Report Header" class="w-full h-auto block" />
          <div class="text-center mt-3 text-xl font-extrabold uppercase tracking-wide text-on-surface">Comprehensive Progress Report</div>
        </div>
      <?php else: ?>
        <div class="text-center pb-4 border-b-2 border-outline-variant/80">
          <div class="flex items-center justify-center gap-2 mb-1">
            <span class="material-symbols-outlined text-primary text-[36px]">trending_up</span>
            <h1 class="text-2xl font-extrabold uppercase tracking-wide text-on-surface"><?php echo html_escape($this->current_school->school_name ?? 'School Management'); ?></h1>
          </div>
          <p class="text-body-md text-on-surface-variant font-medium">Comprehensive Progress Report • Longitudinal Academic Trend Analysis</p>
        </div>
      <?php endif; ?>

      <!-- 2. Student & Session Information Card -->
      <div class="rounded-xl border border-slate-200 bg-white overflow-hidden text-sm shadow-xs print:shadow-none print:border-slate-300">
        <div class="bg-slate-50 px-4 py-2 border-b border-slate-200 flex items-center justify-between">
          <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider">Student Profile & Academic Record</span>
          <span class="text-[11px] text-slate-500 font-medium">Academic Year: <strong class="text-slate-800"><?php echo html_escape($report->student->year_name); ?></strong></span>
        </div>
        <div class="p-3.5">
          <table class="w-full border-collapse">
            <tr>
              <td class="w-1/4 py-1 px-2 align-top">
                <div class="text-[10px] uppercase font-semibold text-slate-500 tracking-wider">Student Name</div>
                <div class="text-[13px] font-bold text-slate-900 mt-0.5"><?php echo html_escape($report->student->first_name . ' ' . $report->student->last_name); ?></div>
                <div class="text-[11px] text-slate-500 font-mono mt-0.5">Adm: <span class="font-semibold text-slate-700"><?php echo html_escape($report->student->admission_number); ?></span></div>
              </td>
              <td class="w-1/4 py-1 px-2 align-top border-l border-slate-100">
                <div class="text-[10px] uppercase font-semibold text-slate-500 tracking-wider">Class & Division</div>
                <div class="text-[13px] font-bold text-slate-900 mt-0.5"><?php echo html_escape($report->student->class_name . ' ' . ($report->student->division_name ?: '')); ?></div>
                <div class="text-[11px] text-slate-500 mt-0.5">Roll No: <strong class="text-slate-800 font-mono"><?php echo html_escape($report->student->roll_number ?: '—'); ?></strong></div>
              </td>
              <td class="w-1/4 py-1 px-2 align-top border-l border-slate-100">
                <div class="text-[10px] uppercase font-semibold text-slate-500 tracking-wider">Academic Year</div>
                <div class="text-[13px] font-bold text-slate-900 mt-0.5"><?php echo html_escape($report->student->year_name); ?></div>
                <div class="text-[11px] text-slate-500 mt-0.5">Status: <span class="text-emerald-700 font-semibold">Enrolled</span></div>
              </td>
              <td class="w-1/4 py-1 px-2 align-top border-l border-slate-100">
                <div class="text-[10px] uppercase font-semibold text-slate-500 tracking-wider">Exams Tracked</div>
                <div class="text-[13px] font-bold text-sky-800 mt-0.5 font-mono"><?php echo count($report->exams); ?> Examinations</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Assessment: <span class="text-slate-700 font-medium">Comparative</span></div>
              </td>
            </tr>
          </table>
        </div>
      </div>

      <!-- 3. Examination Score Trajectory Cards -->
      <div>
        <h3 class="text-[12px] font-bold uppercase tracking-wider text-slate-700 mb-2">Examination Score Trajectory</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
          <?php foreach ($report->exams as $ex): ?>
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-center print:border-slate-300">
              <span class="text-[11.5px] font-bold text-slate-700 block truncate"><?php echo html_escape($ex['exam_name']); ?></span>
              <div class="text-lg font-extrabold font-mono text-slate-900 mt-1"><?php echo number_format($ex['percentage'], 1); ?>%</div>
              <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10.5px] font-bold bg-white text-slate-700 border border-slate-200">
                Grade <?php echo html_escape($ex['grade']); ?>
              </span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- 4. Comparative Subject Matrix Table -->
      <div>
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-[12px] font-bold uppercase tracking-wider text-slate-700">Subject-Wise Trend Matrix</h3>
          <span class="text-[11px] text-slate-500">Longitudinal Performance Tracking</span>
        </div>
        <div class="overflow-x-auto rounded-xl border border-slate-200 print:border-slate-300">
          <table class="w-full border-collapse text-xs">
            <thead>
              <tr class="bg-slate-100/80 border-b border-slate-200 text-slate-600">
                <th class="text-left py-2.5 px-3.5 font-bold uppercase tracking-wider text-[10.5px]">Subject</th>
                <?php foreach ($report->exams as $ex): ?>
                  <th class="text-right py-2.5 px-3 font-bold uppercase tracking-wider text-[10.5px] whitespace-nowrap">
                    <?php echo html_escape($ex['exam_name']); ?>
                  </th>
                <?php endforeach; ?>
                <th class="text-center py-2.5 px-3 font-bold uppercase tracking-wider text-[10.5px] whitespace-nowrap">Overall Trend</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <?php if (empty($report->subject_matrix)): ?>
                <tr><td colspan="<?php echo count($report->exams) + 2; ?>" class="px-4 py-6 text-center text-slate-500 italic">No comparative exam subject marks recorded yet.</td></tr>
              <?php else: ?>
                <?php $rIdx = 0; foreach ($report->subject_matrix as $subName => $data): $rIdx++; ?>
                  <?php
                    $trendClass = 'bg-slate-100 text-slate-700 border border-slate-200';
                    if ($data['trend'] === 'Improving') {
                      $trendClass = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                    } elseif ($data['trend'] === 'Declining') {
                      $trendClass = 'bg-rose-50 text-rose-700 border border-rose-200';
                    }
                    $rowBg = ($rIdx % 2 === 1) ? 'bg-slate-50/50' : 'bg-white';
                  ?>
                  <tr class="<?php echo $rowBg; ?> hover:bg-slate-50 transition-colors">
                    <td class="py-2.5 px-3.5 font-semibold text-slate-800 text-left whitespace-nowrap">
                      <?php echo html_escape($subName); ?>
                      <?php if (!empty($data['subject_code'])): ?>
                        <span class="text-[10px] text-slate-400 block font-mono font-normal"><?php echo html_escape($data['subject_code']); ?></span>
                      <?php endif; ?>
                    </td>
                    <?php foreach ($report->exams as $ex): ?>
                      <?php $em = $data['exams'][$ex['exam_id']] ?? null; ?>
                      <td class="py-2.5 px-3 text-right font-mono whitespace-nowrap">
                        <?php if ($em && $em['marks'] !== null): ?>
                          <span class="font-bold text-slate-900"><?php echo number_format($em['marks'], 1); ?></span>
                          <span class="text-[10.5px] text-slate-500">(<?php echo $em['percentage']; ?>%)</span>
                        <?php else: ?>
                          <span class="text-slate-400">—</span>
                        <?php endif; ?>
                      </td>
                    <?php endforeach; ?>
                    <td class="py-2.5 px-3 text-center whitespace-nowrap">
                      <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-bold tracking-wide <?php echo $trendClass; ?>">
                        <?php echo html_escape($data['trend']); ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 5. Qualitative Assessment -->
      <div class="rounded-xl border border-slate-200 bg-slate-50/40 p-3.5 print:border-slate-300" style="page-break-inside: avoid;">
        <div class="text-[10.5px] uppercase font-bold text-slate-700 tracking-wider mb-1.5">Academic Coordinator Summary</div>
        <p class="text-xs text-slate-800 italic leading-relaxed pl-2 border-l-2 border-sky-500">
          "The progress report tracks cumulative academic growth over the scholastic term. Consistent performance patterns and subject trend vectors assist in targeted academic counseling."
        </p>
      </div>
      
      <!-- 6. Institutional Signatures Area -->
      <div class="pt-8 print:pt-6" style="page-break-inside: avoid;">
        <table class="w-full text-center border-collapse">
          <tr>
            <td class="w-1/3 px-4 align-bottom" style="height: 18mm;">
              <div class="border-t border-slate-400 pt-1.5">
                <div class="text-xs font-bold text-slate-800">Academic Counselor</div>
                <div class="text-[10px] text-slate-500 uppercase tracking-wider mt-0.5">Signature & Date</div>
              </div>
            </td>
            <td class="w-1/3 px-4 align-bottom" style="height: 18mm;">
              <div class="border-t border-slate-400 pt-1.5">
                <div class="text-xs font-bold text-slate-800">Class Teacher</div>
                <div class="text-[10px] text-slate-500 uppercase tracking-wider mt-0.5">Signature</div>
              </div>
            </td>
            <td class="w-1/3 px-4 align-bottom" style="height: 18mm;">
              <div class="border-t border-slate-400 pt-1.5">
                <div class="text-xs font-bold text-slate-800">Principal / Head of Institution</div>
                <div class="text-[10px] text-slate-500 uppercase tracking-wider mt-0.5">Seal & Signature</div>
              </div>
            </td>
          </tr>
        </table>
      </div>

      <!-- Footer Image (if configured) -->
      <?php if (!empty($document_design->has_footer) && !empty($document_design->footer_url)): ?>
        <div class="-mx-8 -mb-8 print:m-0 mt-8 pt-2 border-t border-outline-variant/60 overflow-hidden rounded-b-2xl print:rounded-none">
          <img src="<?php echo html_escape($document_design->footer_url); ?>" alt="Progress Report Footer" class="w-full h-auto block" />
        </div>
      <?php endif; ?>

      
    </div>
