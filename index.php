<?php
  /**
 * Comprobamos la petición POST
 */
  

if (isset($_POST['tag']) && $_POST['tag'] != '') {
    // obtenemos tag
    $tag = $_POST['tag'];
    write_log("Tag: ".$tag, "INFO");
 
    require_once 'DB_Functions.php';
    $db = new DB_Functions();
 
    // Empezamos a preparar la respuesta en forma de Array que luego convertiremos en un json
    $response = array("tag" => $tag, "success" => 0, "error" => 0);
 
    // evaluamos tag
    if ($tag == 'login') {
        // chequeamos el login
        $email = $_POST['email'];
        $password = $_POST['password'];
 
        // obtenemos y comprobamos el usuario por email y password
        $user = $db->getUserByEmailAndPassword($email, $password);
        if ($user != false) {
            // usuario encontrado
            // marcamos el json como correcto con success = 1
            $response["success"] = 1;
            $response["uid"] = $user["unique_id"];
            $response["user"]["name"] = $user["name"];
            $response["user"]["email"] = $user["email"];
            $response["user"]["created_at"] = $user["created_at"];
            $response["user"]["updated_at"] = $user["updated_at"];
            echo json_encode($response);
        } else {
            // usuario no encontrado
            // marcamos el json con error = 1
            $response["error"] = 1;
            $response["error_msg"] = "Email o password incorrecto!";
            echo json_encode($response);
        }
    } else if ($tag == 'register') {
        // Registrar nuevo usuario
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
 
        // comprobamos si ya existe el usuario
        if ($db->isUserExisted($email)) {
            // usuario ya existe - marcamos error 2
            $response["error"] = 2;
            $response["error_msg"] = "Usuario ya existe";
            echo json_encode($response);
        } else {
            // guardamos el nuevo usuario
            $user = $db->storeUser($name, $email, $password);
            if ($user) {
                // usuario guardado correctamente
                $response["success"] = 1;
                $response["uid"] = $user["unique_id"];
                $response["user"]["name"] = $user["name"];
                $response["user"]["email"] = $user["email"];
                $response["user"]["created_at"] = $user["created_at"];
                $response["user"]["updated_at"] = $user["updated_at"];
                echo json_encode($response);
            } else {
                // fallo en la inserción del usuario
                $response["error"] = 1;
                $response["error_msg"] = "Ocurrió un error durante el registro";
                echo json_encode($response);
            }
        }
    } else {
        echo "Petición no válida";
    }
} else {
    echo "Acceso denegado";
}

/**
 * Escribe lo que le pasen a un archivo de logs
 * @param string $cadena texto a escribir en el log
 * @param string $tipo texto que indica el tipo de mensaje. Los valores normales son Info, Error,  
 *                                       Warn Debug, Critical
 */
function write_log($cadena,$tipo)
{
    $arch = fopen(realpath( '.' )."/logs/log_".date("Y-m-d").".txt", "a+"); 

    fwrite($arch, "[".date("Y-m-d H:i:s.u")." ".$_SERVER['REMOTE_ADDR']." ".
                   $_SERVER['HTTP_X_FORWARDED_FOR']." - $tipo ] ".$cadena."\n");
    fclose($arch);
}

?>