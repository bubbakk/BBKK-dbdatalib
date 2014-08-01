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

    const DEFAULT_TCP_PORT = 5432;

    /*
     * Method: __construct
     *   *[public]* default values for charset and TCP port
     *
     */
    public function __construct()
    {
        $this->tcp_port = BBKK_aPDO__mysql::DEFAULT_TCP_PORT;
        //$this->charset  = 'UTF8';
    }


    /*
     * Method: open_connection
     *   *[public]* open DBMS connection
     *
     */
    public function open_connection()
    {

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
}
