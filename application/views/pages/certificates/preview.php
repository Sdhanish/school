<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

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

    <!-- Header & Action Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <div class="flex items-center gap-2">
          <h2 class="font-headline-md text-headline-md text-on-surface"><?php echo html_escape($cert->certificate_type); ?></h2>
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold font-mono bg-primary-container text-on-primary-container">
            <?php echo html_escape($cert->certificate_no); ?>
          </span>
          <span class="px-2 py-0.2 rounded text-[11px] font-mono font-bold bg-surface-container-high text-secondary">
            v<?php echo $cert->version; ?>
          </span>
        </div>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">
          Student: <strong><?php echo html_escape($cert->first_name . ' ' . $cert->last_name); ?></strong> (<?php echo html_escape($cert->admission_number); ?>) • Issued: <?php echo date('d M Y', strtotime($cert->issue_date)); ?>
        </p>
      </div>

      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('certificates/dashboard'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>Back
        </a>
        <button onclick="document.getElementById('reissueModal').showModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-amber-700 bg-surface-container-lowest text-label-md hover:bg-amber-50 transition-colors cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">history_edu</span>Reissue
        </button>
        <a href="<?php echo site_url('certificates/print/' . $cert->certificate_id); ?>" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">print</span>Print Certificate
        </a>
        <?php if ($cert->status !== 'Issued'): ?>
          <a href="<?php echo site_url('certificates/issue/' . $cert->certificate_id); ?>" onclick="return confirm('Confirm marking this certificate as Issued to student?');" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:bg-primary-dark transition-colors shadow-sm">
            <span class="material-symbols-outlined text-[18px]">check_circle</span>Mark as Issued
          </a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Official Certificate Document Paper Frame -->
    <div class="max-w-4xl mx-auto bg-white text-slate-900 border-4 border-double border-slate-400 rounded-2xl p-8 sm:p-12 shadow-2xl relative mb-8 overflow-hidden font-serif">
      
      <!-- Watermark Background -->
      <?php if ($settings->watermark_enabled): ?>
        <div class="absolute inset-0 flex items-center justify-center opacity-5 pointer-events-none select-none font-sans font-black text-6xl tracking-widest uppercase text-slate-800 rotate-[-30deg]">
          <?php echo html_escape($school->school_name ?? 'LOGIN2'); ?>
        </div>
      <?php endif; ?>

      <!-- Header / Logo -->
      <div class="text-center pb-6 border-b-2 border-slate-300 relative">
        <div class="flex items-center justify-between mb-2">
          <div class="text-left font-mono text-xs text-slate-500 font-bold">
            <div>REF: <?php echo html_escape($cert->certificate_no); ?></div>
            <div>DATE: <?php echo date('d-m-Y', strtotime($cert->issue_date)); ?></div>
          </div>
          <div class="text-right font-mono text-xs text-slate-500 font-bold">
            <div>AFFILIATION NO: <?php echo html_escape($school->school_code ?? 'SCH-01'); ?></div>
            <div>STATUS: <?php echo strtoupper($cert->status); ?></div>
          </div>
        </div>

        <h1 class="font-bold text-2xl sm:text-3xl text-slate-900 tracking-wide uppercase font-serif">
          <?php echo html_escape($school->school_name ?? 'LOGIN2 MANAGEMENT SYSTEM'); ?>
        </h1>
        <p class="text-xs text-slate-600 font-sans mt-1">
          <?php echo html_escape($school->address ?? ''); ?> • Phone: <?php echo html_escape($school->phone ?? ''); ?> • Email: <?php echo html_escape($school->email ?? ''); ?>
        </p>

        <div class="mt-5 inline-block">
          <span class="px-6 py-1.5 rounded-full border-2 border-slate-800 text-slate-900 font-bold text-sm sm:text-base tracking-widest uppercase font-sans bg-slate-50">
            <?php echo html_escape($cert->header_content ?: $cert->certificate_type); ?>
          </span>
        </div>
      </div>

      <!-- Certificate Body Content -->
      <div class="py-8 font-serif text-slate-800 text-base leading-relaxed space-y-4">
        <?php echo $cert->generated_content; ?>
      </div>

      <!-- Footer Signatures -->
      <div class="pt-12 border-t border-slate-200 mt-8 font-sans">
        <div class="flex items-end justify-between text-center">
          <div class="w-48">
            <div class="h-12 flex items-center justify-center font-cursive text-sm text-slate-500 italic">
              Authorized Signature
            </div>
            <div class="border-t border-slate-800 pt-1 text-xs font-bold text-slate-700 uppercase">
              Prepared / Verified By
            </div>
          </div>

          <div class="w-32 h-32 rounded-full border-2 border-dashed border-slate-400 flex items-center justify-center text-[10px] text-slate-400 uppercase font-bold tracking-widest select-none">
            [ Login2 Seal ]
          </div>

          <div class="w-48">
            <div class="h-12 flex items-center justify-center font-bold text-slate-900">
              <?php echo html_escape($school->principal_name ?? 'Principal'); ?>
            </div>
            <div class="border-t border-slate-800 pt-1 text-xs font-bold text-slate-700 uppercase">
              Principal Signature
            </div>
          </div>
        </div>

        <div class="mt-8 text-center text-[11px] text-slate-500 font-mono">
          * This document is generated electronically from the Login2 management system ledger.
        </div>
      </div>
    </div>

    <!-- Reissue Certificate Modal Dialog -->
    <dialog id="reissueModal" class="p-0 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-3 w-full max-w-lg backdrop:bg-scrim/40">
      <div class="p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface">Reissue Certificate (Version Bump)</h3>
          <button onclick="document.getElementById('reissueModal').close()" class="p-1 rounded-full hover:bg-surface-container-high text-on-surface-variant">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('certificates/reissue/' . $cert->certificate_id); ?>
          <div class="space-y-4">
            <div class="p-3 rounded-xl bg-amber-100 text-amber-900 text-xs font-medium">
              Reissuing will preserve version <?php echo $cert->version; ?> in the audit history and create version <?php echo $cert->version + 1; ?>.
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Reason for Reissue *</label>
              <textarea name="reissue_reason" rows="3" required placeholder="State reason (e.g. Correction of student name spelling, replacement of lost certificate)..." class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-outline-variant/50">
              <button type="button" onclick="document.getElementById('reissueModal').close()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-label-md">Cancel</button>
              <button type="submit" class="px-5 py-2 rounded-lg bg-amber-600 text-white text-label-md font-semibold hover:bg-amber-700 transition-colors shadow-sm">Confirm Reissue</button>
            </div>
          </div>
        <?php echo form_close(); ?>
      </div>
    </dialog>
