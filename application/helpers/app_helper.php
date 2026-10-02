<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * School Application Helper
 *
 * Centralised utility functions used across controllers, models, and views.
 * Autoloaded via config/autoload.php.
 */

// ---------------------------------------------------------------------------
// User / Identity helpers
// ---------------------------------------------------------------------------

/**
 * Generate up to 2 initials from a full name.
 * e.g. "Ananthu Kumar" → "AK",  "Admin" → "AD"
 */
if ( ! function_exists('school_initials'))
{
    function school_initials($name)
    {
        $name = trim((string)$name);
        if ($name === '') return 'U';

        $parts    = preg_split('/\s+/', $name);
        $initials = '';
        foreach ($parts as $p) {
            if (!empty($p)) $initials .= strtoupper($p[0]);
        }
        $initials = substr($initials, 0, 2);
        return $initials ?: 'U';
    }
}

// ---------------------------------------------------------------------------
// Formatting helpers
// ---------------------------------------------------------------------------

/**
 * Format a number as Indian currency.
 * e.g. 841200 → "₹ 8,41,200"
 */
if ( ! function_exists('school_currency'))
{
    function school_currency($amount, $symbol = '₹', $decimals = 2)
    {
        $amount = (float)$amount;
        // Format absolute amount with specified decimals, suppressing default thousands separator
        $formatted = number_format(abs($amount), $decimals, '.', '');
        // Convert integer part to Indian style (thousands, then lakhs/crores in pairs of 2)
        $parts  = explode('.', $formatted);
        $int    = $parts[0];
        $dec    = (isset($parts[1]) && $decimals > 0) ? '.' . $parts[1] : '';

        if (strlen($int) > 3) {
            $last3 = substr($int, -3);
            $rest  = substr($int, 0, -3);
            $rest  = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
            $int   = $rest . ',' . $last3;
        }

        $sign = $amount < 0 ? '-' : '';
        return $sign . $symbol . ' ' . $int . $dec;
    }
}

/**
 * Return a human-readable "time ago" string.
 * e.g. "2 hours ago", "3 days ago"
 */
if ( ! function_exists('school_timeago'))
{
    function school_timeago($datetime)
    {
        if (empty($datetime)) return '—';
        $now  = time();
        $then = is_numeric($datetime) ? (int)$datetime : strtotime($datetime);
        if ($then === FALSE) return '—';
        $diff = max(0, $now - $then);

        if ($diff < 60)       return 'just now';
        if ($diff < 3600)     return floor($diff / 60) . ' min ago';
        if ($diff < 86400)    return floor($diff / 3600) . ' hr ago';
        if ($diff < 604800)   return floor($diff / 86400) . 'd ago';
        if ($diff < 2592000)  return floor($diff / 604800) . ' weeks ago';
        if ($diff < 31536000) return floor($diff / 2592000) . ' months ago';
        return floor($diff / 31536000) . ' yrs ago';
    }
}

/**
 * Combine first, optional middle, and last name into a consistently formatted full name.
 * Supports:
 * - school_student_name($studentObjectOrArray)
 * - school_student_name($first_name, $last_name)
 * - school_student_name($first_name, $middle_name, $last_name)
 */
if ( ! function_exists('school_student_name'))
{
    function school_student_name($first_name, $middle_or_last = '', $last_name = '')
    {
        $f = '';
        $m = '';
        $l = '';

        if (is_object($first_name)) {
            $f = $first_name->first_name ?? '';
            $m = $first_name->middle_name ?? '';
            $l = $first_name->last_name ?? '';
        } elseif (is_array($first_name)) {
            $f = $first_name['first_name'] ?? '';
            $m = $first_name['middle_name'] ?? '';
            $l = $first_name['last_name'] ?? '';
        } else {
            if (func_num_args() === 2) {
                // Called as school_student_name($first_name, $last_name)
                $f = $first_name;
                $m = '';
                $l = $middle_or_last;
            } else {
                $f = $first_name;
                $m = $middle_or_last;
                $l = $last_name;
            }
        }

        $parts = array();
        foreach (array($f, $m, $l) as $p) {
            $str = trim((string)$p);
            if ($str !== '' && strcasecmp($str, 'null') !== 0 && $str !== '-') {
                $parts[] = $str;
            }
        }

        return implode(' ', $parts);
    }
}

/**
 * Format student address cleanly from separate fields without duplicate commas or blanks.
 */
if ( ! function_exists('school_format_address'))
{
    function school_format_address($house_or_student, $street = '', $city = '', $district = '', $state = '', $pin_code = '')
    {
        $house = '';
        $str   = '';
        $c     = '';
        $dist  = '';
        $st    = '';
        $pin   = '';
        $fallback = '';

        if (is_object($house_or_student)) {
            $house = $house_or_student->house_name ?? '';
            $str   = $house_or_student->street ?? '';
            $c     = $house_or_student->city ?? '';
            $dist  = $house_or_student->district ?? '';
            $st    = $house_or_student->state ?? '';
            $pin   = $house_or_student->pin_code ?? '';
            $fallback = $house_or_student->address ?? '';
        } elseif (is_array($house_or_student)) {
            $house = $house_or_student['house_name'] ?? '';
            $str   = $house_or_student['street'] ?? '';
            $c     = $house_or_student['city'] ?? '';
            $dist  = $house_or_student['district'] ?? '';
            $st    = $house_or_student['state'] ?? '';
            $pin   = $house_or_student['pin_code'] ?? '';
            $fallback = $house_or_student['address'] ?? '';
        } else {
            $house = $house_or_student;
            $str   = $street;
            $c     = $city;
            $dist  = $district;
            $st    = $state;
            $pin   = $pin_code;
        }

        $parts = array();
        foreach (array($house, $str, $c, $dist, $st) as $val) {
            $clean = trim((string)$val);
            if ($clean !== '') {
                $parts[] = $clean;
            }
        }

        $formatted = implode(', ', $parts);
        $pinClean = trim((string)$pin);
        if ($pinClean !== '') {
            $formatted .= ($formatted !== '' ? ' — ' : '') . $pinClean;
        }

        return $formatted !== '' ? $formatted : trim((string)$fallback);
    }
}

/**
 * Format a date for display (accepts Y-m-d or any strtotime-compatible string).
 * e.g. "2025-08-15" → "15 Aug 2025"
 */
if ( ! function_exists('school_date'))
{
    function school_date($date, $format = 'd M Y')
    {
        if (empty($date) || $date === '0000-00-00') return '—';
        $ts = is_numeric($date) ? (int)$date : strtotime($date);
        return $ts ? date($format, $ts) : '—';
    }
}

// ---------------------------------------------------------------------------
// Status / badge helpers (return safe HTML strings)
// ---------------------------------------------------------------------------

/**
 * Return a Tailwind-styled badge span for common status values.
 * Safe to echo directly in views (values are html_escape'd).
 */
if ( ! function_exists('school_status_badge'))
{
    function school_status_badge($status)
    {
        $map = [
            'Active'    => 'bg-secondary-container text-on-secondary-container',
            'Inactive'  => 'bg-surface-container-high text-on-surface-variant',
            'Locked'    => 'bg-error-container text-on-error-container',
            'Suspended' => 'bg-error-container text-on-error-container',
            'Pending'   => 'bg-tertiary-fixed text-on-tertiary-fixed',
            'Approved'  => 'bg-secondary-container text-on-secondary-container',
            'Rejected'  => 'bg-error-container text-on-error-container',
            'Present'   => 'bg-secondary-container text-on-secondary-container',
            'Absent'    => 'bg-error-container text-on-error-container',
            'Late'      => 'bg-tertiary-fixed text-on-tertiary-fixed',
            'Paid'      => 'bg-secondary-container text-on-secondary-container',
            'Unpaid'    => 'bg-error-container text-on-error-container',
            'Partial'   => 'bg-tertiary-fixed text-on-tertiary-fixed',
        ];
        $label = html_escape($status);
        $cls   = isset($map[$status])
            ? $map[$status]
            : 'bg-surface-container-high text-on-surface-variant';
        return '<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold ' . $cls . '">' . $label . '</span>';
    }
}

/**
 * Render a yes/no icon badge.
 */
if ( ! function_exists('school_yn_badge'))
{
    function school_yn_badge($value)
    {
        if ($value === 'y' || $value === 1 || $value === true) {
            return '<span class="material-symbols-outlined text-[16px] text-on-secondary-container">check_circle</span>';
        }
        return '<span class="material-symbols-outlined text-[16px] text-on-surface-variant">cancel</span>';
    }
}

// ---------------------------------------------------------------------------
// Security / sanitisation helpers
// ---------------------------------------------------------------------------

/**
 * Cast a value to a positive integer. Returns NULL if invalid/zero.
 * Use for URL segment IDs to avoid IDOR with non-numeric values.
 */
if ( ! function_exists('school_id'))
{
    function school_id($value)
    {
        $id = (int)$value;
        return $id > 0 ? $id : NULL;
    }
}

/**
 * Safely get an array value with a default fallback.
 */
if ( ! function_exists('school_array_get'))
{
    function school_array_get(array $arr, $key, $default = NULL)
    {
        return isset($arr[$key]) ? $arr[$key] : $default;
    }
}

// ---------------------------------------------------------------------------
// Centralized Active School Context Helpers (Multi-School Isolation)
// ---------------------------------------------------------------------------

/**
 * Get the currently active school ID.
 * Resolves from trusted server-side session:
 * - Super Admin: session('active_school_id') or session('school_id')
 * - Normal User: strictly user['school_id'] from authenticated session
 * - Fallback: 1 (Default School)
 *
 * @param  bool $reset Force cache invalidation
 * @return int
 */
if ( ! function_exists('get_current_school_id'))
{
    function get_current_school_id($reset = FALSE)
    {
        static $cached_school_id = NULL;
        if ($reset) {
            $cached_school_id = NULL;
        }
        if ($cached_school_id !== NULL) {
            return $cached_school_id;
        }

        $CI =& get_instance();
        if (!isset($CI->session)) {
            $CI->load->library('session');
        }

        $user_data = $CI->session->userdata('user');
        $is_super_admin = false;
        if (!empty($user_data)) {
            $is_super_admin = (($user_data['role_code'] ?? '') === 'SUPER_ADMIN' || ($user_data['role'] ?? '') === 'Super Admin' || (int)($user_data['role_id'] ?? 0) === 1 || !empty($user_data['is_super_admin']));
        } else {
            $flat_role = $CI->session->userdata('role_code');
            $flat_is_super = $CI->session->userdata('is_super_admin');
            $flat_role_id = $CI->session->userdata('role_id');
            if ($flat_role === 'SUPER_ADMIN' || $flat_is_super == 1 || (int)$flat_role_id === 1) {
                $is_super_admin = true;
            }
        }

        if ($is_super_admin) {
            $active_sid = $CI->session->userdata('active_school_id') ?: $CI->session->userdata('school_id');
            if ($active_sid && (int)$active_sid > 0) {
                $cached_school_id = (int)$active_sid;
                return $cached_school_id;
            }
        } else {
            if (!empty($user_data['school_id'])) {
                $cached_school_id = (int)$user_data['school_id'];
                return $cached_school_id;
            }
            $flat_sid = $CI->session->userdata('school_id');
            if ($flat_sid && (int)$flat_sid > 0) {
                $cached_school_id = (int)$flat_sid;
                return $cached_school_id;
            }
        }

        $cached_school_id = 1; // Default fallback to School 1
        return $cached_school_id;
    }
}

/**
 * Get the full record object for the currently active school.
 *
 * @param  bool $reset Force cache invalidation
 * @return object|null
 */
if ( ! function_exists('get_current_school'))
{
    function get_current_school($reset = FALSE)
    {
        static $cached_school = NULL;
        if ($reset) {
            $cached_school = NULL;
        }
        if ($cached_school !== NULL) {
            return $cached_school;
        }

        $CI =& get_instance();
        if (!isset($CI->School_model)) {
            $CI->load->model('School_model');
        }

        $school_id = get_current_school_id($reset);
        $school = $CI->School_model->get_by_id($school_id);
        if (!$school && $school_id !== 1) {
            $school = $CI->School_model->get_by_id(1);
        }

        $cached_school = $school ?: null;
        return $cached_school;
    }
}

/**
 * Set the currently active school context in session (Super Admin only).
 * Automatically synchronizes the active academic year to the new school's active year.
 *
 * @param  int $school_id
 * @return bool
 */
if ( ! function_exists('set_current_school_id'))
{
    function set_current_school_id($school_id)
    {
        $CI =& get_instance();
        if (!can_switch_school()) {
            return FALSE;
        }

        if (!isset($CI->School_model)) {
            $CI->load->model('School_model');
        }

        $school = $CI->School_model->get_by_id((int)$school_id);
        if (!$school || $school->is_deleted === 'y' || $school->status !== 'Active') {
            return FALSE;
        }

        $CI->session->set_userdata('active_school_id', (int)$school->id);
        $CI->session->set_userdata('school_id', (int)$school->id);

        // Synchronize active academic year to this school's active academic year
        if (!isset($CI->Academic_year_model)) {
            $CI->load->model('Academic_year_model');
        }
        $school_year = $CI->Academic_year_model->get_strictly_active_year((int)$school->id);
        if (!$school_year) {
            $school_year = $CI->Academic_year_model->get_active_year((int)$school->id);
        }
        if ($school_year) {
            $CI->session->set_userdata('selected_academic_year_id', (int)$school_year->academic_year_id);
            $CI->session->set_userdata('academic_year_id', (int)$school_year->academic_year_id);
        }

        clear_school_cache();
        clear_academic_year_cache();
        return TRUE;
    }
}

/**
 * Retrieve all available schools for the header dropdown selector:
 * - Super Admin: returns all active, non-deleted schools
 * - Normal User: returns ONLY their assigned school
 *
 * @return array
 */
if ( ! function_exists('get_available_schools'))
{
    function get_available_schools()
    {
        $CI =& get_instance();
        if (!isset($CI->School_model)) {
            $CI->load->model('School_model');
        }

        if (can_switch_school()) {
            return $CI->School_model->get_active_schools();
        }

        $curr = get_current_school();
        return $curr ? [$curr] : [];
    }
}

/**
 * Check if the user has permission to switch schools (Super Admin only).
 *
 * @param  int|null $user_id
 * @return bool
 */
if ( ! function_exists('can_switch_school'))
{
    function can_switch_school($user_id = NULL)
    {
        $CI =& get_instance();
        if (!isset($CI->rbac)) {
            $CI->load->library('Rbac');
        }
        return (bool)$CI->rbac->is_super_admin($user_id);
    }
}

/**
 * Clear in-memory static caches for school resolution.
 */
if ( ! function_exists('clear_school_cache'))
{
    function clear_school_cache()
    {
        get_current_school_id(TRUE);
        get_current_school(TRUE);
    }
}

/**
 * Retrieve the active storage configuration for the current school.
 */
if ( ! function_exists('get_current_school_storage'))
{
    function get_current_school_storage($school_id = NULL)
    {
        $CI =& get_instance();
        if ($school_id === NULL) {
            $school_id = get_current_school_id();
        }
        $CI->load->model('School_model');
        return $CI->School_model->get_storage_config($school_id);
    }
}

/**
 * Get school upload path for saving files (creates directory if missing).
 */
if ( ! function_exists('get_school_upload_path'))
{
    function get_school_upload_path($subfolder = '', $school_id = NULL)
    {
        if ($school_id === NULL) {
            $school_id = get_current_school_id();
        }
        $base = FCPATH . 'uploads/schools/' . (int)$school_id . '/';
        if ($subfolder !== '') {
            $base .= trim($subfolder, '/') . '/';
        }
        if (!is_dir($base)) {
            @mkdir($base, 0755, true);
        }
        return $base;
    }
}

// ---------------------------------------------------------------------------
// Global Academic Year Context Helpers (School-Scoped)
// ---------------------------------------------------------------------------

/**
 * Get the currently active academic year ID (defaults to active year of active school).
 * Returns integer ID. Uses per-request caching to eliminate duplicate queries.
 */
if ( ! function_exists('get_current_academic_year_id'))
{
    function get_current_academic_year_id($reset = FALSE)
    {
        static $cached_year_id = NULL;
        if ($reset) {
            $cached_year_id = NULL;
        }
        if ($cached_year_id !== NULL) {
            return $cached_year_id;
        }

        $active_id = get_active_academic_year_id(TRUE);
        if ($active_id) {
            $cached_year_id = (int)$active_id;
            return $cached_year_id;
        }

        return 1; // Default fallback if no years configured
    }
}

/**
 * Canonical function: Get the database-active academic year for active school.
 * Never assumes first array element or newest year is active unless explicitly requested as fallback.
 *
 * @param  bool     $fallback_to_latest
 * @param  bool     $reset
 * @param  int|null $school_id
 * @return object|null
 */
if ( ! function_exists('get_active_academic_year'))
{
    function get_active_academic_year($fallback_to_latest = FALSE, $reset = FALSE, $school_id = NULL)
    {
        static $cached_active = NULL;
        if ($reset) {
            $cached_active = NULL;
        }
        if ($cached_active !== NULL && !$fallback_to_latest && $school_id === NULL) {
            return $cached_active ?: NULL;
        }

        $CI =& get_instance();
        if (!isset($CI->Academic_year_model)) {
            $CI->load->model('Academic_year_model');
        }

        $sid = $school_id !== NULL ? (int)$school_id : get_current_school_id();
        $active = $CI->Academic_year_model->get_strictly_active_year($sid);
        if (!$active && $fallback_to_latest) {
            $active = $CI->Academic_year_model->get_active_year($sid);
        }

        if (!$fallback_to_latest && $school_id === NULL) {
            $cached_active = $active ?: FALSE;
        }

        return $active ?: NULL;
    }
}

/**
 * Canonical function: Get the database-active academic year ID (integer).
 *
 * @param  bool $fallback_to_latest
 * @return int|null
 */
if ( ! function_exists('get_active_academic_year_id'))
{
    function get_active_academic_year_id($fallback_to_latest = FALSE, $school_id = NULL)
    {
        $active = get_active_academic_year($fallback_to_latest, FALSE, $school_id);
        return $active ? (int)$active->academic_year_id : NULL;
    }
}

/**
 * Check academic year active status and edge cases:
 * - Exactly 1 active year
 * - 0 active years (anomaly)
 * - Multiple active years (anomaly)
 *
 * @return array ['has_active' => bool, 'is_multiple' => bool, 'no_active' => bool, 'active_year' => object|null, 'count' => int]
 */
if ( ! function_exists('get_active_academic_years_status'))
{
    function get_active_academic_years_status()
    {
        $CI =& get_instance();
        if (!isset($CI->Academic_year_model)) {
            $CI->load->model('Academic_year_model');
        }

        $all_active = $CI->Academic_year_model->get_all_active_years();
        $count = count($all_active);

        return [
            'has_active'  => ($count === 1),
            'is_multiple' => ($count > 1),
            'no_active'   => ($count === 0),
            'active_year' => ($count > 0) ? $all_active[0] : NULL,
            'count'       => $count,
        ];
    }
}

/**
 * Get the full record object for the currently active / selected academic year.
 */
if ( ! function_exists('get_current_academic_year'))
{
    function get_current_academic_year()
    {
        static $cached_year = NULL;
        if ($cached_year !== NULL) {
            return $cached_year;
        }

        $CI =& get_instance();
        if (!isset($CI->Academic_year_model)) {
            $CI->load->model('Academic_year_model');
        }

        $year_id = get_current_academic_year_id();
        $year = $CI->Academic_year_model->get_by_id($year_id);
        if (!$year) {
            $year = $CI->Academic_year_model->get_active_year();
        }

        $cached_year = $year;
        return $cached_year;
    }
}

/**
 * Get the full record object for a specific or current academic year.
 *
 * @param  int|null $academic_year_id
 * @return object|null
 */
if ( ! function_exists('get_academic_year_record'))
{
    function get_academic_year_record($academic_year_id = NULL)
    {
        $CI =& get_instance();
        if (!isset($CI->Academic_year_model)) {
            $CI->load->model('Academic_year_model');
        }

        if ($academic_year_id !== NULL && (int)$academic_year_id > 0) {
            return $CI->Academic_year_model->get_by_id((int)$academic_year_id);
        }

        return get_current_academic_year();
    }
}

/**
 * Get the default / synchronized date for an academic year.
 * Rule:
 *   If today's server date falls within [start_date, end_date] of the academic year:
 *       return today's date (Y-m-d)
 *   Else:
 *       return academic_year_start_date (Y-m-d)
 *
 * @param  int|object|null $academic_year
 * @return string (Y-m-d)
 */
if ( ! function_exists('get_academic_year_default_date'))
{
    function get_academic_year_default_date($academic_year = NULL)
    {
        $today = date('Y-m-d');
        $year_obj = is_object($academic_year) ? $academic_year : get_academic_year_record($academic_year);

        if (!$year_obj || empty($year_obj->start_date) || empty($year_obj->end_date)) {
            return $today;
        }

        $start_date = $year_obj->start_date;
        $end_date   = $year_obj->end_date;

        if ($today >= $start_date && $today <= $end_date) {
            return $today;
        }

        return $start_date;
    }
}

/**
 * Check if a given date falls within the start_date and end_date of an academic year.
 *
 * @param  string          $date (Y-m-d or parseable string)
 * @param  int|object|null $academic_year
 * @return bool
 */
if ( ! function_exists('is_date_within_academic_year'))
{
    function is_date_within_academic_year($date, $academic_year = NULL)
    {
        if (empty($date)) {
            return FALSE;
        }

        $formatted_date = date('Y-m-d', strtotime($date));
        if ($formatted_date === '1970-01-01' && $date !== '1970-01-01') {
            return FALSE;
        }

        $year_obj = is_object($academic_year) ? $academic_year : get_academic_year_record($academic_year);
        if (!$year_obj || empty($year_obj->start_date) || empty($year_obj->end_date)) {
            return TRUE;
        }

        return ($formatted_date >= $year_obj->start_date && $formatted_date <= $year_obj->end_date);
    }
}

/**
 * Normalize and validate a date against an academic year.
 * Rule:
 *   If user selected a date and it falls within [start_date, end_date], keep it.
 *   If user selected a date and it falls OUTSIDE [start_date, end_date], or date is empty/invalid,
 *   safely normalize to the academic year's default date (today if in-range, else start_date).
 *
 * @param  string|null     $date
 * @param  int|object|null $academic_year
 * @return string (Y-m-d)
 */
if ( ! function_exists('normalize_date_to_academic_year'))
{
    function normalize_date_to_academic_year($date = NULL, $academic_year = NULL)
    {
        $year_obj = is_object($academic_year) ? $academic_year : get_academic_year_record($academic_year);
        $default_date = get_academic_year_default_date($year_obj);

        if (empty($date)) {
            return $default_date;
        }

        $formatted_date = date('Y-m-d', strtotime($date));
        if ($formatted_date === '1970-01-01' && $date !== '1970-01-01') {
            return $default_date;
        }

        if (!$year_obj || empty($year_obj->start_date) || empty($year_obj->end_date)) {
            return $formatted_date;
        }

        if ($formatted_date >= $year_obj->start_date && $formatted_date <= $year_obj->end_date) {
            return $formatted_date;
        }

        return $default_date;
    }
}

/**
 * Set the currently selected academic year in session context.
 *
 * @param  int $academic_year_id
 * @return bool
 */
if ( ! function_exists('set_current_academic_year'))
{
    function set_current_academic_year($academic_year_id)
    {
        $CI =& get_instance();
        if (!isset($CI->Academic_year_model)) {
            $CI->load->model('Academic_year_model');
        }

        $year = $CI->Academic_year_model->get_by_id((int)$academic_year_id);
        if (!$year || $year->is_deleted === 'y' || (int)$year->status !== 1) {
            return FALSE;
        }

        $CI->session->set_userdata('selected_academic_year_id', (int)$year->academic_year_id);
        $CI->session->set_userdata('academic_year_id', (int)$year->academic_year_id);
        return TRUE;
    }
}

/**
 * Retrieve all available (active & non-deleted) academic years for dropdown selector.
 * Scoped to active school context or specified school_id.
 *
 * @param  bool     $reset
 * @param  int|null $school_id
 * @return array
 */
if ( ! function_exists('get_available_academic_years'))
{
    function get_available_academic_years($reset = FALSE, $school_id = NULL)
    {
        static $cached_years = [];
        $sid = $school_id !== NULL ? (int)$school_id : get_current_school_id();
        if ($reset) {
            $cached_years = [];
        }
        if (isset($cached_years[$sid])) {
            return $cached_years[$sid];
        }

        $CI =& get_instance();
        if (!isset($CI->Academic_year_model)) {
            $CI->load->model('Academic_year_model');
        }
        $years = $CI->Academic_year_model->get_available_years($sid);
        $cached_years[$sid] = $years;
        return $years;
    }
}

/**
 * Clear in-memory static caches for academic year resolution.
 */
if ( ! function_exists('clear_academic_year_cache'))
{
    function clear_academic_year_cache()
    {
        get_active_academic_year(FALSE, TRUE);
        get_current_academic_year_id(TRUE);
        get_available_academic_years(TRUE);

        $CI =& get_instance();
        if (isset($CI->Academic_year_model)) {
            $CI->Academic_year_model->clear_cache();
        }
    }
}

/**
 * Check if the user has permission to change the active global academic year.
 * Only Super Admin users are authorized to change the school's global active year.
 *
 * @param  int|null $user_id
 * @return bool
 */
if ( ! function_exists('can_change_academic_year'))
{
    function can_change_academic_year($user_id = NULL)
    {
        $CI =& get_instance();
        if (!isset($CI->rbac)) {
            $CI->load->library('Rbac');
        }

        return (bool)$CI->rbac->is_super_admin($user_id);
    }
}

// ---------------------------------------------------------------------------
// Student Attendance & Class Level helpers
// ---------------------------------------------------------------------------

/**
 * Determine whether a class is Higher Secondary (+1 or +2)
 * e.g. "Grade 11", "Grade 12", "+1", "+2", "Class 11", "Class 12", "Plus One", "Plus Two", "XI", "XII"
 * Returns FALSE for LKG, UKG, Class 1 through Class 10.
 */
/**
 * Get attendance workflow type for a class or academic group: 'daily' or 'period'.
 * Completely database-driven based on tbl_academic_groups.attendance_type.
 *
 * @param mixed $class Class object, array, class_id, or academic_group_id
 * @return string 'daily' or 'period'
 */
if ( ! function_exists('get_attendance_workflow_type'))
{
    function get_attendance_workflow_type($class)
    {
        if (empty($class)) {
            return 'daily';
        }

        static $workflow_cache = array();

        // 1. Direct attendance_type property check
        if (is_object($class) && !empty($class->attendance_type)) {
            return strtolower(trim($class->attendance_type));
        }
        if (is_array($class) && !empty($class['attendance_type'])) {
            return strtolower(trim($class['attendance_type']));
        }

        // 2. Direct academic_group_id check
        $group_id = NULL;
        if (is_object($class) && !empty($class->academic_group_id)) {
            $group_id = (int)$class->academic_group_id;
        } elseif (is_array($class) && !empty($class['academic_group_id'])) {
            $group_id = (int)$class['academic_group_id'];
        }

        if ($group_id) {
            $cache_key = 'group_' . $group_id;
            if (isset($workflow_cache[$cache_key])) {
                return $workflow_cache[$cache_key];
            }
            if (function_exists('get_instance')) {
                $CI = &get_instance();
                if ($CI && isset($CI->db)) {
                    $row = $CI->db->select('attendance_type')
                        ->from('tbl_academic_groups')
                        ->where('academic_group_id', $group_id)
                        ->where('is_deleted', 'n')
                        ->get()
                        ->row();
                    $type = ($row && !empty($row->attendance_type)) ? strtolower(trim($row->attendance_type)) : 'daily';
                    $workflow_cache[$cache_key] = $type;
                    return $type;
                }
            }
        }

        // 3. Resolve by class_id
        $cid = NULL;
        if (is_object($class) && !empty($class->class_id)) {
            $cid = (int)$class->class_id;
        } elseif (is_array($class) && !empty($class['class_id'])) {
            $cid = (int)$class['class_id'];
        } elseif (is_int($class) || (is_string($class) && ctype_digit(trim($class)))) {
            $cid = (int)$class;
        }

        if ($cid) {
            $cache_key = 'class_' . $cid;
            if (isset($workflow_cache[$cache_key])) {
                return $workflow_cache[$cache_key];
            }
            if (function_exists('get_instance')) {
                $CI = &get_instance();
                if ($CI && isset($CI->db)) {
                    $row = $CI->db->select('ag.attendance_type')
                        ->from('tbl_classes c')
                        ->join('tbl_academic_groups ag', 'ag.academic_group_id = c.academic_group_id', 'inner')
                        ->where('c.class_id', $cid)
                        ->where('c.is_deleted', 'n')
                        ->where('ag.is_deleted', 'n')
                        ->get()
                        ->row();
                    $type = ($row && !empty($row->attendance_type)) ? strtolower(trim($row->attendance_type)) : 'daily';
                    $workflow_cache[$cache_key] = $type;
                    return $type;
                }
            }
        }

        // 4. Resolve by class_name
        $cname = NULL;
        if (is_object($class) && !empty($class->class_name)) {
            $cname = trim($class->class_name);
        } elseif (is_array($class) && !empty($class['class_name'])) {
            $cname = trim($class['class_name']);
        } elseif (is_string($class) && trim($class) !== '') {
            $cname = trim($class);
        }

        if ($cname) {
            $school_id = function_exists('get_school_id') ? get_school_id() : 0;
            $cache_key = 'name_' . $school_id . '_' . strtolower($cname);
            if (isset($workflow_cache[$cache_key])) {
                return $workflow_cache[$cache_key];
            }
            if (function_exists('get_instance')) {
                $CI = &get_instance();
                if ($CI && isset($CI->db)) {
                    $CI->db->select('ag.attendance_type')
                        ->from('tbl_classes c')
                        ->join('tbl_academic_groups ag', 'ag.academic_group_id = c.academic_group_id', 'inner')
                        ->where('c.class_name', $cname)
                        ->where('c.is_deleted', 'n')
                        ->where('ag.is_deleted', 'n');
                    if (!empty($school_id)) {
                        $CI->db->where('c.school_id', $school_id);
                    }
                    $row = $CI->db->get()->row();
                    $type = ($row && !empty($row->attendance_type)) ? strtolower(trim($row->attendance_type)) : 'daily';
                    $workflow_cache[$cache_key] = $type;
                    return $type;
                }
            }
        }

        return 'daily';
    }
}

/**
 * Backward compatibility alias: returns true if class uses period attendance, false if daily.
 * Driven strictly by database attendance_type.
 */
if ( ! function_exists('is_higher_secondary_class'))
{
    function is_higher_secondary_class($class)
    {
        return (get_attendance_workflow_type($class) === 'period');
    }
}

/**
 * Get allowed attendance statuses based on class workflow
 */
if ( ! function_exists('get_allowed_attendance_statuses'))
{
    function get_allowed_attendance_statuses($class = NULL)
    {
        if ($class !== NULL && get_attendance_workflow_type($class) === 'period') {
            // Period attendance: Present, Half Day, Absent, Late Coming
            return array('Present', 'Half Day', 'Absent', 'Late Coming');
        }
        // Daily attendance: Present, Half Day, Absent
        return array('Present', 'Half Day', 'Absent');
    }
}

// ---------------------------------------------------------------------------
// Student Document Repository Helpers
// ---------------------------------------------------------------------------

/**
 * Returns allowed file extensions for Student Document Repository uploads.
 * Allowed formats: PDF, PNG, JPG/JPEG, DOC/DOCX
 */
if ( ! function_exists('student_document_allowed_extensions'))
{
    function student_document_allowed_extensions()
    {
        return array('pdf', 'png', 'jpg', 'jpeg', 'doc', 'docx');
    }
}

/**
 * Returns allowed MIME types for Student Document Repository uploads.
 */
if ( ! function_exists('student_document_allowed_mimes'))
{
    function student_document_allowed_mimes()
    {
        return array(
            'application/pdf',
            'application/x-pdf',
            'image/png',
            'image/x-png',
            'image/jpeg',
            'image/pjpeg',
            'image/jpg',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip',
            'application/octet-stream',
            'application/vnd.ms-office',
            'application/cdfv2',
            'application/x-ole-storage'
        );
    }
}

/**
 * Returns HTML accept attribute string for Student Document Repository file inputs.
 */
if ( ! function_exists('student_document_accept_attribute'))
{
    function student_document_accept_attribute()
    {
        return '.pdf,.png,.jpg,.jpeg,.doc,.docx';
    }
}

/**
 * Returns user-friendly instructional notice for allowed student document formats.
 */
if ( ! function_exists('student_document_format_notice'))
{
    function student_document_format_notice()
    {
        return 'Allowed formats: PDF, PNG, JPG/JPEG, DOC/DOCX';
    }
}

/**
 * Validates an uploaded student document file.
 * Checks:
 * 1. File existence & PHP upload error status.
 * 2. File extension is strictly within the allowed list.
 * 3. Server-side MIME type detection using finfo (prevents renamed malicious binaries).
 * 
 * @param array $file $_FILES['document_file']
 * @return array ['valid' => bool, 'error' => string|null, 'ext' => string, 'mime' => string]
 */
if ( ! function_exists('validate_student_document_file'))
{
    function validate_student_document_file($file)
    {
        if (empty($file) || !isset($file['error'])) {
            return array('valid' => false, 'error' => 'No file was selected for upload.');
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            if ($file['error'] === UPLOAD_ERR_NO_FILE) {
                return array('valid' => false, 'error' => 'Please select a file to upload.');
            }
            if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
                return array('valid' => false, 'error' => 'File exceeds the maximum upload size limit.');
            }
            return array('valid' => false, 'error' => 'File upload failed with error code ' . $file['error'] . '.');
        }

        $origName = isset($file['name']) ? $file['name'] : '';
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $allowedExts = student_document_allowed_extensions();

        if (!in_array($ext, $allowedExts, true)) {
            return array('valid' => false, 'error' => 'Unsupported file format. Please upload PDF, PNG, JPG/JPEG, DOC, or DOCX.');
        }

        $tmpFile = isset($file['tmp_name']) ? $file['tmp_name'] : '';
        if (empty($tmpFile) || !file_exists($tmpFile)) {
            return array('valid' => false, 'error' => 'Invalid upload request.');
        }

        // Deep server-side MIME detection
        $detectedMime = '';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $detectedMime = strtolower((string)finfo_file($finfo, $tmpFile));
                finfo_close($finfo);
            }
        }
        if (empty($detectedMime) && function_exists('mime_content_type')) {
            $detectedMime = strtolower((string)mime_content_type($tmpFile));
        }

        // Specifically guard against executable, script, or server files disguised with allowed extension
        $disallowedMimes = array(
            'application/x-dosexec',
            'application/x-executable',
            'application/x-msdownload',
            'application/x-sh',
            'application/x-batch',
            'text/x-php',
            'application/x-php',
            'text/html',
            'text/javascript',
            'application/javascript',
            'application/x-javascript'
        );

        if (in_array($detectedMime, $disallowedMimes, true)) {
            return array('valid' => false, 'error' => 'Unsupported file format. Please upload PDF, PNG, JPG/JPEG, DOC, or DOCX.');
        }

        // Strict extension to detected MIME correlation
        $mimeMatch = false;
        switch ($ext) {
            case 'pdf':
                $mimeMatch = in_array($detectedMime, array('application/pdf', 'application/x-pdf'), true);
                break;
            case 'png':
                $mimeMatch = in_array($detectedMime, array('image/png', 'image/x-png'), true);
                break;
            case 'jpg':
            case 'jpeg':
                $mimeMatch = in_array($detectedMime, array('image/jpeg', 'image/pjpeg', 'image/jpg'), true);
                break;
            case 'doc':
                $mimeMatch = in_array($detectedMime, array(
                    'application/msword',
                    'application/octet-stream',
                    'application/vnd.ms-office',
                    'application/cdfv2',
                    'application/x-ole-storage'
                ), true);
                break;
            case 'docx':
                $mimeMatch = in_array($detectedMime, array(
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/zip',
                    'application/octet-stream',
                    'application/msword'
                ), true);
                break;
        }

        if (!$mimeMatch) {
            return array('valid' => false, 'error' => 'Unsupported file format. Please upload PDF, PNG, JPG/JPEG, DOC, or DOCX.');
        }

        return array('valid' => true, 'error' => null, 'ext' => $ext, 'mime' => $detectedMime);
    }
}

// ---------------------------------------------------------------------------
// Timezone & Date/Time Helpers (Canonical: Asia/Kolkata, UTC+05:30)
// ---------------------------------------------------------------------------

/**
 * Return the canonical application timezone.
 */
if ( ! function_exists('school_timezone'))
{
    function school_timezone()
    {
        return 'Asia/Kolkata';
    }
}

/**
 * Return current timestamp in Asia/Kolkata.
 */
if ( ! function_exists('school_now'))
{
    function school_now($format = 'Y-m-d H:i:s')
    {
        if (date_default_timezone_get() !== 'Asia/Kolkata') {
            date_default_timezone_set('Asia/Kolkata');
        }
        return date($format);
    }
}

/**
 * Return current date in Asia/Kolkata (guarantees IST calendar day boundary).
 */
if ( ! function_exists('school_today'))
{
    function school_today($format = 'Y-m-d')
    {
        if (date_default_timezone_get() !== 'Asia/Kolkata') {
            date_default_timezone_set('Asia/Kolkata');
        }
        return date($format);
    }
}

/**
 * Safely format an actual DATETIME/TIMESTAMP into Asia/Kolkata representation.
 * If the input is null or empty, returns a fallback (default '—').
 */
if ( ! function_exists('school_format_datetime'))
{
    function school_format_datetime($datetime, $format = 'd M Y, h:i A', $fallback = '—')
    {
        if (empty($datetime) || $datetime === '0000-00-00 00:00:00') {
            return $fallback;
        }
        try {
            $tz = new DateTimeZone('Asia/Kolkata');
            if ($datetime instanceof DateTimeInterface) {
                $dt = clone $datetime;
                $dt->setTimezone($tz);
            } else {
                $dt = new DateTime($datetime, $tz);
            }
            return $dt->format($format);
        } catch (Exception $e) {
            return $fallback;
        }
    }
}

/**
 * Format a DATE-ONLY calendar value (e.g. DOB, Academic Year, Holiday, Exam date).
 * IMPORTANT: Strictly does NOT perform any timezone shift or hour adjustments.
 */
if ( ! function_exists('school_format_date'))
{
    function school_format_date($date, $format = 'd M Y', $fallback = '—')
    {
        if (empty($date) || $date === '0000-00-00') {
            return $fallback;
        }
        // Extract only Y-m-d portion to prevent any time/offset shifts
        $date_part = substr((string)$date, 0, 10);
        $parts = explode('-', $date_part);
        if (count($parts) === 3 && checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0])) {
            $dt = DateTime::createFromFormat('!Y-m-d', $date_part);
            return $dt ? $dt->format($format) : $date_part;
        }
        return (string)$date;
    }
}

/**
 * Validate binary header and MIME signature for uploaded TC documents (PDF, JPG, JPEG, PNG).
 *
 * @param  string $tmp_path
 * @param  string $ext
 * @return array ['valid' => bool, 'error' => string|null]
 */
if ( ! function_exists('validate_uploaded_tc_file'))
{
    function validate_uploaded_tc_file($tmp_path, $ext)
    {
        $ext = strtolower(trim((string)$ext));
        if ($ext === 'jpeg') {
            $ext = 'jpg';
        }
        $allowed = array('pdf', 'jpg', 'png');
        if (!in_array($ext, $allowed, TRUE)) {
            return array('valid' => FALSE, 'error' => 'TC Document must be a PDF, JPG, JPEG, or PNG file.');
        }

        if (empty($tmp_path) || !file_exists($tmp_path)) {
            return array('valid' => FALSE, 'error' => 'Uploaded temporary file not found.');
        }

        if ($ext === 'pdf') {
            $fh = @fopen($tmp_path, 'rb');
            $header = $fh ? @fread($fh, 5) : '';
            if ($fh) { @fclose($fh); }
            if (strpos($header, '%PDF-') !== 0) {
                return array('valid' => FALSE, 'error' => 'TC Document must be a valid PDF file.');
            }
        } else {
            $img_info = @getimagesize($tmp_path);
            if ($img_info === FALSE || !in_array($img_info[2], array(IMAGETYPE_JPEG, IMAGETYPE_PNG), TRUE)) {
                return array('valid' => FALSE, 'error' => 'TC Document must be a valid PDF, JPG, JPEG, or PNG file.');
            }
        }

        return array('valid' => TRUE, 'error' => NULL);
    }
}


