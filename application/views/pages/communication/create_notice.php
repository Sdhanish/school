<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Publish New Notice / Circular</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Broadcast official announcements, holiday notices, examination alerts, and emergency bulletins.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="<?php echo site_url('communication/notices'); ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>Back to Notices
        </a>
      </div>
    </div>

    <!-- Notice Form -->
    <?php echo form_open_multipart('communication/create_notice'); ?>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        
        <!-- Left: Notice Content (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
          <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
            <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2 pb-2 border-b border-outline-variant/50">
              <span class="material-symbols-outlined text-primary text-[22px]">article</span>Notice Details
            </h3>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Notice Title *</label>
              <input type="text" name="title" required placeholder="e.g. Annual Sports Day Schedule & Participation Guidelines" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary font-medium"/>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Category *</label>
                <select name="category" required class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <?php foreach (['General', 'Academic', 'Examination', 'Holiday', 'Fee', 'Attendance', 'Event', 'Emergency'] as $cat): ?>
                    <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Priority Level *</label>
                <select name="priority" required class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <option value="Normal">Normal</option>
                  <option value="Important">Important</option>
                  <option value="Urgent">Urgent</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Notice Content *</label>
              <textarea name="content" rows="6" required placeholder="Write the complete circular text, instructions, dates, and details..." class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Attachment (PDF / Image / Doc)</label>
              <input type="file" name="attachment" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-secondary-container file:text-on-secondary-container hover:file:bg-secondary file:cursor-pointer"/>
            </div>
          </div>
        </div>

        <!-- Right: Audience & Schedule (1 Col) -->
        <div class="space-y-6">
          <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
            <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2 pb-2 border-b border-outline-variant/50">
              <span class="material-symbols-outlined text-primary text-[22px]">groups</span>Target Audience
            </h3>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Target Role *</label>
              <select name="target_role" required class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="All">All Users & Parents</option>
                <option value="Parents">Parents Only</option>
                <option value="Teachers">Teachers Only</option>
                <option value="Students">Students Only</option>
                <option value="Staff">All Staff Members</option>
              </select>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Academic Scope</label>
              <select name="target_type" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="Entire School">Entire School</option>
                <option value="Class">Specific Class</option>
                <option value="Section">Specific Section</option>
              </select>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Target Class (Optional)</label>
              <select name="class_id" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="">-- All Classes --</option>
                <?php foreach ($classes as $c): ?>
                  <option value="<?php echo $c->class_id; ?>"><?php echo html_escape($c->class_name); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 space-y-4">
            <h3 class="font-headline-md text-title-md font-bold text-on-surface flex items-center gap-2 pb-2 border-b border-outline-variant/50">
              <span class="material-symbols-outlined text-primary text-[22px]">calendar_today</span>Publishing Schedule
            </h3>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Publish Date *</label>
              <input type="date" name="publish_date" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Expiry Date (Optional)</label>
              <input type="date" name="expiry_date" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
            </div>
          </div>

          <!-- Submit Actions -->
          <div class="p-4 rounded-xl bg-surface-container-lowest border border-outline-variant/50 elevation-1 flex items-center justify-between gap-3">
            <button type="submit" name="submit_action" value="draft" class="w-1/2 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface bg-surface-container-low text-label-md font-medium hover:bg-surface-container-high transition-colors cursor-pointer">
              Save Draft
            </button>
            <button type="submit" name="submit_action" value="publish" class="w-1/2 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
              Publish Notice
            </button>
          </div>
        </div>
      </div>
    <?php echo form_close(); ?>
