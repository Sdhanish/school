<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Print Student ID Cards - <?php echo html_escape($settings->school_name ?? 'Login2'); ?></title>
  <link href="<?php echo base_url('assets/fonts/inter.css'); ?>" rel="stylesheet"/>
  <link href="<?php echo base_url('assets/fonts/material-symbols.css'); ?>" rel="stylesheet"/>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: #f1f5f9;
      color: #0f172a;
      padding: 20px;
    }

    /* Print action bar */
    .print-bar {
      max-width: 900px;
      margin: 0 auto 20px auto;
      background: #ffffff;
      padding: 12px 20px;
      border-radius: 12px;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .print-btn {
      background: #006c4a;
      color: #ffffff;
      border: none;
      padding: 9px 20px;
      border-radius: 8px;
      font-weight: 600;
      font-size: 14px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .close-btn {
      background: #e2e8f0;
      color: #334155;
      border: none;
      padding: 9px 18px;
      border-radius: 8px;
      font-weight: 600;
      font-size: 14px;
      cursor: pointer;
      text-decoration: none;
    }

    .cards-container {
      display: flex;
      flex-wrap: wrap;
      gap: 25px;
      justify-content: center;
      max-width: 1000px;
      margin: 0 auto;
    }

    /* Standard CR80 Portrait Card: 2.125 inches x 3.375 inches (54mm x 86mm) */
    .cr80-portrait-card {
      width: 54mm;
      height: 86mm;
      background: #ffffff;
      border-radius: 3.5mm;
      overflow: hidden;
      position: relative;
      border: 1px solid #cbd5e1;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      page-break-inside: avoid;
      break-inside: avoid;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
      font-size: 7pt;
      line-height: 1.2;
    }

    /* Lanyard Slot Hole Accent */
    .lanyard-slot {
      position: absolute;
      top: 1.8mm;
      left: 50%;
      transform: translateX(-50%);
      width: 11mm;
      height: 2mm;
      background: #ffffff;
      border: 0.3mm solid #cbd5e1;
      border-radius: 2mm;
      z-index: 20;
    }

    /* Wave/Geometric Background Decorations */
    .card-waves-svg {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      pointer-events: none;
      z-index: 1;
    }

    .card-content-layer {
      position: relative;
      z-index: 10;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 3mm 2.8mm 2.4mm 2.8mm;
    }

    /* Header */
    .card-front-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding-top: 1mm;
      padding-left: 1mm;
      padding-right: 1mm;
    }
    .school-logo-img {
      max-height: 6mm;
      max-width: 18mm;
      object-fit: contain;
      filter: drop-shadow(0 0.2mm 0.4mm rgba(0,0,0,0.2));
    }
    .school-name-text {
      font-size: 5.6pt;
      font-weight: 900;
      color: #ffffff;
      text-transform: uppercase;
      letter-spacing: 0.4px;
      line-height: 1;
      text-align: right;
    }
    .school-sub-text {
      font-size: 4.2pt;
      font-weight: 700;
      color: #ccfbf1;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      margin-top: 0.3mm;
      line-height: 1;
      text-align: right;
    }

    /* Photo Frame (Circular Medallion) */
    .student-photo-wrapper {
      margin: 0.6mm auto;
      width: 18.5mm;
      height: 18.5mm;
      border-radius: 50%;
      background: #ffffff;
      padding: 0.4mm;
      box-shadow: 0 1mm 2.5mm rgba(0,0,0,0.18), 0 0 0 0.5mm #cbd5e1;
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .student-photo-inner {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #f1f5f9;
      border: 0.3mm solid #ffffff;
    }
    .student-photo-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 50%;
    }
    .student-photo-fallback {
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, #0f766e, #044e54);
      color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 8.5pt;
      font-weight: 900;
      letter-spacing: 0.3px;
      border-radius: 50%;
    }

    /* Student Name & Title */
    .student-name-title {
      text-align: center;
      font-size: 7.2pt;
      font-weight: 900;
      color: #0f766e;
      text-transform: uppercase;
      letter-spacing: 0.2px;
      line-height: 1;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      margin-top: 0.3mm;
    }
    .student-role-title {
      text-align: center;
      font-size: 4.6pt;
      font-weight: 800;
      color: #0d9488;
      text-transform: uppercase;
      letter-spacing: 0.4px;
      line-height: 1;
      margin-top: 0.3mm;
      margin-bottom: 0.6mm;
    }

    /* Details Table Container */
    .details-table-box {
      background: rgba(248, 250, 252, 0.85);
      border: 0.2mm solid #e2e8f0;
      border-radius: 1.8mm;
      padding: 0.8mm 1.2mm 0.8mm 2.8mm;
      margin-top: 0.2mm;
    }
    .details-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 5pt;
      line-height: 1.15;
    }
    .details-table td {
      padding: 0.25mm 0;
      vertical-align: middle;
    }
    .details-table .lbl-col {
      color: #0f766e;
      font-weight: 800;
      width: 15mm;
      white-space: nowrap;
      text-transform: uppercase;
      font-size: 4.4pt;
    }
    .details-table .sep-col {
      width: 1.5mm;
      text-align: center;
      color: #94a3b8;
      font-weight: 700;
    }
    .details-table .val-col {
      color: #0f172a;
      font-weight: 700;
      word-break: break-word;
      padding-left: 0.5mm;
    }
    .blood-highlight {
      color: #e11d48 !important;
      font-weight: 900 !important;
    }

    /* Back Side Styles */
    .back-header-box {
      text-align: right;
      padding-top: 0.8mm;
      padding-right: 0.8mm;
    }
    .back-school-name {
      font-size: 5.6pt;
      font-weight: 900;
      color: #0f766e;
      text-transform: uppercase;
      letter-spacing: 0.3px;
      line-height: 1;
    }
    .back-school-sub {
      font-size: 4.2pt;
      font-weight: 700;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .back-terms-box {
      padding: 0 0.8mm;
    }
    .back-terms-title {
      font-size: 5pt;
      font-weight: 900;
      color: #0f766e;
      text-transform: uppercase;
      letter-spacing: 0.3px;
      margin-bottom: 0.6mm;
      display: flex;
      align-items: center;
      gap: 0.8mm;
    }
    .back-term-dot {
      width: 1mm;
      height: 1mm;
      border-radius: 50%;
      background: #0f766e;
      display: inline-block;
      flex-shrink: 0;
      margin-top: 0.5mm;
    }
    .back-term-item {
      display: flex;
      align-items: flex-start;
      gap: 0.8mm;
      font-size: 4.3pt;
      color: #334155;
      font-weight: 500;
      line-height: 1.15;
      margin-bottom: 0.5mm;
    }

    .back-sig-wrapper {
      text-align: center;
      margin-top: 0.3mm;
    }
    .back-sig-img {
      height: 4.5mm;
      max-width: 18mm;
      object-fit: contain;
      margin: 0 auto;
      display: block;
    }
    .back-sig-line {
      width: 18mm;
      height: 0.2mm;
      background: #cbd5e1;
      margin: 0.3mm auto;
    }
    .back-sig-title {
      font-size: 4.2pt;
      font-weight: 700;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }

    .back-dates-bar {
      display: flex;
      justify-content: space-between;
      font-size: 4.2pt;
      font-weight: 700;
      color: #334155;
      background: #f8fafc;
      border: 0.2mm solid #e2e8f0;
      border-radius: 1.2mm;
      padding: 0.4mm 1mm;
      margin: 0.4mm 0.6mm;
    }

    .back-info-list {
      display: flex;
      flex-direction: column;
      gap: 0.4mm;
      padding: 0 0.8mm;
      margin-top: 0.2mm;
    }
    .back-info-item {
      display: flex;
      align-items: center;
      gap: 0.8mm;
      font-size: 4.4pt;
      color: #1e293b;
      font-weight: 600;
    }
    .back-info-icon {
      color: #0f766e;
      font-size: 5.5pt;
      flex-shrink: 0;
    }

    .back-return-disclaimer {
      font-size: 4.2pt;
      font-weight: 700;
      color: #0f766e;
      text-align: center;
      line-height: 1.1;
      padding: 0 0.5mm 0.2mm 0.5mm;
    }

    /* Print media query */
    @media print {
      body {
        background: #ffffff !important;
        padding: 0 !important;
        margin: 0 !important;
      }
      .no-print {
        display: none !important;
      }
      .cards-container {
        display: block !important;
        gap: 0 !important;
        max-width: 100% !important;
        margin: 0 !important;
      }
      .card-pair-wrapper {
        display: flex !important;
        gap: 6mm !important;
        margin-bottom: 8mm !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
      }
      .cr80-portrait-card {
        border: 0.3mm solid #cbd5e1 !important;
        box-shadow: none !important;
      }
      @page {
        size: A4 portrait;
        margin: 8mm;
      }
    }
  </style>
</head>
<body>

  <!-- Print Action Bar -->
  <div class="print-bar no-print">
    <div>
      <h3 style="font-size: 16px; font-weight: 700; color: #006c4a;">Print Student ID Cards</h3>
      <p style="font-size: 12px; color: #64748b; margin-top: 2px;">Total: <?php echo count($students); ?> Student Card(s) · Standard CR80 Portrait (2.125" × 3.375" / 54mm × 86mm)</p>
    </div>
    <div style="display: flex; gap: 10px;">
      <button onclick="window.print()" class="print-btn">
        <span class="material-symbols-outlined" style="font-size: 18px;">print</span> Print Cards
      </button>
      <button onclick="window.close()" class="close-btn">Close</button>
    </div>
  </div>

  <div class="cards-container">
    <?php foreach ($students as $st): ?>
      <?php
        $fullName = trim($st->first_name . ' ' . ($st->middle_name ? $st->middle_name . ' ' : '') . $st->last_name);
        $nameParts = explode(' ', $fullName);
        $initials = '';
        foreach ($nameParts as $np) { if (!empty($np)) $initials .= strtoupper($np[0]); }
        $initials = substr($initials, 0, 2) ?: 'ST';
        $classDisplay = trim(($st->class_name ?: 'Grade 10'));
        $sectionDisplay = trim($st->section_name ?: 'A');
        $dobFormatted = !empty($st->date_of_birth) ? date('d-m-Y', strtotime($st->date_of_birth)) : '15-06-2012';

        $hasPhoto = !empty($st->photo) && file_exists(FCPATH . 'uploads/students/' . $st->photo);
        
        $hasLogo  = !empty($settings->school_logo) && (file_exists(FCPATH . 'uploads/id_card/' . $settings->school_logo) || file_exists(FCPATH . 'uploads/settings/' . $settings->school_logo));
        $logoPath = base_url('assets/logo.png');
        if ($hasLogo) {
          $logoPath = file_exists(FCPATH . 'uploads/id_card/' . $settings->school_logo)
            ? base_url('uploads/id_card/' . $settings->school_logo)
            : base_url('uploads/settings/' . $settings->school_logo);
        }

        $hasSig  = !empty($settings->principal_signature) && file_exists(FCPATH . 'uploads/id_card/' . $settings->principal_signature);
        $sigPath = $hasSig ? base_url('uploads/id_card/' . $settings->principal_signature) : '';
      ?>

      <div class="card-pair-wrapper" style="display: flex; gap: 20px; margin-bottom: 20px;">
        
        <?php if ($side === 'both' || $side === 'front'): ?>
          <!-- FRONT SIDE (CR80 Portrait: 54mm x 86mm) -->
          <div class="cr80-portrait-card">
            <!-- Lanyard Slot Accent -->
            <div class="lanyard-slot"></div>

            <?php if (!empty($document_design->has_front) && !empty($document_design->front_url)): ?>
              <!-- School Custom Front Design Background -->
              <div class="card-bg-layer" style="position: absolute; inset: 0; width: 100%; height: 100%; z-index: 1; overflow: hidden;">
                <img src="<?php echo html_escape($document_design->front_url); ?>" alt="Front Design" style="width: 100%; height: 100%; object-fit: cover; display: block;" />
              </div>

              <!-- Dynamic Student Data Overlay Layer (Static School Details are inside the uploaded design) -->
              <?php $fc = $document_design->field_config ?? []; ?>
              <div class="card-content-layer custom-overlay-layer" style="position: absolute; inset: 0; width: 100%; height: 100%; z-index: 10; pointer-events: none;">
                <?php if (!empty($fc['photo']['enabled'])): ?>
                  <div style="position: absolute; top: <?php echo floatval($fc['photo']['top']); ?>%; left: <?php echo floatval($fc['photo']['left']); ?>%; width: <?php echo floatval($fc['photo']['width']); ?>%; height: <?php echo floatval($fc['photo']['height']); ?>%; border-radius: <?php echo html_escape($fc['photo']['radius'] ?? '8px'); ?>; overflow: hidden; border: <?php echo html_escape($fc['photo']['border'] ?? '1px solid #cbd5e1'); ?>; background: #ffffff;">
                    <?php if ($hasPhoto): ?>
                      <img src="<?php echo base_url('uploads/students/' . $st->photo); ?>" alt="<?php echo html_escape($fullName); ?>" style="width: 100%; height: 100%; object-fit: cover; display: block;" />
                    <?php else: ?>
                      <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #e2e8f0; font-size: 13pt; font-weight: bold; color: #475569;"><?php echo html_escape($initials); ?></div>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>

                <?php if (!empty($fc['student_name']['enabled'])): ?>
                  <div style="position: absolute; top: <?php echo floatval($fc['student_name']['top']); ?>%; left: <?php echo floatval($fc['student_name']['left']); ?>%; width: <?php echo floatval($fc['student_name']['width']); ?>%; font-size: <?php echo html_escape($fc['student_name']['font_size'] ?? '9.5pt'); ?>; font-weight: <?php echo html_escape($fc['student_name']['weight'] ?? 'bold'); ?>; color: <?php echo html_escape($fc['student_name']['color'] ?? '#0f172a'); ?>; text-align: <?php echo html_escape($fc['student_name']['align'] ?? 'center'); ?>; text-transform: uppercase;">
                    <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.1;"><?php echo html_escape($fullName); ?></div>
                    <div style="font-size: 6.5pt; font-weight: 800; color: <?php echo html_escape($fc['student_name']['color'] ?? '#0d9488'); ?>; opacity: 0.85; letter-spacing: 0.5px; margin-top: 1px;">STUDENT</div>
                  </div>
                <?php endif; ?>

                <?php echo $this->document_design_service->render_student_details_table($st, $fc, ['is_print' => true, 'is_bulk' => (count($students) > 1)]); ?>
              </div>
            <?php else: ?>
              <!-- Geometric Teal / Charcoal / Silver Background (SVG Fallback) -->
              <svg class="card-waves-svg" viewBox="0 0 204 325" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                  <linearGradient id="printFrontTeal_<?php echo $st->student_id; ?>" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#00656e" />
                    <stop offset="45%" stop-color="#087f8c" />
                    <stop offset="100%" stop-color="#023b42" />
                  </linearGradient>
                  <linearGradient id="printFrontDark_<?php echo $st->student_id; ?>" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#1e293b" />
                    <stop offset="100%" stop-color="#0a0f1d" />
                  </linearGradient>
                  <linearGradient id="printFrontSilver_<?php echo $st->student_id; ?>" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#f8fafc" />
                    <stop offset="50%" stop-color="#cbd5e1" />
                    <stop offset="100%" stop-color="#94a3b8" />
                  </linearGradient>
                </defs>
                <rect width="204" height="325" fill="#ffffff" />
                <path d="M0 0 H204 V86 L0 191 Z" fill="url(#printFrontTeal_<?php echo $st->student_id; ?>)" />
                <path d="M0 0 H73 L0 180 Z" fill="url(#printFrontDark_<?php echo $st->student_id; ?>)" />
                <polygon points="0,191 204,86 204,91 0,196" fill="url(#printFrontSilver_<?php echo $st->student_id; ?>)" />
                <path d="M204 325 H178 L204 299 Z" fill="url(#printFrontTeal_<?php echo $st->student_id; ?>)" opacity="0.25" />
              </svg>

              <!-- Card Content Layer (Fallback Built-in Template) -->
              <div class="card-content-layer">
                <!-- Front Header -->
                <div class="card-front-header">
                  <img src="<?php echo $logoPath; ?>" alt="Logo" class="school-logo-img"/>
                  <div>
                    <div class="school-name-text"><?php echo html_escape($this->current_school->school_name ?? $settings->school_name ?? 'SCHOOL'); ?></div>
                    <div class="school-sub-text"><?php echo html_escape($settings->card_title ?? 'IDENTITY CARD'); ?></div>
                  </div>
                </div>

                <!-- Student Photo Medallion -->
                <div class="student-photo-wrapper">
                  <div class="student-photo-inner">
                    <?php if ($hasPhoto): ?>
                      <img src="<?php echo base_url('uploads/students/' . $st->photo); ?>" alt="<?php echo html_escape($fullName); ?>" class="student-photo-img"/>
                    <?php else: ?>
                      <div class="student-photo-fallback"><?php echo html_escape($initials); ?></div>
                    <?php endif; ?>
                  </div>
                </div>

                <!-- Student Name & Role -->
                <div>
                  <div class="student-name-title">
                    <?php echo html_escape($fullName); ?>
                  </div>
                  <div class="student-role-title">
                    STUDENT
                  </div>
                </div>

                <!-- Details Table -->
                <div class="details-table-box">
                  <table class="details-table">
                    <tr>
                      <td class="lbl-col">ID</td>
                      <td class="sep-col">:</td>
                      <td class="val-col" style="font-family: monospace; font-weight: 800;"><?php echo html_escape($st->admission_number ?? 'EDU2026015'); ?></td>
                    </tr>
                    <tr>
                      <td class="lbl-col">Father's Name</td>
                      <td class="sep-col">:</td>
                      <td class="val-col"><?php echo html_escape($st->guardian_name ?: 'test'); ?></td>
                    </tr>
                    <tr>
                      <td class="lbl-col">Class & Sec</td>
                      <td class="sep-col">:</td>
                      <td class="val-col"><?php echo html_escape($classDisplay . ' - ' . $sectionDisplay); ?></td>
                    </tr>
                    <?php if (!empty($st->roll_number)): ?>
                    <tr>
                      <td class="lbl-col">Roll No.</td>
                      <td class="sep-col">:</td>
                      <td class="val-col" style="font-family: monospace;"><?php echo html_escape($st->roll_number); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                      <td class="lbl-col">D.O.B</td>
                      <td class="sep-col">:</td>
                      <td class="val-col"><?php echo $dobFormatted; ?></td>
                    </tr>
                    <tr>
                      <td class="lbl-col">Blood Group</td>
                      <td class="sep-col">:</td>
                      <td class="val-col blood-highlight"><?php echo html_escape($st->blood_group ?: 'A+'); ?></td>
                    </tr>
                  </table>
                </div>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <?php if ($side === 'both' || $side === 'back'): ?>
          <!-- BACK SIDE (CR80 Portrait: 54mm x 86mm) -->
          <div class="cr80-portrait-card">
            <!-- Lanyard Slot Accent -->
            <div class="lanyard-slot"></div>

            <?php if (!empty($document_design->has_back) && !empty($document_design->back_url)): ?>
              <!-- School Custom Back Design Background (Static School Info already present in design) -->
              <div class="card-bg-layer" style="position: absolute; inset: 0; width: 100%; height: 100%; z-index: 1; overflow: hidden;">
                <img src="<?php echo html_escape($document_design->back_url); ?>" alt="Back Design" style="width: 100%; height: 100%; object-fit: cover; display: block;" />
              </div>
            <?php else: ?>
              <!-- Geometric Teal / Charcoal / Silver Background (SVG Fallback) -->
              <svg class="card-waves-svg" viewBox="0 0 204 325" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                  <linearGradient id="printBackTeal_<?php echo $st->student_id; ?>" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#00656e" />
                    <stop offset="45%" stop-color="#087f8c" />
                    <stop offset="100%" stop-color="#023b42" />
                  </linearGradient>
                  <linearGradient id="printBackDark_<?php echo $st->student_id; ?>" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#1e293b" />
                    <stop offset="100%" stop-color="#0a0f1d" />
                  </linearGradient>
                  <linearGradient id="printBackSilver_<?php echo $st->student_id; ?>" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#f8fafc" />
                    <stop offset="50%" stop-color="#cbd5e1" />
                    <stop offset="100%" stop-color="#94a3b8" />
                  </linearGradient>
                </defs>
                <rect width="204" height="325" fill="#ffffff" />
                <path d="M0 0 H135 L0 72 Z" fill="url(#printBackTeal_<?php echo $st->student_id; ?>)" />
                <path d="M0 0 H48 L0 67 Z" fill="url(#printBackDark_<?php echo $st->student_id; ?>)" />
                <polygon points="135,0 139,0 0,76 0,72" fill="url(#printBackSilver_<?php echo $st->student_id; ?>)" />
                <path d="M69 325 L204 254 V325 Z" fill="url(#printBackTeal_<?php echo $st->student_id; ?>)" />
                <path d="M157 325 L204 258 V325 Z" fill="url(#printBackDark_<?php echo $st->student_id; ?>)" />
                <polygon points="69,325 65,325 204,250 204,254" fill="url(#printBackSilver_<?php echo $st->student_id; ?>)" />
              </svg>

              <!-- Card Content Layer (Fallback Built-in Template) -->
              <div class="card-content-layer">
                <!-- Back Header -->
                <div class="back-header-box">
                  <div class="back-school-name"><?php echo html_escape($settings->school_name ?? 'LOGIN2'); ?></div>
                  <div class="back-school-sub"><?php echo html_escape($settings->card_title ?? 'PUBLIC SCHOOL'); ?></div>
                </div>

                <!-- Terms and conditions -->
                <div class="back-terms-box">
                  <div class="back-terms-title">
                    <span class="back-term-dot" style="margin-top:0;"></span>
                    <span>Terms and conditions</span>
                  </div>
                  <div class="back-term-item">
                    <span class="back-term-dot"></span>
                    <span>Students are required to carry this card while on campus.</span>
                  </div>
                  <div class="back-term-item">
                    <span class="back-term-dot"></span>
                    <span>If lost or damaged, a duplicate will be issued per school regulations.</span>
                  </div>
                  <div class="back-term-item">
                    <span class="back-term-dot"></span>
                    <span>If you find this card, please return it to the school address below.</span>
                  </div>
                </div>

                <!-- Principal Signature Block -->
                <div class="back-sig-wrapper">
                  <?php if ($hasSig): ?>
                    <img src="<?php echo $sigPath; ?>" alt="Signature" class="back-sig-img"/>
                  <?php else: ?>
                    <div style="font-family: Georgia, serif; font-style: italic; font-size: 8.5pt; color: #1e293b; line-height: 1;">John</div>
                  <?php endif; ?>
                  <div class="back-sig-line"></div>
                  <div class="back-sig-title">Authorized Signature</div>
                </div>

                <!-- Dates & Validity -->
                <div class="back-dates-bar">
                  <div>Issue Date : <strong style="color: #0f172a; font-family: monospace;">01/06/2026</strong></div>
                  <div>Valid : <strong style="color: #0f172a;">Academic Session</strong></div>
                </div>

                <!-- Contact Info List -->
                <div class="back-info-list">
                  <div class="back-info-item">
                    <span class="material-symbols-outlined back-info-icon">call</span>
                    <span style="font-family: monospace;"><?php echo html_escape($settings->phone ?? '001 123 456 789'); ?></span>
                  </div>
                  <div class="back-info-item">
                    <span class="material-symbols-outlined back-info-icon">mail</span>
                    <span><?php echo html_escape($settings->email ?? 'info@login2school.com'); ?></span>
                  </div>
                  <div class="back-info-item">
                    <span class="material-symbols-outlined back-info-icon">globe</span>
                    <span><?php echo html_escape($settings->website ?? 'www.login2school.com'); ?></span>
                  </div>
                  <div class="back-info-item">
                    <span class="material-symbols-outlined back-info-icon">location_on</span>
                    <span style="font-size: 3.8pt; line-height: 1.1;"><?php echo html_escape($settings->school_address ?? '100/1 Bryant Lane, Manor, New York'); ?></span>
                  </div>
                </div>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

      </div>
    <?php endforeach; ?>
  </div>

  <script>
    window.addEventListener('DOMContentLoaded', () => {
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get('autoprint') === '1') {
        setTimeout(() => { window.print(); }, 500);
      }
    });
  </script>
</body>
</html>
