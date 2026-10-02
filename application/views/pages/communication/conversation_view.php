<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <div class="flex items-center gap-2">
          <h2 class="font-headline-md text-headline-md text-on-surface"><?php echo html_escape($conv->title ?: 'Conversation Thread'); ?></h2>
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-primary-container text-on-primary-container">
            <?php echo html_escape($conv->conversation_type); ?>
          </span>
        </div>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">
          Initiated by <strong><?php echo html_escape($conv->creator_name ?: 'Staff'); ?></strong> • <?php echo date('d M Y, h:i A', strtotime($conv->created_at)); ?>
        </p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('communication/conversations'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>Back to Chat Console
        </a>
      </div>
    </div>

    <!-- Messages Container -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6 flex flex-col min-h-[500px]">
      
      <!-- Stream -->
      <div class="flex-1 p-6 overflow-y-auto space-y-4 max-h-[460px] bg-surface-container-low/20">
        <?php if (empty($messages)): ?>
          <div class="text-center py-12 text-on-surface-variant text-body-md">No messages yet. Send a message to start!</div>
        <?php else: ?>
          <?php foreach ($messages as $m): ?>
            <?php
              $isMe = ($m->sender_type === 'Staff' && $m->sender_id == ($this->session->userdata('user_id') ?: 1));
              $senderLabel = $m->sender_staff_name ?: ($m->sender_parent_name ?: ($m->sender_student_name ?: 'Participant'));
            ?>
            <div class="flex flex-col <?php echo $isMe ? 'items-end' : 'items-start'; ?>">
              <span class="text-[11px] text-on-surface-variant mb-1 px-1 font-semibold"><?php echo html_escape($senderLabel); ?></span>
              <div class="max-w-[75%] p-4 rounded-2xl text-[14px] leading-relaxed <?php echo $isMe ? 'bg-primary text-on-primary rounded-br-none shadow-sm' : 'bg-surface-container-lowest text-on-surface border border-outline-variant/50 rounded-bl-none shadow-sm'; ?>">
                <?php echo html_escape($m->message_text); ?>
              </div>
              <span class="text-[10px] font-mono text-on-surface-variant mt-1 px-1"><?php echo date('d M Y, h:i A', strtotime($m->created_at)); ?></span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Reply Box -->
      <div class="p-4 border-t border-outline-variant/50 bg-surface-container-lowest">
        <?php echo form_open('communication/conversation_view/' . $conv->conversation_id, ['class' => 'flex items-center gap-3']); ?>
          <input type="text" name="message_text" required placeholder="Type your reply message..." class="flex-1 px-4 py-3 rounded-xl border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm"/>
          <button type="submit" class="px-6 py-3 rounded-xl bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer flex items-center gap-1.5 shrink-0">
            <span class="material-symbols-outlined text-[18px]">send</span>Send Message
          </button>
        <?php echo form_close(); ?>
      </div>
    </div>
