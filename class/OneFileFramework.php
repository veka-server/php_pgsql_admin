<?php
/**
 * Class OneFileFramework
 */
class OneFileFramework
{
    private static $route = array();
    private static $folder_list = array('class', 'controller');
    public static $current_route_name = null;

    /**
     * Autoload des classes
     */
    public static function AutoloadClass(){
        spl_autoload_register(
            function ($class_name) {

                foreach (self::$folder_list as $folder){
                    $file = __DIR__.'/../'.$folder.'/'.$class_name.'.php';
                    if(is_file($file)){
                        require($file);
                        return ;
                    }
                }

            }
        );
    }

    /**
     * Gestion des erreurs
     * @param int $level
     */
    public static function ErrorHandler($level = -1){
        error_reporting($level);

        set_error_handler(function($errno, $errstr, $errfile, $errline ){
            throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
        });

        set_exception_handler(
            function($exception){
                /** @var \Exception $exception */
                echo "Uncaught exception: " , $exception->getMessage(), "\n";

                self::dump($exception);

                die();
            }
        );

    }

    /**
     * Pretty dump
     * @param $var
     * @param bool $die
     */
    public static function dump($var, $die = false)
    {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';

        if( $die )
            die();
    }

    /**
     * Ajouter une route
     * @param string|array $url
     * @param string $class
     * @param string $method
     */
    public static function addRoute($url, $class, $method, $name = null){
        self::$route[] = array('url'=>$url, 'class'=>$class, 'method'=>$method, 'name' => $name);
    }

    /**
     * Dispatch
     */
    public static function run(){

        $url = strtok($_SERVER["REQUEST_URI"],'?');

        $file_path = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, explode('/', $url));
        $file_path = realpath($file_path);
        $asset_path = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
        $request_uri = substr( $file_path, strlen($asset_path));

        if(substr( $request_uri, 0,6) === DIRECTORY_SEPARATOR.'asset'){
            if(!is_file($file_path))
                return ;

            $ct = mime_content_type($file_path);
            if ($ct !== false) {

                // si text ont check si css
                if($ct === 'text/plain'){
                    $file_parts = pathinfo($file_path);

                    switch($file_parts['extension'])
                    {
                        case "css":
                            $ct = 'text/css';
                            break;
                    }

                }

                header('Content-type: '.$ct);
            }

            readfile($file_path);
            die();
        }

        foreach (self::$route as $route){

            if(!is_array($route['url']))
                $route['url'] = array($route['url']);

            foreach($route['url'] as $regex) {

                $len = strlen($regex);

                if ($len <= 0)
                    continue;

                // Fix missing begin-/
                if ($regex[0] != '/')
                    $regex = '/' . $regex;

                // Fix trailing /
                if ($len > 1 && $regex[$len - 1] == '/')
                    $regex = substr($regex, 0, -1);

                // Prevent @ collision
                $regex = str_replace('@', '\\@', $regex);
                $test = preg_match('@^' . $regex . '$@', $url, $params);

                // If the path matches the pattern
                if ($test) {
                    // Pass the params to the callback, without the full url
                    array_shift($params);

                    self::$current_route_name = $route['name'] ?? null;

                    $obj = new $route['class']();
                    $obj->{$route['method']}($params);
                    return ;
                }

            }

        }

        echo 'ERROR 404 : Page not found';
        return ;
    }

    /**
     * @param $file
     * @param array $data
     * @return false|string
     * @throws Exception
     */
    public function getView($file, /** @noinspection PhpUnusedParameterInspection */ $data){

        if(!is_file($file)){
            throw new Exception('View introuvable : '.$file);
        }

        ob_start();
        include($file);
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    /**
     * @param array $params
     * @throws Exception
     */
    public function show($params = array()){
        echo $this->getView(__DIR__.'/../view/template.php', $params);
    }

    /**
     * @param $str
     * @return string
     */
    public static function menu($str){
        return self::$current_route_name == $str ?  'class="active"' : '';
    }

}