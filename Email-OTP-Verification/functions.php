<?php
/*
Plugin Name: Login with OTP
Description: Send and verify OTP while user login
Version: 1.0
Author: Your Name
*/

add_filter('authenticate', 'login_with_otp', 30, 3);

function login_with_otp($user, $username, $password) {
    if (is_a($user, 'WP_User')) {
        $user_email = $user->user_email;
        $user_phone = get_user_meta($user->ID, 'phone_number', true);
        setcookie('user_id',$user->ID, time() + 86400, COOKIEPATH, COOKIE_DOMAIN);
        if (!empty($user_phone) || !empty($user_email)) {
            $otp = strval(rand(100000, 999999));
            if (!empty($user_phone)) {
                $to = $user_phone;
            } elseif (!empty($user_email)) {
                $to = $user_email;
            }
            $subject = 'Your OTP code for login';
            $message = 'Your OTP code is: ' . $otp;
            $headers = array('Content-Type: text/html; charset=UTF-8');
            wp_mail($to, $subject, $message, $headers);
            // Save the OTP code to the user's meta data
            update_user_meta($user->ID, 'otp_code', $otp);
            // Redirect the user to the OTP verification page
            wp_redirect(home_url('/verify-otp/'));
            exit;
        }
    }
    return $user;
}

function display_otp_verification_form() {
    if (isset($_POST['submit'])) {
        $otp_code = sanitize_text_field($_POST['otp_code2']);
        $user_id = (isset($_COOKIE['user_id']))? $_COOKIE['user_id']: "";
        $saved_otp = get_user_meta($user_id, 'otp_code', true);
        if ($otp_code == $saved_otp) {
            // OTP code is valid, log the user in
            wp_clear_auth_cookie();
            wp_set_current_user($user_id);
            wp_set_auth_cookie($user_id);
            // Delete the OTP code from the user's meta data
            delete_user_meta($user_id, 'otp_code');
            // Redirect the user to the homepage
            wp_redirect(home_url('/'));
            exit;
        } else {
            // OTP code is invalid, display an error message
            echo '<div class="error text-center">Invalid OTP code. Please try again.</div>';
        }
    }
    ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <style>
        body{
  background:#eee;
}

.bgWhite{
  background:white;
  box-shadow:0px 3px 6px 0px #cacaca;
}

.title{
  font-weight:600;
  margin-top:20px;
  font-size:24px
}

.customBtn{
  border-radius:0px;
  padding:10px;
}

    </style>
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col-md-4 text-center">
                <div class="row">
                    <div class="col-sm-12 mt-5 bgWhite">
                        <div class="title">
                        Verify OTP
                        </div>
                        <form  method="post" class="mt-5">
                            <input type="text" name="otp_code2" id="otp_code" class="form-control" required>
                            <hr class="mt-4">
                            <button  type="submit" name="submit" class='btn btn-primary btn-block mt-4 mb-4 customBtn'>Verify</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

add_shortcode('otp_verification_form', 'display_otp_verification_form');

add_action('init', function () {
    add_rewrite_rule('^verify-otp/?', 'index.php?verify_otp=1', 'top');
    add_rewrite_tag('%verify_otp%', '1');
});

add_action('template_redirect', function () {
    if (get_query_var('verify_otp')) {
        if (is_user_logged_in()) {
            wp_redirect(home_url('/'));
            exit;
        }
        display_otp_verification_form();
        exit;
    }
});
