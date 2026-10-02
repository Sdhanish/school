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
        <h2 class="font-headline-md text-headline-md text-on-surface">SMS Templates Management</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Short message service templates with 160-character segment validation and dynamic tokens.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('communication/templates'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">list</span>All Templates
        </a>
      </div>
    </div>

    <!-- Templates Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Configured SMS Templates (<?php echo count($templates); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Template Name</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Code</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Message Template</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Char Count</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php foreach ($templates as $tmpl): ?>
              <tr class="hover:bg-surface-container-low transition-colors">
                <td class="px-4 py-3 whitespace-nowrap font-medium text-on-surface">
                  <?php echo html_escape($tmpl->template_name); ?>
                </td>
                <td class="px-4 py-3 whitespace-nowrap font-mono font-bold text-primary text-[12px]">
                  <?php echo html_escape($tmpl->template_code); ?>
                </td>
                <td class="px-4 py-3 text-[12px] text-on-surface-variant max-w-[350px]">
                  <div class="line-clamp-2 font-mono text-[11px]"><?php echo html_escape($tmpl->message_template); ?></div>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap font-mono text-xs">
                  <span class="<?php echo (mb_strlen($tmpl->message_template) > 160) ? 'text-amber-600 font-bold' : 'text-secondary font-bold'; ?>">
                    <?php echo mb_strlen($tmpl->message_template); ?> / 160
                  </span>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                  <a href="<?php echo site_url('communication/toggle_template/' . $tmpl->template_id); ?>" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold <?php echo ($tmpl->status === 'Active') ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container-high text-on-surface-variant'; ?>">
                    <?php echo html_escape($tmpl->status); ?>
                  </a>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                  <div class="flex items-center justify-center gap-1.5">
                    <button onclick="previewSMS('<?php echo html_escape(addslashes($tmpl->template_name)); ?>', '<?php echo html_escape(addslashes($tmpl->message_template)); ?>')" class="p-1 rounded hover:bg-surface-container-high text-primary font-semibold text-xs inline-flex items-center gap-1 cursor-pointer">
                      <span class="material-symbols-outlined text-[16px]">visibility</span>Preview
                    </button>
                    <a href="<?php echo site_url('communication/duplicate_template/' . $tmpl->template_id); ?>" class="p-1 rounded hover:bg-surface-container-high text-secondary font-semibold text-xs inline-flex items-center gap-1">
                      <span class="material-symbols-outlined text-[16px]">content_copy</span>Duplicate
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Preview Modal Dialog -->
    <dialog id="smsPrevModal" class="p-0 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-3 w-full max-w-md backdrop:bg-scrim/40">
      <div class="p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 id="smsPrevTitle" class="font-headline-md text-title-md font-bold text-on-surface">SMS Preview</h3>
          <button onclick="document.getElementById('smsPrevModal').close()" class="p-1 rounded-full hover:bg-surface-container-high text-on-surface-variant">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant/40 space-y-2">
          <div class="flex items-center justify-between text-xs text-on-surface-variant">
            <span>Sender ID: <strong>EDUSCH</strong></span>
            <span id="smsCharLength" class="font-mono font-bold text-secondary"></span>
          </div>
          <div id="smsRenderedText" class="p-3 bg-white rounded-lg border border-outline-variant/30 text-xs font-mono text-slate-900 whitespace-pre-wrap"></div>
        </div>

        <div class="flex items-center justify-end pt-3 border-t border-outline-variant/50">
          <button type="button" onclick="document.getElementById('smsPrevModal').close()" class="px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold">Close</button>
        </div>
      </div>
    </dialog>

    <script>
      function previewSMS(name, text) {
        document.getElementById('smsPrevTitle').innerText = name;
        fetch('<?php echo site_url("communication/preview_template"); ?>', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: 'template_text=' + encodeURIComponent(text)
        })
        .then(res => res.json())
        .then(data => {
          document.getElementById('smsRenderedText').innerText = data.compiled;
          document.getElementById('smsCharLength').innerText = data.char_count + ' Characters (' + Math.ceil(data.char_count / 160) + ' SMS segment)';
          document.getElementById('smsPrevModal').showModal();
        });
      }
    </script>
