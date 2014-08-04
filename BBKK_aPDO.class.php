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
 * This file contains BBKK_aPDO class implementation and is part of
 * BBKK-dbdatalib project.
 *
 * The project can be followed at <https://github.com/bubbakk/BBKK-dbdatalib>
 *
 * Code is documented using <NaturalDocs at http://www.naturaldocs.org> format.
 */



/*
 * Class: BBKK_aPDO
 *   Implements generic base and abstract methods for database connection
 *   handling.
 *
 * Details:
 *
 */
abstract class BBKK_aPDO extends BBKK_BaseClass
{
    /*
     * Constants: error/warning messages codes
     *
     * CONN_ALREADY_OPN     - connection to database is already open
     */
    const CONN_ALREADY_OPN             = 101;  // connections messages

    /*
     * Property: const_messages
     *   *[private]* {array} messages corresponding to error codes
     */
    private $const_messages =
        array(// DBMS not supported
            BBKK_aPDO::CONN_ALREADY_OPN => 'connection already open'
        );





   /*
    * Property: $pdo
    *   *[protected]* {PDO} PHP PDO object instance that handles database
    *   connection
    *
    * See also:
    *   <BBKK_aPDO.open_connection>, <BBKK_aPDO.get_pdo>
    */
    protected $pdo = null;

    /*
     * Property: $db_name
     *   *[protected]* {string} database name. *For SQLite* set this property to
     *   /absolutepath/to/sqlitedb.sqlite
     */
    protected $db_name = '';

    /*
     * Property: $username
     *   *[protected]* {string} database user name
     */
    protected $username = '';

    /*
     * Property: $password
     *   *[protected]* {string} user's password
     */
    protected $password = '';

    /*
     * Property: $hostname
     *   *[protected]* {string} DBMS host (generally a hostname or IP address)
     */
    protected $hostname = '';

    /*
     * Property: $tcp_port
     *   *[protected]* {int} valid TCP connection port number
     */
    protected $tcp_port = 0;

    /*
     * Property: $charset
     *   *[protected]* {string} database charset to use
     *
     * Details:
     *   each superclass should implement proper check routine
     *
     * See also:
     *   <PHP supported charsets at
     *    http://php.net/manual/it/mbstring.supported-encodings.php>
     */
    protected $charset = '';




    /*
     * Property: $last_exception
     *   *[protected]* {Exception} last exception occurred
     */
    protected $last_exception = null;










    /*
     * Method: __construct
     */
    public function __construct()
    {
        // explicit parent constructor call
        parent::__construct();
    }




    /*
     * Method: open_connection
     *   *[abstract public]* open the connection to database/DBMS
     */
    abstract public function open_connection();

    /*
     * Method: create_database
     *   *[abstract public]* create new database
     *
     * Details:
     *   each DBMS will have its own parameters; please refer to specific
     *   superclass documentation
     */
    abstract public function create_database();




    /*
     * Method: close_connection
     *   *[public]* close the connection to DBMS
     *
     * Description:
     *   resetting PDO to null is generally enough. Anyway there are DBMS that
     *   should implement specific actions to ensure the connection is really
     *   closed
     *
     * Returns:
     *   {BBKK_aPOD ref} self reference
     */
    public function close_connection()
    {
        $this->pdo = null;

        return $this;
    }


   /*
    * Method: get_pdo
    *   *[public]* return the pointer to private PDO attribute
    *
    * Details:
    *   Yes, it works! You can return a pointer to a protected property
    *
    * Returns:
    *   {PDO ref} pointer to (private) <BBKK_aPDO.$pdo> attribute
    *
    * See also:
    *   <BBKK_aPDO.$pdo>
    */
    public function &get_pdo()
    {
        return $this->pdo;
    }


    /*
     * Method: dbms_supported
     *   *[public]* check if a certain DBMS is supported
     *
     * Details:
     *   filename checked is in this library format BBKK_aPDO__[DBMS].class.php
     *   where [DBMS] can be lower case DBMS name (like "sqlite", "mysql",
     *   "postgresql", ....)
     *
     * Returns:
     *   {bool} TRUE if PHP file class exists and is readable, FALSE otherwise
     */
    public static function is_dbms_supported($dbms_name, $aPDOlibrary_path) {

        if ( !is_string($dbms_name) || !is_string($aPDOlibrary_path) )
            return false;

        $file_name = "{$aPDOlibrary_path}BBKK_aPDO__{$dbms_name}.class.php";

        return is_file($file_name) && is_readable($file_name);
    }



    /*
     * Method: connection_is_open
     *   *[protected]* check if a connection to database is open
     *
     * Details:
     *   this method sets <BBKK_BaseClass.$last_error_message>
     *
     * Returns:
     *   {bool} TRUE if connection is open, FALSE otherwise
     *
     * See also:
     *   <BBKK_aPDO.$pdo>
     */
    protected function connection_is_open()
    {
        if ( $this->pdo !== null && is_a($this->pdo, 'PDO') ) {
            $msg = $this->const_messages[BBKK_aPDO::CONN_ALREADY_OPN];
            $this->last_error_message = $msg;
            return true;
        }

        return false;
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
            case 'hostname':
            case 'port'    :
            case 'db_name' :
            case 'username':
            case 'password':
            case 'charset' :
            case 'schema' :
                $this->$attr_name = $value;
                break;
            default:
                parent::__set($attr_name, $value);
                break;
        }
    }
}





/*
 * Class: BBKK_aPDO_OLD
 *   Implements methods for database connection handling.
 *
 * Details:
 *    This class abstracts PHP's PDO class offering a seamless multi-dbms and
 *    multi-type connection interface.
 *
 *    Supported DBMSs are: <MySQL at https://www.mysql.com/>,
 *    <PostrgreSQL at http://www.postgresql.org/> and
 *    <SQLite at http://www.sqlite.org>
 *
 *    Supported connection types are: <TCP at BBKK_aPDO.CONNECTION_TCP>,
 *    <socket at BBKK_aPDO.CONNECTION_SOCKET>,
 *    <file at BBKK_aPDO.CONNECTION_FILE>
 *
 *    Not each pair DBMS-connection_type is supported: some are not yet
 *    implemented, others simply are not allowed. For example, SQLite only
 *    supports file connection type.
 *
 *   - *MySQL*:      actually only implemented TCP connection and username and
 *                   password authentication method (no anonymous, no socket)
 *   - *PostgreSQL*: DBMS connection is available both via TCP and via socket.
 *                   Also non-password authentication is allowed: simply do not
 *                   set the property <BBKK_aPDO.$password> (or set il to empty
 *                   string)
 *   - *SQLite*:     the only available connection type is
 *                   <file at BBKK_aPDO.CONNECTION_FILE>.
 *
 * TODO:
 *   - implement socket connection for MySQL
 *   - implement anonymous connection for PostgreSQL
 */
class BBKK_aPDO_OLD extends BBKK_BaseClass
{

    /*
     * Property: $error_messages
     *   *[private]* {array} error codes and messages
     *
     * Description:
     *   error ranges are:
     *   - 101 - 120: strict DBMS parameters error (such as db_type or
     *     connection_type)
     *   - 121 - 131: connection layer network error (such as not valid domain
     *     or port, or SQLite file path not found)
     * See also:
     *   <BBKK_BaseClass.$base_errors_list>
     */
    private $errors_list =
        array(// DBMS not supported
              101 => 'connection already open'                               ,
              // connection not supported
              102 => 'Wrong connection type. Please use constats \
                      CONNECTION_*'                                          ,
              // connection type for PostreSQL not supported
              103 => 'The connection type requested for the selected \
                      DBMS is not supported. Connection types allowed for \
                      PostgreSQL are BBKK_aPDO.CONNECTION_SOCKET and \
                      BBKK_aPDO.CONNECTION_TCP'                              ,
              // connection type for MySQL not supported or not yet implemented
              104 => 'The connection type requested for the selected \
                      DBMS is not supported. The only connection type \
                      allowed for MySQL is BBKK_aPDO.CONNECTION_TCP'         ,
              // connection type for SQLite not supported
              105 => 'The connection type requested for the selected \
                      DBMS is not supported. The only connection type \
                      allowed  for SQLite is BBKK_aPDO.CONNECTION_FILE'      ,
              // only absolute path for SQLite database file
              106 => 'File path for SQLite database file must be absolute'   ,
              // connection already established
              107 => 'A connection is already open. Can\'t open a new one'   ,

              121 => 'Host not valid'                                        ,
              122 => 'TCP port not valid');


    /*
     * Constants: error codes
     *
     * ERR__DBMS_NOT_SPPRTD     - DBMS not supported
     */
    const CONN_ALREADY_OPN             = 101;  // connection errors

    const ERR__CONN_TYPE_NOT_SPPRTD         = 102;

    const ERR__PGSQL_CONN_TYPE_NOT_SPPRTD   = 103;
    const ERR__MYSQL_CONN_TYPE_NOT_SPPRTD   = 104;
    const ERR__SQLITE_CONN_TYPE_NOT_SPPRTD  = 105;
    const ERR__SQLITE_ONLY_ABSPATH_DBFILE   = 106;

    const ERR_TCP_HOST_NOT_VLD              = 121;
    const ERR_TCP_PORT_NOT_VLD              = 122;




    /*
     * Method: open_connection
     *   *[public]* open the connection to specified DBMS
     *
     * Description:
     *   This method wraps specific class private [DBMS]_connect methods.
     *
     *   If parameters are not set correctly or the connection can't be
     *   established, FALSE is returned and public attribute
     *   <BBKK_aPDO.$user_warning_message> is set to custom or driver text
     *   message.
     *
     * Returns:
     *   {bool} TRUE if the connection to database is established correctly,
     *   FALSE otherwise
     *
     * See also:
     *   <BBKK_aPDO.mysql_connect>, <BBKK_aPDO.sqlite_connect>,
     *   <BBKK_aPDO.postresql_connect>, <BBKK_aPDO.$user_warning_message>
     *
     */
    public function open_connection()
    {
        // do checks
        if (true) {
            // connection already open
            if ( is_a($this->pdo, 'PDO') || $this->pdo !== null ) {
                trigger_error(
                    $this->errors_list[self::ERR__CONN_ALREADY_OPN],
                    E_USER_ERROR);
            }
        }

        switch ($this->db_type)
        {
            case self::DBMS_MYSQL:
                return $this->mysql_connect();
            case self::DBMS_SQLITE:
                return $this->sqlite_connect();
            case self::DBMS_POSTGRESQL:
                return $this->postgresql_connect();
            default:
                trigger_error($this->errors_list[self::ERR__DBMS_NOT_SPPRTD],
                              E_USER_ERROR);
                break;
        }

        return false;
    }


    /*
     * Method: close_connection
     *   close database connection
     *
     * Details:
     *   not always is possible to close the connection. Please refer to
     *   <http://us3.php.net/manual/en/pdo.connections.php#114822>
     *
     *   The attribute that handles DBMS connection is checked before
     *   attempting to close it.
     *
     * Returns:
     *   {bool} TRUE if the close operations are performed, FALSE if not
     */
    public function close_connection()
    {
        // check if connection is active
        if ( !is_a($this->pdo, 'PDO') ) {
            trigger_error($this->error_messages[ERR_CONN_NOT_OPND],
                          E_USER_WARNING);
            return false;
        }

        switch ($this->db_type)
        {
            case self::DBMS_POSTGRESQL:
                // force the connection termination server-side
                $this->pdo->query('SELECT pg_terminate_backend(pg_backend_pid());');
                $this->pdo = null;
                break;
            case self::DBMS_MYSQL:
            case self::DBMS_SQLITE:
                $this->pdo = null;
                break;
        }

        return true;
    }



    public function create_database($if_not_exists = false,
                                    $adm_user = '',
                                    $adm_passwd = '')
    {
        switch ($this->db_type)
        {
            case self::DBMS_MYSQL:
                return $this->mysql_create_database($if_not_exists,
                                                    $adm_user,
                                                    $adm_passwd);
                break;
            case self::DBMS_SQLITE:
                return $this->sqlite_create_database($if_not_exists);
                break;
            case self::DBMS_POSTGRESQL:
                //return $this->postgresql_create_database($if_not_exists);
                break;
            default:
                trigger_error($this->errors_list[self::ERR__DBMS_NOT_SPPRTD],
                              E_USER_ERROR);
                break;
        }

        return false;
    }



    /*
        private function sqlite_create_database($if_not_exists = false)
        {
            if (true) {
                if ( $if_not_exists=== true && $if_not_exists === false ) {
                    $this->trigger_usr_err(BBKK_BaseClass::ERR__PARM_NOT_VLD_TYP);
                }
            }
        }
    */












    /*
     * Method: postgresql_connect
     *   open a connection to PostgreSQL DBMS according to specified parameters
     *
     * Description:
     *   - supported connection types are <BBKK_aPDO.CONNECTION_SOCKET> and
     *     <BBKK_aPDO.CONNECTION_TCP>
     *   - socket connection is suggested when postgreSQL is local
     *
     * Return:
     *   {bool} TRUE if the connection is succesfully established
     *
     * See also:
     *   <BBKK_aPDO.open_connection>
     * /
    private function postgresql_connect()
    {
        // TCP connection
        if ( $this->connection_type === BBKK_aPDO::CONNECTION_TCP )
        {
            // if port is not set, assign default service port
            if ( $this->port === 0 ) $this->port = BBKK_aPDO::POSTGRESQL_DEFAULT_PORT;

            // do checks
            if (true)
            {
                // basic hostname check
                if ( !is_string($this->host) || $this->host === ''  )
                {
                    trigger_error($this->error_messages[BBKK_aPDO::ERR_TCP_HOST_NOT_VLD],
                                  E_USER_ERROR);
                    return false;
                }

                // check port and/or set default value
                $this->check_tcp_port();
            }

            // create DSN string
            $dsn =  'pgsql:host=' . $this->host . ';port=' . $this->port .
                    ';dbname=' . $this->db_name . ';user=' . $this->username;
            if ( is_string($this->password) && !$this->password === '' ) {
                $dsn .= ';password=' . $this->password;
            }

            // try connection
            try {
                $this->pdo = new PDO($dsn, $this->username, $this->password);
            }
            catch (PDOException $e) {
                $error_text = 'connessione al database PostgreSQL non effettuata: '.$e->getMessage();
                $this->setError($error_text, E_USER_ERROR);
                return false;
            }
        }
        else
        // SOCKET connection
        if ( $this->connection_type === BBKK_aPDO::CONNECTION_SOCKET )
        {
            // add implementation here
        }
        // FILE connection (does not exist for PostgreSQL)
        if ( $this->connection_type === BBKK_aPDO::CONNECTION_FILE )
        {
            trigger_error($this->error_messages[BBKK_aPDO::ERR__PGSQL_CONN_TYPE_NOT_SPPRTD],
                          E_USER_ERROR);
            return false;
        }
        else
        {
            trigger_error($this->error_messages[BBKK_aPDO::ERR__CONN_TYPE_NOT_SPPRTD],
                          E_USER_ERROR);
            return false;
        }

        return true;
    }



    /*
     * Please document me
     * /
    private function check_tcp_port()
    {
        if ( !is_numeric($this->port) ||
             $this->port < 1          ||
             $this->port > 65535         )
        {
            trigger_error($this->error_messages[ERR_TCP_PORT_NOT_VLD],
                          E_USER_ERROR);
            return false;
        }
    }


    /*
     * Method: mysql_connect
     *   open a connection to MySQL DBMS according to specified parameters
     *
     * Description:
     *   - actually the only supported connection type is
     *     <BBKK_aPDO.CONNECTION_TCP>
     *
     * Return:
     *   {bool} TRUE if the connection is succesfully established
     *
     * See also:
     *   <BBKK_aPDO.open_connection>
     * /
    private function mysql_connect()
    {
    }

    /*
     * Method: sqlite_connect
     *   *[private]* connect to a SQLite database
     *
     * Description:
     *   SQLite is a little different from other DBMS, because... it is not a
     *   DBMS. Connect to such a database means to open a data file, and there
     *   is no server to connect to.
     *
     * Return:
     *   {bool} TRUE if the connection is opened, FALSE otherwise
     *
     * See also:
     *   <GEN_PDO.openConnection>, <GEN_PDO.$connection_type>,
     *   <GEN_PDO.$db_name>
     */
    private function sqlite_connect()
    {
        // checks
        if ( true ) {
            if ( $this->connection_type !== BBKK_aPDO::CONNECTION_FILE ) {
                $err_msg_id = BBKK_aPDO::ERR__SQLITE_CONN_TYPE_NOT_SPPRTD;
                $err_msg = $this->error_messages[$err_msg_id];
                trigger_error($err_msg, E_USER_ERROR);
                return false;
            }

            if ( substr($this->db_name, 0, 1) !== '/' ) {
                $err_msg_id = BBKK_aPDO::ERR__SQLITE_ONLY_ABSPATH_DBFILE;
                $err_msg = $this->error_messages[$err_msg_id];
                trigger_error($err_msg, E_USER_ERROR);
                return false;
            }
        }

        // create DSN string
        $dsn =  'sqlite:' . $this->db_name;

        // try connection
        try {
            $this->pdo = new PDO($dsn);
        }
        catch (PDOException $e) {
            $this->user_warning_message = 'error opening SQLite database ' .
                                          'connection: ' . $e->getMessage();
            return false;
        }

        return true;
    }








    /*
     *
     *
     *
    private function pdo_mysql_connect()
    {
        // check properties
        // TODO

        try
        {
            $this->dbh = new PDO('mysql:host='.$this->host.';
            dbname='.$this->dbname, $this->username, $this->password);
        }
        catch (PDOException $e)
        {
            $this->set_error($e->getMessage(), __METHOD__, __LINE__);
            $this->error_type = E_ERROR;
            return false;
        }

        return true;
    }
*/
/*
    private function pdo_sqlite_connect()
    {
        try
        {
            $this->dbh = new PDO('sqlite:'.$this->dbname);
        }
        catch (PDOException $e)
        {
            $this->set_error($e->getMessage(), __METHOD__, __LINE__);
            $this->error_type = E_ERROR;
            return false;
        }

        return true;
    }
*/






    /*
     * Method: check_connection_parameters
     *   verify if all basic connection parameters are correctly set
     *
     * Returns:
     *   {bool} TRUE if all parameters are correctly set, FALSE if not
     *
     */
/*
    private function check_connection_parameters()
    {
        // check selected database type

        // check selected connection type according to database type and
        // implementations

        // for tcp connection type, check host and port
    }
*/
}
