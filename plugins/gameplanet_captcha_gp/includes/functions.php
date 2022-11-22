<?php
/**
 * Se hace una peticion a google para validar el captcha
 * @return array respose Respuesta formateada al valor de google
 */
function validaGoogleCaptcha($captchaValue){
  $response = wp_remote_post( "https://www.google.com/recaptcha/api/siteverify", array(
    'method' => 'POST',
    'timeout' => 45,
    'redirection' => 5,
    'blocking' => true,
    'headers' => array(),
    'body' => array(
      //'secret' => "6LcgupshAAAAAMp5CoO3mUKHcPXxpIRYp5iEMGeg",//esto se obtine en google
      'secret' => get_option('gc-clave-secreta'),//esto se obtine en google
      'response' => esc_attr($captchaValue)),//valor del captcha $_POST['g-recaptcha-response']
    'cookies' => array()
    )
  );

  //Comprobamos si tenemos algún tipo de error en la conexión con google
  if ( is_wp_error( $response ) ) {
    return [
      "success"=>false,
      "message"=>"Captcha Invalido",
      "data"=>"Se ha producido un error comprobando el captcha",
    ];
  } else {
    //Si hemos conectado correctamente con google, comprobamos si la respuesta es true o false
    $g_response = json_decode($response["body"]);
    if ($g_response->success == false) {
      //abria que mandar mas tipos de mensajes lo dejo asi por el momento
      return [
        "success"=>false,
        "message"=>"Captcha Invalido",
        "data"=>"Se ha producido un error comprobando el captcha",
      ];
    }
    return [
      "success"=>true,
      "message"=>"Captcha Valido",
      "data"=>401,//sin autorizacion
    ];
  }
 }