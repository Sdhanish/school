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
        <h2 class="font-headline-md text-headline-md text-on-surface">Security & Password Policies</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Configure failed login lockout thresholds, session timeouts, and password complexity enforcement.</p>
      </div>
    </div>

    <!-- Settings Form -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 p-6 max-w-2xl mb-6">
      <?php echo form_open('users/security_settings'); ?>
        <div class="space-y-6">
          
          <!-- Lockout & Sessions -->
          <div>
            <h3 class="font-headline-md text-title-md font-bold text-on-surface pb-2 border-b border-outline-variant/50 mb-4 flex items-center gap-2">
              <span class="material-symbols-outlined text-error text-[20px]">lock_clock</span>Account Lockout & Sessions
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Max Failed Login Attempts</label>
                <input type="number" name="max_failed_attempts" value="<?php echo (int)$settings->max_failed_attempts; ?>" min="3" max="20" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Lockout Duration (Mins)</label>
                <input type="number" name="lockout_duration_minutes" value="<?php echo (int)$settings->lockout_duration_minutes; ?>" min="5" max="1440" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Session Inactivity Timeout (Mins)</label>
                <input type="number" name="session_timeout_minutes" value="<?php echo (int)$settings->session_timeout_minutes; ?>" min="15" max="480" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Password Expiry (Days)</label>
                <input type="number" name="password_expiry_days" value="<?php echo (int)$settings->password_expiry_days; ?>" min="0" max="365" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>
            </div>
          </div>

          <!-- Password Complexity Rules -->
          <div>
            <h3 class="font-headline-md text-title-md font-bold text-on-surface pb-2 border-b border-outline-variant/50 mb-4 flex items-center gap-2">
              <span class="material-symbols-outlined text-secondary text-[20px]">key</span>Password Complexity
            </h3>

            <div class="space-y-3">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Minimum Password Length</label>
                <input type="number" name="password_min_length" value="<?php echo (int)$settings->password_min_length; ?>" min="6" max="32" required class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>

              <label class="flex items-center gap-3 p-3 rounded-lg bg-surface-container-low border border-outline-variant/40 cursor-pointer">
                <input type="checkbox" name="require_special_chars" value="1" <?php echo $settings->require_special_chars ? 'checked' : ''; ?> class="w-4 h-4 rounded text-secondary focus:ring-secondary"/>
                <span class="text-body-md text-on-surface">Require at least one special character (!@#$%^&*)</span>
              </label>

              <label class="flex items-center gap-3 p-3 rounded-lg bg-surface-container-low border border-outline-variant/40 cursor-pointer">
                <input type="checkbox" name="require_numbers" value="1" <?php echo $settings->require_numbers ? 'checked' : ''; ?> class="w-4 h-4 rounded text-secondary focus:ring-secondary"/>
                <span class="text-body-md text-on-surface">Require at least one numerical digit (0-9)</span>
              </label>
            </div>
          </div>

          <div class="flex items-center justify-end pt-4 border-t border-outline-variant/50">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
              <span class="material-symbols-outlined text-[18px]">save</span>Save Security Policies
            </button>
          </div>
        </div>
      <?php echo form_close(); ?>
    </div>
