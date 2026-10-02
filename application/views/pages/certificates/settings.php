<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('success')): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-secondary-container text-on-secondary-container text-body-md font-medium flex items-center gap-2 border border-secondary/20">
        <span class="material-symbols-outlined text-[20px] text-secondary">check_circle</span>
        <?php echo html_escape($this->session->flashdata('success')); ?>
      </div>
    <?php endif; ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Certificate & Document Settings</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Configure automated numbering schemes, clearance requirements, watermarks, and review audit logs.</p>
      </div>
    </div>

    <!-- Settings Form -->
    <?php echo form_open('certificates/settings'); ?>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        
        <!-- Numbering & Clearance -->
        <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
          <div class="flex items-center gap-2 pb-3 border-b border-outline-variant/50">
            <span class="material-symbols-outlined text-primary text-[24px]">pin</span>
            <h3 class="font-headline-md text-title-lg font-bold text-on-surface">Numbering & Verification Rules</h3>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Numbering Format</label>
              <input type="text" name="numbering_format" value="<?php echo html_escape($settings->numbering_format); ?>" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Sequence Padding Length</label>
              <input type="number" name="number_sequence_length" value="<?php echo (int)$settings->number_sequence_length; ?>" min="3" max="8" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>
          </div>

          <div class="space-y-3 pt-2">
            <label class="flex items-center justify-between p-3 rounded-xl bg-surface-container-low border border-outline-variant/40 cursor-pointer">
              <div>
                <strong class="text-on-surface text-body-md block">Require Request Approval Workflow</strong>
                <span class="text-[12px] text-on-surface-variant">Mandate authorized staff approval prior to certificate generation.</span>
              </div>
              <input type="checkbox" name="require_approval" value="1" <?php echo $settings->require_approval ? 'checked' : ''; ?> class="w-5 h-5 rounded text-secondary focus:ring-secondary"/>
            </label>

            <label class="flex items-center justify-between p-3 rounded-xl bg-surface-container-low border border-outline-variant/40 cursor-pointer">
              <div>
                <strong class="text-on-surface text-body-md block">Require Fee Clearance for Transfer Certificate</strong>
                <span class="text-[12px] text-on-surface-variant">Verify student has zero outstanding fee balances before issuing TC.</span>
              </div>
              <input type="checkbox" name="require_fee_clearance_for_tc" value="1" <?php echo $settings->require_fee_clearance_for_tc ? 'checked' : ''; ?> class="w-5 h-5 rounded text-secondary focus:ring-secondary"/>
            </label>

            <label class="flex items-center justify-between p-3 rounded-xl bg-surface-container-low border border-outline-variant/40 cursor-pointer">
              <div>
                <strong class="text-on-surface text-body-md block">Require Library Clearance for TC</strong>
                <span class="text-[12px] text-on-surface-variant">Verify all issued books are returned prior to TC generation.</span>
              </div>
              <input type="checkbox" name="require_library_clearance_for_tc" value="1" <?php echo $settings->require_library_clearance_for_tc ? 'checked' : ''; ?> class="w-5 h-5 rounded text-secondary focus:ring-secondary"/>
            </label>

            <label class="flex items-center justify-between p-3 rounded-xl bg-surface-container-low border border-outline-variant/40 cursor-pointer">
              <div>
                <strong class="text-on-surface text-body-md block">Require Transport Clearance for TC</strong>
                <span class="text-[12px] text-on-surface-variant">Verify bus dues and seat cancellation prior to TC generation.</span>
              </div>
              <input type="checkbox" name="require_transport_clearance_for_tc" value="1" <?php echo $settings->require_transport_clearance_for_tc ? 'checked' : ''; ?> class="w-5 h-5 rounded text-secondary focus:ring-secondary"/>
            </label>
          </div>
        </div>

        <!-- Document Security & Expiry -->
        <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
          <div class="flex items-center gap-2 pb-3 border-b border-outline-variant/50">
            <span class="material-symbols-outlined text-secondary text-[24px]">security</span>
            <h3 class="font-headline-md text-title-lg font-bold text-on-surface">Branding & Expiry Alerts</h3>
          </div>

          <div class="space-y-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Document Expiration Warning Window (Days)</label>
              <input type="number" name="document_expiry_reminder_days" value="<?php echo (int)$settings->document_expiry_reminder_days; ?>" min="7" max="90" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>

            <div class="space-y-3 pt-2">
              <label class="flex items-center justify-between p-3 rounded-xl bg-surface-container-low border border-outline-variant/40 cursor-pointer">
                <div>
                  <strong class="text-on-surface text-body-md block">Enable Diagonal Institutional Watermark</strong>
                  <span class="text-[12px] text-on-surface-variant">Print translucent school name watermark on official certificate paper.</span>
                </div>
                <input type="checkbox" name="watermark_enabled" value="1" <?php echo $settings->watermark_enabled ? 'checked' : ''; ?> class="w-5 h-5 rounded text-secondary focus:ring-secondary"/>
              </label>
            </div>
          </div>
        </div>
      </div>

      <div class="flex items-center justify-end p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 mb-6">
        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">save</span>Save Certificate Settings
        </button>
      </div>
    <?php echo form_close(); ?>

    <!-- Audit Trail -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
          <span class="material-symbols-outlined text-primary text-[20px]">history</span>Certificate Audit Trail
        </h3>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Timestamp</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Action</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Entity</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Details</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($audit_logs)): ?>
              <tr><td colspan="4" class="px-4 py-6 text-center text-on-surface-variant">No audit logs recorded yet.</td></tr>
            <?php else: ?>
              <?php foreach ($audit_logs as $l): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[12px] text-on-surface-variant"><?php echo date('d M Y, h:i A', strtotime($l->created_at)); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap font-bold text-primary"><?php echo html_escape($l->action); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap text-on-surface font-mono text-[12px]">#<?php echo $l->entity_id; ?></td>
                  <td class="px-4 py-3 text-on-surface text-[13px]"><?php echo html_escape($l->details); ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
