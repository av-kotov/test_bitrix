<?php
if ( isset( $_POST['error_text'], $_POST['page_url'] ) )
 {
 $error_text = trim( strval( $_POST['error_text'] ) );
 $page_url = trim( strval( $_POST['page_url'] ) );

 if ( mb_strlen( $error_text ) < 1 ) {
  $output['ErrorMessage'] = 'Выделите текст с ошибкой.';
  exit( json_encode( $output ) );
  }
 elseif ( mb_strlen( $error_text ) > 100 ) {
  $output['ErrorMessage'] = 'Максимально можно выделить 100 символов.';
  exit( json_encode( $output ) );
  }

 $output['Success'] = True;
 $output['SendMessage'] = 'Спасибо, сообщение успешно отправлено!';
 exit( json_encode( $output ) );
 }
?>
