<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Preview - <?php echo html_escape($doc_meta['label'] ?? 'Document Design'); ?></title>
  <link href="<?php echo base_url('assets/fonts/inter.css'); ?>" rel="stylesheet" />
  <link href="<?php echo base_url('assets/fonts/material-symbols.css'); ?>" rel="stylesheet" />
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background-color: #f1f5f9;
      color: #0f172a;
      padding: 24px;
      display: flex;
      justify-content: center;
    }
    .sheet {
      width: 100%;
      max-width: 820px;
      background: #ffffff;
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.06);
      padding: 0;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      min-height: 900px;
      position: relative;
    }
    .header-zone, .footer-zone {
      width: 100%;
      line-height: 0;
    }
    .header-zone img, .footer-zone img {
      width: 100%;
      height: auto;
      display: block;
    }
    .header-placeholder {
      margin: 20px;
      border: 2px dashed #94a3b8;
      border-radius: 8px;
      padding: 20px;
      background: #f8fafc;
      color: #64748b;
      font-size: 13px;
      line-height: normal;
      text-align: center;
    }
    .footer-placeholder {
      margin: 20px;
      border: 2px dashed #94a3b8;
      border-radius: 8px;
      padding: 16px;
      background: #f8fafc;
      color: #64748b;
      font-size: 12px;
      line-height: normal;
      text-align: center;
    }
    .content-zone {
      margin: 24px 32px;
      flex-grow: 1;
      border: 1px solid #e2e8f0;
      border-radius: 6px;
      padding: 20px;
      background: #fcfdfe;
    }
    .meta-bar {
      display: flex;
      justify-content: space-between;
      margin-bottom: 16px;
      padding-bottom: 10px;
      border-bottom: 1px solid #e2e8f0;
      font-size: 12px;
    }
    .sample-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 12px;
      font-size: 12px;
    }
    .sample-table th, .sample-table td {
      border: 1px solid #e2e8f0;
      padding: 8px 10px;
      text-align: left;
    }
    .sample-table th {
      background: #f1f5f9;
      font-weight: 600;
    }
    .badge {
      display: inline-block;
      padding: 2px 8px;
      font-size: 11px;
      border-radius: 4px;
      background: #e0f2fe;
      color: #0369a1;
      font-weight: 600;
    }
  </style>
</head>
<body>

  <?php if ($document_type === 'id_card'): ?>
    <!-- =======================================================================
         SPECIALIZED CR80 PORTRAIT ID CARD PREVIEW (FRONT & BACK DUAL VIEW)
         ======================================================================= -->
    <div style="width: 100%; max-width: 900px; display: flex; flex-direction: column; align-items: center; gap: 20px;">
      
      <!-- Preview Meta Banner -->
      <div style="width: 100%; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 12px; padding: 14px 20px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.04);">
        <div>
          <div style="display: flex; align-items: center; gap: 8px;">
            <span class="material-symbols-outlined" style="color: #006c4a; font-size: 20px;">badge</span>
            <strong style="font-size: 15px; color: #0f172a;">Student ID Card Design Preview</strong>
            <span class="badge" style="background: #e0f2fe; color: #0369a1;">Sample Student Data</span>
          </div>
          <div style="font-size: 12px; color: #64748b; margin-top: 3px;">
            School: <strong><?php echo html_escape($school->school_name ?? 'School'); ?></strong> · Standard CR80 Portrait (54mm × 86mm / 2.125" × 3.375")
          </div>
        </div>

        <div style="display: flex; gap: 10px; font-size: 12px;">
          <div style="display: flex; align-items: center; gap: 6px;">
            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: <?php echo !empty($design->has_front) ? '#16a34a' : '#94a3b8'; ?>;"></span>
            <span>Front: <strong><?php echo !empty($design->has_front) ? 'Custom Design' : 'Default Wave'; ?></strong></span>
          </div>
          <div style="display: flex; align-items: center; gap: 6px;">
            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: <?php echo !empty($design->has_back) ? '#16a34a' : '#94a3b8'; ?>;"></span>
            <span>Back: <strong><?php echo !empty($design->has_back) ? 'Custom Design' : 'Default Wave'; ?></strong></span>
          </div>
        </div>
      </div>

      <!-- Dual Cards Stage Container -->
      <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 35px; padding: 24px 10px; width: 100%;">
        
        <!-- ================= 1. FRONT ID CARD ================= -->
        <div style="display: flex; flex-direction: column; align-items: center;">
          <div style="font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
            Front Side
          </div>

          <div style="position: relative; width: 280px; height: 445px; border-radius: 18px; overflow: hidden; border: 1px solid #cbd5e1; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.12); background: #ffffff;">
            <!-- Lanyard Slot Accent Hole -->
            <div style="position: absolute; top: 8px; left: 50%; transform: translateX(-50%); width: 48px; height: 8px; background: rgba(255,255,255,0.9); border: 1px solid #cbd5e1; border-radius: 9999px; z-index: 20;"></div>

            <!-- Background Layer -->
            <?php if (!empty($design->has_front) && !empty($design->front_url)): ?>
              <img src="<?php echo html_escape($design->front_url); ?>" alt="Front Design" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; z-index: 1;" />

              <!-- Dynamic Student Data Overlay Layer (Static School Details are inside the uploaded design) -->
              <?php $fc = $design->field_config ?? []; ?>
              <div style="position: absolute; inset: 0; width: 100%; height: 100%; z-index: 10; pointer-events: none;">
                <?php if (!empty($fc['photo']['enabled'])): ?>
                  <div style="position: absolute; top: <?php echo floatval($fc['photo']['top']); ?>%; left: <?php echo floatval($fc['photo']['left']); ?>%; width: <?php echo floatval($fc['photo']['width']); ?>%; height: <?php echo floatval($fc['photo']['height']); ?>%; border-radius: <?php echo html_escape($fc['photo']['radius'] ?? '10px'); ?>; overflow: hidden; border: <?php echo html_escape($fc['photo']['border'] ?? '2px solid #cbd5e1'); ?>; background: #ffffff; display: flex; align-items: center; justify-content: center;">
                    <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #0f766e, #044e54); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 22px; letter-spacing: 1px;">
                      JD
                    </div>
                  </div>
                <?php endif; ?>

                <?php if (!empty($fc['student_name']['enabled'])): ?>
                  <div style="position: absolute; top: <?php echo floatval($fc['student_name']['top']); ?>%; left: <?php echo floatval($fc['student_name']['left']); ?>%; width: <?php echo floatval($fc['student_name']['width']); ?>%; font-size: <?php echo html_escape($fc['student_name']['font_size'] ?? '9.5pt'); ?>; font-weight: <?php echo html_escape($fc['student_name']['weight'] ?? 'bold'); ?>; color: <?php echo html_escape($fc['student_name']['color'] ?? '#0f172a'); ?>; text-align: <?php echo html_escape($fc['student_name']['align'] ?? 'center'); ?>; text-transform: uppercase;">
                    <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.1;">JOHN DOE</div>
                    <div style="font-size: 6.5pt; font-weight: 800; color: <?php echo html_escape($fc['student_name']['color'] ?? '#0d9488'); ?>; opacity: 0.85; letter-spacing: 0.5px; margin-top: 1px;">STUDENT</div>
                  </div>
                <?php endif; ?>

                <?php
                  $sample_student = (object)[
                    'admission_number' => 'SCH20260115467',
                    'guardian_name'    => 'Mr. Menon',
                    'class_name'       => 'LKG',
                    'division_name'    => 'A',
                    'roll_number'      => '1',
                    'date_of_birth'    => '2022-03-11',
                    'blood_group'      => 'A+',
                  ];
                  echo $this->document_design_service->render_student_details_table($sample_student, $fc, ['context' => 'preview']);
                ?>
              </div>
            <?php else: ?>
              <!-- Geometric SVG Wave Fallback -->
              <svg style="position: absolute; inset: 0; width: 100%; height: 100%; pointer-events: none; z-index: 1;" viewBox="0 0 280 445" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                  <linearGradient id="prevFrontTeal" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#00656e" />
                    <stop offset="45%" stop-color="#087f8c" />
                    <stop offset="100%" stop-color="#023b42" />
                  </linearGradient>
                  <linearGradient id="prevFrontDark" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#1e293b" />
                    <stop offset="100%" stop-color="#0a0f1d" />
                  </linearGradient>
                  <linearGradient id="prevFrontSilver" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#f8fafc" />
                    <stop offset="50%" stop-color="#cbd5e1" />
                    <stop offset="100%" stop-color="#94a3b8" />
                  </linearGradient>
                </defs>
                <rect width="280" height="445" fill="#ffffff" />
                <path d="M0 0 H280 V118 L0 262 Z" fill="url(#prevFrontTeal)" />
                <path d="M0 0 H100 L0 246 Z" fill="url(#prevFrontDark)" />
                <polygon points="0,262 280,118 280,124 0,268" fill="url(#prevFrontSilver)" />
                <path d="M280 445 H245 L280 410 Z" fill="url(#prevFrontTeal)" opacity="0.25" />
              </svg>

              <!-- Content Layer (Fallback Built-in Template) -->
              <div style="position: relative; z-index: 10; height: 100%; display: flex; flex-direction: column; justify-content: space-between; padding: 14px 14px 12px 14px; box-sizing: border-box;">
                <!-- Header -->
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 2px 4px 0 4px;">
                  <div style="height: 32px; max-width: 95px; display: flex; align-items: center;">
                    <img src="<?php echo base_url('assets/logo.png'); ?>" alt="Logo" style="max-height: 28px; max-width: 90px; object-fit: contain; filter: brightness(1.1);"/>
                  </div>
                  <div style="text-align: right; min-width: 0; flex: 1; padding-left: 8px;">
                    <div style="font-weight: 900; font-size: 11px; color: #ffffff; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.1;">
                      <?php echo html_escape($school->school_name ?? 'LOGIN2 ACADEMY'); ?>
                    </div>
                    <div style="font-weight: 700; font-size: 8px; color: #ccfbf1; text-transform: uppercase; letter-spacing: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.1; margin-top: 1px;">
                      STUDENT IDENTITY CARD
                    </div>
                  </div>
                </div>

                <!-- Student Photo Medallion -->
                <div style="margin: 4px auto; text-align: center;">
                  <div style="width: 92px; height: 92px; border-radius: 50%; padding: 3px; background: #ffffff; box-shadow: 0 4px 12px rgba(0,0,0,0.2), 0 0 0 2px #cbd5e1; display: inline-flex; align-items: center; justify-content: center;">
                    <div style="width: 100%; height: 100%; border-radius: 50%; background: linear-gradient(135deg, #0f766e, #044e54); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 22px; letter-spacing: 1px;">
                      JD
                    </div>
                  </div>
                </div>

                <!-- Student Name & Role -->
                <div style="text-align: center; margin-top: -2px;">
                  <div style="font-weight: 900; font-size: 13px; color: #0f766e; text-transform: uppercase; letter-spacing: 0.4px; line-height: 1.1;">
                    JOHN DOE
                  </div>
                  <div style="font-weight: 800; font-size: 9px; color: #0d9488; text-transform: uppercase; letter-spacing: 0.6px; margin-top: 2px;">
                    STUDENT
                  </div>
                </div>

                <!-- Details Grid Table -->
                <div style="background: rgba(248, 250, 252, 0.92); border: 1px solid #e2e8f0; border-radius: 10px; padding: 7px 10px 7px 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                  <table style="width: 100%; border-collapse: collapse; font-size: 9px; line-height: 1.25;">
                    <tr>
                      <td style="color: #0f766e; font-weight: 800; text-transform: uppercase; width: 75px; padding: 2px 0;">ID</td>
                      <td style="color: #94a3b8; font-weight: 700; width: 10px; text-align: center;">:</td>
                      <td style="color: #0f172a; font-weight: 900; font-family: monospace; padding-left: 4px;">EDU2026015</td>
                    </tr>
                    <tr>
                      <td style="color: #0f766e; font-weight: 800; text-transform: uppercase; padding: 2px 0;">Father's Name</td>
                      <td style="color: #94a3b8; font-weight: 700; text-align: center;">:</td>
                      <td style="color: #0f172a; font-weight: 700; padding-left: 4px;">Robert Doe</td>
                    </tr>
                    <tr>
                      <td style="color: #0f766e; font-weight: 800; text-transform: uppercase; padding: 2px 0;">Class & Div</td>
                      <td style="color: #94a3b8; font-weight: 700; text-align: center;">:</td>
                      <td style="color: #0f172a; font-weight: 700; padding-left: 4px;">Grade 10 - A</td>
                    </tr>
                    <tr>
                      <td style="color: #0f766e; font-weight: 800; text-transform: uppercase; padding: 2px 0;">Roll No.</td>
                      <td style="color: #94a3b8; font-weight: 700; text-align: center;">:</td>
                      <td style="color: #0f172a; font-weight: 800; font-family: monospace; padding-left: 4px;">0125</td>
                    </tr>
                    <tr>
                      <td style="color: #0f766e; font-weight: 800; text-transform: uppercase; padding: 2px 0;">D.O.B</td>
                      <td style="color: #94a3b8; font-weight: 700; text-align: center;">:</td>
                      <td style="color: #0f172a; font-weight: 700; padding-left: 4px;">15-06-2012</td>
                    </tr>
                    <tr>
                      <td style="color: #0f766e; font-weight: 800; text-transform: uppercase; padding: 2px 0;">Blood Group</td>
                      <td style="color: #94a3b8; font-weight: 700; text-align: center;">:</td>
                      <td style="color: #e11d48; font-weight: 900; padding-left: 4px;">B+</td>
                    </tr>
                  </table>
                </div>

                <div style="height: 2px;"></div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- ================= 2. BACK ID CARD ================= -->
        <div style="display: flex; flex-direction: column; align-items: center;">
          <div style="font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
            Back Side
          </div>

          <div style="position: relative; width: 280px; height: 445px; border-radius: 18px; overflow: hidden; border: 1px solid #cbd5e1; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.12); background: #ffffff;">
            <!-- Lanyard Slot Accent Hole -->
            <div style="position: absolute; top: 8px; left: 50%; transform: translateX(-50%); width: 48px; height: 8px; background: rgba(255,255,255,0.9); border: 1px solid #cbd5e1; border-radius: 9999px; z-index: 20;"></div>

            <!-- Background Layer -->
            <?php if (!empty($design->has_back) && !empty($design->back_url)): ?>
              <img src="<?php echo html_escape($design->back_url); ?>" alt="Back Design" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; z-index: 1;" />

              <!-- Dynamic Back Fields Overlay Layer -->
              <?php $bc = $design->back_field_config ?? []; ?>
              <div style="position: absolute; inset: 0; width: 100%; height: 100%; z-index: 10; pointer-events: none;">
                <?php if (!empty($bc['instructions']['enabled'])): ?>
                  <div style="position: absolute; top: <?php echo floatval($bc['instructions']['top']); ?>%; left: <?php echo floatval($bc['instructions']['left']); ?>%; width: <?php echo floatval($bc['instructions']['width']); ?>%; font-size: <?php echo html_escape($bc['instructions']['font_size'] ?? '8pt'); ?>; font-weight: <?php echo html_escape($bc['instructions']['weight'] ?? 'normal'); ?>; color: <?php echo html_escape($bc['instructions']['color'] ?? '#334155'); ?>; text-align: <?php echo html_escape($bc['instructions']['align'] ?? 'left'); ?>;">
                    Students are required to carry this card while on campus. If lost or damaged, report immediately.
                  </div>
                <?php endif; ?>

                <?php if (!empty($bc['principal_signature']['enabled'])): ?>
                  <div style="position: absolute; top: <?php echo floatval($bc['principal_signature']['top']); ?>%; left: <?php echo floatval($bc['principal_signature']['left']); ?>%; width: <?php echo floatval($bc['principal_signature']['width']); ?>%; text-align: <?php echo html_escape($bc['principal_signature']['align'] ?? 'center'); ?>;">
                    <div style="font-family: Georgia, serif; font-style: italic; font-size: 12px; color: #1e293b;">Principal</div>
                    <div style="width: 80px; height: 1px; background: #94a3b8; margin: 2px auto;"></div>
                    <div style="font-size: 7pt; font-weight: bold; color: #64748b;">Authorized Signature</div>
                  </div>
                <?php endif; ?>

                <?php if (!empty($bc['emergency_contact']['enabled'])): ?>
                  <div style="position: absolute; top: <?php echo floatval($bc['emergency_contact']['top']); ?>%; left: <?php echo floatval($bc['emergency_contact']['left']); ?>%; width: <?php echo floatval($bc['emergency_contact']['width']); ?>%; font-size: <?php echo html_escape($bc['emergency_contact']['font_size'] ?? '7.5pt'); ?>; font-weight: <?php echo html_escape($bc['emergency_contact']['weight'] ?? 'normal'); ?>; color: <?php echo html_escape($bc['emergency_contact']['color'] ?? '#475569'); ?>; text-align: <?php echo html_escape($bc['emergency_contact']['align'] ?? 'left'); ?>;">
                    <span style="font-weight: bold;"><?php echo html_escape($bc['emergency_contact']['label'] ?? 'Contact: '); ?></span>+1 555 019 2834
                  </div>
                <?php endif; ?>

                <?php if (!empty($bc['school_address']['enabled'])): ?>
                  <div style="position: absolute; top: <?php echo floatval($bc['school_address']['top']); ?>%; left: <?php echo floatval($bc['school_address']['left']); ?>%; width: <?php echo floatval($bc['school_address']['width']); ?>%; font-size: <?php echo html_escape($bc['school_address']['font_size'] ?? '7pt'); ?>; font-weight: <?php echo html_escape($bc['school_address']['weight'] ?? 'normal'); ?>; color: <?php echo html_escape($bc['school_address']['color'] ?? '#475569'); ?>; text-align: <?php echo html_escape($bc['school_address']['align'] ?? 'left'); ?>;">
                    <?php echo html_escape($school->address ?? 'Main Campus, High Street'); ?>
                  </div>
                <?php endif; ?>
              </div>
            <?php else: ?>
              <!-- Geometric SVG Wave Fallback -->
              <svg style="position: absolute; inset: 0; width: 100%; height: 100%; pointer-events: none; z-index: 1;" viewBox="0 0 280 445" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                  <linearGradient id="prevBackTeal" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#00656e" />
                    <stop offset="45%" stop-color="#087f8c" />
                    <stop offset="100%" stop-color="#023b42" />
                  </linearGradient>
                  <linearGradient id="prevBackDark" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#1e293b" />
                    <stop offset="100%" stop-color="#0a0f1d" />
                  </linearGradient>
                  <linearGradient id="prevBackSilver" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#f8fafc" />
                    <stop offset="50%" stop-color="#cbd5e1" />
                    <stop offset="100%" stop-color="#94a3b8" />
                  </linearGradient>
                </defs>
                <rect width="280" height="445" fill="#ffffff" />
                <path d="M0 0 H185 L0 98 Z" fill="url(#prevBackTeal)" />
                <path d="M0 0 H65 L0 92 Z" fill="url(#prevBackDark)" />
                <polygon points="185,0 191,0 0,104 0,98" fill="url(#prevBackSilver)" />
                <path d="M95 445 L280 348 V445 Z" fill="url(#prevBackTeal)" />
                <path d="M215 445 L280 353 V445 Z" fill="url(#prevBackDark)" />
                <polygon points="95,445 89,445 280,342 280,348" fill="url(#prevBackSilver)" />
              </svg>

              <!-- Content Layer (Fallback Built-in Template) -->
              <div style="position: relative; z-index: 10; height: 100%; display: flex; flex-direction: column; justify-content: space-between; padding: 14px 14px 12px 14px; box-sizing: border-box;">
                <!-- Back Header -->
                <div style="text-align: right; padding-top: 6px;">
                  <div style="font-weight: 900; font-size: 11px; color: #0f766e; text-transform: uppercase; letter-spacing: 0.4px; line-height: 1.1;">
                    <?php echo html_escape($school->school_name ?? 'LOGIN2 ACADEMY'); ?>
                  </div>
                  <div style="font-weight: 700; font-size: 8px; color: #64748b; text-transform: uppercase; letter-spacing: 0.6px; margin-top: 2px;">
                    INSTRUCTIONS & VERIFICATION
                  </div>
                </div>

              <!-- Terms and Conditions -->
              <div style="padding: 0 4px; font-size: 8.5px; line-height: 1.35; color: #334155;">
                <div style="font-weight: 900; font-size: 9.5px; color: #0f766e; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 5px; display: flex; align-items: center; gap: 4px;">
                  <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #0f766e;"></span>
                  <span>Terms and conditions</span>
                </div>
                <div style="margin-bottom: 4px;">• Students are required to carry this card while on campus.</div>
                <div style="margin-bottom: 4px;">• If lost or damaged, a duplicate will be issued per school regulations.</div>
                <div>• If found, please return this card to the school address below.</div>
              </div>

              <!-- Principal Signature Block -->
              <div style="text-align: center; margin-top: 4px;">
                <div style="font-family: Georgia, serif; font-style: italic; font-size: 14px; color: #1e293b; line-height: 1;">
                  Authorized Signature
                </div>
                <div style="width: 100px; height: 1px; background: #cbd5e1; margin: 3px auto;"></div>
                <div style="font-size: 8px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.4px;">
                  Principal Signature
                </div>
              </div>

              <!-- Dates & Validity -->
              <div style="display: flex; justify-content: space-between; font-size: 8px; font-weight: 700; color: #334155; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 4px 8px;">
                <div>Issue Date: <span style="font-family: monospace; color: #0f172a;">01/06/2026</span></div>
                <div>Valid: <span style="color: #0f172a;">Academic Session</span></div>
              </div>

              <!-- Contact Info -->
              <div style="font-size: 8px; color: #1e293b; font-weight: 600; display: flex; flex-direction: column; gap: 3px; padding: 0 4px;">
                <div style="display: flex; align-items: center; gap: 6px;">
                  <span class="material-symbols-outlined" style="font-size: 11px; color: #0f766e;">call</span>
                  <span>+1 555 019 2834</span>
                </div>
                <div style="display: flex; align-items: center; gap: 6px;">
                  <span class="material-symbols-outlined" style="font-size: 11px; color: #0f766e;">mail</span>
                  <span>office@<?php echo strtolower(preg_replace('/[^a-z0-9]/', '', $school->school_code ?? 'school')); ?>.edu</span>
                </div>
                <div style="display: flex; align-items: center; gap: 6px;">
                  <span class="material-symbols-outlined" style="font-size: 11px; color: #0f766e;">location_on</span>
                  <span style="font-size: 7.5px;"><?php echo html_escape($school->address ?? 'Main Campus, High Street'); ?></span>
                </div>
              </div>

              <!-- Return Notice -->
              <div style="font-size: 7.5px; font-weight: 700; color: #0f766e; text-align: center; padding-top: 2px;">
                If found, please return this card to the school.
              </div>

            </div>
            <?php endif; ?>
          </div>
        </div>

      </div>

    </div>

  <?php else: ?>

    <div class="sheet">
      
      <!-- 1. Header Zone -->
      <div class="header-zone">
        <?php if (!empty($design->has_header) && !empty($design->header_url)): ?>
          <img src="<?php echo html_escape($design->header_url); ?>" alt="Document Header" />
          <?php if ($design->is_fallback_header): ?>
            <div style="font-size: 11px; color: #15803d; font-weight: 600; padding: 4px; text-align: center; background: #f0fdf4; border-bottom: 1px solid #bbf7d0; line-height: normal;">(Rendered from School Default Header)</div>
          <?php endif; ?>
        <?php else: ?>
          <div class="header-placeholder">
            <strong>NO HEADER IMAGE CONFIGURED</strong>
            <div style="font-size: 11px; margin-top: 4px;">System will render the existing default layout/logo for <?php echo html_escape($school->school_name ?? 'this school'); ?>.</div>
          </div>
        <?php endif; ?>
      </div>

      <!-- 2. Document Content Sample Area -->
      <div class="content-zone">
        <div class="meta-bar">
          <div>
            <strong>Document:</strong> <?php echo html_escape($doc_meta['label'] ?? $document_type); ?>
            <span class="badge" style="margin-left: 6px;">Sample Preview</span>
          </div>
          <div>
            <strong>School:</strong> <?php echo html_escape($school->school_name ?? 'Model School'); ?>
          </div>
        </div>

        <div style="font-size: 13px; line-height: 1.6; color: #334155;">
          <p style="margin-bottom: 10px;">
            This interactive preview illustrates how your uploaded <strong>Header Image</strong> and <strong>Footer Image</strong> will encapsulate the generated report data across printouts and mPDF documents.
          </p>
          
          <table class="sample-table">
            <thead>
              <tr>
                <th>Reference / Roll #</th>
                <th>Student Name</th>
                <th>Class & Section</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>ADM-2026-081</td>
                <td>Aarav Sharma</td>
                <td>Grade 10 - Division A</td>
                <td><span style="color: #16a34a; font-weight: bold;">Verified</span></td>
                <td><?php echo date('d M Y'); ?></td>
              </tr>
              <tr>
                <td>ADM-2026-082</td>
                <td>Diya Patel</td>
                <td>Grade 10 - Division A</td>
                <td><span style="color: #16a34a; font-weight: bold;">Verified</span></td>
                <td><?php echo date('d M Y'); ?></td>
              </tr>
            </tbody>
          </table>

          <div style="margin-top: 24px; padding-top: 14px; border-top: 1px dashed #cbd5e1; display: flex; justify-content: space-between; font-size: 11px; color: #64748b;">
            <div>Authorized Signatory</div>
            <div>Principal / Examination Controller</div>
          </div>
        </div>
      </div>

      <!-- 3. Footer Zone -->
      <div class="footer-zone" style="margin-top: auto;">
        <?php if (!empty($design->has_footer) && !empty($design->footer_url)): ?>
          <img src="<?php echo html_escape($design->footer_url); ?>" alt="Document Footer" />
          <?php if ($design->is_fallback_footer): ?>
            <div style="font-size: 11px; color: #15803d; font-weight: 600; padding: 4px; text-align: center; background: #f0fdf4; border-top: 1px solid #bbf7d0; line-height: normal;">(Rendered from School Default Footer)</div>
          <?php endif; ?>
        <?php else: ?>
          <div class="footer-placeholder">
            <strong>NO FOOTER IMAGE CONFIGURED</strong>
            <div style="font-size: 11px; margin-top: 4px;">System will render the existing default footer for <?php echo html_escape($school->school_name ?? 'this school'); ?>.</div>
          </div>
        <?php endif; ?>
      </div>

    </div>

  <?php endif; ?>

</body>
</html>
