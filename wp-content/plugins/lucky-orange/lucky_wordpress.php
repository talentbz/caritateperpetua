<?php

/*
Plugin Name: Lucky Orange
Plugin URI: https://www.luckyorange.com
Description: Less time crunching numbers, more time growing your business.
Version: 2.1.3
Author: Lucky Orange
Author URI: https://www.luckyorange.com
*/

register_activation_hook(__FILE__, 'lo_activate');
function lo_activate () {
  add_option('lo_needs_connection', true);
  add_option('lo_needs_setup', true);
  remove_action('wp_head', 'lucky_orange_print_track_code');
}

register_deactivation_hook(__FILE__, 'lo_deactivate');
function lo_deactivate () {
  delete_option('lo_needs_setup');
  remove_action('wp_head', 'lo_load_tracking_code');
  remove_action('wp_head', 'lucky_orange_print_track_code');

  if (get_option('lo_setup')) {
    delete_option('lo_setup');
    uninstall_integration();
  }
}

register_uninstall_hook(__FILE__, 'lo_uninstall');
function lo_uninstall () {
  delete_option('lo_needs_setup');
  remove_action('wp_head', 'lo_load_tracking_code');
  remove_action('wp_head', 'lucky_orange_print_track_code');

  if (get_option('lo_setup')) {
    delete_option('lo_setup');
    uninstall_integration();
  }
}

function uninstall_integration () {
  $site_url = get_home_url();
  $uuid = get_option('lo_uuid');
  delete_option('lo_site_id');
  delete_option('lo_uuid');

  $body = wp_json_encode([
    'siteUrl' => $site_url,
    'uuid' => $uuid
  ]);
  $options = [
    'headers' => [
      'Content-Type' => 'application/json'
    ],
    'body' => $body
  ];

  wp_remote_post('https://api-preview.luckyorange.com/integration-wordpress-backend/uninstall', $options);
}

add_action('admin_init', 'lo_load_settings');
function lo_load_settings () {
  register_setting(
    'lo_settings',
    'lo_site_id',
    [
      'type' => 'string',
      'default' => ''
    ]
  );

  add_settings_section(
    'lo_settings_section',
    'General',
    'lo_settings_section_cb',
    'lo_settings'
  );

  add_settings_field(
    'lo_site_id',
    'Site ID',
    'lo_load_setting_site_id',
    'lo_settings',
    'lo_settings_section',
    [
      'label_for' => 'lo_site_id'
    ]
  );
}
function lo_settings_section_cb () {
  echo '';
}
function lo_load_setting_site_id () {
  $setting = esc_attr(get_option('lo_site_id'));

  echo "<input type='text' name='lo_site_id', value='{$setting}'>";
}

add_action('admin_menu', 'lo_menus');
function lo_menus () {
  if (current_user_can('administrator')) {
    add_menu_page(
      'Lucky Orange', // Page Title
      'Lucky Orange', // Menu Title
      'administrator', // WP Capability
      'lucky-orange', // Menu Slug
      'lo_load_dashboard', // Callable function
      'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTI4cHgiIGhlaWdodD0iMTI4cHgiIHZpZXdCb3g9IjAgMCAxMjggMTI4IiB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPgogICAgPCEtLSBHZW5lcmF0b3I6IFNrZXRjaCA0OC4yICg0NzMyNykgLSBodHRwOi8vd3d3LmJvaGVtaWFuY29kaW5nLmNvbS9za2V0Y2ggLS0+CiAgICA8dGl0bGU+QXJ0Ym9hcmQ8L3RpdGxlPgogICAgPGRlc2M+Q3JlYXRlZCB3aXRoIFNrZXRjaC48L2Rlc2M+CiAgICA8ZGVmcz48L2RlZnM+CiAgICA8ZyBpZD0iUGFnZS0xIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj4KICAgICAgICA8ZyBpZD0iQXJ0Ym9hcmQiIGZpbGwtcnVsZT0ibm9uemVybyI+CiAgICAgICAgICAgIDxnIGlkPSJMTy1JY29uIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgyNi4wMDAwMDAsIDAuMDAwMDAwKSI+CiAgICAgICAgICAgICAgICA8cGF0aCBkPSJNOS44NjEzMzk0MywxMTUuOTc5ODE1IEMyLjc1ODQzNjU3LDEwNS4wOTU0MzkgLTIuODczMTk3NzEsOTEuMTI1OTM1MSAxLjU4ODg4MjI5LDc4LjMyNDg0MTEgQzYuMDQ5ODY1MTQsNjUuNTIzNzQ3IDEyLjA1MzQzMDksNTYuNDc5NTA3NyAyMi4yOTk2NDgsNTIuODIwMDIwNSBDMjcuNjcyMzU2Niw1MC45MDExMTQ1IDQzLjgzODc1NjYsNDMuNjkxNTQxOCA1Ni40MjA3OTA5LDU0LjgzMDgyNCBDNjkuMDAxNzI4LDY1Ljk3MTIgNzUuMzE0Njg4LDc5Ljg3Mzk2OTIgNzUuNzgwOTczNyw4OS41Mzk2MTAyIEM3Ny4wNzIzMTA5LDExNi4zMDQ3MzggNDEuNjg1MDY1MSwxMjcuMjgzMiAzNS40Njc1NTY2LDEyNy4yODMyIEMyNy4wODUzODUxLDEyNy4yODMyIDE0LjU4MzQ0MjMsMTIzLjIxNjczOSA5Ljg2MTMzOTQzLDExNS45Nzk4MTUiIGlkPSJTaGFwZSIgZmlsbD0iI0ZGNzA0QyI+PC9wYXRoPgogICAgICAgICAgICAgICAgPHBhdGggZD0iTTU2LjA1MDA2NjMsMC43MjY2NDYxNTQgQzUxLjMxNTg5NDksLTUuODczNTU4OTggMzYuMTU2NjcyLDM0LjU3NzcyMzEgNDMuMzQwNzYzNCwzOS41Njc1MzUxIEM0NS4xNjA5MjM0LDQwLjgzMjIxODggNjYuMzI5MTk3NywxNS4wNTgyNzAxIDU2LjA1MDA2NjMsMC43MjY2NDYxNTQgTTM1LjA1MDg2MTcsMzkuNzcwNDc1MiBDMzYuMTI4MjU2LDQ1LjQzMzEwNzcgMjIuOTQ0OTg3NCwyNi41MjA4MzQyIDI1LjY0OTQ0NDYsMjMuMDUxNzA2IEMzMS44NTA0OTYsMTUuMDkzODI1NiAzNC40MTU2MTYsMzYuNDMwNDQxMSAzNS4wNTA4NjE3LDM5Ljc3MDQ3NTIiIGlkPSJTaGFwZSIgZmlsbD0iIzQyNEEzQyI+PC9wYXRoPgogICAgICAgICAgICA8L2c+CiAgICAgICAgPC9nPgogICAgPC9nPgo8L3N2Zz4=', // Icon URL
      3 // Position
    );

    add_submenu_page(
      'lucky-orange', // Parent slug
      'Lucky Orange', // Page Title
      'Dashboard', // Menu Title
      'administrator', // WP Capability
      'lucky-orange', // Menu Slug
      'lo_load_dashboard' // Callable function
    );

    add_submenu_page(
      'lucky-orange', // Parent slug
      'Lucky Orange Settings', // Page Title
      'Settings', // Title
      'administrator', // WP Capability
      'lucky-orange-settings', // Menu Slug
      'lo_settings_page' // Callable function
    );

    add_options_page(
      'Lucky Orange', // Page Title
      'Lucky Orange', // Menu Title
      'administrator', // WP Capability
      'lucky-orange-settings', // Menu Slug
      'lo_settings_page' // Callable function
    );
  }
}
function lo_load_dashboard () {
  $needs_connection = !!(get_option('lo_needs_connection'));

  $url = '';

  $uuid = get_option('lo_uuid');

  if (!$uuid) {
    $uuid = wp_generate_uuid4();
    add_option('lo_uuid', $uuid);
  }

  $site_name = get_bloginfo();
  $site_url = get_home_url();
  $user = wp_get_current_user();
  $user_email = $user->user_email;
  $user_name = $user->user_login;

  $query = "siteName={$site_name}&siteUrl={$site_url}&userName={$user_name}&userEmail={$user_email}&uuid={$uuid}";

  if ($needs_connection) {
    delete_option('lo_needs_connection');
    $url = esc_url("https://api-preview.luckyorange.com/integration-wordpress-backend/connect/ui?{$query}");
  } else {
    $url = esc_url("https://api-preview.luckyorange.com/integration-wordpress-backend/load?{$query}");
  }

  $iframeStr = "
    <iframe
      src='{$url}'
      height='100%'
      width='100%'
      style='width: calc(100% + 19px); margin-left: -20px; height: calc(100vh - 150px); border-bottom: 1px solid #23282d;'
      frameboarder=0
    >
    </iframe>
  ";

  echo $iframeStr;
}
function lo_settings_page () {
  if (!current_user_can('administrator')) {
    return;
  }

  ?>
    <div class="wrap">
      <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
      <form action="options.php" method="post">
        <?php
          settings_fields('lo_settings');
          do_settings_sections('lo_settings');
          submit_button('Save Settings');
        ?>
      </form>
    </div>
  <?php
}

add_action('wp_head', 'lo_load_tracking_code');
function lo_load_tracking_code () {
  $site_id = esc_html(get_option('lo_site_id'));
  $current_user = wp_get_current_user();
  $user = esc_js($current_user->user_login);
  $email = esc_js($current_user->user_email);

  if ($site_id) {
    if (is_numeric($site_id)) {
      // Classic
      echo "\r\n";
      echo "<script>window.__lo_site_id = '{$site_id}';</script>";
      echo "<script async src='https://d10lpsik1i8c69.cloudfront.net/w.js'></script>";
    } else {
      // New App
      echo "\r\n";
      echo "<script>window.LOSiteId = '{$site_id}';</script>";
      echo "<script async defer src='https://tools.luckyorange.com/core/lo.js'></script>";
    }
  }

  if ($user) {
    // echo "\r\n";
    // echo "
    //   <script type='text/javascript'>
    //     window.loWpCustomData = {
    //       name: '{$user}',
    //       email: '{$email}'
    //     }
    //   </script>
    // ";
  }
}

// Redirect to the setup page if this is the initial install
function lo_redirect_setup () {
  $needs_setup = !!(get_option('lo_needs_setup'));

  if ($needs_setup) {
    delete_option('lo_needs_setup');
    add_option('lo_setup', true);
    header('Location: admin.php?page=lucky-orange');
    exit;
  }
}
lo_redirect_setup();
