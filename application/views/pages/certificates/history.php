<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Certificate History & Reissue Ledger</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Audit log of all issued, printed, and reissued official certificates and versioning records.</p>
      </div>
      <div class="flex items-center gap-2">
        <a href="<?php echo site_url('certificates/dashboard'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>Dashboard
        </a>
      </div>
    </div>

    <!-- History Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Certificate Records (<?php echo count($certificates); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Certificate No</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Student</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Certificate Type</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Issue Date</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Version</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Reissued</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($certificates)): ?>
              <tr><td colspan="8" class="px-4 py-8 text-center text-on-surface-variant">No certificate history records found.</td></tr>
            <?php else: ?>
              <?php foreach ($certificates as $c): ?>
                <?php
                  $stBadge = ($c->status === 'Issued') ? 'bg-secondary-container text-on-secondary-container' : (($c->status === 'Printed') ? 'bg-primary-container text-on-primary-container' : 'bg-surface-container-high text-on-surface-variant');
                ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap font-mono font-bold text-primary text-[13px]">
                    <?php echo html_escape($c->certificate_no); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <strong class="text-on-surface block"><?php echo html_escape($c->first_name . ' ' . $c->last_name); ?></strong>
                    <span class="text-[11px] text-on-surface-variant font-mono"><?php echo html_escape($c->admission_number); ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-[13px] text-on-surface font-medium">
                    <?php echo html_escape($c->certificate_type); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[12px] text-on-surface">
                    <?php echo date('d M Y', strtotime($c->issue_date)); ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap font-mono text-xs font-bold text-secondary">
                    v<?php echo $c->version; ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="text-xs font-semibold <?php echo $c->is_reissued ? 'text-amber-600 font-bold' : 'text-on-surface-variant'; ?>">
                      <?php echo $c->is_reissued ? 'Yes' : 'No'; ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold <?php echo $stBadge; ?>">
                      <?php echo html_escape($c->status); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <div class="flex items-center justify-center gap-2">
                      <a href="<?php echo site_url('certificates/preview/' . $c->certificate_id); ?>" class="p-1 rounded hover:bg-surface-container-high text-primary font-semibold text-xs inline-flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">visibility</span>Preview
                      </a>
                      <a href="<?php echo site_url('certificates/print/' . $c->certificate_id); ?>" target="_blank" class="p-1 rounded hover:bg-surface-container-high text-secondary font-semibold text-xs inline-flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">print</span>Print
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
