<?php

/*
 * Copyright 2013, 2014 Andrea Ferroni
 *
 * This file is part of "JoM|The Job Manager".
 *
 * "JoM|The Job Manager" is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * "JoM|The Job Manager" is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with JoM|The Job Manager. If not, see <http://www.gnu.org/licenses/>.
 *
 */

/*
 * This file contains functions useful in array manipulation
 */



/*
 * Function: bk2l_array__replace_values
 *   replace values in the first associative array with values of the second
 *   "plane" array
 *
 * Parameters:
 *   $varnames_and_defaults {array} - associative array. The order is
 *                                    important (default: array() )
 *   $replace_values {array}        - ordered list of values. Must have less
 *                                    or equal number of elements than the first
 *                                    parameter (default: array() )
 *
 * Details:
 *   This function can be very useful if want to implement overload methods
 *   expecially when inherited.
 *
 * Returns:
 *   the {array} as is the first parameter but with the valus in second one
 *   replaced
 */
function bk2l_array__replace_values($varnames_and_defaults = array(),
                                    $replace_values        = array() )
{
    // checks
    if ( true ) {
        if ( !isset($replace_values[0]) ) {
            trigger_error("second argument must be an array");
        }
        if ( !is_array($varnames_and_defaults) ) {
            trigger_error("first argument must be an array");
        }
        if ( count($varnames_and_defaults) < count($replace_values) ) {
            trigger_error("second passed argument array must have no more " .
                          "elements than the first one");
        }
    }



    // extracting variables names
    $var_names           = array_keys($varnames_and_defaults);
    // extracting (default) values
    $default_vals        = array_values($varnames_and_defaults);
    // replacing (defaults) values with new ones
    $values_and_defaults = array_replace($default_vals, $replace_values);

    return array_combine($var_names, $values_and_defaults);
}
