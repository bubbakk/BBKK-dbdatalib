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
 * This file contains BBKK_Logger class implementation and is part of
 * BBKK-dbdatalib project... but can be used independently ;)
 * The project can be followed and downloaded at
 * https://github.com/bubbakk/BBKK-dbdatalib
 */



/*
 * Class: BBKK_Logger
 *   This class implements simple logging setup and methods.
 *
 * Details:
 *   Actually the class only logs messages into a file.
 *
 *   To set successfully the log filename, this class needs that a base-path is
 *   set.
 *   The <BBKK_Logger.__construct> method sets a default value to application
 *   base path (see <BBKK_Logger.get_application_base_path>. It is NOT POSSIBLE
 *   to set this value at will for security reasons.
 *   Generally speaking, the application directory is frequently a good
 *   candidate for logging purposes.
 *   If the log file located in the application directory is not writeable for
 *   any reason, the user home directory will be used (option valid only for
 *   CLI execution).
 *
 *   In order to use this class, *call the constructor* or *set the (magic)
 *   <BBKK_Logger.$log_filename>* attribute . Please *remember* that if this
 *   class is inherited, the constructor is not automatically called (how
 *   much strange is PHP!).
 *
 * TODO:
 *   - add an extra parameter to configure the class behaviour on no writeable
 *     log file (return a FALSE or trigger an error)
 *
 * NOTES:
 *   it is suggested to turn following methods into functions:
 *     - <BBKK_Logger.get_application_base_path>
 *     - <BBKK_Logger.get_application_base_directory>
 *     - <BBKK_Logger.get_user_home_directory>
 */
class BBKK_Logger
{
    /*
     * Attribute: $base_path
     */
    private $base_path = '';

    /*
     * Attribute: $log_filename
     */
    private $log_filename = '';

    /*
     * Attribute: $abspath_log_filename
     */
    private $abspath_log_filename = '';

    /*
     * Constant: DEFAULT_LOG_FILE_NAME
     */
    const DEFAULT_LOG_FILE_NAME = 'error.log';




    /*
     * Method: __construct
     *   Set and check the log file to its default value or to passed parameter
     *
     * Parameters:
     *   $_log_filename {string} - desired log file name. Pass empty string or
     *                             nothing to set the file name to default value
     *                             <BBKK_Logger.DEFAULT_LOG_FILE_NAME>
     *                             (default: '')
     *
     * See:
     *   <BBKK_Logger.set_log_filename>
     */
    public function __construct($_log_filename = '')
    {
        if ( $_log_filename === '' )
            $_log_filename = BBKK_Logger::DEFAULT_LOG_FILE_NAME;

        $this->set_log_filename($_log_filename);
    }

    /*
     * Method: __set
     *   magic setter method (not a real method)
     *
     * Details:
     *   parameters that can be set are:
     *     - *log_filename*: the log file name (is it enough clear?)
     *
     * Parameters:
     *   $attr_name {string} - one of class' known attribute name
     *   $value {mixed}      - value to be set
     */
    public function __set($attr_name, $value)
    {
        switch ( $attr_name )
        {
            case 'log_filename':
                $this->set_log_filename($value);
                break;
            default:
                trigger_error();
                break;
        }
    }


    /*
     * Method: set_log_filename
     *   set the log file name and its absolute-path "version".
     *
     * Details:
     *   before setting the new full-path filename for the log file, a
     *   sanitization to passed file name is performed to ensure correct
     *   assignement (is always risky to let users/programmers set file names!)
     *
     *   The base path (used to build <BBKK_Logger.$abspath_log_filename) is
     *   alse initialized if still not set.
     *
     * Parameters:
     *   $_log_filename {string} - the name to be set as log file (default = '')
     *
     * NOTE:
     *   if a good log file is set, only the file name can be changed, not
     *   the base path
     *
     * Returns:
     *   TRUE if everything's fine, FALSE if passed file name is not a suitable
     *   name for a file (see <BBKK_Logger.filename_check> sanitization method
     *   for further details) or if can't be set a writeable log file
     */
    private function set_log_filename($_log_filename = '')
    {
        // check parameter passed
        if ( !$this->filename_check($_log_filename) )
            return false;

        $this->log_filename = $_log_filename;

        $logfile_writable = false;  // used in the next do-while
        if ( $this->base_path === '' ) $base_path_set = false;
        else                           $base_path_set = true;
        // used to launch base path functions
        $base_paths_available = ['application', 'userhome'];
        $base_paths_guesses   = count($base_paths_available);
        $path_guess_no        = 0;

        do
        {
            // set base path if not set
            if ( !$base_path_set )
            {
                switch ($base_paths[$i])
                {
                    case 'application':
                        $this->base_path  = $this->get_application_base_directory();
                        $this->base_path .= '/';  // append trailing '/'
                        break;
                    case 'userhome':
                        $this->base_path = $this->get_user_home_directory();
                        $this->base_path .= '/';  // append trailing '/'
                        break;
                }
            }

            // set filename attribute with absolute path
            $this->abspath_log_filename  = $this->base_path;
            $this->abspath_log_filename .= $this->log_filename;

            // check if log file set is writeable
            $logfile_writable = is_writable($this->abspath_log_filename);
        }
        while ( !$logfile_writable && $path_guess_no < $base_paths_guesses )

        // if all path-file couples are tested and no one is writeable...
        if ( !$logfile_writable ) {
            // ..reset attributes
            $this->abspath_log_filename = '';
            $this->base_path            = '';
        }

        return $logfile_writable;
    }

    /*
     * Method: filename_check
     *   check if passed parameter is a good string for a file name
     *
     * Details:
     *   parameter checks are made againts being a string, trailing
     *   white-spaces ("\t\n\r\0\x0B") and sanitization from special characters
     *   like tilde, colon, dot-dot-slash and so on (this is the
     *   regex: ([^\w\s\d\-_~,;:\[\]\(\].]|[\.]{2,})) ).
     *
     * Parameters:
     *   $filename {string} - string that will be checked as a valid file name
     *                        (default: '')
     *
     * Returns:
     *   {bool} TRUE if the parameter passes each single check, FALSE if not
     */
    private function filename_check($filename = '')
    {
        $whitespaces          = " \t\n\r\0\x0B";
        $filename_sanit_regex = "([^\w\s\d\-_~,;:\[\]\(\].]|[\.]{2,})";

        if ( !is_string($ilename) ) return false;

        // trim and sanitize
        $filename_trimmed   = trim($filename, $whitespaces);
        $filename_sanitized = preg_replace($filename_sanit_regex,
                                           '',
                                           $filename_trimmed);
        if ( $filename_sanitized !== $filename ) return false;

        return true;
    }

    /*
     * Method: get_application_base_path
     */
    private function get_application_base_directory()
    {
        if ( PHP_SAPI == 'cli' ) $retval = $_SERVER["PWD"] );
        else                     $retval = $_SERVER["DOCUMENT_ROOT"];

        return $retval;
    }

    /*
     * Method: set_user_home_directory
     *   get user's home directory
     *
     * Details:
     *   This method return user's home only if the PHP application/script is
     *   launched from CLI
     *
     * Returns:
     *   {string|bool} user's home directory or FALSE if the script is not a CLI
     *   application (eg: run in web server environment)
     */
    private function get_user_home_directory()
    {
        if ( PHP_SAPI == 'cli' ) return $_SERVER["HOME"];
        else                     return false;
    }

}
