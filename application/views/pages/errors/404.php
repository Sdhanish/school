<!DOCTYPE html>
<html class="light" lang="en">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <title><?php echo html_escape($title ?? '404 - Page Not Found'); ?> - Login2</title>
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
                  "secondary": "#6F8FA3",
                  "primary": "#6F8FA3",
                  "sky-blue": "#357AC4",
                  "beige": "#F3EEE9",
                  "light-blue": "#EAF2F7",
                  "dark-text": "#243746",
                  "muted-text": "#607482",
                  "background": "#F7F9FB",
                  "on-background": "#243746",
                  "surface-container-lowest": "#FFFFFF",
                  "outline-variant": "#DCE4E8"
              }
          }
      }
  }
  </script>
</head>
<body class="bg-background text-on-background min-h-screen flex items-center justify-center p-4 font-['Inter']">
  <div class="max-w-md w-full text-center bg-surface-container-lowest p-8 rounded-2xl border border-outline-variant shadow-xl elevation-2">
    <div class="w-16 h-16 rounded-2xl bg-light-blue text-primary flex items-center justify-center mx-auto mb-4 border border-sky-blue/50">
      <span class="material-symbols-outlined text-[36px]">travel_explore</span>
    </div>
    <h1 class="text-3xl font-bold text-dark-text tracking-tight mb-2">404 - Page Not Found</h1>
    <p class="text-muted-text text-sm mb-6 leading-relaxed">
      <?php echo html_escape($message ?? 'The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.'); ?>
    </p>
    <div class="flex items-center justify-center gap-3">
      <a href="<?php echo site_url('dashboard'); ?>" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-primary/90 transition-all shadow-sm">
        <span class="material-symbols-outlined text-[18px]">home</span>Go to Dashboard
      </a>
      <a href="javascript:history.back()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-outline-variant text-dark-text bg-surface-container-lowest text-sm font-medium hover:bg-light-blue transition-colors">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span>Go Back
      </a>
    </div>
  </div>
</body>
</html>
