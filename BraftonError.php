<?php
/*
 * Brafton Error Class
 * rewrite for seperate function to get the erros currently logged, add new error function
 */
//for debugging.  Displays
class BraftonErrorReport {

    /*
     *$url Current url location
     */
    private $url;
    /*
     *$e_key Encryption key for verification for the error logging api
     */
    private $e_key;
    /*
     *$post_url Url location for error reporting with $e_key as [GET] Parameter
     */
    private $post_url;
    /*
     *$section Current sectoin reporting the error set by passing variable to the set_section method
     */
    private $section;
    /*
     *$level current brafton level of severity set by passing int variable to the set_level method
     */
    public $level;

    public $debug;

    private $domain;
    //Construct our error reporting functions
    public function __construct($api, $brand , $debug = false){

        $this->debug = $debug;
        $this->url = $_SERVER['REQUEST_URI'];
        $this->domain = $_SERVER['HTTP_HOST'];
        //$this->domain = 'mydomain.com';
        $this->api = $api;
        $this->brand = $brand;
        $this->e_key = 'i2kwarez98z6zcmnkx3iyuj3';
        $this->post_url = 'http://updater.brafton.com/errorlog/drupal7/error/'.$this->e_key;
        $this->level = 1;
        $this->section = 'error initialize';
        register_shutdown_function(array($this,  'check_for_fatal'));
        set_error_handler(array($this, 'log_error') );
        set_exception_handler(array($this, 'log_exception'));
        ini_set( "display_errors", 0 );
        error_reporting( E_ALL );

        // Remove 'http://' from 'brafton_api_root' if present
        if ( substr( $this->api, 0, 7 ) == 'http://' ) {
            variable_set('brafton_api_root', substr($this->api, 7));
        }

    }
    //handles the error log page
    static function errorPage(){
        if($_POST['braftonClearLog'] == 1){
            delete_option('brafton_e_log');
        }
        $_POST['braftonClearLog'] = 0;
        $save = BraftonOptions::saveAllOptions();
    }
    //Sets the current section reporting the error periodically set by the article and video loops themselves
    public function set_section($sec){
        $this->section = $sec;
    }
    //sets the current level of error reporting used to determine if remote sending is enabled periodically upgraded during article and video loops from 1 (critical error script stopped running) -> 5 (minor error script continued but something happened.)
    public function set_level($level){
        $this->level = $level;
    }
    //upon error being thrown log_error fires off to throw an exception erro
    public function log_error( $num, $str, $file, $line, $context = null )
    {
        $this->log_exception( new ErrorException( $str, 0, $num, $file, $line ) );
    }
    //retrieves the current error log from the db returns an array of current logs
    private function b_e_log(){
        if(!$brafton_error = variable_get('brafton_e_log')){
            variable_set('brafton_e_log', '');
            $brafton = null;
        }
        else{
            $brafton_error = unserialize(variable_get('brafton_e_log'));
            $brafton = $brafton_error;
        }
        return $brafton;
    }
    //Known minor Errors occuring from normal operation.
    public function check_known_errors($e){

    }
    //workhorse of the error reporting.  This function does the heavy lifting of logging the error and sending an error report
    public function log_exception( Exception $e ){
        //Drupal uses @ to supress certain errors lets ignore those as well.
        if(error_reporting() == 0){
            return;
        }
        //assigns values for missing arguments on custom exceptions from the api libarary
        $errorLevel = method_exists($e,'getseverity')? $e->getseverity(): 1;
        //if errorLevel == 1 (script stop running error) and the error was not part of one of the below know issues for those pages runs error reporting.
        if ( ($errorLevel == 1) || ($this->debug) ){

            $brafton_error = $this->b_e_log();
            $errorlog = array(
                'Domain'    => $this->domain,
                'API'       => $this->api,
                'Brand'     => $this->brand,
                'client_sys_time'  => date('Y-m-d H:i:s'),
                'error'     => get_class($e).' : '.$errorLevel.' | '.$e->getMessage().' in '.$e->getFile().' on line '.$e->getLine().' brafton_level '.$this->level.' in section '.$this->section.$this->debug
            );

            $brafton_error[] = $errorlog;
            $brafton_error = serialize($brafton_error);
            //update_option('brafton_e_log', $brafton_error);
            $errorlog = json_encode($errorlog);
            $post_args = array(
                    'error' => $errorlog
            );
            variable_set('brafton_e_log', $brafton_error);
            //$this->level = 2;
            if(($errorLevel == 1 || ($this->debug == true && $this->level == 1)) && strpos($this->domain, 'localhost') === false){
                //prevent possible loop on some systems

                $ch  = curl_init();
                curl_setopt($ch, CURLOPT_URL, $this->post_url);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_args);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_exec($ch);
                variable_set('brafton_debug_mode', 1);
                header("LOCATION:$this->url?b_error=vital");
                return;
            } else if($errorLevel == 1){
                variable_set('brafton_debug_mode', 1);
                header("LOCATION:$this->url?b_error=vital");
            }else{
                return;
            }
        }
        else{
            return;
        }
        //exit();
        return;
    }

    //function for checking if fatal error has occured and trigger the error flow
    public function check_for_fatal(){
        $error = error_get_last();
        if ( $error["type"] == E_ERROR )
            $this->log_error( $error["type"], $error["message"], $error["file"], $error["line"] );
    }

}
?>
