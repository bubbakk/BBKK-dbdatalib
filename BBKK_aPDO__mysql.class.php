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
 * This file contains BBKK_aPDO class implementation for MySQL DBMS and is part
 * of BBKK-dbdatalib project.
 *
 * The project can be followed at <https://github.com/bubbakk/BBKK-dbdatalib>
 *
 * Code is documented using <NaturalDocs at http://www.naturaldocs.org> format.
 */



/*
 * Class: BBKK_aPDO__mysql
 *   implements specific methods for MySQL DBMS connection handling
 */
class BBKK_aPDO__mysql extends BBKK_aPDO
{

    /*
     * Constants: error/warning messages codes
     *
     */
    const MYSQL_CANT_CREATE_DB      = 102;
    const MYSQL_CONN_OPEN_ERR       = 200; // 200+ are about connection
    const MYSQL_CONN_TCP_HOST_ERR   = 201;
    const MYSQL_CONN_SOCKF_NOT_SET  = 202;

    /*
     * Constant: default MySQL server TCP port
     *
     */
    const DEFAULT_TCP_PORT = 3306;



    /*
     * Property: const_messages
     *   *[private]* {array} messages corresponding to error codes
     */
    private $const_messages =
        array(
            BBKK_aPDO__mysql::MYSQL_CANT_CREATE_DB          =>
                'can\'t create MySQL database',
            BBKK_aPDO__mysql::MYSQL_CONN_OPEN_ERROR         =>
                'error opening MySQL database connection',
            BBKK_aPDO__mysql::MYSQL_CONN_TCP_HOST_ERR       =>
                'host not set',
            BBKK_aPDO__mysql::MYSQL_CONN_SOCKF_NOT_SET      =>
                'socket file not set: review mysql.default_socket in php.ini'
        );





    /*
     * Method: __construct
     *   set default values
     *
     * Details:
     *   defaults are:
     *     - tcp_port: 3306 (constant <BBKK_aPDO__mysql.DEFAULT_TCP_PORT>)
     *     - type: TCP connection <BBKK_aPDO.CONN_VIA_TCP>
     *     - charset: 'UTF-8'
     */
    public function __construct()
    {
        // set dependencies
        $func = "bk2l_array__replace_values";
        $type = BBKK_BaseClass::DEP_FUNCTIONS;
        $this->dependencies_add($func, $type);

        // set defaults
        $this->tcp_port = BBKK_aPDO__mysql::DEFAULT_TCP_PORT;
        $this->type     = BBKK_aPDO::CONN_VIA_TCP;
        $this->charset  = 'UTF8';

        // explicit parent constructor call
        parent::__construct();
    }


    /*
     * Method: open_connection
     *   *[public]* open MySQL database connection
     *
     * Details:
     *    parameters are not explicit (hidden) because PHP methods can't be
     *    explicitly overloaded
     *
     *    Socket connetion uses socket name set in php.ini. <More informations
     *    at http://it2.php.net/manual/it/ref.pdo-mysql.php>
     *
     * Parameters:
     *   $override_user {string} - override class property username
     *   $override_pass {string} - override class property password
     *
     * Returns:
     *   {bool} TRUE if connection is opened, FALSE otherwise
     */
    public function open_connection()
    {
        // reading arguments
        $vars_defs = array('override_user' => null, 'override_pass' => null);
        $keys_values = bk2l_array__replace_values($vars_defs, func_get_args());
        extract($keys_values);



        // checks
        if ( true ) {
            if ( $this->hostname === '' ) {
                $err_code = BBKK_aPDO__mysql::MYSQL_CONN_TCP_HOST_ERR;
                $err_msg  = $this->const_messages[$err_code];
                $this->trigger_err($err_msg);
            }
        }



        // open connection
        $dsn = 'mysql:';
        // TCP
        if ( $this->type === BBKK_aPDO::CONN_VIA_TCP ) {
            $dsn .= 'host=' . $this->hostname . ';port=' . $this->tcp_port;
        }
        // socket
        else {
            $socket_file = ini_get('pdo_mysql.default_socket');
            if ( $socket_file !== '' ) {
                $dsn .= 'unix_socket=' . $socket_file;
            }
            else {
                $err_code = BBKK_aPDO__mysql::MYSQL_CONN_SOCKF_NOT_SET;
                $err_msg  = $this->const_messages[$err_code];
                $this->trigger_err($err_msg);
            }
        }
        // user and password
        $user = $this->username;
        $pass = $this->password;
        // user/pass override ?
        if ( is_string($override_user) && is_string($override_pass) ) {
            $user = $override_user;
            $pass = $override_pass;
        }
        try {
            // no user
            if ( $user === '' ) {
                $this->pdo = new PDO($dsn);
            }
            else {
                // user but no password
                if ( $pass === '' ) {
                    $this->pdo = new PDO($dsn, $user);
                }
                // user and password
                else {
                    $this->pdo = new PDO($dsn, $user, $pass);
                }
            }

        }
        catch (PDOException $e) {
            $this->last_exception = $e;
            $msg_id = BBKK_aPDO__mysql::MYSQL_CONN_OPEN_ERROR;
            $msg = $this->const_messages[$msg_id] . ": " . $e->getMessage();
            $this->last_error_message = $msg;
            return false;
        }

        return true;
    }


    /*
     * Method: create_database
     *   *[public]* create a new database
     *
     * Details:
     *    method parameters are not explicit (hidden) because PHP methods
     *    can't be explicitly overloaded
     *
     * Parameters:
     *   $if_not_exists - add clause "IF NOT EXISTS" to CREATE DATABASE SQL
     *                    command (default: FALSE)
     *   $adm_user      - username with "create database" privileges
     *                    (default: '')
     *   $adm_pass      - $adm_user's password (default: '')
     *
     * Returns:
     *   {bool} TRUE if database is successfully created or already exists if
     *   the first parameter passed is TRUE, FALSE otherwise.
     *
     * TODO:
     *   add grant privileges with using something like this:
     *     CREATE USER '$user'@'localhost' IDENTIFIED BY '$pass';
     *     GRANT ALL ON `$db`.* TO '$user'@'localhost';
     *     FLUSH PRIVILEGES;")
     */
    public function create_database()
    {
        // reading arguments
        $vars_defs = array('if_not_exists'  => false,
                           'adm_user'       => '',
                           'adm_pass'       => ''   );
        $keys_values = bk2l_array__replace_values($vars_defs, func_get_args());
        extract($keys_values);



        // checks
        if (true) {
            if ( $if_not_exists !== true && $if_not_exists !== false ) {
                $this->trigger_base_err(BBKK_BaseClass::ERR__PARM_NOT_VLD_TYP);
            }
            if ( !is_string($adm_user) ) {
                $this->trigger_base_err(BBKK_BaseClass::ERR__PARM_NOT_VLD_TYP);
            }
            if ( !is_string($adm_pass) ) {
                $this->trigger_base_err(BBKK_BaseClass::ERR__PARM_NOT_VLD_TYP);
            }
        }



        // open connection
        if ( !$this->open_connection($adm_user, $adm_pass) ) {
            return false;
        }



        // create database
        $if_not_exists_clause = '';
        if ( $if_not_exists ) $if_not_exists_clause = ' IF NOT EXISTS';
        $query = 'CREATE DATABASE ' . $if_not_exists_clause . ' ' .
                 '`' . $this->db_name . '`'                       .
                 'CHARACTER SET ' . strtolower($this->charset);
        try {
            $res = $this->pdo->exec($query);
        }
        catch (PDOException $e) {
            $this->last_exception = $e;
            $msg_id = BBKK_aPDO__mysql::MYSQL_CONN_OPEN_ERROR;
            $msg = $this->const_messages[$msg_id] . ": " . $e->getMessage();
            $this->last_error_message = $msg;
            return false;
        }

        if ( !$res )
        {
            $err_data = $this->pdo->errorInfo();
            $this->last_exception = $err_data;
            $msg_id = BBKK_aPDO__mysql::MYSQL_CANT_CREATE_DB;
            $msg = $this->const_messages[$msg_id] . ": " . $err_data[2];
            $this->last_error_message = $msg;
            return false;
        }

        return true;
    }
}
