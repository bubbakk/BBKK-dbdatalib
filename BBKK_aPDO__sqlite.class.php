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
 * This file contains BBKK_aPDO class implementation for SQLite database
 * manager, and is part of BBKK-dbdatalib project.
 *
 * The project can be followed at <https://github.com/bubbakk/BBKK-dbdatalib>
 *
 * Code is documented using <NaturalDocs at http://www.naturaldocs.org> format.
 */



/*
 * Class: BBKK_aPDO__sqlite
 *   implements specific methods for SQLite database connection handling
 */
class BBKK_aPDO__sqlite extends BBKK_aPDO
{
    /*
     * Constants: error/warning messages codes
     *
     * SQLITE_ONLY_ABSPATH_DBFILE - database file name must have absolute path
     */
    const SQLITE_ONLY_ABSPATH_DBFILE = 101;
    const SQLITE_CANT_CREATE_DB      = 102;
    const SQLITE_CONN_OPEN_ERROR     = 200; // 200+ are about connection

    /*
     * Property: const_messages
     *   *[private]* {array} messages corresponding to error codes
     */
    private $const_messages =
        array(// DBMS not supported
            BBKK_aPDO__sqlite::SQLITE_ONLY_ABSPATH_DBFILE =>
                'file path for SQLite database file must be absolute',
            BBKK_aPDO__sqlite::SQLITE_CANT_CREATE_DB =>
                'can\'t create SQLite database',
            BBKK_aPDO__sqlite::SQLITE_CONN_OPEN_ERROR     =>
                'error opening SQLite database connection'
        );





    /*
     * Method: __construct
     *   set default values
     *
     * Details:
     *   defaults are:
     *     - type: TCP connection <BBKK_aPDO.CONN_VIA_FILE>
     */
    public function __construct()
    {
        $this->type = BBKK_aPDO::CONN_VIA_FILE;

        // explicit parent constructor call
        parent::__construct();
    }



    /*
     * Method: open_connection
     *   *[public]* open SQLite database connection
     *
     * Description:
     *   SQLite is a little different from other DBMS, because simply it is not
     *   a DBMS. Connect to such a database means to open a data file: there
     *   is no server to connect to.
     *
     * Returns:
     *   {bool} TRUE if connection is opened, FALSE otherwise
     */
    public function open_connection()
    {
        // do checks
        if (true) {
            // connection already open
            if ( parent::connection_is_open() ) return false;

            // filename must be a string
            if ( !is_string($this->db_name) ) {
                parent::trigger_base_err(BBKK_BaseClass::ERR__ATTR_NOT_VLD_VL);
            }

            // filename path must be absolute
            if ( substr($this->db_name, 0, 1) !== '/' ) {
                $msg_id = BBKK_aPDO__sqlite::SQLITE_ONLY_ABSPATH_DBFILE;
                $msg    = $this->const_messages[$msg_id];
                parent::trigger_err($msg, E_USER_ERROR);
                return false;
            }
        }



        // try connection
        $dsn =  'sqlite:' . $this->db_name;
        try {
            $this->pdo = new PDO($dsn);
        }
        catch (PDOException $e) {
            $this->last_exception = $e;
            $msg_id = BBKK_aPDO__sqlite::SQLITE_GEN_OPEN_DRIVER_MSG;
            $msg = $this->const_messages[$msg_id] . ": " . $e->getMessage();
            $this->last_error_message = $msg;
            return false;
        }

        return true;
    }


    /*
     * Method: create_database
     *   *[public]* create SQLite database
     *
     * Description:
     *   when opening SQLite database connection, if the database file does
     *   not exist, is created... this is why this method calls open_connection
     *   method and, immediately after, a <BBKK_aPDO.close_connection>.
     *
     * Returns:
     *   {bool} TRUE if connection is created, FALSE otherwise
     */
    public function create_database()
    {
        // call open_connection
        if ( $this->open_connection() === false ) {
            $msg_id = BBKK_aPDO__sqlite::SQLITE_CANT_CREATE_DB;
            $exc_error = $this->last_exception->getMessage();
            $msg = $this->const_messages[$msg_id] . ": " . $exc_error;
            $this->last_error_message = $msg;
            return false;
        };

        $this->close_connection();

        return true;
    }

}
