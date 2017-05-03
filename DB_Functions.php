<?php
 
class DB_Functions {
 
    private $db;
 

    function __construct() {
        require_once 'DB_Connect.php';       
        $this->db = new DB_Connect();
        $this->db->connect();
    }
 
    function __destruct() {
 
    }
 
    /**
     * Guardamos nuevos usuarios
     * Devolvemos detalle del usuario
     */
    public function storeUser($name, $email, $password) {
        $uuid = uniqid('', true);
        $hash = $this->hashSSHA($password);
        $encrypted_password = $hash["encrypted"]; // password encriptada
        $salt = $hash["salt"]; // salt
        $result = mysql_query("INSERT INTO users(unique_id, name, email, encrypted_password, salt, created_at) VALUES('$uuid', '$name', '$email', '$encrypted_password', '$salt', NOW())");
        // comprobamos que la query se ejecutó correctamente
        if ($result) {
            // obtener detalle del usuario
            $uid = mysql_insert_id(); 
            $result = mysql_query("SELECT * FROM users WHERE uid = $uid");
            // devolvemos detalle del usuario
            return mysql_fetch_array($result);
        } else {
            return false;
        }
    }
 
    /**
     * Obtenemos usuario por email y contraseña
     */
    public function getUserByEmailAndPassword($email, $password) {
        $result = mysql_query("SELECT * FROM users WHERE email = '$email'") or die(mysql_error());
        // verificamos el resultado de la query
        $no_of_rows = mysql_num_rows($result);
        if ($no_of_rows > 0) {
            $result = mysql_fetch_array($result);
            $salt = $result['salt'];
            $encrypted_password = $result['encrypted_password'];
            $hash = $this->checkhashSSHA($salt, $password);
            
            if ($encrypted_password == $hash) {
                // Se ha autenticado al usuario correctamente
                return $result;
            }
        } else {
            // usuario no encontrado
            return false;
        }
    }
 
    /**
     * Comprobamos si el usuario existe o no
     */
    public function isUserExisted($email) {
        $result = mysql_query("SELECT email from users WHERE email = '$email'");
        $no_of_rows = mysql_num_rows($result);
        if ($no_of_rows > 0) {
            // usuario existe
            return true;
        } else {
            // usuario no existe
            return false;
        }
    }
 
    /**
     * Encriptamos la password
     * devolvemos el salt y la password encriptada
     */
    public function hashSSHA($password) {
 
        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);
        return $hash;
    }
 
    /**
     * Desencriptamos la password
     * Devolvemos una hash string
     */
    public function checkhashSSHA($salt, $password) {
 
        $hash = base64_encode(sha1($password . $salt, true) . $salt);
 
        return $hash;
    }
 
}
 
?>