<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<!-- <title></?php echo html_escape($student_name); ?> - Overall Academic Report</title> -->
<style>
    body {
        font-family: dejavusans, sans-serif;
        font-size: 8pt;
        color: #1e293b;
        line-height: 1.3;
    }
    table {
        border-collapse: collapse;
        width: 100%;
    }
    .section-title {
        font-size: 8.5pt;
        font-weight: bold;
        color: #0f172a;
        background-color: #f1f5f9;
        padding: 3px 6px;
        margin-top: 3.5mm;
        margin-bottom: 2mm;
        border-left: 3px solid #258CC9;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .info-table {
        width: 100%;
        margin-bottom: 2mm;
    }
    .info-table td {
        padding: 2.5px 4px;
        vertical-align: top;
    }
    .label {
        font-size: 7pt;
        color: #64748b;
        text-transform: uppercase;
        font-weight: bold;
    }
    .val {
        font-size: 8pt;
        color: #0f172a;
        font-weight: normal;
    }
    .data-table {
        width: 100%;
        border: 0.25mm solid #cbd5e1;
        margin-bottom: 2.5mm;
    }
    .data-table th {
        background-color: #f8fafc;
        border: 0.2mm solid #cbd5e1;
        padding: 3.5px 5px;
        font-size: 7.5pt;
        text-transform: uppercase;
        color: #475569;
        font-weight: bold;
    }
    .data-table td {
        border: 0.2mm solid #e2e8f0;
        padding: 3.5px 5px;
        font-size: 7.5pt;
    }
    .text-center { text-align: center; }
    .text-right  { text-align: right; }
    .text-left   { text-align: left; }
    .font-bold   { font-weight: bold; }
    .text-primary { color: #258CC9; }
    .text-success { color: #15803d; }
    .text-danger  { color: #b91c1c; }
    .text-muted   { color: #64748b; }

    .badge {
        display: inline-block;
        padding: 1.5px 5px;
        font-size: 7pt;
        font-weight: bold;
        border-radius: 3px;
    }
    .badge-pass { background-color: #dcfce7; color: #166534; }
    .badge-fail { background-color: #fee2e2; color: #991b1b; }
    .badge-info { background-color: #e0f2fe; color: #0369a1; }

    .kpi-table {
        margin-bottom: 2.5mm;
    }
    .kpi-box {
        border: 0.25mm solid #cbd5e1;
        background-color: #f8fafc;
        padding: 4px 5px;
        text-align: center;
    }
    .kpi-num {
        font-size: 10pt;
        font-weight: bold;
        color: #0f172a;
    }
    .kpi-label {
        font-size: 6.5pt;
        color: #64748b;
        text-transform: uppercase;
        margin-top: 1px;
    }

    .signature-area {
        margin-top: 5mm;
        page-break-inside: avoid;
    }
    .signature-line {
        border-top: 0.3mm dashed #64748b;
        width: 55mm;
        margin: 0 auto;
        padding-top: 1.5mm;
        font-size: 8pt;
        font-weight: bold;
        color: #0f172a;
        text-align: center;
    }
    .report-content-wrapper {
        margin-left: 10mm;
        margin-right: 10mm;
    }
</style>
</head>
<body>

    <!-- ================= STANDARDIZED GLOBAL HEADER (ALL PAGES) ================= -->
    <htmlpageheader name="globalHeader">
        <?php if (!empty($document_design->has_header) && !empty($document_design->header_path) && file_exists($document_design->header_path)): ?>
            <div style="width: 100%; margin: 0; padding: 0; line-height: 0;">
                <img src="<?php echo htmlspecialchars($document_design->header_path, ENT_QUOTES, 'UTF-8'); ?>" style="width: 210mm; display: block; margin: 0; padding: 0;" />
            </div>
        <?php else: ?>
            <div style="margin: 0 10mm;">
                <table width="100%" style="border-collapse: collapse; font-family: dejavusans, sans-serif;">
                    <tr>
                        <td width="15%" valign="middle" align="left" style="padding-bottom: 1.5mm;">
                            <?php if (!empty($school_logo_src)): ?>
                                <img src="<?php echo $school_logo_src; ?>" style="height: 13mm; max-width: 26mm;" />
                            <?php else: ?>
                                <div style="background-color: #258CC9; color: #ffffff; width: 13mm; height: 13mm; text-align: center; line-height: 13mm; font-size: 11pt; font-weight: bold; border-radius: 3px;">
                                    L2
                                </div>
                            <?php endif; ?>
                        </td>
                        <td width="57%" valign="middle" align="left" style="padding-bottom: 1.5mm; padding-left: 2mm;">
                            <div style="font-size: 12pt; font-weight: bold; color: #0f172a;"><?php echo html_escape($school_name); ?></div>
                            <div style="font-size: 7.5pt; color: #475569; margin-top: 0.5mm;"><?php echo html_escape($school_address); ?></div>
                            <?php if (!empty($school_contact)): ?>
                                <div style="font-size: 7pt; color: #64748b; margin-top: 0.5mm;"><?php echo html_escape($school_contact); ?></div>
                            <?php endif; ?>
                        </td>
                        <td width="28%" valign="middle" align="right" style="padding-bottom: 1.5mm;">
                            <div style="background-color: #e0f2fe; color: #0369a1; padding: 1.5px 5px; font-size: 7pt; font-weight: bold; display: inline-block; border-radius: 3px; text-transform: uppercase;">
                                Academic Year
                            </div>
                            <div style="font-size: 8pt; font-weight: bold; color: #0f172a; margin-top: 0.5mm;"><?php echo html_escape($academic_year_name); ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" style="border-top: 1.5px solid #258CC9; border-bottom: 0.5px solid #cbd5e1; background-color: #F7F7F7; padding: 2mm 5px; text-align: center;">
                            <span style="font-size: 8.5pt; font-weight: bold; color: #0f172a; text-transform: uppercase; letter-spacing: 0.8px;">STUDENT OVERALL REPORT</span>
                        </td>
                    </tr>
                </table>
            </div>
        <?php endif; ?>
    </htmlpageheader>
    <sethtmlpageheader name="globalHeader" value="on" show-this-page="1" />

    <!-- ================= STANDARDIZED GLOBAL FOOTER (ALL PAGES) ================= -->
    <htmlpagefooter name="globalFooter">
        <?php if (!empty($document_design->has_footer) && !empty($document_design->footer_path) && file_exists($document_design->footer_path)): ?>
            <div style="width: 100%; margin: 0; padding: 0; line-height: 0;">
                <img src="<?php echo htmlspecialchars($document_design->footer_path, ENT_QUOTES, 'UTF-8'); ?>" style="width: 210mm; display: block; margin: 0; padding: 0;" />
            </div>
        <?php else: ?>
            <div style="margin: 0 10mm;">
                <table width="100%" style="border-top: 0.25mm solid #cbd5e1; font-size: 7pt; color: #64748b; padding-top: 1.5mm; font-family: dejavusans, sans-serif;">
                    <tr>
                        <td width="38%" align="left"><?php echo html_escape($school_name); ?> | Student Overall Report</td>
                        <td width="38%" align="center">Generated: <?php echo html_escape($generated_at); ?> IST</td>
                        <td width="24%" align="right">Page {PAGENO} of {nbpg}</td>
                    </tr>
                </table>
            </div>
        <?php endif; ?>
    </htmlpagefooter>
    <sethtmlpagefooter name="globalFooter" value="on" />

    <!-- ================= BODY CONTENT: NATURAL SEQUENTIAL FLOW ================= -->
    <div class="report-content-wrapper">

    <!-- Document Report Title Banner -->
    <div style="margin-bottom: 3.5mm; padding-bottom: 2mm; border-bottom: 1.5px solid #0284c7;">
        <table width="100%" style="border-collapse: collapse;">
            <tr>
                <td align="left" valign="middle">
                    <span style="font-size: 11.5pt; font-weight: bold; color: #0f172a; letter-spacing: 0.3px;">
                        <?php echo html_escape($student_name); ?> — Overall Academic Report
                    </span>
                </td>
                <td align="right" valign="middle">
                    <span style="background-color: #f0f9ff; color: #0369a1; border: 0.2mm solid #bae6fd; padding: 2px 7px; font-size: 7.5pt; font-weight: bold; display: inline-block; border-radius: 2px; text-transform: uppercase;">
                        Academic Session: <?php echo html_escape($academic_year_name); ?>
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <!-- 1. Student Personal & Academic Details Card -->
    <div style="border: 0.25mm solid #cbd5e1; background-color: #ffffff; margin-bottom: 3mm; page-break-inside: avoid;">
        <div style="background-color: #f8fafc; border-bottom: 0.2mm solid #e2e8f0; padding: 2mm 3.5mm;">
            <table width="100%" style="border-collapse: collapse;">
                <tr>
                    <td align="left" style="font-size: 7.5pt; font-weight: bold; color: #334155; text-transform: uppercase; letter-spacing: 0.5px;">
                        Student Profile & Academic Record
                    </td>
                    <td align="right" style="font-size: 7pt; color: #64748b;">
                        Adm No: <strong style="color: #0f172a;"><?php echo html_escape($admission_number); ?></strong>
                    </td>
                </tr>
            </table>
        </div>
        <div style="padding: 2.5mm 3.5mm;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <!-- Student Photo -->
                    <td width="16%" align="center" valign="top" style="padding-right: 3mm;">
                        <?php if (!empty($student_photo_src)): ?>
                            <img src="<?php echo $student_photo_src; ?>" style="width: 21mm; height: 26mm; border: 0.25mm solid #cbd5e1; border-radius: 2px;" />
                        <?php else: ?>
                            <div style="width: 21mm; height: 26mm; background-color: #f1f5f9; color: #0284c7; text-align: center; line-height: 26mm; font-size: 12pt; font-weight: bold; border: 0.25mm solid #cbd5e1; border-radius: 2px;">
                                <?php echo html_escape($initials); ?>
                            </div>
                        <?php endif; ?>
                        <div style="margin-top: 1mm;">
                            <span class="badge <?php echo ($status_label === 'Active') ? 'badge-pass' : 'badge-info'; ?>" style="font-size: 6.5pt;">
                                <?php echo html_escape($status_label); ?>
                            </span>
                        </div>
                    </td>

                    <!-- Student Metadata Columns -->
                    <td width="84%" valign="top">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td width="33%" style="padding-bottom: 1.8mm;">
                                    <div class="label">Full Name</div>
                                    <div class="val" style="font-size: 8.5pt; font-weight: bold;"><?php echo html_escape($student_name); ?></div>
                                </td>
                                <td width="33%" style="padding-bottom: 1.8mm;">
                                    <div class="label">Class & Division</div>
                                    <div class="val" style="font-size: 8.5pt; font-weight: bold;"><?php echo html_escape($class_name); ?></div>
                                </td>
                                <td width="34%" style="padding-bottom: 1.8mm;">
                                    <div class="label">Roll Number</div>
                                    <div class="val" style="font-size: 8.5pt; font-weight: bold;"><?php echo html_escape($roll_number ?: '—'); ?></div>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding-bottom: 1.8mm;">
                                    <div class="label">Group / Section</div>
                                    <div class="val"><?php echo html_escape($group_name ?: 'Standard'); ?></div>
                                </td>
                                <td style="padding-bottom: 1.8mm;">
                                    <div class="label">Gender</div>
                                    <div class="val"><?php echo html_escape($gender); ?></div>
                                </td>
                                <td style="padding-bottom: 1.8mm;">
                                    <div class="label">Date of Birth</div>
                                    <div class="val"><?php echo html_escape($date_of_birth); ?></div>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding-bottom: 1.8mm;">
                                    <div class="label">Blood Group</div>
                                    <div class="val"><?php echo html_escape($blood_group ?: '—'); ?></div>
                                </td>
                                <td style="padding-bottom: 1.8mm;">
                                    <div class="label">Admission Date</div>
                                    <div class="val"><?php echo html_escape($admission_date ?: '—'); ?></div>
                                </td>
                                <td style="padding-bottom: 1.8mm;">
                                    <div class="label">Contact Phone</div>
                                    <div class="val"><?php echo html_escape($student_phone ?: '—'); ?></div>
                                </td>
                            </tr>
                            <?php if (!empty($student_address) && $student_address !== '—'): ?>
                                <tr>
                                    <td colspan="3" style="padding-top: 0.5mm; border-top: 0.2mm dashed #e2e8f0;">
                                        <div class="label">Residential Address</div>
                                        <div class="val" style="color: #475569;"><?php echo html_escape($student_address); ?></div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- 2. Parent / Guardian Information Card -->
    <div style="border: 0.25mm solid #cbd5e1; background-color: #ffffff; margin-bottom: 3.5mm; page-break-inside: avoid;">
        <div style="background-color: #f8fafc; border-bottom: 0.2mm solid #e2e8f0; padding: 1.5mm 3.5mm;">
            <span style="font-size: 7.5pt; font-weight: bold; color: #334155; text-transform: uppercase; letter-spacing: 0.5px;">
                Parent & Guardian Information
            </span>
        </div>
        <div style="padding: 2mm 3.5mm;">
            <table width="100%" style="border-collapse: collapse;">
                <tr>
                    <td width="28%">
                        <div class="label">Guardian Name</div>
                        <div class="val" style="font-weight: bold;"><?php echo html_escape($guardian_name ?: '—'); ?></div>
                    </td>
                    <td width="20%">
                        <div class="label">Relationship</div>
                        <div class="val"><?php echo html_escape($guardian_relation ?: 'Parent'); ?></div>
                    </td>
                    <td width="24%">
                        <div class="label">Phone Contact</div>
                        <div class="val"><?php echo html_escape($guardian_phone ?: '—'); ?></div>
                    </td>
                    <td width="28%">
                        <div class="label">Email Address</div>
                        <div class="val"><?php echo html_escape($guardian_email ?: '—'); ?></div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- 3. Examination Results Section -->
    <div class="section-title">Academic Examination Results</div>

    <?php if (empty($exam_reports)): ?>
        <p style="text-align: center; color: #64748b; padding: 3mm 0; font-style: italic; border: 0.2mm dashed #cbd5e1; margin-bottom: 2.5mm;">
            No examination results available for this academic year.
        </p>
    <?php else: ?>
        <?php foreach ($exam_reports as $ex): ?>
            <div style="page-break-inside: avoid; margin-bottom: 3mm;">
                <table style="width: 100%; margin-bottom: 1mm;">
                    <tr>
                        <td align="left" style="font-weight: bold; font-size: 8.5pt; color: #0f172a;">
                            EXAMINATION: <?php echo html_escape($ex->exam_name); ?>
                            <?php if (!empty($ex->exam_date)): ?>
                                <span style="font-size: 7pt; color: #64748b; font-weight: normal;">(Conducted: <?php echo date('d M Y', strtotime($ex->exam_date)); ?>)</span>
                            <?php endif; ?>
                        </td>
                        <td align="right" style="font-size: 7.5pt;">
                            <?php if (!empty($ex->pass_status)): ?>
                                <span class="badge <?php echo ($ex->pass_status === 'Pass') ? 'badge-pass' : 'badge-fail'; ?>"><?php echo html_escape($ex->pass_status); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($ex->class_rank)): ?>
                                <span class="badge badge-info">Rank: #<?php echo (int)$ex->class_rank; ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>

                <table class="data-table" repeat_header="1">
                    <thead>
                        <tr>
                            <th width="38%" class="text-left">Subject</th>
                            <th width="15%" class="text-right">Marks Obtained</th>
                            <th width="15%" class="text-right">Maximum Marks</th>
                            <th width="14%" class="text-center">Grade</th>
                            <th width="18%" class="text-center">Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ex->subject_marks)): ?>
                            <tr><td colspan="5" class="text-center text-muted">No subject marks recorded.</td></tr>
                        <?php else: ?>
                            <?php foreach ($ex->subject_marks as $idx => $m): ?>
                                <?php $trBg = ($idx % 2 === 1) ? 'style="background-color: #f8fafc;"' : ''; ?>
                                <tr <?php echo $trBg; ?>>
                                    <td class="text-left font-bold"><?php echo html_escape($m->subject_name); ?></td>
                                    <td class="text-right" style="font-weight: bold;">
                                        <?php
                                            if (!empty($m->is_absent)) echo '<span class="text-danger font-bold">ABS</span>';
                                            elseif (!empty($m->is_exempted)) echo '<span class="text-primary font-bold">EXM</span>';
                                            else echo number_format((float)$m->marks_obtained, 1);
                                        ?>
                                    </td>
                                    <td class="text-right text-muted"><?php echo number_format((float)$m->max_marks, 1); ?></td>
                                    <td class="text-center font-bold"><?php echo html_escape($m->grade ?: '—'); ?></td>
                                    <td class="text-center">
                                        <?php
                                            $sub_pass = !empty($m->passing_marks) ? (float)$m->passing_marks : 35.0;
                                            $obtained = (float)($m->marks_obtained ?? 0);
                                            $is_sub_pass = empty($m->is_absent) && ($obtained >= $sub_pass);
                                        ?>
                                        <span class="<?php echo $is_sub_pass ? 'text-success' : 'text-danger'; ?> font-bold">
                                            <?php echo $is_sub_pass ? 'PASS' : 'FAIL'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #f1f5f9; font-weight: bold; border-top: 0.3mm solid #cbd5e1;">
                            <td class="text-left" style="text-transform: uppercase; font-size: 7pt; color: #475569;">Grand Total / Percentage</td>
                            <td class="text-right text-primary" style="font-size: 8.5pt;"><?php echo number_format((float)$ex->total_marks, 1); ?></td>
                            <td class="text-right" style="font-size: 8.5pt;"><?php echo number_format((float)$ex->max_marks, 1); ?></td>
                            <td class="text-center text-primary"><?php echo html_escape($ex->overall_grade ?: '—'); ?></td>
                            <td class="text-center" style="font-size: 8.5pt;"><?php echo number_format((float)$ex->percentage, 1); ?>%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- 4. Attendance Section -->
    <div class="section-title">
        Attendance Summary (<?php echo !empty($attendance_data->is_ss) ? 'Subject-wise Period Attendance' : 'Academic Days Summary'; ?>)
    </div>

    <?php if (!empty($attendance_data->is_ss)): ?>
        <!-- SS ATTENDANCE -->
        <table class="kpi-table" style="page-break-inside: avoid;">
            <tr>
                <td width="16%" class="kpi-box">
                    <div class="kpi-num"><?php echo (int)($attendance_data->period_summary->total_periods ?? 0); ?></div>
                    <div class="kpi-label">Scheduled Periods</div>
                </td>
                <td width="16%" class="kpi-box">
                    <div class="kpi-num text-success"><?php echo (int)($attendance_data->period_summary->present ?? 0); ?></div>
                    <div class="kpi-label">Present</div>
                </td>
                <td width="16%" class="kpi-box">
                    <div class="kpi-num text-danger"><?php echo (int)($attendance_data->period_summary->absent ?? 0); ?></div>
                    <div class="kpi-label">Absent</div>
                </td>
                <td width="16%" class="kpi-box">
                    <div class="kpi-num" style="color: #b45309;"><?php echo (int)($attendance_data->period_summary->late ?? 0); ?></div>
                    <div class="kpi-label">Late</div>
                </td>
                <td width="16%" class="kpi-box">
                    <div class="kpi-num text-primary"><?php echo (int)($attendance_data->period_summary->excused ?? 0); ?></div>
                    <div class="kpi-label">Excused</div>
                </td>
                <td width="20%" class="kpi-box" style="background-color: #f0fdf4; border-color: #86efac;">
                    <div class="kpi-num text-success"><?php echo number_format((float)($attendance_data->period_summary->percentage ?? 0), 1); ?>%</div>
                    <div class="kpi-label">Attendance Rate</div>
                </td>
            </tr>
        </table>

        <table class="data-table" repeat_header="1" style="page-break-inside: avoid;">
            <thead>
                <tr>
                    <th width="35%" class="text-left">Subject</th>
                    <th width="12%" class="text-right">Total Periods</th>
                    <th width="10%" class="text-right">Present</th>
                    <th width="10%" class="text-right">Absent</th>
                    <th width="10%" class="text-right">Late</th>
                    <th width="11%" class="text-right">Excused</th>
                    <th width="12%" class="text-center">Attendance %</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($attendance_data->subject_summary)): ?>
                    <tr><td colspan="7" class="text-center text-muted">No period attendance records logged.</td></tr>
                <?php else: ?>
                    <?php foreach ($attendance_data->subject_summary as $idx => $ssub): ?>
                        <?php $trBg = ($idx % 2 === 1) ? 'style="background-color: #f8fafc;"' : ''; ?>
                        <tr <?php echo $trBg; ?>>
                            <td class="text-left font-bold"><?php echo html_escape($ssub->subject_name); ?></td>
                            <td class="text-right"><?php echo (int)$ssub->total_classes; ?></td>
                            <td class="text-right text-success font-bold"><?php echo (int)$ssub->present; ?></td>
                            <td class="text-right text-danger font-bold"><?php echo (int)$ssub->absent; ?></td>
                            <td class="text-right"><?php echo (int)$ssub->late; ?></td>
                            <td class="text-right"><?php echo (int)$ssub->excused; ?></td>
                            <td class="text-center font-bold"><?php echo number_format((float)$ssub->percentage, 1); ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

    <?php else: ?>
        <!-- NON-SS ATTENDANCE -->
        <table class="kpi-table" style="page-break-inside: avoid;">
            <tr>
                <td width="16%" class="kpi-box">
                    <div class="kpi-num"><?php echo (int)($attendance_data->day_summary->working_days ?? 0); ?></div>
                    <div class="kpi-label">Academic Days</div>
                </td>
                <td width="16%" class="kpi-box">
                    <div class="kpi-num text-success"><?php echo (int)($attendance_data->day_summary->present ?? 0); ?></div>
                    <div class="kpi-label">Present</div>
                </td>
                <td width="16%" class="kpi-box">
                    <div class="kpi-num text-danger"><?php echo (int)($attendance_data->day_summary->absent ?? 0); ?></div>
                    <div class="kpi-label">Absent</div>
                </td>
                <td width="16%" class="kpi-box">
                    <div class="kpi-num" style="color: #b45309;"><?php echo (int)($attendance_data->day_summary->late ?? 0); ?></div>
                    <div class="kpi-label">Late</div>
                </td>
                <td width="16%" class="kpi-box">
                    <div class="kpi-num text-primary"><?php echo (int)($attendance_data->day_summary->excused ?? 0); ?></div>
                    <div class="kpi-label">Excused</div>
                </td>
                <td width="20%" class="kpi-box" style="background-color: #f0fdf4; border-color: #86efac;">
                    <div class="kpi-num text-success"><?php echo number_format((float)($attendance_data->day_summary->percentage ?? 0), 1); ?>%</div>
                    <div class="kpi-label">Attendance Rate</div>
                </td>
            </tr>
        </table>

        <table class="data-table" repeat_header="1" style="page-break-inside: avoid;">
            <thead>
                <tr>
                    <th width="35%" class="text-left">Month</th>
                    <th width="12%" class="text-right">Present</th>
                    <th width="12%" class="text-right">Absent</th>
                    <th width="12%" class="text-right">Late</th>
                    <th width="12%" class="text-right">Excused</th>
                    <th width="15%" class="text-right">Total Days</th>
                    <th width="15%" class="text-center">Attendance %</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($attendance_data->day_monthly)): ?>
                    <tr><td colspan="7" class="text-center text-muted">No attendance records logged for this academic year.</td></tr>
                <?php else: ?>
                    <?php foreach ($attendance_data->day_monthly as $idx => $dm): ?>
                        <?php $trBg = ($idx % 2 === 1) ? 'style="background-color: #f8fafc;"' : ''; ?>
                        <tr <?php echo $trBg; ?>>
                            <td class="text-left font-bold"><?php echo html_escape($dm->month_name); ?></td>
                            <td class="text-right text-success font-bold"><?php echo (int)$dm->present_count; ?></td>
                            <td class="text-right text-danger font-bold"><?php echo (int)$dm->absent_count; ?></td>
                            <td class="text-right"><?php echo (int)$dm->late_count; ?></td>
                            <td class="text-right"><?php echo (int)$dm->excused_count; ?></td>
                            <td class="text-right text-muted"><?php echo (int)$dm->total_days; ?></td>
                            <td class="text-center font-bold"><?php echo number_format((float)$dm->percentage, 1); ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- 5. Fees & Finance Summary -->
    <div style="page-break-inside: avoid; margin-bottom: 3.5mm;">
        <div class="section-title">Fees & Finance Summary</div>
        <table class="kpi-table">
            <tr>
                <td width="25%" class="kpi-box">
                    <div class="kpi-num">₹<?php echo number_format((float)($fee_summary->total_assigned ?? 0), 2); ?></div>
                    <div class="kpi-label">Total Fee Assigned</div>
                </td>
                <td width="25%" class="kpi-box">
                    <div class="kpi-num text-success">₹<?php echo number_format((float)($fee_summary->total_paid ?? 0), 2); ?></div>
                    <div class="kpi-label">Total Amount Paid</div>
                </td>
                <td width="25%" class="kpi-box">
                    <div class="kpi-num" style="color: #c2410c;">₹<?php echo number_format((float)($fee_summary->total_due ?? 0), 2); ?></div>
                    <div class="kpi-label">Current Pending</div>
                </td>
                <td width="25%" class="kpi-box">
                    <div class="kpi-num text-danger">₹<?php echo number_format((float)($fee_summary->total_overdue ?? 0), 2); ?></div>
                    <div class="kpi-label">Overdue Amount</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- 6. Institutional Signatures Area -->
    <div class="signature-area">
        <table style="width: 100%;">
            <tr>
                <td width="33.3%" align="center" valign="bottom" style="height: 18mm;">
                    <div class="signature-line">
                        Class Teacher
                        <?php if (!empty($teacher_name)): ?>
                            <div style="font-size: 7pt; font-weight: normal; color: #64748b; margin-top: 0.8mm;"><?php echo html_escape($teacher_name); ?></div>
                        <?php else: ?>
                            <div style="font-size: 7pt; font-weight: normal; color: #64748b; margin-top: 0.8mm;">Signature & Date</div>
                        <?php endif; ?>
                    </div>
                </td>
                <td width="33.3%" align="center" valign="bottom" style="height: 18mm;">
                    <div class="signature-line">
                        Parent / Guardian
                        <div style="font-size: 7pt; font-weight: normal; color: #64748b; margin-top: 0.8mm;">Signature</div>
                    </div>
                </td>
                <td width="33.3%" align="center" valign="bottom" style="height: 18mm;">
                    <div class="signature-line">
                        Principal / Head
                        <div style="font-size: 7pt; font-weight: normal; color: #64748b; margin-top: 0.8mm;"><?php echo html_escape($principal_name ?: 'Seal & Signature'); ?></div>
                    </div>
                </td>
            </tr>
        </table>
        <div style="margin-top: 3.5mm; text-align: center; font-size: 6.5pt; color: #94a3b8;">
            This is an official computer-generated academic record issued by <?php echo html_escape($school_name); ?>.
        </div>
    </div>
    </div>

</body>
</html>
