/* ==========================================================================
   School — shared app shell (sidebar + header) and small UI helpers.
   Loaded on every page via <script src="<?php echo base_url('assets/app.js'); ?>">.
   Renders into #sidebar-root and #header-root, wires up all interactivity,
   and highlights the active nav item based on body[data-page].

   CI3 NOTE: this file is a static asset, so it can't run PHP. Every
   controller/template sets `window.APP_BASE_URL` (the CodeIgniter base_url())
   before this script loads. All internal links below are built from
   PAGE_URLS + APP_BASE_URL instead of hardcoded "*.html" files, so they
   resolve to the real CodeIgniter controller/method routes.
   ========================================================================== */

// Page key -> CodeIgniter URI (relative, no leading slash). Mirrors the
// controller/method map in application/config/routes.php + the controllers.
const PAGE_URLS = {
  "dashboard": "dashboard",
  // Student Management
  "students": "students/overview",
  "student-overview": "students/overview",
  "all-students": "students/all",
  "student-bulk-add": "student-bulk-add",
  "student-directory": "students/list",
  "student-registration": "students/register",
  "student-add": "students/register",
  "admissions": "students/admissions",
  "student-documents": "students/documents",
  "student-id-cards": "students/id_cards",
  "student-promotion": "students/promotion",
  "student-transfers": "students/transfers",
  "student-search": "students/search",
  "student-profile": "students/profile",
  "student-reports": "students/overview",
  // Staff / Teacher Management
  "staff": "staff/overview",
  "staff-overview": "staff/overview",
  "staff-directory": "staff/directory",
  "staff-search": "staff/directory",
  "teachers": "staff/teachers",
  "non-teaching-staff": "staff/non_teaching",
  "departments-designations": "staff/designations",
  "departments": "staff/designations",
  "designations": "staff/designations",
  "staff-documents": "staff/documents",
  "teacher-workload": "staff/workload",
  "staff-attendance": "staff/attendance",
  "staff-leave": "staff/leave",
  "staff-reports": "staff/overview",
  // Academic Management
  "academics": "academics/overview",
  "academic-dashboard": "academics/overview",
  "academic-years": "academics/years",
  "academic-groups": "academics/academic_groups",
  "academics-groups": "academics/academic_groups",
  "classes": "academics/classes",
  "divisions": "academics/divisions",
  "academics-divisions": "academics/divisions",
  "sections": "academics/divisions",
  "subjects": "academics/subjects",
  "class-teachers": "academics/classes",
  "subject-teachers": "academics/subjects",
  "timetable": "academics/timetable",
  "academic-calendar": "academics/calendar",
  "academic-reports": "academics/years",
  // Student Attendance
  "attendance": "attendance",
  "attendance-dashboard": "attendance",
  "attendance-overview": "attendance",
  "attendance-mark": "attendance/mark_attendance",
  "attendance-daily": "attendance/mark_attendance",
  "attendance-periods": "timetable/period_setup",
  "attendance-period-wise": "attendance/period_wise",
  "attendance-class": "attendance/class_attendance",
  "attendance-view": "student-attendance/view",
  "attendance-student": "student-attendance/details",
  "attendance-section": "attendance/class_attendance",
  "attendance-history": "attendance/class_attendance",
  "attendance-tracking": "attendance/class_attendance",
  "attendance-calendar": "attendance/calendar",
  "attendance-reports": "attendance/reports",
  "attendance-notifications": "attendance/notifications",
  "attendance-notification-history": "attendance/notification_history",
  "attendance-settings": "attendance/settings",
  // Examination & Results
  "exam-dashboard": "examinations",
  "exam-overview": "examinations",
  "exams": "examinations/exams",
  "exam-types": "examinations/types",
  "exam-schedules": "examinations/schedules",
  "exam-allocations": "examinations/allocations",
  "marks-entry": "examinations/marks_entry",
  "marks-verification": "examinations/verification",
  "grade-management": "examinations/grades",
  "result-calculation": "examinations/calculate",
  "results": "examinations/results",
  "exam-ranks": "examinations/ranks",
  "report-cards": "examinations/report_cards",
  "progress-reports": "examinations/progress_reports",
  "result-publishing": "examinations/publishing",
  "exam-reports": "examinations/reports",
  "exam-settings": "examinations/settings",
  // Fees & Finance
  "fee-dashboard": "finance/dashboard",
  "fee-overview": "finance/dashboard",
  "fee-categories": "finance/fee_types",
  "fee-structures": "finance/fee_structures",
  "fee-assignments": "finance/fee_assignments",
  "student-fees": "finance/ledger_students",
  "fee-collection": "finance/fee_collection",
  "payment-history": "finance/fee_receipts",
  "fee-receipts": "finance/fee_receipts",
  "fee-discounts": "finance/fee_structures",
  "due-fees": "finance/pending_fees",
  "fee-reminders": "finance/pending_fees",
  "fee-adjustments": "finance/adjustments",
  "fee-refunds": "finance/refunds",
  "collection-reports": "finance/reports",
  "student-fee-reports": "finance/reports",
  "due-reports": "finance/reports",
  "finance-settings": "finance/dashboard",
  // Timetable
  "timetable-dashboard": "timetable",
  "timetable-overview": "timetable",
  "class-timetable": "timetable/classes",
  "teacher-timetable": "timetable/teachers",
  "subject-allocation": "timetable/allocations",
  "timetable-builder": "timetable/builder",
  "timetable-period-setup": "timetable/period_setup",
  "period-setup": "timetable/period_setup",
  "free-periods": "timetable/free_periods",
  "timetable-conflicts": "timetable/conflicts",
  "timetable-publish": "timetable/publish_lock",
  "timetable-reports": "timetable/reports",
  "timetable-settings": "timetable/settings",
  // Homework / Assignments
  "homework-dashboard": "homework",
  "homework-overview": "homework",
  "homework-assignments": "homework/assignments",
  "homework-create": "homework/create",
  "homework-calendar": "homework/calendar",
  "homework-subjects": "homework/subjects",
  "homework-classes": "homework/classes",
  "homework-submissions": "homework/submissions",
  "homework-types": "homework/types",
  "homework-reports": "homework/reports",
  "homework-settings": "homework/settings",
  // Communication
  "comm-dashboard": "communication",
  "comm-overview": "communication",
  "comm-templates": "communication/templates",
  "comm-sms-templates": "communication/sms_templates",
  "comm-whatsapp-templates": "communication/whatsapp_templates",
  "comm-email-templates": "communication/email_templates",
  "comm-automated": "communication/automated_notifications",
  "comm-rules": "communication/automated_notifications",
  "comm-queue": "communication/queue",
  "comm-history": "communication/history",
  "comm-reports": "communication/reports",
  "comm-settings": "communication/settings",
  "comm-failed": "communication/failed",
  "notices": "communication/notices",
  "announcements": "communication/announcements",
  "communication": "communication",
  // Leave Management
  "leave-dashboard": "leave",
  "leave-overview": "leave",
  "leave-student": "leave/student_leave",
  "leave-staff": "leave/staff_leave",
  "leave-types": "leave/types",
  "leave-request": "leave/request",
  "leave-approval": "leave/approval",
  "leave-balance": "leave/balances",
  "leave-calendar": "leave/calendar",
  "leave-history": "leave/history",
  "leave-reports": "leave/reports",
  "leave-settings": "leave/settings",
  "leave-management": "leave",
  // Transport Management
  "transport-dashboard": "transport",
  "transport-overview": "transport",
  "transport-vehicles": "transport/vehicles",
  "transport-drivers": "transport/drivers",
  "transport-routes": "transport/routes",
  "transport-stops": "transport/stops",
  "transport-assignments": "transport/assignments",
  "transport-bulk": "transport/bulk_assign",
  "transport-fees": "transport/fees",
  "transport-maintenance": "transport/maintenance",
  "transport-maintenance-history": "transport/maintenance_history",
  "transport-documents": "transport/documents",
  "transport-reports": "transport/reports",
  "transport-settings": "transport/settings",
  "transport": "transport",
  // Certificate & Document Management
  "certificates-dashboard": "certificates",
  "certificates-overview": "certificates",
  "certificates-reports": "certificates/history",
  "certificates-requests": "certificates/requests",
  "certificates-types": "certificates/types",
  "certificates-bonafide": "certificates/bonafide",
  "certificates-transfer": "certificates/transfer_certificate",
  "certificates-study": "certificates/study_certificate",
  "certificates-conduct": "certificates/conduct_certificate",
  "certificates-generate": "certificates/generate",
  "certificates-templates": "certificates/templates",
  "certificates-documents": "certificates/documents",
  "certificates-doc-categories": "certificates/document_categories",
  "certificates-doc-verification": "certificates/document_verification",
  "certificates-history": "certificates/history",
  "certificates-reports": "certificates/reports",
  "certificates-settings": "certificates/settings",
  // Reports
  "reports": "reports",
  "reports-overview": "reports",
  "fee-reports": "fees/reports?type=due",
  "result-reports": "examinations/results",
  "library-reports": "reports",
  "inventory-reports": "reports",
  // User & Permission Management
  "user-dashboard": "users",
  "user-overview": "users",
  "users": "users/list",
  "user-create": "users/create",
  "user-details": "users/details",
  "user-roles": "users/roles",
  "user-permissions": "users/permissions",
  "user-role-permissions": "users/role_permissions",
  "user-parents": "users/parents",
  "user-students": "users/students",
  "user-teachers": "users/teachers",
  "user-staff": "users/staff",
  "user-login-activity": "users/login_activity",
  "user-security-settings": "users/security_settings",
  "user-audit-logs": "users/audit_logs",
  "user-management": "users",
  "change-password": "change-password",
  // School Settings
  "settings": "settings",
  "settings-overview": "settings",
  "staff-document-settings": "settings/staff_documents",
  "settings-staff-documents": "settings/staff_documents",
  "portal-dashboard": "dashboard",
  "portal-profile": "students",
  "portal-attendance": "attendance",
  "portal-homework": "homework",
  "portal-results": "examinations/results",
  "portal-timetable": "academics/timetable",
  "portal-fees": "fees",
  "portal-notices": "communication/notices",
  "portal-leave": "leave",
  "portal-messages": "communication",
  "library-dashboard": "reports",
  "library-books": "reports",
  "library-categories": "reports",
  "library-authors": "reports",
  "library-publishers": "reports",
  "library-issue": "reports",
  "library-return": "reports",
  "library-members": "reports",
  "library-fines": "reports",
  "library-available": "reports",
  "hostel-dashboard": "reports",
  "hostel-buildings": "reports",
  "hostel-rooms": "reports",
  "hostel-allocation": "reports",
  "hostel-attendance": "reports",
  "hostel-fees": "fees",
  "hostel-visitors": "reports",
  "hostel-reports": "reports",
  "inventory-dashboard": "reports",
  "inventory-products": "reports",
  "inventory-categories": "reports",
  "inventory-stock-in": "reports",
  "inventory-stock-out": "reports",
  "inventory-current-stock": "reports",
  "inventory-suppliers": "reports",
  "inventory-purchases": "reports",
  "inventory-alerts": "reports",
  "events-dashboard": "academics/calendar",
  "events-list": "academics/calendar",
  "events-programs": "academics/calendar",
  "events-competitions": "academics/calendar",
  "events-sports": "academics/calendar",
  "events-cultural": "academics/calendar",
  "events-calendar": "academics/calendar",
  "events-reports": "reports",
  "unauthorized": "unauthorized",
  "login": "auth/login",
  "logout": "auth/logout",
  "search": "search",
  "search/global": "search/global",
  "academic-year-switch": "academic-year/switch",
  "academic-year/switch": "academic-year/switch",
  "academics/switch_year": "academics/switch_year",
};

// Builds an absolute app URL for a given page key or relative route
function url(pageKey) {
  const base = window.APP_BASE_URL || "";
  if (!pageKey) return base;
  if (pageKey.startsWith("http://") || pageKey.startsWith("https://")) return pageKey;
  const uri = PAGE_URLS[pageKey] !== undefined ? PAGE_URLS[pageKey] : pageKey.replace(/^\//, '');
  return base + uri;
}

const CURRENT_USER = window.CURRENT_USER || {
  name: "Unknown User",
  role: "",
  email: "",
  initials: "?",
};

// Reorganized 3-Level Sidebar Nav Model:
// Level 1: Main Module (with icon)
// Level 2: Logical Group (collapsible)
// Level 3: Existing Pages (linked to existing routes)
const NAV = [
  // 1. Dashboard
  { key: "dashboard", label: "Dashboard", icon: "dashboard" },

  // 2. Student Management
  {
    key: "students", label: "Student Management", icon: "school",
    groups: [
      { key: "students", label: "Overview" },
      { key: "all-students", label: "All Students" },
      { key: "student-bulk-add", label: "Bulk Student Add" },
      {
        label: "Admissions",
        items: [
          { key: "student-registration", label: "Student Registration" },
          // { key: "admissions", label: "Admission Management" },
        ],
      },
      {
        key: "student-documents", label: "Student Documents"
      },
      {
        label: "Student Services",
        items: [
          { key: "student-id-cards", label: "Student ID Cards" },
          { key: "student-promotion", label: "Student Promotion" },
          { key: "student-transfers", label: "Transfer / TC Management" },
        ],
      },
    ],
  },

  // 3. Staff / Teacher Management
  {
    key: "staff", label: "Staff / Teacher Management", icon: "badge",
    groups: [
      { key: "staff", label: "Overview" },
      {
        label: "Staff Profiles",
        items: [
          { key: "teachers", label: "Teacher Profiles" },
          { key: "non-teaching-staff", label: "Staff Profiles" },
          { key: "staff-documents", label: "Staff Documents" },
        ],
      },
      {
        label: "Organization",
        items: [
          { key: "designations", label: "Designations" },
        ],
      },
      { key: "teacher-workload", label: "Teacher Workload" },
      { key: "staff-attendance", label: "Staff Attendance" },
      { key: "staff-leave", label: "Leave Management" },
    ],
  },

  // 4. Academic Management
  {
    key: "academics", label: "Academic Management", icon: "menu_book",
    groups: [
      { key: "academics", label: "Overview" },
      {
        label: "Academic Setup",
        items: [
          { key: "academic-years", label: "Academic Year" },
          // { key: "academic-groups", label: "Department / Groups" },
          { key: "classes", label: "Classes & Divisions" },
          { key: "subjects", label: "Subjects" },
        ],
      },
      { key: "academic-calendar", label: "Academic Calendar" },
    ],
  },

  // 5. Student Attendance
  {
    key: "attendance", label: "Student Attendance", icon: "fact_check",
    groups: [
      { key: "attendance-dashboard", label: "Overview" },
      {
        label: "Attendance",
        items: [
          { key: "attendance-mark", label: "Mark Attendance" },
          { key: "attendance-class", label: "Class Attendance" },
          { key: "attendance-period-wise", label: "Period Attendance (+1 / +2)" },
          { key: "attendance-calendar", label: "Attendance Calendar" },
        ],
      },
      {
        label: "Notifications",
        items: [
          { key: "attendance-notifications", label: "Parent Notifications" },
          { key: "attendance-notification-history", label: "Notification History" },
        ],
      },
      { key: "attendance-settings", label: "Settings" },
    ],
  },

  // 6. Examination & Results
  {
    key: "examinations", label: "Examination & Results", icon: "assignment_turned_in",
    groups: [
      { key: "exam-dashboard", label: "Overview" },
      {
        label: "Exam Setup",
        items: [
          { key: "exams", label: "Exam Creation" },
          { key: "grade-management", label: "Grade Management" },
        ],
      },
      { key: "exam-schedules", label: "Exam Schedules" },
      {
        label: "Marks & Results",
        items: [
          { key: "marks-entry", label: "Marks Entry" },
          { key: "result-calculation", label: "Result Calculation" },
        ],
      },
      { key: "report-cards", label: "Report Cards" },
      { key: "exam-ranks", label: "Rank / Position" },
      { key: "progress-reports", label: "Progress Reports" },
    ],
  },

  // 7. Fees & Finance
  // {
  //   key: "fees", label: "Fees & Finance", icon: "payments",
  //   groups: [
  //     { key: "fee-dashboard", label: "Overview" },
  //     {
  //       label: "Fee Setup",
  //       items: [
  //         { key: "fee-categories", label: "Fee Categories" },
  //         { key: "fee-structures", label: "Fee Structure" },
  //       ],
  //     },
  //     {
  //       label: "Student Fees",
  //       items: [
  //         { key: "fee-assignments", label: "Student Fee Assignment" },
  //         { key: "fee-discounts", label: "Discounts / Concessions" },
  //         { key: "due-fees", label: "Due Fees" },
  //       ],
  //     },
  //     {
  //       label: "Payments",
  //       items: [
  //         { key: "fee-collection", label: "Fee Collection" },
  //         { key: "fee-receipts", label: "Receipts" },
  //       ],
  //     },
  //     { key: "fee-reminders", label: "Fee Reminders" },
  //     { key: "collection-reports", aliases: ["fee-reports", "due-reports"], label: "Reports" },
  //     { key: "finance-settings", label: "Fee Settings" },
  //   ],
  // },
  // 8. Timetable
  {
    key: "timetable", label: "Timetable", icon: "calendar_month",
    groups: [
      { key: "timetable-dashboard", label: "Overview" },
      {
        label: "Timetables",
        items: [
          { key: "class-timetable", label: "Class Timetable" },
          { key: "teacher-timetable", label: "Teacher Timetable" },
        ],
      },
      { key: "subject-allocation", label: "Subject Allocation" },
      { key: "timetable-builder", label: "Period Management" },
      { key: "timetable-period-setup", label: "Period Setup" },
      { key: "free-periods", label: "Free-period Management" },
      { key: "timetable-reports", label: "Reports" },
    ],
  },

  // 9. Homework / Assignments
  {
    key: "homework", label: "Homework / Assignments", icon: "assignment",
    groups: [
      { key: "homework-dashboard", label: "Overview" },
      {
        label: "Assignments",
        items: [
          { key: "homework-create", label: "Homework Creation" },
          { key: "homework-subjects", label: "Subject-wise Assignments" },
        ],
      },
      { key: "homework-submissions", label: "Submissions" },
      {
        label: "Evaluation",
        items: [
          { key: "homework-assignments", label: "Teacher Remarks" },
          { key: "homework-types", label: "Assignment Evaluation" },
        ],
      },
      { key: "homework-classes", label: "Student / Parent Access" },
      { key: "homework-reports", label: "Reports" },
    ],
  },

  // 10. Communication
  {
    key: "communication", label: "Communication", icon: "campaign",
    groups: [
      { key: "comm-dashboard", label: "Overview" },
      { key: "notices", label: "Notices" },
      { key: "announcements", label: "Announcements" },
      {
        label: "Messaging",
        items: [
          { key: "comm-templates", label: "Parent-Teacher Communication" },
          { key: "comm-automated", label: "Internal Messaging" },
        ],
      },
      {
        label: "Communication Channels",
        items: [
          { key: "comm-sms-templates", label: "SMS" },
          { key: "comm-whatsapp-templates", label: "WhatsApp" },
          { key: "comm-email-templates", label: "Email" },
        ],
      },
      { key: "comm-history", label: "History" },
      { key: "comm-settings", label: "Notification Settings" },

    ],
  },

  // 11. Parent & Student Portal (SECOND PHASE)
  {
    key: "portal", label: "Parent & Student Portal", icon: "diversity_1", soon: true,
    groups: [
      { key: "portal-dashboard", label: "Overview", soon: true },
      { key: "portal-profile", label: "Student Profile", soon: true },
      {
        label: "Academics",
        soon: true,
        items: [
          { key: "portal-attendance", label: "Attendance", soon: true },
          { key: "portal-homework", label: "Homework", soon: true },
          { key: "portal-results", label: "Exam Results", soon: true },
          { key: "portal-timetable", label: "Timetable", soon: true },
        ],
      },
      { key: "portal-fees", label: "Fees", soon: true },
      { key: "portal-notices", label: "Notices", soon: true },
      { key: "portal-leave", label: "Leave Requests", soon: true },
      { key: "portal-messages", label: "Messages", soon: true },
    ],
  },

  // 12. Leave Management
  {
    key: "leave", label: "Leave Management", icon: "event_busy",
    groups: [
      { key: "leave-dashboard", label: "Overview" },
      {
        label: "Leave Requests",
        items: [
          { key: "leave-student", label: "Student Leave" },
          { key: "leave-staff", label: "Staff Leave" },
        ],
      },
      { key: "leave-types", label: "Leave Types" },
      { key: "leave-approval", label: "Leave Approval" },
      { key: "leave-balance", label: "Leave Balance" },
      { key: "leave-history", label: "Leave History" },
      { key: "leave-reports", label: "Leave Reports" },
    ],
  },

  // 13. Library Management (SECOND PHASE)
  {
    key: "library", label: "Library Management", icon: "local_library", soon: true,
    groups: [
      { key: "library-dashboard", label: "Overview", soon: true },
      {
        label: "Books",
        soon: true,
        items: [
          { key: "library-books", label: "Book Management", soon: true },
          { key: "library-categories", label: "Categories", soon: true },
          { key: "library-authors", label: "Authors", soon: true },
          { key: "library-publishers", label: "Publishers", soon: true },
        ],
      },
      {
        label: "Issue / Return",
        soon: true,
        items: [
          { key: "library-issue", label: "Book Issue", soon: true },
          { key: "library-return", label: "Book Return", soon: true },
        ],
      },
      { key: "library-members", label: "Library Members", soon: true },
      { key: "library-fines", label: "Fine Management", soon: true },
      { key: "library-available", label: "Available Books", soon: true },
      { key: "library-reports", label: "Reports", soon: true },
    ],
  },

  // 14. Transport Management
  {
    key: "transport", label: "Transport Management", icon: "directions_bus",
    groups: [
      { key: "transport-dashboard", label: "Overview" },
      { key: "transport-vehicles", label: "Vehicles" },
      { key: "transport-drivers", label: "Drivers" },
      {
        label: "Routes & Stops",
        items: [
          { key: "transport-routes", label: "Routes" },
          { key: "transport-stops", label: "Stops" },
        ],
      },
      { key: "transport-assignments", label: "Student Transport Assignment" },
      { key: "transport-maintenance", label: "Vehicle Maintenance" },
      { key: "transport-fees", label: "Transport Fees" },
      { key: "transport-reports", label: "Reports" },
    ],
  },

  // 15. Hostel Management (SECOND PHASE)
  {
    key: "hostel", label: "Hostel Management", icon: "hotel", soon: true,
    groups: [
      { key: "hostel-dashboard", label: "Overview", soon: true },
      {
        label: "Hostel Setup",
        soon: true,
        items: [
          { key: "hostel-buildings", label: "Hostel Buildings", soon: true },
          { key: "hostel-rooms", label: "Rooms / Beds", soon: true },
        ],
      },
      { key: "hostel-allocation", label: "Student Allocation", soon: true },
      { key: "hostel-attendance", label: "Hostel Attendance", soon: true },
      { key: "hostel-fees", label: "Hostel Fees", soon: true },
      { key: "hostel-visitors", label: "Visitor Management", soon: true },
      { key: "hostel-reports", label: "Reports", soon: true },
    ],
  },

  // 16. Inventory / Store Management (SECOND PHASE)
  {
    key: "inventory", label: "Inventory / Store Management", icon: "inventory_2", soon: true,
    groups: [
      { key: "inventory-dashboard", label: "Overview", soon: true },
      {
        label: "Products",
        soon: true,
        items: [
          { key: "inventory-products", label: "Products / Items", soon: true },
          { key: "inventory-categories", label: "Categories", soon: true },
        ],
      },
      {
        label: "Stock",
        soon: true,
        items: [
          { key: "inventory-stock-in", label: "Stock In", soon: true },
          { key: "inventory-stock-out", label: "Stock Out", soon: true },
          { key: "inventory-current-stock", label: "Current Stock", soon: true },
        ],
      },
      { key: "inventory-suppliers", label: "Suppliers", soon: true },
      { key: "inventory-purchases", label: "Purchase Management", soon: true },
      { key: "inventory-alerts", label: "Low-stock Alerts", soon: true },
      { key: "inventory-reports", label: "Reports", soon: true },
    ],
  },

  // 17. Certificate & Document Management
  {
    key: "certificates", label: "Certificate & Document Management", icon: "workspace_premium",
    groups: [
      { key: "certificates-dashboard", label: "Overview" },
      {
        label: "Certificates",
        items: [
          { key: "certificates-bonafide", label: "Bonafide Certificate" },
          { key: "certificates-transfer", label: "Transfer Certificate" },
          { key: "certificates-study", label: "Study Certificate" },
          { key: "certificates-conduct", label: "Conduct Certificate" },
        ],
      },
      { key: "certificates-requests", label: "Certificate Requests" },
      {
        label: "Generation & Printing",
        items: [
          { key: "certificates-generate", label: "Certificate Generation" },
          { key: "certificates-templates", label: "Certificate Printing" },
        ],
      },
      { key: "certificates-documents", label: "Student Documents" },
      { key: "certificates-reports", label: "Reports" },
    ],
  },

  // 18. Events & Activities (SECOND PHASE)
  {
    key: "events", label: "Events & Activities", icon: "event", soon: true,
    groups: [
      { key: "events-dashboard", label: "Overview", soon: true },
      {
        label: "Events",
        soon: true,
        items: [
          { key: "events-list", label: "School Events", soon: true },
          { key: "events-programs", label: "Programs", soon: true },
        ],
      },
      {
        label: "Activities",
        soon: true,
        items: [
          { key: "events-competitions", label: "Competitions", soon: true },
          { key: "events-sports", label: "Sports Activities", soon: true },
          { key: "events-cultural", label: "Cultural Activities", soon: true },
        ],
      },
      { key: "events-calendar", label: "Event Calendar", soon: true },
      { key: "events-reports", label: "Reports", soon: true },
    ],
  },

  // 19. Communication / Notifications (SECOND PHASE)
  {
    key: "comm-notifications", label: "Communication / Notifications", icon: "notifications_active", soon: true,
    groups: [
      { key: "comm-dashboard", label: "Overview", soon: true },
      { key: "comm-templates", label: "Notification Templates", soon: true },
      { key: "comm-sms-templates", label: "SMS Templates", soon: true },
      { key: "comm-whatsapp-templates", label: "WhatsApp Templates", soon: true },
      { key: "comm-email-templates", label: "Email Templates", soon: true },
      {
        label: "Automated Notifications",
        soon: true,
        items: [
          { key: "comm-rules", label: "Notification Rules", soon: true },
          { key: "comm-automated", label: "Scheduled Notifications", soon: true },
        ],
      },
      { key: "comm-history", label: "Notification History", soon: true },
      { key: "comm-reports", label: "Delivery Reports", soon: true },
    ],
  },

  // 20. Reports
  {
    key: "reports", label: "Reports", icon: "bar_chart",
    groups: [
      { key: "reports", label: "Overview" },
      { key: "student-reports", label: "Student Reports" },
      { key: "attendance-reports", label: "Attendance Reports" },
      {
        label: "Finance Reports",
        items: [
          { key: "fee-reports", aliases: ["due-reports", "student-fee-reports"], label: "Fee Reports" },
          { key: "collection-reports", label: "Collection Reports" },
        ],
      },
      {
        label: "Examination & Results",
        items: [
          { key: "exam-reports", label: "Exam Reports" },
          { key: "result-reports", aliases: ["results"], label: "Result Reports" },
        ],
      },
      { key: "staff-reports", label: "Staff Reports" },
      { key: "academic-reports", label: "Academic Reports" },
      { key: "transport-reports", label: "Transport Reports" },
      { key: "library-reports", label: "Library Reports", soon: true },
      { key: "inventory-reports", label: "Inventory Reports", soon: true },
    ],
  },

  // 21. User & Permission Management
  {
    key: "user-management", label: "User & Permission Management", icon: "manage_accounts",
    groups: [
      { key: "user-dashboard", label: "Overview" },
      {

        key: "users", label: "Users"


      },
      {
        label: "Roles",
        items: [
          { key: "user-roles", label: "Roles" },
          { key: "user-role-permissions", label: "Role Permissions" },
        ],
      },
      {
        label: "Accounts",
        items: [
          { key: "user-parents", label: "Parent Accounts" },
          { key: "user-students", label: "Student Accounts" },
          { key: "user-teachers", label: "Teacher Accounts" },
          { key: "user-staff", label: "Staff Accounts" },
        ],
      },
      {
        label: "Security", key: "user-security-settings"
      },
      {
        label: "Activity",
        items: [
          { key: "user-login-activity", label: "Login Activity" },
          { key: "user-audit-logs", label: "Permission Audit Logs" },
        ],
      },
    ],
  },

  // 22. School Settings
  {
    key: "settings", label: "Login2 Settings", icon: "settings",
    groups: [
      { key: "settings/schools", label: "Register Schools", perm: "super_admin", aliases: ["schools"] },
      { key: "settings", label: "Overview" },
      { key: "settings", label: "Login2 Profile" },
      { key: "staff-document-settings", label: "Staff Document" },
    ],
  },
];

const PAGE_TITLES = {
  "schools": "School Management",
  "settings/schools": "School Management",
  "dashboard": "Dashboard", "students": "Student Directory", "all-students": "All Students", "student-bulk-add": "Bulk Student Add", "student-registration": "Student Registration", "student-admission": "Student Admission", "student-profile": "Student Profile",
  "student-categories": "Student Categories", "student-houses": "Student Houses", "student-roll": "Roll Number Assignment", "student-promote": "Student Promotion",
  "student-documents": "Student Documents", "student-id-cards": "Student ID Cards", "student-promotion": "Student Promotion", "student-transfers": "Transfer / TC Management", "student-search": "Student Search", "student-reports": "Student Reports",
  "staff": "Staff Directory", "staff-directory": "Staff Directory", "staff-search": "Staff Search", "teachers": "Teacher Profiles", "non-teaching-staff": "Staff Profiles",
  "departments-designations": "Designations", "departments": "Designations", "designations": "Designations", "staff-documents": "Staff Documents", "staff-document-settings": "Staff Document Settings", "settings-staff-documents": "Staff Document Settings",
  "teacher-workload": "Teacher Workload", "staff-attendance": "Staff Attendance", "staff-leave": "Staff Leave Management", "staff-reports": "Staff Reports",
  "academics": "Academic Management", "academic-years": "Academic Years", "academic-groups": "Department / Groups", "classes": "Classes & Divisions", "divisions": "Division Management", "sections": "Division Management",
  "subjects": "Subject Management", "subject-teachers": "Subject Teachers (via Subjects)",
  "timetable": "Timetable", "academic-calendar": "Academic Calendar", "academic-reports": "Academic Reports",
  "attendance": "Attendance Dashboard", "attendance-dashboard": "Attendance Dashboard", "attendance-daily": "Daily Attendance", "attendance-periods": "Period Management",
  "attendance-period-wise": "Period Attendance (+1 / +2)", "attendance-class": "Class Attendance", "attendance-view": "View Attendance", "attendance-student": "Student Attendance Details", "attendance-section": "Division Attendance",
  "attendance-history": "Attendance History", "attendance-tracking": "Absent / Late Tracking", "attendance-calendar": "Attendance Calendar",
  "attendance-reports": "Attendance Reports", "attendance-notifications": "Parent Notifications", "attendance-notification-history": "Notification History",
  "attendance-settings": "Attendance Settings",
  "exam-dashboard": "Examination Dashboard", "exams": "Exam Management", "exam-types": "Exam Types",
  "exam-schedules": "Exam Schedules", "exam-allocations": "Add Schedule", "marks-entry": "Marks Entry",
  "marks-verification": "Marks Verification", "grade-management": "Grade Management", "result-calculation": "Result Calculation",
  "results": "Student Results", "exam-ranks": "Rank / Position", "report-cards": "Report Cards",
  "progress-reports": "Progress Reports", "result-publishing": "Result Publishing", "exam-reports": "Examination Reports",
  "exam-settings": "Examination Settings",
  "fee-dashboard": "Fee Dashboard", "fee-categories": "Fee Categories", "fee-structures": "Fee Structure", "fee-assignments": "Fee Assignment",
  "student-fees": "Student Fees", "fee-collection": "Fee Collection", "payment-history": "Payment History", "fee-receipts": "Receipts",
  "fee-discounts": "Discounts & Concessions", "due-fees": "Due Fees", "fee-reminders": "Fee Reminders", "fee-adjustments": "Fee Adjustments",
  "fee-refunds": "Refunds", "collection-reports": "Collection Reports", "due-reports": "Due Reports", "finance-settings": "Fee Settings",
  "timetable-dashboard": "Timetable Dashboard", "class-timetable": "Class Timetable", "teacher-timetable": "Teacher Timetable",
  "subject-allocation": "Subject Allocation", "timetable-builder": "Timetable Builder", "timetable-period-setup": "Period Setup", "period-setup": "Period Setup", "free-periods": "Free Periods & Substitution",
  "timetable-conflicts": "Conflict Management", "timetable-publish": "Publish / Lock", "timetable-reports": "Timetable Reports",
  "timetable-settings": "Timetable Settings",
  "homework-dashboard": "Homework Dashboard", "homework-assignments": "Assignments", "homework-create": "Create Assignment",
  "homework-calendar": "Assignment Calendar", "homework-subjects": "Subject-wise Assignments", "homework-classes": "Class-wise Assignments",
  "homework-submissions": "Submission Tracking", "homework-types": "Assignment Types", "homework-reports": "Assignment Reports",
  "homework-settings": "Homework Settings",
  "comm-dashboard": "Notification Dashboard", "notices": "Notices & Circulars", "announcements": "Announcements",
  "comm-templates": "Notification Templates", "comm-sms-templates": "SMS Templates",
  "comm-whatsapp-templates": "WhatsApp Templates", "comm-email-templates": "Email Templates",
  "comm-automated": "Automated Notifications", "comm-rules": "Notification Rules",
  "comm-queue": "Notification Queue", "comm-history": "Notification History",
  "comm-reports": "Delivery Reports", "comm-settings": "Notification Settings", "comm-failed": "Failed Notifications",
  "communication": "Communication & Notifications",
  "leave-dashboard": "Leave Dashboard", "leave-student": "Student Leave", "leave-staff": "Staff Leave",
  "leave-types": "Leave Types", "leave-request": "Leave Request", "leave-approval": "Leave Approval",
  "leave-balance": "Leave Balance", "leave-calendar": "Leave Calendar", "leave-history": "Leave History",
  "leave-reports": "Leave Reports", "leave-settings": "Leave Settings", "leave-management": "Leave Management",
  "transport-dashboard": "Transport Dashboard", "transport-vehicles": "Vehicles", "transport-drivers": "Drivers",
  "transport-routes": "Routes", "transport-stops": "Stops", "transport-assignments": "Student Transport Assignments",
  "transport-bulk": "Bulk Student Assignment", "transport-fees": "Transport Fees", "transport-maintenance": "Vehicle Maintenance",
  "transport-maintenance-history": "Maintenance History", "transport-documents": "Transport Documents", "transport-reports": "Transport Reports",
  "transport-settings": "Transport Settings", "transport": "Transport Management",
  "certificates-dashboard": "Certificate Dashboard", "certificates-requests": "Certificate Requests",
  "certificates-types": "Certificate Types", "certificates-bonafide": "Bonafide Certificate",
  "certificates-transfer": "Transfer Certificate", "certificates-study": "Study Certificate",
  "certificates-conduct": "Conduct Certificate", "certificates-generate": "Certificate Generation",
  "certificates-templates": "Certificate Templates", "certificates-documents": "Student Documents",
  "certificates-doc-categories": "Document Categories", "certificates-doc-verification": "Document Verification",
  "certificates-history": "Certificate History", "certificates-reports": "Certificate Reports",
  "certificates-settings": "Certificate Settings", "certificates": "Certificates & Documents",
  "reports": "Reports",
  "fee-reports": "Fee Reports",
  "result-reports": "Result Reports",
  "library-reports": "Library Reports",
  "inventory-reports": "Inventory Reports",
  "user-dashboard": "User & Permission Dashboard",
  "users": "User Management",
  "user-create": "Add User",
  "user-details": "User Profile & Permissions",
  "user-roles": "Role Management",
  "user-permissions": "Permissions Catalog",
  "user-role-permissions": "Role Permissions Matrix",
  "user-parents": "Parent Accounts",
  "user-students": "Student Accounts",
  "user-teachers": "Teacher Accounts",
  "user-staff": "Staff Accounts",
  "user-login-activity": "Login Activity",
  "user-security-settings": "Security Settings",
  "user-audit-logs": "Permission Audit Logs",
  "user-management": "User & Permission Management",
  "settings": "Login2 Settings",
  "unauthorized": "Access Restricted",
};

function iconSpan(name, extra) {
  return `<span class="material-symbols-outlined ${extra || ""}">${name}</span>`;
}

function detectCurrentPageKey() {
  const bodyKey = (document.body.dataset.page || "").trim();
  const currentPath = (window.location && window.location.pathname ? window.location.pathname.toLowerCase() : "");

  // Normalize path by stripping base directory and index.php
  const cleanPath = currentPath
    .replace(/^.*\/index\.php\/?/, "")
    .replace(/^.*\/schoolnew\/?/i, "")
    .replace(/^\/+|\/+$/g, "");

  // Root or explicit dashboard
  if (cleanPath === "" || cleanPath === "dashboard") {
    return "dashboard";
  }

  // If body[data-page] is explicitly given and is NOT dashboard
  if (bodyKey && bodyKey !== "dashboard") {
    return bodyKey;
  }

  // Exact matching against PAGE_URLS
  for (const [key, uri] of Object.entries(PAGE_URLS)) {
    const normUri = uri.toLowerCase().replace(/^\/+|\/+$/g, "");
    if (cleanPath === normUri || cleanPath === normUri + "/index") {
      return key;
    }
  }

  // Match with underscore/dash conversions
  const normClean = cleanPath.replace(/_/g, "-");
  for (const [key, uri] of Object.entries(PAGE_URLS)) {
    const normUri = uri.toLowerCase().replace(/_/g, "-").replace(/^\/+|\/+$/g, "");
    if (normClean === normUri || normClean === normUri + "-index") {
      return key;
    }
  }

  // Match prefix e.g. "students/register/..." -> "student-registration"
  for (const [key, uri] of Object.entries(PAGE_URLS)) {
    const normUri = uri.toLowerCase().replace(/^\/+|\/+$/g, "");
    if (cleanPath.startsWith(normUri + "/")) {
      return key;
    }
  }

  // Match by first path segment (module name)
  const firstSegment = cleanPath.split("/")[0];
  for (const item of getNavItems()) {
    if (item.key === firstSegment) {
      return item.key;
    }
  }

  return bodyKey || "dashboard";
}

// Helper to resolve navigation source: database-driven window.DYNAMIC_NAV preferred
function getNavItems() {
  if (window.DYNAMIC_NAV && Array.isArray(window.DYNAMIC_NAV) && window.DYNAMIC_NAV.length > 0) {
    return window.DYNAMIC_NAV;
  }
  return NAV;
}

// Find active hierarchy path: Module Key and Group Label for the current active page
function findActiveHierarchy(activeKey) {
  const normKey = (activeKey || "").toLowerCase();
  const normKeyDash = normKey.replace(/_/g, "-");

  // Helper matcher
  function matchesKey(k, aliases) {
    if (!k) return false;
    const nk = k.toLowerCase();
    const nkDash = nk.replace(/_/g, "-");
    if (nk === normKey || nkDash === normKeyDash) return true;
    if (aliases && Array.isArray(aliases)) {
      return aliases.some(a => {
        const na = a.toLowerCase();
        return na === normKey || na.replace(/_/g, "-") === normKeyDash;
      });
    }
    return false;
  }

  // Preserve Examination & Results module expansion on direct navigation to exam-reports
  if (normKeyDash === "exam-reports") {
    return { moduleKey: "examinations", groupLabel: null, pageKey: "exam-reports" };
  }

  // 1. Exact match across NAV
  for (const item of getNavItems()) {
    if (!item.groups) {
      if (matchesKey(item.key)) {
        return { moduleKey: item.key, groupLabel: null, pageKey: item.key };
      }
      continue;
    }

    for (const group of item.groups) {
      // Direct submodule item (Level 2)
      if (group.key) {
        if (matchesKey(group.key, group.aliases)) {
          return { moduleKey: item.key, groupLabel: null, pageKey: group.key };
        }
      }
      // Collapsible subgroup (Level 2 dropdown)
      if (group.items) {
        for (const child of group.items) {
          if (matchesKey(child.key, child.aliases)) {
            return { moduleKey: item.key, groupLabel: group.label, pageKey: child.key };
          }
        }
      }
    }
  }

  // 2. Prefix match (e.g. "leave-..." belongs to module "leave")
  for (const item of getNavItems()) {
    if (item.groups) {
      if (normKeyDash.startsWith(item.key + "-") || normKeyDash === item.key) {
        // Find default/overview subitem if available
        const defaultGroup = item.groups[0];
        const defaultKey = defaultGroup.key || (defaultGroup.items ? defaultGroup.items[0].key : item.key);
        return { moduleKey: item.key, groupLabel: defaultGroup.label || null, pageKey: defaultKey };
      }
    }
  }

  // 3. Fallback: if activeKey is 'dashboard'
  if (normKey === "dashboard") {
    return { moduleKey: "dashboard", groupLabel: null, pageKey: "dashboard" };
  }

  return { moduleKey: null, groupLabel: null, pageKey: activeKey };
}

function renderNavItem(item, activeKey, activeHierarchy) {
  const isModuleOpen = item.key === activeHierarchy.moduleKey;
  if (!item.groups) {
    const active = isModuleOpen && item.key === activeHierarchy.pageKey;
    return `
      <div class="nav-group mb-1">
        <a href="${url(item.key)}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-body-md font-body-md transition-all duration-200
          ${active ? "bg-white text-primary font-bold shadow-sm" : "text-white/90 hover:bg-white/15 hover:text-white"}">
          <span class="flex items-center gap-3">
            ${iconSpan(item.icon, `text-[20px] ${active ? "text-primary" : "text-white/90"}`)}
            <span class="sidebar-label truncate">${item.label}</span>
          </span>
          ${item.soon ? '<span class="sidebar-label shrink-0 rounded-full bg-white/20 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white border border-white/30">Soon</span>' : ""}
        </a>
      </div>`;
  }

  // Expandable Module with Direct Items or Subgroups
  const groupsHtml = item.groups.map((group) => {
    if (group.perm === "super_admin" && !window.IS_SUPER_ADMIN) {
      return "";
    }
    // Level 2 Direct Item (e.g. Overview, Student Reports, Attendance Reports inside Reports)
    if (group.key) {
      const active = isModuleOpen && (
        group.key === activeHierarchy.pageKey ||
        (group.aliases && group.aliases.includes(activeHierarchy.pageKey))
      );
      return `
        <a href="${url(group.key)}" class="flex items-center justify-between pl-7 pr-3 py-1.5 rounded-lg text-xs font-body-md transition-colors
          ${active ? "bg-light-blue text-primary font-bold shadow-2xs" : "text-dark-text/80 hover:bg-light-blue/60 hover:text-dark-text"}">
          <span class="truncate">${group.label}</span>
          ${group.soon ? '<span class="ml-1.5 shrink-0 rounded-full bg-light-blue px-1.5 py-0.2 text-[9px] font-semibold uppercase tracking-wide text-primary">Soon</span>' : ""}
        </a>`;
    }

    // Level 2 Expandable Group (e.g. Finance Reports, Examination & Results, Leave Requests)
    const isGroupOpen = isModuleOpen && (
      group.label === activeHierarchy.groupLabel ||
      (group.items && group.items.some((c) => c.key === activeHierarchy.pageKey || (c.aliases && c.aliases.includes(activeHierarchy.pageKey))))
    );
    const itemsHtml = group.items.map((c) => {
      const active = isModuleOpen && (
        c.key === activeHierarchy.pageKey ||
        (c.aliases && c.aliases.includes(activeHierarchy.pageKey))
      );
      return `
        <a href="${url(c.key)}" class="flex items-center justify-between pl-10 pr-3 py-1.5 rounded-lg text-xs font-body-md transition-colors
          ${active ? "bg-light-blue text-primary font-bold shadow-2xs" : "text-dark-text/80 hover:bg-light-blue/60 hover:text-dark-text"}">
          <span class="truncate">${c.label}</span>
          ${c.soon ? '<span class="ml-1.5 shrink-0 rounded-full bg-light-blue px-1.5 py-0.2 text-[9px] font-semibold uppercase tracking-wide text-primary">Soon</span>' : ""}
        </a>`;
    }).join("");

    return `
      <div class="nav-level-2" data-group-label="${group.label}">
        <button type="button" data-toggle-subgroup
          class="w-full flex items-center justify-between pl-7 pr-3 py-1.5 rounded-lg text-[11px] font-bold uppercase tracking-wider transition-colors
          ${isGroupOpen ? "text-primary font-bold" : "text-dark-text/75 hover:bg-light-blue/60 hover:text-dark-text"}">
          <span class="truncate">${group.label}</span>
          ${iconSpan("chevron_right", `text-[16px] text-primary transition-transform ${isGroupOpen ? "rotate-90" : ""}`)}
        </button>
        <div class="nav-sub-items mt-0.5 space-y-0.5 ${isGroupOpen ? "" : "hidden"}">${itemsHtml}</div>
      </div>`;
  }).join("");

  return `
    <div class="nav-group mb-1 ${isModuleOpen ? "bg-white text-primary rounded-xl p-1.5 shadow-sm" : ""}" data-module-key="${item.key}">
      <button type="button" data-toggle-module="${item.key}"
        class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-body-md font-body-md transition-all duration-200
        ${isModuleOpen ? "text-primary font-bold bg-transparent" : "text-white/90 hover:bg-white/15 hover:text-white"}">
        <span class="flex items-center gap-3">
          ${iconSpan(item.icon, `text-[20px] ${isModuleOpen ? "text-primary" : "text-white/90"}`)}
          <span class="sidebar-label truncate">${item.label}</span>
        </span>
        ${item.soon ? '<span class="sidebar-label shrink-0 rounded-full bg-white/20 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white border border-white/30">Soon</span>' : iconSpan("expand_more", `sidebar-label text-[18px] transition-transform ${isModuleOpen ? "rotate-180 text-primary" : "text-white/70"}`)}
      </button>
      <div class="nav-groups mt-1 pt-1 border-t border-theme-border/60 space-y-0.5 ${isModuleOpen ? "" : "hidden"}">${groupsHtml}</div>
    </div>`;
}

function renderSidebar(activeKey) {
  const activeHierarchy = findActiveHierarchy(activeKey);
  const navHtml = getNavItems().map((item) => renderNavItem(item, activeKey, activeHierarchy)).join("");
  return `
  <!-- Mobile overlay -->
  <div id="sidebar-overlay" class="fixed inset-0 bg-dark-text/30 z-30 hidden lg:hidden"></div>

  <aside id="app-sidebar"
    class="fixed lg:sticky top-0 left-0 h-screen w-[264px] shrink-0 bg-sky-blue border-r border-sidebar-border
    flex flex-col z-40 -translate-x-full lg:translate-x-0 transition-transform duration-200">
    <div class="h-16 flex items-center gap-3 px-4 border-b border-white/20 shrink-0">
      <div class="w-9 h-9 rounded-xl bg-white text-primary shadow-xs flex items-center justify-center shrink-0 border border-white/40">
        ${iconSpan("school", "text-primary text-[20px]")}
      </div>
      <a href="${url("dashboard")}" class="sidebar-label font-headline-md text-headline-md text-white font-bold tracking-tight truncate">Login2</a>
      <button id="sidebar-collapse-btn" type="button" class="ml-auto hidden lg:flex items-center justify-center w-8 h-8 rounded-lg hover:bg-white/20 text-white/80 hover:text-white transition-colors">
        ${iconSpan("dock_to_right", "text-[20px]")}
      </button>
      <button id="sidebar-close-btn" type="button" class="ml-auto lg:hidden flex items-center justify-center w-8 h-8 rounded-lg hover:bg-white/20 text-white/80 hover:text-white transition-colors">
        ${iconSpan("close", "text-[20px]")}
      </button>
    </div>
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">${navHtml}</nav>
    <div class="border-t border-white/20 p-3">
      <a href="${url("logout")}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-body-md font-body-md text-white/90 hover:bg-white/15 hover:text-white transition-colors">
        ${iconSpan("logout", "text-[20px] text-white")}
        <span class="sidebar-label">Log Out</span>
      </a>
    </div>
  </aside>`;
}

function renderHeader(pageKey, breadcrumb) {
  const title = PAGE_TITLES[pageKey] || "Login2";
  const crumbHtml = (breadcrumb && breadcrumb.length ? breadcrumb : ["Dashboard", title])
    .map((c, i, arr) => (i === arr.length - 1
      ? `<span class="text-on-surface font-medium">${c}</span>`
      : `<span>${c}</span><span class="text-outline">/</span>`))
    .join(" ");

  const currentSchoolId = window.CURRENT_SCHOOL_ID || (window.CURRENT_SCHOOL ? window.CURRENT_SCHOOL.id : 1);
  const currentSchool = window.CURRENT_SCHOOL || {};
  const currentSchoolName = currentSchool.school_name || (window.AVAILABLE_SCHOOLS && window.AVAILABLE_SCHOOLS[0] ? window.AVAILABLE_SCHOOLS[0].school_name : "Default School");
  const availableSchools = window.AVAILABLE_SCHOOLS || [];
  const canSwitchSchool = (window.CAN_SWITCH_SCHOOL === true) && (window.IS_SUPER_ADMIN === true);

  let schoolSelectorHtml = "";
  if (canSwitchSchool && availableSchools.length > 0) {
    const sOptions = availableSchools.map(s => `
      <option value="${s.id}" ${Number(s.id) === Number(currentSchoolId) ? "selected" : ""}>
        ${s.school_name}${Number(s.id) === Number(currentSchoolId) ? " (Active)" : ""}
      </option>
    `).join("");

    schoolSelectorHtml = `
      <div class="flex items-center gap-1 sm:gap-1.5 px-2 sm:px-2.5 py-1 rounded-lg bg-light-blue border border-theme-border text-dark-text shrink min-w-0">
        <span class="material-symbols-outlined text-[16px] sm:text-[18px] text-primary shrink-0">domain</span>
        <label for="global-school-select" class="text-[11px] font-semibold uppercase tracking-wider text-muted-text hidden sm:inline">School:</label>
        <select data-testid="school-select" id="global-school-select" data-current-school="${currentSchoolId}" class="bg-transparent border-0 py-0.5 pl-0.5 sm:pl-1 pr-5 sm:pr-6 text-[11px] sm:text-[13px] font-semibold text-primary focus:ring-0 focus:outline-none cursor-pointer transition-opacity max-w-[70px] sm:max-w-[140px] md:max-w-none truncate">
          ${sOptions}
        </select>
      </div>
    `;
  } else if (currentSchoolName) {
    schoolSelectorHtml = `
      <div class="flex items-center gap-1 sm:gap-1.5 px-2 sm:px-2.5 py-1 rounded-lg bg-light-blue border border-theme-border text-dark-text shrink min-w-0">
        <span class="material-symbols-outlined text-[16px] sm:text-[18px] text-primary shrink-0">domain</span>
        <span class="text-[11px] font-semibold uppercase tracking-wider text-muted-text hidden sm:inline">School:</span>
        <span class="text-[11px] sm:text-[13px] font-semibold text-dark-text max-w-[70px] sm:max-w-[140px] md:max-w-none truncate inline-block align-middle">${currentSchoolName}</span>
      </div>
    `;
  }

  const currentYearId = window.ACTIVE_ACADEMIC_YEAR_ID || window.CURRENT_ACADEMIC_YEAR_ID || 1;
  const currentYear = window.ACTIVE_ACADEMIC_YEAR || window.CURRENT_ACADEMIC_YEAR || {};
  const currentYearName = currentYear.year_name || (window.AVAILABLE_ACADEMIC_YEARS && window.AVAILABLE_ACADEMIC_YEARS[0] ? window.AVAILABLE_ACADEMIC_YEARS[0].year_name : "");
  const availableYears = window.AVAILABLE_ACADEMIC_YEARS || [];
  const canChangeYear = (window.CAN_CHANGE_ACADEMIC_YEAR === true) && (window.IS_SUPER_ADMIN === true);

  let yearSelectorHtml = "";
  if (canChangeYear && availableYears.length > 0) {
    const options = availableYears.map(y => `
      <option value="${y.academic_year_id}" ${Number(y.academic_year_id) === Number(currentYearId) ? "selected" : ""}>
        ${y.year_name}${Number(y.academic_year_id) === Number(currentYearId) ? " (Active)" : ""}
      </option>
    `).join("");

    yearSelectorHtml = `
      <div class="flex items-center gap-1 sm:gap-1.5 px-2 sm:px-2.5 py-1 rounded-lg bg-beige border border-theme-border text-dark-text shrink min-w-0">
        <span class="material-symbols-outlined text-[16px] sm:text-[18px] text-primary shrink-0">calendar_month</span>
        <label for="global-academic-year-select" class="text-[11px] font-semibold uppercase tracking-wider text-muted-text hidden sm:inline">Academic Year:</label>
        <select data-testid="academic-year-select" id="global-academic-year-select" data-current-year="${currentYearId}" class="bg-transparent border-0 py-0.5 pl-0.5 sm:pl-1 pr-5 sm:pr-6 text-[11px] sm:text-[13px] font-semibold text-primary focus:ring-0 focus:outline-none cursor-pointer transition-opacity max-w-[65px] sm:max-w-[120px] md:max-w-none truncate">
          ${options}
        </select>
      </div>
    `;
  } else {
    yearSelectorHtml = `
      <div class="flex items-center gap-1 sm:gap-1.5 px-2 sm:px-2.5 py-1 rounded-lg bg-beige border border-theme-border text-dark-text shrink min-w-0">
        <span class="material-symbols-outlined text-[16px] sm:text-[18px] text-primary shrink-0">calendar_month</span>
        <span class="text-[11px] font-semibold uppercase tracking-wider text-muted-text hidden sm:inline">Academic Year:</span>
        <span class="text-[11px] sm:text-[13px] font-semibold text-dark-text max-w-[65px] sm:max-w-[120px] md:max-w-none truncate inline-block align-middle">${currentYearName} <span class="text-xs text-primary font-medium hidden sm:inline">(Active)</span></span>
      </div>
    `;
  }

  return `
  <header data-testid="app-header" class="sticky top-0 z-20 h-16 bg-white/95 backdrop-blur border-b border-theme-border flex items-center justify-between gap-2 sm:gap-3 px-3 sm:px-4 lg:px-6">
    <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1 sm:flex-initial">
      <button id="sidebar-open-btn" type="button" class="lg:hidden flex items-center justify-center w-9 h-9 rounded-lg hover:bg-light-blue text-dark-text shrink-0" aria-label="Open navigation menu">
        ${iconSpan("menu", "text-[22px]")}
      </button>
      <div class="min-w-0">
        <div class="hidden sm:flex items-center gap-1.5 text-label-md text-label-md text-muted-text uppercase tracking-wide truncate">${crumbHtml}</div>
        <h1 class="text-[15px] sm:font-headline-lg-mobile sm:text-headline-lg-mobile lg:font-headline-lg lg:text-headline-lg font-semibold text-dark-text truncate leading-tight">${title}</h1>
      </div>
    </div>

    <div id="global-search-container" class="relative hidden md:flex items-center flex-1 max-w-sm ml-3 lg:ml-6">
      <div class="relative w-full">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-muted-text/70 text-[20px] pointer-events-none">search</span>
        <input 
          id="global-search-input"
          type="text" 
          autocomplete="off"
          spellcheck="false"
          placeholder="Search students, staff, records..."
          class="w-full pl-10 pr-8 py-1.5 rounded-xl border border-theme-border bg-light-blue/40 text-body-md font-body-md text-dark-text placeholder-muted-text/60 focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-white transition-all" />
        <button id="global-search-clear-btn" type="button" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-muted-text hover:text-dark-text p-0.5 rounded-full hover:bg-light-blue transition">
          <span class="material-symbols-outlined text-[16px]">close</span>
        </button>
      </div>

      <!-- Dropdown Container -->
      <div id="global-search-dropdown" class="hidden absolute left-0 top-full mt-2 w-[440px] max-w-[90vw] max-h-[75vh] flex flex-col bg-white rounded-2xl shadow-xl border border-theme-border overflow-hidden z-50 text-left transition-all duration-150">
      </div>
    </div>

    <div class="flex items-center gap-1.5 sm:gap-2.5 shrink-0">
      <a href="${url("search")}" class="hidden sm:flex md:hidden items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg hover:bg-light-blue text-dark-text shrink-0" title="Global Search">
        ${iconSpan("search", "text-[20px] sm:text-[22px]")}
      </a>
      ${schoolSelectorHtml}
      ${yearSelectorHtml}

      <button type="button" class="hidden sm:flex relative items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg hover:bg-light-blue text-dark-text shrink-0">
        ${iconSpan("notifications", "text-[20px] sm:text-[22px]")}
        <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-error ring-2 ring-white"></span>
      </button>
      <div class="relative shrink-0">
        <button data-testid="profile-menu-btn" id="profile-menu-btn" type="button" class="flex items-center gap-1.5 sm:gap-2 p-1 sm:pl-1.5 sm:pr-2 sm:py-1.5 rounded-lg hover:bg-light-blue transition-colors">
          <div class="w-8 h-8 rounded-full bg-light-blue text-primary border border-sky-blue/50 flex items-center justify-center text-label-md text-label-md font-semibold shrink-0">${CURRENT_USER.initials}</div>
          <div class="hidden sm:block text-left leading-tight">
            <div data-testid="current-user-name" class="text-body-md font-body-md font-medium text-dark-text">${CURRENT_USER.name}</div>
            <div class="text-[11px] text-muted-text">${CURRENT_USER.role}</div>
          </div>
          ${iconSpan("expand_more", "hidden sm:block text-[18px] text-muted-text")}
        </button>
        <div data-testid="profile-menu" id="profile-menu" class="hidden absolute right-0 mt-2 w-56 rounded-xl border border-theme-border bg-white shadow-lg py-2 z-30">
          <div class="px-3.5 py-2 border-b border-theme-border">
            <div class="text-body-md font-body-md font-medium text-dark-text">${CURRENT_USER.name}</div>
            <div class="text-[12px] text-muted-text truncate">${CURRENT_USER.email}</div>
            <span class="inline-block mt-1.5 rounded-full bg-light-blue text-primary text-[10px] font-semibold uppercase tracking-wide px-2 py-0.5 border border-sky-blue/40">${CURRENT_USER.role}</span>
          </div>
          <a href="${url("settings")}" class="flex items-center gap-2.5 px-3.5 py-2 text-body-md font-body-md text-dark-text hover:bg-light-blue hover:text-dark-text">${iconSpan("settings", "text-[18px] text-primary")} Login2 Settings</a>
          <a href="${url("user-management")}" class="flex items-center gap-2.5 px-3.5 py-2 text-body-md font-body-md text-dark-text hover:bg-light-blue hover:text-dark-text">${iconSpan("manage_accounts", "text-[18px] text-primary")} User Management</a>
          <button type="button" id="header-change-password-btn" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-body-md font-body-md text-dark-text hover:bg-light-blue hover:text-dark-text text-left transition-colors">${iconSpan("lock_reset", "text-[18px] text-primary")} Change Password</button>
          <div class="my-1 border-t border-theme-border"></div>
          <a data-testid="logout-btn" href="${url("logout")}" class="flex items-center gap-2.5 px-3.5 py-2 text-body-md font-body-md text-error hover:bg-error-container/40">${iconSpan("logout", "text-[18px]")} Log Out</a>
        </div>
      </div>
    </div>
  </header>`;
}

function initShell() {
  const pageKey = detectCurrentPageKey();
  const breadcrumb = document.body.dataset.breadcrumb ? JSON.parse(document.body.dataset.breadcrumb) : null;

  const sidebarRoot = document.getElementById("sidebar-root");
  const headerRoot = document.getElementById("header-root");
  if (sidebarRoot) {
    if (!document.getElementById("app-sidebar")) {
      sidebarRoot.outerHTML = renderSidebar(pageKey);
    } else {
      sidebarRoot.outerHTML = sidebarRoot.innerHTML;
    }
  }
  if (headerRoot) headerRoot.outerHTML = renderHeader(pageKey, breadcrumb);

  // Mobile drawer
  const sidebar = document.getElementById("app-sidebar");
  const overlay = document.getElementById("sidebar-overlay");
  const openBtn = document.getElementById("sidebar-open-btn");
  const closeBtn = document.getElementById("sidebar-close-btn");
  function openDrawer() { sidebar.classList.remove("-translate-x-full"); overlay.classList.remove("hidden"); }
  function closeDrawer() { sidebar.classList.add("-translate-x-full"); overlay.classList.add("hidden"); }
  openBtn && openBtn.addEventListener("click", openDrawer);
  closeBtn && closeBtn.addEventListener("click", closeDrawer);
  overlay && overlay.addEventListener("click", closeDrawer);

  // Desktop collapse (icon rail)
  const collapseBtn = document.getElementById("sidebar-collapse-btn");
  collapseBtn && collapseBtn.addEventListener("click", () => {
    document.body.classList.toggle("sidebar-collapsed");
  });

  // Level 1: Main Module expand/collapse (Accordion: Only ONE module open at a time)
  document.querySelectorAll("[data-toggle-module]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const group = btn.closest(".nav-group");
      const groupsDiv = group ? group.querySelector(".nav-groups") : null;
      const mainIcon = btn.querySelector(".material-symbols-outlined:first-child");
      const chevron = btn.querySelector(".material-symbols-outlined:last-child");
      const isCurrentlyOpen = group && group.classList.contains("bg-white");

      // Accordion behavior: Close ALL other open modules
      document.querySelectorAll("#app-sidebar .nav-group").forEach((otherGroup) => {
        if (otherGroup !== group) {
          otherGroup.classList.remove("bg-white", "text-dark-blue", "text-primary", "rounded-xl", "p-1.5", "shadow-sm");
          const otherBtn = otherGroup.querySelector("[data-toggle-module]");
          const otherGroups = otherGroup.querySelector(".nav-groups");
          const otherIcon = otherBtn ? otherBtn.querySelector(".material-symbols-outlined:first-child") : null;
          const otherChevron = otherBtn ? otherBtn.querySelector(".material-symbols-outlined:last-child") : null;

          if (otherGroups) otherGroups.classList.add("hidden");
          if (otherBtn) {
            otherBtn.className = "w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-body-md font-body-md transition-all duration-200 text-white/90 hover:bg-white/15 hover:text-white";
          }
          if (otherIcon) {
            otherIcon.classList.remove("text-dark-blue", "text-primary");
            otherIcon.classList.add("text-white/90");
          }
          if (otherChevron) {
            otherChevron.classList.remove("rotate-180", "text-dark-blue", "text-primary");
            otherChevron.classList.add("text-white/70");
          }
        }
      });

      if (isCurrentlyOpen) {
        // Toggle closed
        group.classList.remove("bg-white", "text-dark-blue", "text-primary", "rounded-xl", "p-1.5", "shadow-sm");
        if (groupsDiv) groupsDiv.classList.add("hidden");
        btn.className = "w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-body-md font-body-md transition-all duration-200 text-white/90 hover:bg-white/15 hover:text-white";
        if (mainIcon) {
          mainIcon.classList.remove("text-dark-blue", "text-primary");
          mainIcon.classList.add("text-white/90");
        }
        if (chevron) {
          chevron.classList.remove("rotate-180", "text-dark-blue", "text-primary");
          chevron.classList.add("text-white/70");
        }
      } else {
        // Expand as single active White Module Block
        group.classList.add("bg-white", "text-primary", "rounded-xl", "p-1.5", "shadow-sm");
        if (groupsDiv) groupsDiv.classList.remove("hidden");
        btn.className = "w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-body-md font-body-md transition-all duration-200 text-primary font-bold bg-transparent";
        if (mainIcon) {
          mainIcon.classList.remove("text-white/90");
          mainIcon.classList.add("text-primary");
        }
        if (chevron) {
          chevron.classList.add("rotate-180", "text-primary");
          chevron.classList.remove("text-white/70");
        }
      }
    });
  });

  // Level 2: Subgroup expand/collapse (Accordion behavior within module)
  document.querySelectorAll("[data-toggle-subgroup]").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.stopPropagation();
      const level2 = btn.closest(".nav-level-2");
      const parentContainer = level2 ? level2.closest(".nav-groups") : null;
      const subItems = level2 ? level2.querySelector(".nav-sub-items") : null;
      const chevron = btn.querySelector(".material-symbols-outlined:last-child");
      const isCurrentlyOpen = subItems && !subItems.classList.contains("hidden");

      // Accordion behavior: only one dropdown subgroup remains expanded at a time inside module
      if (parentContainer) {
        parentContainer.querySelectorAll(".nav-level-2").forEach((otherL2) => {
          if (otherL2 !== level2) {
            const otherSub = otherL2.querySelector(".nav-sub-items");
            const otherChevron = otherL2.querySelector("[data-toggle-subgroup] .material-symbols-outlined:last-child");
            if (otherSub) otherSub.classList.add("hidden");
            if (otherChevron) {
              otherChevron.classList.remove("rotate-90");
            }
          }
        });
      }

      if (subItems) {
        if (isCurrentlyOpen) {
          subItems.classList.add("hidden");
          if (chevron) {
            chevron.classList.remove("rotate-90");
          }
        } else {
          subItems.classList.remove("hidden");
          if (chevron) {
            chevron.classList.add("rotate-90");
          }
        }
      }
    });
  });

  // Global Toast Helper
  function showAppToast(message, type) {
    let container = document.getElementById("app-toast-container");
    if (!container) {
      container = document.createElement("div");
      container.id = "app-toast-container";
      container.className = "fixed bottom-5 right-5 z-[9999] flex flex-col gap-2 pointer-events-none";
      document.body.appendChild(container);
    }

    const toast = document.createElement("div");
    const isSuccess = (type === "success");
    const icon = isSuccess ? "check_circle" : "error";
    const bgClass = isSuccess ? "bg-surface-container-lowest text-on-surface border-secondary" : "bg-surface-container-lowest text-on-surface border-error";
    const iconColor = isSuccess ? "text-secondary" : "text-error";

    toast.className = `pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl border-l-4 shadow-xl text-body-md font-medium max-w-md transition-all duration-300 transform translate-y-2 opacity-0 ${bgClass}`;
    toast.innerHTML = `
      <span class="material-symbols-outlined ${iconColor} text-[22px] shrink-0">${icon}</span>
      <span class="flex-1">${message}</span>
    `;

    container.appendChild(toast);
    requestAnimationFrame(() => {
      toast.classList.remove("translate-y-2", "opacity-0");
    });

    setTimeout(() => {
      toast.classList.add("opacity-0", "translate-y-2");
      setTimeout(() => toast.remove(), 300);
    }, 4000);
  }

  // Confirmation Modal for Changing Global Active Academic Year
  function openChangeAcademicYearModal(fromYear, toYear, onConfirm, onCancel) {
    let modal = document.getElementById("change-active-year-modal");
    if (!modal) {
      modal = document.createElement("div");
      modal.id = "change-active-year-modal";
      modal.className = "fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 hidden";
      modal.innerHTML = `
        <div class="bg-surface-container-lowest rounded-2xl shadow-2xl border border-outline-variant max-w-md w-full p-6 space-y-4 animate-in fade-in zoom-in-95 duration-200">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-secondary-container text-on-secondary-container flex items-center justify-center shrink-0">
              <span class="material-symbols-outlined text-[26px] text-secondary">published_with_changes</span>
            </div>
            <div>
              <h3 class="text-headline-sm font-bold text-on-surface">Change Active Academic Year?</h3>
              <p class="text-xs font-semibold uppercase tracking-wider text-secondary">System-Wide Setting</p>
            </div>
          </div>

          <p class="text-body-md text-on-surface-variant leading-relaxed">
            You are about to change the global active academic year from:
          </p>

          <div class="flex items-center justify-between p-3.5 rounded-xl bg-surface-container-low border border-outline-variant/70">
            <div>
              <span class="text-[11px] uppercase font-bold text-on-surface-variant block">Current Active</span>
              <span id="cay-modal-from" class="text-body-lg font-bold text-on-surface"></span>
            </div>
            <span class="material-symbols-outlined text-outline text-[24px]">arrow_forward</span>
            <div>
              <span class="text-[11px] uppercase font-bold text-secondary block">New Active</span>
              <span id="cay-modal-to" class="text-body-lg font-bold text-secondary"></span>
            </div>
          </div>

          <div class="p-3 rounded-lg bg-amber-50/80 border border-amber-200/80 text-amber-900 text-xs flex items-start gap-2">
            <span class="material-symbols-outlined text-[18px] text-amber-600 shrink-0 mt-0.5">warning</span>
            <span>This will change the default active academic year across the entire school management system. Existing historical records will not be modified.</span>
          </div>

          <div class="flex items-center justify-end gap-3 pt-2">
            <button type="button" id="cay-modal-cancel-btn" class="px-4 py-2 rounded-lg border border-outline-variant text-body-md font-semibold text-on-surface hover:bg-surface-container-high transition-colors">
              Cancel
            </button>
            <button type="button" id="cay-modal-confirm-btn" class="btn-primary px-5 py-2 rounded-lg bg-button-primary text-white text-body-md font-semibold hover:bg-button-primary-hover shadow-sm transition-all flex items-center gap-2">
              <span class="btn-spinner material-symbols-outlined text-[18px] animate-spin hidden">progress_activity</span>
              <span class="btn-label">Change Academic Year</span>
            </button>
          </div>
        </div>
      `;
      document.body.appendChild(modal);
    }

    const fromEl = document.getElementById("cay-modal-from");
    const toEl = document.getElementById("cay-modal-to");
    if (fromEl) fromEl.textContent = fromYear.name;
    if (toEl) toEl.textContent = toYear.name;

    const cancelBtn = document.getElementById("cay-modal-cancel-btn");
    const confirmBtn = document.getElementById("cay-modal-confirm-btn");
    const spinner = confirmBtn.querySelector(".btn-spinner");
    const label = confirmBtn.querySelector(".btn-label");

    cancelBtn.disabled = false;
    confirmBtn.disabled = false;
    spinner.classList.add("hidden");
    label.textContent = "Change Academic Year";

    function closeModal() {
      modal.classList.add("hidden");
      cleanup();
    }

    function handleCancel() {
      closeModal();
      if (typeof onCancel === "function") onCancel();
    }

    function handleKeydown(e) {
      if (e.key === "Escape") handleCancel();
    }

    function handleConfirm() {
      cancelBtn.disabled = true;
      confirmBtn.disabled = true;
      spinner.classList.remove("hidden");
      label.textContent = "Updating...";
      if (typeof onConfirm === "function") {
        onConfirm(() => closeModal());
      }
    }

    function handleBackdropClick(e) {
      if (e.target === modal) handleCancel();
    }

    function cleanup() {
      cancelBtn.removeEventListener("click", handleCancel);
      confirmBtn.removeEventListener("click", handleConfirm);
      modal.removeEventListener("click", handleBackdropClick);
      document.removeEventListener("keydown", handleKeydown);
    }

    cancelBtn.addEventListener("click", handleCancel);
    confirmBtn.addEventListener("click", handleConfirm);
    modal.addEventListener("click", handleBackdropClick);
    document.addEventListener("keydown", handleKeydown);

    modal.classList.remove("hidden");
  }

  // Academic Year Switch Dropdown with Confirmation Dialog
  const yearSelect = document.getElementById("global-academic-year-select");
  if (yearSelect) {
    yearSelect.addEventListener("change", (e) => {
      const newYearId = parseInt(e.target.value, 10);
      const previousYearId = parseInt(yearSelect.getAttribute("data-current-year") || window.ACTIVE_ACADEMIC_YEAR_ID || window.CURRENT_ACADEMIC_YEAR_ID || 1, 10);

      if (!newYearId || isNaN(newYearId) || newYearId === previousYearId) {
        return;
      }

      const availableYears = window.AVAILABLE_ACADEMIC_YEARS || [];
      const fromObj = availableYears.find(y => Number(y.academic_year_id) === Number(previousYearId)) || { name: (window.ACTIVE_ACADEMIC_YEAR ? window.ACTIVE_ACADEMIC_YEAR.year_name : "Current") };
      const toObj = availableYears.find(y => Number(y.academic_year_id) === Number(newYearId)) || { name: "Selected" };
      const fromYearName = fromObj.year_name || fromObj.name;
      const toYearName = toObj.year_name || toObj.name;

      openChangeAcademicYearModal(
        { id: previousYearId, name: fromYearName },
        { id: newYearId, name: toYearName },
        (closeModalFn) => {
          yearSelect.disabled = true;
          yearSelect.classList.add("opacity-50", "cursor-wait");

          const postData = {
            academic_year_id: newYearId,
            redirect_url: window.location.href
          };

          if (window.CSRF_TOKEN_NAME && window.CSRF_HASH) {
            postData[window.CSRF_TOKEN_NAME] = window.CSRF_HASH;
          }

          $.ajax({
            url: url("academic-year/switch"),
            type: "POST",
            dataType: "json",
            data: postData,
            success: function (res) {
              if (res && res.csrf_hash) {
                window.CSRF_HASH = res.csrf_hash;
              }
              if (res && (res.status === true || res.status === "success")) {
                closeModalFn();
                const successMsg = `Active academic year changed to ${res.year_name || toYearName}.`;
                showAppToast(successMsg, "success");

                yearSelect.setAttribute("data-current-year", newYearId);
                window.ACTIVE_ACADEMIC_YEAR_ID = newYearId;
                window.CURRENT_ACADEMIC_YEAR_ID = newYearId;

                Array.from(yearSelect.options).forEach(opt => {
                  opt.text = opt.text.replace(/\s*\(Active\)/g, "");
                  if (Number(opt.value) === Number(newYearId)) {
                    opt.text += " (Active)";
                    opt.selected = true;
                  }
                });

                try {
                  const currentUrl = new URL(window.location.href);
                  let hasModified = false;
                  if (currentUrl.searchParams.has("date") && res.selected_date) {
                    currentUrl.searchParams.set("date", res.selected_date);
                    hasModified = true;
                  }
                  if (currentUrl.searchParams.has("from_date") && res.selected_date) {
                    currentUrl.searchParams.set("from_date", res.selected_date);
                    hasModified = true;
                  }
                  if (currentUrl.searchParams.has("to_date") && res.selected_date) {
                    currentUrl.searchParams.set("to_date", res.selected_date);
                    hasModified = true;
                  }
                  if (currentUrl.searchParams.has("academic_year_id")) {
                    currentUrl.searchParams.set("academic_year_id", res.academic_year_id);
                    hasModified = true;
                  }
                  if (hasModified) {
                    window.location.href = currentUrl.toString();
                    return;
                  }
                } catch (err) { }

                setTimeout(() => {
                  window.location.reload();
                }, 350);
              } else {
                closeModalFn();
                const errorMsg = (res && res.message) ? res.message : "Unable to change the active academic year. Please try again.";
                showAppToast(errorMsg, "error");
                yearSelect.value = previousYearId;
                yearSelect.disabled = false;
                yearSelect.classList.remove("opacity-50", "cursor-wait");
              }
            },
            error: function (xhr) {
              closeModalFn();
              let msg = "Unable to change the active academic year. Please try again.";
              try {
                const json = JSON.parse(xhr.responseText);
                if (json) {
                  if (json.csrf_hash) window.CSRF_HASH = json.csrf_hash;
                  if (json.message) msg = json.message;
                }
              } catch (e) {
                if (xhr.status === 403) msg = "You do not have permission to change the active academic year.";
                else if (xhr.status === 401) msg = "Session expired. Please log in again.";
                else if (xhr.status === 400 || xhr.status === 422) msg = "The selected academic year is invalid or inactive.";
              }
              showAppToast(msg, "error");
              yearSelect.value = previousYearId;
              yearSelect.disabled = false;
              yearSelect.classList.remove("opacity-50", "cursor-wait");
            }
          });
        },
        () => {
          // Revert selection on cancel
          yearSelect.value = previousYearId;
        }
      );
    });
  }

  // School Switch Dropdown for Super Admin
  const schoolSelect = document.getElementById("global-school-select");
  if (schoolSelect) {
    schoolSelect.addEventListener("change", (e) => {
      const newSchoolId = parseInt(e.target.value, 10);
      const previousSchoolId = parseInt(schoolSelect.getAttribute("data-current-school") || window.CURRENT_SCHOOL_ID || 1, 10);

      if (!newSchoolId || isNaN(newSchoolId) || newSchoolId === previousSchoolId) {
        return;
      }

      schoolSelect.disabled = true;
      schoolSelect.classList.add("opacity-50", "cursor-wait");

      const postData = {
        school_id: newSchoolId,
        redirect_url: window.location.href
      };

      if (window.CSRF_TOKEN_NAME && window.CSRF_HASH) {
        postData[window.CSRF_TOKEN_NAME] = window.CSRF_HASH;
      }

      $.ajax({
        url: url("settings/schools/switch"),
        type: "POST",
        dataType: "json",
        data: postData,
        success: function (res) {
          if (res && res.csrf_hash) {
            window.CSRF_HASH = res.csrf_hash;
          }
          if (res && (res.status === true || res.status === "success")) {
            showAppToast("Switching school...", "info");
            setTimeout(() => {
              window.location.reload();
            }, 300);
          } else {
            schoolSelect.disabled = false;
            schoolSelect.classList.remove("opacity-50", "cursor-wait");
            schoolSelect.value = previousSchoolId;
            showAppToast(res && res.message ? res.message : "Failed to switch school.", "error");
          }
        },
        error: function (xhr) {
          schoolSelect.disabled = false;
          schoolSelect.classList.remove("opacity-50", "cursor-wait");
          schoolSelect.value = previousSchoolId;
          let msg = "Error switching school. Please try again.";
          try {
            const json = JSON.parse(xhr.responseText);
            if (json) {
              if (json.csrf_hash) window.CSRF_HASH = json.csrf_hash;
              if (json.message) msg = json.message;
            }
          } catch (e) {
            if (xhr.status === 403) msg = "You do not have permission to switch schools.";
          }
          showAppToast(msg, "error");
        }
      });
    });
  }

  // Profile dropdown
  const profileBtn = document.getElementById("profile-menu-btn");
  const profileMenu = document.getElementById("profile-menu");
  if (profileBtn) {
    profileBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      profileMenu.classList.toggle("hidden");
    });
    document.addEventListener("click", (e) => {
      if (!profileMenu.contains(e.target)) profileMenu.classList.add("hidden");
    });
  }

  // Change Password Modal trigger
  const changePasswordBtn = document.getElementById("header-change-password-btn");
  if (changePasswordBtn) {
    changePasswordBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      if (profileMenu) profileMenu.classList.add("hidden");
      openChangePasswordModal();
    });
  }

  // Global Header Live Search
  initGlobalHeaderSearch();
}

/**
 * Interactive Change Password Modal Implementation
 */
function openChangePasswordModal() {
  let modal = document.getElementById("change-password-modal");
  if (!modal) {
    modal = document.createElement("div");
    modal.id = "change-password-modal";
    modal.className = "fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto hidden";
    modal.innerHTML = `
      <div class="bg-surface-container-lowest rounded-2xl shadow-2xl border border-outline-variant max-w-md w-full p-6 space-y-5 animate-in fade-in zoom-in-95 duration-200 my-auto text-left">
        <!-- Header -->
        <div class="flex items-start justify-between">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-light-blue text-primary border border-sky-blue/40 flex items-center justify-center shrink-0">
              <span class="material-symbols-outlined text-[24px]">lock_reset</span>
            </div>
            <div>
              <h3 class="text-headline-sm font-bold text-on-surface">Change Password</h3>
              <p class="text-xs text-on-surface-variant">Update your account password securely</p>
            </div>
          </div>
          <button type="button" id="cp-modal-close-icon" class="text-on-surface-variant/70 hover:text-on-surface p-1 rounded-lg hover:bg-surface-container-high transition-colors" title="Close">
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <!-- Alert Banner -->
        <div id="cp-modal-alert" class="hidden p-3 rounded-xl text-xs flex items-start gap-2.5 transition-all">
          <span id="cp-modal-alert-icon" class="material-symbols-outlined text-[18px] shrink-0 mt-0.5"></span>
          <span id="cp-modal-alert-msg" class="flex-1 font-medium"></span>
        </div>

        <!-- Form -->
        <form id="cp-modal-form" class="space-y-4" autocomplete="off" onsubmit="return false;">
          <!-- Current Password -->
          <div>
            <label for="cp-current-password" class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Current Password <span class="text-error">*</span></label>
            <div class="relative">
              <input type="password" id="cp-current-password" name="current_password" required placeholder="Enter current password" class="w-full pl-3.5 pr-10 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all font-mono" autocomplete="current-password" />
              <button type="button" class="cp-toggle-pwd absolute right-2.5 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface p-1 rounded transition-colors" data-target="cp-current-password" title="Show / Hide password">
                <span class="material-symbols-outlined text-[18px]">visibility</span>
              </button>
            </div>
          </div>

          <!-- New Password -->
          <div>
            <label for="cp-new-password" class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">New Password <span class="text-error">*</span></label>
            <div class="relative">
              <input type="password" id="cp-new-password" name="new_password" required placeholder="Enter new password" class="w-full pl-3.5 pr-10 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all font-mono" autocomplete="new-password" />
              <button type="button" class="cp-toggle-pwd absolute right-2.5 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface p-1 rounded transition-colors" data-target="cp-new-password" title="Show / Hide password">
                <span class="material-symbols-outlined text-[18px]">visibility</span>
              </button>
            </div>
            
            <!-- Strength Meter Bar -->
            <div class="mt-2 space-y-1.5">
              <div class="flex items-center justify-between text-[11px]">
                <span class="text-on-surface-variant font-medium">Password Strength:</span>
                <span id="cp-strength-text" class="font-bold text-on-surface-variant">None</span>
              </div>
              <div class="grid grid-cols-4 gap-1.5 h-1.5 w-full bg-surface-container-high rounded-full overflow-hidden p-0.5">
                <div id="cp-strength-bar-1" class="h-full rounded-full transition-all duration-300 bg-transparent"></div>
                <div id="cp-strength-bar-2" class="h-full rounded-full transition-all duration-300 bg-transparent"></div>
                <div id="cp-strength-bar-3" class="h-full rounded-full transition-all duration-300 bg-transparent"></div>
                <div id="cp-strength-bar-4" class="h-full rounded-full transition-all duration-300 bg-transparent"></div>
              </div>
            </div>

            <!-- Rules Checklist -->
            <div class="mt-3 p-3 rounded-xl bg-surface-container-low border border-outline-variant/60 space-y-1.5 text-[11px] text-on-surface-variant">
              <div id="cp-rule-len" class="flex items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-[15px]">radio_button_unchecked</span>
                <span>At least 8 characters</span>
              </div>
              <div id="cp-rule-num" class="flex items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-[15px]">radio_button_unchecked</span>
                <span>At least 1 number (0-9)</span>
              </div>
              <div id="cp-rule-special" class="flex items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-[15px]">radio_button_unchecked</span>
                <span>At least 1 special character (e.g. !@#$%^&*)</span>
              </div>
              <div id="cp-rule-diff" class="flex items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-[15px]">radio_button_unchecked</span>
                <span>Different from current password</span>
              </div>
            </div>
          </div>

          <!-- Confirm New Password -->
          <div>
            <label for="cp-confirm-password" class="block font-label-md text-label-md text-on-surface mb-1.5 font-medium">Confirm New Password <span class="text-error">*</span></label>
            <div class="relative">
              <input type="password" id="cp-confirm-password" name="confirm_password" required placeholder="Confirm new password" class="w-full pl-3.5 pr-10 py-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-body-md text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all font-mono" autocomplete="new-password" />
              <button type="button" class="cp-toggle-pwd absolute right-2.5 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface p-1 rounded transition-colors" data-target="cp-confirm-password" title="Show / Hide password">
                <span class="material-symbols-outlined text-[18px]">visibility</span>
              </button>
            </div>
            <div id="cp-match-status" class="hidden mt-1.5 text-[11px] font-medium flex items-center gap-1">
              <span id="cp-match-icon" class="material-symbols-outlined text-[14px]"></span>
              <span id="cp-match-text"></span>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center justify-end gap-3 pt-3 border-t border-outline-variant/60">
            <button type="button" id="cp-modal-cancel-btn" class="px-4 py-2 rounded-lg border border-outline-variant text-body-md font-semibold text-on-surface hover:bg-surface-container-high transition-colors">
              Cancel
            </button>
            <button type="submit" id="cp-modal-submit-btn" class="btn-primary px-5 py-2 rounded-lg bg-button-primary text-white text-body-md font-semibold hover:bg-button-primary-hover shadow-sm transition-all flex items-center gap-2">
              <span class="btn-spinner material-symbols-outlined text-[18px] animate-spin hidden">progress_activity</span>
              <span class="btn-label">Change Password</span>
            </button>
          </div>
        </form>
      </div>
    `;
    document.body.appendChild(modal);

    // Wire up show/hide password toggles
    modal.querySelectorAll(".cp-toggle-pwd").forEach((btn) => {
      btn.addEventListener("click", (e) => {
        e.preventDefault();
        const targetId = btn.getAttribute("data-target");
        const input = document.getElementById(targetId);
        if (input) {
          const isPwd = input.type === "password";
          input.type = isPwd ? "text" : "password";
          const icon = btn.querySelector(".material-symbols-outlined");
          if (icon) icon.textContent = isPwd ? "visibility_off" : "visibility";
        }
      });
    });

    // Wire up inputs for real-time validation & strength
    const curInput = document.getElementById("cp-current-password");
    const newInput = document.getElementById("cp-new-password");
    const confInput = document.getElementById("cp-confirm-password");
    const matchStatus = document.getElementById("cp-match-status");
    const matchIcon = document.getElementById("cp-match-icon");
    const matchText = document.getElementById("cp-match-text");

    function updateModalRule(elId, passed) {
      const el = document.getElementById(elId);
      if (!el) return;
      const icon = el.querySelector(".material-symbols-outlined");
      if (passed) {
        el.className = "flex items-center gap-1.5 transition-colors text-[#1e7e34] font-medium";
        if (icon) {
          icon.textContent = "check_circle";
          icon.className = "material-symbols-outlined text-[15px] text-[#1e7e34]";
        }
      } else {
        el.className = "flex items-center gap-1.5 transition-colors text-on-surface-variant";
        if (icon) {
          icon.textContent = "radio_button_unchecked";
          icon.className = "material-symbols-outlined text-[15px]";
        }
      }
    }

    function renderModalStrength(score, len) {
      const txt = document.getElementById("cp-strength-text");
      const b1 = document.getElementById("cp-strength-bar-1");
      const b2 = document.getElementById("cp-strength-bar-2");
      const b3 = document.getElementById("cp-strength-bar-3");
      const b4 = document.getElementById("cp-strength-bar-4");
      const bars = [b1, b2, b3, b4];

      bars.forEach(b => {
        b.className = "h-full rounded-full transition-all duration-300 bg-transparent";
      });

      if (len === 0) {
        txt.textContent = "None";
        txt.className = "font-bold text-on-surface-variant";
        return;
      }

      if (score <= 1) {
        txt.textContent = "Weak";
        txt.className = "font-bold text-error";
        b1.classList.remove("bg-transparent");
        b1.classList.add("bg-error");
      } else if (score === 2) {
        txt.textContent = "Fair";
        txt.className = "font-bold text-amber-500";
        b1.classList.remove("bg-transparent");
        b1.classList.add("bg-amber-500");
        b2.classList.remove("bg-transparent");
        b2.classList.add("bg-amber-500");
      } else if (score === 3) {
        txt.textContent = "Good";
        txt.className = "font-bold text-primary";
        b1.classList.remove("bg-transparent");
        b1.classList.add("bg-primary");
        b2.classList.remove("bg-transparent");
        b2.classList.add("bg-primary");
        b3.classList.remove("bg-transparent");
        b3.classList.add("bg-primary");
      } else {
        txt.textContent = "Strong";
        txt.className = "font-bold text-[#1e7e34]";
        bars.forEach(b => {
          b.classList.remove("bg-transparent");
          b.classList.add("bg-[#1e7e34]");
        });
      }
    }

    function validateModalInputs() {
      const curVal = curInput.value;
      const newVal = newInput.value;
      const confVal = confInput.value;

      const hasLen = newVal.length >= 8;
      const hasNum = /[0-9]/.test(newVal);
      const hasSpecial = /[^a-zA-Z0-9]/.test(newVal);
      const isDiff = newVal.length > 0 && curVal.length > 0 && newVal !== curVal;

      updateModalRule("cp-rule-len", hasLen);
      updateModalRule("cp-rule-num", hasNum);
      updateModalRule("cp-rule-special", hasSpecial);
      updateModalRule("cp-rule-diff", isDiff || (newVal.length > 0 && curVal.length === 0));

      let score = 0;
      if (hasLen) score++;
      if (newVal.length >= 12 || (hasNum && hasSpecial)) score++;
      if (hasNum) score++;
      if (hasSpecial) score++;

      renderModalStrength(score, newVal.length);

      if (confVal.length > 0) {
        matchStatus.classList.remove("hidden");
        if (newVal === confVal) {
          matchStatus.className = "mt-1.5 text-[11px] font-medium flex items-center gap-1 text-[#1e7e34]";
          matchIcon.textContent = "check_circle";
          matchText.textContent = "Passwords match";
        } else {
          matchStatus.className = "mt-1.5 text-[11px] font-medium flex items-center gap-1 text-error";
          matchIcon.textContent = "cancel";
          matchText.textContent = "New password and confirm password do not match.";
        }
      } else {
        matchStatus.classList.add("hidden");
      }
    }

    curInput.addEventListener("input", validateModalInputs);
    newInput.addEventListener("input", validateModalInputs);
    confInput.addEventListener("input", validateModalInputs);

    // Helper to show alert in modal
    function showModalAlert(message, type) {
      const alertBox = document.getElementById("cp-modal-alert");
      const icon = document.getElementById("cp-modal-alert-icon");
      const msg = document.getElementById("cp-modal-alert-msg");
      if (!alertBox || !icon || !msg) return;

      const isError = (type === "error");
      alertBox.className = isError
        ? "p-3 rounded-xl text-xs flex items-start gap-2.5 transition-all bg-error-container/40 text-on-error-container border border-error/30"
        : "p-3 rounded-xl text-xs flex items-start gap-2.5 transition-all bg-secondary-container/40 text-on-secondary-container border border-secondary/30";
      icon.textContent = isError ? "error" : "check_circle";
      icon.className = isError ? "material-symbols-outlined text-[18px] text-error shrink-0 mt-0.5" : "material-symbols-outlined text-[18px] text-secondary shrink-0 mt-0.5";
      msg.textContent = message;
      alertBox.classList.remove("hidden");
    }

    function hideModalAlert() {
      const alertBox = document.getElementById("cp-modal-alert");
      if (alertBox) alertBox.classList.add("hidden");
    }

    // Submission handler
    const form = document.getElementById("cp-modal-form");
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      hideModalAlert();

      const curVal = curInput.value.trim();
      const newVal = newInput.value;
      const confVal = confInput.value;

      if (!curVal) {
        showModalAlert("Current password is required.", "error");
        curInput.focus();
        return;
      }
      if (!newVal) {
        showModalAlert("New password is required.", "error");
        newInput.focus();
        return;
      }
      if (!confVal) {
        showModalAlert("Please confirm your new password.", "error");
        confInput.focus();
        return;
      }
      if (newVal !== confVal) {
        showModalAlert("New password and confirm password do not match.", "error");
        confInput.focus();
        return;
      }
      if (newVal === curVal) {
        showModalAlert("New password cannot be the same as the current password.", "error");
        newInput.focus();
        return;
      }
      if (newVal.length < 8) {
        showModalAlert("New password must be at least 8 characters long.", "error");
        newInput.focus();
        return;
      }

      const submitBtn = document.getElementById("cp-modal-submit-btn");
      const cancelBtn = document.getElementById("cp-modal-cancel-btn");
      const spinner = submitBtn.querySelector(".btn-spinner");
      const label = submitBtn.querySelector(".btn-label");

      submitBtn.disabled = true;
      cancelBtn.disabled = true;
      spinner.classList.remove("hidden");
      label.textContent = "Updating...";

      const postData = {
        current_password: curVal,
        new_password: newVal,
        confirm_password: confVal
      };

      if (window.CSRF_TOKEN_NAME && window.CSRF_HASH) {
        postData[window.CSRF_TOKEN_NAME] = window.CSRF_HASH;
      }

      $.ajax({
        url: url("change-password"),
        type: "POST",
        dataType: "json",
        data: postData,
        success: function (res) {
          if (res && res.csrf_hash) {
            window.CSRF_HASH = res.csrf_hash;
          }
          submitBtn.disabled = false;
          cancelBtn.disabled = false;
          spinner.classList.add("hidden");
          label.textContent = "Change Password";

          if (res && (res.status === true || res.status === "success")) {
            showAppToast(res.message || "Password changed successfully.", "success");
            curInput.value = "";
            newInput.value = "";
            confInput.value = "";
            validateModalInputs();
            closeChangePasswordModal();
          } else {
            showModalAlert(res && res.message ? res.message : "Failed to change password.", "error");
          }
        },
        error: function (xhr) {
          submitBtn.disabled = false;
          cancelBtn.disabled = false;
          spinner.classList.add("hidden");
          label.textContent = "Change Password";

          let msg = "An error occurred while changing password. Please try again.";
          try {
            const json = JSON.parse(xhr.responseText);
            if (json) {
              if (json.csrf_hash) window.CSRF_HASH = json.csrf_hash;
              if (json.message) msg = json.message;
            }
          } catch (e) {
            if (xhr.status === 401 || xhr.status === 403) {
              msg = "Authentication error. Please re-login.";
            }
          }
          showModalAlert(msg, "error");
        }
      });
    });

    // Close logic
    function closeChangePasswordModal() {
      modal.classList.add("hidden");
      curInput.value = "";
      newInput.value = "";
      confInput.value = "";
      modal.querySelectorAll(".cp-toggle-pwd").forEach(b => {
        const icon = b.querySelector(".material-symbols-outlined");
        if (icon) icon.textContent = "visibility";
      });
      curInput.type = "password";
      newInput.type = "password";
      confInput.type = "password";
      hideModalAlert();
      validateModalInputs();
    }

    document.getElementById("cp-modal-cancel-btn").addEventListener("click", closeChangePasswordModal);
    document.getElementById("cp-modal-close-icon").addEventListener("click", closeChangePasswordModal);
    modal.addEventListener("click", (e) => {
      if (e.target === modal) closeChangePasswordModal();
    });
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && !modal.classList.contains("hidden")) {
        closeChangePasswordModal();
      }
    });

    modal._close = closeChangePasswordModal;
  }

  // Reset & Open
  const alertBox = document.getElementById("cp-modal-alert");
  if (alertBox) alertBox.classList.add("hidden");
  document.getElementById("cp-current-password").value = "";
  document.getElementById("cp-new-password").value = "";
  document.getElementById("cp-confirm-password").value = "";
  modal.classList.remove("hidden");
  setTimeout(() => {
    document.getElementById("cp-current-password").focus();
  }, 100);
}

/**
 * Global Header Live Search Implementation
 */
function initGlobalHeaderSearch() {
  const container = document.getElementById("global-search-container");
  const input = document.getElementById("global-search-input");
  const clearBtn = document.getElementById("global-search-clear-btn");
  const dropdown = document.getElementById("global-search-dropdown");

  if (!container || !input || !dropdown) return;

  let debounceTimer = null;
  let currentAbortController = null;
  let highlightedIndex = -1;
  let lastSearchedQuery = "";
  let currentResults = null;

  function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function setDropdownVisible(visible) {
    if (visible) {
      dropdown.classList.remove("hidden");
    } else {
      dropdown.classList.add("hidden");
      highlightedIndex = -1;
    }
  }

  function getFocusableItems() {
    return Array.from(dropdown.querySelectorAll(".search-result-item, .search-view-all-btn"));
  }

  function highlightItem(index) {
    const items = getFocusableItems();
    if (!items.length) return;
    if (index < 0) index = items.length - 1;
    if (index >= items.length) index = 0;
    highlightedIndex = index;

    items.forEach((item, idx) => {
      if (idx === highlightedIndex) {
        item.classList.add("bg-surface-container-high");
        item.scrollIntoView({ block: "nearest" });
      } else {
        item.classList.remove("bg-surface-container-high");
      }
    });
  }

  function renderLoading() {
    dropdown.innerHTML = `
      <div class="p-6 text-center space-y-2">
        <div class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary animate-spin">
          <span class="material-symbols-outlined text-[20px]">sync</span>
        </div>
        <div class="text-body-md font-medium text-on-surface">Searching school records...</div>
        <div class="text-xs text-on-surface-variant">Searching across students, staff, classes &amp; subjects</div>
      </div>
    `;
    setDropdownVisible(true);
  }

  function renderError(msg) {
    dropdown.innerHTML = `
      <div class="p-5 text-center space-y-1">
        <span class="material-symbols-outlined text-error text-[24px]">error</span>
        <div class="text-body-md font-medium text-on-surface">${escapeHtml(msg || "Unable to search right now. Please try again.")}</div>
        <div class="text-xs text-on-surface-variant">Please check your connection or try a different term.</div>
      </div>
    `;
    setDropdownVisible(true);
  }

  function renderNoResults(query) {
    const qEsc = escapeHtml(query);
    dropdown.innerHTML = `
      <div class="p-5 text-center space-y-3">
        <div class="w-11 h-11 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center mx-auto">
          <span class="material-symbols-outlined text-[24px]">search_off</span>
        </div>
        <div>
          <div class="text-body-md font-bold text-on-surface">No results found</div>
          <div class="text-xs text-on-surface-variant mt-0.5">No school records matched "${qEsc}"</div>
        </div>
        <div class="text-left bg-surface-container-low p-3 rounded-xl border border-outline-variant/60 text-[11px] text-on-surface-variant space-y-1">
          <div class="font-semibold text-on-surface flex items-center gap-1">
            <span class="material-symbols-outlined text-[14px] text-primary">lightbulb</span>
            <span>Try searching by:</span>
          </div>
          <p>• Student name, admission number (e.g. EDU2026132), roll number</p>
          <p>• Student, guardian, or staff phone number</p>
          <p>• Staff name, teacher employee code (e.g. TCH2026001)</p>
          <p>• Class name (e.g. Grade 10) or subject</p>
        </div>
      </div>
    `;
    setDropdownVisible(true);
  }

  function renderResults(data, query) {
    currentResults = data;
    highlightedIndex = -1;

    if (!data.status || data.total_results === 0) {
      renderNoResults(query);
      return;
    }

    let html = `<div class="overflow-y-auto max-h-[60vh] divide-y divide-outline-variant/40 custom-scrollbar">`;

    // 1. STUDENTS
    if (data.categories.students && data.categories.students.items && data.categories.students.items.length > 0) {
      html += `
        <div class="p-2.5">
          <div class="px-2 py-1 text-[11px] font-bold uppercase tracking-wider text-on-surface-variant flex items-center justify-between">
            <span class="flex items-center gap-1.5 text-primary">
              <span class="material-symbols-outlined text-[16px]">school</span>
              <span>Students</span>
            </span>
            <span class="text-[10px] px-1.5 py-0.2 rounded-full bg-primary/10 text-primary font-bold">${data.categories.students.count}</span>
          </div>
          <div class="mt-1 space-y-0.5">
      `;

      data.categories.students.items.forEach(st => {
        html += `
          <a href="${st.url}" class="search-result-item flex items-center gap-3 p-2 rounded-xl hover:bg-surface-container-high transition-colors group">
            <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary font-bold flex items-center justify-center shrink-0 text-xs">
              <span class="material-symbols-outlined text-[18px]">person</span>
            </div>
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-1.5">
                <span class="text-body-md font-semibold text-on-surface truncate group-hover:text-primary transition-colors">${escapeHtml(st.title)}</span>
                ${st.is_active_year ? '<span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-secondary-container text-on-secondary-container shrink-0">Active</span>' : ''}
              </div>
              <div class="text-[11px] text-on-surface-variant flex items-center gap-2 truncate">
                <span class="font-mono text-on-surface/80">Adm: ${escapeHtml(st.admission_number)}</span>
                ${st.roll_number ? `<span>•</span><span>Roll: <strong>${escapeHtml(st.roll_number)}</strong></span>` : ''}
                <span>•</span>
                <span>${escapeHtml(st.class_name)}</span>
              </div>
            </div>
            <span class="material-symbols-outlined text-[16px] text-on-surface-variant/40 group-hover:text-primary group-hover:translate-x-0.5 transition-all">chevron_right</span>
          </a>
        `;
      });

      html += `</div></div>`;
    }

    // 2. STAFF
    if (data.categories.staff && data.categories.staff.items && data.categories.staff.items.length > 0) {
      html += `
        <div class="p-2.5">
          <div class="px-2 py-1 text-[11px] font-bold uppercase tracking-wider text-on-surface-variant flex items-center justify-between">
            <span class="flex items-center gap-1.5 text-secondary">
              <span class="material-symbols-outlined text-[16px]">badge</span>
              <span>Staff / Teachers</span>
            </span>
            <span class="text-[10px] px-1.5 py-0.2 rounded-full bg-secondary-container text-on-secondary-container font-bold">${data.categories.staff.count}</span>
          </div>
          <div class="mt-1 space-y-0.5">
      `;

      data.categories.staff.items.forEach(stf => {
        html += `
          <a href="${stf.url}" class="search-result-item flex items-center gap-3 p-2 rounded-xl hover:bg-surface-container-high transition-colors group">
            <div class="w-8 h-8 rounded-lg bg-secondary-container text-on-secondary-container font-bold flex items-center justify-center shrink-0 text-xs">
              <span class="material-symbols-outlined text-[18px]">badge</span>
            </div>
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-1.5">
                <span class="text-body-md font-semibold text-on-surface truncate group-hover:text-primary transition-colors">${escapeHtml(stf.title)}</span>
                <span class="px-1.5 py-0.2 rounded text-[9px] font-semibold bg-surface-container-high text-on-surface-variant shrink-0">${escapeHtml(stf.role)}</span>
              </div>
              <div class="text-[11px] text-on-surface-variant flex items-center gap-2 truncate">
                <span class="font-mono text-on-surface/80">Code: ${escapeHtml(stf.employee_code)}</span>
              </div>
            </div>
            <span class="material-symbols-outlined text-[16px] text-on-surface-variant/40 group-hover:text-primary group-hover:translate-x-0.5 transition-all">chevron_right</span>
          </a>
        `;
      });

      html += `</div></div>`;
    }

    // 3. CLASSES & DIVISIONS
    if (data.categories.classes && data.categories.classes.items && data.categories.classes.items.length > 0) {
      html += `
        <div class="p-2.5">
          <div class="px-2 py-1 text-[11px] font-bold uppercase tracking-wider text-on-surface-variant flex items-center justify-between">
            <span class="flex items-center gap-1.5 text-primary">
              <span class="material-symbols-outlined text-[16px]">meeting_room</span>
              <span>Classes &amp; Divisions</span>
            </span>
            <span class="text-[10px] px-1.5 py-0.2 rounded-full bg-light-blue text-primary font-bold border border-sky-blue/50">${data.categories.classes.count}</span>
          </div>
          <div class="mt-1 space-y-0.5">
      `;

      data.categories.classes.items.forEach(cls => {
        html += `
          <a href="${cls.url}" class="search-result-item flex items-center gap-3 p-2 rounded-xl hover:bg-light-blue/50 transition-colors group">
            <div class="w-8 h-8 rounded-lg bg-light-blue text-primary font-bold flex items-center justify-center shrink-0 text-xs border border-sky-blue/40">
              <span class="material-symbols-outlined text-[18px]">meeting_room</span>
            </div>
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-1.5">
                <span class="text-body-md font-semibold text-on-surface truncate group-hover:text-primary transition-colors">${escapeHtml(cls.title)}</span>
                ${cls.is_active_year ? '<span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-secondary-container text-on-secondary-container shrink-0">Active</span>' : ''}
              </div>
              <div class="text-[11px] text-on-surface-variant flex items-center gap-2 truncate">
                <span class="font-mono text-on-surface/80">Code: ${escapeHtml(cls.class_code || 'N/A')}</span>
                <span>•</span>
                <span>Divisions: <strong>${escapeHtml(cls.divisions)}</strong></span>
              </div>
            </div>
            <span class="material-symbols-outlined text-[16px] text-on-surface-variant/40 group-hover:text-primary group-hover:translate-x-0.5 transition-all">chevron_right</span>
          </a>
        `;
      });

      html += `</div></div>`;
    }

    // 4. SUBJECTS
    if (data.categories.subjects && data.categories.subjects.items && data.categories.subjects.items.length > 0) {
      html += `
        <div class="p-2.5">
          <div class="px-2 py-1 text-[11px] font-bold uppercase tracking-wider text-on-surface-variant flex items-center justify-between">
            <span class="flex items-center gap-1.5 text-amber-600">
              <span class="material-symbols-outlined text-[16px]">menu_book</span>
              <span>Subjects</span>
            </span>
            <span class="text-[10px] px-1.5 py-0.2 rounded-full bg-amber-100 text-amber-800 font-bold">${data.categories.subjects.count}</span>
          </div>
          <div class="mt-1 space-y-0.5">
      `;

      data.categories.subjects.items.forEach(sub => {
        html += `
          <a href="${sub.url}" class="search-result-item flex items-center gap-3 p-2 rounded-xl hover:bg-surface-container-high transition-colors group">
            <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 font-bold flex items-center justify-center shrink-0 text-xs">
              <span class="material-symbols-outlined text-[18px]">menu_book</span>
            </div>
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-1.5">
                <span class="text-body-md font-semibold text-on-surface truncate group-hover:text-primary transition-colors">${escapeHtml(sub.title)}</span>
                <span class="px-1.5 py-0.2 rounded text-[9px] font-semibold bg-surface-container-high text-on-surface-variant shrink-0">${escapeHtml(sub.subject_type || 'Subject')}</span>
              </div>
              <div class="text-[11px] text-on-surface-variant flex items-center gap-2 truncate">
                <span class="font-mono text-on-surface/80">Code: ${escapeHtml(sub.subject_code)}</span>
                <span>•</span>
                <span>Class: ${escapeHtml(sub.class_name)}</span>
              </div>
            </div>
            <span class="material-symbols-outlined text-[16px] text-on-surface-variant/40 group-hover:text-primary group-hover:translate-x-0.5 transition-all">chevron_right</span>
          </a>
        `;
      });

      html += `</div></div>`;
    }

    html += `</div>`;

    // Footer: View All Results
    html += `
      <div class="p-2.5 bg-surface-container-low border-t border-outline-variant/60 flex items-center justify-between">
        <span class="text-[11px] text-on-surface-variant font-medium">${data.total_results} record${data.total_results === 1 ? '' : 's'} found</span>
        <a href="${data.view_all_url}" class="search-view-all-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-primary hover:bg-primary-hover text-white text-xs font-semibold shadow-sm transition-all group">
          <span>View all results</span>
          <span class="material-symbols-outlined text-[14px] group-hover:translate-x-0.5 transition-transform">arrow_forward</span>
        </a>
      </div>
    `;

    dropdown.innerHTML = html;
    setDropdownVisible(true);
  }

  function doSearch(q) {
    const term = q.trim();
    if (term.length < 2) {
      setDropdownVisible(false);
      lastSearchedQuery = "";
      return;
    }

    if (currentAbortController) {
      currentAbortController.abort();
    }

    lastSearchedQuery = term;
    renderLoading();

    currentAbortController = new AbortController();
    const searchUrl = url("search/global") + "?q=" + encodeURIComponent(term);

    fetch(searchUrl, {
      method: "GET",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
        "Accept": "application/json"
      },
      signal: currentAbortController.signal
    })
      .then((res) => {
        if (!res.ok) {
          throw new Error("HTTP " + res.status);
        }
        return res.json();
      })
      .then((data) => {
        renderResults(data, term);
      })
      .catch((err) => {
        if (err.name === "AbortError") return;
        renderError("Unable to search right now. Please try again.");
      });
  }

  // Input event (debounced 300ms)
  input.addEventListener("input", () => {
    const val = input.value;
    if (val.trim().length > 0) {
      clearBtn.classList.remove("hidden");
    } else {
      clearBtn.classList.add("hidden");
    }

    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      doSearch(val);
    }, 300);
  });

  // Focus event: reopen if query exists
  input.addEventListener("focus", () => {
    const val = input.value.trim();
    if (val.length >= 2) {
      if (dropdown.children.length > 0 && lastSearchedQuery === val) {
        setDropdownVisible(true);
      } else {
        doSearch(val);
      }
    }
  });

  // Clear button
  clearBtn.addEventListener("click", () => {
    input.value = "";
    clearBtn.classList.add("hidden");
    setDropdownVisible(false);
    input.focus();
  });

  // Click outside to close
  document.addEventListener("click", (e) => {
    if (!container.contains(e.target)) {
      setDropdownVisible(false);
    }
  });

  // Keyboard navigation
  input.addEventListener("keydown", (e) => {
    const items = getFocusableItems();
    const isVisible = !dropdown.classList.contains("hidden");

    if (e.key === "ArrowDown") {
      if (!isVisible && input.value.trim().length >= 2) {
        doSearch(input.value);
        return;
      }
      e.preventDefault();
      highlightItem(highlightedIndex + 1);
    } else if (e.key === "ArrowUp") {
      if (!isVisible) return;
      e.preventDefault();
      highlightItem(highlightedIndex - 1);
    } else if (e.key === "Enter") {
      e.preventDefault();
      if (isVisible && highlightedIndex >= 0 && items[highlightedIndex]) {
        items[highlightedIndex].click();
      } else if (input.value.trim().length >= 2) {
        // Direct to best match or full search results page
        if (isVisible && items.length > 0) {
          items[0].click();
        } else {
          window.location.href = url("search") + "?q=" + encodeURIComponent(input.value.trim());
        }
      }
    } else if (e.key === "Escape") {
      setDropdownVisible(false);
      input.blur();
    }
  });
}

// ---- Small reusable UI helpers used inline by pages ----
function badge(status) {
  const map = {
    Active: "bg-secondary-container text-on-secondary-container",
    Inactive: "bg-surface-container-high text-on-surface-variant",
    Pending: "bg-tertiary-container/10 text-on-tertiary-container",
    Present: "bg-secondary-container text-on-secondary-container",
    Absent: "bg-error-container text-on-error-container",
    Late: "bg-tertiary-container/10 text-on-tertiary-container",
    Overdue: "bg-error-container text-on-error-container",
    Paid: "bg-secondary-container text-on-secondary-container",
    Archived: "bg-surface-container-high text-on-surface-variant",
  };
  const cls = map[status] || "bg-surface-container-high text-on-surface-variant";
  return `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold ${cls}">${status}</span>`;
}

// =============================================================================
// School Application Global Namespace
// =============================================================================
window.School = window.School || {};

// 1. Centralized AJAX Helper
School.ajax = {
  request: function (options) {
    const config = Object.assign({
      url: '',
      method: 'POST',
      data: {},
      dataType: 'json',
      beforeSend: null,
      success: null,
      error: null,
      complete: null,
      showLoading: false
    }, options);

    if (typeof jQuery === 'undefined') {
      console.error('jQuery is required for School.ajax');
      return;
    }

    // Attach CSRF token if POST/PUT/DELETE and not already present
    if (typeof config.data === 'object' && config.method.toUpperCase() !== 'GET') {
      if (window.CSRF_TOKEN_NAME && window.CSRF_HASH && !config.data[window.CSRF_TOKEN_NAME]) {
        config.data[window.CSRF_TOKEN_NAME] = window.CSRF_HASH;
      }
    }

    return jQuery.ajax({
      url: config.url,
      type: config.method,
      data: config.data,
      dataType: config.dataType,
      beforeSend: function (xhr, settings) {
        if (config.showLoading) {
          School.ui.showLoading();
        }
        if (typeof config.beforeSend === 'function') {
          config.beforeSend(xhr, settings);
        }
      },
      success: function (response, status, xhr) {
        if (response && response.csrf_hash) {
          window.CSRF_HASH = response.csrf_hash;
        }
        if (typeof config.success === 'function') {
          config.success(response, status, xhr);
        }
      },
      error: function (xhr, status, error) {
        let msg = 'An unexpected error occurred. Please try again.';
        if (xhr.status === 403) {
          msg = 'Access Denied: You do not have permission for this action.';
        } else if (xhr.status === 401) {
          msg = 'Session expired. Please log in again.';
        } else if (xhr.status === 404) {
          msg = 'The requested resource was not found.';
        } else if (xhr.status === 500) {
          msg = 'A server error occurred. Please contact the administrator.';
        }
        try {
          const res = xhr.responseJSON || JSON.parse(xhr.responseText);
          if (res && res.csrf_hash) window.CSRF_HASH = res.csrf_hash;
          if (res && res.message) msg = res.message;
        } catch (e) { }

        if (typeof config.error === 'function') {
          config.error(xhr, status, error, msg);
        } else {
          alert(msg);
        }
      },
      complete: function (xhr, status) {
        if (config.showLoading) {
          School.ui.hideLoading();
        }
        if (typeof config.complete === 'function') {
          config.complete(xhr, status);
        }
      }
    });
  }
};

// 2. Centralized DataTables Helper
School.DataTable = {
  init: function (selector, options) {
    if (typeof jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') {
      return null;
    }

    const defaultOptions = {
      responsive: true,
      pageLength: 25,
      lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
      language: {
        search: "_INPUT_",
        searchPlaceholder: "Search records...",
        lengthMenu: "Show _MENU_ entries",
        info: "Showing _START_ to _END_ of _TOTAL_ entries",
        infoEmpty: "Showing 0 to 0 of 0 entries",
        infoFiltered: "(filtered from _MAX_ total records)",
        emptyTable: "No matching records found",
        processing: '<div class="flex items-center justify-center gap-2 py-3"><span class="material-symbols-outlined animate-spin text-[20px]">sync</span> Loading records...</div>'
      }
    };

    const merged = jQuery.extend(true, {}, defaultOptions, options);

    // Destroy previous instance if already initialized on this selector
    if (jQuery.fn.DataTable.isDataTable(selector)) {
      jQuery(selector).DataTable().destroy();
    }

    return jQuery(selector).DataTable(merged);
  },

  serverSide: function (selector, options) {
    if (typeof jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') {
      return null;
    }

    const ajaxUrl = options.ajaxUrl || (typeof options.ajax === 'string' ? options.ajax : (options.ajax && options.ajax.url ? options.ajax.url : ''));
    let ajaxConfig = typeof options.ajax === 'object' ? options.ajax : {};
    if (ajaxUrl && !ajaxConfig.url) {
      ajaxConfig.url = ajaxUrl;
    }

    const originalDataFn = typeof ajaxConfig.data === 'function' ? ajaxConfig.data : null;
    const originalDataObj = typeof ajaxConfig.data === 'object' ? ajaxConfig.data : {};
    const extraDataFn = typeof options.extraData === 'function' ? options.extraData : null;

    const mergedAjax = jQuery.extend(true, {}, ajaxConfig, {
      url: ajaxConfig.url || ajaxUrl,
      type: ajaxConfig.type || 'POST',
      data: function (d) {
        if (window.CSRF_TOKEN_NAME && window.CSRF_HASH) {
          d[window.CSRF_TOKEN_NAME] = window.CSRF_HASH;
        }
        jQuery.extend(d, originalDataObj);
        if (originalDataFn) {
          originalDataFn(d);
        }
        if (extraDataFn) {
          extraDataFn(d);
        }
      },
      dataSrc: function (json) {
        if (json && json.csrf_hash) {
          window.CSRF_HASH = json.csrf_hash;
        }
        if (typeof ajaxConfig.dataSrc === 'function') {
          return ajaxConfig.dataSrc(json);
        }
        return json.data || [];
      },
      error: function (xhr, error, thrown) {
        console.error('DataTables AJAX error:', error, thrown);
        if (typeof showAppToast === 'function') {
          showAppToast('Failed to load table data. Please refresh.', 'error');
        }
      }
    });

    const serverSideOptions = jQuery.extend(true, {}, options, {
      processing: true,
      serverSide: true,
      ajax: mergedAjax,
      searchDelay: options.searchDelay || 400
    });

    return School.DataTable.init(selector, serverSideOptions);
  }
};

// 3. UI Helpers
School.ui = {
  badge: badge,
  showLoading: function () {
    let loader = document.getElementById('school-global-loader');
    if (!loader) {
      loader = document.createElement('div');
      loader.id = 'school-global-loader';
      loader.className = 'fixed inset-0 bg-primary/20 backdrop-blur-xs flex items-center justify-center z-50';
      loader.innerHTML = '<div class="bg-surface-container-lowest p-4 rounded-xl shadow-xl flex items-center gap-3 border border-outline-variant/60 font-medium text-body-md text-on-surface"><span class="material-symbols-outlined animate-spin text-secondary text-[24px]">sync</span> Processing request...</div>';
      document.body.appendChild(loader);
    }
    loader.classList.remove('hidden');
  },
  hideLoading: function () {
    const loader = document.getElementById('school-global-loader');
    if (loader) {
      loader.classList.add('hidden');
    }
  }
};

// 4. Duplicate Form Submission Prevention
document.addEventListener("submit", function (e) {
  const form = e.target;
  if (form && form.tagName === 'FORM' && !form.dataset.submitting && !form.hasAttribute('data-no-lock')) {
    const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
    if (submitBtn) {
      form.dataset.submitting = "true";
      submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
      submitBtn.setAttribute('disabled', 'disabled');
      // Auto re-enable after 8s fallback in case of validation interruption
      setTimeout(function () {
        delete form.dataset.submitting;
        submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
        submitBtn.removeAttribute('disabled');
      }, 8000);
    }
  }
});

document.addEventListener("DOMContentLoaded", initShell);
