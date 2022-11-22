<?php
defined( 'ABSPATH' ) || exit;

class GP_OTP_CONTROL_MODEL{
  protected $table_users = '';
  protected $table_ip = '';
  protected $table_config = '';
  protected $table_error_logs = '';
  //TIEMPOS
  protected $time_resend_soft = 60;//segundos entre peticiones de usuario
  protected $time_resend_medium = 180;//segundos entre peticiones del usuario con el mimso telefono
  protected $time_resend_hard = 3600;//segundos  entre peticiones del usuario con la misma ip
  protected $time_resend_hard_ip = 7200;//segundos de la misma IP
  //INTENTOS
  protected $max_attends_user_phone = 3;//maximos intentos por usuario y el mismo telefono
  protected $max_ip_attends_user = 5;//maximos intentos por IP y el mismo usuario
  protected $max_ip_attends = 17;//maximos intentos por IP
 

  //ERRORES DEL GATEWAY
  protected $max_gateway_errors = 5;//maximos intentos por IP

  public function __construct() {
		global $table_prefix;
    $this->table_users      = ($table_prefix??'').'gp_otp_control_users';
    $this->table_ip         = ($table_prefix??'').'gp_otp_control_ip';
    $this->table_config     = ($table_prefix??'').'gp_otp_config';
    $this->table_error_logs = ($table_prefix??'').'gp_otp_gateway_error_log';

    $this->time_resend_soft    = get_option('gp_otp-time_resend_soft');
    $this->time_resend_medium  = get_option('gp_otp-time_resend_medium');
    $this->time_resend_hard    = get_option('gp_otp-time_resend_hard');
    $this->time_resend_hard_ip = get_option('gp_otp-time_resend_hard_ip');

    $this->max_attends_user_phone = get_option('gp_otp-max_attends_user_phone');
    $this->max_ip_attends_user    = get_option('gp_otp-max_ip_attends_user');
    $this->max_ip_attends         = get_option('gp_otp-max_ip_attends');
    
    $this->max_gateway_errors         = get_option('gp_otp-max_gateway_errors');
	}

  /**
   * Consulta par aobtner el ultimo intento del usuario
   * 
   * @param int $idUser El id del usuario,  si no contamos con poner en 0 el se ira por el email proprocionado
   * @param int ip IP public del usuario
   * @param string email En caso de no contar con el id del usuario se buscara por email
   */
  public function show($idUser,$ip = '',$email = ""){
    try {
      global $wpdb;
      //vamos por el mas reciente no importa el telefono
      $query = "";
      if($idUser != 0){
        $query="
          SELECT c_users.*,c_ip.resend_attends as 'ip_resend_attends', 
          c_ip.is_blocked as 'ip_blocked',c_ip.time_blocked as 'ip_time_blocked'

          FROM {$this->table_users} c_users 
          LEFT JOIN {$this->table_ip} c_ip ON c_ip.ip = c_users.user_ip
          WHERE c_users.user_id = {$idUser}
          AND c_users.user_ip = '{$ip}'
          GROUP BY c_users.id
          ORDER BY c_users.updated_at DESC
          ;
        ";
      }
      else{

        //tal vez este cambie con el tiempo
        $query="
          SELECT c_users.*,c_ip.resend_attends as 'ip_resend_attends', 
          c_ip.is_blocked as 'ip_blocked',c_ip.time_blocked as 'ip_time_blocked'

          FROM {$this->table_users} c_users 
          LEFT JOIN {$this->table_ip} c_ip ON c_ip.ip = c_users.user_ip
          WHERE c_users.user_email = '{$email}'
          AND c_users.user_ip = '{$ip}'
          GROUP BY c_users.id
          ORDER BY c_users.updated_at DESC
          ;
        ";
      }
      
      $resultados=$wpdb->get_results($query); 
      return[
        "success"=>true,
        "data"=>$resultados
      ];
    } catch (\Exception $e) {
      gameplanet_otp_logs("GP_OTP_USERS_MODEL: ",$e->getMessage());
      return[
        "success"=>false,
        "message"=>"CODE-500-GOUM-SBU",
        "data"=>[]
      ];
    }
   
  }

  /**
   * Crea o actualiza el log del intento
   * 
   * se crean dos registros el del contador de usuario
   * y el contador por IP
   * 
   * @return int time_resend tiempo para el siguiente intento
   */
  public function create_update($id_usuario,$ip_usuario,$phone,$email = ""){
    global $wpdb;
    $query_user = "";
    if($id_usuario != 0){
      $query_user="
        SELECT *
        FROM {$this->table_users} c_users 
        WHERE c_users.user_id = {$id_usuario}
        AND c_users.user_ip = '{$ip_usuario}'
        ORDER BY c_users.updated_at DESC
        ;
      ";
    }
    else{
      $query_user="
      SELECT *
      FROM {$this->table_users} c_users 
      WHERE c_users.user_email = '{$email}'
      AND c_users.user_ip = '{$ip_usuario}'
      ORDER BY c_users.updated_at DESC
      ;
    ";
    }
    
    $existe_usuario = $wpdb->get_results($query_user); 

    $query_ip="
        SELECT *
        FROM {$this->table_ip} c_ip 
        WHERE c_ip.ip = '{$ip_usuario}'
        ORDER BY c_ip.updated_at DESC
        ;
      ";
    $existe_ip = $wpdb->get_results($query_ip); 

    //no exisite ningun registro del usuario (primer intento)
    if(empty($existe_usuario)){
      gameplanet_otp_logs('Modelo create_update','usuario nuevo');
      $tiempo_bloqueo = strtotime ( "+{$this->time_resend_soft} second" , time() );
      $tiempo_bloqueo_flat = $this->time_resend_soft;
      if(empty($existe_ip)){
        //creamos la ip
        $wpdb->insert($this->table_ip,[
          "ip"=>$ip_usuario,
          "resend_attends"=>1,
        ]);
      }else{
        //actualizamos
        $update_data_ip = [
          "resend_attends"=>$existe_ip[0]->resend_attends+1,
        ];
        //si ya llego al limite de peticones por IP debemos  bloquear la IP

        $intentos_de_la_ip = $existe_ip[0]->resend_attends;
        if((($intentos_de_la_ip + 1) % $this->max_ip_attends) == 0){
          gameplanet_otp_logs('Modelo create_update','LIMITE EN LA MISMA IP'.json_encode(['ip'=>$ip_usuario]));
          $tiempo_bloqueo = strtotime ( "+{$this->time_resend_hard_ip} second" , time() );
          $tiempo_bloqueo_flat = $this->time_resend_hard_ip;
          $update_data_ip['time_blocked'] = $tiempo_bloqueo;
        }

        $wpdb->update($this->table_ip, $update_data_ip,["id"=>$existe_ip[0]->id]);
      }
       //creamos usuario 
       $wpdb->insert($this->table_users,[
        "user_id"=>$id_usuario,
        "user_ip"=>$ip_usuario,
        "user_email"=>$email,
        "user_phone"=>$phone,
        "resend_attends"=>1,
        "time_blocked"=> $tiempo_bloqueo,// es el primer intento del usuario
      ]);

      return [
        "success"=>true,
        "data"=>[
          "time_blocked"=>$tiempo_bloqueo_flat
        ]
      ];
    }

    //actualizamos el usuario

    //revisamos si ya estaba el telefono
    $update = false;
    $update_id = 0;
    $intentos_del_usuario = 0;
    $intentos_del_telefono= 0;
    foreach ($existe_usuario as $row) {
      if($row->user_phone == $phone){
        $update=true;
        $update_id = $row->id;
        $intentos_del_telefono =(Int) $row->resend_attends;
      }
      $intentos_del_usuario += (Int) $row->resend_attends;
     
    }

    /**********   REGLAS DEL TIEMPO DE BLOQUEO  ******** */
    $tiempo_bloqueo =strtotime ( "+300 second" , time() );
    $tiempo_bloqueo_flat = 300;
    $bloquear_ip = false;

    //tiempo por IP
    $intentos_de_la_ip = $existe_ip[0]->resend_attends;
    if((($intentos_de_la_ip + 1) % $this->max_ip_attends) == 0){
      $bloquear_ip = true;
      gameplanet_otp_logs('Modelo create_update','LIMITE EN LA MISMA IP'.json_encode(['ip'=>$ip_usuario]));
      $tiempo_bloqueo = strtotime ( "+{$this->time_resend_hard_ip} second" , time() );
      $tiempo_bloqueo_flat = $this->time_resend_hard_ip;

    }else{
      //calculamos tiempo de bloqueo del usuario

      if((($intentos_del_usuario + 1) % $this->max_ip_attends_user) == 0){
        //ya esta en el limite por usuario e IP
        gameplanet_otp_logs('Modelo create_update','Limite de usuario en la misma ip'.json_encode(['id'=>$id_usuario,'ip'=>$ip_usuario]));
        $tiempo_bloqueo = strtotime ( "+{$this->time_resend_hard} second" , time() );
        $tiempo_bloqueo_flat = $this->time_resend_hard;

      }else{
        if((($intentos_del_telefono +1) % $this->max_attends_user_phone) == 0){
          gameplanet_otp_logs('Modelo create_update','Limite de usuario con el mismo telefono'.json_encode(['id'=>$id_usuario,'ip'=>$ip_usuario]));

          $tiempo_bloqueo = strtotime ( "+{$this->time_resend_medium} second" , time() );
          $tiempo_bloqueo_flat = $this->time_resend_medium;

        }
        else{
          gameplanet_otp_logs('Modelo create_update','Bloqueop normal entre peticiones'.json_encode(['id'=>$id_usuario,'ip'=>$ip_usuario]));

          $tiempo_bloqueo = strtotime ( "+{$this->time_resend_soft} second" , time() );
          $tiempo_bloqueo_flat = $this->time_resend_soft;

        }
      }
    }
    

    if(!$update){
      gameplanet_otp_logs('Modelo create_update','es el mimso usuario diferente tel:'.$phone);
      
      $wpdb->insert($this->table_users,[
        "user_id"=>$id_usuario,
        "user_ip"=>$ip_usuario,
        "user_phone"=>$phone,
        "user_email"=>$email,
        "resend_attends"=>1,
        "time_blocked"=>$tiempo_bloqueo,
      ]);
    }
    else{
      
      $update_data_user = [
        "resend_attends"=> $intentos_del_telefono + 1,
        "time_blocked"=>$tiempo_bloqueo
      ];
      $wpdb->update($this->table_users, $update_data_user,["id"=>$update_id]);
    }

    //actualizamos IP
    $update_data_ip = [
      "resend_attends"=>(int)$existe_ip[0]->resend_attends + 1,
    ];
    //si ya llego al limite de peticones por IP debemos  bloquear la IP
    if($bloquear_ip){
      //bloqueamos la ip con un tiempo en especifico
      $update_data_ip["time_blocked"] = $tiempo_bloqueo;
    }
    $wpdb->update($this->table_ip, $update_data_ip,["id"=>$existe_ip[0]->id]);

    return [
      "success"=>false,
      "data"=>[
        "time_blocked"=>$tiempo_bloqueo_flat
      ]
    ];

  }

  /**
   * Validamos si el usuario actual puede enviar codigos
   */
  public function validateCanSend($idUser,$ip,$email=''){
    try {
      $current_attempt = $this->show($idUser,$ip,$email);
     
      if(!$current_attempt['success']){
        //algo fallo en la consulta del intento
        return $current_attempt;
      }
       //si no existe entonces dejamos pasar (es el primer intento en teoria)
      if(empty($current_attempt['data'])){
        return[
          "success"=>true,
          "message"=>"Puede enviar es el primer intento",
          "data"=>[]
        ];
      }

      //si encontramos el registro de intento vamos a pasar las reglas de las mas severa para atras
      $current_attempt = $current_attempt['data'][0];

      $regla1_IP_Bloqueda = $this->regla1_IP_Bloqueda($current_attempt);
      if(!$regla1_IP_Bloqueda['success']){
        //bloqueo completo IP
        return[
          "success"=>false,
          "code"=>1,
          "message"=>"Bloqueo completo IP",
          "data"=>[
            "time_blocked"=>$regla1_IP_Bloqueda['data'],
          ]
        ];
      }
      $regla2_IP_Usuario_Bloqueda = $this->regla2_IP_Usuario_Bloqueda($current_attempt);
      if(!$regla2_IP_Usuario_Bloqueda['success']){
        //Bloqueo ip por usurio
        return[
          "success"=>false,
          "code"=>2,
          "message"=>"Bloqueo ip por usuario",
          "data"=>[
            "time_blocked"=>$regla2_IP_Usuario_Bloqueda['data'],
          ]
        ];
      }
      $regla3_Usuario_Telefono = $this->regla3_Usuario_Telefono($current_attempt);
      if(!$regla3_Usuario_Telefono['success']){
        //Bloqueo usuario por telefono
        return[
          "success"=>false,
          "code"=>3,
          "message"=>"Bloqueo usuario por telefono",
          "data"=>[
            "time_blocked"=>$regla3_Usuario_Telefono['data'],
          ]
        ];
      }
      $regla3_Usuario_Telefono = $this->regla3_Usuario_Telefono($current_attempt);
      if(!$regla3_Usuario_Telefono['success']){
        //Bloqueo usuario por telefono
        return[
          "success"=>false,
          "code"=>3,
          "message"=>"Bloqueo usuario por telefono",
          "data"=>[
            "time_blocked"=>$regla3_Usuario_Telefono['data'],
          ]
        ];
      }
      $regla4_Usuario_Normal = $this->regla4_Usuario_Normal($current_attempt);
      if(!$regla4_Usuario_Normal['success']){
        //Bloqueo usuario normal
        return[
          "success"=>false,
          "code"=>4,
          "message"=>"Bloqueo usuario normal",
          "data"=>[
            "time_blocked"=>$regla4_Usuario_Normal['data'],
          ]
        ];
      }
      //paso todos los castigos
      return[
        "success"=>true,
        "code"=>0,
        "message"=>"Puede hacer peticion",
        "data"=>[]
      ];

    } catch (\Exception $e) {
      gameplanet_otp_logs("ERRRO GP_OTP_USERS_MODEL: ",$e->getMessage());
      return[
        "success"=>false,
        "code"=>500,
        "message"=>"CODE-500-GOUM-SBU",
        "data"=>[]
      ];
    }
    
  }

  /**
   * Verifica que no alla un bloque de IP general
   */
  private function regla1_IP_Bloqueda($data){
    $peticiones_ip = $data->ip_resend_attends % $this->max_ip_attends; // 0 esta en el limite, otro puede pasar
    $tiempo_para_liberar = (int) $data->ip_time_blocked - time();// + aun falta , - o 0 ya paso el tiempo
    //si esta en el limite de peticion y no a pasado el tiempo
    if($peticiones_ip == 0 && $tiempo_para_liberar > 0){
      gameplanet_otp_logs('regla1_IP_Bloqueda','no puede pasar limite por IP');
      return [
        'success'=>false,
        'data' => $tiempo_para_liberar
      ];
    }
    return [
      'success'=>true,
    ];

  }

  /**
   * El usuario llega al limite con la misma IP
   */
  private function regla2_IP_Usuario_Bloqueda($data){
    $peticiones_ip_del_usuario = $data->resend_attends % $this->max_ip_attends_user; // 0 esta en el limite, otro puede pasar
    $tiempo_para_liberar = (int) $data->time_blocked - time();// + aun falta , - o 0 ya paso el tiempo
    //si esta en el limite de peticion y no a pasado el tiempo
    if($peticiones_ip_del_usuario == 0 && $tiempo_para_liberar > 0){
      gameplanet_otp_logs('regla2_IP_Usuario_Bloqueda','no puede pasar limite por IP por usuario');
      return [
        'success'=>false,
        'data' => $tiempo_para_liberar
      ];
    }
    return [
      'success'=>true,
    ];
  }
  
  /**
   * El usuario llega al limite con el mismo telefono
   */
  private function regla3_Usuario_Telefono($data){
    $peticiones_ip_del_usuario = $data->resend_attends % $this->max_attends_user_phone; // 0 esta en el limite, otro puede pasar
    $tiempo_para_liberar = (int) $data->time_blocked - time();// + aun falta , - o 0 ya paso el tiempo
    //si esta en el limite de peticion y no a pasado el tiempo
    if($peticiones_ip_del_usuario == 0 && $tiempo_para_liberar > 0){
      gameplanet_otp_logs('regla3_Usuario_Telefono','no puede pasar limite por usuario mismo telefono');
      return [
        'success'=>false,
        'data' => $tiempo_para_liberar
      ];
    }
    return [
      'success'=>true,
    ];
  }
  
  /**
   * Bloqueo entre peticiones del mismo usuario
   */
  private function regla4_Usuario_Normal($data){
    $tiempo_para_liberar = (int) $data->time_blocked - time();// + aun falta , - o 0 ya paso el tiempo
    //si esta en el limite de peticion y no a pasado el tiempo
    if($tiempo_para_liberar > 0){
      gameplanet_otp_logs('regla4_Usuario_Normal','no puede pasar limite entre peticiones iguales');
      return [
        'success'=>false,
        'data' => $tiempo_para_liberar
      ];
    }
    return [
      'success'=>true,
    ];
  }


  /**
   * Si por alguna razon el gateway (twilio) falla
   * se guarda el log y se manda a mantenimiento si se llego al meximo de errores
   * @param string $gateway
   * @param string $event
   * @param string $log
   */
  public function registrarErrorGateway($gateway,$event,$log){
    try {
      //registramos el log
      global $wpdb;
      $wpdb->insert($this->table_error_logs,[
        "gateway"=>$gateway,
        "event"=>$event,
        "log"=>$log,
      ]);
      //contamos cuantos logs hay hoy
      $total_logs_query ="
          SELECT *
          FROM {$this->table_error_logs} 
          WHERE event = '{$event}'
          AND (created_at between current_date() AND current_timestamp())
          ORDER BY updated_at DESC
          ;
        ";
      $total_logs = $wpdb->get_results($total_logs_query); 
      if(count($total_logs) == $this->max_gateway_errors){
        //mandamos a mantenimiento
        update_option( 'gp_otp-active', 0);


      }
      return true;
      
    } catch (\Exception $e) {
      gameplanet_otp_logs("registrarErrorGateway",'ERROR NO se registro el LOG',$e->getMessage());
      return false;
      /* return[
        "success"=>false,
        "message"=>"CODE-500-GOUM-SBU",
        "data"=>[]
      ]; */
    }
  }
  public function countErrors(){
    try {
      global $wpdb;

      $total_logs_query ="
      SELECT COUNT(*) as total
      FROM {$this->table_error_logs} 
      WHERE admin_watched != 1
      ;
    ";
    $total_logs = $wpdb->get_results($total_logs_query); 
 
      return $total_logs[0]['total'];

    } catch (\Throwable $th) {
      return 0;

    }
  }
}

