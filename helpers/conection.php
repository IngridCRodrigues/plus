<?php

use \PDO;

class Connection {

    /**
     * database connection
     *
     * @return object \PDO
     */
    public static function start() {
        protected $connection;

        try {
            $env = array();
            foreach(file('../.env') as $line) {
                list($key, $value) = explode('=', $line, 2) + array(NULL, NULL);
                $env[trim($key)] = trim($value);
            }
            $connection = new PDO($env['DB_CONNECTION'].":host=".$env['DB_HOST'].";dbname=".$env['DB_NAME'], $env['DB_USER'], $env['DB_PASSWORD'], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            //$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo $e->getMessage();
        }

        return $connection;
    }
}
