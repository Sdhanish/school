<!DOCTYPE html>
<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?php echo html_escape(($title ?? 'Dashboard') . ' - Login2'); ?></title>
<script src="<?php echo base_url('assets/vendor/tailwind.min.js'); ?>"></script>
<link href="<?php echo base_url('assets/fonts/material-symbols.css'); ?>" rel="stylesheet"/>
<link href="<?php echo base_url('assets/fonts/inter.css'); ?>" rel="stylesheet"/>
<link href="<?php echo base_url('assets/vendor/datatables/jquery.dataTables.min.css'); ?>" rel="stylesheet"/>
<link href="<?php echo base_url('assets/vendor/datatables/responsive.dataTables.min.css'); ?>" rel="stylesheet"/>
<link href="<?php echo base_url('assets/vendor/cropper/cropper.min.css'); ?>" rel="stylesheet"/>
<link href="<?php echo base_url('assets/vendor/intl-tel-input/css/intlTelInput.min.css'); ?>" rel="stylesheet"/>
<link href="<?php echo base_url('assets/app.css'); ?>" rel="stylesheet"/>
<script src="<?php echo base_url('assets/vendor/jquery.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendor/cropper/cropper.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendor/datatables/jquery.dataTables.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendor/datatables/dataTables.responsive.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendor/intl-tel-input/js/intlTelInputWithUtils.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/phone-input-manager.js'); ?>"></script>
<script id="tailwind-config">
tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: {
                "sky-blue": "#357AC4", "beige": "#F3EEE9", "white": "#FFFFFF",
                "dark-text": "#243746", "muted-text": "#607482", "theme-border": "#DCE4E8",
                "light-blue": "#EAF2F7", "dark-blue": "#357AC4", "sidebar-border": "#B8CCD9",
                "primary": "#357AC4", "primary-container": "#EAF2F7", "primary-fixed": "#357AC4",
                "primary-fixed-dim": "#B8CCD9", "on-primary": "#FFFFFF", "on-primary-container": "#243746",
                "on-primary-fixed": "#FFFFFF", "on-primary-fixed-variant": "#2C68A8",
                "secondary": "#357AC4", "secondary-container": "#EAF2F7", "secondary-fixed": "#357AC4",
                "secondary-fixed-dim": "#B8CCD9", "on-secondary": "#FFFFFF", "on-secondary-container": "#243746",
                "on-secondary-fixed": "#FFFFFF", "on-secondary-fixed-variant": "#2C68A8",
                "button-primary": "#357AC4", "button-primary-hover": "#2C68A8",
                "background": "#F7F9FB", "surface": "#FFFFFF", "surface-dim": "#EAF2F7",
                "surface-bright": "#FFFFFF", "surface-tint": "#357AC4", "surface-variant": "#EAF2F7",
                "surface-container-lowest": "#FFFFFF", "surface-container-low": "#F3EEE9",
                "surface-container": "#EAF2F7", "surface-container-high": "#DCE4E8",
                "surface-container-highest": "#357AC4", "on-surface": "#243746",
                "on-surface-variant": "#607482", "on-background": "#243746",
                "outline": "#607482", "outline-variant": "#DCE4E8",
                "tertiary": "#357AC4", "tertiary-container": "#F3EEE9", "on-tertiary-container": "#243746",
                "tertiary-fixed": "#F3EEE9", "on-tertiary": "#FFFFFF",
                "error": "#C5221F", "error-container": "#FCE8E6", "on-error-container": "#93000A", "on-error": "#FFFFFF",
                "inverse-surface": "#243746", "inverse-on-surface": "#FFFFFF", "inverse-primary": "#357AC4"
            },
            borderRadius: { DEFAULT: "0.25rem", lg: "0.5rem", xl: "0.75rem", full: "9999px" },
            spacing: { md: "16px", "grid-gutter": "20px", xs: "4px", "grid-margin": "24px", sm: "12px", lg: "24px", xl: "32px", base: "8px" },
            fontFamily: {
                "body-lg": ["Inter"], "headline-md": ["Inter"], "body-md": ["Inter"], "label-md": ["Inter"],
                "headline-xl": ["Inter"], "headline-lg": ["Inter"], "data-tabular": ["Inter"], "headline-lg-mobile": ["Inter"]
            },
            fontSize: {
                "body-lg": ["16px", { lineHeight: "24px", fontWeight: "400" }],
                "headline-md": ["20px", { lineHeight: "28px", fontWeight: "600" }],
                "body-md": ["14px", { lineHeight: "20px", fontWeight: "400" }],
                "label-md": ["12px", { lineHeight: "16px", letterSpacing: "0.05em", fontWeight: "600" }],
                "headline-xl": ["36px", { lineHeight: "44px", letterSpacing: "-0.02em", fontWeight: "700" }],
                "headline-lg": ["28px", { lineHeight: "36px", letterSpacing: "-0.01em", fontWeight: "600" }],
                "data-tabular": ["14px", { lineHeight: "20px", fontWeight: "500" }],
                "headline-lg-mobile": ["24px", { lineHeight: "32px", fontWeight: "600" }]
            }
        }
    }
}
</script>

</head>
<body class="bg-background text-on-background font-body-lg" data-page="<?php echo html_escape($page_key); ?>"<?php echo $breadcrumb ? ' data-breadcrumb=\'' . $breadcrumb . '\'' : ''; ?> >
<script>
window.APP_BASE_URL = "<?php echo base_url(); ?>";
window.CSRF_TOKEN_NAME = "<?php echo $this->security->get_csrf_token_name(); ?>";
window.CSRF_HASH = "<?php echo $this->security->get_csrf_hash(); ?>";
<?php if (isset($current_user)): ?>
window.CURRENT_USER = <?php echo json_encode($current_user); ?>;
window.IS_SUPER_ADMIN = <?php echo (!empty($is_super_admin)) ? 'true' : 'false'; ?>;
window.USER_PERMISSIONS = <?php echo json_encode($effective_permissions ?? []); ?>;
window.CURRENT_SCHOOL_ID = <?php echo (int)($current_school_id ?? get_current_school_id() ?? 1); ?>;
window.CURRENT_SCHOOL = <?php echo json_encode($current_school ?? get_current_school() ?? null); ?>;
window.AVAILABLE_SCHOOLS = <?php echo json_encode($available_schools ?? get_available_schools() ?? []); ?>;
window.CAN_SWITCH_SCHOOL = <?php echo (!empty($can_switch_school)) ? 'true' : 'false'; ?>;
window.CURRENT_ACADEMIC_YEAR_ID = <?php echo (int)($current_academic_year_id ?? 1); ?>;
window.CURRENT_ACADEMIC_YEAR = <?php echo json_encode($current_academic_year ?? null); ?>;
window.ACTIVE_ACADEMIC_YEAR_ID = <?php echo (int)($active_academic_year_id ?? get_active_academic_year_id() ?? 1); ?>;
window.ACTIVE_ACADEMIC_YEAR = <?php echo json_encode($active_academic_year ?? get_active_academic_year() ?? null); ?>;
window.AVAILABLE_ACADEMIC_YEARS = <?php echo json_encode($available_academic_years ?? []); ?>;
window.CAN_CHANGE_ACADEMIC_YEAR = <?php echo (!empty($can_change_academic_year)) ? 'true' : 'false'; ?>;
window.DYNAMIC_NAV = <?php echo json_encode($sidebar_items ?? []); ?>;
<?php endif; ?>
</script>
<div class="flex min-h-screen">
  <div id="sidebar-root">
    <?php if (isset($sidebar_items)): ?>
      <?php $this->load->view('templates/sidebar', [
          'sidebar_items' => $sidebar_items,
          'page_key'      => $page_key ?? 'dashboard'
      ]); ?>
    <?php endif; ?>
  </div>
  <div class="flex-1 min-w-0 flex flex-col">
    <div id="header-root"></div>
    <main class="flex-1 p-4 lg:p-6 max-w-[1600px] w-full mx-auto">
