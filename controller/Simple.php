<?php

class Simple extends OneFileFramework
{

    public function home(){
        $content = self::getView(__DIR__.'/../view/database.php', []);
        $this->show([
            'content' => $content
            ,'databases' => PGSQL::getAllDatabases()
        ]);
    }

    public function execute_request(){
        $_SESSION['current_table'] = $_POST['table'];
        PGSQL::setDatabase($_SESSION['current_BDD']);

        $executionStartTime = microtime(true);

        $response = [];
        $datas = Pagination::query($response, $_POST['request']);

        $seconds = microtime(true) - $executionStartTime;

        echo json_encode([
            'datas' => $datas
            ,'infos' => [
                'execution_time' => $seconds
                ,'page_curr' => $response['page_curr']
                ,'page_nb' => $response['page_nb']
                ,'page_size' => $response['page_size']
                ,'is_last_page' => $response['is_last_page']
            ]
        ]);
        die();
    }

    public function get_schema(){
        $_SESSION['current_BDD'] = $_POST['database'];
        PGSQL::setDatabase($_SESSION['current_BDD']);
        $schema = PGSQL::schemas();

        $found = false;
        foreach ($schema as $s){
            if($s['table_schema'] != ($_SESSION['current_schema'] ?? null) ){
                continue;
            }
            $found = true;
        }

        echo json_encode([
            'schemas' => $schema
            ,'current_schema' => $found ? $_SESSION['current_schema'] : null
        ]);
        die();
    }

    public function get_table(){
        $_SESSION['current_schema'] = $_POST['schema'];
        PGSQL::setDatabase($_SESSION['current_BDD']);
        $tables = PGSQL::getTable($_SESSION['current_schema']);

        $found = false;
        foreach ($tables as $s){
            if($s['table_name'] != ( $_SESSION['current_table'] ?? null) ){
                continue;
            }
            $found = true;
        }

        echo json_encode([
            'tables' => $tables
            ,'current_table' => $found ? $_SESSION['current_table'] : null
        ]);
        die();
    }

}