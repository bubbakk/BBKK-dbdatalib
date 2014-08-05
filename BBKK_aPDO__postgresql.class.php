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
 * This file contains BBKK_aPDO class implementation for PostgreSQL DBMS and is
 * part of BBKK-dbdatalib project.
 *
 * The project can be followed at <https://github.com/bubbakk/BBKK-dbdatalib>
 *
 * Code is documented using <NaturalDocs at http://www.naturaldocs.org> format.
 */



/*
 * Class: BBKK_aPDO__postgresql
 *   implements specific methods for PostgreSQL DBMS connection handling
 */
class BBKK_aPDO__postgresql extends BBKK_aPDO
{
    /*
     * Constants: error/warning messages codes
     *
     */
    const PGSQL_DB_NM_NSET        = 101; // 100+ are about database
    const PGSQL_CONN_OPEN_ERR     = 200; // 200+ are about connection
    const PGSQL_CONN_TCP_HOST_ERR = 201;


    /*
     * Constant: default PostgreSQL server TCP port
     *
     */
    const DEFAULT_TCP_PORT = 5432;

    /*
     * Property: $schema
     *    schema name in the database
     */
    protected $schema = '';


    /*
     * Property: const_messages
     *   *[private]* {array} messages corresponding to error codes
     */
    private $const_messages =
        array(
            BBKK_aPDO__postgresql::PGSQL_DB_NM_NSET          =>
                'database name is not correctly set or empty',
            BBKK_aPDO__postgresql::PGSQL_CONN_OPEN_ERR       =>
                'error opening PostgreSQL database connection',
            BBKK_aPDO__postgresql::PGSQL_CONN_TCP_HOST_ERR   =>
                'host not set'
        );





    /*
     * Method: __construct
     *   set default values
     *
     * Details:
     *   defaults are:
     *     - tcp_port: 5432 (constant <BBKK_aPDO__postgresql.DEFAULT_TCP_PORT>)
     *     - type: TCP connection <BBKK_aPDO.CONN_VIA_TCP>
     */
    public function __construct()
    {
        // set dependencies
        $func = "bk2l_array__replace_values";
        $type = BBKK_BaseClass::DEP_FUNCTIONS;
        $this->dependencies_add($func, $type);

        // set defaults
        $this->tcp_port = BBKK_aPDO__postgresql::DEFAULT_TCP_PORT;
        $this->type     = BBKK_aPDO::CONN_VIA_TCP;

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
                $err_code = BBKK_aPDO__postgresql::PGSQL_CONN_TCP_HOST_ERR;
                $err_msg  = $this->const_messages[$err_code];
                $this->trigger_err($err_msg);
            }
        }



        // connection parameters
        $dsn     = 'pgsql:';
        $options = array();
        // database
        if ( $this->db_name !== '' ) {
            $options[] = 'dbname=' . $this->db_name;
        }
        // TCP (socket does not add any extra parameter do DSN)
        if ( $this->type === BBKK_aPDO::CONN_VIA_TCP ) {
            $options[] = 'host=' . $this->hostname;
            $options[] = 'port=' . $this->tcp_port;
        }
        // user and password
        $user = $this->username;
        $pass = $this->password;
        // user/pass override ?
        if ( is_string($override_user) && is_string($override_pass) ) {
            $user = $override_user;
            $pass = $override_pass;
        }
        if ( $user !== '' ) {
            $options[] = 'user='     . $user;
            if ( $pass !== '' )
                $options[] = 'password=' . $pass;
        }



        // connect
        $dsn .= implode($options, ";");
        try {
            $this->pdo = new PDO($dsn);
        }
        catch (PDOException $e) {
            $this->last_exception = $e;
            $msg_id = BBKK_aPDO__postgresql::PGSQL_CONN_OPEN_ERR;
            $msg = $this->const_messages[$msg_id] . ": " . $e->getMessage();
            $this->last_error_message = $msg;
            return false;
        }

        return true;
    }


    /*
     * Method: close_connection
     *   *[public]* close DBMS connection
     *
     * Details:
     *    ask to PostgreSQL server to detach connection (PHP's PDO driver does
     *    not ensure disconnection)
     *
     * Returns:
     *   {BBKK_aPOD ref} self reference
     */
    public function close_connection()
    {
        // force the connection termination server-side
        $this->pdo->query('SELECT pg_terminate_backend(pg_backend_pid());');

        return parent::close_connection();
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
     *   $if_not_exists - before create the database, check if it exists
     *   $adm_user      - username with "create database" privileges
     *                    (default: '')
     *   $adm_pass      - $adm_user's password (default: '')
     *
     * Returns:
     *   {bool} TRUE if database is successfully created or already exists if
     *   the first parameter passed is TRUE, FALSE otherwise.
     *
     * TODO:
     *   add owner clause
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



        // open connection but not to database
        $buffer_db_name = $this->db_name;
        $buffer_schema  = $this->schema;
        $this->db_name = '';
        $this->schema  = '';
        if ( !$this->open_connection($adm_user, $adm_pass) ) {
            return false;
        }



        // create database
        $db_exists = $this->database_exists();

        die("continua qui!");
        /*
        if ( $if_not_exists )
        try {
            $query = 'CREATE DATABASE ' . $if_not_exists_clause . ' ' .
                '`' . $this->db_name . '` '                      .
                'CHARACTER SET ' . strtolower($this->charset);

            $res = $this->pdo->exec($query);
            if ( !$res )
            {
                $err_data = $this->pdo->errorInfo();
                $this->last_exception = $err_data;
                $msg_id = BBKK_aPDO__mysql::MYSQL_CANT_CREATE_DB;
                $msg = $this->const_messages[$msg_id] . ": " . $err_data[2];
                $this->last_error_message = $msg;
                return false;
            }
        }
        catch (PDOException $e) {
            $this->last_exception = $e;
            $msg_id = BBKK_aPDO__mysql::MYSQL_CONN_OPEN_ERROR;
            $msg = $this->const_messages[$msg_id] . ": " . $e->getMessage();
            $this->last_error_message = $msg;
            return false;
        }
        */

        return true;
    }








    /*
     * Method:
     *   check if the database exists
     *
     * Details:
     *   this method queries pg_catalog.pg_database schema catalog
     *
     * Returns:
     *   {bool} TRUE if satabase exists, FALSE if not
     *
     * See:
     *   <BBKK_aPDO.$db_name>
     */
    private function database_exists()
    {
        if ( true ) {
            if ( $this->db_name === '' ) {
                $err_code = BBKK_aPDO__postgresql::PGSQL_DB_NM_NSET;
                $err_msg  = $this->const_messages[$err_code];
                $this->trigger_err($err_msg);
            }

            // error is set in the parent class
            if ( !$this->connection_is_open() ) {
                return false;
            }
        }

        $dbname_quoted = $this->pdo->quote($this->db_name);
        $query  = 'SELECT count(datname) ';
        $query .= '  FROM pg_catalog.pg_database ';
        $query .= ' WHERE datname = ' . $dbname_quoted;

        $stmt = $this->pdo->query($query);
        $col0_rows = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        return ( $col0_rows[0] === "0" && !isset($col0_rows[1]) );
    }


   /*
    * Method: __set
    *   (setter magic method) set some private attributes
    *
    * Details:
    *   attributes settable are: <BBKK_aPDO.$host>, <BBKK_aPDO.$port>,
    *   <BBKK_aPDO.$db_name>, <BBKK_aPDO.$username>, <BBKK_aPDO.$password>
    *
    * Parameters:
    *   $attr_name {string} - object attribute to set
    *   $value {mixed}      - value to assign to attribute
    */
    public function __set($attr_name, $value)
    {
        switch($attr_name)
        {
            case 'schema'  :
                $this->$attr_name = $value;
                break;
            default:
                parent::__set($attr_name, $value);
                break;
        }
    }
}
