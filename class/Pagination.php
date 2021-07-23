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

        return ' LIMIT '.self::getPageSize().' OFFSET '.((int) $_POST['page_curr']* self::getPageSize() - self::getPageSize());
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

    public static function getPageSize(){
        if( ($_POST['page_size'] ?? 0 ) <= 0 ){
            $_POST['page_size'] = 20;
        }
        return (int) $_POST['page_size'];
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

        if(substr( $clean_sql, 0, strlen( 'SELECT' ) ) !== 'SELECT'){
            throw new \Exception('la requete doit commencer par un select');
        }

        $last_page_requested = (($_POST['page_curr'] ?? '') == 'last');

        /** si la requete porte demande explicitement la derniere page */
        if($last_page_requested == true) {
            /** realise un count pour connaitre le nombre de page mais impact pas mal les performances */
            $count_sql = 'SELECT count(*) AS count_self FROM ('.$clean_sql.') main ';
            $rs = PGSQL::executeRequest($count_sql);
            $nb_line = self::getNbLineFromRS($rs);
            $_POST['page_curr'] = ceil($nb_line / self::getPageSize() );
        }

        $response['page_curr'] = (int) ($_POST['page_curr'] ?? 1);

        if($response['page_curr'] <= 0 ){
            $response['page_curr'] = 1;
        }

        $clean_sql = 'SELECT main.* FROM ('.$clean_sql.') main ';
        $clean_sql .= self::getLimitSQL();

        $rs = PGSQL::executeRequest($clean_sql);

        $response['page_size'] = self::getPageSize();
        $response['is_last_page'] = self::getPageSize() > count($rs) || $last_page_requested ;

        if(empty($rs)){
            if($response['page_curr'] > 1){
                $_POST['page_curr'] = 'last';
                return self::query($response, $sql, $param_sql);
            } else {
                $response['page_nb'] = 1;
                return [];
            }
        }

        if($response['page_nb'] ?? 0 <= 0 ){
            $response['page_nb'] = 1;
        }

        return $rs;
    }

    /**
     * @param string $clean_sql
     * @param $param_sql
     * @return string
     */
    private static function addFiltre(string $clean_sql, &$param_sql)
    {
        if(!isset($_POST['filtre']) || empty($_POST['filtre'])) {
            return $clean_sql;
        }

        $where = [];
        foreach ($_POST['filtre'] as $name => $value){
            $value = trim($value);
            if(empty($value) || $name == 'order_by'){
                continue;
            }

            if(strpos($name, '-') === false){
                $name = 's-'.$name;
            }

            list($prefix, $name) = explode('-',$name);

            $name = preg_replace('/[^a-zA-Z0-9_-]/s', '', $name);

            switch ($prefix){

                case 'i' :
                    $where[] = 'LOWER('.$name.'::text) LIKE :'.$name.'';
                    $param_sql['s-'.$name] = '%'.(int)$value.'%';
                    break;

                case 's' :
                    $where[] = 'LOWER('.$name.') LIKE LOWER(:'.$name.')';
                    $param_sql['s-'.$name] = '%'.$value.'%';
                    break;

            }

        }

        return 'SELECT main.* FROM ('.$clean_sql.') main '.( count($where) > 0 ? ' WHERE ' : '').implode(' AND ', $where);
    }

}