<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en" class="light">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo html_escape(($title ?? 'Official Document') . ' - ' . ($current_school->school_name ?? 'Login2')); ?></title>
  <link href="<?php echo base_url('assets/fonts/inter.css'); ?>" rel="stylesheet"/>
  <link href="<?php echo base_url('assets/fonts/material-symbols.css'); ?>" rel="stylesheet"/>
  <script src="<?php echo base_url('assets/vendor/tailwind.min.js'); ?>"></script>
  <link href="<?php echo base_url('assets/app.css'); ?>" rel="stylesheet"/>
  <style>
    /* Document preview styling */
    body {
      background-color: #f1f5f9;
      color: #0f172a;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      margin: 0;
      padding: 0;
      -webkit-font-smoothing: antialiased;
    }

    .document-page-wrapper {
      min-height: 100vh;
      padding: 24px 16px 48px;
    }

    /* Print Architecture: 100% Isolated Clean Document */
    @media print {
      @page {
        margin: 0;
        size: auto;
      }
      html, body {
        background: #ffffff !important;
        color: #000000 !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        height: auto !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }
      .no-print,
      .print\:hidden,
      .document-action-bar,
      #sidebar-root,
      #header-root,
      nav,
      aside,
      header {
        display: none !important;
        visibility: hidden !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden !important;
      }
      .document-page-wrapper {
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
      }
      .report-card-container,
      .receipt-sheet,
      .tc-container,
      #report-sheet,
      .sheet,
      .document-sheet {
        border: none !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
      }
      .document-design-header,
      .document-design-footer {
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
      }
      .document-design-header img,
      .document-design-footer img {
        width: 100% !important;
        height: auto !important;
        display: block !important;
        margin: 0 !important;
      }
    }
  </style>
</head>
<body class="bg-surface-container-low text-on-surface font-body-lg">
  <div class="document-page-wrapper">
