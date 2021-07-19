<?php

/**
 * Class Pagination
 */
class Pagination
{

    /**
     * @return string
     */
    public static function getLimitSQL()
    {

        if( ($_POST['page_curr'] ?? 0) <= 0 ){
            $_POST['page_curr'] = 1;
        }

        if( ($_POST['page_size'] ?? 0 ) <= 0 ){
            $_POST['page_size'] = 20;
        }

        return ' LIMIT '.(int) $_POST['page_size'].' OFFSET '.((int) $_POST['page_curr']*(int)$_POST['page_size'] - (int)$_POST['page_size']);
    }

    /**
     * @return string
     */
    public static function getSelectSQL()
    {
        return ' count(*) OVER() AS count_self ';
    }

    /**
     * @param $rs
     * @return int
     */
    public static function getNbLineFromRS(&$rs)
    {
        foreach ($rs as $key => $line){
            if(!isset($rs[$key]['count_self'])){
                continue;
            }
            $nb_line = $rs[$key]['count_self'];
            unset($rs[$key]['count_self']);
        }
        return (int) ($nb_line ?? 0 );
    }

    /**
     * Modifie une requete select pour l'utiliser avec la self
     * La requete doit commencer par select
     * elle ne doit pas avoir de limit deja existant
     * Si la requete ne commence pas par select on l'execute de facon classic
     * @param &$response
     * @param $sql
     * @param array $param_sql
     * @return array
     * @throws \Exception
     */
    public static function query(&$response, $sql, $param_sql=[]){

        $clean_sql = trim($sql);

        $response['page_curr'] = (int) ($_POST['page_curr'] ?? 1);

        if($response['page_curr'] <= 0 ){
            $response['page_curr'] = 1;
        }

        if(substr( $clean_sql, 0, strlen( 'SELECT' ) ) !== 'SELECT'){
            throw new \Exception('la requete doit commencer par un select');
        }

        $clean_sql = self::addFiltre($clean_sql);

        /**
         * estimation du nombre de ligne d'une table

            SELECT reltuples::bigint AS estimate
            FROM   pg_class
            WHERE  oid = 'projection_compta_campagne'::regclass;

         */

//      $clean_sql = preg_replace('/SELECT/i','SELECT '.self::getSelectSQL().', ', $clean_sql, 1);
        $clean_sql .= self::getLimitSQL();

        $rs = PGSQL::executeRequest($clean_sql);

        if(empty($rs)){
            $response['page_nb'] = 1;
            return [];
        }

        // recuperer le nombre de ligne depuis le rs
        $nb_line = self::getNbLineFromRS($rs);
        $response['page_nb'] = ceil($nb_line / (int) $_POST['page_size']);

        if($response['page_nb'] <= 0 ){
            $response['page_nb'] = 1;
        }

        return $rs;
    }

    /**
     * @param string $clean_sql
     * @return string
     */
    private static function addFiltre(string $clean_sql)
    {
        if(!isset($_POST['filtre']) || empty($_POST['filtre'])) {
            return $clean_sql;
        }

        $where = [];
        foreach ($_POST['filtre'] as $name => $value){
            $value = trim($value);
            if(empty($value)){
                continue;
            }
            $where[] = $name.' LIKE \'%'.$value.'%\'';
        }

        return 'SELECT main.* FROM ('.$clean_sql.') main '.( count($where) > 0 ? ' WHERE ' : '').implode(' AND ', $where);
    }

}