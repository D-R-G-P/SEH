<?php

require_once 'db.php';

class User extends DB
{
    private $dni;
    private $password;
    private $apellido;
    private $nombre;
    private $servicio;
    private $cargo;
    private $especialidad;
    private $mn;
    private $mp;
    private $sistemas;
    private $rol;

    public function userExists($dni, $password)
    {
        $md5pass = md5($password);

        $query = $this->connect()->prepare('SELECT * FROM personal WHERE dni = :dni AND password = :password');
        $query->execute(['dni' => $dni, 'password' => $md5pass]);

        if ($query->rowCount()) {
            return true;
        } else {
            return false;
        }
    }

    public function setUser($dni)
    {
        $query = $this->connect()->prepare('SELECT password, apellido, nombre, dni, servicio, cargo, especialidad, mn, mp, sistemas, rol FROM personal WHERE dni = :dni');
        $query->execute(['dni' => $dni]);

        if ($query->rowCount()) {
            $result = $query->fetch(PDO::FETCH_ASSOC);
            $this->dni = $result['dni'];
            $this->password = $result['password'];
            $this->apellido = $result['apellido'];
            $this->nombre = $result['nombre'];
            $this->servicio = $result['servicio'];
            $this->cargo = $result['cargo'];
            $this->especialidad = $result['especialidad'];
            $this->mn = $result['mn'];
            $this->mp = $result['mp'];
            $this->sistemas = $result['sistemas'];
            $this->rol = $result['rol'];
        }
    }

    public function getDni() {
        return $this->dni;
    }
    public function getApellido() {
        return $this->apellido;
    }
    public function getNombre() {
        return $this->nombre;
    }

    public function getServicio() {
        return $this->servicio;
    }

    public function getCargo() {
        return $this->cargo;
    }

    public function getEspecialidad() {
        return $this->especialidad;
    }

    public function getMn() {
        return $this->mn;
    }

    public function getMp() {
        return $this->mp;
    }

    public function getSistemas() {
        return $this->sistemas;
    }
    public function getRol() {
        return $this->rol;
    }
}