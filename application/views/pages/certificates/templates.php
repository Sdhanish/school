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

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Certificate Templates Management</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Design certificate layouts, body wording, logo positions, and dynamic template variables.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="document.getElementById('tmplModal').showModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Create Template
        </button>
      </div>
    </div>

    <!-- Supported Variables Pill Grid -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-5 mb-6 space-y-3">
      <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-[20px]">code</span>Supported Dynamic Template Variables
      </h3>
      <div class="flex flex-wrap gap-2">
        <?php foreach ($vars as $v): ?>
          <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-surface-container-high text-primary select-all">
            <?php echo $v; ?>
          </span>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Templates Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Configured Templates (<?php echo count($templates); ?>)</span>
      </div>

      <div class="divide-y divide-outline-variant/40">
        <?php foreach ($templates as $tmpl): ?>
          <div class="p-5 space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
              <div class="flex items-center gap-2">
                <strong class="text-title-md text-on-surface"><?php echo html_escape($tmpl->template_name); ?></strong>
                <span class="px-2 py-0.2 rounded text-[11px] font-mono font-bold bg-primary-container text-on-primary-container"><?php echo $tmpl->type_code; ?></span>
              </div>
              <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-secondary-container text-on-secondary-container self-start sm:self-auto"><?php echo $tmpl->status; ?></span>
            </div>

            <div class="p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/40 text-xs font-mono text-on-surface-variant max-h-36 overflow-y-auto">
              <?php echo nl2br(html_escape($tmpl->body_content)); ?>
            </div>

            <div class="text-[12px] text-on-surface-variant flex items-center gap-4">
              <span>Paper: <strong><?php echo $tmpl->paper_size; ?> (<?php echo $tmpl->orientation; ?>)</strong></span>
              <span>Header: <strong><?php echo html_escape($tmpl->header_content); ?></strong></span>
              <span>Signatures: <strong><?php echo $tmpl->signature_layout; ?></strong></span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Create Template Modal Dialog -->
    <dialog id="tmplModal" class="p-0 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-3 w-full max-w-2xl backdrop:bg-scrim/40">
      <div class="p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface">Create Certificate Template</h3>
          <button onclick="document.getElementById('tmplModal').close()" class="p-1 rounded-full hover:bg-surface-container-high text-on-surface-variant">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('certificates/templates'); ?>
          <div class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Template Name *</label>
                <input type="text" name="template_name" required placeholder="e.g. Migration Template 2026" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Certificate Type *</label>
                <select name="type_code" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <?php foreach ($types as $t): ?>
                    <option value="<?php echo $t->type_code; ?>"><?php echo html_escape($t->type_name . ' (' . $t->type_code . ')'); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Header Title *</label>
              <input type="text" name="header_content" required placeholder="e.g. OFFICIAL BONAFIDE CERTIFICATE" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Template Body HTML / Text *</label>
              <textarea name="body_content" rows="6" required placeholder="This is to certify that {student_name} (Adm: {admission_number}) is studying in {class}..." class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Footer Note</label>
                <input type="text" name="footer_content" placeholder="Issued under institutional authority" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Signature Layout</label>
                <select name="signature_layout" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <option value="Principal-Only">Principal Only</option>
                  <option value="Principal-And-Officer">Principal & Authorized Officer</option>
                  <option value="Officer-Only">Authorized Officer Only</option>
                </select>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-outline-variant/50">
              <button type="button" onclick="document.getElementById('tmplModal').close()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-label-md">Cancel</button>
              <button type="submit" class="px-5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">Save Template</button>
            </div>
          </div>
        <?php echo form_close(); ?>
      </div>
    </dialog>
