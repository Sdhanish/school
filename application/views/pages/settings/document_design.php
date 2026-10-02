<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('success')): ?>
      <div class="mb-5 p-4 rounded-xl bg-secondary-container/80 border border-secondary text-on-secondary-container text-body-md flex items-center gap-3">
        <span class="material-symbols-outlined text-[22px] text-secondary">check_circle</span>
        <div><?php echo html_escape($this->session->flashdata('success')); ?></div>
      </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
      <div class="mb-5 p-4 rounded-xl bg-error-container/40 border border-error/30 text-error text-body-md flex items-center gap-3">
        <span class="material-symbols-outlined text-[22px] text-error">error</span>
        <div><?php echo html_escape($this->session->flashdata('error')); ?></div>
      </div>
    <?php endif; ?>

    <!-- Page Header & School Context Selector -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <span class="material-symbols-outlined text-primary text-[28px]">palette</span>
          <h2 class="font-headline-md text-headline-md text-on-surface">Document Design Management</h2>
        </div>
        <p class="text-body-md text-on-surface-variant max-w-3xl">
          Configure centralized school headers and footers for all printable reports, fee receipts, certificates, mark lists, and documents. Report-specific designs take precedence, falling back cleanly to the School Default design.
        </p>
      </div>
    </div>

    <?php
      $id_card_item = $designs_summary['id_card'] ?? null;
      $id_raw = $id_card_item['raw'] ?? null;
      $id_resolved = $id_card_item['resolved'] ?? null;
      $has_front = !empty($id_resolved->has_front);
      $has_back = !empty($id_resolved->has_back);
    ?>

    <!-- =========================================================================
         SECTION 1: STUDENT ID CARD DESIGN MANAGEMENT (FRONT & BACK SIDES)
         ========================================================================= -->
    <div class="mb-10 p-6 rounded-2xl bg-surface-container-lowest border border-outline-variant/60 shadow-2xs">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-5 border-b border-outline-variant/40 mb-6">
        <div>
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[26px]">badge</span>
            <h3 class="font-bold text-title-lg text-on-surface">Student ID Card Design</h3>
            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-primary/10 text-primary border border-primary/20">
              CR80 Portrait (54mm × 86mm)
            </span>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <button type="button" onclick="openFieldEditorModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-outline-variant bg-surface-container-lowest text-xs font-semibold text-on-surface hover:bg-surface-container-high transition-colors shadow-2xs cursor-pointer">
            <span class="material-symbols-outlined text-[17px]">tune</span>Configure Student Fields
          </button>
          <button type="button" onclick="previewDocModal('id_card', 'Student ID Card')" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-outline-variant bg-surface-container-lowest text-xs font-semibold text-on-surface hover:bg-surface-container-high transition-colors shadow-2xs cursor-pointer">
            <span class="material-symbols-outlined text-[17px]">preview</span>Preview Full ID Card
          </button>
        </div>
      </div>

      <!-- Front and Back Side Cards Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- ================= FRONT SIDE ================= -->
        <div class="rounded-xl border border-outline-variant/60 bg-surface-container-low/20 p-5 flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between mb-3">
              <h4 class="font-bold text-sm text-on-surface">Student ID Card - Front</h4>
            </div>

            <!-- Thumbnail Container with CR80 Aspect Ratio -->
            <div class="w-full flex items-center justify-center p-4 bg-surface-container-low rounded-xl border border-dashed border-outline-variant/80 mb-4">
              <div class="relative w-[130px] h-[206px] rounded-lg overflow-hidden border border-outline-variant shadow-xs bg-white flex items-center justify-center">
                <?php if ($has_front): ?>
                  <img src="<?php echo html_escape($id_resolved->front_url); ?>" alt="Front Design" class="w-full h-full object-cover" />
                  <div class="absolute inset-0 bg-dark-text/40 opacity-0 hover:opacity-100 transition-opacity flex items-center justify-center">
                    <a href="<?php echo html_escape($id_resolved->front_url); ?>" target="_blank" class="px-2.5 py-1 rounded bg-white text-dark-text text-[11px] font-bold shadow-xs flex items-center gap-1">
                      <span class="material-symbols-outlined text-[13px]">visibility</span>View
                    </a>
                  </div>
                <?php else: ?>
                  <div class="text-center p-3 text-on-surface-variant/50">
                    <span class="material-symbols-outlined text-[28px] block mx-auto mb-1">badge</span>
                    <span class="text-[11px] block leading-tight">Default Template Active</span>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="pt-3 border-t border-outline-variant/40 flex items-center justify-between gap-2">
            <button type="button" onclick="previewDocModal('id_card', 'Student ID Card - Front')" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-outline-variant text-xs font-semibold text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer">
              <span class="material-symbols-outlined text-[15px]">visibility</span>Preview
            </button>

            <div class="flex items-center gap-2">
              <?php if (!empty($id_raw->header_image)): ?>
                <button type="button" onclick="removeImage('id_card', 'front')" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-error hover:bg-error/10 transition-colors cursor-pointer">
                  Remove
                </button>
              <?php endif; ?>
              <button type="button" onclick="openUploadModal('id_card', 'Student ID Card', '<?php echo $id_raw ? html_escape($id_raw->status) : 'Active'; ?>', 'front')" class="inline-flex items-center gap-1 px-3.5 py-1.5 rounded-lg bg-secondary text-white text-xs font-semibold hover:bg-secondary/90 transition-colors shadow-2xs cursor-pointer">
                <span class="material-symbols-outlined text-[15px]">upload</span><?php echo $has_front ? 'Replace Design' : 'Upload Design'; ?>
              </button>
            </div>
          </div>
        </div>

        <!-- ================= BACK SIDE ================= -->
        <div class="rounded-xl border border-outline-variant/60 bg-surface-container-low/20 p-5 flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between mb-3">
              <h4 class="font-bold text-sm text-on-surface">Student ID Card - Back</h4>
            </div>

            <!-- Thumbnail Container with CR80 Aspect Ratio -->
            <div class="w-full flex items-center justify-center p-4 bg-surface-container-low rounded-xl border border-dashed border-outline-variant/80 mb-4">
              <div class="relative w-[130px] h-[206px] rounded-lg overflow-hidden border border-outline-variant shadow-xs bg-white flex items-center justify-center">
                <?php if ($has_back): ?>
                  <img src="<?php echo html_escape($id_resolved->back_url); ?>" alt="Back Design" class="w-full h-full object-cover" />
                  <div class="absolute inset-0 bg-dark-text/40 opacity-0 hover:opacity-100 transition-opacity flex items-center justify-center">
                    <a href="<?php echo html_escape($id_resolved->back_url); ?>" target="_blank" class="px-2.5 py-1 rounded bg-white text-dark-text text-[11px] font-bold shadow-xs flex items-center gap-1">
                      <span class="material-symbols-outlined text-[13px]">visibility</span>View
                    </a>
                  </div>
                <?php else: ?>
                  <div class="text-center p-3 text-on-surface-variant/50">
                    <span class="material-symbols-outlined text-[28px] block mx-auto mb-1">badge</span>
                    <span class="text-[11px] block leading-tight">Default Template Active</span>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="pt-3 border-t border-outline-variant/40 flex items-center justify-between gap-2">
            <button type="button" onclick="previewDocModal('id_card', 'Student ID Card - Back')" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-outline-variant text-xs font-semibold text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer">
              <span class="material-symbols-outlined text-[15px]">visibility</span>Preview
            </button>

            <div class="flex items-center gap-2">
              <?php if (!empty($id_raw->footer_image)): ?>
                <button type="button" onclick="removeImage('id_card', 'back')" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-error hover:bg-error/10 transition-colors cursor-pointer">
                  Remove
                </button>
              <?php endif; ?>
              <button type="button" onclick="openUploadModal('id_card', 'Student ID Card', '<?php echo $id_raw ? html_escape($id_raw->status) : 'Active'; ?>', 'back')" class="inline-flex items-center gap-1 px-3.5 py-1.5 rounded-lg bg-secondary text-white text-xs font-semibold hover:bg-secondary/90 transition-colors shadow-2xs cursor-pointer">
                <span class="material-symbols-outlined text-[15px]">upload</span><?php echo $has_back ? 'Replace Design' : 'Upload Design'; ?>
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- =========================================================================
         SECTION 2: DOCUMENT LETTERHEAD & REPORT DESIGNS (A4 BANNERS)
         ========================================================================= -->
    <div class="mb-4 flex items-center gap-2">
      <span class="material-symbols-outlined text-primary text-[22px]">description</span>
      <h3 class="font-bold text-title-md text-on-surface">Document Letterhead & Report Designs</h3>
    </div>

    <!-- Document Designs Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 mb-8">
      <?php foreach ($designs_summary as $doc_key => $item): 
        if ($doc_key === 'id_card') continue; // Handled in dedicated section above
        $meta = $item['meta'];
        $raw = $item['raw'];
        $resolved = $item['resolved'];
        $is_default = ($doc_key === 'default');
      ?>
        <div class="rounded-2xl bg-surface-container-lowest border border-outline-variant/60 shadow-2xs hover:shadow-sm hover:border-outline-variant/90 flex flex-col justify-between overflow-hidden transition-all">
          
          <!-- Card Header -->
          <div class="px-5 py-3.5 border-b border-outline-variant/40 bg-surface-container-low/20">
            <h3 class="font-semibold text-[15px] text-on-surface tracking-tight"><?php echo html_escape($meta['label']); ?></h3>
          </div>

          <!-- Thumbnails Section -->
          <div class="p-5 space-y-4 grow">
            <!-- Header Preview -->
            <div>
              <div class="flex items-center justify-between text-xs mb-1.5">
                <span class="font-medium text-on-surface">Header Image</span>
                <div class="flex items-center gap-2">
                  <?php if ($resolved->is_fallback_header): ?>
                    <span class="text-[11px] text-on-surface-variant/60 italic lowercase">(from default)</span>
                  <?php endif; ?>
                  <?php if ($raw && !empty($raw->header_image)): ?>
                    <button type="button" onclick="removeImage('<?php echo $doc_key; ?>', 'header')" class="text-error hover:underline text-xs font-semibold cursor-pointer">Remove</button>
                  <?php endif; ?>
                </div>
              </div>

              <div class="h-20 w-full rounded-xl border border-dashed border-outline-variant/80 bg-surface-container-low flex items-center justify-center overflow-hidden p-1.5 relative group">
                <?php if ($resolved->has_header): ?>
                  <img src="<?php echo html_escape($resolved->header_url); ?>" alt="Header preview" class="max-h-full max-w-full object-contain mx-auto" />
                  <div class="absolute inset-0 bg-dark-text/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <a href="<?php echo html_escape($resolved->header_url); ?>" target="_blank" class="px-2.5 py-1 rounded-lg bg-white/90 text-dark-text text-[11px] font-bold shadow-xs hover:bg-white flex items-center gap-1">
                      <span class="material-symbols-outlined text-[14px]">visibility</span>Full View
                    </a>
                  </div>
                <?php else: ?>
                  <div class="text-center text-on-surface-variant/40">
                    <span class="material-symbols-outlined text-[18px] block mx-auto mb-0.5">image_not_supported</span>
                    <span class="text-[10px]">No header image</span>
                  </div>
                <?php endif; ?>
              </div>
            </div>

            <!-- Footer Preview -->
            <div>
              <div class="flex items-center justify-between text-xs mb-1.5">
                <span class="font-medium text-on-surface">Footer Image</span>
                <div class="flex items-center gap-2">
                  <?php if ($resolved->is_fallback_footer): ?>
                    <span class="text-[11px] text-on-surface-variant/60 italic lowercase">(from default)</span>
                  <?php endif; ?>
                  <?php if ($raw && !empty($raw->footer_image)): ?>
                    <button type="button" onclick="removeImage('<?php echo $doc_key; ?>', 'footer')" class="text-error hover:underline text-xs font-semibold cursor-pointer">Remove</button>
                  <?php endif; ?>
                </div>
              </div>

              <div class="h-16 w-full rounded-xl border border-dashed border-outline-variant/80 bg-surface-container-low flex items-center justify-center overflow-hidden p-1.5 relative group">
                <?php if ($resolved->has_footer): ?>
                  <img src="<?php echo html_escape($resolved->footer_url); ?>" alt="Footer preview" class="max-h-full max-w-full object-contain mx-auto" />
                  <div class="absolute inset-0 bg-dark-text/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <a href="<?php echo html_escape($resolved->footer_url); ?>" target="_blank" class="px-2.5 py-1 rounded-lg bg-white/90 text-dark-text text-[11px] font-bold shadow-xs hover:bg-white flex items-center gap-1">
                      <span class="material-symbols-outlined text-[14px]">visibility</span>Full View
                    </a>
                  </div>
                <?php else: ?>
                  <div class="text-center text-on-surface-variant/40">
                    <span class="material-symbols-outlined text-[18px] block mx-auto mb-0.5">image_not_supported</span>
                    <span class="text-[10px]">No footer image</span>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Card Actions Footer -->
          <div class="p-4 bg-surface-container-low/30 border-t border-outline-variant/40 flex items-center justify-between gap-2">
            <button type="button" onclick="previewDocModal('<?php echo $doc_key; ?>', '<?php echo html_escape($meta['label']); ?>')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-xs font-semibold text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer">
              <span class="material-symbols-outlined text-[16px]">visibility</span>Preview Layout
            </button>

            <button type="button" onclick="openUploadModal('<?php echo $doc_key; ?>', '<?php echo html_escape($meta['label']); ?>', '<?php echo $raw ? html_escape($raw->status) : 'Active'; ?>')" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-secondary text-white text-xs font-semibold hover:bg-secondary/90 transition-colors shadow-2xs cursor-pointer">              <span class="material-symbols-outlined text-[16px]">upload</span>Configure / Upload
            </button>
          </div>

        </div>
      <?php endforeach; ?>
    </div>

    <!-- =========================================================================
         UPLOAD / CONFIGURE MODAL
         ========================================================================= -->
    <div id="upload_modal" class="fixed inset-0 z-50 hidden bg-dark-text/50 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-xl w-full max-w-lg overflow-hidden flex flex-col">
        <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/50">
          <div class="flex items-center gap-2.5">
            <span class="material-symbols-outlined text-primary text-[24px]">cloud_upload</span>
            <h3 id="upload_modal_title" class="font-bold text-title-md text-on-surface">Configure Document Design</h3>
          </div>
          <button type="button" onclick="closeUploadModal()" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <?php echo form_open_multipart('settings/document_design_save', ['id' => 'design_upload_form', 'class' => 'p-6 space-y-5']); ?>
          <input type="hidden" name="school_id" value="<?php echo $target_school_id; ?>" />
          <input type="hidden" name="document_type" id="modal_document_type" value="" />

          <!-- Info banner -->
          <div id="modal_info_banner" class="p-3 rounded-lg bg-surface-container-low text-xs text-on-surface-variant border border-outline-variant/40">
            Allowed formats: <strong class="text-on-surface">PNG, JPG, WEBP</strong>. Fixed crop dimensions:
            <strong class="text-on-surface">Header: 2480 × 561 px</strong> | <strong class="text-on-surface">Footer: 3539 × 400 px</strong>.
          </div>

          <!-- Header / Front Upload Section -->
          <div class="space-y-2" id="section_header_upload">
            <div class="flex items-center justify-between">
              <label id="modal_header_label" class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                Header Image
              </label>
              <span id="modal_header_badge" class="text-[11px] font-semibold text-primary bg-primary/10 px-2 py-0.5 rounded-md">
                Required: 2480 × 561 px
              </span>
            </div>
            <input type="file" id="header_file_input" name="header_image" accept="image/png, image/jpeg, image/webp" class="w-full text-xs text-on-surface file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-secondary file:text-white hover:file:bg-secondary/90 file:cursor-pointer border border-outline-variant rounded-lg bg-surface-container-low" onchange="handleDocumentImageSelect(this, 'header')" />
            <input type="hidden" name="header_image_cropped" id="header_image_cropped" value="" />

            <!-- Cropped Status & Preview Box -->
            <div id="header_cropped_preview_wrap" class="hidden p-2.5 rounded-xl border border-secondary/40 bg-surface-container-low flex items-center justify-between gap-3">
              <div class="flex items-center gap-2.5 overflow-hidden">
                <img id="header_cropped_preview_img" src="" alt="Cropped preview" class="h-12 w-auto max-w-[140px] rounded border border-outline-variant object-contain bg-white shrink-0" />
                <div class="truncate">
                  <div class="flex items-center gap-1 text-xs font-bold text-secondary">
                    <span class="material-symbols-outlined text-[16px]">check_circle</span>
                    <span>Cropped & Ready</span>
                  </div>
                  <div id="header_cropped_dim_text" class="text-[11px] text-on-surface-variant">Normalized to required size</div>
                </div>
              </div>
              <div class="flex items-center gap-1.5 shrink-0">
                <button type="button" onclick="recropImage('header')" class="px-2.5 py-1 rounded-lg border border-outline-variant text-xs font-semibold text-on-surface hover:bg-surface-container-high transition-colors cursor-pointer">
                  Re-crop
                </button>
                <button type="button" onclick="clearCroppedImage('header')" class="px-2.5 py-1 rounded-lg text-error hover:bg-error/10 text-xs font-semibold transition-colors cursor-pointer">
                  Remove
                </button>
              </div>
            </div>
          </div>

          <!-- Footer / Back Upload Section -->
          <div class="space-y-2" id="section_footer_upload">
            <div class="flex items-center justify-between">
              <label id="modal_footer_label" class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                Footer Image
              </label>
              <span id="modal_footer_badge" class="text-[11px] font-semibold text-primary bg-primary/10 px-2 py-0.5 rounded-md">
                Required: 3539 × 400 px
              </span>
            </div>
            <input type="file" id="footer_file_input" name="footer_image" accept="image/png, image/jpeg, image/webp" class="w-full text-xs text-on-surface file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-secondary file:text-white hover:file:bg-secondary/90 file:cursor-pointer border border-outline-variant rounded-lg bg-surface-container-low" onchange="handleDocumentImageSelect(this, 'footer')" />
            <input type="hidden" name="footer_image_cropped" id="footer_image_cropped" value="" />

            <!-- Cropped Status & Preview Box -->
            <div id="footer_cropped_preview_wrap" class="hidden p-2.5 rounded-xl border border-secondary/40 bg-surface-container-low flex items-center justify-between gap-3">
              <div class="flex items-center gap-2.5 overflow-hidden">
                <img id="footer_cropped_preview_img" src="" alt="Cropped preview" class="h-12 w-auto max-w-[140px] rounded border border-outline-variant object-contain bg-white shrink-0" />
                <div class="truncate">
                  <div class="flex items-center gap-1 text-xs font-bold text-secondary">
                    <span class="material-symbols-outlined text-[16px]">check_circle</span>
                    <span>Cropped & Ready</span>
                  </div>
                  <div id="footer_cropped_dim_text" class="text-[11px] text-on-surface-variant">Normalized to required size</div>
                </div>
              </div>
              <div class="flex items-center gap-1.5 shrink-0">
                <button type="button" onclick="recropImage('footer')" class="px-2.5 py-1 rounded-lg border border-outline-variant text-xs font-semibold text-on-surface hover:bg-surface-container-high transition-colors cursor-pointer">
                  Re-crop
                </button>
                <button type="button" onclick="clearCroppedImage('footer')" class="px-2.5 py-1 rounded-lg text-error hover:bg-error/10 text-xs font-semibold transition-colors cursor-pointer">
                  Remove
                </button>
              </div>
            </div>
          </div>

          <!-- Status -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">Design Status</label>
            <select name="status" id="modal_status" class="w-full text-xs font-semibold px-3 py-2 rounded-lg border border-outline-variant bg-surface-container-low text-on-surface focus:ring-2 focus:ring-primary">
              <option value="Active">Active (Apply this design)</option>
              <option value="Inactive">Inactive (Temporarily disable)</option>
            </select>
          </div>

          <!-- Action buttons -->
          <div class="pt-3 border-t border-outline-variant/40 flex items-center justify-end gap-2.5">
            <button type="button" onclick="closeUploadModal()" class="px-4 py-2 rounded-lg border border-outline-variant text-label-md font-semibold text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer">Cancel</button>
            <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2 rounded-lg bg-secondary text-white text-label-md font-semibold hover:bg-secondary/90 transition-colors shadow-2xs cursor-pointer">
              <span class="material-symbols-outlined text-[18px]">save</span>Save Design
            </button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>

    <!-- =========================================================================
         FIXED DIMENSION CROPPER MODAL (EXCLUSIVE MODAL STATE)
         ========================================================================= -->
    <div id="doc_crop_modal" class="fixed inset-0 z-50 hidden bg-dark-text/80 backdrop-blur-sm flex items-center justify-center p-3 sm:p-5" style="z-index: 1050;">
      <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-2xl w-[92vw] max-w-6xl h-[88vh] max-h-[92vh] flex flex-col overflow-hidden">
        <!-- Crop Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/50 shrink-0 bg-surface-container-low/40">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
              <span class="material-symbols-outlined text-[24px]">crop</span>
            </div>
            <div>
              <div class="flex items-center gap-2.5">
                <h3 id="crop_modal_title" class="font-bold text-title-md text-on-surface">Crop Image</h3>
                <span id="crop_modal_badge" class="text-xs font-bold text-primary bg-primary/10 border border-primary/20 px-2.5 py-0.5 rounded-full">
                  2480 × 561 px
                </span>
              </div>
              <p id="crop_modal_subtitle" class="text-xs text-on-surface-variant mt-0.5">
                Drag to reposition or resize the crop box. Aspect ratio is locked to the required document dimensions.
              </p>
            </div>
          </div>
          <button type="button" onclick="cancelCrop()" class="p-2 rounded-xl hover:bg-surface-container-high text-on-surface-variant cursor-pointer transition-colors" title="Cancel Crop">
            <span class="material-symbols-outlined text-[22px]">close</span>
          </button>
        </div>

        <!-- Crop Modal Body -->
        <div class="p-4 sm:p-5 flex flex-col gap-3 sm:gap-4 overflow-hidden grow min-h-0 bg-surface-container-low/10">
          <!-- Main Cropper Canvas Container -->
          <div class="grow min-h-[260px] w-full bg-dark-text/95 rounded-xl overflow-hidden flex items-center justify-center relative select-none shadow-inner border border-outline-variant/30">
            <img id="doc_cropper_image" src="" alt="To crop" class="max-w-full block" />
          </div>

          <!-- Controls & Live Preview Bar -->
          <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-2.5 bg-surface-container-lowest border border-outline-variant/50 rounded-xl shrink-0">
            <div class="flex items-center gap-2">
              <span class="text-xs font-semibold text-on-surface-variant mr-1">Controls:</span>
              <button type="button" onclick="cropperZoom(0.1)" title="Zoom In" class="px-3 py-1.5 rounded-lg border border-outline-variant hover:bg-surface-container-high text-on-surface text-xs font-medium transition-colors cursor-pointer flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">zoom_in</span>
                <span class="hidden sm:inline">Zoom In</span>
              </button>
              <button type="button" onclick="cropperZoom(-0.1)" title="Zoom Out" class="px-3 py-1.5 rounded-lg border border-outline-variant hover:bg-surface-container-high text-on-surface text-xs font-medium transition-colors cursor-pointer flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">zoom_out</span>
                <span class="hidden sm:inline">Zoom Out</span>
              </button>
              <div class="h-5 w-px bg-outline-variant/60 mx-1"></div>
              <button type="button" onclick="cropperRotate(-90)" title="Rotate Left 90°" class="px-3 py-1.5 rounded-lg border border-outline-variant hover:bg-surface-container-high text-on-surface text-xs font-medium transition-colors cursor-pointer flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">rotate_left</span>
                <span class="hidden sm:inline">-90°</span>
              </button>
              <button type="button" onclick="cropperRotate(90)" title="Rotate Right 90°" class="px-3 py-1.5 rounded-lg border border-outline-variant hover:bg-surface-container-high text-on-surface text-xs font-medium transition-colors cursor-pointer flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">rotate_right</span>
                <span class="hidden sm:inline">+90°</span>
              </button>
              <div class="h-5 w-px bg-outline-variant/60 mx-1"></div>
              <button type="button" onclick="cropperReset()" title="Reset Crop & View" class="px-3 py-1.5 rounded-lg border border-outline-variant hover:bg-surface-container-high text-on-surface text-xs font-semibold transition-colors cursor-pointer flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                <span>Reset</span>
              </button>
            </div>

            <!-- Live Preview -->
            <div class="flex items-center gap-2.5">
              <span class="text-xs font-semibold text-on-surface-variant">Live Preview:</span>
              <div id="crop_live_preview" class="overflow-hidden rounded-lg border border-outline-variant bg-white shadow-xs" style="width: 160px; height: 36px;"></div>
            </div>
          </div>
        </div>

        <!-- Crop Modal Footer -->
        <div class="px-6 py-3.5 bg-surface-container-low/50 border-t border-outline-variant/50 flex items-center justify-between shrink-0">
          <button type="button" onclick="cropperReset()" class="px-4 py-2 rounded-xl border border-outline-variant text-xs font-semibold text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[16px]">refresh</span>Reset
          </button>
          <div class="flex items-center gap-3">
            <button type="button" onclick="cancelCrop()" class="px-4 py-2 rounded-xl border border-outline-variant text-xs font-semibold text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer">
              Cancel
            </button>
            <button type="button" onclick="applyCrop()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-secondary text-white text-xs font-semibold hover:bg-secondary/90 transition-colors shadow-sm cursor-pointer">
              <span class="material-symbols-outlined text-[18px]">check</span>Apply Crop
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- =========================================================================
         REMOVE IMAGE CONFIRMATION FORM (HIDDEN)
         ========================================================================= -->
    <form id="remove_image_form" method="post" action="<?php echo site_url('settings/document_design_remove_image'); ?>" style="display:none;">
      <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
      <input type="hidden" name="school_id" value="<?php echo $target_school_id; ?>" />
      <input type="hidden" name="document_type" id="remove_doc_type" value="" />
      <input type="hidden" name="image_type" id="remove_img_type" value="" />
    </form>

    <!-- =========================================================================
         DOCUMENT DESIGN PREVIEW MODAL
         ========================================================================= -->
    <div id="preview_modal" class="fixed inset-0 z-50 hidden bg-dark-text/60 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-2xl w-full max-w-4xl h-[90vh] flex flex-col overflow-hidden">
        <div class="flex items-center justify-between px-6 py-3.5 border-b border-outline-variant/50 shrink-0 bg-surface-container-low/50">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[22px]">preview</span>
            <h3 id="preview_modal_title" class="font-bold text-title-md text-on-surface">Design Preview</h3>
          </div>
          <button type="button" onclick="closePreviewModal()" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>
        <div class="grow bg-surface-container-low/20 overflow-hidden">
          <iframe id="preview_iframe" src="about:blank" class="w-full h-full border-none"></iframe>
        </div>
      </div>
    </div>

    <!-- =========================================================================
         STUDENT ID CARD DYNAMIC FIELD POSITION EDITOR MODAL
         ========================================================================= -->
    <div id="field_editor_modal" class="fixed inset-0 z-50 hidden bg-dark-text/60 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-2xl w-full max-w-5xl h-[92vh] flex flex-col overflow-hidden">
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/50 shrink-0 bg-surface-container-low/50">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[22px]">tune</span>
            <div>
              <h3 class="font-bold text-title-md text-on-surface">Configure Student ID Card Dynamic Fields</h3>
              <p class="text-xs text-on-surface-variant">Position and style the dynamic student data overlaid on your school's ID card design.</p>
            </div>
          </div>
          <button type="button" onclick="closeFieldEditorModal()" class="p-1 rounded-lg hover:bg-surface-container-high text-on-surface-variant cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <!-- Modal Body (Two-Column Layout) -->
        <div class="grow flex flex-col md:flex-row overflow-hidden bg-surface-container-low/20">
          
          <!-- Left Column: Interactive Live Card Preview Stage -->
          <div class="w-full md:w-[380px] p-6 flex flex-col items-center justify-center border-b md:border-b-0 md:border-r border-outline-variant/50 shrink-0 bg-surface-container-low/40">
            <div class="text-xs font-bold text-on-surface-variant mb-3 flex items-center gap-1.5">
              <span class="material-symbols-outlined text-[16px]">visibility</span>
              Live Design Preview (CR80 Portrait)
            </div>

            <!-- Card Container (280px × 445px standard scale) -->
            <div id="editor-card-stage" class="relative select-none shadow-xl bg-white" style="width: 280px; height: 445px; aspect-ratio: 2.125 / 3.375; border-radius: 18px; overflow: hidden; border: 1px solid #cbd5e1;">
              
              <!-- Lanyard Hole Slot -->
              <div class="absolute top-2 left-1/2 -translate-x-1/2 w-12 h-2 bg-white/90 border border-slate-300 rounded-full z-20 shadow-inner pointer-events-none"></div>

              <!-- Background Image / SVG -->
              <?php if ($has_front): ?>
                <img src="<?php echo html_escape($id_resolved->front_url); ?>" alt="Front Design" class="absolute inset-0 w-full h-full object-cover z-0 pointer-events-none" />
              <?php else: ?>
                <svg class="absolute inset-0 w-full h-full pointer-events-none z-0" viewBox="0 0 280 445" fill="none">
                  <rect width="280" height="445" fill="#f8fafc"/>
                  <path d="M0 0 H280 V118 L0 262 Z" fill="#0d9488" opacity="0.3"/>
                  <path d="M0 0 H100 L0 246 Z" fill="#1e293b" opacity="0.2"/>
                </svg>
              <?php endif; ?>

              <!-- Dynamic Overlaid Fields -->
              <div id="editor-overlay-wrap" class="absolute inset-0 w-full h-full z-10 pointer-events-none">
                
                <!-- Field: Photo -->
                <div id="prev-field-photo" class="absolute flex items-center justify-center bg-white shadow-md border border-slate-300 rounded-xl overflow-hidden transition-all duration-100">
                  <div class="w-full h-full bg-gradient-to-br from-teal-700 to-teal-900 text-white flex items-center justify-center font-bold text-sm">
                    PHOTO
                  </div>
                </div>

                <!-- Field: Student Name -->
                <div id="prev-field-student_name" class="absolute leading-tight transition-all duration-100 font-bold text-slate-900 text-center uppercase tracking-wide">
                  <div class="truncate">JOHN DOE</div>
                  <div style="font-size: 6.5pt; font-weight: 800; color: #0d9488; opacity: 0.85; letter-spacing: 0.5px; margin-top: 1px;">STUDENT</div>
                </div>

                <!-- Field: Student Details (Structured Aligned Table) -->
                <div id="prev-field-student_details" class="absolute pointer-events-none transition-all duration-100" style="top: 61%; left: 7%; width: 86%; box-sizing: border-box; padding-left: 10px;">
                  <table class="w-full border-collapse" style="table-layout: fixed; font-size: 7pt; line-height: 1.5; text-align: left;">
                    <tr id="prev-row-admission_number">
                      <td id="prev-lbl-admission_number" class="prev-lbl-col font-bold text-teal-700 truncate" style="width: 36%;">ID</td>
                      <td class="w-2.5 text-center text-slate-400 font-bold">:</td>
                      <td class="prev-val-col font-mono font-bold text-slate-900 truncate pl-1">SCH20260115467</td>
                    </tr>
                    <tr id="prev-row-guardian_name">
                      <td id="prev-lbl-guardian_name" class="prev-lbl-col font-bold text-teal-700 truncate" style="width: 36%;">FATHER'S NAME</td>
                      <td class="w-2.5 text-center text-slate-400 font-bold">:</td>
                      <td class="prev-val-col font-bold text-slate-800 truncate pl-1">Mr. Menon</td>
                    </tr>
                    <tr id="prev-row-class_division">
                      <td id="prev-lbl-class_division" class="prev-lbl-col font-bold text-teal-700 truncate" style="width: 36%;">CLASS & DIV</td>
                      <td class="w-2.5 text-center text-slate-400 font-bold">:</td>
                      <td class="prev-val-col font-bold text-slate-800 truncate pl-1">LKG - A</td>
                    </tr>
                    <tr id="prev-row-roll_number">
                      <td id="prev-lbl-roll_number" class="prev-lbl-col font-bold text-teal-700 truncate" style="width: 36%;">ROLL NO.</td>
                      <td class="w-2.5 text-center text-slate-400 font-bold">:</td>
                      <td class="prev-val-col font-mono font-bold text-slate-800 truncate pl-1">1</td>
                    </tr>
                    <tr id="prev-row-date_of_birth">
                      <td id="prev-lbl-date_of_birth" class="prev-lbl-col font-bold text-teal-700 truncate" style="width: 36%;">D.O.B</td>
                      <td class="w-2.5 text-center text-slate-400 font-bold">:</td>
                      <td class="prev-val-col font-bold text-slate-800 truncate pl-1">11-03-2022</td>
                    </tr>
                    <tr id="prev-row-blood_group">
                      <td id="prev-lbl-blood_group" class="prev-lbl-col font-bold text-teal-700 truncate" style="width: 36%;">BLOOD GROUP</td>
                      <td class="w-2.5 text-center text-slate-400 font-bold">:</td>
                      <td class="prev-val-col font-bold text-rose-600 truncate pl-1">A+</td>
                    </tr>
                  </table>
                </div>

              </div>
            </div>
            <div class="text-[11px] text-on-surface-variant/70 mt-3 text-center">
              Coordinates are calculated as percentages of card dimensions.
            </div>
          </div>

          <!-- Right Column: Settings & Controls for each Field -->
          <div class="grow flex flex-col justify-between overflow-hidden">
            <div class="grow overflow-y-auto p-6 space-y-4">
              
              <!-- Tab / Accordion of Field Controls -->
              <div id="field-controls-container" class="space-y-3">
                <!-- Dynamically populated by JavaScript -->
              </div>

            </div>

            <!-- Modal Footer Action Bar -->
            <div class="p-4 px-6 border-t border-outline-variant/50 bg-surface-container-low/50 flex items-center justify-between">
              <button type="button" onclick="resetFieldConfigToDefaults()" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-outline-variant text-xs font-semibold text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-[16px]">restart_alt</span>Reset to Defaults
              </button>

              <div class="flex items-center gap-2">
                <button type="button" onclick="closeFieldEditorModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-on-surface-variant hover:bg-surface-container-high transition-colors cursor-pointer">
                  Cancel
                </button>
                <button type="button" id="save-field-config-btn" onclick="saveFieldConfig()" class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl bg-primary text-white text-xs font-semibold hover:bg-primary/90 transition-colors shadow-2xs cursor-pointer">
                  <span class="material-symbols-outlined text-[16px]">check</span>Save Field Positions
                </button>
              </div>
            </div>

          </div>

        </div>

      </div>
    </div>

    <script>
      // =========================================================================
      // FIXED DIMENSION SPECIFICATIONS
      // Header: 2480 × 561 px (Aspect ratio: 2480 / 561)
      // Footer: 3539 × 400 px (Aspect ratio: 3539 / 400)
      // ID Card Front & Back: 638 × 1013 px (Aspect ratio: 638 / 1013 · CR80 Portrait 2.125" × 3.375")
      // =========================================================================
      var CROP_SPECS = {
        header: {
          type: 'header',
          name: 'Header Image',
          width: 2480,
          height: 561,
          aspectRatio: 2480 / 561,
          liveWidth: 160,
          liveHeight: Math.round(160 * (561 / 2480))
        },
        footer: {
          type: 'footer',
          name: 'Footer Image',
          width: 3539,
          height: 400,
          aspectRatio: 3539 / 400,
          liveWidth: 160,
          liveHeight: Math.round(160 * (400 / 3539))
        },
        id_card_front: {
          type: 'header',
          name: 'Student ID Card - Front Design',
          width: 638,
          height: 1013,
          aspectRatio: 638 / 1013,
          liveWidth: 90,
          liveHeight: Math.round(90 * (1013 / 638))
        },
        id_card_back: {
          type: 'footer',
          name: 'Student ID Card - Back Design',
          width: 638,
          height: 1013,
          aspectRatio: 638 / 1013,
          liveWidth: 90,
          liveHeight: Math.round(90 * (1013 / 638))
        }
      };

      var activeCropper = null;
      var activeCropType = null;
      var activeCropSpecKey = null;
      var cachedCropSource = {
        header: null,
        footer: null
      };

      // Open Upload / Configure Modal
      function openUploadModal(docType, label, status, targetSide) {
        // Ensure crop modal is closed
        var cropModal = document.getElementById('doc_crop_modal');
        if (cropModal) {
          cropModal.classList.add('hidden');
        }

        document.getElementById('modal_document_type').value = docType;
        document.getElementById('modal_status').value = status || 'Active';

        var isIdCard = (docType === 'id_card');
        var titleElem = document.getElementById('upload_modal_title');
        var bannerElem = document.getElementById('modal_info_banner');
        var headerLabelElem = document.getElementById('modal_header_label');
        var headerBadgeElem = document.getElementById('modal_header_badge');
        var footerLabelElem = document.getElementById('modal_footer_label');
        var footerBadgeElem = document.getElementById('modal_footer_badge');

        if (isIdCard) {
          titleElem.textContent = 'Configure Student ID Card Design';
          bannerElem.innerHTML = 'Allowed formats: <strong class="text-on-surface">PNG, JPG, WEBP</strong>. Fixed CR80 Portrait dimensions: <strong class="text-on-surface">638 × 1013 px</strong> (54mm × 86mm / 2.125" × 3.375").';
          headerLabelElem.textContent = 'Front Design (Background Image)';
          headerBadgeElem.textContent = 'Required: 638 × 1013 px';
          footerLabelElem.textContent = 'Back Design (Background Image)';
          footerBadgeElem.textContent = 'Required: 638 × 1013 px';
        } else {
          titleElem.textContent = 'Configure Design: ' + label;
          bannerElem.innerHTML = 'Allowed formats: <strong class="text-on-surface">PNG, JPG, WEBP</strong>. Fixed crop dimensions: <strong class="text-on-surface">Header: 2480 × 561 px</strong> | <strong class="text-on-surface">Footer: 3539 × 400 px</strong>.';
          headerLabelElem.textContent = 'Header Image';
          headerBadgeElem.textContent = 'Required: 2480 × 561 px';
          footerLabelElem.textContent = 'Footer Image';
          footerBadgeElem.textContent = 'Required: 3539 × 400 px';
        }
        
        // Reset file inputs & crop states for clean session
        clearCroppedImage('header');
        clearCroppedImage('footer');

        document.getElementById('upload_modal').classList.remove('hidden');
      }

      // Close Upload / Configure Modal
      function closeUploadModal() {
        document.getElementById('upload_modal').classList.add('hidden');
        clearCroppedImage('header');
        clearCroppedImage('footer');
      }

      // Handle Image selection and trigger crop modal immediately
      function handleDocumentImageSelect(input, type) {
        if (!input.files || !input.files[0]) {
          return;
        }
        var file = input.files[0];

        // Format validation
        var validTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];
        if (validTypes.indexOf(file.type.toLowerCase()) === -1) {
          alert('Unsupported file format (' + file.type + '). Please select a valid PNG, JPG, or WEBP image.');
          input.value = '';
          return;
        }

        // Size check (max 15MB before crop)
        if (file.size > 15 * 1024 * 1024) {
          alert('Selected image exceeds 15MB. Please choose a smaller image.');
          input.value = '';
          return;
        }

        var docType = document.getElementById('modal_document_type').value;
        var cropTypeKey = type;
        if (docType === 'id_card') {
          cropTypeKey = (type === 'footer' || type === 'back') ? 'id_card_back' : 'id_card_front';
        }

        var reader = new FileReader();
        reader.onload = function(e) {
          cachedCropSource[type] = e.target.result;
          openCropModal(type, e.target.result, cropTypeKey);
        };
        reader.onerror = function() {
          alert('Failed to read selected image file.');
          input.value = '';
        };
        reader.readAsDataURL(file);
      }

      // Open Crop Image Modal: Temporarily hides Configure Modal so cropper is exclusive
      function openCropModal(type, imgSrc, cropKey) {
        activeCropType = type;
        activeCropSpecKey = cropKey || type;
        var spec = CROP_SPECS[activeCropSpecKey];

        // 1. Temporarily HIDE Configure Modal so there is NEVER two competing active modals
        var uploadModal = document.getElementById('upload_modal');
        if (uploadModal) {
          uploadModal.classList.add('hidden');
        }

        // 2. Configure Crop Modal metadata and badges
        document.getElementById('crop_modal_title').textContent = 'Crop ' + spec.name;
        document.getElementById('crop_modal_badge').textContent = spec.width + ' × ' + spec.height + ' px';
        document.getElementById('crop_modal_subtitle').textContent = 'Drag to reposition or resize the crop box. Aspect ratio locked to ' + spec.width + ':' + spec.height + '.';

        // 3. Configure Live Preview sizing
        var liveBox = document.getElementById('crop_live_preview');
        liveBox.style.width = spec.liveWidth + 'px';
        liveBox.style.height = spec.liveHeight + 'px';
        liveBox.innerHTML = '';

        // 4. Reveal Crop Modal
        var cropModal = document.getElementById('doc_crop_modal');
        cropModal.classList.remove('hidden');

        // 5. Setup image & initialize Cropper.js cleanly
        var cropImg = document.getElementById('doc_cropper_image');
        
        if (activeCropper) {
          activeCropper.destroy();
          activeCropper = null;
        }

        function initCropperInstance() {
          if (activeCropper) {
            activeCropper.destroy();
            activeCropper = null;
          }
          activeCropper = new Cropper(cropImg, {
            aspectRatio: spec.aspectRatio,
            viewMode: 1, // Restrict cropbox to image boundaries
            dragMode: 'move',
            autoCropArea: 0.98,
            responsive: true,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
            preview: '#crop_live_preview'
          });
        }

        cropImg.onload = function() {
          requestAnimationFrame(function() {
            setTimeout(initCropperInstance, 60);
          });
        };

        cropImg.src = imgSrc;
        if (cropImg.complete) {
          requestAnimationFrame(function() {
            setTimeout(initCropperInstance, 60);
          });
        }
      }

      function cropperZoom(delta) {
        if (activeCropper) {
          activeCropper.zoom(delta);
        }
      }

      function cropperRotate(deg) {
        if (activeCropper) {
          activeCropper.rotate(deg);
        }
      }

      function cropperReset() {
        if (activeCropper) {
          activeCropper.reset();
        }
      }

      // Cancel Crop: Closes Crop Modal and Restores Configure Modal
      function cancelCrop() {
        if (activeCropper) {
          activeCropper.destroy();
          activeCropper = null;
        }

        // Close Crop Modal
        document.getElementById('doc_crop_modal').classList.add('hidden');

        // If no crop was previously saved for this type, clear the file input
        if (activeCropType) {
          var croppedVal = document.getElementById(activeCropType + '_image_cropped').value;
          if (!croppedVal) {
            document.getElementById(activeCropType + '_file_input').value = '';
          }
        }
        activeCropType = null;
        activeCropSpecKey = null;

        // RESTORE Configure Modal
        var uploadModal = document.getElementById('upload_modal');
        if (uploadModal) {
          uploadModal.classList.remove('hidden');
        }
      }

      // Apply Crop: Exports canvas, populates inputs, Closes Crop Modal, Restores Configure Modal
      function applyCrop() {
        if (!activeCropper || !activeCropType) {
          return;
        }
        var spec = CROP_SPECS[activeCropSpecKey || activeCropType];

        // Export cropped canvas at EXACT required pixel dimensions
        var canvas = activeCropper.getCroppedCanvas({
          width: spec.width,
          height: spec.height,
          imageSmoothingEnabled: true,
          imageSmoothingQuality: 'high'
        });

        if (!canvas) {
          alert('Could not generate cropped canvas. Please reposition your crop box.');
          return;
        }

        // Export as high-quality PNG
        var croppedBase64 = canvas.toDataURL('image/png', 1.0);

        // Store into hidden input
        document.getElementById(activeCropType + '_image_cropped').value = croppedBase64;

        // Show preview in upload modal
        document.getElementById(activeCropType + '_cropped_preview_img').src = croppedBase64;
        var dimTextElem = document.getElementById(activeCropType + '_cropped_dim_text');
        if (dimTextElem) {
          dimTextElem.textContent = 'Exact: ' + spec.width + ' × ' + spec.height + ' px';
        }
        document.getElementById(activeCropType + '_cropped_preview_wrap').classList.remove('hidden');

        // Clean up and close cropper modal
        activeCropper.destroy();
        activeCropper = null;
        document.getElementById('doc_crop_modal').classList.add('hidden');
        activeCropType = null;
        activeCropSpecKey = null;

        // RESTORE Configure Modal with updated crop data
        var uploadModal = document.getElementById('upload_modal');
        if (uploadModal) {
          uploadModal.classList.remove('hidden');
        }
      }

      // Re-crop previously selected file
      function recropImage(type) {
        var docType = document.getElementById('modal_document_type').value;
        var cropTypeKey = (docType === 'id_card')
          ? ((type === 'footer' || type === 'back') ? 'id_card_back' : 'id_card_front')
          : type;

        if (cachedCropSource[type]) {
          openCropModal(type, cachedCropSource[type], cropTypeKey);
        } else {
          document.getElementById(type + '_file_input').click();
        }
      }

      // Clear cropped image
      function clearCroppedImage(type) {
        document.getElementById(type + '_image_cropped').value = '';
        document.getElementById(type + '_file_input').value = '';
        document.getElementById(type + '_cropped_preview_wrap').classList.add('hidden');
        document.getElementById(type + '_cropped_preview_img').src = '';
        cachedCropSource[type] = null;
      }

      // Prevent uncropped image from being submitted
      document.getElementById('design_upload_form').addEventListener('submit', function(e) {
        var isIdCard = (document.getElementById('modal_document_type').value === 'id_card');
        var headerFile = document.getElementById('header_file_input').files[0];
        var headerCropped = document.getElementById('header_image_cropped').value;
        if (headerFile && !headerCropped) {
          e.preventDefault();
          var msg = isIdCard
            ? 'Please crop the selected Front Design Image to 638 × 1013 px before saving.'
            : 'Please crop the selected Header Image to 2480 × 561 px before saving.';
          alert(msg);
          if (cachedCropSource.header) {
            openCropModal('header', cachedCropSource.header, isIdCard ? 'id_card_front' : 'header');
          }
          return false;
        }

        var footerFile = document.getElementById('footer_file_input').files[0];
        var footerCropped = document.getElementById('footer_image_cropped').value;
        if (footerFile && !footerCropped) {
          e.preventDefault();
          var msg = isIdCard
            ? 'Please crop the selected Back Design Image to 638 × 1013 px before saving.'
            : 'Please crop the selected Footer Image to 3539 × 400 px before saving.';
          alert(msg);
          if (cachedCropSource.footer) {
            openCropModal('footer', cachedCropSource.footer, isIdCard ? 'id_card_back' : 'footer');
          }
          return false;
        }
      });

      function previewDocModal(docType, label) {
        document.getElementById('preview_modal_title').textContent = 'Document Design Preview: ' + label;
        var url = '<?php echo site_url("settings/document_design_preview/{$target_school_id}/"); ?>' + docType;
        document.getElementById('preview_iframe').src = url;
        document.getElementById('preview_modal').classList.remove('hidden');
      }

      function closePreviewModal() {
        document.getElementById('preview_iframe').src = 'about:blank';
        document.getElementById('preview_modal').classList.add('hidden');
      }

      function removeImage(docType, imageType) {
        if (!confirm('Are you sure you want to remove this ' + imageType + ' image?')) {
          return;
        }
        document.getElementById('remove_doc_type').value = docType;
        document.getElementById('remove_img_type').value = imageType;
        document.getElementById('remove_image_form').submit();
      }

      // =========================================================================
      // STUDENT ID CARD FIELD POSITION EDITOR LOGIC
      // =========================================================================
      var currentFieldConfig = <?php echo json_encode($id_card_field_config ?? []); ?>;
      var defaultFieldConfig = <?php echo json_encode($this->document_design_service->get_default_id_card_field_config()); ?>;

      var ROW_FIELDS = [
        { key: 'admission_number', defaultLabel: 'ID', hint: 'Admission/Student ID' },
        { key: 'guardian_name', defaultLabel: "FATHER'S NAME", hint: "Father/Guardian Name" },
        { key: 'class_division', defaultLabel: 'CLASS & DIV', hint: 'Class and Division' },
        { key: 'roll_number', defaultLabel: 'ROLL NO.', hint: 'Roll Number' },
        { key: 'date_of_birth', defaultLabel: 'D.O.B', hint: 'Date of Birth' },
        { key: 'blood_group', defaultLabel: 'BLOOD GROUP', hint: 'Blood Group' }
      ];

      function openFieldEditorModal() {
        renderFieldControls();
        applyFieldConfigToPreview();
        document.getElementById('field_editor_modal').classList.remove('hidden');
      }

      function closeFieldEditorModal() {
        document.getElementById('field_editor_modal').classList.add('hidden');
      }

      function renderFieldControls() {
        var container = document.getElementById('field-controls-container');
        if (!container) return;
        container.innerHTML = '';

        // 1. Photo Card
        var pCfg = currentFieldConfig.photo || defaultFieldConfig.photo || {};
        var photoCard = document.createElement('div');
        photoCard.className = 'p-4 rounded-xl border border-outline-variant/60 bg-surface-container-lowest shadow-2xs space-y-3';
        photoCard.innerHTML = '<div class="flex items-center justify-between pb-3 border-b border-outline-variant/30">' +
          '<div class="flex items-center gap-2">' +
            '<input type="checkbox" id="chk-photo-enabled" class="rounded border-outline-variant text-primary focus:ring-primary/20 w-4 h-4 cursor-pointer" ' + (pCfg.enabled !== false ? 'checked' : '') + ' onchange="onFieldParamChange(\'photo\', \'enabled\', this.checked)">' +
            '<label for="chk-photo-enabled" class="font-bold text-xs text-on-surface cursor-pointer select-none">Student Photo</label>' +
          '</div>' +
          '<span class="text-[10px] font-semibold text-on-surface-variant/70 uppercase tracking-wider">Image Frame</span>' +
        '</div>' +
        '<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">' +
          '<div>' +
            '<label class="block text-[11px] font-medium text-on-surface-variant mb-1">Top Position (%)</label>' +
            '<input type="number" min="0" max="100" step="1" value="' + (pCfg.top !== undefined ? pCfg.top : 24) + '" class="w-full px-2.5 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-on-surface text-xs focus:ring-1 focus:ring-primary" oninput="onFieldParamChange(\'photo\', \'top\', parseFloat(this.value))">' +
          '</div>' +
          '<div>' +
            '<label class="block text-[11px] font-medium text-on-surface-variant mb-1">Left Position (%)</label>' +
            '<input type="number" min="0" max="100" step="1" value="' + (pCfg.left !== undefined ? pCfg.left : 31) + '" class="w-full px-2.5 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-on-surface text-xs focus:ring-1 focus:ring-primary" oninput="onFieldParamChange(\'photo\', \'left\', parseFloat(this.value))">' +
          '</div>' +
          '<div>' +
            '<label class="block text-[11px] font-medium text-on-surface-variant mb-1">Width (%)</label>' +
            '<input type="number" min="10" max="100" step="1" value="' + (pCfg.width !== undefined ? pCfg.width : 38) + '" class="w-full px-2.5 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-on-surface text-xs focus:ring-1 focus:ring-primary" oninput="onFieldParamChange(\'photo\', \'width\', parseFloat(this.value))">' +
          '</div>' +
          '<div>' +
            '<label class="block text-[11px] font-medium text-on-surface-variant mb-1">Height (%)</label>' +
            '<input type="number" min="10" max="100" step="1" value="' + (pCfg.height !== undefined ? pCfg.height : 26) + '" class="w-full px-2.5 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-on-surface text-xs focus:ring-1 focus:ring-primary" oninput="onFieldParamChange(\'photo\', \'height\', parseFloat(this.value))">' +
          '</div>' +
        '</div>';
        container.appendChild(photoCard);

        // 2. Student Name Card
        var nCfg = currentFieldConfig.student_name || defaultFieldConfig.student_name || {};
        var nameCard = document.createElement('div');
        nameCard.className = 'p-4 rounded-xl border border-outline-variant/60 bg-surface-container-lowest shadow-2xs space-y-3';
        nameCard.innerHTML = '<div class="flex items-center justify-between pb-3 border-b border-outline-variant/30">' +
          '<div class="flex items-center gap-2">' +
            '<input type="checkbox" id="chk-student_name-enabled" class="rounded border-outline-variant text-primary focus:ring-primary/20 w-4 h-4 cursor-pointer" ' + (nCfg.enabled !== false ? 'checked' : '') + ' onchange="onFieldParamChange(\'student_name\', \'enabled\', this.checked)">' +
            '<label for="chk-student_name-enabled" class="font-bold text-xs text-on-surface cursor-pointer select-none">Student Full Name</label>' +
          '</div>' +
          '<span class="text-[10px] font-semibold text-on-surface-variant/70 uppercase tracking-wider">Name Headline</span>' +
        '</div>' +
        '<div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">' +
          '<div>' +
            '<label class="block text-[11px] font-medium text-on-surface-variant mb-1">Top Position (%)</label>' +
            '<input type="number" min="0" max="100" step="1" value="' + (nCfg.top !== undefined ? nCfg.top : 53) + '" class="w-full px-2.5 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-on-surface text-xs focus:ring-1 focus:ring-primary" oninput="onFieldParamChange(\'student_name\', \'top\', parseFloat(this.value))">' +
          '</div>' +
          '<div>' +
            '<label class="block text-[11px] font-medium text-on-surface-variant mb-1">Left Position (%)</label>' +
            '<input type="number" min="0" max="100" step="1" value="' + (nCfg.left !== undefined ? nCfg.left : 5) + '" class="w-full px-2.5 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-on-surface text-xs focus:ring-1 focus:ring-primary" oninput="onFieldParamChange(\'student_name\', \'left\', parseFloat(this.value))">' +
          '</div>' +
          '<div>' +
            '<label class="block text-[11px] font-medium text-on-surface-variant mb-1">Width (%)</label>' +
            '<input type="number" min="10" max="100" step="1" value="' + (nCfg.width !== undefined ? nCfg.width : 90) + '" class="w-full px-2.5 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-on-surface text-xs focus:ring-1 focus:ring-primary" oninput="onFieldParamChange(\'student_name\', \'width\', parseFloat(this.value))">' +
          '</div>' +
        '</div>' +
        '<div class="pt-2 border-t border-outline-variant/20 grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">' +
          '<div>' +
            '<label class="block text-[11px] font-medium text-on-surface-variant mb-1">Font Size</label>' +
            '<select class="w-full px-2 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-on-surface text-xs" onchange="onFieldParamChange(\'student_name\', \'font_size\', this.value)">' +
              '<option value="8pt"' + (nCfg.font_size === '8pt' ? ' selected' : '') + '>8 pt</option>' +
              '<option value="9pt"' + (nCfg.font_size === '9pt' ? ' selected' : '') + '>9 pt</option>' +
              '<option value="9.5pt"' + (nCfg.font_size === '9.5pt' ? ' selected' : '') + '>9.5 pt</option>' +
              '<option value="11pt"' + (nCfg.font_size === '11pt' ? ' selected' : '') + '>11 pt</option>' +
              '<option value="12pt"' + (nCfg.font_size === '12pt' ? ' selected' : '') + '>12 pt</option>' +
            '</select>' +
          '</div>' +
          '<div>' +
            '<label class="block text-[11px] font-medium text-on-surface-variant mb-1">Color</label>' +
            '<div class="flex items-center gap-1.5">' +
              '<input type="color" value="' + (nCfg.color || '#0f172a') + '" class="w-7 h-7 p-0 rounded border border-outline-variant cursor-pointer" onchange="onFieldParamChange(\'student_name\', \'color\', this.value)">' +
              '<span class="text-[10px] font-mono text-on-surface-variant">' + (nCfg.color || '#0f172a') + '</span>' +
            '</div>' +
          '</div>' +
          '<div>' +
            '<label class="block text-[11px] font-medium text-on-surface-variant mb-1">Alignment</label>' +
            '<select class="w-full px-2 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-on-surface text-xs" onchange="onFieldParamChange(\'student_name\', \'align\', this.value)">' +
              '<option value="center"' + (nCfg.align === 'center' ? ' selected' : '') + '>Center</option>' +
              '<option value="left"' + (nCfg.align === 'left' ? ' selected' : '') + '>Left</option>' +
              '<option value="right"' + (nCfg.align === 'right' ? ' selected' : '') + '>Right</option>' +
            '</select>' +
          '</div>' +
        '</div>';
        container.appendChild(nameCard);

        // 3. Student Details Aligned Container Card
        var dCfg = currentFieldConfig.student_details || defaultFieldConfig.student_details || {};
        var detailsCard = document.createElement('div');
        detailsCard.className = 'p-4 rounded-xl border border-teal-600/30 bg-teal-500/5 shadow-2xs space-y-4';
        
        var detailsHtml = '<div class="flex items-center justify-between pb-3 border-b border-outline-variant/30">' +
          '<div class="flex items-center gap-2">' +
            '<input type="checkbox" id="chk-student_details-enabled" class="rounded border-outline-variant text-teal-700 focus:ring-teal-700/20 w-4 h-4 cursor-pointer" ' + (dCfg.enabled !== false ? 'checked' : '') + ' onchange="onFieldParamChange(\'student_details\', \'enabled\', this.checked)">' +
            '<label for="chk-student_details-enabled" class="font-bold text-xs text-on-surface cursor-pointer select-none">Student Details Block (Aligned Rows)</label>' +
          '</div>' +
          '<span class="text-[10px] font-bold text-teal-700 uppercase tracking-wider bg-teal-100/60 px-2 py-0.5 rounded">Structured Table</span>' +
        '</div>' +
        '<div class="text-[11px] text-on-surface-variant/80">' +
          'All dynamic student rows share this container with fixed column alignment (<strong class="text-teal-900">LABEL : VALUE</strong>), ensuring values start from the exact same horizontal position.' +
        '</div>' +
        '<div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">' +
          '<div>' +
            '<label class="block text-[11px] font-medium text-on-surface-variant mb-1">Top Position (%)</label>' +
            '<input type="number" min="0" max="100" step="1" value="' + (dCfg.top !== undefined ? dCfg.top : 61) + '" class="w-full px-2.5 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-on-surface text-xs focus:ring-1 focus:ring-primary" oninput="onFieldParamChange(\'student_details\', \'top\', parseFloat(this.value))">' +
          '</div>' +
          '<div>' +
            '<label class="block text-[11px] font-medium text-on-surface-variant mb-1">Left Position (%)</label>' +
            '<input type="number" min="0" max="100" step="1" value="' + (dCfg.left !== undefined ? dCfg.left : 7) + '" class="w-full px-2.5 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-on-surface text-xs focus:ring-1 focus:ring-primary" oninput="onFieldParamChange(\'student_details\', \'left\', parseFloat(this.value))">' +
          '</div>' +
          '<div>' +
            '<label class="block text-[11px] font-medium text-on-surface-variant mb-1">Width (%)</label>' +
            '<input type="number" min="20" max="100" step="1" value="' + (dCfg.width !== undefined ? dCfg.width : 86) + '" class="w-full px-2.5 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-on-surface text-xs focus:ring-1 focus:ring-primary" oninput="onFieldParamChange(\'student_details\', \'width\', parseFloat(this.value))">' +
          '</div>' +
        '</div>' +
        '<div class="pt-2 border-t border-outline-variant/20 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">' +
          '<div>' +
            '<label class="block text-[11px] font-medium text-on-surface-variant mb-1">Font Size</label>' +
            '<select class="w-full px-2 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-on-surface text-xs" onchange="onFieldParamChange(\'student_details\', \'font_size\', this.value)">' +
              '<option value="6.5pt"' + (dCfg.font_size === '6.5pt' ? ' selected' : '') + '>6.5 pt</option>' +
              '<option value="7pt"' + (dCfg.font_size === '7pt' ? ' selected' : '') + '>7 pt</option>' +
              '<option value="7.5pt"' + (dCfg.font_size === '7.5pt' ? ' selected' : '') + '>7.5 pt</option>' +
              '<option value="8pt"' + (dCfg.font_size === '8pt' ? ' selected' : '') + '>8 pt</option>' +
            '</select>' +
          '</div>' +
          '<div>' +
            '<label class="block text-[11px] font-medium text-on-surface-variant mb-1">Label Width</label>' +
            '<select class="w-full px-2 py-1.5 rounded-lg border border-outline-variant bg-surface-container-low text-on-surface text-xs" onchange="onFieldParamChange(\'student_details\', \'label_width\', this.value)">' +
              '<option value="30%"' + (dCfg.label_width === '30%' ? ' selected' : '') + '>30%</option>' +
              '<option value="34%"' + (dCfg.label_width === '34%' ? ' selected' : '') + '>34%</option>' +
              '<option value="36%"' + (dCfg.label_width === '36%' ? ' selected' : '') + '>36% (Default)</option>' +
              '<option value="40%"' + (dCfg.label_width === '40%' ? ' selected' : '') + '>40%</option>' +
              '<option value="44%"' + (dCfg.label_width === '44%' ? ' selected' : '') + '>44%</option>' +
            '</select>' +
          '</div>' +
          '<div>' +
            '<label class="block text-[11px] font-medium text-on-surface-variant mb-1">Label Color</label>' +
            '<div class="flex items-center gap-1.5">' +
              '<input type="color" value="' + (dCfg.label_color || '#0f766e') + '" class="w-7 h-7 p-0 rounded border border-outline-variant cursor-pointer" onchange="onFieldParamChange(\'student_details\', \'label_color\', this.value)">' +
              '<span class="text-[10px] font-mono text-on-surface-variant">' + (dCfg.label_color || '#0f766e') + '</span>' +
            '</div>' +
          '</div>' +
          '<div>' +
            '<label class="block text-[11px] font-medium text-on-surface-variant mb-1">Value Color</label>' +
            '<div class="flex items-center gap-1.5">' +
              '<input type="color" value="' + (dCfg.value_color || '#0f172a') + '" class="w-7 h-7 p-0 rounded border border-outline-variant cursor-pointer" onchange="onFieldParamChange(\'student_details\', \'value_color\', this.value)">' +
              '<span class="text-[10px] font-mono text-on-surface-variant">' + (dCfg.value_color || '#0f172a') + '</span>' +
            '</div>' +
          '</div>' +
        '</div>';

        // Sub-rows Config
        detailsHtml += '<div class="pt-3 border-t border-teal-600/20">' +
          '<div class="text-[11px] font-bold text-teal-900 mb-2">Rows & Field Labels:</div>' +
          '<div class="space-y-2">';

        ROW_FIELDS.forEach(function(row) {
          var rCfg = currentFieldConfig[row.key] || defaultFieldConfig[row.key] || {};
          var isRowEnabled = (rCfg.enabled !== false);
          var lblVal = (rCfg.label !== undefined ? rCfg.label : row.defaultLabel);

          detailsHtml += '<div class="flex items-center justify-between gap-3 p-2 rounded-lg bg-surface-container-low border border-outline-variant/40 text-xs">' +
            '<div class="flex items-center gap-2 min-w-[130px]">' +
              '<input type="checkbox" id="chk-' + row.key + '-enabled" class="rounded border-outline-variant text-teal-700 focus:ring-teal-700/20 w-4 h-4 cursor-pointer" ' + (isRowEnabled ? 'checked' : '') + ' onchange="onFieldParamChange(\'' + row.key + '\', \'enabled\', this.checked)">' +
              '<label for="chk-' + row.key + '-enabled" class="font-medium text-xs text-on-surface cursor-pointer select-none">' + row.hint + '</label>' +
            '</div>' +
            '<div class="flex items-center gap-2 flex-1 max-w-[220px]">' +
              '<span class="text-[10px] text-on-surface-variant shrink-0">Label:</span>' +
              '<input type="text" value="' + lblVal + '" class="w-full px-2 py-1 rounded border border-outline-variant bg-surface-container-lowest text-on-surface text-xs font-bold" oninput="onFieldParamChange(\'' + row.key + '\', \'label\', this.value)">' +
            '</div>' +
          '</div>';
        });

        detailsHtml += '</div></div>';
        detailsCard.innerHTML = detailsHtml;
        container.appendChild(detailsCard);
      }

      function onFieldParamChange(fieldKey, paramKey, value) {
        if (!currentFieldConfig[fieldKey]) {
          currentFieldConfig[fieldKey] = {};
        }
        currentFieldConfig[fieldKey][paramKey] = value;
        applyFieldConfigToPreview();
      }

      function applyFieldConfigToPreview() {
        // 1. Photo
        var pEl = document.getElementById('prev-field-photo');
        var pCfg = currentFieldConfig.photo || defaultFieldConfig.photo || {};
        if (pEl) {
          pEl.style.display = (pCfg.enabled !== false) ? 'flex' : 'none';
          pEl.style.top = (pCfg.top !== undefined ? pCfg.top : 24) + '%';
          pEl.style.left = (pCfg.left !== undefined ? pCfg.left : 31) + '%';
          pEl.style.width = (pCfg.width !== undefined ? pCfg.width : 38) + '%';
          pEl.style.height = (pCfg.height !== undefined ? pCfg.height : 26) + '%';
        }

        // 2. Student Name
        var nEl = document.getElementById('prev-field-student_name');
        var nCfg = currentFieldConfig.student_name || defaultFieldConfig.student_name || {};
        if (nEl) {
          nEl.style.display = (nCfg.enabled !== false) ? 'block' : 'none';
          nEl.style.top = (nCfg.top !== undefined ? nCfg.top : 53) + '%';
          nEl.style.left = (nCfg.left !== undefined ? nCfg.left : 5) + '%';
          nEl.style.width = (nCfg.width !== undefined ? nCfg.width : 90) + '%';
          nEl.style.fontSize = nCfg.font_size || '9.5pt';
          nEl.style.color = nCfg.color || '#0f172a';
          nEl.style.textAlign = nCfg.align || 'center';
        }

        // 3. Student Details Table Container
        var dEl = document.getElementById('prev-field-student_details');
        var dCfg = currentFieldConfig.student_details || defaultFieldConfig.student_details || {};
        if (dEl) {
          dEl.style.display = (dCfg.enabled !== false) ? 'block' : 'none';
          dEl.style.top = (dCfg.top !== undefined ? dCfg.top : 61) + '%';
          dEl.style.left = (dCfg.left !== undefined ? dCfg.left : 7) + '%';
          dEl.style.width = (dCfg.width !== undefined ? dCfg.width : 86) + '%';

          var tbl = dEl.querySelector('table');
          if (tbl) {
            tbl.style.fontSize = dCfg.font_size || '7pt';
          }
          var lblCols = dEl.querySelectorAll('.prev-lbl-col');
          lblCols.forEach(function(col) {
            col.style.width = dCfg.label_width || '36%';
            col.style.color = dCfg.label_color || '#0f766e';
          });
          var valCols = dEl.querySelectorAll('.prev-val-col');
          valCols.forEach(function(col) {
            if (!col.classList.contains('text-rose-600')) {
              col.style.color = dCfg.value_color || '#0f172a';
            }
          });
        }

        // 4. Sub-rows & custom row labels
        ROW_FIELDS.forEach(function(row) {
          var rowEl = document.getElementById('prev-row-' + row.key);
          var lblEl = document.getElementById('prev-lbl-' + row.key);
          var rCfg = currentFieldConfig[row.key] || defaultFieldConfig[row.key] || {};
          if (rowEl) {
            rowEl.style.display = (rCfg.enabled !== false) ? '' : 'none';
          }
          if (lblEl) {
            lblEl.textContent = (rCfg.label !== undefined ? rCfg.label : row.defaultLabel);
          }
        });
      }

      function resetFieldConfigToDefaults() {
        if (!confirm('Reset all field positions and styles back to system defaults?')) {
          return;
        }
        currentFieldConfig = JSON.parse(JSON.stringify(defaultFieldConfig));
        renderFieldControls();
        applyFieldConfigToPreview();
      }

      function saveFieldConfig() {
        var btn = document.getElementById('save-field-config-btn');
        btn.disabled = true;
        btn.innerHTML = '<span class="material-symbols-outlined text-[16px] animate-spin">refresh</span>Saving...';

        var formData = new FormData();
        formData.append('<?php echo $this->security->get_csrf_token_name(); ?>', '<?php echo $this->security->get_csrf_hash(); ?>');
        formData.append('fields', JSON.stringify(currentFieldConfig));

        fetch('<?php echo site_url("settings/document_design_save_fields?school_id={$target_school_id}"); ?>', {
          method: 'POST',
          body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
          btn.disabled = false;
          btn.innerHTML = '<span class="material-symbols-outlined text-[16px]">check</span>Save Field Positions';

          if (data.status) {
            alert(data.message || 'Field positions saved successfully.');
            closeFieldEditorModal();
          } else {
            alert('Error: ' + (data.message || 'Failed to save field positions.'));
          }
        })
        .catch(function(err) {
          btn.disabled = false;
          btn.innerHTML = '<span class="material-symbols-outlined text-[16px]">check</span>Save Field Positions';
          alert('Network or server error while saving field configuration.');
        });
      }

      // Backdrop click listeners for single active modal
      document.getElementById('doc_crop_modal').addEventListener('click', function(e) {
        if (e.target === this) {
          cancelCrop();
        }
      });

      document.getElementById('upload_modal').addEventListener('click', function(e) {
        if (e.target === this) {
          closeUploadModal();
        }
      });

      document.getElementById('preview_modal').addEventListener('click', function(e) {
        if (e.target === this) {
          closePreviewModal();
        }
      });

      var fieldEditorModal = document.getElementById('field_editor_modal');
      if (fieldEditorModal) {
        fieldEditorModal.addEventListener('click', function(e) {
          if (e.target === this) {
            closeFieldEditorModal();
          }
        });
      }

      // Escape key handler: unwinds active modal in order
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' || e.keyCode === 27) {
          var cropModal = document.getElementById('doc_crop_modal');
          var uploadModal = document.getElementById('upload_modal');
          var previewModal = document.getElementById('preview_modal');
          var fModal = document.getElementById('field_editor_modal');

          if (cropModal && !cropModal.classList.contains('hidden')) {
            cancelCrop();
            return;
          }
          if (fModal && !fModal.classList.contains('hidden')) {
            closeFieldEditorModal();
            return;
          }
          if (uploadModal && !uploadModal.classList.contains('hidden')) {
            closeUploadModal();
            return;
          }
          if (previewModal && !previewModal.classList.contains('hidden')) {
            closePreviewModal();
            return;
          }
        }
      });
    </script>

