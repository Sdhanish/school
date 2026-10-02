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
        <div class="flex items-center gap-2">
          <h2 class="font-headline-md text-headline-md text-on-surface">Notification Log #<?php echo $msg->message_id; ?></h2>
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold font-mono bg-primary-container text-on-primary-container">
            <?php echo $msg->channel; ?>
          </span>
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold <?php echo ($msg->status === 'Delivered') ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container-high text-on-surface-variant'; ?>">
            <?php echo $msg->status; ?>
          </span>
        </div>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Source: <?php echo html_escape($msg->source_module); ?> • Event: <?php echo html_escape($msg->event_name ?: 'Direct'); ?></p>
      </div>

      <div class="flex items-center gap-2">
        <a href="<?php echo site_url('communication/history'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>Back to History
        </a>
        <?php if ($msg->status === 'Failed'): ?>
          <a href="<?php echo site_url('communication/retry_failed/' . $msg->message_id); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">
            <span class="material-symbols-outlined text-[18px]">replay</span>Retry Notification
          </a>
        <?php endif; ?>
      </div>
    </div>

    <!-- 2 Column Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
      
      <!-- Left 2 Cols: Message Contents & Timeline -->
      <div class="lg:col-span-2 space-y-6">
        
        <!-- Rendered Message Card -->
        <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
            <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2">
              <span class="material-symbols-outlined text-primary text-[20px]">mark_email_read</span>Exact Preserved Message
            </h3>
            <span class="text-xs font-mono text-on-surface-variant font-bold"><?php echo html_escape($msg->template_code ?: 'CUSTOM_MESSAGE'); ?></span>
          </div>

          <?php if ($msg->subject): ?>
            <div>
              <span class="text-xs font-semibold text-on-surface-variant uppercase block mb-1">Subject Header:</span>
              <div class="text-body-md font-bold text-on-surface"><?php echo html_escape($msg->subject); ?></div>
            </div>
          <?php endif; ?>

          <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant/40">
            <span class="text-xs font-semibold text-on-surface-variant uppercase block mb-2">Preserved Rendered Body:</span>
            <div class="text-body-md font-sans text-on-surface whitespace-pre-wrap leading-relaxed">
              <?php echo nl2br(html_escape($msg->rendered_message ?: $msg->message)); ?>
            </div>
          </div>
        </div>

        <!-- Failure Log Card if failed -->
        <?php if ($msg->status === 'Failed' || !empty($msg->failure_reason)): ?>
          <div class="p-6 rounded-2xl bg-error-container/40 border border-error/30 elevation-1 space-y-2">
            <h3 class="font-headline-md text-title-md font-bold text-error flex items-center gap-2">
              <span class="material-symbols-outlined text-error text-[20px]">error</span>Failure Diagnostics
            </h3>
            <p class="text-xs font-mono text-on-error-container"><?php echo html_escape($msg->failure_reason); ?></p>
            <div class="text-xs text-on-surface-variant pt-1">
              Retries recorded: <strong><?php echo $msg->retry_count; ?> / <?php echo $msg->max_retries; ?></strong>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Right 1 Col: Recipient & Metadata -->
      <div class="space-y-6">
        <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface pb-3 border-b border-outline-variant/50">
            Recipient & Delivery
          </h3>

          <div class="space-y-3 text-xs">
            <div>
              <span class="text-on-surface-variant uppercase font-semibold block">Recipient Name:</span>
              <strong class="text-body-md text-on-surface block"><?php echo html_escape($msg->recipient_name); ?></strong>
            </div>

            <div>
              <span class="text-on-surface-variant uppercase font-semibold block">Contact Details:</span>
              <span class="text-body-md font-mono text-primary font-bold block"><?php echo html_escape($msg->recipient_contact); ?></span>
            </div>

            <div>
              <span class="text-on-surface-variant uppercase font-semibold block">Audience Classification:</span>
              <span class="text-on-surface font-medium block"><?php echo html_escape($msg->recipient_type); ?></span>
            </div>

            <div>
              <span class="text-on-surface-variant uppercase font-semibold block">Priority Level:</span>
              <span class="px-2 py-0.5 rounded text-[10px] font-bold <?php echo ($msg->priority === 'Urgent') ? 'bg-error-container text-error' : 'bg-surface-container-high text-on-surface-variant'; ?>">
                <?php echo html_escape($msg->priority); ?>
              </span>
            </div>

            <div class="pt-2 border-t border-outline-variant/40 space-y-2 font-mono">
              <div class="flex justify-between">
                <span class="text-on-surface-variant">Created:</span>
                <span><?php echo date('d-m-Y h:i A', strtotime($msg->created_at)); ?></span>
              </div>
              <div class="flex justify-between">
                <span class="text-on-surface-variant">Sent:</span>
                <span><?php echo $msg->sent_at ? date('d-m-Y h:i A', strtotime($msg->sent_at)) : 'N/A'; ?></span>
              </div>
              <div class="flex justify-between">
                <span class="text-on-surface-variant">Delivered:</span>
                <span><?php echo $msg->delivered_at ? date('d-m-Y h:i A', strtotime($msg->delivered_at)) : 'N/A'; ?></span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
