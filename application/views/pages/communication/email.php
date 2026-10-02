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
        <h2 class="font-headline-md text-headline-md text-on-surface">Email Newsletters & Notices</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Send rich HTML email circulars, term newsletters, grade sheets, and detailed fee invoices.</p>
      </div>
    </div>

    <!-- Email Composer Form -->
    <?php echo form_open('communication/email'); ?>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        
        <!-- Left: Email Content (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
          <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
            <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2 pb-2 border-b border-outline-variant/50">
              <span class="material-symbols-outlined text-primary text-[22px]">mail</span>Compose Email
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Recipients *</label>
                <select name="recipient_type" required class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <option value="All Parents">All Registered Parents</option>
                  <option value="All Teachers">All Faculty Members</option>
                  <option value="All Staff">All Administrative Staff</option>
                  <option value="Entire School">Entire School Directory</option>
                  <option value="Custom Email">Specific Email Address</option>
                </select>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Load Template</label>
                <select id="emailTemplateSelect" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <option value="">-- Select Template --</option>
                  <?php foreach ($templates as $t): ?>
                    <option value="<?php echo html_escape($t->message_template); ?>" data-subject="<?php echo html_escape($t->subject); ?>"><?php echo html_escape($t->template_name); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Email Subject *</label>
              <input type="text" id="emailSubject" name="subject" required placeholder="e.g. End of Term Examination Schedule & General Guidelines" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary font-medium"/>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Email Body (HTML Supported) *</label>
              <textarea id="emailBodyArea" name="message" rows="8" required placeholder="Dear {parent_name}, please find attached the details regarding {student_name}'s academic progress..." class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary font-mono text-[13px]"></textarea>
            </div>
          </div>
        </div>

        <!-- Right: Dispatch Options (1 Col) -->
        <div class="space-y-6">
          <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
            <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2 pb-2 border-b border-outline-variant/50">
              <span class="material-symbols-outlined text-primary text-[22px]">send</span>Dispatch Options
            </h3>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Schedule (Optional)</label>
              <input type="datetime-local" name="schedule_time" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>

            <button type="submit" class="w-full py-3 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer flex items-center justify-center gap-2">
              <span class="material-symbols-outlined text-[18px]">send</span>Send Email Campaign
            </button>
          </div>
        </div>
      </div>
    <?php echo form_close(); ?>

    <!-- History Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
          <span class="material-symbols-outlined text-primary text-[20px]">history</span>Recent Email Broadcasts
        </h3>
        <a href="<?php echo site_url('communication/history?channel=Email'); ?>" class="text-xs text-primary font-semibold hover:underline">View All</a>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Recipient</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Subject</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Sent Date</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($history)): ?>
              <tr><td colspan="4" class="px-4 py-6 text-center text-on-surface-variant">No emails dispatched recently.</td></tr>
            <?php else: ?>
              <?php foreach ($history as $h): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap font-semibold text-on-surface"><?php echo html_escape($h->recipient_name ?: $h->recipient_contact); ?></td>
                  <td class="px-4 py-3 text-[13px] text-on-surface font-medium"><?php echo html_escape($h->subject); ?></td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[12px] text-on-surface-variant"><?php echo date('d M Y, h:i A', strtotime($h->created_at)); ?></td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-primary-container text-on-primary-container">
                      <?php echo html_escape($h->status); ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const sel = document.getElementById('emailTemplateSelect');
        const txt = document.getElementById('emailBodyArea');
        const subj = document.getElementById('emailSubject');
        if (sel && txt) {
          sel.addEventListener('change', function() {
            if (this.value) {
              txt.value = this.value;
              const opt = this.options[this.selectedIndex];
              if (opt && opt.dataset.subject && subj) {
                subj.value = opt.dataset.subject;
              }
            }
          });
        }
      });
    </script>
