<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Login - Login2 School Management</title>
<script src="<?php echo base_url('assets/vendor/tailwind.min.js'); ?>"></script>
<link href="<?php echo base_url('assets/fonts/material-symbols.css'); ?>" rel="stylesheet"/>
<link href="<?php echo base_url('assets/fonts/inter.css'); ?>" rel="stylesheet"/>
<link href="<?php echo base_url('assets/app.css'); ?>" rel="stylesheet"/>
<script id="tailwind-config">
tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: {
                "sky-blue": "#C8D9E6", "beige": "#F3EEE9", "white": "#FFFFFF",
                "dark-text": "#243746", "muted-text": "#607482", "theme-border": "#DCE4E8",
                "light-blue": "#EAF2F7", "dark-blue": "#357AC4", "sidebar-border": "#B8CCD9",
                "primary": "#357AC4", "primary-dark": "#2C68A8", "primary-container": "#EAF2F7", "primary-fixed": "#357AC4",
                "primary-fixed-dim": "#B8CCD9", "on-primary": "#FFFFFF", "on-primary-container": "#243746",
                "on-primary-fixed": "#FFFFFF", "on-primary-fixed-variant": "#2C68A8",
                "secondary": "#357AC4", "secondary-container": "#EAF2F7", "secondary-fixed": "#357AC4",
                "secondary-fixed-dim": "#B8CCD9", "on-secondary": "#FFFFFF", "on-secondary-container": "#243746",
                "on-secondary-fixed": "#FFFFFF", "on-secondary-fixed-variant": "#2C68A8",
                "button-primary": "#357AC4", "button-primary-hover": "#2C68A8",
                "background": "#F7F9FB", "surface": "#FFFFFF", "surface-dim": "#EAF2F7",
                "surface-bright": "#FFFFFF", "surface-tint": "#357AC4", "surface-variant": "#EAF2F7",
                "surface-container-lowest": "#FFFFFF", "surface-container-low": "#F3EEE9",
                "surface-container": "#EAF2F7", "surface-container-high": "#DCE4E8",
                "surface-container-highest": "#357AC4", "on-surface": "#243746",
                "on-surface-variant": "#607482", "on-background": "#243746",
                "outline": "#607482", "outline-variant": "#DCE4E8",
                "error": "#C5221F", "error-container": "#FCE8E6", "on-error-container": "#93000A", "on-error": "#FFFFFF"
            },
            borderRadius: { DEFAULT: "0.25rem", lg: "0.5rem", xl: "0.75rem", full: "9999px" },
            fontFamily: {
                "body-lg": ["Inter", "sans-serif"], "body-md": ["Inter", "sans-serif"], "label-md": ["Inter", "sans-serif"]
            }
        }
    }
}
</script>
<style>
  .login-page-bg {
    background-color: #EDF3F8;
    background-image: radial-gradient(#C8D9E6 1px, transparent 1px);
    background-size: 24px 24px;
  }
</style>
</head>
<body class="login-page-bg text-dark-text min-h-screen flex items-center justify-center p-4 sm:p-6 lg:p-8 font-body-lg" data-page="login">

  <!-- Main Split-Screen Container -->
  <div class="w-full max-w-[1020px] bg-white rounded-3xl shadow-[0_20px_50px_-15px_rgba(36,55,70,0.12)] border border-[#E2E8F0] overflow-hidden flex flex-col md:flex-row relative z-10">

    <!-- =====================================================================
         LEFT SIDE — SCHOOL CAMPUS VISUAL
         Clean professional illustration with students and modern school building
         ===================================================================== -->
    <div class="w-full md:w-[46%] lg:w-[48%] relative overflow-hidden bg-[#EBF2F7] shrink-0 h-52 sm:h-64 md:h-auto min-h-[220px]">
      <img 
        src="<?php echo base_url('assets/school_illustration.png'); ?>" 
        alt="Modern School Campus and Students" 
        class="w-full h-full object-cover object-center block"
        loading="eager"
      />
    </div>

    <!-- =====================================================================
         RIGHT SIDE — LOGIN FORM / AUTHENTICATION CARD
         ===================================================================== -->
    <div class="flex-1 p-6 sm:p-10 lg:p-12 flex flex-col justify-center bg-white relative">
      
      <!-- Top Branding: Logo + Management Portal Badge -->
      <div class="mb-6">
        <div class="flex items-center gap-3 mb-5">
          <img src="<?php echo base_url('assets/logo.png'); ?>" alt="Login2 IT Solutions" class="h-8 sm:h-9 w-auto object-contain"/>
          <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-primary bg-[#EAF2F8] border border-[#D5E3F0]">
            MANAGEMENT PORTAL
          </span>
        </div>

        <h1 class="text-2xl sm:text-3xl font-bold text-dark-text tracking-tight">Welcome Back</h1>
        <p class="text-xs sm:text-sm text-muted-text mt-1.5">Sign in to manage your school</p>
      </div>

      <!-- Error Messages / Flash Alerts -->
      <?php if ($this->session->flashdata('error')): ?>
        <div data-testid="login-error" class="mb-5 p-3.5 rounded-xl bg-error-container text-on-error-container border border-error/20 text-xs font-semibold flex items-center gap-2.5 shadow-2xs">
          <span class="material-symbols-outlined text-[18px] text-error shrink-0">error</span>
          <span><?php echo html_escape($this->session->flashdata('error')); ?></span>
        </div>
      <?php endif; ?>

      <?php if (validation_errors()): ?>
        <div class="mb-5 p-3.5 rounded-xl bg-error-container text-on-error-container border border-error/20 text-xs font-semibold space-y-1 shadow-2xs">
          <?php echo validation_errors('<div class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[16px] text-error shrink-0">info</span>', '</div>'); ?>
        </div>
      <?php endif; ?>

      <!-- Authentication Form -->
      <?php echo form_open('auth/login', array('id' => 'login-form', 'class' => 'space-y-4', 'data-testid' => 'login-form')); ?>
        
        <!-- Email / Username Field -->
        <div>
          <label class="block text-xs font-bold text-dark-text mb-2" for="email">Email Address or Username</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#94A3B8]">
              <span class="material-symbols-outlined text-[20px]">person</span>
            </div>
            <input 
              data-testid="login-email" 
              class="block w-full pl-11 pr-4 py-3 border border-theme-border rounded-xl bg-white text-dark-text text-sm font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all placeholder:text-[#94A3B8] outline-none" 
              id="email" 
              name="email" 
              placeholder="e.g. username" 
              required="" 
              type="text" 
              value="<?php echo set_value('email'); ?>"
              autocomplete="username"
            />
          </div>
        </div>

        <!-- Password Field with Visibility Toggle -->
        <div>
          <label class="block text-xs font-bold text-dark-text mb-2" for="password">Password</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#94A3B8]">
              <span class="material-symbols-outlined text-[20px]">lock</span>
            </div>
            <input 
              data-testid="login-password" 
              class="block w-full pl-11 pr-11 py-3 border border-theme-border rounded-xl bg-white text-dark-text text-sm font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all placeholder:text-[#94A3B8] outline-none" 
              id="password" 
              name="password" 
              placeholder="••••••••" 
              required="" 
              type="password"
              autocomplete="current-password"
            />
            <button 
              type="button" 
              onclick="togglePasswordVisibility()" 
              class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#94A3B8] hover:text-dark-text transition-colors cursor-pointer" 
              title="Show/Hide password"
              aria-label="Toggle password visibility"
            >
              <span id="pwd-toggle-icon" class="material-symbols-outlined text-[20px]">visibility</span>
            </button>
          </div>
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between pt-1">
          <label class="flex items-center gap-2 cursor-pointer select-none">
            <input class="h-4 w-4 rounded border-theme-border text-primary focus:ring-primary/40 bg-white cursor-pointer" id="remember-me" name="remember-me" type="checkbox"/>
            <span class="text-xs font-medium text-muted-text">Remember me</span>
          </label>
          <!-- <a class="text-xs font-bold text-primary hover:text-primary-dark transition-colors" href="#">
            Forgot password?
          </a> -->
        </div>

        <!-- Sign In Button -->
        <div class="pt-2">
          <button 
            id="login-submit-btn"
            data-testid="login-submit" 
            class="btn-primary w-full flex justify-center items-center gap-2 py-3 px-4 rounded-xl shadow-xs font-bold text-sm text-white bg-button-primary hover:bg-button-primary-hover active:bg-[#23548A] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-200 active:scale-[0.99] cursor-pointer disabled:opacity-75 disabled:cursor-not-allowed" 
            type="submit"
          >
            <span id="btn-text">Sign In</span>
            <span id="btn-icon" class="material-symbols-outlined text-[18px]">arrow_forward</span>
            <span id="btn-spinner" class="hidden w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
          </button>
        </div>

      <?php echo form_close(); ?>

      <!-- Footer Info -->
      <div class="mt-8 pt-5 border-t border-theme-border/60 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 text-xs text-muted-text">
        <span>© <?php echo date('Y'); ?> Login2 Management. All rights reserved.</span>
        <a class="text-primary hover:underline font-semibold" href="https://login2itsolutions.com/" target="_blank">Contact Support</a>
      </div>

    </div>

  </div>

  <script>
    function togglePasswordVisibility() {
      const pwdInput = document.getElementById('password');
      const icon = document.getElementById('pwd-toggle-icon');
      if (pwdInput.type === 'password') {
        pwdInput.type = 'text';
        icon.textContent = 'visibility_off';
      } else {
        pwdInput.type = 'password';
        icon.textContent = 'visibility';
      }
    }

    // Interactive loading state on submit
    document.getElementById('login-form')?.addEventListener('submit', function() {
      const btn = document.getElementById('login-submit-btn');
      const btnText = document.getElementById('btn-text');
      const icon = document.getElementById('btn-icon');
      const spinner = document.getElementById('btn-spinner');
      if (btn) {
        btn.disabled = true;
        if (spinner) spinner.classList.remove('hidden');
        if (icon) icon.classList.add('hidden');
      }
    });
  </script>

</body>
</html>
