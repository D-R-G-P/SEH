<?php

require_once 'app/db/db.php';

class User extends DB
{

    private $tipo_usuario;
    private $username;
    private $pass;

    public function userExists($user, $pass)
    {
        $md5pass = md5($pass);

        $query = $this->connect()->prepare('SELECT * FROM user WHERE username = :user AND password = :pass');
        $query->execute(['user' => $user, 'pass' => $md5pass]);

        if ($query->rowCount()) {
            return true;
        } else {
            return false;
        }
    }

    public function setUser($user)
    {
        $query = $this->connect()->prepare('SELECT password, tipo_usuario, username FROM user WHERE username = :user');
        $query->execute(['user' => $user]);

        if ($query->rowCount()) {
            $result = $query->fetch(PDO::FETCH_ASSOC);
            $this->pass = $result['password'];
            $this->tipo_usuario = $result['tipo_usuario'];
            $this->username = $result['username'];
        }
    }

    public function getUsername() {
        return $this->username;
    }
    public function getTipo_usuario() {
        return $this->tipo_usuario;
    }
    public function getPassword() {
        return $this->pass;
    }
}