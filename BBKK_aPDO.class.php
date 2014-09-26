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
     * Constants: DBMS connection "medium"
     *
     * CONN_VIA_FILE    - connect through a file (generally only for SQLite)
     * CONN_VIA_TCP     - TCP connection
     * CONN_VIA_SOCKET  - fastest local connection (when supported)
     *
     *
     */
    const CONN_VIA_FILE     = 1;
    const CONN_VIA_TCP      = 2;
    const CONN_VIA_SOCKET   = 4;



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
    protected $db_name  = '';

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
     * Property: $type
     *   *[protected]* {int} sets connection type: file, TCP or socket
     */
    protected $type     = 0;

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
    protected $charset  = '';




    /*
     * Property: $last_exception
     *   *[protected]* {Exception} last exception occurred
     */
    protected $last_exception = null;



    private $parser_field_templates = array();


    private $parser_data_types = array(
        'bool',
        'smallint', 'integer', 'int', 'bigint',
        'serial', 'bigserial',
        'real', 'double',
        'char', 'varchar', 'text',
        'date', 'time', 'timestamp', 'timestampz',
        'binary'
    );








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
            case 'type'    :
            case 'db_name' :
            case 'username':
            case 'password':
            case 'charset' :
            case 'schema'  :
                $this->$attr_name = $value;
                break;
            default:
                parent::__set($attr_name, $value);
                break;
        }
    }



    /*
     * Method: parse_table_def
     *   {public} Parse table definition
     *
     * Parameters:
     *   $def_content {string} - definition data to parse (default = '')
     *
     * Returns:
     *   {array} containing structured generic data for table and fields
     *   definition
     *
     * See:
     *   please read [definition format documentation at http://goo.gl/AdAQ6H]
     */
    public function parse_table_def($def_content = '')
    {
        $TABLE = array('header' => array(), 'body' => array());
        $TH    = &$TABLE['header'];
        $TB    = &$TABLE['body'];

        $func_trim = function($row){return trim($row);};



        // split sections
        $sections = explode("\n\n\n", $def_content);
        $table_def  = $sections[0];
        $table_flds = $sections[1];
        unset($sections);



        // TABLE parser
        $table_lines = explode("\n", $table_def);
        $table_lc    = count($table_lines);
        // table name
        preg_match("[\w+]", $table_lines[0], $matches);
        if ( $matches[0] === "" ) die('can\'t find table name');
        $TH['table_name'] = $matches[0];
        // table name delimiter
        preg_match("[-+]", $table_lines[1], $matches);
        if ( $matches[0] === "" ) die('can\'t parse table name delimiter');
        // search for primary key fields
        $last_line     = $table_lines[$table_lc - 1];
        $first_char_ls = substr($last_line, 0, 1);
        $last_char_ls  = substr($last_line, -1, 1);
        $pkey_found    = false;
        if ( $first_char_ls === '[' && $last_char_ls === ']') {
            $pkey_fields = explode(',', substr($last_line, 1, -1));
            $pkey_found  = true;
            $pkey_fields = array_map($func_trim, $pkey_fields); // trim fields
            $TH['pkeys'] = $pkey_fields;
        }
        else $TH['pkeys'] = '';

        // description
        if ($pkey_found) $last_desc_idx = $table_lc - 2;
        else             $last_desc_idx = $table_lc - 1;
        if ( $last_desc_idx >= 2 ) {
            $desc_array = $table_lines;
            unset($desc_array[0], $desc_array[1]);
            if ( $pkey_found ) unset($desc_array[$table_lc - 1]);

            $desc_array = array_map($func_trim, $desc_array);   // trim lines
            $desc_text  = implode(' ', $desc_array);
            $TH['description'] = $desc_text;
        }
        else $TH['description'] = '';



        // FIELDS parser
        $fields_lines = explode("\n", $table_flds);
        $fields_lc    = count($fields_lines);
        $fields_names = array();
        // starting parser, line by line
        for ( $i = 0 ; $i < $fields_lc ; $i++ )
        {
            // avoid match test if line role is found
            $token_found = false;

            $f_line = $fields_lines[$i];

            // field name with or without field template
            // start with nothing preceding the field name
            preg_match("/^[\w]+/", $f_line, $matches);  // start with no w-spcs
            if ( count($matches) === 1 )
            {
                $token_found = true;

                // reset field data
                $field = array();

                // field parameters recognition
                $field_name = $matches[0];

                // check field name duplication
                if ( in_array($field_name, $fields_names) )
                    die('duplicate field name');

                //$fields_lines[] = $field_name_or_desc;

                $field['name'] = $field_name;

                // template ?
                $t_name = '';
                preg_match("/^[\w]+ +< +([\w]+)/", $f_line, $matches);
                if ( count($matches) === 2 ) {
                    $t_name = $matches[1];
                    //if ( !isset($this->parser_field_templates[$t_name]) )
                    //    die('template "' . $t_name . '" not found');
                    //$field = $field + $this->parser_field_templates[$t_name];
                    $field['template'] = $t_name;
                }
                //var_dump($matches);
            }

            // field block parameters
            if ( !$token_found )
            {
                $this->parse_block_parameters($f_line, $field);
            }

            // if nothing is found (empty line) the field block is completed
            if ( $f_line === '' ) {
                $TB[] = $field;
            }
        }

        return $TABLE;
    }



    /*
     * Method: parse_template_def
     *   {public} Parse template definition
     *
     * Parameters:
     *   $def_content {string} - definition data to parse (default = '')
     *
     * Returns:
     *   {array} containing structured generic data for table and fields
     *   definition
     *
     * See:
     *   please read [definition format documentation at http://goo.gl/AdAQ6H]
     */
    public function parse_template_def($def_content = '')
    {
        $this->parser_field_templates = array();

        // FIELDS parser
        $template_lines = explode("\n", $def_content);
        $template_lc    = count($template_lines);
        $template_names = array();
        // starting parser, line by line
        for ( $i = 0 ; $i < $template_lc ; $i++ )
        {
            // avoid match test if line role is found
            $token_found = false;

            $t_line = $template_lines[$i];

            // template block name
            // start with nothing preceding the name itself
            $beginchar_regex = '/^[\w]+/';
            preg_match($beginchar_regex, $t_line, $matches);  // start with no w-spcs
            if ( count($matches) === 1 )
            {
                $token_found = true;

                // reset template data
                $template = array();

                // template name
                $template_name = $matches[0];

                // check template name duplication
                if ( in_array($template_name, $template_names) )
                    die('duplicate template name');

                //$fields_lines[] = $field_name_or_desc;

                $template['name'] = $template_name;
            }

            // template block parameters
            if ( !$token_found )
            {
                $this->parse_block_parameters($t_line, $template);
            }

            // if nothing is found (empty line) the block is completed
            if ( $t_line === '' ) {
                $this->parser_field_templates[] = $template;
            }
        }

        return $this->parser_field_templates;
    }



    /*
     * Method: parse_block_parameters
     *   {private} Parse details blocks
     *
     * Parameters:
     *   $target {string}    - string to parse (default = '')
     *   &$res   {array ref} - the array where to save results
     *                         (default = array())
     *
     * Returns:
     *   {bool} TRUE if parsing is done FALSE otherwise (parameters error)
     *
     * See:
     *   <BBKK_aPDO.parse_table_def>, <BBKK_aPDO.parse_template_def>
     */
    private function parse_block_parameters($target = '', &$res = array())
    {
        if ( !is_string($target) )  return false;
        if ( !is_array($res) )      return false;

        // description
        // begin with a semi-colon character eventually preceded by whitespaces
        $semicolon_reges = '[[:blank:]]*;[[:blank:]]*(.*)';
        preg_match("/$semicolon_reges/", $target, $matches);
        if ( count($matches) > 1 ) {
            $res['description'] = $matches[1];
            return;
        }

        // field details
        // begin with a full stop eventually preceded by whitespaces
        $period_regex = '[[:blank:]]*\.[[:blank:]]*([\w-_]*)';
        preg_match("/$period_regex/", $target, $matches);
        if ( count($matches) > 0 )
        {
            // case non-sensitive
            $word_found = strtolower($matches[1]);

            // modifier: NOT NULL
            $nn1 = ($word_found === 'not-null');
            $nn2 = ($word_found === 'not_null');
            $nn3 = ($word_found === 'notnull');
            if ( $nn1 || $nn2 || $nn3 ) {
                $res['not null'] = true;
            }
            // modifier: UNIQUE
            elseif ( $word_found === 'unique' ) {
                $res['unique'] = true;
            }
            // data type
            elseif ( in_array($word_found, $this->parser_data_types) )
            {
                $res['data type'] = $word_found;

                // optional
                // field length
                // digit in round parenthesis
                $digitinrndparen_regex = '\((\d+)\)';
                preg_match("/$digitinrndparen_regex/", $target, $matches);
                if ( count($matches) > 0 ) {
                    $res['length'] = $matches[1];
                }

                // optional
                // field default value
                // any string in squared parenthesis
                $anystringinsqparen_regex = '\[(.+)\]';
                preg_match("/$anystringinsqparen_regex/", $target, $matches);
                if ( count($matches) > 0 ) {
                    $res['default'] = $matches[1];
                }
            }
        }

        return true;
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

}
