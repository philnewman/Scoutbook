jQuery(document).ready(function($) {
    $('#scoutbook_add_to_email').click(function(e) {
      var sb_email_to_add = $("#scoutbook_email_to_add").val();
      e.preventDefault();
      var add_email_url = 'https://troop351.org/wp-content/plugins/scoutbook/includes/add_to_email_list.php?email=' + sb_email_to_add;
      var placeholder = $("#scoutbook_email_to_add").attr('placeholder');

      $.ajax({
         url: add_email_url,
          success: function( data ) {
               var success_message = sb_email_to_add + ' added to all@troop351.org';
               $("#add_email_success").text(success_message);
               $("#add_email_success").show().delay(5000).fadeOut();        
               $("#scoutbook_email_to_add").val(placeholder);
          },
          error: function( data) {
               var success_message = 'An error has occurred, email not added.';
               $("#add_email_success").text(success_message);
               $("#add_email_success").show().delay(5000).fadeOut();   
               $("#scoutbook_email_to_add").reset();
               $("#scoutbook_email_to_add").val(placeholder);
          }
        });       
    });
});