<?php
  /**
   * Sets error header and json error message response.
   *
   * @param  String $messsage error message of response
   * @return void
   */
  function errorResponse ($messsage) {
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(array('message' => $messsage)));
  }

  /**
   * Pulls posted values for all fields in $fields_req array.
   * If a required field does not have a value, an error response is given.
   */
  function constructMessageBody () {
    $fields_req =  array("name" => true, "email" => true, "message" => true);
    $message_body = "";
	$message_body = "Producto: ".$_POST['reason']."\n";
    foreach ($fields_req as $name => $required) {
      $postedValue = $_POST[$name];
      if ($required && empty($postedValue)) {
        errorResponse("$name is empty.");
      } else {
        $message_body .= ucfirst($name) . ":  " . $postedValue . "\n";
      }
    }
    return $message_body;
  }

  header('Content-type: application/json');

  //do Captcha check, make sure the submitter is not a robot:)...
  //Variables para el captcha
	$FEEDBACK_HOSTNAME 		= "smtp.gmail.com";
	$FEEDBACK_EMAIL 		= "contacto.mcachis@gmail.com";
	$FEEDBACK_PASSWORD 		= "mcachis2016";
	$FEEDBACK_ENCRYPTION	= "TLS";
	$RECAPTCHA_SECRET_KEY 	= "6LduihoTAAAAAF5FA2XO08wLl2MNTOW_ilH_8SiK";//Cambiar cuando se suba
	$SEND_EMAIL_1			= "a.chiquet@mcachis.com.mx";
	$SEND_EMAIL_2			= "a.velasco@mcachis.com.mx";
	$FEEDBACK_SKIP_AUTH 	= false;
	
  $url = 'https://www.google.com/recaptcha/api/siteverify';
  $opts = array('http' =>
    array(
      'method'  => 'POST',
      'header'  => 'Content-type: application/x-www-form-urlencoded',
      'content' => http_build_query(array('secret' => $RECAPTCHA_SECRET_KEY, 'response' => $_POST["g-recaptcha-response"]))
    )
  );
  $context  = stream_context_create($opts);
  $result = json_decode(file_get_contents($url, false, $context, -1, 40000));

  if (!$result->success) {
    errorResponse('reCAPTCHA checked failed! Error codes: ' . join(', ', $result->{"error-codes"}));
  }
  //attempt to send email
  $messageBody = constructMessageBody();
  require './vender/php_mailer/PHPMailerAutoload.php';
  $mail = new PHPMailer;
  $mail->CharSet = 'UTF-8';
  $mail->isSMTP();
  $mail->Host = $FEEDBACK_HOSTNAME;
  if (!$FEEDBACK_SKIP_AUTH) {
    $mail->SMTPAuth = true;
    $mail->Username = $FEEDBACK_EMAIL;
    $mail->Password = $FEEDBACK_PASSWORD;
  }
  if ($FEEDBACK_ENCRYPTION == 'TLS') {
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
  } elseif ($FEEDBACK_ENCRYPTION == 'SSL') {
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
  }

  $mail->Sender = $FEEDBACK_EMAIL;
  $mail->setFrom($_POST['email'], $_POST['name']);
  $mail->addAddress($SEND_EMAIL_1);
  $mail->addAddress($SEND_EMAIL_2);

  //$mail->Subject = $_POST['reason'];
  $mail->Subject = $_POST['name'];
  $mail->Body  = $messageBody;


  //try to send the message
  if($mail->send()) {
    echo json_encode(array('message' => 'Hemos recibido tu mensaje, en breve nos comunicaremos contigo.'));
  } else {
    errorResponse('An expected error occured while attempting to send the email: ' . $mail->ErrorInfo);
  }
?>
