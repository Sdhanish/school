<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title><?php echo html_escape($student_name); ?> - Attendance Report</title>
<style>
  body {
    font-family: 'dejavusans', 'freeserif', sans-serif;
    color: #0f172a;
    font-size: 8.5pt;
    line-height: 1.35;
    margin: 0;
    padding: 0;
  }

  /* First Page Official Header */
  .official-header {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 2mm;
  }
  .logo-cell {
    width: 25mm;
    vertical-align: top;
    padding-right: 3mm;
  }
  .logo-img {
    width: 22mm;
    height: 22mm;
  }
  .logo-placeholder {
    width: 22mm;
    height: 22mm;
    background-color: #006c4a;
    color: #ffffff;
    font-size: 16pt;
    font-weight: bold;
    text-align: center;
    line-height: 22mm;
    border-radius: 2mm;
  }
  .school-details-cell {
    vertical-align: middle;
  }
  .school-title {
    font-size: 14pt;
    font-weight: bold;
    color: #0f172a;
    margin-bottom: 1mm;
  }
  .school-meta {
    font-size: 8pt;
    color: #475569;
    line-height: 1.4;
  }
  .header-rule {
    border-bottom: 0.6mm solid #006c4a;
    margin-top: 2.5mm;
    margin-bottom: 2.5mm;
  }

  /* Title Banner Box */
  .report-title-banner {
    width: 100%;
    background-color: #f1f5f9;
    border: 0.25mm solid #e2e8f0;
    border-radius: 1.5mm;
    padding: 2.5mm 3.5mm;
    margin-bottom: 3.5mm;
  }
  .banner-table {
    width: 100%;
    border-collapse: collapse;
  }
  .banner-title {
    font-size: 10.5pt;
    font-weight: bold;
    color: #006c4a;
    text-align: left;
    width: 58%;
  }
  .banner-session {
    font-size: 8.5pt;
    font-weight: bold;
    color: #0f172a;
    text-align: right;
    width: 42%;
  }
  .banner-period {
    font-size: 7.5pt;
    color: #64748b;
    text-align: left;
    padding-top: 1mm;
  }
  .banner-generated {
    font-size: 7.5pt;
    color: #64748b;
    text-align: right;
    padding-top: 1mm;
  }

  /* Student Info Box */
  .student-info-box {
    width: 100%;
    border: 0.25mm solid #cbd5e1;
    border-radius: 1.5mm;
    background-color: #ffffff;
    padding: 2.5mm 3.5mm;
    margin-bottom: 4mm;
  }
  .student-info-table {
    width: 100%;
    border-collapse: collapse;
  }
  .student-name-header {
    font-size: 10.5pt;
    font-weight: bold;
    color: #0f172a;
    padding-bottom: 1.5mm;
  }
  .info-lbl {
    font-size: 8pt;
    color: #475569;
    width: 22mm;
    padding: 0.8mm 0;
  }
  .info-val {
    font-size: 8pt;
    font-weight: bold;
    color: #0f172a;
    width: 42mm;
    padding: 0.8mm 0;
  }
  .student-photo-cell {
    width: 22mm;
    vertical-align: top;
    text-align: right;
    padding-left: 2mm;
  }
  .student-photo-img {
    width: 20mm;
    height: 24mm;
    border: 0.2mm solid #cbd5e1;
    border-radius: 1mm;
  }

  /* Section Titles */
  .section-title {
    font-size: 9pt;
    font-weight: bold;
    color: #0f172a;
    margin-top: 3.5mm;
    margin-bottom: 1.5mm;
  }

  /* Summary KPI Cards Table */
  .kpi-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 1.5mm;
    margin-bottom: 3.5mm;
  }
  .kpi-card {
    background-color: #f8fafc;
    border: 0.25mm solid #e2e8f0;
    border-radius: 1.5mm;
    text-align: center;
    padding: 2mm 1mm;
  }
  .kpi-card.highlight {
    background-color: #e6f5ee;
    border: 0.35mm solid #006c4a;
  }
  .kpi-val {
    font-size: 11pt;
    font-weight: bold;
    color: #0f172a;
  }
  .kpi-card.highlight .kpi-val {
    color: #006c4a;
  }
  .kpi-lbl {
    font-size: 6.8pt;
    color: #64748b;
    margin-top: 0.5mm;
  }

  /* Data Tables */
  .data-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 3.5mm;
    font-size: 7.5pt;
  }
  .data-table th {
    background-color: #f1f5f9;
    color: #334155;
    font-size: 7.5pt;
    font-weight: bold;
    border: 0.2mm solid #cbd5e1;
    padding: 1.8mm 1.5mm;
    text-align: left;
  }
  .data-table th.center {
    text-align: center;
  }
  .data-table td {
    border: 0.2mm solid #e2e8f0;
    padding: 1.6mm 1.5mm;
    color: #0f172a;
    vertical-align: middle;
    word-wrap: break-word;
  }
  .data-table td.center {
    text-align: center;
  }
  .data-table tr.zebra {
    background-color: #f8fafc;
  }
  .data-table tr {
    page-break-inside: avoid;
  }

  /* Status Colors */
  .status-present {
    color: #006c4a;
    font-weight: bold;
  }
  .status-absent {
    color: #b91c1c;
    font-weight: bold;
  }
  .status-late {
    color: #b45309;
    font-weight: bold;
  }
  .status-excused {
    color: #1d4ed8;
    font-weight: bold;
  }

  .pct-good {
    color: #006c4a;
    font-weight: bold;
  }
  .pct-bad {
    color: #b91c1c;
    font-weight: bold;
  }

  .empty-cell {
    text-align: center;
    padding: 3.5mm !important;
    color: #64748b;
    font-style: italic;
  }
  .report-content-wrapper {
    margin-left: 10mm;
    margin-right: 10mm;
  }
</style>
</head>
<body>

  <?php $has_custom_header = !empty($document_design->has_header) && !empty($document_design->header_path) && file_exists($document_design->header_path); ?>

  <!-- Configure Running Header: On all pages when custom design exists; starting page 2 for fallback -->
  <sethtmlpageheader name="runningHeader" value="on" show-this-page="<?php echo $has_custom_header ? '1' : '0'; ?>" />

  <div class="report-content-wrapper">

  <?php if (!$has_custom_header): ?>
    <!-- First Page Fallback Header (Only when no custom design image is configured) -->
    <table class="official-header">
      <tr>
        <?php if (!empty($school_logo_src)): ?>
          <td class="logo-cell">
            <img src="<?php echo html_escape($school_logo_src); ?>" class="logo-img" alt="Logo" />
          </td>
        <?php elseif (!empty($school_name)): ?>
          <td class="logo-cell">
            <div class="logo-placeholder"><?php echo strtoupper(substr($school_name, 0, 2)); ?></div>
          </td>
        <?php endif; ?>
        <td class="school-details-cell">
          <div class="school-title"><?php echo html_escape($school_name); ?></div>
          <?php if (!empty($school_address)): ?>
            <div class="school-meta"><?php echo html_escape($school_address); ?></div>
          <?php endif; ?>
          <?php if (!empty($school_contact)): ?>
            <div class="school-meta"><?php echo html_escape($school_contact); ?></div>
          <?php endif; ?>
        </td>
      </tr>
    </table>
    <div class="header-rule"></div>
  <?php endif; ?>

  <!-- Report Title Banner -->
  <div class="report-title-banner">
    <table class="banner-table">
      <tr>
        <td class="banner-title">STUDENT ATTENDANCE REPORT</td>
        <td class="banner-session">Session: <?php echo html_escape($academic_year_name); ?></td>
      </tr>
      <tr>
        <td class="banner-period">Period: <?php echo html_escape($attendance_period); ?></td>
        <td class="banner-generated">Generated: <?php echo html_escape($generated_at); ?></td>
      </tr>
    </table>
  </div>

  <!-- Student Details Box -->
  <div class="student-info-box">
    <table class="student-info-table">
      <tr>
        <td style="vertical-align: top;">
          <div class="student-name-header"><?php echo html_escape($student_name); ?></div>
          <table style="width: 100%; border-collapse: collapse;">
            <tr>
              <td class="info-lbl">Admission No:</td>
              <td class="info-val"><?php echo html_escape($admission_number); ?></td>
              <td class="info-lbl">Roll No:</td>
              <td class="info-val"><?php echo html_escape($roll_number ?: '—'); ?></td>
              <td class="info-lbl">Gender:</td>
              <td class="info-val"><?php echo html_escape($gender ?: '—'); ?></td>
            </tr>
            <tr>
              <td class="info-lbl">Class &amp; Div:</td>
              <td class="info-val"><?php echo html_escape($class_name ?: '—'); ?></td>
              <td class="info-lbl">Division:</td>
              <td class="info-val"><?php echo html_escape($division_name ?: '—'); ?></td>
              <td class="info-lbl">Group:</td>
              <td class="info-val"><?php echo html_escape($group_name ?: '—'); ?></td>
            </tr>
            <tr>
              <td class="info-lbl">Guardian:</td>
              <td class="info-val"><?php echo html_escape($guardian_display ?: '—'); ?></td>
              <td class="info-lbl">Contact:</td>
              <td class="info-val"><?php echo html_escape($contact_phone ?: '—'); ?></td>
              <td class="info-lbl">Status:</td>
              <td class="info-val" style="color: #006c4a;"><?php echo !empty($student_active) ? 'Active' : 'Inactive'; ?></td>
            </tr>
          </table>
        </td>
        <?php if (!empty($student_photo_src)): ?>
          <td class="student-photo-cell">
            <img src="<?php echo html_escape($student_photo_src); ?>" class="student-photo-img" alt="Photo" />
          </td>
        <?php endif; ?>
      </tr>
    </table>
  </div>

  <?php
    $att = $student->attendance ?? null;
    $hasDaily = !empty($att->has_daily);
    $hasPeriod = !empty($att->has_period);
    $mode = $att->attendance_mode ?? ($hasPeriod ? 'period' : 'daily');
    $is_higher_sec = !empty($att->is_higher_sec) || $hasPeriod || $mode === 'period';
  ?>

  <!-- 1. Attendance Summaries -->
  <?php if ($mode === 'both' || ($hasDaily && $hasPeriod)): ?>
    <!-- Day-wise KPI -->
    <?php if (!empty($att->day_summary)): ?>
      <div class="section-title">DAY-WISE ATTENDANCE SUMMARY</div>
      <table class="kpi-table">
        <tr>
          <td class="kpi-card" style="width: 16.6%;">
            <div class="kpi-val"><?php echo (string)$att->day_summary->working_days; ?></div>
            <div class="kpi-lbl">Total Working Days</div>
          </td>
          <td class="kpi-card" style="width: 16.6%;">
            <div class="kpi-val"><?php echo (string)$att->day_summary->present; ?></div>
            <div class="kpi-lbl">Days Present</div>
          </td>
          <td class="kpi-card" style="width: 16.6%;">
            <div class="kpi-val"><?php echo (string)$att->day_summary->absent; ?></div>
            <div class="kpi-lbl">Days Absent</div>
          </td>
          <td class="kpi-card" style="width: 16.6%;">
            <div class="kpi-val"><?php echo (string)$att->day_summary->late; ?></div>
            <div class="kpi-lbl">Late Arrivals</div>
          </td>
          <td class="kpi-card" style="width: 16.6%;">
            <div class="kpi-val"><?php echo (string)$att->day_summary->excused; ?></div>
            <div class="kpi-lbl">Excused / Leave</div>
          </td>
          <td class="kpi-card highlight" style="width: 17%;">
            <div class="kpi-val"><?php echo number_format($att->day_summary->percentage, 1); ?>%</div>
            <div class="kpi-lbl">Overall Attendance</div>
          </td>
        </tr>
      </table>
    <?php endif; ?>

    <!-- Period-wise KPI -->
    <?php if (!empty($att->period_summary)): ?>
      <div class="section-title">PERIOD-WISE ATTENDANCE SUMMARY</div>
      <table class="kpi-table">
        <tr>
          <td class="kpi-card" style="width: 16.6%;">
            <div class="kpi-val"><?php echo (string)$att->period_summary->total_periods; ?></div>
            <div class="kpi-lbl">Scheduled Periods</div>
          </td>
          <td class="kpi-card" style="width: 16.6%;">
            <div class="kpi-val"><?php echo (string)$att->period_summary->present; ?></div>
            <div class="kpi-lbl">Periods Present</div>
          </td>
          <td class="kpi-card" style="width: 16.6%;">
            <div class="kpi-val"><?php echo (string)$att->period_summary->absent; ?></div>
            <div class="kpi-lbl">Periods Absent</div>
          </td>
          <td class="kpi-card" style="width: 16.6%;">
            <div class="kpi-val"><?php echo (string)$att->period_summary->late; ?></div>
            <div class="kpi-lbl">Late Periods</div>
          </td>
          <td class="kpi-card" style="width: 16.6%;">
            <div class="kpi-val"><?php echo (string)$att->period_summary->excused; ?></div>
            <div class="kpi-lbl">Excused / Leave</div>
          </td>
          <td class="kpi-card highlight" style="width: 17%;">
            <div class="kpi-val"><?php echo number_format($att->period_summary->percentage, 1); ?>%</div>
            <div class="kpi-lbl">Period Attendance</div>
          </td>
        </tr>
      </table>
    <?php endif; ?>

  <?php elseif ($is_higher_sec && !empty($att->period_summary)): ?>
    <!-- Period Summary only -->
    <div class="section-title">PERIOD-WISE ATTENDANCE SUMMARY</div>
    <table class="kpi-table">
      <tr>
        <td class="kpi-card" style="width: 16.6%;">
          <div class="kpi-val"><?php echo (string)$att->period_summary->total_periods; ?></div>
          <div class="kpi-lbl">Scheduled Periods</div>
        </td>
        <td class="kpi-card" style="width: 16.6%;">
          <div class="kpi-val"><?php echo (string)$att->period_summary->present; ?></div>
          <div class="kpi-lbl">Periods Present</div>
        </td>
        <td class="kpi-card" style="width: 16.6%;">
          <div class="kpi-val"><?php echo (string)$att->period_summary->absent; ?></div>
          <div class="kpi-lbl">Periods Absent</div>
        </td>
        <td class="kpi-card" style="width: 16.6%;">
          <div class="kpi-val"><?php echo (string)$att->period_summary->late; ?></div>
          <div class="kpi-lbl">Late Periods</div>
        </td>
        <td class="kpi-card" style="width: 16.6%;">
          <div class="kpi-val"><?php echo (string)$att->period_summary->excused; ?></div>
          <div class="kpi-lbl">Excused / Leave</div>
        </td>
        <td class="kpi-card highlight" style="width: 17%;">
          <div class="kpi-val"><?php echo number_format($att->period_summary->percentage, 1); ?>%</div>
          <div class="kpi-lbl">Period Attendance</div>
        </td>
      </tr>
    </table>

  <?php elseif (!empty($att->day_summary)): ?>
    <!-- Day Summary only -->
    <div class="section-title">DAY-WISE ATTENDANCE SUMMARY</div>
    <table class="kpi-table">
      <tr>
        <td class="kpi-card" style="width: 16.6%;">
          <div class="kpi-val"><?php echo (string)$att->day_summary->working_days; ?></div>
          <div class="kpi-lbl">Total Working Days</div>
        </td>
        <td class="kpi-card" style="width: 16.6%;">
          <div class="kpi-val"><?php echo (string)$att->day_summary->present; ?></div>
          <div class="kpi-lbl">Days Present</div>
        </td>
        <td class="kpi-card" style="width: 16.6%;">
          <div class="kpi-val"><?php echo (string)$att->day_summary->absent; ?></div>
          <div class="kpi-lbl">Days Absent</div>
        </td>
        <td class="kpi-card" style="width: 16.6%;">
          <div class="kpi-val"><?php echo (string)$att->day_summary->late; ?></div>
          <div class="kpi-lbl">Late Arrivals</div>
        </td>
        <td class="kpi-card" style="width: 16.6%;">
          <div class="kpi-val"><?php echo (string)$att->day_summary->excused; ?></div>
          <div class="kpi-lbl">Excused / Leave</div>
        </td>
        <td class="kpi-card highlight" style="width: 17%;">
          <div class="kpi-val"><?php echo number_format($att->day_summary->percentage, 1); ?>%</div>
          <div class="kpi-lbl">Overall Attendance</div>
        </td>
      </tr>
    </table>
  <?php endif; ?>

  <!-- 2. Subject-wise Attendance Breakdown (if period student or subject data exists) -->
  <?php if ($is_higher_sec || !empty($att->subject_summary)): ?>
    <div class="section-title">SUBJECT-WISE ATTENDANCE BREAKDOWN</div>
    <table class="data-table" repeat_header="1">
      <thead>
        <tr>
          <th style="width: 32%;">Subject</th>
          <th class="center" style="width: 13%;">Code</th>
          <th class="center" style="width: 12%;">Total Classes</th>
          <th class="center" style="width: 11%;">Present</th>
          <th class="center" style="width: 10%;">Absent</th>
          <th class="center" style="width: 11%;">Late/Excused</th>
          <th class="center" style="width: 11%;">Attendance %</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($att->subject_summary)): ?>
          <tr>
            <td colspan="7" class="empty-cell">No subject-wise attendance recorded for this student in selected date range.</td>
          </tr>
        <?php else: ?>
          <?php $i = 0; foreach ($att->subject_summary as $s): ?>
            <?php
              $late_exc = ((int)($s->late ?? 0)) + ((int)($s->excused ?? 0));
              $pct = (float)$s->percentage;
            ?>
            <tr class="<?php echo ($i++ % 2 === 1) ? 'zebra' : ''; ?>">
              <td><?php echo html_escape($s->subject_name); ?></td>
              <td class="center"><?php echo html_escape($s->subject_code ?: '-'); ?></td>
              <td class="center"><?php echo (string)$s->total_classes; ?></td>
              <td class="center"><?php echo (string)$s->present; ?></td>
              <td class="center"><?php echo (string)$s->absent; ?></td>
              <td class="center"><?php echo (string)$late_exc; ?></td>
              <td class="center <?php echo $pct >= 75 ? 'pct-good' : 'pct-bad'; ?>"><?php echo number_format($pct, 1); ?>%</td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <!-- 3. Month-wise Attendance Breakdown Table -->
  <div class="section-title">MONTH-WISE ATTENDANCE BREAKDOWN</div>
  <?php
    $monthlyList = $is_higher_sec ? ($att->period_monthly ?? array()) : ($att->day_monthly ?? array());
  ?>
  <table class="data-table" repeat_header="1">
    <thead>
      <tr>
        <th style="width: 27%;">Month</th>
        <th class="center" style="width: 13%;"><?php echo $is_higher_sec ? 'Present Periods' : 'Present Days'; ?></th>
        <th class="center" style="width: 13%;"><?php echo $is_higher_sec ? 'Absent Periods' : 'Absent Days'; ?></th>
        <th class="center" style="width: 11%;">Late</th>
        <th class="center" style="width: 11%;">Excused</th>
        <th class="center" style="width: 12%;"><?php echo $is_higher_sec ? 'Total Periods' : 'Total Days'; ?></th>
        <th class="center" style="width: 13%;">Percentage</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($monthlyList)): ?>
        <tr>
          <td colspan="7" class="empty-cell">No monthly attendance logged for selected date range.</td>
        </tr>
      <?php else: ?>
        <?php $i = 0; foreach ($monthlyList as $m): ?>
          <?php
            $pres = $is_higher_sec ? ($m->present_periods ?? 0) : ($m->present_count ?? 0);
            $abs  = $is_higher_sec ? ($m->absent_periods ?? 0) : ($m->absent_count ?? 0);
            $late = $is_higher_sec ? ($m->late_periods ?? 0) : ($m->late_count ?? 0);
            $exc  = $is_higher_sec ? ($m->excused_periods ?? 0) : ($m->excused_count ?? 0);
            $tot  = $is_higher_sec ? ($m->total_periods ?? 0) : ($m->total_days ?? 0);
            $pct  = (float)($m->percentage ?? 0);
          ?>
          <tr class="<?php echo ($i++ % 2 === 1) ? 'zebra' : ''; ?>">
            <td><?php echo html_escape($m->month_name); ?></td>
            <td class="center"><?php echo (string)$pres; ?></td>
            <td class="center"><?php echo (string)$abs; ?></td>
            <td class="center"><?php echo (string)$late; ?></td>
            <td class="center"><?php echo (string)$exc; ?></td>
            <td class="center"><?php echo (string)$tot; ?></td>
            <td class="center <?php echo $pct >= 75 ? 'pct-good' : 'pct-bad'; ?>"><?php echo number_format($pct, 1); ?>%</td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- 4. Detailed Attendance Logs -->
  <?php if ($is_higher_sec): ?>
    <div class="section-title">DETAILED PERIOD-WISE ATTENDANCE LOGS</div>
    <!--
      MANDATORY REQUIREMENT:
      Column Order:
      1. Date
      2. Period
      3. Subject
      4. Status
      5. Marked By
      6. Remarks (LAST COLUMN)
    -->
    <table class="data-table" repeat_header="1">
      <thead>
        <tr>
          <th style="width: 17%;">Date</th>
          <th style="width: 13%;">Period</th>
          <th style="width: 20%;">Subject</th>
          <th class="center" style="width: 12%;">Status</th>
          <th style="width: 20%;">Marked By</th>
          <th style="width: 18%;">Remarks</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($att->period_records)): ?>
          <tr>
            <td colspan="6" class="empty-cell">No period-wise attendance records found for the selected date range.</td>
          </tr>
        <?php else: ?>
          <?php $i = 0; foreach ($att->period_records as $r): ?>
            <?php
              $date_str = date('d M Y', strtotime($r->attendance_date));
              $period_str = !empty($r->period_name) ? $r->period_name : ('Period ' . ($r->period_number ?: $r->period_id));
              $subject_str = !empty($r->subject_name) ? $r->subject_name : '-';
              $status_str = $r->attendance_status;
              $marker_str = !empty($r->marked_by_display) ? $r->marked_by_display : 'Not Available';
              $remarks_str = !empty($r->remarks) ? $r->remarks : '-';

              $status_class = 'status-present';
              if ($status_str === 'Absent') {
                  $status_class = 'status-absent';
              } elseif (in_array($status_str, array('Late', 'Late Coming'))) {
                  $status_class = 'status-late';
              } elseif (in_array($status_str, array('Excused', 'Leave', 'Half Day', 'Late / Half Day', 'Half-day'))) {
                  $status_class = 'status-excused';
              }
            ?>
            <tr class="<?php echo ($i++ % 2 === 1) ? 'zebra' : ''; ?>">
              <td><?php echo html_escape($date_str); ?></td>
              <td><?php echo html_escape($period_str); ?></td>
              <td><?php echo html_escape($subject_str); ?></td>
              <td class="center <?php echo $status_class; ?>"><?php echo html_escape($status_str); ?></td>
              <td><?php echo html_escape($marker_str); ?></td>
              <td><?php echo html_escape($remarks_str); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>

  <?php else: ?>
    <!-- Day-wise Student Detailed Attendance Logs -->
    <div class="section-title">DETAILED DAY-WISE ATTENDANCE LOGS</div>
    <table class="data-table" repeat_header="1">
      <thead>
        <tr>
          <th style="width: 22%;">Date</th>
          <th class="center" style="width: 15%;">Status</th>
          <th style="width: 38%;">Marked By</th>
          <th style="width: 25%;">Remarks</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($att->day_records)): ?>
          <tr>
            <td colspan="4" class="empty-cell">No attendance records found for the selected date range.</td>
          </tr>
        <?php else: ?>
          <?php $i = 0; foreach ($att->day_records as $r): ?>
            <?php
              $date_str = date('d M Y', strtotime($r->attendance_date)) . (!empty($r->day_name) ? ' (' . substr($r->day_name, 0, 3) . ')' : '');
              $status_str = $r->attendance_status;
              $marker_str = !empty($r->marked_by_display) ? $r->marked_by_display : 'Not Available';
              $remarks_str = !empty($r->remarks) ? $r->remarks : '-';

              $status_class = 'status-present';
              if ($status_str === 'Absent') {
                  $status_class = 'status-absent';
              } elseif (in_array($status_str, array('Late', 'Late Coming'))) {
                  $status_class = 'status-late';
              } elseif (in_array($status_str, array('Excused', 'Leave', 'Half Day', 'Late / Half Day', 'Half-day'))) {
                  $status_class = 'status-excused';
              }
            ?>
            <tr class="<?php echo ($i++ % 2 === 1) ? 'zebra' : ''; ?>">
              <td><?php echo html_escape($date_str); ?></td>
              <td class="center <?php echo $status_class; ?>"><?php echo html_escape($status_str); ?></td>
              <td><?php echo html_escape($marker_str); ?></td>
              <td><?php echo html_escape($remarks_str); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <!-- Institutional Signatures Area -->
  <div class="signature-area" style="margin-top: 6mm; page-break-inside: avoid;">
    <table style="width: 100%; border-collapse: collapse;">
      <tr>
        <td style="width: 33.3%; text-align: center; vertical-align: bottom; height: 16mm;">
          <div style="border-top: 0.3mm dashed #64748b; width: 50mm; margin: 0 auto; padding-top: 1.5mm; font-size: 7.5pt; font-weight: bold; color: #0f172a;">
            Class Teacher
            <div style="font-size: 6.5pt; font-weight: normal; color: #64748b; margin-top: 0.5mm;">Signature & Date</div>
          </div>
        </td>
        <td style="width: 33.3%; text-align: center; vertical-align: bottom; height: 16mm;">
          <div style="border-top: 0.3mm dashed #64748b; width: 50mm; margin: 0 auto; padding-top: 1.5mm; font-size: 7.5pt; font-weight: bold; color: #0f172a;">
            Attendance In-Charge
            <div style="font-size: 6.5pt; font-weight: normal; color: #64748b; margin-top: 0.5mm;">Verified By</div>
          </div>
        </td>
        <td style="width: 33.3%; text-align: center; vertical-align: bottom; height: 16mm;">
          <div style="border-top: 0.3mm dashed #64748b; width: 50mm; margin: 0 auto; padding-top: 1.5mm; font-size: 7.5pt; font-weight: bold; color: #0f172a;">
            Principal / Head
            <div style="font-size: 6.5pt; font-weight: normal; color: #64748b; margin-top: 0.5mm;">Seal & Signature</div>
          </div>
        </td>
      </tr>
    </table>
    <div style="margin-top: 3mm; text-align: center; font-size: 6.5pt; color: #94a3b8;">
      This is an official computer-generated student attendance report issued by <?php echo html_escape($school_name); ?>.
    </div>
  </div>

  </div>

</body>
</html>
