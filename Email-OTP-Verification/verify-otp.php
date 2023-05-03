<?php 
function display_otp_verification_form() {
    if (isset($_POST['submit'])) {
        $otp_code = sanitize_text_field($_POST['otp_code']);
        $user = wp_get_current_user();
        $saved_otp = get_user_meta($user->ID, 'otp_code', true);
        if ($otp_code == $saved_otp) {
            // OTP code is valid, log the user in
            wp_clear_auth_cookie();
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID);
            // Delete the OTP code from the user's meta data
            delete_user_meta($user->ID, 'otp_code');
            // Redirect the user to the homepage
            wp_redirect(home_url('/'));
            exit;
        } else {
            // OTP code is invalid, display an error message
            echo '<div class="error">Invalid OTP code. Please try again.</div>';
        }
    }
}
add_shortcode('otp_verification_form', 'display_otp_verification_form');