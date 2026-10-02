<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="max-w-xl mx-auto py-4">
  <!-- Flash Messages -->
  <?php if ($this->session->flashdata('success')): ?>
    <div class="mb-5 p-4 rounded-xl bg-secondary-container/40 text-on-secondary-container text-body-md font-medium flex items-center gap-3 border border-secondary/30 shadow-sm animate-in fade-in duration-200">
      <span class="material-symbols-outlined text-[22px] text-secondary shrink-0">check_circle</span>
      <span class="flex-1"><?php echo html_escape($this->session->flashdata('success')); ?></span>
    </div>
  <?php endif; ?>

  <?php if ($this->session->flashdata('error')): ?>
    <div class="mb-5 p-4 rounded-xl bg-error-container/40 text-on-error-container text-body-md font-medium flex items-center gap-3 border border-error/30 shadow-sm animate-in fade-in duration-200">
      <span class="material-symbols-outlined text-[22px] text-error shrink-0">error</span>
      <span class="flex-1"><?php echo html_escape($this->session->flashdata('error')); ?></span>
    </div>
  <?php endif; ?>

  <!-- Card Container -->
  <div class="elevation-1 rounded-2xl bg-surface-container-lowest border border-outline-variant/60 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.05)] overflow-hidden">
    <!-- Header -->
    <div class="p-6 border-b border-outline-variant/60 bg-surface-container-low/50">
      <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 rounded-xl bg-primary-fixed/60 text-primary flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-[26px]">lock_reset</span>
        </div>
        <div>
          <h2 class="font-headline-md text-headline-sm font-bold text-on-surface">Change Password</h2>
          <p class="text-body-md text-on-surface-variant mt-0.5">Update your account password to keep your account safe</p>
        </div>
      </div>
    </div>

    <!-- Form Body -->
    <div class="p-6">
      <?php echo form_open('users/change_password', ['id' => 'page-change-password-form', 'class' => 'space-y-5', 'autocomplete' => 'off']); ?>
        
        <!-- Current Password -->
        <div>
          <label for="page-current-password" class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">
            Current Password <span class="text-error">*</span>
          </label>
          <div class="relative">
            <input type="password" id="page-current-password" name="current_password" required placeholder="Enter your current password" class="w-full pl-3.5 pr-10 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all font-mono" autocomplete="current-password" />
            <button type="button" class="page-toggle-pwd absolute right-2.5 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface p-1 rounded transition-colors" data-target="page-current-password" title="Show / Hide password">
              <span class="material-symbols-outlined text-[18px]">visibility</span>
            </button>
          </div>
        </div>

        <!-- New Password -->
        <div>
          <label for="page-new-password" class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">
            New Password <span class="text-error">*</span>
          </label>
          <div class="relative">
            <input type="password" id="page-new-password" name="new_password" required placeholder="Enter new password" class="w-full pl-3.5 pr-10 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all font-mono" autocomplete="new-password" />
            <button type="button" class="page-toggle-pwd absolute right-2.5 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface p-1 rounded transition-colors" data-target="page-new-password" title="Show / Hide password">
              <span class="material-symbols-outlined text-[18px]">visibility</span>
            </button>
          </div>

          <!-- Strength Meter -->
          <div class="mt-2 space-y-1.5">
            <div class="flex items-center justify-between text-[11px]">
              <span class="text-on-surface-variant font-medium">Password Strength:</span>
              <span id="page-strength-text" class="font-bold text-on-surface-variant">None</span>
            </div>
            <div class="grid grid-cols-4 gap-1.5 h-1.5 w-full bg-surface-container-high rounded-full overflow-hidden p-0.5">
              <div id="page-strength-bar-1" class="h-full rounded-full transition-all duration-300 bg-transparent"></div>
              <div id="page-strength-bar-2" class="h-full rounded-full transition-all duration-300 bg-transparent"></div>
              <div id="page-strength-bar-3" class="h-full rounded-full transition-all duration-300 bg-transparent"></div>
              <div id="page-strength-bar-4" class="h-full rounded-full transition-all duration-300 bg-transparent"></div>
            </div>
          </div>

          <!-- Requirements Checklist -->
          <div class="mt-3 p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/60 space-y-2 text-xs text-on-surface-variant">
            <div id="page-rule-len" class="flex items-center gap-2 transition-colors">
              <span class="material-symbols-outlined text-[16px]">radio_button_unchecked</span>
              <span>At least 8 characters long</span>
            </div>
            <div id="page-rule-num" class="flex items-center gap-2 transition-colors">
              <span class="material-symbols-outlined text-[16px]">radio_button_unchecked</span>
              <span>Contains at least 1 number (0-9)</span>
            </div>
            <div id="page-rule-special" class="flex items-center gap-2 transition-colors">
              <span class="material-symbols-outlined text-[16px]">radio_button_unchecked</span>
              <span>Contains at least 1 special character (e.g. !@#$%^&*)</span>
            </div>
            <div id="page-rule-diff" class="flex items-center gap-2 transition-colors">
              <span class="material-symbols-outlined text-[16px]">radio_button_unchecked</span>
              <span>Must not match your current password</span>
            </div>
          </div>
        </div>

        <!-- Confirm New Password -->
        <div>
          <label for="page-confirm-password" class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">
            Confirm New Password <span class="text-error">*</span>
          </label>
          <div class="relative">
            <input type="password" id="page-confirm-password" name="confirm_password" required placeholder="Confirm new password" class="w-full pl-3.5 pr-10 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all font-mono" autocomplete="new-password" />
            <button type="button" class="page-toggle-pwd absolute right-2.5 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface p-1 rounded transition-colors" data-target="page-confirm-password" title="Show / Hide password">
              <span class="material-symbols-outlined text-[18px]">visibility</span>
            </button>
          </div>
          <div id="page-match-status" class="hidden mt-1.5 text-xs font-medium flex items-center gap-1.5">
            <span id="page-match-icon" class="material-symbols-outlined text-[16px]"></span>
            <span id="page-match-text"></span>
          </div>
        </div>

        <!-- Submit & Cancel Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-outline-variant/60">
          <a href="<?php echo base_url('dashboard'); ?>" class="px-5 py-2.5 rounded-lg border border-outline-variant text-body-md font-semibold text-on-surface hover:bg-surface-container-high transition-colors inline-block text-center">
            Cancel
          </a>
          <button type="submit" id="page-submit-btn" class="px-6 py-2.5 rounded-lg bg-primary text-on-primary text-body-md font-semibold hover:bg-primary/90 shadow-sm transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">lock_reset</span>
            <span>Change Password</span>
          </button>
        </div>

      <?php echo form_close(); ?>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('page-change-password-form');
  const curInput = document.getElementById('page-current-password');
  const newInput = document.getElementById('page-new-password');
  const confInput = document.getElementById('page-confirm-password');

  // Toggle buttons
  document.querySelectorAll('.page-toggle-pwd').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      const targetId = this.getAttribute('data-target');
      const input = document.getElementById(targetId);
      if (input) {
        const isPwd = input.type === 'password';
        input.type = isPwd ? 'text' : 'password';
        const icon = this.querySelector('.material-symbols-outlined');
        if (icon) icon.textContent = isPwd ? 'visibility_off' : 'visibility';
      }
    });
  });

  function updateRule(elId, passed) {
    const el = document.getElementById(elId);
    if (!el) return;
    const icon = el.querySelector('.material-symbols-outlined');
    if (passed) {
      el.classList.remove('text-on-surface-variant');
      el.classList.add('text-emerald-600', 'font-medium');
      if (icon) {
        icon.textContent = 'check_circle';
        icon.classList.add('text-emerald-600');
        icon.classList.remove('text-on-surface-variant');
      }
    } else {
      el.classList.remove('text-emerald-600', 'font-medium');
      el.classList.add('text-on-surface-variant');
      if (icon) {
        icon.textContent = 'radio_button_unchecked';
        icon.classList.remove('text-emerald-600');
        icon.classList.add('text-on-surface-variant');
      }
    }
  }

  function renderStrength(score, len) {
    const txt = document.getElementById('page-strength-text');
    const b1 = document.getElementById('page-strength-bar-1');
    const b2 = document.getElementById('page-strength-bar-2');
    const b3 = document.getElementById('page-strength-bar-3');
    const b4 = document.getElementById('page-strength-bar-4');
    const bars = [b1, b2, b3, b4];

    bars.forEach(b => {
      b.className = 'h-full rounded-full transition-all duration-300 bg-transparent';
    });

    if (len === 0) {
      txt.textContent = 'None';
      txt.className = 'font-bold text-on-surface-variant';
      return;
    }

    if (score <= 1) {
      txt.textContent = 'Weak';
      txt.className = 'font-bold text-error';
      b1.classList.remove('bg-transparent');
      b1.classList.add('bg-error');
    } else if (score === 2) {
      txt.textContent = 'Fair';
      txt.className = 'font-bold text-amber-500';
      b1.classList.remove('bg-transparent');
      b1.classList.add('bg-amber-500');
      b2.classList.remove('bg-transparent');
      b2.classList.add('bg-amber-500');
    } else if (score === 3) {
      txt.textContent = 'Good';
      txt.className = 'font-bold text-secondary';
      b1.classList.remove('bg-transparent');
      b1.classList.add('bg-secondary');
      b2.classList.remove('bg-transparent');
      b2.classList.add('bg-secondary');
      b3.classList.remove('bg-transparent');
      b3.classList.add('bg-secondary');
    } else {
      txt.textContent = 'Strong';
      txt.className = 'font-bold text-emerald-600';
      bars.forEach(b => {
        b.classList.remove('bg-transparent');
        b.classList.add('bg-emerald-600');
      });
    }
  }

  function validateInputs() {
    const curVal = curInput.value;
    const newVal = newInput.value;
    const confVal = confInput.value;

    const hasLen = newVal.length >= 8;
    const hasNum = /[0-9]/.test(newVal);
    const hasSpecial = /[^a-zA-Z0-9]/.test(newVal);
    const isDiff = newVal.length > 0 && curVal.length > 0 && newVal !== curVal;

    updateRule('page-rule-len', hasLen);
    updateRule('page-rule-num', hasNum);
    updateRule('page-rule-special', hasSpecial);
    updateRule('page-rule-diff', isDiff || (newVal.length > 0 && curVal.length === 0));

    let score = 0;
    if (hasLen) score++;
    if (newVal.length >= 12 || (hasNum && hasSpecial)) score++;
    if (hasNum) score++;
    if (hasSpecial) score++;

    renderStrength(score, newVal.length);

    const matchStatus = document.getElementById('page-match-status');
    const matchIcon = document.getElementById('page-match-icon');
    const matchText = document.getElementById('page-match-text');

    if (confVal.length > 0) {
      matchStatus.classList.remove('hidden');
      if (newVal === confVal) {
        matchStatus.className = 'mt-1.5 text-xs font-medium flex items-center gap-1.5 text-emerald-600';
        matchIcon.textContent = 'check_circle';
        matchText.textContent = 'Passwords match';
      } else {
        matchStatus.className = 'mt-1.5 text-xs font-medium flex items-center gap-1.5 text-error';
        matchIcon.textContent = 'cancel';
        matchText.textContent = 'New password and confirm password do not match.';
      }
    } else {
      matchStatus.classList.add('hidden');
    }
  }

  curInput.addEventListener('input', validateInputs);
  newInput.addEventListener('input', validateInputs);
  confInput.addEventListener('input', validateInputs);

  form.addEventListener('submit', function(e) {
    const curVal = curInput.value.trim();
    const newVal = newInput.value;
    const confVal = confInput.value;

    if (!curVal) {
      e.preventDefault();
      alert('Current password is required.');
      curInput.focus();
      return;
    }
    if (!newVal) {
      e.preventDefault();
      alert('New password is required.');
      newInput.focus();
      return;
    }
    if (newVal === curVal) {
      e.preventDefault();
      alert('New password cannot be the same as the current password.');
      newInput.focus();
      return;
    }
    if (newVal !== confVal) {
      e.preventDefault();
      alert('New password and confirm password do not match.');
      confInput.focus();
      return;
    }
  });
});
</script>
