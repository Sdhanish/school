<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

  <div class="min-h-[70vh] flex flex-col items-center justify-center text-center">
    <div class="w-20 h-20 rounded-full bg-error-container flex items-center justify-center mb-5"><span class="material-symbols-outlined text-error text-[32px]">lock</span></div>
    <h2 class="font-headline-lg text-headline-lg text-on-surface">You don't have access to this page</h2>
    <p class="text-body-md font-body-md text-on-surface-variant mt-2 max-w-md">Your current role doesn't include permission to view this module. Contact your Super Admin if you believe this is a mistake.</p>
    <div class="flex gap-3 mt-6"><a href="<?php echo site_url('dashboard'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant bg-surface-container-lowest text-label-md text-label-md hover:bg-surface-container-high transition-colors"><span class="material-symbols-outlined text-[18px]">arrow_back</span>Back to Dashboard</a><a href="<?php echo site_url('auth/login'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm"><span class="material-symbols-outlined text-[18px]">logout</span>Log Out</a></div>
  </div>

