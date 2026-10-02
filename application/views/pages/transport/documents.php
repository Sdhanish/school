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
        <h2 class="font-headline-md text-headline-md text-on-surface">Transport Compliance Documents</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mt-1">Manage vehicle fitness, insurance, pollution certificates, road permits, and driver commercial licenses.</p>
      </div>
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <button onclick="document.getElementById('docModal').showModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">upload_file</span>Upload Document
        </button>
      </div>
    </div>

    <!-- Documents Table -->
    <div class="elevation-1 rounded-xl bg-surface-container-lowest border border-outline-variant/50 overflow-hidden mb-6">
      <div class="p-4 border-b border-outline-variant/50 flex items-center justify-between">
        <span class="text-body-md font-semibold text-on-surface">Compliance Records (<?php echo count($documents); ?>)</span>
      </div>

      <div class="table-scroll overflow-x-auto">
        <table class="w-full data-table zebra border-collapse text-body-md">
          <thead>
            <tr class="border-b border-outline-variant/60 bg-surface-container-low/50">
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Entity / Vehicle</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Document Type</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Document Number</th>
              <th class="text-left px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Expiry Date</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">Compliance Status</th>
              <th class="text-center px-4 py-3 text-label-md font-semibold text-on-surface-variant uppercase whitespace-nowrap">File</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/40">
            <?php if (empty($documents)): ?>
              <tr><td colspan="6" class="px-4 py-8 text-center text-on-surface-variant">No compliance documents uploaded yet.</td></tr>
            <?php else: ?>
              <?php foreach ($documents as $doc): ?>
                <?php
                  $stBadge = ($doc->status === 'Active') ? 'bg-secondary-container text-on-secondary-container' : (($doc->status === 'Expiring Soon') ? 'bg-amber-100 text-amber-900' : 'bg-error-container text-error');
                  $target = ($doc->entity_type === 'Vehicle') ? ($doc->vehicle_number . ' (' . $doc->registration_number . ')') : $doc->driver_name;
                ?>
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap">
                    <strong class="text-on-surface block"><?php echo html_escape($target); ?></strong>
                    <span class="text-[10px] text-on-surface-variant"><?php echo $doc->entity_type; ?> Document</span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-medium text-on-surface">
                    <?php echo html_escape($doc->document_type); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[13px] text-on-surface">
                    <?php echo html_escape($doc->document_number ?: 'N/A'); ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap font-mono text-[12px] text-on-surface">
                    <?php echo $doc->expiry_date ? date('d M Y', strtotime($doc->expiry_date)) : 'Permanent / N/A'; ?>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold <?php echo $stBadge; ?>">
                      <?php echo html_escape($doc->status); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center whitespace-nowrap">
                    <?php if ($doc->file_path): ?>
                      <a href="<?php echo base_url('uploads/transport/' . $doc->file_path); ?>" target="_blank" class="p-1 rounded hover:bg-surface-container-high text-primary font-semibold text-xs inline-flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">download</span>View
                      </a>
                    <?php else: ?>
                      <span class="text-[11px] text-on-surface-variant font-mono">No File</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Upload Document Modal Dialog -->
    <dialog id="docModal" class="p-0 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 elevation-3 w-full max-w-lg backdrop:bg-scrim/40">
      <div class="p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/50">
          <h3 class="font-headline-md text-title-md font-bold text-on-surface">Upload Compliance Document</h3>
          <button onclick="document.getElementById('docModal').close()" class="p-1 rounded-full hover:bg-surface-container-high text-on-surface-variant">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open_multipart('transport/documents'); ?>
          <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Document Target *</label>
                <select name="entity_type" id="selTargetType" onchange="toggleDocTarget(this.value)" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <option value="Vehicle">Vehicle</option>
                  <option value="Driver">Driver</option>
                </select>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Select Target *</label>
                <select name="entity_id" id="selTargetId" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                  <?php foreach ($vehicles as $v): ?>
                    <option value="<?php echo $v->vehicle_id; ?>"><?php echo html_escape($v->vehicle_number); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Document Type *</label>
              <select name="document_type" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <option value="Registration">Vehicle Registration (RC)</option>
                <option value="Insurance">Insurance Policy</option>
                <option value="Fitness Certificate">Fitness Certificate</option>
                <option value="Pollution Certificate">Pollution Under Control (PUC)</option>
                <option value="Permit">Transport Permit</option>
                <option value="Driving License">Driver Commercial License</option>
                <option value="Medical Certificate">Medical Fitness Certificate</option>
                <option value="Police Verification">Police Verification</option>
                <option value="Other">Other Document</option>
              </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Document Number</label>
                <input type="text" name="document_number" placeholder="Certificate / Policy #" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>

              <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Expiry Date</label>
                <input type="date" name="expiry_date" class="w-full px-3.5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md focus:ring-2 focus:ring-primary/20 focus:border-primary"/>
              </div>
            </div>

            <div>
              <label class="block font-label-md text-label-md text-on-surface mb-1 font-medium">Upload File</label>
              <input type="file" name="document_file" class="w-full px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-secondary-container file:text-on-secondary-container hover:file:bg-secondary file:cursor-pointer"/>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-outline-variant/50">
              <button type="button" onclick="document.getElementById('docModal').close()" class="px-4 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-high text-label-md">Cancel</button>
              <button type="submit" class="px-5 py-2 rounded-lg bg-secondary text-on-secondary text-label-md font-semibold hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">Save Document</button>
            </div>
          </div>
        <?php echo form_close(); ?>
      </div>
    </dialog>
