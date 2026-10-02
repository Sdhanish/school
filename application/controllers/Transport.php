<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transport extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Transport_model');
        $this->load->model('Vehicle_model');
        $this->load->model('Driver_model');
        $this->load->model('Route_model');
        $this->load->model('Stop_model');
        $this->load->model('Transport_assignment_model');
        $this->load->model('Vehicle_maintenance_model');
        $this->load->model('Transport_document_model');
        $this->load->model('Transport_setting_model');
        $this->load->model('Class_model');
        $this->load->model('Division_model');
        $this->load->model('Section_model');
        $this->load->model('Student_model');
    }

    // 1. Transport Dashboard
    public function index()
    {
        $this->dashboard();
    }

    public function dashboard()
    {
        $this->require_permission('transport.view');
        $data['title'] = 'Transport Dashboard';
        $data['stats'] = $this->Transport_model->get_dashboard_stats();
        $data['vehicles'] = $this->Vehicle_model->get_all();
        $data['recent_maintenance'] = $this->Vehicle_maintenance_model->get_all();
        $data['expiring_docs'] = $this->Transport_document_model->get_all();

        $this->render('pages/transport/dashboard', $data);
    }

    // 2. Vehicles Directory
    public function vehicles()
    {
        $this->require_permission('transport.view');
        if ($this->input->post()) {
            $this->require_permission('transport.manage');
            $vehicle_id = (int)$this->input->post('vehicle_id');
            $action = $this->input->post('action');

            if ($action === 'delete') {
                $this->Vehicle_model->delete($vehicle_id);
                $this->session->set_flashdata('success', 'Vehicle record removed.');
            } else {
                $postData = [
                    'vehicle_number'      => trim($this->input->post('vehicle_number')),
                    'registration_number' => strtoupper(trim($this->input->post('registration_number'))),
                    'vehicle_type'        => $this->input->post('vehicle_type') ?: 'School Bus',
                    'manufacturer'        => trim($this->input->post('manufacturer')),
                    'model'               => trim($this->input->post('model')),
                    'manufacturing_year'  => $this->input->post('manufacturing_year') ?: date('Y'),
                    'seating_capacity'    => (int)$this->input->post('seating_capacity') ?: 40,
                    'vehicle_color'       => trim($this->input->post('vehicle_color')) ?: 'School Yellow',
                    'registration_date'   => $this->input->post('registration_date') ?: NULL,
                    'registration_expiry' => $this->input->post('registration_expiry') ?: NULL,
                    'insurance_number'    => trim($this->input->post('insurance_number')),
                    'insurance_expiry'    => $this->input->post('insurance_expiry') ?: NULL,
                    'fitness_number'      => trim($this->input->post('fitness_number')),
                    'fitness_expiry'      => $this->input->post('fitness_expiry') ?: NULL,
                    'pollution_number'    => trim($this->input->post('pollution_number')),
                    'pollution_expiry'    => $this->input->post('pollution_expiry') ?: NULL,
                    'permit_number'       => trim($this->input->post('permit_number')),
                    'permit_expiry'       => $this->input->post('permit_expiry') ?: NULL,
                    'assigned_driver_id'  => $this->input->post('assigned_driver_id') ? (int)$this->input->post('assigned_driver_id') : NULL,
                    'assigned_route_id'   => $this->input->post('assigned_route_id') ? (int)$this->input->post('assigned_route_id') : NULL,
                    'status'              => $this->input->post('status') ?: 'Active'
                ];

                if ($vehicle_id > 0) {
                    $this->Vehicle_model->update($vehicle_id, $postData);
                    $this->session->set_flashdata('success', 'Vehicle updated successfully.');
                } else {
                    $this->Vehicle_model->insert($postData);
                    $this->session->set_flashdata('success', 'New vehicle added to fleet.');
                }
            }
            redirect('transport/vehicles');
            return;
        }

        $data['title'] = 'Vehicle Management';
        $data['vehicles'] = $this->Vehicle_model->get_all();
        $data['drivers'] = $this->Driver_model->get_all(TRUE);
        $data['routes'] = $this->Route_model->get_all(TRUE);

        $this->render('pages/transport/vehicles', $data);
    }

    // 3. Vehicle Details Profile
    public function vehicle_details($id)
    {
        $this->require_permission('transport.view');
        $vehicle = $this->Vehicle_model->get_by_id($id);
        if (!$vehicle) {
            $this->session->set_flashdata('error', 'Vehicle not found.');
            redirect('transport/vehicles');
            return;
        }

        $data['title'] = 'Vehicle Profile: ' . $vehicle->vehicle_number;
        $data['vehicle'] = $vehicle;
        $data['students'] = $this->Transport_assignment_model->get_all(['vehicle_id' => $id, 'status' => 'Active']);
        $data['maintenance'] = $this->Vehicle_maintenance_model->get_all($id);
        $data['total_maintenance_cost'] = $this->Vehicle_maintenance_model->get_total_cost($id);
        $data['documents'] = $this->Transport_document_model->get_all('Vehicle', $id);

        $this->render('pages/transport/vehicle_details', $data);
    }

    // 4. Drivers Directory
    public function drivers()
    {
        $this->require_permission('transport.view');
        if ($this->input->post()) {
            $this->require_permission('transport.manage');
            $driver_id = (int)$this->input->post('driver_id');
            $action = $this->input->post('action');

            if ($action === 'delete') {
                $this->Driver_model->delete($driver_id);
                $this->session->set_flashdata('success', 'Driver record deleted.');
            } else {
                $postData = [
                    'driver_name'         => trim($this->input->post('driver_name')),
                    'phone'               => trim($this->input->post('phone')),
                    'alternate_phone'     => trim($this->input->post('alternate_phone')),
                    'address'             => trim($this->input->post('address')),
                    'license_number'      => strtoupper(trim($this->input->post('license_number'))),
                    'license_type'        => $this->input->post('license_type') ?: 'Heavy Commercial Vehicle (HCV)',
                    'license_issue_date'  => $this->input->post('license_issue_date') ?: NULL,
                    'license_expiry_date' => $this->input->post('license_expiry_date'),
                    'experience_years'    => (int)$this->input->post('experience_years') ?: 5,
                    'assigned_vehicle_id' => $this->input->post('assigned_vehicle_id') ? (int)$this->input->post('assigned_vehicle_id') : NULL,
                    'status'              => $this->input->post('status') ?: 'Active'
                ];

                if ($driver_id > 0) {
                    $this->Driver_model->update($driver_id, $postData);
                    $this->session->set_flashdata('success', 'Driver record updated.');
                } else {
                    $this->Driver_model->insert($postData);
                    $this->session->set_flashdata('success', 'Driver registered successfully.');
                }
            }
            redirect('transport/drivers');
            return;
        }

        $data['title'] = 'Driver Management';
        $data['drivers'] = $this->Driver_model->get_all();
        $data['vehicles'] = $this->Vehicle_model->get_all(TRUE);

        $this->render('pages/transport/drivers', $data);
    }

    // 5. Driver Details Profile
    public function driver_details($id)
    {
        $this->require_permission('transport.view');
        $driver = $this->Driver_model->get_by_id($id);
        if (!$driver) {
            $this->session->set_flashdata('error', 'Driver not found.');
            redirect('transport/drivers');
            return;
        }

        $data['title'] = 'Driver Profile: ' . $driver->driver_name;
        $data['driver'] = $driver;
        $data['documents'] = $this->Transport_document_model->get_all('Driver', $id);

        $this->render('pages/transport/driver_details', $data);
    }

    // 6. Routes Directory
    public function routes()
    {
        $this->require_permission('transport.view');
        if ($this->input->post()) {
            $this->require_permission('transport.manage');
            $route_id = (int)$this->input->post('route_id');
            $action = $this->input->post('action');

            if ($action === 'delete') {
                $this->Route_model->delete($route_id);
                $this->session->set_flashdata('success', 'Route deleted.');
            } else {
                $postData = [
                    'route_name'                => trim($this->input->post('route_name')),
                    'route_code'                => strtoupper(trim($this->input->post('route_code'))),
                    'description'               => trim($this->input->post('description')),
                    'start_point'               => trim($this->input->post('start_point')),
                    'end_point'                 => trim($this->input->post('end_point')),
                    'estimated_distance_km'     => (float)$this->input->post('estimated_distance_km'),
                    'estimated_travel_time_min' => (int)$this->input->post('estimated_travel_time_min'),
                    'assigned_vehicle_id'       => $this->input->post('assigned_vehicle_id') ? (int)$this->input->post('assigned_vehicle_id') : NULL,
                    'assigned_driver_id'        => $this->input->post('assigned_driver_id') ? (int)$this->input->post('assigned_driver_id') : NULL,
                    'status'                    => $this->input->post('status') ?: 'Active'
                ];

                if ($route_id > 0) {
                    $this->Route_model->update($route_id, $postData);
                    $this->session->set_flashdata('success', 'Route updated successfully.');
                } else {
                    $this->Route_model->insert($postData);
                    $this->session->set_flashdata('success', 'New route created.');
                }
            }
            redirect('transport/routes');
            return;
        }

        $data['title'] = 'Route Management';
        $data['routes'] = $this->Route_model->get_all();
        $data['vehicles'] = $this->Vehicle_model->get_all(TRUE);
        $data['drivers'] = $this->Driver_model->get_all(TRUE);

        $this->render('pages/transport/routes', $data);
    }

    // 7. Route Details & Passenger Manifest
    public function route_details($id)
    {
        $this->require_permission('transport.view');
        $route = $this->Route_model->get_by_id($id);
        if (!$route) {
            $this->session->set_flashdata('error', 'Route not found.');
            redirect('transport/routes');
            return;
        }

        $data['title'] = 'Route Profile: ' . $route->route_name;
        $data['route'] = $route;
        $data['stops'] = $this->Stop_model->get_all_by_route($id);
        $data['students'] = $this->Transport_assignment_model->get_all(['route_id' => $id, 'status' => 'Active']);

        $this->render('pages/transport/route_details', $data);
    }

    // 8. Stops Management
    public function stops()
    {
        $this->require_permission('transport.view');
        $route_id = $this->input->get('route_id') ?: NULL;

        if ($this->input->post()) {
            $this->require_permission('transport.manage');
            $stop_id = (int)$this->input->post('stop_id');
            $action = $this->input->post('action');

            if ($action === 'delete') {
                $this->Stop_model->delete($stop_id);
                $this->session->set_flashdata('success', 'Stop removed.');
            } else {
                $postData = [
                    'route_id'       => (int)$this->input->post('route_id'),
                    'stop_name'      => trim($this->input->post('stop_name')),
                    'stop_code'      => strtoupper(trim($this->input->post('stop_code'))),
                    'sequence_order' => (int)$this->input->post('sequence_order') ?: 1,
                    'pickup_time'    => $this->input->post('pickup_time') ?: '07:30:00',
                    'drop_time'      => $this->input->post('drop_time') ?: '15:30:00',
                    'landmark'       => trim($this->input->post('landmark')),
                    'distance_km'    => (float)$this->input->post('distance_km'),
                    'fare_amount'    => (float)$this->input->post('fare_amount') ?: 1500.00,
                    'status'         => 1
                ];

                if ($stop_id > 0) {
                    $this->Stop_model->update($stop_id, $postData);
                    $this->session->set_flashdata('success', 'Stop updated.');
                } else {
                    $this->Stop_model->insert($postData);
                    $this->session->set_flashdata('success', 'New stop added to route.');
                }
            }
            redirect('transport/stops' . ($route_id ? '?route_id=' . $route_id : ''));
            return;
        }

        $data['title'] = 'Stop Management';
        $data['route_id'] = $route_id;
        $data['routes'] = $this->Route_model->get_all();
        $data['stops'] = $this->Stop_model->get_all_by_route($route_id);

        $this->render('pages/transport/stops', $data);
    }

    // 9. Student Transport Assignment & Allocation
    public function assignments()
    {
        $this->require_permission('transport.view');
        if ($this->input->post()) {
            $this->require_permission('transport.manage');
            $student_id = (int)$this->input->post('student_id');
            $route_id = (int)$this->input->post('route_id');
            $vehicle_id = (int)$this->input->post('vehicle_id');
            $pickup_stop_id = (int)$this->input->post('pickup_stop_id');
            $drop_stop_id = (int)$this->input->post('drop_stop_id') ?: $pickup_stop_id;
            $transport_type = $this->input->post('transport_type') ?: 'Two Way';
            $monthly_fee = (float)$this->input->post('monthly_fee') ?: 1500.00;

            // Capacity Check
            if (!$this->Transport_assignment_model->validate_capacity($vehicle_id, 1)) {
                $this->session->set_flashdata('error', 'Selected vehicle has reached its maximum seating capacity.');
                redirect('transport/assignments');
                return;
            }

            $st = $this->Student_model->get_by_id($student_id);
            $assignData = [
                'academic_year_id' => $this->academic_year_id,
                'student_id'       => $student_id,
                'class_id'         => $st ? $st->class_id : NULL,
                'division_id'       => $st ? $st->section_id : NULL,
                'route_id'         => $route_id,
                'pickup_stop_id'   => $pickup_stop_id,
                'drop_stop_id'     => $drop_stop_id,
                'vehicle_id'       => $vehicle_id,
                'transport_type'   => $transport_type,
                'monthly_fee'      => $monthly_fee,
                'status'           => 'Active'
            ];

            $this->Transport_assignment_model->assign_student($assignData);
            $this->session->set_flashdata('success', 'Student transport allocated successfully.');
            redirect('transport/assignments');
            return;
        }

        $year_id = $this->input->get('academic_year_id') ?: $this->academic_year_id;
        $filters = [
            'academic_year_id' => $year_id,
            'route_id'         => $this->input->get('route_id') ?: NULL,
            'class_id'         => $this->input->get('class_id') ?: NULL,
            'division_id'       => $this->input->get('division_id') ?: NULL,
            'status'           => $this->input->get('status') ?: 'Active',
            'search'           => $this->input->get('search') ?: NULL,
        ];

        $data['title'] = 'Student Transport Assignments';
        $data['filters'] = $filters;
        $data['routes'] = $this->Route_model->get_all(TRUE);
        $data['vehicles'] = $this->Vehicle_model->get_all(TRUE);
        $data['stops'] = $this->Stop_model->get_all_by_route();
        $data['classes'] = $this->Class_model->get_all($year_id);
        $data['students'] = $this->Student_model->get_all(['academic_year_id' => $year_id, 'status' => 1], 60);

        $this->render('pages/transport/assignments', $data);
    }

    public function ajax_assignments_list()
    {
        $this->require_permission('transport.view');

        $draw = (int)$this->input->post('draw');
        $start = (int)$this->input->post('start');
        $length = (int)$this->input->post('length');
        if ($length <= 0) {
            $length = 10;
        }

        $order = $this->input->post('order');
        $order_col = isset($order[0]['column']) ? (int)$order[0]['column'] : 0;
        $order_dir = isset($order[0]['dir']) ? $order[0]['dir'] : 'desc';

        $search_val = $this->input->post('search');
        $search = isset($search_val['value']) ? trim($search_val['value']) : '';

        $filters = [
            'academic_year_id' => $this->input->post('academic_year_id') ?: $this->academic_year_id,
            'route_id'         => $this->input->post('route_id') ?: NULL,
            'class_id'         => $this->input->post('class_id') ?: NULL,
            'status'           => $this->input->post('status') ?: NULL,
            'search'           => $search
        ];

        $total_records = $this->Transport_assignment_model->count_all($this->school_id, $filters['academic_year_id']);
        $filtered_records = $this->Transport_assignment_model->count_filtered($filters);
        $records = $this->Transport_assignment_model->get_datatables_data($filters, $start, $length, $order_col, $order_dir);

        $data = array();
        foreach ($records as $a) {
            $student_html = '<strong class="text-on-surface block">' . html_escape($a->first_name . ' ' . $a->last_name) . '</strong>' .
                '<span class="text-[11px] text-on-surface-variant font-mono">' . html_escape($a->admission_number ?? $a->admission_no ?? '') . '</span>';

            $class_sec = html_escape(trim(($a->class_name ?? '') . ($a->section_name ? ' - ' . $a->section_name : '')));

            $route_vehicle_html = '<strong class="block">' . html_escape($a->route_name ?? '') . '</strong>' .
                '<span class="text-on-surface-variant font-mono text-[11px]">' . html_escape($a->vehicle_number ?? '') . '</span>';

            $stop_time_html = '<span>' . html_escape($a->pickup_stop_name ?? '') . '</span>' .
                '<span class="block text-on-surface-variant font-mono text-[11px]">' . html_escape($a->pickup_time ?? '') . '</span>';

            $type_html = '<span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-surface-container-high text-on-surface-variant">' .
                html_escape($a->transport_type ?? '') . '</span>';

            $fee_html = '<div class="text-right font-mono font-bold text-on-surface">₹' . number_format((float)$a->monthly_fee, 2) . '</div>';

            $status_class = ($a->status === 'Active') ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container-high text-on-surface-variant';
            $status_html = '<div class="text-center"><span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold ' . $status_class . '">' . html_escape($a->status ?? '') . '</span></div>';

            if ($a->status === 'Active') {
                $action_html = '<div class="text-center"><a href="' . site_url('transport/remove_assignment/' . $a->assignment_id) . '" onclick="return confirm(\'Cancel this student transport assignment?\');" class="p-1 rounded hover:bg-error-container text-error font-semibold text-xs inline-flex items-center gap-1">Cancel</a></div>';
            } else {
                $action_html = '<div class="text-center"><span class="text-[11px] text-on-surface-variant font-mono">Inactive</span></div>';
            }

            $data[] = array(
                $student_html,
                $class_sec,
                $route_vehicle_html,
                $stop_time_html,
                $type_html,
                $fee_html,
                $status_html,
                $action_html
            );
        }

        echo json_encode(array(
            'draw' => $draw,
            'recordsTotal' => (int)$total_records,
            'recordsFiltered' => (int)$filtered_records,
            'data' => $data
        ));
        exit;
    }

    public function remove_assignment($id)
    {
        $this->remove_assignment_action($id);
    }

    public function remove_assignment_action($id)
    {
        $this->require_permission('transport.manage');
        $this->Transport_assignment_model->remove_assignment($id, 'Removed via transport console');
        $this->session->set_flashdata('success', 'Transport assignment cancelled.');
        redirect('transport/assignments');
    }

    // 10. Bulk Student Assignment
    public function bulk_assign()
    {
        $this->require_permission('transport.manage');
        if ($this->input->post()) {
            $student_ids = $this->input->post('student_ids') ?: [];
            $route_id = (int)$this->input->post('route_id');
            $vehicle_id = (int)$this->input->post('vehicle_id');
            $pickup_stop_id = (int)$this->input->post('pickup_stop_id');
            $monthly_fee = (float)$this->input->post('monthly_fee') ?: 1500.00;

            $count = count($student_ids);
            if ($count === 0) {
                $this->session->set_flashdata('error', 'Please select at least one student.');
                redirect('transport/bulk_assign');
                return;
            }

            if (!$this->Transport_assignment_model->validate_capacity($vehicle_id, $count)) {
                $this->session->set_flashdata('error', "Cannot assign {$count} students. Exceeds available vehicle capacity.");
                redirect('transport/bulk_assign');
                return;
            }

            $st_map = [];
            if (!empty($student_ids)) {
                $st_rows = $this->Student_model->get_basic_info_by_ids($student_ids);
                foreach ($st_rows as $sr) {
                    $st_map[$sr->student_id] = $sr;
                }
            }

            foreach ($student_ids as $sid) {
                $st = $st_map[$sid] ?? NULL;
                $this->Transport_assignment_model->assign_student([
                    'academic_year_id' => $this->academic_year_id,
                    'student_id'       => $sid,
                    'class_id'         => $st ? $st->class_id : NULL,
                    'division_id'       => $st ? $st->section_id : NULL,
                    'route_id'         => $route_id,
                    'pickup_stop_id'   => $pickup_stop_id,
                    'drop_stop_id'     => $pickup_stop_id,
                    'vehicle_id'       => $vehicle_id,
                    'transport_type'   => 'Two Way',
                    'monthly_fee'      => $monthly_fee,
                    'status'           => 'Active'
                ]);
            }

            $this->session->set_flashdata('success', "{$count} students assigned to route successfully.");
            redirect('transport/assignments');
            return;
        }

        $year_id = $this->academic_year_id;
        $class_id = $this->input->get('class_id') ?: NULL;
        $section_id = $this->input->get('division_id') ?: NULL;

        $data['title'] = 'Bulk Transport Assignment';
        $data['class_id'] = $class_id;
        $data['division_id'] = $section_id;
        $data['classes'] = $this->Class_model->get_all($year_id);
        $data['routes'] = $this->Route_model->get_all(TRUE);
        $data['vehicles'] = $this->Vehicle_model->get_all(TRUE);
        $data['stops'] = $this->Stop_model->get_all_by_route();
        $data['students'] = ($class_id) ? $this->Student_model->get_by_class_section($class_id, $section_id) : [];

        $this->render('pages/transport/bulk_assign', $data);
    }

    // 11. Transport Fees Configuration & Overview
    public function fees()
    {
        $this->require_permission('transport.view');
        $data['title'] = 'Transport Fees & Pricing';
        $data['routes'] = $this->Route_model->get_all();
        $data['stops'] = $this->Stop_model->get_all_by_route();
        $data['assignments'] = $this->Transport_assignment_model->get_all(['status' => 'Active']);

        $this->render('pages/transport/fees', $data);
    }

    // 12. Vehicle Maintenance Logs
    public function maintenance()
    {
        $this->require_permission('transport.view');
        if ($this->input->post()) {
            $this->require_permission('transport.manage');
            $postData = [
                'vehicle_id'        => (int)$this->input->post('vehicle_id'),
                'maintenance_type'  => $this->input->post('maintenance_type') ?: 'Routine Service',
                'service_date'      => $this->input->post('service_date') ?: date('Y-m-d'),
                'next_service_date' => $this->input->post('next_service_date') ?: date('Y-m-d', strtotime('+3 months')),
                'description'       => trim($this->input->post('description')),
                'service_provider'  => trim($this->input->post('service_provider')),
                'cost'              => (float)$this->input->post('cost'),
                'invoice_number'    => trim($this->input->post('invoice_number')),
                'status'            => $this->input->post('status') ?: 'Completed'
            ];

            $this->Vehicle_maintenance_model->insert($postData);
            $this->session->set_flashdata('success', 'Maintenance record logged.');
            redirect('transport/maintenance');
            return;
        }

        $data['title'] = 'Vehicle Maintenance';
        $data['records'] = $this->Vehicle_maintenance_model->get_all();
        $data['vehicles'] = $this->Vehicle_model->get_all();
        $data['total_cost'] = $this->Vehicle_maintenance_model->get_total_cost();

        $this->render('pages/transport/maintenance', $data);
    }

    // 13. Maintenance History
    public function maintenance_history()
    {
        $this->require_permission('transport.view');
        $vehicle_id = $this->input->get('vehicle_id') ?: NULL;

        $data['title'] = 'Maintenance History Ledger';
        $data['vehicle_id'] = $vehicle_id;
        $data['vehicles'] = $this->Vehicle_model->get_all();
        $data['records'] = $this->Vehicle_maintenance_model->get_all($vehicle_id);
        $data['total_cost'] = $this->Vehicle_maintenance_model->get_total_cost($vehicle_id);

        $this->render('pages/transport/maintenance_history', $data);
    }

    // 14. Transport Documents
    public function documents()
    {
        $this->require_permission('transport.view');
        if ($this->input->post()) {
            $this->require_permission('transport.manage');
            $action = $this->input->post('action');
            if ($action === 'delete') {
                $doc_id = (int)$this->input->post('document_id');
                $this->Transport_document_model->delete($doc_id);
                $this->session->set_flashdata('success', 'Document removed.');
            } else {
                $file_path = NULL;
                if (!empty($_FILES['document_file']['name'])) {
                    $upload_path = './uploads/transport/';
                    if (!is_dir($upload_path)) mkdir($upload_path, 0777, TRUE);

                    $config['upload_path']   = $upload_path;
                    $config['allowed_types'] = 'pdf|doc|docx|jpg|jpeg|png';
                    $config['max_size']      = 10240;
                    $this->load->library('upload', $config);

                    if ($this->upload->do_upload('document_file')) {
                        $upData = $this->upload->data();
                        $file_path = $upData['file_name'];
                    }
                }

                $postData = [
                    'entity_type'     => $this->input->post('entity_type') ?: 'Vehicle',
                    'entity_id'       => (int)$this->input->post('entity_id'),
                    'document_type'   => $this->input->post('document_type') ?: 'Registration',
                    'document_number' => trim($this->input->post('document_number')),
                    'issue_date'      => $this->input->post('issue_date') ?: NULL,
                    'expiry_date'     => $this->input->post('expiry_date') ?: NULL,
                    'file_path'       => $file_path,
                    'status'          => 'Active'
                ];

                $this->Transport_document_model->insert($postData);
                $this->session->set_flashdata('success', 'Compliance document uploaded.');
            }
            redirect('transport/documents');
            return;
        }

        $data['title'] = 'Transport Compliance Documents';
        $data['documents'] = $this->Transport_document_model->get_all();
        $data['vehicles'] = $this->Vehicle_model->get_all();
        $data['drivers'] = $this->Driver_model->get_all();

        $this->render('pages/transport/documents', $data);
    }

    // 15. Transport Reports
    public function reports()
    {
        $this->require_permission('transport.view');
        $type = $this->input->get('type') ?: 'vehicle';

        if ($this->input->get('export') === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=transport_report_' . date('Ymd_His') . '.csv');
            $out = fopen('php://output', 'w');

            if ($type === 'student') {
                $assigns = $this->Transport_assignment_model->get_all();
                fputcsv($out, ['Student ID', 'Student Name', 'Admission No', 'Class', 'Route', 'Stop', 'Vehicle', 'Fee', 'Status']);
                foreach ($assigns as $a) {
                    fputcsv($out, [$a->student_id, $a->first_name . ' ' . $a->last_name, $a->admission_no, $a->class_name . ' ' . $a->section_name, $a->route_name, $a->pickup_stop_name, $a->vehicle_number, $a->monthly_fee, $a->status]);
                }
            } else {
                $vehicles = $this->Vehicle_model->get_all();
                fputcsv($out, ['Vehicle No', 'Registration No', 'Type', 'Capacity', 'Occupied', 'Available', 'Driver', 'Route', 'Status']);
                foreach ($vehicles as $v) {
                    fputcsv($out, [$v->vehicle_number, $v->registration_number, $v->vehicle_type, $v->seating_capacity, $v->occupied_seats, $v->available_seats, $v->driver_name, $v->route_name, $v->status]);
                }
            }
            fclose($out);
            exit;
        }

        $data['title'] = 'Transport Reports & Analytics';
        $data['type'] = $type;
        $data['stats'] = $this->Transport_model->get_dashboard_stats();
        $data['vehicles'] = $this->Vehicle_model->get_all();
        $data['drivers'] = $this->Driver_model->get_all();
        $data['routes'] = $this->Route_model->get_all();
        $data['assignments'] = $this->Transport_assignment_model->get_all();
        $data['maintenance'] = $this->Vehicle_maintenance_model->get_all();

        $this->render('pages/transport/reports', $data);
    }

    // 16. Transport Settings
    public function settings()
    {
        $this->require_permission('transport.manage');
        if ($this->input->post()) {
            $postData = [
                'enable_transport'              => $this->input->post('enable_transport') ? 1 : 0,
                'enforce_capacity'              => $this->input->post('enforce_capacity') ? 1 : 0,
                'allow_capacity_override'       => $this->input->post('allow_capacity_override') ? 1 : 0,
                'default_monthly_fee'           => (float)$this->input->post('default_monthly_fee'),
                'fee_frequency'                 => $this->input->post('fee_frequency') ?: 'Monthly',
                'maintenance_reminder_days'     => (int)$this->input->post('maintenance_reminder_days'),
                'document_expiry_reminder_days' => (int)$this->input->post('document_expiry_reminder_days'),
                'driver_license_reminder_days'  => (int)$this->input->post('driver_license_reminder_days'),
                'allow_one_way'                 => $this->input->post('allow_one_way') ? 1 : 0,
                'allow_pickup_only'             => $this->input->post('allow_pickup_only') ? 1 : 0,
                'allow_drop_only'               => $this->input->post('allow_drop_only') ? 1 : 0,
                'allow_bulk_assignment'         => $this->input->post('allow_bulk_assignment') ? 1 : 0
            ];

            $this->Transport_setting_model->update_settings($postData);
            $this->session->set_flashdata('success', 'Transport settings updated.');
            redirect('transport/settings');
            return;
        }

        $data['title'] = 'Transport Settings';
        $data['settings'] = $this->Transport_setting_model->get_settings();
        $data['audit_logs'] = $this->Transport_setting_model->get_audit_logs(25);

        $this->render('pages/transport/settings', $data);
    }
}
