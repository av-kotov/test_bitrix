$(document).keypress(function(e){
  if ( ( e.ctrlKey == true ) && ( e.keyCode == 13 || e.keyCode == 10 ) ) {
   let selected_text = window.getSelection(  ).toString(  );
   let page_url = window.location.href;
   if ( selected_text != '' ) {
    $.ajax({
     type: "POST",
     url: "/send_error.php",
     data: { "error_text": selected_text, "page_url": page_url },
     success: function( data ) {
      obj = $.parseJSON( data );
      if ( obj.ErrorMessage ) {
       alert( obj.ErrorMessage );
      }
      else if ( obj.Success ) {
       alert( obj.SendMessage );
      }
     }
    });
   }
  }
 });
