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
 * This file contains functions useful for numeric checks and manipulation
 * and is part of BBKK-dbdatalib project.
 * The project can be followed and downloaded at
 * https://github.com/bubbakk/BBKK-dbdatalib
 */



/*
 * Function: bbkklib_numeric__is_positive_integer
 *   Check whether passed parameter is a positive integer or not
 *
 * Parameters:
 *   $value {integer|string} - value to check (default: 1)
 *   $strict_integer {bool}  - if TRUE, the first parameter must also be
 *                             integer type, if FALSE also numeric string will
 *                             be accepted and checked (default: FALSE)
 *
 * Depends on:
 *   <bbkklib_numeric__is_numeric_string>
 *
 * Returns:
 *   {bool} TRUE passed $value parameter is positive integer, FALSE if is not
 */
function bbkklib_numeric__is_positive_integer($value = 1,
                                              $strict_integer = false)
{
    if ( $strict_integer === true ) {
        $is_good_integer = is_int($value);
    }
    else
    if ( $strict_integer === false ) {
        $is_int            = is_int($value);
        $is_integer_string = bbkklib_numeric__is_numeric_string($value);

        $is_good_integer = $is_int || $is_integer_string;
    }
    else {
        die('the second parameter must be a boolean value');
    }

    return ( $is_good_integer && $value > 0 );
}


/*
 * Function: bbkklib_numeric__is_numeric_string
 *   Checks whether passed string is a valid integer number
 *
 * Details:
 *   this function returns TRUE if and only if passed string contains a well
 *   formed integer decimal value, positive or negative as well. Returns FALSE
 *    n: leading/trailing white spaces, floats, format different from decimal
 *   (hex/octal/binary formats will make this function return FALSE) or
 *   exponential notation (eg: 0.3e5).
 *
 *   This function returns FALSE evens if the parameter to test begins by '0'
 *   (in other words, '0123' does not accepted to be a numeric string)
 *
 *   *Warning*: this function return FALSE if an integer type is passed: only
 *   strings are accepted as input parameter
 *
 *   This function partially replaces
 *   <is_numeric() at http://php.net/manual/en/function.is-numeric.php>. In fact
 *   that one returns TRUE even if the string have leading/trailing white
 *   spaces, or the number is a float, or is in exponential notation, or the
 *   string begins with the decimal separator dot (eg: '.12', '  .67' and
 *   ' 0.3e12' are considered valid string numbers by that PHP's is_numeric()
 *   function. This is a more strict version.
 *
 * Parameters:
 *   $value {mixed} - the variable being evaluated (default: '')
 *
 * Returns:
 *   {bool} TRUE if passed variable is a valid number, FALSE otherwise
 */
function bbkklib_numeric__is_numeric_string($value = '')
{
    // non-string parameter or emtpy string are not integer strings (no error)
    if ( !is_string($value) || $value === '' ) {
        return false;
    }

    $no_chars = strlen($value);

    // the string number must not begin by '0'
    if ( $no_chars > 1 && $value[0] === '0' ) {
        return false;
    }

    // the string number can begin by '-' (negative integer)
    $start_from = 0;
    if ( $no_chars > 1 && $value[0] === '-' ) {
        $start_from = 1;
    }

    // check the ASCII code, char by char
    for ( $i = $start_from ; $i < $no_chars ; $i++)
    {
        $ascii_code = ord($value[$i]);

        if ( $ascii_code < 48 || $ascii_code > 57 ) {
            return false;
        }
    }

    return true;
}
