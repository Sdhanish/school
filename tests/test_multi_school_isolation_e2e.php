<?php
/**
 * Comprehensive Multi-School Isolation End-to-End Test Suite
 *
 * Tests:
 * 1. School creation & automatic entity seeding (Roles, Academic Year, Settings).
 * 2. Super Admin school switching & session persistence.
 * 3. Normal user school immutability (blocking IDOR tampering).
 * 4. Academic year independence per school.
 * 5. Class & Division isolation.
 * 6. Student isolation & duplicate admission numbers across different schools.
 * 7. IDOR attack prevention across models (get_by_id cross-school returns null).
 * 8. Staff isolation.
 * 9. Fee structure & payments isolation (Metrics & collections).
 * 10. Attendance sheet & dashboard stats isolation.
 * 11. Global search isolation.
 * 12. Dynamic school branding in PDF service settings.
 */

// Define CLI environment and bootstrap CodeIgniter 3
define('ENVIRONMENT', 'development');
$_SERVER['CI_ENV'] = 'development';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['HTTP_HOST'] = 'localhost';

require_once __DIR__ . '/../index.php';

$CI =& get_instance();
$CI->load->database();
$CI->load->library('session');
$CI->load->model('School_model');
$CI->load->model('Student_model');
$CI->load->model('Staff_model');
$CI->load->model('Class_model');
$CI->load->model('Division_model');
$CI->load->model('Academic_year_model');
$CI->load->model('Fee_model');
$CI->load->model('Fee_category_model');
$CI->load->model('Fee_structure_model');
$CI->load->model('Attendance_model');
$CI->load->model('Setting_model');
$CI->load->model('Dashboard_model');
$CI->load->model('Global_search_model');

class MultiSchoolTestSuite {

    private $CI;
    private $passed = 0;
    private $failed = 0;
    private $created_school_id = null;

    public function __construct($ci) {
        $this->CI = $ci;
    }

    public function assert($condition, $test_name, $details = '') {
        if ($condition) {
            $this->passed++;
            echo "  [PASS] {$test_name}\n";
        } else {
            $this->failed++;
            echo "  [FAIL] {$test_name}" . ($details ? " - {$details}" : "") . "\n";
        }
    }

    public function run() {
        echo "============================================================\n";
        echo " MULTI-SCHOOL DATA ISOLATION & SECURITY E2E TEST SUITE\n";
        echo "============================================================\n\n";

        try {
            $this->test_1_verify_default_school();
            $this->test_2_create_second_school_with_auto_seeding();
            $this->test_3_super_admin_switching();
            $this->test_4_normal_user_tamper_blocking();
            $this->test_5_academic_year_isolation();
            $this->test_6_class_and_division_isolation();
            $this->test_7_duplicate_admission_number_across_schools();
            $this->test_8_student_crud_and_idor_protection();
            $this->test_9_staff_isolation();
            $this->test_10_fee_and_payment_isolation();
            $this->test_11_attendance_isolation();
            $this->test_12_global_search_isolation();
            $this->test_13_school_branding_and_settings();
        } catch (Exception $e) {
            echo "EXCEPTION OCCURRED: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
            $this->failed++;
        } finally {
            $this->cleanup();
        }

        echo "\n============================================================\n";
        echo " TEST SUMMARY\n";
        echo "============================================================\n";
        echo " Passed: {$this->passed}\n";
        echo " Failed: {$this->failed}\n";
        echo " Total:  " . ($this->passed + $this->failed) . "\n";
        echo "============================================================\n";

        return ($this->failed === 0);
    }

    private function test_1_verify_default_school() {
        echo "[1] Testing Default School (School 1 Integrity)...\n";
        $school1 = $this->CI->School_model->get_by_id(1);
        $this->assert(!empty($school1), "Default School (id=1) exists");
        $this->assert($school1->status === 'active', "Default School is active");
        $this->assert(!empty($school1->school_code), "Default School has school_code: {$school1->school_code}");
    }

    private function test_2_create_second_school_with_auto_seeding() {
        echo "\n[2] Testing Creation of School 2 & Auto-seeding...\n";
        // Clean up any previous test school with this code
        $prev = $this->CI->db->where('school_code', 'TEST-SCH-02')->get('tbl_schools')->row();
        if ($prev) {
            $this->CI->School_model->delete_school($prev->id);
        }

        $school_data = array(
            'school_name'     => 'KHM Higher Secondary School',
            'school_code'     => 'TEST-SCH-02',
            'email'           => 'admin@khmschool.edu',
            'phone'           => '9876543210',
            'address'         => 'Valanchery, Malappuram, Kerala',
            'status'          => 'active',
            'subscription_plan' => 'enterprise',
            'max_students'    => 1000,
            'max_staff'       => 100,
        );

        $new_school_id = $this->CI->School_model->create_school($school_data);
        $this->created_school_id = $new_school_id;
        $this->assert($new_school_id > 1, "School 2 created with ID: {$new_school_id}");

        // Check storage config
        $storage = $this->CI->db->where('school_id', $new_school_id)->get('tbl_school_storage_configs')->row();
        $this->assert(!empty($storage), "Auto-seeded storage configuration for School 2");

        // Check seeded roles
        $roles_count = $this->CI->db->where('school_id', $new_school_id)->count_all_results('tbl_roles');
        $this->assert($roles_count >= 5, "Auto-seeded {$roles_count} standard roles for School 2");

        // Check seeded academic year
        $ay = $this->CI->db->where('school_id', $new_school_id)->get('tbl_academic_years')->row();
        $this->assert(!empty($ay) && $ay->is_active == 1, "Auto-seeded active academic year for School 2: " . ($ay ? $ay->year_name : 'none'));

        // Check settings
        $settings = $this->CI->Setting_model->get_settings($new_school_id);
        $this->assert($settings->school_name === 'KHM Higher Secondary School', "School settings seeded correctly for School 2");
    }

    private function test_3_super_admin_switching() {
        echo "\n[3] Testing Super Admin School Switching...\n";
        // Simulate Super Admin session
        $_SESSION['user_id'] = 1;
        $_SESSION['user_type'] = 'Admin';
        $_SESSION['role_id'] = 1;
        $_SESSION['role_code'] = 'SUPER_ADMIN';
        $_SESSION['is_super_admin'] = 1;
        $_SESSION['school_id'] = 1;

        // Switch to School 2
        $switched = set_current_school_id($this->created_school_id);
        $this->assert($switched === true, "Super Admin successfully switched to School 2");
        $this->assert(get_current_school_id() === (int)$this->created_school_id, "get_current_school_id() returns School 2 ID");
        $this->assert($_SESSION['active_school_id'] === (int)$this->created_school_id, "Session active_school_id updated to School 2");

        // Switch back to School 1
        $switched_back = set_current_school_id(1);
        $this->assert($switched_back === true, "Super Admin successfully switched back to School 1");
        $this->assert(get_current_school_id() === 1, "get_current_school_id() returns School 1 ID");
    }

    private function test_4_normal_user_tamper_blocking() {
        echo "\n[4] Testing Normal User School Tampering / IDOR Prevention...\n";
        // Simulate normal Teacher belonging to School 1
        $_SESSION['user_id'] = 999;
        $_SESSION['user_type'] = 'Teacher';
        $_SESSION['role_id'] = 3;
        $_SESSION['role_code'] = 'TEACHER';
        $_SESSION['is_super_admin'] = 0;
        $_SESSION['school_id'] = 1;
        unset($_SESSION['active_school_id']);

        $can_switch = can_switch_school();
        $this->assert($can_switch === false, "can_switch_school() returns FALSE for normal teacher");

        // Attempt unauthorized switch
        $tamper_attempt = set_current_school_id($this->created_school_id);
        $this->assert($tamper_attempt === false, "set_current_school_id() rejects normal user tampering");
        $this->assert(get_current_school_id() === 1, "get_current_school_id() strictly remains 1");

        // Restore Super Admin session for subsequent automated tests
        $_SESSION['user_id'] = 1;
        $_SESSION['user_type'] = 'Admin';
        $_SESSION['role_id'] = 1;
        $_SESSION['role_code'] = 'SUPER_ADMIN';
        $_SESSION['is_super_admin'] = 1;
        $_SESSION['school_id'] = 1;
    }

    private function test_5_academic_year_isolation() {
        echo "\n[5] Testing Academic Year Isolation...\n";
        set_current_school_id(1);
        $ay1 = $this->CI->Academic_year_model->get_all();
        foreach ($ay1 as $y) {
            $this->assert((int)$y->school_id === 1, "School 1 academic year ({$y->year_name}) has school_id=1");
        }

        set_current_school_id($this->created_school_id);
        $ay2 = $this->CI->Academic_year_model->get_all();
        $this->assert(!empty($ay2), "School 2 has its own academic year list");
        foreach ($ay2 as $y) {
            $this->assert((int)$y->school_id === (int)$this->created_school_id, "School 2 academic year ({$y->year_name}) has school_id={$this->created_school_id}");
        }
    }

    private function test_6_class_and_division_isolation() {
        echo "\n[6] Testing Class & Division Isolation...\n";
        // In School 2, create a class and division
        set_current_school_id($this->created_school_id);

        $school2_ay = $this->CI->Academic_year_model->get_active_year();
        $class_id_sch2 = $this->CI->Class_model->insert(array(
            'class_name'        => 'Standard 10 - KHM',
            'academic_year_id'  => $school2_ay ? $school2_ay->academic_year_id : 1,
            'status'            => 1,
        ));
        $this->assert($class_id_sch2 > 0, "Created class in School 2 with ID: {$class_id_sch2}");

        $div_id_sch2 = $this->CI->Division_model->insert(array(
            'class_id'      => $class_id_sch2,
            'division_name' => 'Rose',
            'status'        => 1,
        ));
        $this->assert($div_id_sch2 > 0, "Created division 'Rose' in School 2 with ID: {$div_id_sch2}");

        // Switch to School 1: verify School 1 CANNOT see Standard 10 - KHM
        set_current_school_id(1);
        $classes_sch1 = $this->CI->Class_model->get_all();
        $found_in_sch1 = false;
        foreach ($classes_sch1 as $c) {
            if ($c->class_id == $class_id_sch2 || $c->class_name === 'Standard 10 - KHM') {
                $found_in_sch1 = true;
            }
        }
        $this->assert($found_in_sch1 === false, "School 1 does NOT see School 2's class 'Standard 10 - KHM'");

        // IDOR check: School 1 trying to get School 2 class by ID
        $idor_class = $this->CI->Class_model->get_by_id($class_id_sch2);
        $this->assert(empty($idor_class), "Class_model::get_by_id({$class_id_sch2}) from School 1 returns NULL (IDOR blocked)");
    }

    private function test_7_duplicate_admission_number_across_schools() {
        echo "\n[7] Testing Duplicate Admission Numbers Across Different Schools...\n";
        $test_adm_no = 'TEST-ADM-SAME-01';

        // 1. In School 1: insert student with test_adm_no
        set_current_school_id(1);
        // Clean up any previous test student with this adm number in School 1
        $this->CI->db->where('admission_number', $test_adm_no)->delete('tbl_students');

        $class_sch1 = $this->CI->db->where('school_id', 1)->where('status', 1)->get('tbl_classes')->row();
        $class_id1 = $class_sch1 ? $class_sch1->class_id : 1;

        $student1_id = $this->CI->Student_model->insert(array(
            'admission_number' => $test_adm_no,
            'first_name'       => 'Rahul',
            'last_name'        => 'SchoolOne',
            'gender'           => 'Male',
            'dob'              => '2010-05-15',
            'class_id'         => $class_id1,
            'status'           => 1,
            'is_deleted'       => 'n',
        ));
        $this->assert($student1_id > 0, "Created Student in School 1 with adm_no '{$test_adm_no}' (ID: {$student1_id})");

        // 2. In School 2: insert student with the EXACT SAME admission number
        set_current_school_id($this->created_school_id);
        $class_sch2 = $this->CI->db->where('school_id', $this->created_school_id)->where('status', 1)->get('tbl_classes')->row();
        $class_id2 = $class_sch2 ? $class_sch2->class_id : 1;

        $student2_id = $this->CI->Student_model->insert(array(
            'admission_number' => $test_adm_no,
            'first_name'       => 'Sameera',
            'last_name'        => 'SchoolTwo',
            'gender'           => 'Female',
            'dob'              => '2010-08-20',
            'class_id'         => $class_id2,
            'status'           => 1,
            'is_deleted'       => 'n',
        ));
        $this->assert($student2_id > 0, "Created Student in School 2 with identical adm_no '{$test_adm_no}' (ID: {$student2_id}) - Composite Unique Key verified!");
    }

    private function test_8_student_crud_and_idor_protection() {
        echo "\n[8] Testing Student Query Isolation & IDOR Protection...\n";
        // In School 1 context
        set_current_school_id(1);
        $students_sch1 = $this->CI->Student_model->get_all();
        foreach ($students_sch1 as $s) {
            $this->assert((int)$s->school_id === 1, "School 1 student ({$s->first_name}) has school_id=1");
        }

        // In School 2 context
        set_current_school_id($this->created_school_id);
        $students_sch2 = $this->CI->Student_model->get_all();
        $this->assert(count($students_sch2) >= 1, "School 2 has students");
        foreach ($students_sch2 as $s) {
            $this->assert((int)$s->school_id === (int)$this->created_school_id, "School 2 student ({$s->first_name}) has school_id={$this->created_school_id}");
        }

        // Cross-school IDOR: Student 2 ID queried from School 1 context
        $s2_id = $students_sch2[0]->student_id;
        set_current_school_id(1);
        $idor_student = $this->CI->Student_model->get_by_id($s2_id);
        $this->assert(empty($idor_student), "Student_model::get_by_id({$s2_id}) from School 1 returns NULL (cross-school read blocked)");

        // Cross-school soft delete attempt
        $delete_attempt = $this->CI->Student_model->soft_delete($s2_id, 1);
        $this->assert($delete_attempt === false, "Student_model::soft_delete({$s2_id}) from School 1 rejected (cross-school delete blocked)");
    }

    private function test_9_staff_isolation() {
        echo "\n[9] Testing Staff Isolation...\n";
        // Create staff in School 2
        set_current_school_id($this->created_school_id);
        $staff_sch2_id = $this->CI->Staff_model->insert(array(
            'staff_code' => 'KHM-STF-01',
            'first_name' => 'Fathima',
            'last_name'  => 'Teacher',
            'email'      => 'fathima@khmschool.edu',
            'phone'      => '9123456780',
            'designation'=> 'Mathematics Teacher',
            'staff_type' => 'Teaching',
            'status'     => 1,
            'is_deleted' => 'n'
        ));
        $this->assert($staff_sch2_id > 0, "Created Staff in School 2 with ID: {$staff_sch2_id}");

        // Switch to School 1
        set_current_school_id(1);
        $staff_sch1 = $this->CI->Staff_model->get_all();
        $found_in_sch1 = false;
        foreach ($staff_sch1 as $stf) {
            if ($stf->staff_id == $staff_sch2_id) {
                $found_in_sch1 = true;
            }
        }
        $this->assert($found_in_sch1 === false, "School 1 staff list does NOT contain School 2 staff");

        // IDOR test
        $idor_staff = $this->CI->Staff_model->get_by_id($staff_sch2_id);
        $this->assert(empty($idor_staff), "Staff_model::get_by_id({$staff_sch2_id}) from School 1 returns NULL (cross-school staff blocked)");
    }

    private function test_10_fee_and_payment_isolation() {
        echo "\n[10] Testing Fee Structures & Payments Isolation...\n";
        // In School 2: create fee head and structure
        set_current_school_id($this->created_school_id);
        $head_id = $this->CI->Fee_category_model->save(array(
            'head_name'     => 'KHM Term 1 Tuition Fee',
            'category_code' => 'KHM-T1',
            'status'        => 1,
            'is_deleted'    => 'n'
        ));
        $this->assert($head_id > 0, "Created Fee Head in School 2 with ID: {$head_id}");

        // Switch to School 1: verify School 1 does not see KHM Term 1 Tuition Fee
        set_current_school_id(1);
        $sch1_heads = $this->CI->Fee_category_model->get_all();
        $found_in_sch1 = false;
        foreach ($sch1_heads as $h) {
            if ($h->head_name === 'KHM Term 1 Tuition Fee') {
                $found_in_sch1 = true;
            }
        }
        $this->assert($found_in_sch1 === false, "School 1 fee heads do NOT contain School 2's 'KHM Term 1 Tuition Fee'");

        // Fee metrics isolation
        $metrics_sch1 = $this->CI->Fee_model->get_dashboard_metrics();
        $this->assert(is_array($metrics_sch1) && isset($metrics_sch1['total_expected']), "Fee_model::get_dashboard_metrics() runs safely for School 1");
    }

    private function test_11_attendance_isolation() {
        echo "\n[11] Testing Attendance Isolation...\n";
        set_current_school_id($this->created_school_id);
        $today = date('Y-m-d');
        $student_sch2 = $this->CI->db->where('school_id', $this->created_school_id)->get('tbl_students')->row();
        $this->assert(!empty($student_sch2), "Found student in School 2 for attendance marking");

        if ($student_sch2) {
            $saved = $this->CI->Attendance_model->save_daily_attendance(
                array($student_sch2->student_id => 'Present'),
                $today,
                1,
                $student_sch2->class_id,
                $student_sch2->division_id ?: 0,
                1
            );
            $this->assert($saved > 0, "Saved daily attendance in School 2");

            // Verify attendance record has school_id = School 2
            $att_rec = $this->CI->db->where('student_id', $student_sch2->student_id)->where('attendance_date', $today)->get('tbl_attendance')->row();
            $this->assert(!empty($att_rec) && (int)$att_rec->school_id === (int)$this->created_school_id, "Attendance record tagged with School 2 ID");

            // Switch to School 1 and query attendance sheet
            set_current_school_id(1);
            $sch1_att_stats = $this->CI->Attendance_model->get_dashboard_stats($today);
            $this->assert(is_object($sch1_att_stats), "Attendance_model::get_dashboard_stats() returns object for School 1");
        }
    }

    private function test_12_global_search_isolation() {
        echo "\n[12] Testing Global Search Isolation...\n";
        // Search for 'Sameera' (School 2 student) while in School 1
        set_current_school_id(1);
        $results_sch1 = $this->CI->Global_search_model->search_all('Sameera');
        $this->assert(empty($results_sch1['students']), "Searching for School 2 student 'Sameera' while in School 1 returns 0 results");

        // Search for 'Sameera' while in School 2
        set_current_school_id($this->created_school_id);
        $results_sch2 = $this->CI->Global_search_model->search_all('Sameera');
        $this->assert(!empty($results_sch2['students']), "Searching for 'Sameera' while in School 2 returns the student");
    }

    private function test_13_school_branding_and_settings() {
        echo "\n[13] Testing School Branding & Settings Isolation...\n";
        // In School 2
        $sch2_settings = $this->CI->Setting_model->get_settings($this->created_school_id);
        $this->assert($sch2_settings->school_name === 'KHM Higher Secondary School', "School 2 branding name is 'KHM Higher Secondary School'");

        // In School 1
        $sch1_settings = $this->CI->Setting_model->get_settings(1);
        $this->assert($sch1_settings->school_name !== 'KHM Higher Secondary School', "School 1 branding name is completely distinct: '{$sch1_settings->school_name}'");
    }

    private function cleanup() {
        echo "\n[Cleaning up test data...]\n";
        if ($this->created_school_id) {
            $this->CI->School_model->delete_school($this->created_school_id);
            echo "  Cleaned up test school (ID: {$this->created_school_id})\n";
        }
        // Clean up test student from School 1
        $this->CI->db->where('admission_number', 'TEST-ADM-SAME-01')->delete('tbl_students');
        set_current_school_id(1);
        echo "  Reset active school to School 1\n";
    }
}

$suite = new MultiSchoolTestSuite($CI);
$success = $suite->run();
exit($success ? 0 : 1);
