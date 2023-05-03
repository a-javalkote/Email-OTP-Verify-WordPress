jQuery(document).ready(function($) {
    $('#otp-verify-btn').click(function() {
      $.ajax({
        type: 'POST',
        url: ajaxurl, // This is the WordPress AJAX URL
        data: {
          action: 'my_function', // This is the name of your plugin function
          // Additional data you want to pass to the function
        },
        success: function(response) {
          // Handle the response from the server
        },
        error: function(jqXHR, textStatus, errorThrown) {
          // Handle errors
        }
      });
    });
  });
  