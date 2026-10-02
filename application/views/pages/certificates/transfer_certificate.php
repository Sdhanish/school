<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Transfer Certificate (TC) Management</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Official leaving certificates, attendance summaries, academic records, and fee clearance checks.</p>
      </div>
      <div class="flex items-center gap-2">
        <a href="<?php echo site_url('certificates/generate'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Generate TC
        </a>
      </div>
    </div>

    <!-- TC Policy Information Box -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <div class="flex items-center gap-3">
        <span class="material-symbols-outlined text-primary text-[24px]">verified</span>
        <div class="text-xs text-on-surface-variant">
          <strong class="text-on-surface text-body-md block">Configured Transfer Certificate Clearance Policies:</strong>
          <span>Fee Clearance Check: <strong><?php echo $settings->require_fee_clearance_for_tc ? 'Enforced' : 'Optional'; ?></strong></span> • 
          <span>Library Clearance: <strong><?php echo $settings->require_library_clearance_for_tc ? 'Enforced' : 'Optional'; ?></strong></span> • 
          <span>Transport Clearance: <strong><?php echo $settings->require_transport_clearance_for_tc ? 'Enforced' : 'Optional'; ?></strong></span>
        </div>
      </div>
    </div>

    <!-- TC Records Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Issued Transfer Certificates (<?php echo count($certificates); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">TC Number</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Student</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Last Class</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Date of Leaving</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Reason for Leaving</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Version</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($certificates)): ?>
              <tr><td colspan="8" class="px-4 py-8 text-center text-on-surface-variant">No transfer certificates generated yet.</td></tr>
            <?php else: ?>
              <?php foreach ($certificates as $c): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap font-mono font-bold text-primary text-[13px]"><?php echo html_escape($c->certificate_no); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <strong class="text-on-surface block"><?php echo html_escape($c->first_name . ' ' . $c->last_name); ?></strong>
                    <span class="text-[11px] text-on-surface-variant font-mono"><?php echo html_escape($c->admission_number); ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-[13px] text-on-surface"><?php echo html_escape($c->class_name . ' - ' . $c->section_name); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[12px] text-on-surface"><?php echo date('d M Y', strtotime($c->issue_date)); ?></td>
                  <td class="px-4 py-3 text-[12px] text-on-surface-variant max-w-[200px] truncate"><?php echo html_escape($c->remarks ?: 'Relocation / Course Completion'); ?></td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-mono text-xs font-bold text-secondary">v<?php echo $c->version; ?></td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-secondary-container text-on-secondary-container"><?php echo $c->status; ?></span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <a href="<?php echo site_url('certificates/preview/' . $c->certificate_id); ?>" class="p-1 rounded hover:bg-surface-container-high text-primary font-semibold text-xs inline-flex items-center gap-1">
                      <span class="material-symbols-outlined text-[16px]">visibility</span>Preview
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
