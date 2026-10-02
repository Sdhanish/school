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
        <h2 class="font-headline-md text-headline-md text-on-surface">Certificate Requests Queue</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Manage, verify, approve, and track student certificate applications and reasons.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="document.getElementById('requestModal').showModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>New Request
        </button>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('certificates/requests'); ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Certificate Type</label>
          <select name="certificate_type_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Types</option>
            <?php foreach ($certificate_types as $t): ?>
              <option value="<?php echo $t->type_id; ?>" <?php echo (($filters['certificate_type_id'] ?? '') == $t->type_id) ? 'selected' : ''; ?>><?php echo html_escape($t->type_name); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Status</label>
          <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Statuses</option>
            <option value="Pending" <?php echo (($filters['status'] ?? '') === 'Pending') ? 'selected' : ''; ?>>Pending</option>
            <option value="Approved" <?php echo (($filters['status'] ?? '') === 'Approved') ? 'selected' : ''; ?>>Approved</option>
            <option value="Generated" <?php echo (($filters['status'] ?? '') === 'Generated') ? 'selected' : ''; ?>>Generated</option>
            <option value="Rejected" <?php echo (($filters['status'] ?? '') === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Class</label>
          <select name="class_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Classes</option>
            <?php foreach ($classes as $c): ?>
              <option value="<?php echo $c->class_id; ?>" <?php echo (($filters['class_id'] ?? '') == $c->class_id) ? 'selected' : ''; ?>><?php echo html_escape($c->class_name); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Search</label>
          <div class="flex gap-2">
            <input type="text" name="search" value="<?php echo html_escape($filters['search'] ?? ''); ?>" placeholder="Student name, admission no..." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            <button type="submit" class="px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant cursor-pointer">
              Go
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Requests Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Certificate Requests (<?php echo count($requests); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Req ID</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Student</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Class</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Certificate Type</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Requested Date</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Reason</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($requests)): ?>
              <tr><td colspan="8" class="px-4 py-8 text-center text-on-surface-variant">No certificate requests found matching current filters.</td></tr>
            <?php else: ?>
              <?php foreach ($requests as $r): ?>
                <?php
                  $stBadge = ($r->status === 'Approved') ? 'bg-secondary-container text-on-secondary-container' : (($r->status === 'Pending') ? 'bg-amber-100 text-amber-900' : (($r->status === 'Generated') ? 'bg-primary-container text-on-primary-container' : 'bg-error-container text-error'));
                ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap font-mono font-bold text-primary">#<?php echo $r->request_id; ?></td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <strong class="text-on-surface block"><?php echo html_escape($r->first_name . ' ' . $r->last_name); ?></strong>
                    <span class="text-[11px] text-on-surface-variant font-mono"><?php echo html_escape($r->admission_number); ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-[13px] text-on-surface"><?php echo html_escape($r->class_name . ' - ' . $r->section_name); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-[13px] text-on-surface font-medium"><?php echo html_escape($r->type_name); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[12px] text-on-surface"><?php echo date('d M Y', strtotime($r->requested_date)); ?></td>
                  <td class="px-4 py-3 text-[12px] text-on-surface-variant max-w-[200px] truncate"><?php echo html_escape($r->reason); ?></td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold <?php echo $stBadge; ?>"><?php echo html_escape($r->status); ?></span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <div class="flex items-center justify-center gap-1.5">
                      <?php if ($r->status === 'Pending'): ?>
                        <a href="<?php echo site_url('certificates/approve_request/' . $r->request_id); ?>" class="p-1 rounded hover:bg-secondary-container text-secondary font-semibold text-xs inline-flex items-center gap-1">
                          Approve
                        </a>
                      <?php endif; ?>
                      <?php if ($r->status === 'Approved' || $r->status === 'Pending'): ?>
                        <a href="<?php echo site_url('certificates/generate/' . $r->request_id); ?>" class="p-1 rounded hover:bg-primary-container text-primary font-semibold text-xs inline-flex items-center gap-1">
                          Generate
                        </a>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Request Modal Dialog -->
    <dialog id="requestModal" class="p-0 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-3 w-full max-w-lg backdrop:bg-scrim/40">
      <div class="p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface">Submit Certificate Request</h3>
          <button onclick="document.getElementById('requestModal').close()" class="p-1 rounded-full hover:bg-surface-container-high text-on-surface-variant">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open_multipart('certificates/request_create'); ?>
          <div class="space-y-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Select Student *</label>
              <select name="student_id" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="">-- Choose Student --</option>
                <?php foreach ($students as $st): ?>
                  <option value="<?php echo $st->student_id; ?>"><?php echo html_escape($st->first_name . ' ' . $st->last_name . ' (' . $st->admission_number . ' - ' . $st->class_name . ' ' . $st->section_name . ')'); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Certificate Type *</label>
                <select name="certificate_type_id" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <?php foreach ($certificate_types as $t): ?>
                    <option value="<?php echo $t->type_id; ?>"><?php echo html_escape($t->type_name); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Required Date</label>
                <input type="date" name="required_date" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Reason for Request *</label>
              <textarea name="reason" rows="2" required placeholder="State purpose (e.g. Passport verification, Higher studies admission)..." class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Supporting Document (Optional)</label>
              <input type="file" name="supporting_document" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-secondary-container file:text-on-secondary-container hover:file:bg-secondary file:cursor-pointer"/>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-outline-variant/50">
              <button type="button" onclick="document.getElementById('requestModal').close()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-label-md">Cancel</button>
              <button type="submit" class="px-5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">Submit Request</button>
            </div>
          </div>
        <?php echo form_close(); ?>
      </div>
    </dialog>
