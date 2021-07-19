<?php

/**
 * Class PGSQL
 */
class PGSQL
{

    /**
     * @var null|PDO
     */
    private static $con = null;
    private static $database = 'dev_l16_veolia';
    private static $password;
    private static $user;
    private static $port;
    private static $host;

    public static function cleanString($str){
        return preg_replace('/[^A-Za-z0-9\-_]/', '', $str);
    }

    /**
     * @return PDO|null
     */
    public static function getConnexion(){

        if(!empty(self::$con))
            return self::$con;

        try {
            $database =  self::cleanString(self::$database);
            self::$con = new PDO('pgsql:host='.self::$host.';port='.self::$port.';dbname='.$database.';', self::$user, self::$password);
            self::$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
            die();
        }

        return self::$con;
    }

    /**
     * @param $database
     */
    public static function setDatabase($database){
        $database =  self::cleanString($database);
        self::$database = $database;
        self::$con = null;
    }

    /**
     * @param $host
     */
    public static function setHost($host){
        self::$host = $host;
    }

    /**
     * @param $port
     */
    public static function setPort($port){
        self::$port = $port;
    }

    /**
     * @param $user
     */
    public static function setUser($user){
        self::$user = $user;
    }

    /**
     * @param $password
     */
    public static function setPassword($password){
        self::$password = $password;
    }

    /**
     * @return array
     */
    public static function getRequests(){

        $query = "SELECT datname as Database, pid as PID, query AS Query, (NOW() - query_start) as Time, state
                        FROM pg_catalog.pg_stat_activity
                        WHERE 
                              query NOT LIKE '%exclude_me1652%' 
                              AND 'exclude_me1652' = 'exclude_me1652'
                        ORDER BY datname, query_start DESC;";
        $result = self::getConnexion()->query($query);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array
     */
    public static function getAllDatabases(){
        $query = "SELECT datname FROM pg_database WHERE datistemplate = false;";
        $result = self::getConnexion()->query($query);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array
     */
    public static function getAllTables(){
        $query = "SELECT table_schema,table_name FROM information_schema.tables ORDER BY table_schema,table_name;";
        $result = self::getConnexion()->query($query);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array
     */
    public static function getTable($schema){
        $query = "SELECT table_name FROM information_schema.tables WHERE table_schema = :table_schema ORDER BY table_name;";
        $sth = self::getConnexion()->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->execute(array(':table_schema' => $schema));
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array
     */
    public static function schemas()
    {
        $query = "SELECT distinct table_schema FROM information_schema.tables ORDER BY table_schema;";
        $result = self::getConnexion()->query($query);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function executeRequest($request)
    {
        $query = $request;
        $result = self::getConnexion()->query($query);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

}