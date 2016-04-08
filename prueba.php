<?php

echo 'probando php';

$para      = 'rogelio@dsindigo.mx';
$titulo    = 'El título';
$mensaje   = 'Hola';
$cabeceras = 'From: rogelio@dsindigo.mx' . "\r\n" .
   'Reply-To: rogelio@dsindigo.mx' . "\r\n" .
   'X-Mailer: PHP/' . phpversion();

mail($para, $titulo, $mensaje, $cabeceras);

?>
