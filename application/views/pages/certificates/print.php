<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <title><?php echo html_escape($cert->certificate_no); ?> - <?php echo html_escape($cert->certificate_type); ?></title>
  <style>
    @page { size: A4 portrait; margin: 15mm; }
    body {
      font-family: 'Times New Roman', Times, serif;
      background: #fff;
      color: #111;
      margin: 0;
      padding: 20px;
    }
    .cert-frame {
      border: 3px double #333;
      padding: 40px;
      position: relative;
      min-height: 900px;
      box-sizing: border-box;
    }
    .cert-header {
      text-align: center;
      border-bottom: 2px solid #555;
      padding-bottom: 20px;
      margin-bottom: 30px;
    }
    .school-title {
      font-size: 26px;
      font-weight: bold;
      text-transform: uppercase;
      margin: 0 0 5px 0;
    }
    .school-subtitle {
      font-size: 13px;
      font-family: Arial, sans-serif;
      color: #444;
      margin: 0;
    }
    .cert-badge {
      display: inline-block;
      margin-top: 20px;
      padding: 8px 30px;
      border: 2px solid #222;
      font-size: 16px;
      font-weight: bold;
      text-transform: uppercase;
      font-family: Arial, sans-serif;
      letter-spacing: 2px;
    }
    .meta-bar {
      display: flex;
      justify-content: space-between;
      font-family: monospace;
      font-size: 12px;
      margin-bottom: 15px;
    }
    .cert-body {
      font-size: 17px;
      line-height: 2;
      text-align: justify;
      margin: 40px 0;
    }
    .cert-body p { margin-bottom: 20px; }
    .cert-body table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 15px; }
    .cert-body td { padding: 8px 4px; }
    .cert-footer {
      position: absolute;
      bottom: 40px;
      left: 40px;
      right: 40px;
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      font-family: Arial, sans-serif;
    }
    .sig-box {
      text-align: center;
      width: 180px;
    }
    .sig-line {
      border-top: 1px solid #222;
      padding-top: 5px;
      font-size: 12px;
      font-weight: bold;
      text-transform: uppercase;
    }
    .seal-box {
      width: 110px;
      height: 110px;
      border: 2px dashed #888;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 10px;
      color: #888;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    .watermark {
      position: absolute;
      top: 45%;
      left: 15%;
      right: 15%;
      text-align: center;
      font-size: 70px;
      color: rgba(0,0,0,0.04);
      transform: rotate(-35deg);
      font-weight: bold;
      text-transform: uppercase;
      pointer-events: none;
      user-select: none;
    }
    @media print {
      body { padding: 0; }
      .no-print { display: none; }
    }
  </style>
</head>
<body onload="window.print()">
  <div class="cert-frame">
    <?php if ($settings->watermark_enabled): ?>
      <div class="watermark"><?php echo html_escape($school->school_name ?? 'LOGIN2'); ?></div>
    <?php endif; ?>

    <div class="cert-header">
      <div class="meta-bar">
        <div>REF NO: <?php echo html_escape($cert->certificate_no); ?></div>
        <div>DATE: <?php echo date('d-m-Y', strtotime($cert->issue_date)); ?></div>
      </div>
      <?php if (!empty($document_design->has_header) && !empty($document_design->header_url)): ?>
        <div style="margin-bottom: 16px;">
          <img src="<?php echo html_escape($document_design->header_url); ?>" alt="Certificate Header" style="width: 100%; height: auto; display: block;" />
        </div>
      <?php else: ?>
        <h1 class="school-title"><?php echo html_escape($school->school_name ?? 'SCHOOL MANAGEMENT SYSTEM'); ?></h1>
        <p class="school-subtitle"><?php echo html_escape($school->address ?? ''); ?> • Phone: <?php echo html_escape($school->phone ?? ''); ?></p>
      <?php endif; ?>
      
      <div>
        <span class="cert-badge"><?php echo html_escape($cert->header_content ?: $cert->certificate_type); ?></span>
      </div>
    </div>

    <div class="cert-body">
      <?php echo $cert->generated_content; ?>
    </div>

    <?php if (!empty($document_design->has_footer) && !empty($document_design->footer_url)): ?>
      <div style="margin: 20px 0 15px 0;">
        <img src="<?php echo html_escape($document_design->footer_url); ?>" alt="Certificate Footer" style="width: 100%; height: auto; display: block;" />
      </div>
    <?php endif; ?>

    <div class="cert-footer">
      <div class="sig-box">
        <div style="height: 40px;"></div>
        <div class="sig-line">Verified By</div>
      </div>

      <div class="seal-box">Official Seal</div>

      <div class="sig-box">
        <div style="height: 40px; font-weight: bold; font-family: serif;"><?php echo html_escape($school->principal_name ?? 'Principal'); ?></div>
        <div class="sig-line">Principal</div>
      </div>
    </div>
  </div>
</body>
</html>
