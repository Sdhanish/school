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

    <!-- Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Certificate & Document Management</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Generate official bonafide, transfer, study, and conduct certificates, manage student documents and verification.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('certificates/requests'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">inbox</span>Requests
        </a>
        <a href="<?php echo site_url('certificates/generate'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Generate Certificate
        </a>
      </div>
    </div>

    <!-- STATS CARDS GRID -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <!-- Total Certificates -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Total Certificates</span>
          <span class="material-symbols-outlined text-primary text-[22px]">workspace_premium</span>
        </div>
        <div class="text-headline-md font-bold text-on-surface"><?php echo $stats->total_certificates; ?></div>
        <div class="text-xs text-on-surface-variant flex items-center gap-1">
          <span class="font-semibold text-secondary"><?php echo $stats->issued_certs; ?> Issued</span> • <span><?php echo $stats->printed_certs; ?> Printed</span>
        </div>
      </div>

      <!-- Pending Requests -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Pending Requests</span>
          <span class="material-symbols-outlined text-amber-600 text-[22px]">pending_actions</span>
        </div>
        <div class="text-headline-md font-bold text-amber-600"><?php echo $stats->pending_requests; ?></div>
        <div class="text-xs text-on-surface-variant">
          <span class="font-semibold text-secondary"><?php echo $stats->approved_requests; ?> Approved</span> & ready for generation
        </div>
      </div>

      <!-- Student Documents -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Student Documents</span>
          <span class="material-symbols-outlined text-secondary text-[22px]">folder_shared</span>
        </div>
        <div class="text-headline-md font-bold text-on-surface"><?php echo $stats->total_docs; ?></div>
        <div class="text-xs text-on-surface-variant">
          <span class="font-semibold text-amber-600"><?php echo $stats->pending_docs; ?> Pending Verification</span>
        </div>
      </div>

      <!-- Generated Certificates -->
      <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-2">
        <div class="flex items-center justify-between text-on-surface-variant">
          <span class="text-label-md font-semibold uppercase tracking-wider text-xs">Generated / Ready</span>
          <span class="material-symbols-outlined text-primary text-[22px]">description</span>
        </div>
        <div class="text-headline-md font-bold text-primary"><?php echo $stats->generated_certs; ?></div>
        <div class="text-xs text-on-surface-variant">
          Ready for preview and printing
        </div>
      </div>
    </div>

    <!-- Certificate Summary Breakdown -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <a href="<?php echo site_url('certificates/bonafide'); ?>" class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 hover:bg-surface-container-low transition-colors block">
        <span class="text-xs font-semibold text-on-surface-variant uppercase block">Bonafide</span>
        <div class="text-title-lg font-bold text-primary mt-1"><?php echo $stats->bonafide_count; ?> Records</div>
      </a>
      <a href="<?php echo site_url('certificates/transfer_certificate'); ?>" class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 hover:bg-surface-container-low transition-colors block">
        <span class="text-xs font-semibold text-on-surface-variant uppercase block">Transfer (TC)</span>
        <div class="text-title-lg font-bold text-secondary mt-1"><?php echo $stats->tc_count; ?> Records</div>
      </a>
      <a href="<?php echo site_url('certificates/study_certificate'); ?>" class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 hover:bg-surface-container-low transition-colors block">
        <span class="text-xs font-semibold text-on-surface-variant uppercase block">Study</span>
        <div class="text-title-lg font-bold text-amber-600 mt-1"><?php echo $stats->study_count; ?> Records</div>
      </a>
      <a href="<?php echo site_url('certificates/conduct_certificate'); ?>" class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 hover:bg-surface-container-low transition-colors block">
        <span class="text-xs font-semibold text-on-surface-variant uppercase block">Conduct</span>
        <div class="text-title-lg font-bold text-on-surface mt-1"><?php echo $stats->conduct_count; ?> Records</div>
      </a>
    </div>

    <!-- 2 Column Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
      
      <!-- Left 2 Cols: Recent Certificates -->
      <div class="lg:col-span-2 elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
        <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[22px]">history</span>Recently Generated Certificates
          </h3>
          <a href="<?php echo site_url('certificates/history'); ?>" class="text-xs text-primary font-semibold hover:underline">View All</a>
        </div>

        <div class="table-scroll overflow-x-auto">
          <table class="w-full data-table zebra border-collapse text-body-md">
            <thead>
              <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Certificate No</th>
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Student</th>
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Type</th>
                <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Issue Date</th>
                <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
                <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/40">
              <?php if (empty($recent_certificates)): ?>
                <tr><td colspan="6" class="px-4 py-8 text-center text-on-surface-variant">No certificates generated yet.</td></tr>
              <?php else: ?>
                <?php foreach ($recent_certificates as $c): ?>
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
                    <td class="px-4 py-3 text-center whitespace-nowrap">
                      <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold <?php echo $stBadge; ?>">
                        <?php echo html_escape($c->status); ?>
                      </span>
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

      <!-- Right 1 Col: Pending Requests Queue -->
      <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-amber-600 text-[22px]">pending</span>Pending Requests
          </h3>
          <a href="<?php echo site_url('certificates/requests'); ?>" class="text-xs text-primary font-semibold hover:underline">All Requests</a>
        </div>

        <div class="divide-y divide-outline-variant/40">
          <?php if (empty($pending_requests)): ?>
            <div class="py-6 text-center text-on-surface-variant text-xs">No pending certificate requests.</div>
          <?php else: ?>
            <?php foreach ($pending_requests as $req): ?>
              <div class="py-3 space-y-1.5">
                <div class="flex items-center justify-between">
                  <strong class="text-body-md text-on-surface"><?php echo html_escape($req->first_name . ' ' . $req->last_name); ?></strong>
                  <span class="px-2 py-0.2 rounded text-[10px] font-bold bg-amber-100 text-amber-900"><?php echo $req->type_name; ?></span>
                </div>
                <div class="text-[12px] text-on-surface-variant flex items-center justify-between font-mono">
                  <span>Adm: <?php echo html_escape($req->admission_number); ?></span>
                  <span><?php echo date('d M Y', strtotime($req->requested_date)); ?></span>
                </div>
                <p class="text-[11px] text-on-surface-variant line-clamp-1 italic">"<?php echo html_escape($req->reason); ?>"</p>
                <div class="pt-1 flex items-center gap-2">
                  <a href="<?php echo site_url('certificates/approve_request/' . $req->request_id); ?>" class="px-2.5 py-1 rounded bg-secondary text-on-secondary text-[11px] font-semibold hover:bg-on-secondary-fixed-variant transition-colors">
                    Approve
                  </a>
                  <a href="<?php echo site_url('certificates/generate/' . $req->request_id); ?>" class="px-2.5 py-1 rounded bg-primary text-on-primary text-[11px] font-semibold hover:bg-primary-dark transition-colors">
                    Generate
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
