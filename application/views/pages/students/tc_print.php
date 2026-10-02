<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <div class="flex items-center justify-between gap-4 mb-6 no-print">
      <div class="flex items-center gap-2">
        <a href="<?php echo site_url('students/transfers'); ?>" class="inline-flex items-center gap-1 px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-label-md text-on-surface-variant hover:bg-surface-container-high"><span class="material-symbols-outlined text-[18px]">arrow_back</span>Back to Transfers</a>
      </div>
      <div class="flex items-center gap-2">
        <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-lg bg-primary text-on-primary text-label-md hover:bg-primary/90 transition-colors shadow-sm cursor-pointer"><span class="material-symbols-outlined text-[18px]">print</span>Print Certificate</button>
      </div>
    </div>

    <style>
      @media print {
        body { background: #fff !important; margin: 0; padding: 0; }
        .no-print, #sidebar-root, #header-root { display: none !important; }
        .tc-container { border: 3px double #000 !important; box-shadow: none !important; margin: 0 auto !important; width: 100% !important; padding: 25px !important; }
      }
    </style>

    <!-- Certificate Document Container -->
    <div class="tc-container elevation-2 rounded-2xl bg-surface-container-lowest border-2 border-outline-variant p-10 max-w-3xl mx-auto my-4 text-on-surface overflow-hidden">
      
      <!-- School Header -->
      <?php if (!empty($document_design->has_header) && !empty($document_design->header_url)): ?>
        <div class="-mx-10 -mt-10 print:m-0 pb-4 border-b-2 border-primary/40 mb-6 overflow-hidden rounded-t-2xl print:rounded-none">
          <img src="<?php echo html_escape($document_design->header_url); ?>" alt="Transfer Certificate Header" class="w-full h-auto block" />
          <div class="text-center mt-3">
            <div class="inline-block px-6 py-1.5 rounded-full bg-surface-container-high font-bold text-sm uppercase tracking-wider text-on-surface border border-outline-variant">
              Transfer Certificate / School Leaving Certificate
            </div>
          </div>
        </div>
      <?php else: ?>
        <div class="text-center border-b-2 border-primary/40 pb-6 mb-6">
          <div class="text-2xl font-bold uppercase tracking-wider text-primary"><?php echo html_escape($this->current_school->school_name ?? 'School Management'); ?></div>
          <div class="text-xs text-on-surface-variant uppercase tracking-widest mt-0.5">Affiliation No. <?php echo html_escape($this->current_school->school_code ?? ''); ?></div>
          <div class="text-xs text-on-surface-variant mt-1"><?php echo html_escape($this->current_school->address ?? ''); ?> | Phone: <?php echo html_escape($this->current_school->phone ?? ''); ?></div>
          <div class="mt-4 inline-block px-6 py-1.5 rounded-full bg-surface-container-high font-bold text-sm uppercase tracking-wider text-on-surface border border-outline-variant">
            Transfer Certificate / School Leaving Certificate
          </div>
        </div>
      <?php endif; ?>

      <!-- TC Metadata Bar -->
      <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3 mb-6 print:border-slate-300">
        <table class="w-full border-collapse text-xs">
          <tr>
            <td class="w-1/3 text-left">
              <span class="text-[10.5px] uppercase font-bold text-slate-500 tracking-wider">Certificate No:</span>
              <span class="font-mono font-bold text-sky-800 text-[13px] ml-1"><?php echo html_escape($transfer->tc_number); ?></span>
            </td>
            <td class="w-1/3 text-center">
              <span class="text-[10.5px] uppercase font-bold text-slate-500 tracking-wider">Admission No:</span>
              <span class="font-mono font-bold text-slate-800 text-[13px] ml-1"><?php echo html_escape($transfer->admission_number); ?></span>
            </td>
            <td class="w-1/3 text-right">
              <span class="text-[10.5px] uppercase font-bold text-slate-500 tracking-wider">Date of Issue:</span>
              <span class="font-semibold text-slate-800 ml-1"><?php echo date('d M Y', strtotime($transfer->transfer_date)); ?></span>
            </td>
          </tr>
        </table>
      </div>

      <!-- Certificate Particulars List -->
      <div class="rounded-xl border border-slate-200 overflow-hidden mb-8 print:border-slate-300">
        <table class="w-full text-xs border-collapse">
          <tbody class="divide-y divide-slate-100">
            <tr class="bg-white">
              <td class="py-2.5 px-3.5 w-2/5 text-slate-600 font-semibold">1. Name of Pupil</td>
              <td class="py-2.5 px-3.5 font-bold uppercase text-slate-900"><?php echo html_escape($transfer->first_name . ' ' . $transfer->last_name); ?></td>
            </tr>
            <tr class="bg-slate-50/50">
              <td class="py-2.5 px-3.5 text-slate-600 font-semibold">2. Father's / Guardian's Name</td>
              <td class="py-2.5 px-3.5 font-medium text-slate-800"><?php echo html_escape($transfer->guardian_name); ?> <span class="text-slate-500 font-normal">(<?php echo html_escape($transfer->guardian_relation ?: 'Father'); ?>)</span></td>
            </tr>
            <tr class="bg-white">
              <td class="py-2.5 px-3.5 text-slate-600 font-semibold">3. Gender</td>
              <td class="py-2.5 px-3.5 text-slate-800"><?php echo html_escape($transfer->gender); ?></td>
            </tr>
            <tr class="bg-slate-50/50">
              <td class="py-2.5 px-3.5 text-slate-600 font-semibold">4. Date of Birth (in figures & words)</td>
              <td class="py-2.5 px-3.5 text-slate-800 font-medium"><?php echo date('d-m-Y', strtotime($transfer->date_of_birth)); ?> <span class="text-slate-500 font-normal">(<?php echo date('jS F, Y', strtotime($transfer->date_of_birth)); ?>)</span></td>
            </tr>
            <tr class="bg-white">
              <td class="py-2.5 px-3.5 text-slate-600 font-semibold">5. Class in which pupil last studied</td>
              <td class="py-2.5 px-3.5 font-bold text-slate-900"><?php echo html_escape($transfer->prev_class ?: 'Grade 10'); ?></td>
            </tr>
            <tr class="bg-slate-50/50">
              <td class="py-2.5 px-3.5 text-slate-600 font-semibold">6. Academic Session</td>
              <td class="py-2.5 px-3.5 text-slate-800"><?php echo html_escape($transfer->year_name ?: ($active_academic_year->year_name ?? '—')); ?></td>
            </tr>
            <tr class="bg-white">
              <td class="py-2.5 px-3.5 text-slate-600 font-semibold">7. Whether school dues cleared</td>
              <td class="py-2.5 px-3.5 font-bold <?php echo ($transfer->dues_cleared == 1) ? 'text-emerald-700' : 'text-amber-700'; ?>">
                <?php echo ($transfer->dues_cleared == 1) ? 'Yes, All Dues Fully Cleared' : 'Pending Dues'; ?>
              </td>
            </tr>
            <tr class="bg-slate-50/50">
              <td class="py-2.5 px-3.5 text-slate-600 font-semibold">8. Reason for leaving the school</td>
              <td class="py-2.5 px-3.5 font-medium text-slate-800"><?php echo html_escape($transfer->reason); ?></td>
            </tr>
            <tr class="bg-white">
              <td class="py-2.5 px-3.5 text-slate-600 font-semibold">9. General Conduct</td>
              <td class="py-2.5 px-3.5 font-medium text-slate-800"><?php echo html_escape($transfer->conduct ?: 'Good'); ?></td>
            </tr>
            <tr class="bg-slate-50/50">
              <td class="py-2.5 px-3.5 text-slate-600 font-semibold">10. Any other remarks</td>
              <td class="py-2.5 px-3.5 text-slate-600 italic"><?php echo html_escape($transfer->remarks ?: 'Nil'); ?></td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Signatures Footer -->
      <div class="pt-8 print:pt-6" style="page-break-inside: avoid;">
        <table class="w-full text-center border-collapse">
          <tr>
            <td class="w-1/3 px-4 align-bottom" style="height: 18mm;">
              <div class="border-t border-slate-400 pt-1.5">
                <div class="text-xs font-bold text-slate-800">Prepared By</div>
                <div class="text-[10px] text-slate-500 uppercase tracking-wider mt-0.5">Clerk / Office Staff</div>
              </div>
            </td>
            <td class="w-1/3 px-4 align-bottom" style="height: 18mm;">
              <div class="border-t border-slate-400 pt-1.5">
                <div class="text-xs font-bold text-slate-800">Checked By</div>
                <div class="text-[10px] text-slate-500 uppercase tracking-wider mt-0.5">Administrative Officer</div>
              </div>
            </td>
            <td class="w-1/3 px-4 align-bottom" style="height: 18mm;">
              <div class="border-t border-slate-400 pt-1.5">
                <div class="text-xs font-bold text-slate-800">Principal</div>
                <div class="text-[10px] text-slate-500 uppercase tracking-wider mt-0.5">Signature & Seal</div>
              </div>
            </td>
          </tr>
        </table>
      </div>

      <!-- Footer Image (if configured) -->
      <?php if (!empty($document_design->has_footer) && !empty($document_design->footer_url)): ?>
        <div class="-mx-10 -mb-10 print:m-0 mt-8 pt-4 border-t border-outline-variant/60 overflow-hidden rounded-b-2xl print:rounded-none">
          <img src="<?php echo html_escape($document_design->footer_url); ?>" alt="Transfer Certificate Footer" class="w-full h-auto block" />
        </div>
      <?php endif; ?>
    </div>
