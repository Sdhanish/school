<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo html_escape($title); ?> - <?php echo html_escape($settings->school_name ?? 'School Management'); ?></title>
  <link href="<?php echo base_url('assets/fonts/inter.css'); ?>" rel="stylesheet"/>
  <link href="<?php echo base_url('assets/fonts/material-symbols.css'); ?>" rel="stylesheet"/>
  <script src="<?php echo base_url('assets/vendor/html2canvas.min.js'); ?>"></script>
  <script src="<?php echo base_url('assets/vendor/jspdf.umd.min.js'); ?>"></script>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
      background-color: #f8fafc;
      color: #0f172a;
      line-height: 1.5;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }

    /* Top Action Bar (hidden on print) */
    .action-bar {
      position: sticky;
      top: 0;
      z-index: 50;
      background: #ffffff;
      border-bottom: 1px solid #e2e8f0;
      padding: 12px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .action-bar-title {
      font-size: 15px;
      font-weight: 700;
      color: #006c4a;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .action-bar-btns {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 16px;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      transition: all 0.15s ease-in-out;
      border: 1px solid transparent;
    }
    .btn-primary {
      background: #006c4a;
      color: #ffffff;
    }
    .btn-primary:hover {
      background: #005338;
    }
    .btn-secondary {
      background: #0f172a;
      color: #ffffff;
    }
    .btn-secondary:hover {
      background: #1e293b;
    }
    .btn-outline {
      background: #ffffff;
      color: #334155;
      border-color: #cbd5e1;
    }
    .btn-outline:hover {
      background: #f1f5f9;
    }

    /* Document Sheet */
    .document-sheet {
      max-width: 1020px;
      margin: 28px auto 40px auto;
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 36px 44px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    }

    /* School Header */
    .school-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 2px solid #006c4a;
      padding-bottom: 20px;
      margin-bottom: 24px;
      gap: 20px;
    }
    .school-brand {
      display: flex;
      align-items: center;
      gap: 18px;
    }
    .school-logo {
      width: 72px;
      height: 72px;
      border-radius: 12px;
      object-fit: contain;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      padding: 4px;
    }
    .school-logo-placeholder {
      width: 72px;
      height: 72px;
      border-radius: 12px;
      background: linear-gradient(135deg, #006c4a 0%, #00875a 100%);
      color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
      font-weight: 800;
      letter-spacing: 1px;
    }
    .school-info h1 {
      font-size: 24px;
      font-weight: 800;
      color: #0f172a;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      line-height: 1.2;
    }
    .school-info p {
      font-size: 12px;
      color: #64748b;
      margin-top: 3px;
    }
    .school-meta-pill {
      text-align: right;
      background: #f0fdf4;
      border: 1px solid #bbf7d0;
      padding: 10px 18px;
      border-radius: 10px;
    }
    .school-meta-pill .label {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: #166534;
    }
    .school-meta-pill .year-name {
      font-size: 18px;
      font-weight: 800;
      color: #006c4a;
    }

    /* Stats Strip */
    .stats-strip {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      gap: 12px;
      margin-bottom: 24px;
    }
    .stat-card {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 10px 14px;
      text-align: center;
    }
    .stat-card.stat-holiday {
      background: #fef2f2;
      border-color: #fecaca;
    }
    .stat-card.stat-exam {
      background: #faf5ff;
      border-color: #e9d5ff;
    }
    .stat-card .stat-val {
      font-size: 18px;
      font-weight: 800;
      color: #0f172a;
    }
    .stat-card.stat-holiday .stat-val {
      color: #dc2626;
    }
    .stat-card.stat-exam .stat-val {
      color: #7e22ce;
    }
    .stat-card .stat-lbl {
      font-size: 11px;
      font-weight: 600;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-top: 2px;
    }
    .stat-card.stat-holiday .stat-lbl {
      color: #b91c1c;
    }

    /* Legend */
    .legend-bar {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 14px;
      padding: 10px 16px;
      background: #ffffff;
      border: 1px dashed #cbd5e1;
      border-radius: 8px;
      margin-bottom: 28px;
      font-size: 12px;
    }
    .legend-title {
      font-weight: 700;
      color: #334155;
      text-transform: uppercase;
      font-size: 11px;
      letter-spacing: 0.5px;
    }
    .legend-item {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-weight: 600;
    }
    .legend-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
    }

    /* Month Section */
    .month-section {
      margin-bottom: 30px;
      break-inside: avoid;
    }
    .month-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: #006c4a;
      color: #ffffff;
      padding: 8px 16px;
      border-radius: 8px 8px 0 0;
      font-size: 14px;
      font-weight: 700;
      letter-spacing: 0.5px;
    }
    .month-header .badge {
      background: rgba(255, 255, 255, 0.2);
      font-size: 11px;
      padding: 2px 8px;
      border-radius: 12px;
      font-weight: 600;
    }

    /* Table */
    .calendar-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 12.5px;
      border: 1px solid #e2e8f0;
      border-top: none;
      border-radius: 0 0 8px 8px;
      overflow: hidden;
    }
    .calendar-table th {
      background: #f1f5f9;
      color: #334155;
      font-weight: 700;
      text-transform: uppercase;
      font-size: 11px;
      letter-spacing: 0.5px;
      padding: 8px 12px;
      border-bottom: 1px solid #cbd5e1;
      text-align: left;
    }
    .calendar-table td {
      padding: 8px 12px;
      border-bottom: 1px solid #f1f5f9;
      vertical-align: middle;
    }
    .calendar-table tr:last-child td {
      border-bottom: none;
    }
    .calendar-table tr:nth-child(even) {
      background: #f8fafc;
    }

    /* Highlight Holiday Row in RED */
    .calendar-table tr.row-holiday {
      background-color: #fef2f2 !important;
    }
    .calendar-table tr.row-holiday td {
      border-bottom-color: #fee2e2;
    }
    .calendar-table tr.row-holiday .event-title {
      color: #991b1b;
      font-weight: 700;
    }

    /* Event Badges */
    .badge-type {
      display: inline-flex;
      align-items: center;
      padding: 2px 8px;
      border-radius: 4px;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      white-space: nowrap;
    }
    .badge-holiday {
      background: #dc2626;
      color: #ffffff;
      box-shadow: 0 1px 2px rgba(220, 38, 38, 0.2);
    }
    .badge-exam {
      background: #f3e8ff;
      color: #7e22ce;
      border: 1px solid #d8b4fe;
    }
    .badge-event {
      background: #e0f2fe;
      color: #0369a1;
      border: 1px solid #bae6fd;
    }
    .badge-activity {
      background: #dcfce7;
      color: #15803d;
      border: 1px solid #bbf7d0;
    }
    .badge-meeting {
      background: #fef3c7;
      color: #92400e;
      border: 1px solid #fde68a;
    }
    .badge-break {
      background: #ffedd5;
      color: #c2410c;
      border: 1px solid #fed7aa;
    }
    .badge-other {
      background: #f1f5f9;
      color: #475569;
      border: 1px solid #e2e8f0;
    }

    .date-box {
      font-weight: 700;
      color: #0f172a;
      white-space: nowrap;
    }
    .row-holiday .date-box {
      color: #b91c1c;
    }
    .date-sub {
      font-size: 11px;
      color: #64748b;
      font-weight: normal;
    }
    .event-title {
      font-weight: 600;
      color: #0f172a;
    }
    .event-desc {
      font-size: 11px;
      color: #64748b;
      margin-top: 2px;
    }

    /* Document Footer */
    .document-footer {
      margin-top: 36px;
      padding-top: 20px;
      border-top: 2px solid #e2e8f0;
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      font-size: 11px;
      color: #64748b;
    }
    .signature-area {
      text-align: center;
      width: 200px;
    }
    .signature-line {
      border-top: 1px solid #0f172a;
      margin-top: 50px;
      padding-top: 4px;
      font-weight: 700;
      color: #0f172a;
      font-size: 11.5px;
      text-transform: uppercase;
    }

    /* Print Styles */
    @media print {
      body {
        background: #ffffff;
        padding: 0;
      }
      .action-bar {
        display: none !important;
      }
      .document-sheet {
        margin: 0;
        padding: 10mm 15mm;
        border: none;
        box-shadow: none;
        max-width: 100%;
        border-radius: 0;
      }
      .month-section {
        break-inside: avoid;
        page-break-inside: avoid;
      }
    }
  </style>
</head>
<body>

  <!-- Sticky Action Bar (Hidden in Print) -->
  <div class="action-bar">
    <div class="action-bar-title">
      <span class="material-symbols-outlined text-[20px]">calendar_month</span>
      <span>Academic Calendar PDF Export — <?php echo html_escape($academic_year ? $academic_year->year_name : ''); ?></span>
    </div>
    <div class="action-bar-btns">
      <button onclick="downloadAsPdf()" id="btn-download-pdf" class="btn btn-secondary">
        <span class="material-symbols-outlined text-[17px]">download</span>
        <span>Download PDF</span>
      </button>
      <button onclick="window.print()" class="btn btn-primary">
        <span class="material-symbols-outlined text-[17px]">print</span>
        <span>Print Calendar</span>
      </button>
      <a href="<?php echo site_url('academics/calendar?academic_year_id=' . ($academic_year ? $academic_year->academic_year_id : 1)); ?>" class="btn btn-outline">
        <span class="material-symbols-outlined text-[17px]">close</span>
        <span>Close</span>
      </a>
    </div>
  </div>

  <!-- Printable Academic Calendar Sheet -->
  <div class="document-sheet" id="printable-sheet">

    <!-- School Header -->
    <?php if (!empty($document_design->has_header) && !empty($document_design->header_url)): ?>
      <div style="margin: -36px -44px 24px -44px; border-radius: 12px 12px 0 0; overflow: hidden; border-bottom: 2px solid #006c4a;">
        <img src="<?php echo html_escape($document_design->header_url); ?>" alt="Academic Calendar Header" style="width: 100%; height: auto; display: block;" />
      </div>
      <div style="text-align: center; margin-bottom: 20px;">
        <div style="display: inline-block; padding: 4px 14px; background: #e0f2fe; color: #0369a1; font-size: 12px; font-weight: 700; border-radius: 6px; text-transform: uppercase;">
          Academic Session <?php echo html_escape($academic_year ? $academic_year->year_name : 'Annual Calendar'); ?>
        </div>
      </div>
    <?php else: ?>
      <div class="school-header">
        <div class="school-brand">
          <?php if (!empty($logo_url)): ?>
            <img src="<?php echo $logo_url; ?>" alt="School Logo" class="school-logo"/>
          <?php else: ?>
            <div class="school-logo-placeholder">
              <?php echo strtoupper(substr($settings->school_name ?? 'L2', 0, 2)); ?>
            </div>
          <?php endif; ?>
          <div class="school-info">
            <h1><?php echo html_escape($settings->school_name ?? 'SCHOOL MANAGEMENT SYSTEM'); ?></h1>
            <p>
              <?php echo html_escape($settings->address ?? 'Institutional Campus Area'); ?>
              <?php if (!empty($settings->phone)): ?> · Ph: <?php echo html_escape($settings->phone); ?><?php endif; ?>
              <?php if (!empty($settings->email)): ?> · Email: <?php echo html_escape($settings->email); ?><?php endif; ?>
              <?php if (!empty($settings->school_code)): ?> · Code: <?php echo html_escape($settings->school_code); ?><?php endif; ?>
            </p>
          </div>
        </div>
        <div class="school-meta-pill">
          <div class="label">Academic Session</div>
          <div class="year-name"><?php echo html_escape($academic_year ? $academic_year->year_name : '2026–2027'); ?></div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Quick Stats Cards -->
    <div class="stats-strip">
      <div class="stat-card">
        <div class="stat-val"><?php echo $total_events; ?></div>
        <div class="stat-lbl">Total Events</div>
      </div>
      <div class="stat-card stat-holiday">
        <div class="stat-val"><?php echo $total_holidays; ?></div>
        <div class="stat-lbl">Holidays</div>
      </div>
      <div class="stat-card stat-exam">
        <div class="stat-val"><?php echo $total_exams; ?></div>
        <div class="stat-lbl">Examinations</div>
      </div>
      <div class="stat-card">
        <div class="stat-val"><?php echo $total_breaks; ?></div>
        <div class="stat-lbl">Vacations / Breaks</div>
      </div>
      <div class="stat-card">
        <div class="stat-val"><?php echo $total_activities + $total_meetings; ?></div>
        <div class="stat-lbl">Activities & Meets</div>
      </div>
    </div>

    <!-- Category Legend -->
    <div class="legend-bar">
      <span class="legend-title">Categories:</span>
      <span class="legend-item"><span class="legend-dot" style="background:#dc2626;"></span> Holiday (Red)</span>
      <span class="legend-item"><span class="legend-dot" style="background:#7e22ce;"></span> Examination</span>
      <span class="legend-item"><span class="legend-dot" style="background:#0369a1;"></span> Event</span>
      <span class="legend-item"><span class="legend-dot" style="background:#15803d;"></span> Activity / Sports</span>
      <span class="legend-item"><span class="legend-dot" style="background:#92400e;"></span> Meeting / PTM</span>
      <span class="legend-item"><span class="legend-dot" style="background:#c2410c;"></span> Term Break</span>
    </div>

    <!-- Month-Wise Calendar Data -->
    <?php if (empty($events_by_month)): ?>
      <div style="padding: 48px; text-align: center; color: #64748b; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;">
        <span class="material-symbols-outlined" style="font-size: 40px; color: #94a3b8; display: block; margin-bottom: 8px;">calendar_today</span>
        <h3 style="font-size: 16px; font-weight: 700; color: #334155;">No calendar events found for Academic Year <?php echo html_escape($academic_year ? $academic_year->year_name : ''); ?></h3>
        <p style="font-size: 13px; margin-top: 4px;">Events and holidays added to this academic session will appear here.</p>
      </div>
    <?php else: ?>
      <?php foreach ($events_by_month as $month_data): ?>
        <div class="month-section">
          <div class="month-header">
            <span><?php echo html_escape($month_data['label']); ?></span>
            <span class="badge"><?php echo count($month_data['events']); ?> <?php echo count($month_data['events']) === 1 ? 'Entry' : 'Entries'; ?></span>
          </div>
          <table class="calendar-table">
            <thead>
              <tr>
                <th style="width: 22%;">Date & Day</th>
                <th style="width: 38%;">Event / Holiday Details</th>
                <th style="width: 16%;">Category</th>
                <th style="width: 12%;">Audience</th>
                <th style="width: 12%;">Venue</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($month_data['events'] as $e): ?>
                <?php
                  $isHoliday = ($e->event_type === 'Holiday');
                  $rowClass = $isHoliday ? 'row-holiday' : '';

                  $badgeClass = 'badge-other';
                  if ($e->event_type === 'Holiday') $badgeClass = 'badge-holiday';
                  elseif ($e->event_type === 'Exam') $badgeClass = 'badge-exam';
                  elseif ($e->event_type === 'Event') $badgeClass = 'badge-event';
                  elseif ($e->event_type === 'Activity') $badgeClass = 'badge-activity';
                  elseif ($e->event_type === 'Meeting') $badgeClass = 'badge-meeting';
                  elseif ($e->event_type === 'Term Break') $badgeClass = 'badge-break';
                ?>
                <tr class="<?php echo $rowClass; ?>">
                  <td>
                    <div class="date-box">
                      <?php echo date('d M Y', strtotime($e->start_date)); ?>
                      <?php if (!empty($e->end_date) && $e->end_date !== $e->start_date): ?>
                        <span class="date-sub"> to <?php echo date('d M Y', strtotime($e->end_date)); ?></span>
                      <?php endif; ?>
                    </div>
                    <div class="date-sub"><?php echo date('l', strtotime($e->start_date)); ?></div>
                  </td>
                  <td>
                    <div class="event-title">
                      <?php if ($isHoliday): ?>
                        <span style="color: #dc2626; margin-right: 2px;">★</span>
                      <?php endif; ?>
                      <?php echo html_escape($e->title); ?>
                    </div>
                    <?php if (!empty($e->description)): ?>
                      <div class="event-desc"><?php echo html_escape($e->description); ?></div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="badge-type <?php echo $badgeClass; ?>">
                      <?php echo html_escape($e->event_type); ?>
                    </span>
                  </td>
                  <td>
                    <span style="font-size: 11.5px; color: #475569; font-weight: 500;">
                      <?php echo html_escape($e->audience); ?>
                    </span>
                  </td>
                  <td>
                    <span style="font-size: 11.5px; color: #475569;">
                      <?php echo !empty($e->venue) ? html_escape($e->venue) : '—'; ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <!-- Document Footer -->
    <?php if (!empty($document_design->has_footer) && !empty($document_design->footer_url)): ?>
      <div style="margin: 24px -44px -36px -44px; border-radius: 0 0 12px 12px; overflow: hidden; border-top: 1px solid #e2e8f0;">
        <img src="<?php echo html_escape($document_design->footer_url); ?>" alt="Academic Calendar Footer" style="width: 100%; height: auto; display: block;" />
      </div>
    <?php else: ?>
      <div class="document-footer">
        <div>
          <p><strong><?php echo html_escape($settings->school_name ?? 'School Management System'); ?></strong> · Academic Calendar</p>
          <p>Printed / Exported on: <?php echo date('d M Y, h:i A'); ?> · Confidential & Official School Document</p>
        </div>
        <div class="signature-area">
          <div class="signature-line">Principal / Authorized Signatory</div>
        </div>
      </div>
    <?php endif; ?>

  </div>

  <script>
    async function downloadAsPdf() {
      const btn = document.getElementById('btn-download-pdf');
      const originalText = btn.innerHTML;
      btn.innerHTML = '<span class="material-symbols-outlined text-[17px] animate-spin">sync</span><span>Generating PDF...</span>';
      btn.disabled = true;

      try {
        const { jsPDF } = window.jspdf;
        const sheet = document.getElementById('printable-sheet');

        const canvas = await html2canvas(sheet, {
          scale: 2,
          useCORS: true,
          logging: false,
          backgroundColor: '#ffffff'
        });

        const imgData = canvas.toDataURL('image/jpeg', 0.98);
        const pdf = new jsPDF('p', 'mm', 'a4');
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = pdf.internal.pageSize.getHeight();
        const imgWidth = pdfWidth - 10;
        const imgHeight = (canvas.height * imgWidth) / canvas.width;

        let heightLeft = imgHeight;
        let position = 5;

        pdf.addImage(imgData, 'JPEG', 5, position, imgWidth, imgHeight);
        heightLeft -= (pdfHeight - 10);

        while (heightLeft > 0) {
          position = heightLeft - imgHeight + 5;
          pdf.addPage();
          pdf.addImage(imgData, 'JPEG', 5, position, imgWidth, imgHeight);
          heightLeft -= (pdfHeight - 10);
        }

        const fileName = 'Academic_Calendar_<?php echo preg_replace("/[^A-Za-z0-9_-]/", "_", $academic_year ? $academic_year->year_name : "Session"); ?>.pdf';
        pdf.save(fileName);
      } catch (err) {
        console.error('PDF generation error:', err);
        // Fallback to browser print dialog
        window.print();
      } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
      }
    }

    // Handle autoprint or autodownload
    window.addEventListener('DOMContentLoaded', function() {
      <?php if (!empty($autoprint)): ?>
        window.print();
      <?php elseif (!empty($autodownload)): ?>
        downloadAsPdf();
      <?php endif; ?>
    });
  </script>
</body>
</html>
