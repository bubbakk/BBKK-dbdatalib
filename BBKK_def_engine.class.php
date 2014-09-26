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
 * This file contains BBKK_def_engine class implementation and is part of
 * BBKK-dbdatalib project.
 *
 * The project can be followed at <https://github.com/bubbakk/BBKK-dbdatalib>
 *
 * Code is documented using <NaturalDocs at http://www.naturaldocs.org> format.
 */



/*
 * Class: BBKK_def_engine
 *   Implements database tables def file parsing and automatic code building.
 *
 * Details:
 *
 */
class BBKK_def_engine
{
    /*
     * Property: $def_fields_template
     *   *[private]* {array} fields templates array
     *
     * Details:
     *   this array is filled calling the method <BBKK_aPDO.parse_template_def>
     */
    private $def_fields_template = array();



    /*
     * Property: $def_table
     *   *[private]* {array} table definition as result of a parse operation
     *
     * Details:
     *   this array is filled calling the method <BBKK_aPDO.parse_table_def>
     */
    private $def_table = array();




    /*
     * Property: $parser_data_types
     *   *[private]* {array} definition of generic data types that can be used
     */
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
        $this->def_table = array('header' => array(), 'body' => array());
        $TH              = &$this->def_table['header'];
        $TB              = &$this->def_table['body'];

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
        $fields_no    = 0;
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
                    //if ( !isset($this->def_fields_template[$t_name]) )
                    //    die('template "' . $t_name . '" not found');
                    //$field = $field + $this->def_fields_template[$t_name];
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
                ++$fields_no;
            }
        }



        // apply template
        for ( $i = 0 ; $i < $fields_no ; $i++ )
        {
            if ( isset($TB[$i]['template']) )
            {
                $template_name = $TB[$i]['template'];
                if ( !isset($this->def_fields_template[$template_name])  ) {
                    die('template "' . $template_name . '" not found');
                } else {
                    $template = $this->def_fields_template[$template_name];
                    if ( isset($template['description']) ) {
                        unset($template['description']);
                    }
                    // merge template definitions
                    $TB[$i] = array_merge($TB[$i], $template);
                }
            }
        }
        return $this->def_table;
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
        if ( !is_string($def_content) ) return false;

        $this->def_fields_template = array();

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
            }

            // template block parameters
            if ( !$token_found )
            {
                $this->parse_block_parameters($t_line, $template);
            }

            // if nothing is found (empty line) the block is completed
            if ( $t_line === '' ) {
                $this->def_fields_template[$template_name] = $template;
            }
        }

        return $this->def_fields_template;
    }


    /*
     * Method: parse_block_parameters
     *   {private} Parse details blocks
     *
     * Parameters:
     *   $str_to_parse {string} - string to parse (default = '')
     *   &$res   {array ref}    - the array where to save results
     *                            (default = array())
     *
     * Returns:
     *   {bool} TRUE if parsing is done FALSE otherwise (parameters error)
     *
     * See:
     *   <BBKK_aPDO.parse_table_def>, <BBKK_aPDO.parse_template_def>
     */
    private function parse_block_parameters($str_to_parse = '', &$res = array())
    {
        if ( !is_string($str_to_parse) )  return false;
        if ( !is_array($res) )      return false;

        // description
        // begin with a semi-colon character eventually preceded by whitespaces
        $semicolon_reges = '[[:blank:]]*;[[:blank:]]*(.*)';
        preg_match("/$semicolon_reges/", $str_to_parse, $matches);
        if ( count($matches) > 1 ) {
            $res['description'] = $matches[1];
            return;
        }

        // field details
        // begin with a full stop eventually preceded by whitespaces
        $period_regex = '[[:blank:]]*\.[[:blank:]]*([\w-_]*)';
        preg_match("/$period_regex/", $str_to_parse, $matches);
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
                preg_match("/$digitinrndparen_regex/", $str_to_parse, $matches);
                if ( count($matches) > 0 ) {
                    $res['length'] = $matches[1];
                }

                // optional
                // field default value
                // any string in squared parenthesis
                $anystringinsqparen_regex = '\[(.+)\]';
                preg_match("/$anystringinsqparen_regex/", $str_to_parse, $matches);
                if ( count($matches) > 0 ) {
                    $res['default'] = $matches[1];
                }
            }
        }

        return true;
    }


    /*
     * Method: generate_tables_names_constants
     *   {private} generate tables names contants for DBMS target
     *
     * Parameters:
     *   $dbms_target {string} - target DBMS
     *
     * Returns:
     *
     */
    public function generate_tables_names_constants($dbms_target)
    {
        ;
    }


    /*
     * Method: generate_tables_data_structure
     *   {private} generate tables names contants for DBMS target
     *
     * Parameters:
     *   $dbms_target {string} - target DBMS
     *
     * Returns:
     *
     */
    public function generate_tables_data_structure($target)
    {
        ;
    }
}