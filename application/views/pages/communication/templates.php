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
        <h2 class="font-headline-md text-headline-md text-on-surface">Notification Template Management</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Configure multi-channel message wording, dynamic token placeholders, and validation limits.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="document.getElementById('createTemplateModal').showModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">add_circle</span>Create Template
        </button>
      </div>
    </div>

    <!-- Supported Variables Pill Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6 space-y-2">
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold text-on-surface uppercase tracking-wider flex items-center gap-1.5">
          <span class="material-symbols-outlined text-primary text-[18px]">code</span>Supported Dynamic Variables
        </span>
        <span class="text-[11px] text-on-surface-variant">Click token to copy or inspect</span>
      </div>
      <div class="flex flex-wrap gap-1.5">
        <?php foreach ($supported_vars as $v): ?>
          <span class="px-2.5 py-1 rounded-md text-xs font-mono font-bold bg-surface-container-high text-primary cursor-pointer hover:bg-primary hover:text-on-primary transition-colors select-all">
            <?php echo $v; ?>
          </span>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-4 mb-6">
      <form method="get" action="<?php echo site_url('communication/templates'); ?>" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Channel</label>
          <select name="channel" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Channels</option>
            <option value="In-App" <?php echo (($filters['channel'] ?? '') === 'In-App') ? 'selected' : ''; ?>>In-App</option>
            <option value="SMS" <?php echo (($filters['channel'] ?? '') === 'SMS') ? 'selected' : ''; ?>>SMS</option>
            <option value="WhatsApp" <?php echo (($filters['channel'] ?? '') === 'WhatsApp') ? 'selected' : ''; ?>>WhatsApp</option>
            <option value="Email" <?php echo (($filters['channel'] ?? '') === 'Email') ? 'selected' : ''; ?>>Email</option>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Category</label>
          <select name="category" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Categories</option>
            <option value="Attendance" <?php echo (($filters['category'] ?? '') === 'Attendance') ? 'selected' : ''; ?>>Attendance</option>
            <option value="Fees" <?php echo (($filters['category'] ?? '') === 'Fees') ? 'selected' : ''; ?>>Fees</option>
            <option value="Homework" <?php echo (($filters['category'] ?? '') === 'Homework') ? 'selected' : ''; ?>>Homework</option>
            <option value="Examination" <?php echo (($filters['category'] ?? '') === 'Examination') ? 'selected' : ''; ?>>Examination</option>
            <option value="Leave" <?php echo (($filters['category'] ?? '') === 'Leave') ? 'selected' : ''; ?>>Leave</option>
            <option value="Transport" <?php echo (($filters['category'] ?? '') === 'Transport') ? 'selected' : ''; ?>>Transport</option>
            <option value="Certificate" <?php echo (($filters['category'] ?? '') === 'Certificate') ? 'selected' : ''; ?>>Certificate</option>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Status</label>
          <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
            <option value="">All Statuses</option>
            <option value="Active" <?php echo (($filters['status'] ?? '') === 'Active') ? 'selected' : ''; ?>>Active</option>
            <option value="Inactive" <?php echo (($filters['status'] ?? '') === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
          </select>
        </div>

        <div>
          <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Search</label>
          <div class="flex gap-2">
            <input type="text" name="search" value="<?php echo html_escape($filters['search'] ?? ''); ?>" placeholder="Template code, name..." class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            <button type="submit" class="px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant cursor-pointer">Go</button>
          </div>
        </div>
      </form>
    </div>

    <!-- Templates Grid Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Configured Templates (<?php echo count($templates); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Template Name</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Code</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Channel</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase">Message Preview</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($templates)): ?>
              <tr><td colspan="6" class="px-4 py-8 text-center text-on-surface-variant">No notification templates found matching current filters.</td></tr>
            <?php else: ?>
              <?php foreach ($templates as $tmpl): ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap">
                    <strong class="text-on-surface block"><?php echo html_escape($tmpl->template_name); ?></strong>
                    <span class="text-[11px] text-on-surface-variant"><?php echo html_escape($tmpl->category); ?></span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono font-bold text-primary text-[12px]">
                    <?php echo html_escape($tmpl->template_code); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-primary-container text-on-primary-container font-mono">
                      <?php echo html_escape($tmpl->channel); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-[12px] text-on-surface-variant max-w-[320px]">
                    <div class="line-clamp-2 font-mono text-[11px]"><?php echo html_escape($tmpl->message_template); ?></div>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <a href="<?php echo site_url('communication/toggle_template/' . $tmpl->template_id); ?>" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold <?php echo ($tmpl->status === 'Active') ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container-high text-on-surface-variant'; ?>">
                      <?php echo html_escape($tmpl->status); ?>
                    </a>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <div class="flex items-center justify-center gap-1.5">
                      <button onclick="previewTemplateModal('<?php echo html_escape(addslashes($tmpl->template_name)); ?>', '<?php echo html_escape(addslashes($tmpl->message_template)); ?>')" class="p-1 rounded hover:bg-surface-container-high text-primary font-semibold text-xs inline-flex items-center gap-1 cursor-pointer">
                        <span class="material-symbols-outlined text-[16px]">visibility</span>Preview
                      </button>
                      <a href="<?php echo site_url('communication/duplicate_template/' . $tmpl->template_id); ?>" class="p-1 rounded hover:bg-surface-container-high text-secondary font-semibold text-xs inline-flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">content_copy</span>Duplicate
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

    <!-- Create Template Modal Dialog -->
    <dialog id="createTemplateModal" class="p-0 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-3 w-full max-w-xl backdrop:bg-scrim/40">
      <div class="p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface">Create Notification Template</h3>
          <button onclick="document.getElementById('createTemplateModal').close()" class="p-1 rounded-full hover:bg-surface-container-high text-on-surface-variant">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open('communication/templates'); ?>
          <input type="hidden" name="action" value="create"/>
          <div class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Template Name *</label>
                <input type="text" name="template_name" required placeholder="e.g. Student Absence Notice" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Template Code * (Unique)</label>
                <input type="text" name="template_code" required placeholder="ATTENDANCE_ABSENT_CUSTOM" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md uppercase font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Channel *</label>
                <select name="channel" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <option value="SMS">SMS</option>
                  <option value="WhatsApp">WhatsApp</option>
                  <option value="Email">Email</option>
                  <option value="In-App">In-App</option>
                </select>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Category</label>
                <select name="category" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <option value="Attendance">Attendance</option>
                  <option value="Fees">Fees</option>
                  <option value="Homework">Homework</option>
                  <option value="Examination">Examination</option>
                  <option value="Leave">Leave</option>
                  <option value="Transport">Transport</option>
                  <option value="Certificate">Certificate</option>
                  <option value="General">General</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Subject Line (Optional / For Email & In-App)</label>
              <input type="text" name="subject" placeholder="e.g. Notice for {student_name}" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>

            <div>
              <div class="flex items-center justify-between mb-1">
                <label class="block font-label-md text-label-md text-on-surface font-medium">Message Body *</label>
                <span id="charCounter" class="text-[11px] text-on-surface-variant font-mono">0 chars</span>
              </div>
              <textarea name="message_template" id="txtTmplBody" rows="4" required oninput="document.getElementById('charCounter').innerText = this.value.length + ' chars'" placeholder="Dear {parent_name}, your child {student_name}..." class="w-full px-3.5 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Description</label>
              <input type="text" name="description" placeholder="Usage context..." class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-outline-variant/50">
              <button type="button" onclick="document.getElementById('createTemplateModal').close()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-label-md">Cancel</button>
              <button type="submit" class="px-5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">Save Template</button>
            </div>
          </div>
        <?php echo form_close(); ?>
      </div>
    </dialog>

    <!-- Preview Modal Dialog -->
    <dialog id="previewModal" class="p-0 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-3 w-full max-w-lg backdrop:bg-scrim/40">
      <div class="p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 id="prevTitle" class="font-headline-md text-title-md font-bold text-on-surface">Template Preview</h3>
          <button onclick="document.getElementById('previewModal').close()" class="p-1 rounded-full hover:bg-surface-container-high text-on-surface-variant">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <div class="space-y-3">
          <div class="p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/40">
            <span class="text-xs font-semibold text-on-surface-variant uppercase block mb-1">Raw Template Pattern:</span>
            <div id="prevRaw" class="text-xs font-mono text-on-surface whitespace-pre-wrap"></div>
          </div>

          <div class="p-4 rounded-xl bg-secondary-container/30 border border-secondary/30">
            <span class="text-xs font-bold text-secondary uppercase block mb-1">Rendered Message (Sample Database Data):</span>
            <div id="prevRendered" class="text-sm font-sans text-on-surface whitespace-pre-wrap font-medium"></div>
          </div>
        </div>

        <div class="flex items-center justify-end pt-3 border-t border-outline-variant/50">
          <button type="button" onclick="document.getElementById('previewModal').close()" class="px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold">Close</button>
        </div>
      </div>
    </dialog>

    <script>
      function previewTemplateModal(name, rawTmpl) {
        document.getElementById('prevTitle').innerText = name + ' - Preview';
        document.getElementById('prevRaw').innerText = rawTmpl;

        fetch('<?php echo site_url("communication/preview_template"); ?>', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: 'template_text=' + encodeURIComponent(rawTmpl)
        })
        .then(res => res.json())
        .then(data => {
          document.getElementById('prevRendered').innerText = data.compiled;
          document.getElementById('previewModal').showModal();
        });
      }
    </script>
