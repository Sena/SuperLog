<?php
class SuperLog {
    // PHP5.2.3
    protected static $log = array ();
    protected static $path = NULL;
    protected static $file = NULL;
    protected static $size = 5242880; //5 megabytes 
    protected static $actionLimit = 'new';
    /*
        7 - emerg - The system is unusable.
        6 - alert - Action must be taken immediately.
        5 - crit - critical conditions.
        4 - error - Error conditions.
        3 - warn - Terms of alert.
        2 - notice - normal but significant condition.
        1 - info - Informational messages.
        0 - debug - debug level messages.
    */
    protected static $level = array();
    
    function __construct($path = NULL, $file = NULL, array $level = array()) {
        self::$path = $path;
        self::$file = $file;
        self::$level = $level;
    }
    public static function message($level, $message) {
        self::prepareLog ();
        
        $level = strtolower ( $level );
        if (in_array ( $level, self::$level ) === TRUE) {
            $backTrace = debug_backtrace ();
            $backTrace = current ( $backTrace );
            
            self::write(date ( 'd-m-Y H:i:s' ) . ' [' . $backTrace ['file'] . '] [' . $backTrace ['line'] . '] [' . $level . '] : ' . $message . PHP_EOL);
        }
    }
    private static function write($message) {
        $fileName = self::$path . DIRECTORY_SEPARATOR . self::$file;
        $handle = fopen ( $fileName, 'a' );
        fwrite ( $handle,  $message);
        fclose ( $handle );
    }
    public static function setBreak() {
        self::prepareLog ();
        self::write(PHP_EOL . str_repeat ( '_', 100 ) . PHP_EOL . PHP_EOL . PHP_EOL);
    }
    public static function setName($file) {
        if (self::$file !== NULL) {
            self::message ( 'debug', 'File name changed from: ' . self::$file . ' to: ' . $file );
        }
        self::$file = $file;
    }
    public static function setLevel(array $level) {
        if (count ( self::$level ) > 0) {
            self::message ( 'debug', 'Level changed from: ' . var_export ( self::$level, TRUE ) . ' to: ' . var_export ( $level, TRUE ) );
        }
        self::$level = $level;
    }
    public static function setPath($path) {
        if (self::$path !== NULL) {
            self::message ( 'debug', 'Path changed from: ' . self::$path . ' to: ' . $path );
        }
        self::$path = $path;
    }
    public static function setActionLimit($actionLimit) {
        if (self::$actionLimit !== NULL) {
            self::message ( 'debug', 'Action changed from: ' . self::$actionLimit . ' to: ' . $actionLimit );
        }
        self::$actionLimit = $actionLimit;
    }
    public static function setSize($size, $unit = 'b') {
        if (self::$size > 0) {
            $unit = strtolower($unit);
            switch ($unit) {
                case 'k':
                case 'kb':
                    $size *= 1024;
                break;

                case 'm':
                case 'mb':
                    $size *= 1048576;
                break;

                case 'g':
                case 'gb':
                    $size *= 1073741824;
                break;

                case 't':
                case 'tb':
                    $size *= 1099511627776;
                break;
            }
            self::message ( 'debug', 'Size changed from: ' . self::$size . ' to: ' . $size );
            self::$size = $size;
        }
    }
    protected static function prepareLog() {
        set_time_limit(0);
        if (self::$path == NULL) {
            self::$path = dirname ( dirname ( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'log';
        }
        if (self::$file == NULL) {
            self::$file = 'log.log';
        }
        if (count ( self::$level ) === 0) {
            
            self::$level [7] = 'emerg';
            self::$level [6] = 'alert';
            self::$level [5] = 'crit';
            self::$level [4] = 'error';
            self::$level [3] = 'warn';
            self::$level [2] = 'notice';
            self::$level [1] = 'info';
            self::$level [0] = 'debug';
        }
        if (is_dir ( self::$path ) === FALSE) {
            mkdir ( self::$path, 0777 );
        }
        $fileName = self::$path . DIRECTORY_SEPARATOR . self::$file;
        
        if (self::$size > 0 && @filesize ( $fileName ) > self::$size) {
            chmod($fileName,0777);

            switch (self::$actionLimit) {
                case 'split' :
                    list($microSec, $timeStamp) = explode(' ', microtime());
                    rename($fileName, $fileName . date('F_jS_Y_Hi', $timeStamp) . (date('s', $timeStamp) + $microSec));
                    break;

                case 'new' :
                default :
                    @unlink ( $fileName );
                    break;
            }
        }
    }
}