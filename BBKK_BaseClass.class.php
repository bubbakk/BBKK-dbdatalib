<?php
/*
    Copyright (c) 2008-2014 - Andrea Ferroni [bubbakk@gmail.com]

    This file is part of BBKK-dbdatalib.

    BBKK-dbdatalib is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program. If not, see <http://www.gnu.org/licenses/>.
*/



/*
 * This file contains BBKK_BaseClass class implementation and is part of
 * BBKK-dbdatalib project.
 * The project can be followed and downloaded at
 * https://github.com/bubbakk/BBKK-dbdatalib
 */



/*
 * Class: BBKK_BaseClass
 *   This class implements common methods and properties such as:
 *    - error management
 *    - logging (via external class)
 *    - dependencies checking
 */
class BBKK_BaseClass
{

    /*
     * Method: __construct
     *   initialize log properties, check dependecies
     */
    public function __construct()
    {
        //$this->error_log_file  = $this->application_base_path . '/';
        //$this->error_log_file .= BBKK_BaseClass::DEFAULT_LOG_FILE_NAME;

        $this->dependencies_check();
    }





//
// DEPENDENCIES SUBSYSTEM: properties, constants and methods
//
    private $dependencies = array();

    /*
     * Constants: valori dei possibili controlli delle dipendenze.
     *   Vedi <check_dependencies>
     *
     * DEP_FUNCTIONS - dipendenze di funzioni
     * DEP_CLASSES   - dipendenze di classi
     */
    const DEP_FUNCTIONS  = 'functions';
    const DEP_CLASSES    = 'classes';


    /*
     * Method: dependencies_add
     *   add single or multiple dependencies by type to dependencies list
     *
     * Parameters:
     *   $deps {string|array} - single or multiple dependencies
     *   $type {sring}        - one of BBKK_BaseClass::DEP_* constants
     *
     * See:
     *   <BBKK_BaseClass.DEP_FUNCTIONS>, <BBKK_BaseClass.DEP_CLASSES>,
     *   <BBKK_BaseClass.dependencies>
     *
     */
    protected function dependencies_add($deps = '', $type = '')
    {
        $t_func = BBKK_BaseClass::DEP_FUNCTIONS;
        $t_clas = BBKK_BaseClass::DEP_CLASSES;

        if ( $type !== $t_func && $type !== $t_clas ) {
            $this->trigger_base_err(BBKK_BaseClass::ERR__PARM_NOT_VLD_TYP);
        }

        if ( !is_string($deps) && !is_array($deps) ) {
            $this->trigger_base_err(BBKK_BaseClass::ERR__PARM_NOT_VLD_VL);
        }

        // append the dependency
        if ( is_string($deps) ) {
            $this->dependencies[$type][] = $deps;
        }
        // or
        else
        // append the dependencies
        if ( is_array($deps) ) {
            $array_origin = &$this->dependencies[$type];
            $this->dependencies[$type] = array_merge($array_origin, $deps);
        }
    }


    /*
     * Method: check_dependencies
     *   *[protected]* Effettua una verifica delle dipendenze: funzioni e classi
     *
     * Parameters:
     *   $dependencies_list {array} - è un array che deve avere
     */
    protected function dependencies_check()
    {
        if ( isset($this->dependencies[BBKK_BaseClass::DEP_FUNCTIONS]) ) {
            $checks = $this->dependencies[BBKK_BaseClass::DEP_FUNCTIONS];
            foreach ($checks as $function) {
                if ( !function_exists($function) ) {
                    $err_code = BBKK_BaseClass::ERR__DEP_FUNC_NOT_FND;
                    $err_text = $this->base_errors_list[$err_code] . ': '.
                                $function;
                    $this->trigger_base_err($err_text);
                }
            }
        }

        if ( isset($this->dependencies[BBKK_BaseClass::DEP_CLASSES]) ) {
            $checks = $this->dependencies[BBKK_BaseClass::DEP_CLASSES];
            foreach ($checks as $class) {
                if ( !class_exists($class) ) {
                    $err_code = BBKK_BaseClass::ERR__DEP_CLASS_NOT_FND;
                    $err_text = $this->base_errors_list[$err_code] . ': '.
                                $function;
                    $this->trigger_base_err($err_text);
                }
            }
        }

        return true;
    }
//
// END DEPENDENCIES SUBSYSTEM
//










//
// ERROR MANAGEMET SUBSYSTEM: properties, constants and methods
//
    /*
     * Property: $last_error_message
     *   *[protected]* Contains last class user error message
     */
    protected $last_error_message = '';


    /*
     * Constants: error codes
     *
     * ERR__ATTR_NOT_FND        - attribute not found
     * ERR__ATTR_NOT_VLD_VL     - attribute not valid value
     */
    const ERR__ATTR_NOT_FND         = 0;
    const ERR__ATTR_NOT_VLD_VL      = 1;
    const ERR__PARM_NOT_VLD_VL      = 10;
    const ERR__PARM_NOT_VLD_TYP     = 11;
    const ERR__DEP_FUNC_NOT_FND     = 20;
    const ERR__DEP_CLASS_NOT_FND    = 21;


    /*
     * Property: $base_errors_list
     *   {array} error codes and messages
     *
     * Details:
     *   errors slots:
     *   - 0 - 100: reserved for class methods and attributes errors (class
     *              misuse)
     *   - others:  reserved for runtime errors (genereted in using class
     *              feature)
     */
    protected $base_errors_list =
              // Attribute not found
        array(  BBKK_BaseClass::ERR__ATTR_NOT_FND       =>
                    'attribute not found'               ,
                BBKK_BaseClass::ERR__ATTR_NOT_VLD_VL    =>
                    'attribute has not a valid value'   ,
                BBKK_BaseClass::ERR__PARM_NOT_VLD_VL    =>
                    'parameter value is not valid'      ,
                BBKK_BaseClass::ERR__DEP_FUNC_NOT_FND   =>
                    'parameter type is not valid'       ,
                BBKK_BaseClass::ERR__DEP_FUNC_NOT_FND   =>
                    'dependency function not found'     ,
                BBKK_BaseClass::ERR__DEP_CLASS_NOT_FND  =>
                    'dependency class not found'
        );


    /*
     * Constants: error types
     *
     * ERR_PROGRAMMING  - invalid software usage; stops script execution
     * ERR_USER_WARNING - generate soft warning
     * ERR_USER_ERROR   - generate error; stops script execution
     */
    const ERR_PROGRAMMING      = 0;
    const ERR_USER_WARNING     = 1;
    const ERR_USER_ERROR       = 2;





    public function get_last_error_message()
    {
        return $this->last_error_message;
    }


    /*
     * Method: trigger_base_err
     *   *[protected]* trigger coded E_USER_ERROR
     *
     * Parameters:
     *   $err_code {int} -
     *
     * See:
     *   <BBKK_BaseClass.$error_list>
     */
    protected function trigger_base_err($err_code)
    {
        // set message and check
        if ( is_integer($err_code) ) {
            $err_msg = $this->base_errors_list[$err_code];
        }
        else
            if ( is_string($err_code) ) {
                $err_msg = $err_code;
            }
            else {
                $err_code = BBKK_BaseClass::ERR__PARM_NOT_VLD_TYP;
                $err_msg = $this->base_errors_list[$err_code];
                $this->trigger_err($err_msg);
            }

        $this->trigger_err($err_msg);
    }


    /*
     * Method: trigger_err
     *   *[protected]* trigger an error
     *
     * Parameters:
     *   $msg {string} -
     *   $error {int}  -
     *
     */
    protected function trigger_err($msg = '', $error = E_USER_ERROR) {
        if ( TRIGGER_ERRORS ) {
            trigger_error($msg, $error);
        }
        else {
            if ( TARGET_PLATFORM === 'browser')
                print "<br><strong>triggering error</strong>: {$msg}\n<br>";
            else
            if ( TARGET_PLATFORM === 'console')
                print "\n**triggering error**: {$msg}\n";
        }
    }
//
// END ERROR MANAGEMET SUBSYSTEM
//






//
// LOG SUBSYSTEM
//
    /*
     * Const: DEFAULT_LOG_FILE_NAME
     *   default value for log file name
     */
    const DEFAULT_LOG_FILE_NAME = "application.log";


    /*
     * Method: log_message
     *   write message to log file
     *
     */
    protected function log_message($message)
    {
        error_log($message, 3, $this->error_log_file);
    }

    /*
     * Method: __set
     *   magic setter method
     */
    public function __set($attr_name, $value)
    {
        $this->trigger_base_err(BBKK_BaseClass::ERR__ATTR_NOT_FND);
    }
//
// END LOG SUBSYSTEM
//
}
?>
