<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'auth';
$route['404_override'] = 'errors/error_404';
$route['translate_uri_dashes'] = FALSE;

/*
 * School route aliases
 * Default CI3 controller/method routing already resolves most URIs
 * (e.g. /students/add -> Students::add()). These aliases only cover
 * the handful of URIs that need a nicer public path than the raw
 * controller/method pair.
 */
$route['login']              = 'auth/login';
$route['logout']             = 'auth/logout';
$route['search']             = 'search/index';
$route['search/global']      = 'search/global_query';
$route['settings/schools']                   = 'schools/index';
$route['settings/schools/create']            = 'schools/create';
$route['settings/schools/generate_code']     = 'schools/generate_code';
$route['settings/schools/edit/(:num)']       = 'schools/edit/$1';
$route['settings/schools/get_details/(:num)'] = 'schools/get_details/$1';
$route['settings/schools/toggle_status/(:num)'] = 'schools/toggle_status/$1';
$route['settings/schools/switch']            = 'schools/switch_school';
$route['schools/switch']                     = 'schools/switch_school';
$route['staff/profile/(:num)'] = 'staff/profile/$1';
$route['students']            = 'students/overview';
$route['students/overview']   = 'students/overview';
$route['students/all']        = 'students/all_students';
$route['students/all_students'] = 'students/all_students';
$route['students/all_students_ajax'] = 'students/all_students_ajax';
$route['students/class_counts_ajax'] = 'students/class_counts_ajax';
$route['student-bulk-add']           = 'students/bulk_add';
$route['students/bulk-add']          = 'students/bulk_add';
$route['students/bulk_add']          = 'students/bulk_add';
$route['students/bulk_template']     = 'students/bulk_template';
$route['students/bulk_validate_ajax']= 'students/bulk_validate_ajax';
$route['students/bulk_revalidate_row_ajax']= 'students/bulk_revalidate_row_ajax';
$route['students/bulk_import_ajax']  = 'students/bulk_import_ajax';
$route['students/bulk_entry_save_ajax']= 'students/bulk_entry_save_ajax';
$route['students/list']       = 'students/list_students';
$route['students/register']   = 'students/register';
$route['students/add']        = 'students/add';
$route['students/wizard_step1']     = 'students/wizard_step1';
$route['students/wizard_step2']     = 'students/wizard_step2';
$route['students/wizard_save']      = 'students/wizard_save';
$route['students/wizard_cancel']    = 'students/wizard_cancel';
$route['students/wizard_tc_upload']    = 'students/wizard_tc_upload';
$route['students/wizard_photo_upload'] = 'students/wizard_photo_upload';
$route['students/admissions'] = 'students/admissions';
$route['students/roll']       = 'students/index';
$route['students/roll_numbers']= 'students/index';
$route['students/profile']    = 'students/profile';
$route['students/profile/(:num)'] = 'students/profile/$1';
$route['students/overall_report/(:num)']     = 'students/overall_report/$1';
$route['students/overall_report_pdf/(:num)'] = 'students/overall_report_pdf/$1';
$route['students/edit']             = 'students/edit';
$route['students/edit/(:num)']      = 'students/edit/$1';
$route['students/documents']                = 'students/documents';
$route['students/id_cards']                  = 'students/id_cards';
$route['students/id_card_preview_ajax']      = 'students/id_card_preview_ajax';
$route['students/id_card_bulk_data_ajax']    = 'students/id_card_bulk_data_ajax';
$route['students/id_card_history_ajax']      = 'students/id_card_history_ajax';
$route['students/id_card_record_ajax']        = 'students/id_card_record_ajax';
$route['students/id_card_regenerate_ajax']   = 'students/id_card_regenerate_ajax';
$route['students/id_card_settings_save']     = 'students/id_card_settings_save';
$route['students/id_card_print']             = 'students/id_card_print';
$route['students/promotion']                 = 'students/promotion';
$route['students/get_divisions_ajax']         = 'students/get_divisions_ajax';
$route['students/get_sections_ajax']          = 'students/get_divisions_ajax';
$route['students/get_classes_ajax']          = 'students/get_classes_ajax';
$route['students/transfers']  = 'students/transfers';
$route['students/search']     = 'students/search';
$route['students/remove_photo/(:num)'] = 'students/remove_photo/$1';
$route['students/tc/(:num)']  = 'students/tc/$1';
$route['academics/get_next_division_ajax']    = 'academics/get_next_division_ajax';
$route['academics/get_next_section_ajax']     = 'academics/get_next_division_ajax';

$route['staff']                             = 'staff/overview';
$route['staff/overview']                    = 'staff/overview';
$route['staff/list']                        = 'staff/directory';
$route['staff/directory']                   = 'staff/directory';
$route['staff/register']                    = 'staff/register';
$route['staff/teachers']                    = 'staff/teachers';
$route['staff/non_teaching']                = 'staff/non_teaching';
$route['staff/designations']                = 'staff/designations';
$route['staff/departments_designations']    = 'staff/designations';
$route['staff/departments']                 = 'staff/designations';
$route['staff/documents']                   = 'staff/documents';
$route['staff/workload']                    = 'staff/workload';
$route['staff/attendance']                  = 'staff/attendance';
$route['staff/leave']                       = 'staff/leave';

$route['academics']                   = 'academics/overview';
$route['academics/overview']          = 'academics/overview';
$route['academics/years']            = 'academics/years';
$route['academics/switch_year']      = 'academics/switch_year';
$route['academic-year/switch']       = 'academics/switch_year';
$route['academics/groups']           = 'academics/academic_groups';
$route['academics/academic_groups']  = 'academics/academic_groups';
$route['academics/delete_academic_group/(:num)'] = 'academics/delete_academic_group/$1';
$route['academics/toggle_group_status/(:num)/(:num)'] = 'academics/toggle_group_status/$1/$2';
$route['academics/ajax_get_group_classes'] = 'academics/ajax_get_group_classes';
$route['academics/ajax_get_classes_by_group'] = 'academics/ajax_get_classes_by_group';
$route['settings/academic_groups']   = 'settings/academic_groups';
$route['academics/classes']          = 'academics/classes';
$route['academics/delete_class/(:num)'] = 'academics/delete_class/$1';
$route['academics/divisions']        = 'academics/divisions';
$route['academics/divisions/add']    = 'academics/divisions';
$route['academics/divisions/edit/(:num)'] = 'academics/divisions';
$route['academics/divisions/delete/(:num)'] = 'academics/delete_division/$1';
$route['academics/delete_division/(:num)'] = 'academics/delete_division/$1';
$route['academics/ajax_get_divisions'] = 'academics/ajax_get_divisions';
$route['academics/ajax_get_divisions/(:num)'] = 'academics/ajax_get_divisions/$1';
$route['academics/ajax_get_divisions/(:any)'] = 'academics/ajax_get_divisions/$1';
$route['academics/ajax_get_classes']   = 'academics/ajax_get_classes';
$route['academics/ajax_get_classes/(:num)'] = 'academics/ajax_get_classes/$1';
$route['academics/ajax_get_classes/(:any)'] = 'academics/ajax_get_classes/$1';
$route['academics/sections']         = 'academics/divisions';
$route['academics/sections/(:any)']  = 'academics/divisions';
$route['academics/subjects']         = 'academics/subjects';
$route['academics/class_teachers']   = 'academics/class_teachers'; // 301 redirect → academics/classes (legacy URL support)
$route['academics/ajax_get_class_teacher_assignments'] = 'academics/ajax_get_class_teacher_assignments';
$route['academics/ajax_get_class_teacher_assignments/(:num)'] = 'academics/ajax_get_class_teacher_assignments/$1';
$route['academics/subject_teachers'] = 'academics/subject_teachers'; // 301 redirect → academics/subjects (legacy URL support)
$route['academics/ajax_get_subject_assignments']  = 'academics/ajax_get_subject_assignments';
$route['academics/ajax_save_subject_teacher']     = 'academics/ajax_save_subject_teacher';
$route['academics/ajax_delete_subject_teacher']   = 'academics/ajax_delete_subject_teacher';
$route['academics/timetable']        = 'academics/timetable';
$route['academics/calendar']         = 'academics/calendar';

$route['attendance']                       = 'attendance/index';
$route['attendance/dashboard']             = 'attendance/index';
$route['attendance/overview']              = 'attendance/index';
$route['attendance/mark_attendance']       = 'attendance/mark_attendance';
$route['attendance/mark']                  = 'attendance/mark_attendance';
$route['student-attendance/mark']          = 'attendance/mark_attendance';
$route['attendance/daily']                 = 'attendance/mark_attendance';
$route['attendance/periods']               = 'attendance/periods';
$route['attendance/periods/(:any)']        = 'attendance/periods/$1';
$route['attendance/period_wise']           = 'attendance/period_wise';
$route['attendance/class_attendance']      = 'attendance/class_attendance';
$route['student-attendance/view']          = 'attendance/view_attendance';
$route['attendance/view']                  = 'attendance/view_attendance';
$route['attendance/view_attendance']       = 'attendance/view_attendance';
$route['student-attendance/details']       = 'attendance/student_attendance';
$route['student-attendance/student/(:num)'] = 'attendance/student_attendance/$1';
$route['attendance/student_attendance']    = 'attendance/student_attendance';
$route['attendance/student/(:num)']       = 'attendance/student_attendance/$1';
$route['attendance/section_attendance']    = 'attendance/class_attendance';
$route['attendance/history']               = 'attendance/class_attendance';
$route['attendance/tracking']              = 'attendance/class_attendance';
$route['attendance/calendar']              = 'attendance/calendar';
$route['attendance/reports']               = 'attendance/reports';
$route['attendance/notifications']         = 'attendance/notifications';
$route['attendance/notification_history']  = 'attendance/notification_history';
$route['attendance/settings']              = 'attendance/settings';

$route['examinations']                      = 'examinations/index';
$route['examinations/dashboard']            = 'examinations/index';
$route['examinations/overview']             = 'examinations/index';
$route['examinations/exams']                = 'examinations/exams';
$route['examinations/types']                = 'examinations/types';
$route['examinations/schedules']            = 'examinations/schedules';
$route['examinations/allocations']          = 'examinations/allocations';
$route['examinations/add_schedule']         = 'examinations/allocations';
$route['examinations/marks_entry']          = 'examinations/marks_entry';
$route['examinations/verification']         = 'examinations/verification';
$route['examinations/grades']               = 'examinations/grades';
$route['examinations/calculate']            = 'examinations/calculate';
$route['examinations/results']              = 'examinations/results';
$route['examinations/result_detail/(:num)'] = 'examinations/result_detail/$1';
$route['examinations/ranks']                = 'examinations/ranks';
$route['examinations/report_cards']         = 'examinations/report_cards';
$route['examinations/report_card/(:num)']   = 'examinations/report_card/$1';
$route['examinations/progress_reports']     = 'examinations/progress_reports';
$route['examinations/progress_report/(:num)']= 'examinations/progress_report/$1';
$route['examinations/publishing']           = 'examinations/publishing';
$route['examinations/reports']              = 'examinations/reports';
$route['examinations/settings']             = 'examinations/settings';
$route['examinations/ajax_get_divisions']   = 'examinations/ajax_get_divisions';
$route['examinations/ajax_get_divisions/(:num)'] = 'examinations/ajax_get_divisions/$1';
$route['examinations/ajax_get_subjects']    = 'examinations/ajax_get_subjects';
$route['examinations/ajax_get_subjects/(:num)']  = 'examinations/ajax_get_subjects/$1';



$route['timetable']                     = 'timetable/index';
$route['timetable/dashboard']           = 'timetable/index';
$route['timetable/overview']            = 'timetable/index';
$route['timetable/classes']             = 'timetable/classes';
$route['timetable/teachers']            = 'timetable/teachers';
$route['timetable/allocations']         = 'timetable/allocations';
$route['timetable/builder']             = 'timetable/builder';
$route['timetable/free_periods']        = 'timetable/free_periods';
$route['timetable/conflicts']           = 'timetable/conflicts';
$route['timetable/publish_lock']        = 'timetable/publish_lock';
$route['timetable/reports']             = 'timetable/reports';
$route['timetable/period_setup']            = 'timetable/period_setup';
$route['timetable/period-setup']            = 'timetable/period_setup';
$route['timetable/periods']                 = 'timetable/period_setup';
$route['timetable/ajax_get_group_periods']  = 'timetable/ajax_get_group_periods';
$route['timetable/ajax_save_group_periods'] = 'timetable/period_setup';
$route['timetable/delete_slot/(:num)']      = 'timetable/delete_slot/$1';
$route['timetable/ajax_get_entry/(:num)']   = 'timetable/ajax_get_entry/$1';

$route['homework']                           = 'homework/index';
$route['homework/dashboard']                 = 'homework/index';
$route['homework/overview']                  = 'homework/index';
$route['homework/assignments']               = 'homework/assignments';
$route['homework/create']                    = 'homework/create';
$route['homework/edit/(:num)']               = 'homework/edit/$1';
$route['homework/details/(:num)']            = 'homework/details/$1';
$route['homework/types']                     = 'homework/types';
$route['homework/subjects']                  = 'homework/subjects';
$route['homework/classes']                   = 'homework/classes';
$route['homework/calendar']                  = 'homework/calendar';
$route['homework/submissions']               = 'homework/submissions';
$route['homework/submission_detail/(:num)']  = 'homework/submission_detail/$1';
$route['homework/review/(:num)']             = 'homework/review/$1';
$route['homework/student_view/(:num)']       = 'homework/student_view/$1';
$route['homework/reports']                   = 'homework/reports';
$route['homework/settings']                  = 'homework/settings';
$route['homework/duplicate/(:num)']          = 'homework/duplicate/$1';
$route['homework/publish/(:num)']            = 'homework/publish/$1';
$route['homework/archive/(:num)']            = 'homework/archive/$1';
$route['homework/delete/(:num)']             = 'homework/delete/$1';

$route['communication']                                     = 'communication/index';
$route['communication/dashboard']                           = 'communication/index';
$route['communication/overview']                            = 'communication/index';
$route['communication/templates']                           = 'communication/templates';
$route['communication/sms_templates']                       = 'communication/sms_templates';
$route['communication/whatsapp_templates']                  = 'communication/whatsapp_templates';
$route['communication/email_templates']                     = 'communication/email_templates';
$route['communication/automated_notifications']             = 'communication/automated_notifications';
$route['communication/rules']                               = 'communication/automated_notifications';
$route['communication/toggle_rule/(:num)']                  = 'communication/toggle_rule/$1';
$route['communication/test_rule/(:num)']                    = 'communication/test_rule/$1';
$route['communication/duplicate_template/(:num)']           = 'communication/duplicate_template/$1';
$route['communication/toggle_template/(:num)']              = 'communication/toggle_template/$1';
$route['communication/queue']                               = 'communication/queue';
$route['communication/process_queue_item/(:num)']           = 'communication/process_queue_item/$1';
$route['communication/cancel_queue_item/(:num)']            = 'communication/cancel_queue_item/$1';
$route['communication/history']                             = 'communication/history';
$route['communication/details/(:num)']                      = 'communication/details/$1';
$route['communication/failed']                              = 'communication/failed';
$route['communication/retry_failed/(:num)']                 = 'communication/retry_failed/$1';
$route['communication/reports']                             = 'communication/reports';
$route['communication/settings']                            = 'communication/settings';
$route['communication/preview_template']                    = 'communication/preview_template';
$route['communication/notices']                             = 'communication/notices';
$route['communication/create_notice']                       = 'communication/create_notice';
$route['communication/announcements']                       = 'communication/announcements';
$route['communication/conversations']                       = 'communication/conversations';
$route['communication/groups']                              = 'communication/groups';

$route['leave']                                   = 'leave/index';
$route['leave/dashboard']                         = 'leave/index';
$route['leave/overview']                          = 'leave/index';
$route['leave/student_leave']                     = 'leave/student_leave';
$route['leave/staff_leave']                       = 'leave/staff_leave';
$route['leave/types']                             = 'leave/types';
$route['leave/request']                           = 'leave/request';
$route['leave/approval']                          = 'leave/approval';
$route['leave/approve/(:num)']                    = 'leave/approve_action/$1';
$route['leave/reject/(:num)']                     = 'leave/reject_action/$1';
$route['leave/clarification/(:num)']              = 'leave/clarification_action/$1';
$route['leave/cancel/(:num)']                     = 'leave/cancel_action/$1';
$route['leave/balances']                          = 'leave/balances';
$route['leave/calendar']                          = 'leave/calendar';
$route['leave/history']                           = 'leave/history';
$route['leave/details/(:num)']                    = 'leave/details/$1';
$route['leave/reports']                           = 'leave/reports';
$route['leave/settings']                          = 'leave/settings';

$route['transport']                               = 'transport/index';
$route['transport/dashboard']                     = 'transport/index';
$route['transport/overview']                      = 'transport/index';
$route['transport/vehicles']                      = 'transport/vehicles';
$route['transport/vehicle_details/(:num)']        = 'transport/vehicle_details/$1';
$route['transport/drivers']                       = 'transport/drivers';
$route['transport/driver_details/(:num)']         = 'transport/driver_details/$1';
$route['transport/routes']                        = 'transport/routes';
$route['transport/route_details/(:num)']          = 'transport/route_details/$1';
$route['transport/stops']                         = 'transport/stops';
$route['transport/assignments']                   = 'transport/assignments';
$route['transport/remove_assignment/(:num)']      = 'transport/remove_assignment_action/$1';
$route['transport/bulk_assign']                   = 'transport/bulk_assign';
$route['transport/fees']                          = 'transport/fees';
$route['transport/maintenance']                   = 'transport/maintenance';
$route['transport/maintenance_history']           = 'transport/maintenance_history';
$route['transport/documents']                     = 'transport/documents';
$route['transport/reports']                       = 'transport/reports';
$route['transport/settings']                      = 'transport/settings';

$route['certificates']                            = 'certificates/index';
$route['certificates/dashboard']                  = 'certificates/index';
$route['certificates/overview']                   = 'certificates/index';
$route['certificates/requests']                   = 'certificates/requests';
$route['certificates/request_create']             = 'certificates/request_create';
$route['certificates/approve_request/(:num)']     = 'certificates/approve_request/$1';
$route['certificates/reject_request/(:num)']      = 'certificates/reject_request/$1';
$route['certificates/types']                      = 'certificates/types';
$route['certificates/bonafide']                   = 'certificates/bonafide';
$route['certificates/transfer_certificate']       = 'certificates/transfer_certificate';
$route['certificates/study_certificate']          = 'certificates/study_certificate';
$route['certificates/conduct_certificate']        = 'certificates/conduct_certificate';
$route['certificates/generate']                   = 'certificates/generate';
$route['certificates/generate/(:num)']            = 'certificates/generate/$1';
$route['certificates/preview/(:num)']             = 'certificates/preview/$1';
$route['certificates/print/(:num)']               = 'certificates/print_cert/$1';
$route['certificates/issue/(:num)']               = 'certificates/issue/$1';
$route['certificates/templates']                  = 'certificates/templates';
$route['certificates/documents']                  = 'certificates/documents';
$route['certificates/document_categories']        = 'certificates/document_categories';
$route['certificates/document_verification']      = 'certificates/document_verification';
$route['certificates/verify_doc/(:num)']          = 'certificates/verify_doc/$1';
$route['certificates/reject_doc/(:num)']          = 'certificates/reject_doc/$1';
$route['certificates/history']                    = 'certificates/history';
$route['certificates/reissue/(:num)']             = 'certificates/reissue/$1';

$route['reports']                                 = 'reports/index';
$route['reports/overview']                        = 'reports/index';

$route['users']                              = 'users/dashboard';
$route['users/dashboard']                    = 'users/dashboard';
$route['users/overview']                     = 'users/dashboard';
$route['users/list']                         = 'users/list_users';
$route['users/create']                       = 'users/create';
$route['users/details']                      = 'users/details';
$route['users/details/(:num)']               = 'users/details/$1';
$route['users/edit/(:num)']                  = 'users/edit/$1';
$route['users/toggle_status/(:num)']         = 'users/toggle_status/$1';
$route['users/delete/(:num)']                = 'users/delete/$1';
$route['users/unlock/(:num)']                = 'users/unlock/$1';
$route['users/reset_password/(:num)']        = 'users/reset_password/$1';
$route['users/roles']                        = 'users/roles';
$route['users/role_permissions']             = 'users/role_permissions';
$route['users/role_permissions/(:num)']      = 'users/role_permissions/$1';
$route['users/designations']                 = 'users/designations';
$route['users/add_designation']              = 'users/add_designation';
$route['users/designation_permissions']      = 'users/designation_permissions';
$route['users/designation_permissions/(:num)'] = 'users/designation_permissions/$1';
$route['users/permissions']                  = 'users/permissions';
$route['users/user_permissions']             = 'users/user_permissions';
$route['users/user_permissions/(:num)']      = 'users/user_permissions/$1';
$route['users/parents']                      = 'users/parents';
$route['users/students']                     = 'users/students';
$route['users/teachers']                     = 'users/teachers';
$route['users/staff']                        = 'users/staff';
$route['users/login_activity']               = 'users/login_activity';
$route['users/security_settings']            = 'users/security_settings';
$route['users/audit_logs']                   = 'users/audit_logs';
$route['change-password']                    = 'users/change_password';
$route['users/change_password']              = 'users/change_password';

$route['settings']                           = 'settings/index';
$route['settings/overview']                  = 'settings/index';
$route['settings/staff_documents']           = 'settings/staff_documents';
$route['settings/staff_documents/add']       = 'settings/staff_documents_add';
$route['settings/staff_documents/edit/(:num)']   = 'settings/staff_documents_edit/$1';
$route['settings/staff_documents/delete/(:num)'] = 'settings/staff_documents_delete/$1';
$route['settings/staff_documents/toggle/(:num)'] = 'settings/staff_documents_toggle/$1';

$route['staff/view_document/(:num)']         = 'staff/view_document/$1';
$route['staff/download_document/(:num)']     = 'staff/download_document/$1';
$route['staff/remove_photo/(:num)']           = 'staff/remove_photo/$1';

$route['unauthorized']                       =  'unauthorized/index';

$route['examinations/ajax_get_divisions']          = 'examinations/ajax_get_divisions';
$route['examinations/ajax_get_divisions/(:num)']    = 'examinations/ajax_get_divisions/$1';

// Fee & Finance Phase 1 Routes
$route['fee-finance']                                = 'finance/dashboard';
$route['fee-finance/dashboard']                      = 'finance/dashboard';
$route['fee-finance/account-groups']                 = 'finance/account_groups';
$route['fee-finance/account_groups']                 = 'finance/account_groups';
$route['fee-finance/account-heads']                  = 'finance/account_heads';
$route['fee-finance/account_heads']                  = 'finance/account_heads';
$route['fee-finance/custom-accounts']                = 'finance/custom_accounts';
$route['fee-finance/custom_accounts']                = 'finance/custom_accounts';

$route['finance']                                    = 'finance/index';
$route['finance/dashboard']                          = 'finance/dashboard';
$route['finance/account-groups']                     = 'finance/account_groups';
$route['finance/account_groups']                     = 'finance/account_groups';
$route['finance/account-heads']                      = 'finance/account_heads';
$route['finance/account_heads']                      = 'finance/account_heads';
$route['finance/custom-accounts']                    = 'finance/custom_accounts';
$route['finance/custom_accounts']                    = 'finance/custom_accounts';
$route['finance/accounts']                           = 'finance/account_heads';

// Phase 1 AJAX Routes
$route['finance/account_groups_ajax']                = 'finance/account_groups_ajax';
$route['finance/save_account_group_ajax']            = 'finance/save_account_group_ajax';
$route['finance/toggle_account_group_status_ajax']   = 'finance/toggle_account_group_status_ajax';
$route['finance/delete_account_group_ajax']          = 'finance/delete_account_group_ajax';

$route['finance/account_heads_ajax']                 = 'finance/account_heads_ajax';
$route['finance/save_account_head_ajax']             = 'finance/save_account_head_ajax';
$route['finance/generate_account_code_ajax']         = 'finance/generate_account_code_ajax';
$route['finance/toggle_account_head_status_ajax']    = 'finance/toggle_account_head_status_ajax';
$route['finance/delete_account_head_ajax']           = 'finance/delete_account_head_ajax';

$route['finance/custom_accounts_ajax']               = 'finance/custom_accounts_ajax';
$route['finance/save_custom_account_ajax']           = 'finance/save_custom_account_ajax';
$route['finance/toggle_custom_account_status_ajax']  = 'finance/toggle_custom_account_status_ajax';
$route['finance/delete_custom_account_ajax']         = 'finance/delete_custom_account_ajax';

$route['finance/ledgers']                            = 'finance/ledgers';
$route['finance/ledger_students']                    = 'finance/ledger_students';
$route['finance/ledger_staff']                       = 'finance/ledger_staff';
$route['finance/ledger_other_parties']               = 'finance/ledger_other_parties';
$route['finance/ledger_general']                     = 'finance/ledger_general';
$route['finance/student_statement/(:num)']          = 'finance/student_statement/$1';
$route['finance/staff_statement/(:num)']            = 'finance/staff_statement/$1';
$route['finance/other_party_statement/(:num)']      = 'finance/other_party_statement/$1';

// Transactions Module Routes
$route['finance/transactions_income']                = 'finance/transactions_income';
$route['finance/income']                             = 'finance/transactions_income';
$route['finance/transactions_expense']               = 'finance/transactions_expense';
$route['finance/adjustments']                        = 'finance/adjustments';
$route['finance/refunds']                            = 'finance/refunds';
$route['finance/view_transaction_ajax/(:num)']       = 'finance/view_transaction_ajax/$1';

// Cash & Bank Module Routes
$route['finance/cash_accounts']                       = 'finance/cash_accounts';
$route['finance/bank_accounts']                       = 'finance/bank_accounts';
$route['finance/cash_bank']                           = 'finance/cash_accounts';
$route['finance/save_cash_account']                   = 'finance/save_cash_account';
$route['finance/save_bank_account']                   = 'finance/save_bank_account';
$route['finance/toggle_account_status']               = 'finance/toggle_account_status';
$route['finance/account_ledger_ajax/(:num)']          = 'finance/account_ledger_ajax/$1';
$route['finance/view_account_ajax/(:num)']            = 'finance/view_account_ajax/$1';
$route['finance/transfers']                           = 'finance/transfers';
$route['finance/view_transfer_ajax/(:num)']           = 'finance/view_transfer_ajax/$1';
$route['finance/reverse_transfer']                    = 'finance/reverse_transfer';
$route['finance/expenses']                           = 'finance/expenses';
$route['finance/expense_entry']                      = 'finance/expenses';
$route['finance/staff_payouts']                      = 'finance/staff_payouts';
$route['finance/vendor_payments']                    = 'finance/vendor_payments';
$route['finance/other_expenses']                     = 'finance/other_expenses';
$route['finance/expense_types']                      = 'finance/expense_types';
$route['finance/view_expense_ajax/(:num)']           = 'finance/view_expense_ajax/$1';
$route['finance/reverse_expense']                    = 'finance/reverse_expense';
$route['finance/journal_entries']                    = 'finance/journal_entries';
$route['finance/reverse_transaction']                = 'finance/reverse_transaction';
$route['finance/reports']                            = 'finance/reports';
$route['finance/reports/(:any)']                     = 'finance/reports/$1';
$route['finance/fee_types']                          = 'finance/fee_types';
$route['finance/fee_structures']                     = 'finance/fee_structures';
$route['finance/fee_assignments']                    = 'finance/fee_assignments';
$route['finance/fee_collection']                     = 'finance/fee_collection';
$route['finance/pending_fees']                       = 'finance/pending_fees';
$route['finance/fee_receipts']                       = 'finance/fee_receipts';
$route['finance/view_invoice_ajax/(:num)']            = 'finance/view_invoice_ajax/$1';
$route['finance/get_class_students_ajax/(:num)']      = 'finance/get_class_students_ajax/$1';
$route['finance/invoice_print/(:num)']                = 'finance/invoice_print/$1';
$route['finance/student_invoice/(:num)']              = 'finance/student_invoice/$1';
$route['finance/student-invoice/(:num)']              = 'finance/student_invoice/$1';
$route['finance/invoice/(:num)']                      = 'finance/student_invoice/$1';
$route['finance/student_receipt/(:num)']              = 'finance/student_receipt/$1';
$route['finance/student-receipt/(:num)']              = 'finance/student_receipt/$1';
$route['finance/receipt/(:num)']                      = 'finance/student_receipt/$1';
$route['finance/fee_receipt/(:num)']                  = 'finance/student_receipt/$1';
$route['finance/receipt_print/(:num)']                = 'finance/receipt_print/$1';
$route['finance/view_receipt_ajax/(:num)']            = 'finance/view_receipt_ajax/$1';
$route['fees/receipt/(:num)']                         = 'finance/student_receipt/$1';
$route['fees/receipt_print/(:num)']                   = 'finance/receipt_print/$1';

// Phase 1: Staff Finance Placeholders & Target Navigation Aliases
$route['finance/salary_setup']                       = 'finance/salary_setup';
$route['finance/salary-setup']                       = 'finance/salary_setup';
$route['finance/salary_processing']                  = 'finance/salary_processing';
$route['finance/salary-processing']                  = 'finance/salary_processing';
$route['finance/salary_payable']                     = 'finance/salary_payable';
$route['finance/salary-payable']                     = 'finance/salary_payable';
$route['finance/salary_payment']                     = 'finance/staff_payouts';
$route['finance/salary-payment']                     = 'finance/staff_payouts';
$route['finance/outstanding_dues']                   = 'finance/pending_fees';
$route['finance/outstanding-dues']                   = 'finance/pending_fees';
$route['finance/receipts']                           = 'finance/fee_receipts';
$route['finance/student_ledger']                     = 'finance/ledger_students';
$route['finance/student-ledger']                     = 'finance/ledger_students';
$route['finance/staff_ledger']                       = 'finance/ledger_staff';
$route['finance/staff-ledger']                       = 'finance/ledger_staff';
$route['finance/vendors_payables']                   = 'finance/vendor_payments';
$route['finance/vendors-payables']                   = 'finance/vendor_payments';
$route['finance/adjustments_refunds']                = 'finance/adjustments';
$route['finance/adjustments-refunds']                = 'finance/adjustments';
$route['finance/general_ledger']                     = 'finance/ledger_general';
$route['finance/general-ledger']                     = 'finance/ledger_general';
$route['finance/financial_reports']                  = 'finance/reports';
$route['finance/financial-reports']                  = 'finance/reports';

// Compatibility aliases for Fee & Finance URLs ensuring zero 404s
$fee_p = 'fees';
$route[$fee_p]                                       = 'finance/dashboard';
$route[$fee_p . '/dashboard']                        = 'finance/dashboard';
$route[$fee_p . '/collection']                       = 'finance/fee_collection';
$route[$fee_p . '/due_fees']                         = 'finance/pending_fees';
$route[$fee_p . '/pending_fees']                     = 'finance/pending_fees';
$route[$fee_p . '/structures']                       = 'finance/fee_structures';
$route[$fee_p . '/fee_structures']                   = 'finance/fee_structures';
$route[$fee_p . '/assignments']                      = 'finance/fee_assignments';
$route[$fee_p . '/fee_assignments']                  = 'finance/fee_assignments';
$route[$fee_p . '/categories']                       = 'finance/fee_types';
$route[$fee_p . '/fee_types']                        = 'finance/fee_types';
$route[$fee_p . '/payments']                         = 'finance/fee_receipts';
$route[$fee_p . '/receipts']                         = 'finance/fee_receipts';
$route[$fee_p . '/fee_receipts']                     = 'finance/fee_receipts';
$route[$fee_p . '/student_fees']                     = 'finance/ledger_students';
$route[$fee_p . '/reports']                          = 'finance/reports';
$route[$fee_p . '/reports/(:any)']                   = 'finance/reports/$1';
$route[$fee_p . '/adjustments']                      = 'finance/adjustments';
$route[$fee_p . '/refunds']                          = 'finance/refunds';

