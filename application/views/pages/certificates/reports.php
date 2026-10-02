<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Certificate & Document Reports</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Analytics, issuance ledgers, student document compliance, and CSV exports.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('certificates/reports?type=' . $type . '&export=csv'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">download</span>Export CSV
        </a>
        <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">print</span>Print Report
        </button>
      </div>
    </div>

    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 mb-6">
      <a href="<?php echo site_url('certificates/reports?type=issued'); ?>" class="px-4 py-2 rounded-lg text-xs font-semibold <?php echo ($type === 'issued') ? 'bg-secondary text-on-secondary shadow-sm' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high'; ?>">
        Certificates Issued Report
      </a>
      <a href="<?php echo site_url('certificates/reports?type=documents'); ?>" class="px-4 py-2 rounded-lg text-xs font-semibold <?php echo ($type === 'documents') ? 'bg-secondary text-on-secondary shadow-sm' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high'; ?>">
        Student Document Compliance
      </a>
    </div>

    <!-- Reports Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Report Records</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <?php if ($type === 'documents'): ?>
          <table class="w-full data-table zebra border-collapse text-body-md">
            <thead>
              <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Student</th>
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Admission No</th>
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Category</th>
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Document Title</th>
                <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Verification</th>
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Expiry Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/40">
              <?php foreach ($documents as $d): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap font-medium text-on-surface"><?php echo html_escape($d->first_name . ' ' . $d->last_name); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[12px] text-on-surface"><?php echo html_escape($d->admission_number); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-[13px] text-on-surface"><?php echo html_escape($d->category_name); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap font-semibold text-[13px] text-on-surface"><?php echo html_escape($d->document_name); ?></td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-secondary-container text-on-secondary-container"><?php echo $d->verification_status; ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-[12px] font-mono text-on-surface-variant"><?php echo html_escape($d->expiry_status); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <table class="w-full data-table zebra border-collapse text-body-md">
            <thead>
              <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Certificate No</th>
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Student</th>
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Class</th>
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Type</th>
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Issue Date</th>
                <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Version</th>
                <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/40">
              <?php foreach ($certificates as $c): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap font-mono font-bold text-primary"><?php echo html_escape($c->certificate_no); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap font-medium text-on-surface"><?php echo html_escape($c->first_name . ' ' . $c->last_name); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-[13px] text-on-surface"><?php echo html_escape($c->class_name . ' ' . $c->section_name); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-[13px] text-on-surface font-medium"><?php echo html_escape($c->certificate_type); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[12px] text-on-surface"><?php echo date('d M Y', strtotime($c->issue_date)); ?></td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-mono text-xs font-bold text-secondary">v<?php echo $c->version; ?></td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-secondary-container text-on-secondary-container"><?php echo $c->status; ?></span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
