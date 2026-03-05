<?php

// Define a constante DIRETORIO_BACKEND apenas se ainda não estiver definida
if (!defined('DIRETORIO_BACKEND')) {
    define('DIRETORIO_BACKEND', '../../backend/app/');
}


// Evita redefinir a classe DbConnection se já foi carregada anteriormente
if (!class_exists('DbConnection')) {
    class DbConnection
    {
        private $host = "10.33.0.4";
        private $dbname = "pdv_tic";
        private $user = "infra";
        private $pass = "8ca06h8rC3QV";

        public function getConnection()
        {
            try {
                $connect = new PDO(
                    "mysql:host=$this->host;dbname=$this->dbname",
                    $this->user,
                    $this->pass
                );
                $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return $connect;
            } catch (PDOException $e) {
                echo "Erro: " . $e->getMessage();
            } catch (Exception $e) {
                echo "Erro: " . $e->getMessage();
            }
        }
    }
}
