<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Conversations & Messaging</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Real-time messaging, inter-faculty communications, and parent dialogs.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="document.getElementById('newStaffConvModal').showModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_comment</span>New Conversation
        </button>
      </div>
    </div>

    <!-- 2-Pane Chat Layout -->
    <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden grid grid-cols-1 lg:grid-cols-12 min-h-[600px] mb-6">
      
      <!-- Left Pane: Conversation List (4 Cols) -->
      <div class="lg:col-span-4 border-r border-outline-variant/50 flex flex-col bg-surface-container-low/30">
        <div class="p-3.5 border-b border-outline-variant/50">
          <input type="text" placeholder="Search conversations..." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary text-xs"/>
        </div>

        <div class="flex-1 overflow-y-auto divide-y divide-outline-variant/30">
          <?php if (empty($conversations)): ?>
            <div class="p-6 text-center text-on-surface-variant text-xs">No active chats. Start one!</div>
          <?php else: ?>
            <?php foreach ($conversations as $c): ?>
              <?php $isActive = ($c->conversation_id == $active_id); ?>
              <a href="<?php echo site_url('communication/conversations?id=' . $c->conversation_id); ?>" class="block p-3.5 hover:bg-surface-container-high transition-colors <?php echo $isActive ? 'bg-primary-container/20 border-l-4 border-primary' : ''; ?>">
                <div class="flex items-start justify-between gap-2 mb-1">
                  <strong class="text-body-md text-on-surface font-semibold line-clamp-1 text-[13px]"><?php echo html_escape($c->title ?: 'Chat Thread'); ?></strong>
                  <span class="text-[10px] font-mono text-on-surface-variant shrink-0"><?php echo date('h:i A', strtotime($c->last_message_time ?: $c->created_at)); ?></span>
                </div>
                <p class="text-[12px] text-on-surface-variant line-clamp-1"><?php echo html_escape($c->last_message ?: 'No messages yet'); ?></p>
                <div class="flex items-center justify-between mt-1 text-[10px] text-on-surface-variant">
                  <span class="px-1.5 py-0.2 rounded bg-surface-container-high"><?php echo html_escape($c->conversation_type); ?></span>
                  <?php if (!empty($c->unread_count)): ?>
                    <span class="px-1.5 py-0.5 rounded-full bg-secondary text-on-secondary font-bold text-[9px]"><?php echo $c->unread_count; ?> new</span>
                  <?php endif; ?>
                </div>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Right Pane: Active Message Thread (8 Cols) -->
      <div class="lg:col-span-8 flex flex-col justify-between bg-surface-container-lowest">
        <?php if ($active_conv): ?>
          <!-- Header -->
          <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between bg-surface-container-low/20">
            <div>
              <h3 class="font-headline-md text-title-md font-bold text-on-surface"><?php echo html_escape($active_conv->title ?: 'Chat'); ?></h3>
              <span class="text-[11px] text-on-surface-variant">Started by: <?php echo html_escape($active_conv->creator_name ?: 'Staff'); ?> • <?php echo count($active_participants); ?> Participants</span>
            </div>
            <a href="<?php echo site_url('communication/conversation_view/' . $active_conv->conversation_id); ?>" class="text-xs text-primary font-semibold hover:underline">Full View</a>
          </div>

          <!-- Message Stream -->
          <div class="flex-1 p-6 overflow-y-auto space-y-4 max-h-[420px]">
            <?php if (empty($active_messages)): ?>
              <div class="text-center py-12 text-on-surface-variant text-xs">No messages in this conversation yet. Send the first message below!</div>
            <?php else: ?>
              <?php foreach ($active_messages as $m): ?>
                <?php
                  $isMe = ($m->sender_type === 'Staff' && $m->sender_id == ($this->session->userdata('user_id') ?: 1));
                  $senderLabel = $m->sender_staff_name ?: ($m->sender_parent_name ?: 'Participant');
                ?>
                <div class="flex flex-col <?php echo $isMe ? 'items-end' : 'items-start'; ?>">
                  <span class="text-[10px] text-on-surface-variant mb-1 px-1"><?php echo html_escape($senderLabel); ?></span>
                  <div class="max-w-[75%] p-3.5 rounded-2xl text-[13px] leading-relaxed <?php echo $isMe ? 'bg-primary text-on-primary rounded-br-none' : 'bg-surface-container-high text-on-surface rounded-bl-none'; ?>">
                    <?php echo html_escape($m->message_text); ?>
                  </div>
                  <span class="text-[10px] font-mono text-on-surface-variant mt-1 px-1"><?php echo date('d M, h:i A', strtotime($m->created_at)); ?></span>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <!-- Reply Input Bar -->
          <div class="p-4 border-t border-outline-variant/50 bg-surface-container-low/20">
            <?php echo form_open('communication/conversation_view/' . $active_conv->conversation_id, ['class' => 'flex items-center gap-2']); ?>
              <input type="text" name="message_text" required placeholder="Type your reply message..." class="flex-1 px-4 py-2.5 rounded-xl border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm"/>
              <button type="submit" class="px-5 py-2.5 rounded-xl bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">send</span>Send
              </button>
            <?php echo form_close(); ?>
          </div>
        <?php else: ?>
          <div class="flex-1 flex flex-col items-center justify-center p-8 text-center text-on-surface-variant">
            <span class="material-symbols-outlined text-[48px] text-outline-variant mb-2">forum</span>
            <p class="text-body-md font-medium">Select a conversation from the list to start reading and replying.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- New Staff Conversation Modal -->
    <dialog id="newStaffConvModal" class="p-0 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-3 w-full max-w-lg backdrop:bg-scrim/40">
      <div class="p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface">Start Internal Conversation</h3>
          <button onclick="document.getElementById('newStaffConvModal').close()" class="p-1 rounded-full hover:bg-surface-container-high text-on-surface-variant">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('communication/parent_teacher'); ?>
          <div class="space-y-4">
            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Discussion Subject *</label>
              <input type="text" name="title" required placeholder="e.g. Science Fair Planning Committee" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Initial Message *</label>
              <textarea name="message" rows="4" required placeholder="Write message to kick off discussion..." class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-outline-variant/50">
              <button type="button" onclick="document.getElementById('newStaffConvModal').close()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-label-md">Cancel</button>
              <button type="submit" class="px-5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">Start Chat</button>
            </div>
          </div>
        <?php echo form_close(); ?>
      </div>
    </dialog>
