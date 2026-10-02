<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Search Controller
 *
 * Handles:
 *  - AJAX live search for the global header search input (/search/global)
 *  - Full search results page with category tabs (/search?q=...)
 */
class Search extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Global_search_model');
    }

    /**
     * Helper to compute user permissions per entity category.
     */
    protected function get_search_permissions()
    {
        $is_admin = $this->rbac->is_super_admin();
        return [
            'students' => $is_admin || $this->rbac->has_permission('students.view'),
            'staff'    => $is_admin || $this->rbac->has_permission('staff.view'),
            'classes'  => $is_admin || $this->rbac->has_permission('academics.view'),
            'subjects' => $is_admin || $this->rbac->has_permission('academics.view'),
        ];
    }

    /**
     * AJAX endpoint for live global header search.
     * GET or POST /search/global?q=...
     */
    public function global_query()
    {
        $raw_query = $this->input->get_post('q', TRUE);
        $query = trim(strip_tags((string)$raw_query));

        // Minimum character check
        if (mb_strlen($query) < 2) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status'        => false,
                    'message'       => 'Please enter at least 2 characters to search.',
                    'query'         => $query,
                    'total_results' => 0,
                    'categories'    => [],
                ]));
        }

        // Limit query length to prevent excessive regex/computation
        if (mb_strlen($query) > 100) {
            $query = mb_substr($query, 0, 100);
        }

        $permissions = $this->get_search_permissions();

        try {
            $active_year_id = $this->academic_year_id ?: get_active_academic_year_id(TRUE);
            $results = $this->Global_search_model->search_all($query, 5, $active_year_id, $permissions);

            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status'        => true,
                    'query'         => $query,
                    'total_results' => $results['total'],
                    'categories'    => [
                        'students' => [
                            'key'   => 'students',
                            'title' => 'Students',
                            'count' => $results['students']['count'],
                            'items' => $results['students']['items'],
                        ],
                        'staff' => [
                            'key'   => 'staff',
                            'title' => 'Staff / Teachers',
                            'count' => $results['staff']['count'],
                            'items' => $results['staff']['items'],
                        ],
                        'classes' => [
                            'key'   => 'classes',
                            'title' => 'Classes & Divisions',
                            'count' => $results['classes']['count'],
                            'items' => $results['classes']['items'],
                        ],
                        'subjects' => [
                            'key'   => 'subjects',
                            'title' => 'Subjects',
                            'count' => $results['subjects']['count'],
                            'items' => $results['subjects']['items'],
                        ],
                    ],
                    'view_all_url'  => site_url('search?q=' . urlencode($query)),
                ]));
        } catch (\Throwable $e) {
            log_message('error', 'Global Search Error: ' . $e->getMessage());
            return $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status'  => false,
                    'message' => 'Unable to search right now. Please try again.',
                ]));
        }
    }

    /**
     * Dedicated Full Global Search Results Page.
     * GET /search?q=...&tab=...&page=...
     */
    public function index()
    {
        $raw_query = $this->input->get('q', TRUE);
        $query     = trim(strip_tags((string)$raw_query));
        $tab       = trim((string)$this->input->get('tab', TRUE));
        if (!in_array($tab, ['all', 'students', 'staff', 'classes', 'subjects'], true)) {
            $tab = 'all';
        }

        $page     = max(1, (int)$this->input->get('page'));
        $per_page = 20;
        $offset   = ($page - 1) * $per_page;

        $permissions = $this->get_search_permissions();
        $active_year_id = $this->academic_year_id ?: get_active_academic_year_id(TRUE);

        $results = [
            'students' => ['count' => 0, 'items' => []],
            'staff'    => ['count' => 0, 'items' => []],
            'classes'  => ['count' => 0, 'items' => []],
            'subjects' => ['count' => 0, 'items' => []],
            'total'    => 0,
        ];

        if (mb_strlen($query) >= 2) {
            if ($tab === 'all') {
                $results = $this->Global_search_model->search_all($query, 10, $active_year_id, $permissions);
            } else {
                // Fetch paginated for single tab
                if ($tab === 'students' && !empty($permissions['students'])) {
                    $results['students'] = $this->Global_search_model->search_students($query, $per_page, $active_year_id, $offset);
                } elseif ($tab === 'staff' && !empty($permissions['staff'])) {
                    $results['staff'] = $this->Global_search_model->search_staff($query, $per_page, $offset);
                } elseif ($tab === 'classes' && !empty($permissions['classes'])) {
                    $results['classes'] = $this->Global_search_model->search_classes($query, $per_page, $active_year_id, $offset);
                } elseif ($tab === 'subjects' && !empty($permissions['subjects'])) {
                    $results['subjects'] = $this->Global_search_model->search_subjects($query, $per_page, $active_year_id, $offset);
                }

                // Also get counts for tabs
                $all_summary = $this->Global_search_model->search_all($query, 0, $active_year_id, $permissions);
                $results['students']['count'] = $all_summary['students']['count'];
                $results['staff']['count']    = $all_summary['staff']['count'];
                $results['classes']['count']  = $all_summary['classes']['count'];
                $results['subjects']['count'] = $all_summary['subjects']['count'];
                $results['total']             = $all_summary['total'];
            }
        }

        $this->render('pages/search/results', [
            'title'       => 'Search Results: ' . ($query ?: 'Global Search'),
            'page_key'    => 'global-search',
            'breadcrumb'  => ['Search', 'Global Search Results'],
            'query'       => $query,
            'tab'         => $tab,
            'page'        => $page,
            'per_page'    => $per_page,
            'results'     => $results,
            'permissions' => $permissions,
        ]);
    }
}
