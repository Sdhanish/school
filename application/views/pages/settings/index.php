<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <?php if ($this->session->flashdata('success')): ?>
      <div class="mb-4 p-3 rounded-lg bg-secondary-container text-on-secondary-container text-body-md font-body-md">
        <?php echo html_escape($this->session->flashdata('success')); ?>
      </div>
    <?php endif; ?>

    <?php echo form_open('settings', array('id' => 'settings-form')); ?>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface">Login2 Settings</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Manage your institution's core information.</p>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md text-label-md hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">check</span>Save Changes
        </button>
      </div>
    </div>
  
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden">
      <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant/50">
        <h3 class="font-headline-md text-headline-md text-on-surface">Institution Information</h3>
        <div class="flex items-center gap-2"></div>
      </div>
      <div class="p-5">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      
    <div class="">
      <label class="block font-label-md text-label-md text-on-surface mb-1.5">School Name</label>
      <input type="text" name="school_name" value="<?php echo html_escape(isset($settings->school_name) ? $settings->school_name : ''); ?>" placeholder="Login2 Public School" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
    </div>
      
    <div class="">
      <label class="block font-label-md text-label-md text-on-surface mb-1.5">School Code</label>
      <input type="text" name="school_code" value="<?php echo html_escape(isset($settings->school_code) ? $settings->school_code : ''); ?>" placeholder="SCH-KL-2026" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
    </div>
      
    <div class="">
      <label class="block font-label-md text-label-md text-on-surface mb-1.5">Established Year</label>
      <input type="text" name="established_year" value="<?php echo html_escape(isset($settings->established_year) ? $settings->established_year : ''); ?>" placeholder="1998" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
    </div>
      
    <div class="">
      <label class="block font-label-md text-label-md text-on-surface mb-1.5">Principal Name</label>
      <input type="text" name="principal_name" value="<?php echo html_escape(isset($settings->principal_name) ? $settings->principal_name : ''); ?>" placeholder="Antony Xavier" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
    </div>
      
    <div class="">
      <label class="block font-label-md text-label-md text-on-surface mb-1.5">Phone</label>
      <input type="text" name="phone" value="<?php echo html_escape(isset($settings->phone) ? $settings->phone : ''); ?>" placeholder="+91 484 234 5678" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
    </div>
      
    <div class="">
      <label class="block font-label-md text-label-md text-on-surface mb-1.5">Email</label>
      <input type="text" name="email" value="<?php echo html_escape(isset($settings->email) ? $settings->email : ''); ?>" placeholder="info@school.edu" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
    </div>
      
    <div class="">
      <label class="block font-label-md text-label-md text-on-surface mb-1.5">Website</label>
      <input type="text" name="website" value="<?php echo html_escape(isset($settings->website) ? $settings->website : ''); ?>" placeholder="www.school.edu" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
    </div>
      
    <div class="">
      <label class="block font-label-md text-label-md text-on-surface mb-1.5">School Logo</label>
      <input type="file" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
    </div>
      
    <div class="sm:col-span-2">
      <label class="block font-label-md text-label-md text-on-surface mb-1.5">Address</label>
      <input type="text" name="address" value="<?php echo html_escape(isset($settings->address) ? $settings->address : ''); ?>" placeholder="Kaloor, Ernakulam, Kerala - 682030" class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
    </div>
      
    <div class="sm:col-span-2">
      <label class="block font-label-md text-label-md text-on-surface mb-1.5">School Description</label>
      <input type="text" name="description" value="<?php echo html_escape(isset($settings->description) ? $settings->description : ''); ?>" placeholder="A CBSE-affiliated school known for academics and sports." class="w-full px-3 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-body-md focus:ring-2 focus:ring-primary/10 focus:border-primary placeholder-on-surface-variant/50"/>
    </div>
    </div>
  </div>
    </div>
    <?php echo form_close(); ?>

