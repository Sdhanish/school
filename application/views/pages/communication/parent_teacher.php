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
        <h2 class="font-headline-md text-headline-md text-on-surface">Parent-Teacher Communication</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Direct, private communication channel between teaching faculty and parents/guardians regarding student progress.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="document.getElementById('startConvModal').showModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">chat_add_on</span>Message Parent
        </button>
      </div>
    </div>

    <!-- Conversations Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Parent Dialogs (<?php echo count($conversations); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Subject / Topic</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Last Message</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Last Activity</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($conversations)): ?>
              <tr><td colspan="4" class="px-4 py-8 text-center text-on-surface-variant">No parent conversations initiated yet.</td></tr>
            <?php else: ?>
              <?php foreach ($conversations as $c): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap">
                    <strong class="text-on-surface block"><?php echo html_escape($c->title ?: 'Parent Inquiry'); ?></strong>
                    <span class="text-[11px] text-on-surface-variant">Started by: <?php echo html_escape($c->creator_name ?: 'Faculty'); ?></span>
                  </td>
                  <td class="px-4 py-3 text-[13px] text-on-surface-variant line-clamp-1">
                    <?php echo html_escape($c->last_message ?: 'No messages yet.'); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[12px] text-on-surface-variant">
                    <?php echo date('d M Y, h:i A', strtotime($c->last_message_time ?: $c->created_at)); ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <a href="<?php echo site_url('communication/conversation_view/' . $c->conversation_id); ?>" class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-primary-container text-on-primary-container text-label-md font-semibold hover:bg-primary/20">
                      <span class="material-symbols-outlined text-[16px]">chat</span>Open Chat
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Start Conversation Modal -->
    <dialog id="startConvModal" class="p-0 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-3 w-full max-w-lg backdrop:bg-scrim/40">
      <div class="p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface">Start Parent Conversation</h3>
          <button onclick="document.getElementById('startConvModal').close()" class="p-1 rounded-full hover:bg-surface-container-high text-on-surface-variant">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('communication/parent_teacher'); ?>
          <div class="space-y-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Select Student / Parent *</label>
              <select name="student_id" required class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="">-- Choose Student Parent --</option>
                <?php foreach ($students as $st): ?>
                  <option value="<?php echo $st->student_id; ?>">
                    <?php echo html_escape($st->first_name . ' ' . $st->last_name . ' (' . ($st->guardian_name ?: 'Parent') . ' - ' . ($st->admission_number ?? $st->admission_no ?? '') . ')'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Subject / Topic *</label>
              <input type="text" name="title" required placeholder="e.g. Mathematics Performance & Term Prep" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Initial Message</label>
              <textarea name="message" rows="4" placeholder="Write initial message to parent..." class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-outline-variant/50">
              <button type="button" onclick="document.getElementById('startConvModal').close()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-label-md">Cancel</button>
              <button type="submit" class="px-5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">Start Dialog</button>
            </div>
          </div>
        <?php echo form_close(); ?>
      </div>
    </dialog>
