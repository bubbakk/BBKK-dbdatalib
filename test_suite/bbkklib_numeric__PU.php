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
 * This file contains PHPUnit tests for bbkklib_numeric library, and is part
 * of BBKK-dbdatalib project.
 *
 * The project can be followed and downloaded at
 * https://github.com/bubbakk/BBKK-dbdatalib
 */



$root = dirname(dirname(__DIR__));
require_once "../bbkklib_numeric.php";


class bbkklib_numeric__is_positive_integer__PUtest extends PHPUnit_Framework_TestCase
{
    public function test_integer_positive() {
        $value = 900;
        $test_res = bbkklib_numeric__is_positive_integer($value);

        $test = 'test ' . $value . ': a positive integer';
        print($test);
        print("\n");
        $this->assertTrue($test_res, 'test ' . strtolower($test) . ' fallito');
    }

    public function test_integer_positive_string() {
        $value = "900";
        $test_res = bbkklib_numeric__is_positive_integer($value);

        $test = 'test "' . $value . '": a positive integer string';
        print($test);
        print("\n");
        $this->assertTrue($test_res, 'test ' . strtolower($test) . ' fallito');
    }

    public function test_strict_integer_positive_string() {
        $value = "900";
        $test_res = bbkklib_numeric__is_positive_integer($value, true);

        $test = 'test "' . $value . '": a positive integer string; type ' .
                'check is enabled';
        print($test);
        print("\n");
        $this->assertFalse($test_res, 'test ' . strtolower($test) . ' fallito');
    }

    public function test_zero() {
        $value = 0;
        $test_res = bbkklib_numeric__is_positive_integer($value);

        $test = 'test ' . $value . ': non negative integer';
        print($test);
        print("\n");
        $this->assertFalse($test_res, 'test ' . strtolower($test) . ' fallito');
    }

    public function test_zero_string() {
        $value = "0";
        $test_res = bbkklib_numeric__is_positive_integer($value);

        $test = 'test "' . $value . '": non negative integer string';
        print($test);
        print("\n");
        $this->assertFalse($test_res, 'test ' . strtolower($test) . ' fallito');
    }

    public function test_strict_zero_string() {
        $value = "0";
        $test_res = bbkklib_numeric__is_positive_integer($value, true);

        $test = 'test "' . $value . '": a non negative integer string; type ' .
                'check is enabled';
        print($test);
        print("\n");
        $this->assertFalse($test_res, 'test ' . strtolower($test) . ' fallito');
    }

    public function test_float() {
        $value = 4.2;
        $test_res = bbkklib_numeric__is_positive_integer($value);

        $test = 'test ' . $value . ': a positive float';
        print($test);
        print("\n");
        $this->assertFalse($test_res, 'test ' . strtolower($test) . ' fallito');
    }

    public function test_float_dot_zero() {
        $value = 7.0;
        $test_res = bbkklib_numeric__is_positive_integer($value);

        $test = 'test ' . $value . ': a positive float ( .0 decimal part)';
        print($test);
        print("\n");
        $this->assertFalse($test_res, 'test ' . strtolower($test) . ' fallito');
    }

    public function test_negative_integer() {
        $value = -234;
        $test_res = bbkklib_numeric__is_positive_integer($value);

        $test = 'test ' . $value . ': a negative float';
        print($test);
        print("\n");
        $this->assertFalse($test_res, 'test ' . strtolower($test) . ' fallito');
    }

    public function test_string() {
        $value = "abcd";
        $test_res = bbkklib_numeric__is_positive_integer($value);

        $test = 'test "' . $value . '": a string';
        print($test);
        print("\n");
        $this->assertFalse($test_res, 'test ' . strtolower($test) . ' fallito');
    }
}



class gen__is_integer_string__PUtest extends PHPUnit_Framework_TestCase
{
    public function test_integer() {
        $value = 900;
        $test_res = lib_numeric__is_integer_string($value);

        $test = 'Intero positivo';
        print($test);
        print("\n");
        $this->assertFalse($test_res, 'test ' . strtolower($test) . ' fallito');
    }

    public function test_integer_string() {
        $value = "1234567890";
        $test_res = lib_numeric__is_integer_string($value);

        $test = 'Intero positivo stringa';
        print($test);
        print("\n");
        $this->assertTrue($test_res, 'test ' . strtolower($test) . ' fallito');
    }

    public function test_empty_string() {
        $value = '';
        $test_res = lib_numeric__is_integer_string($value);

        $test = 'stringa vuota';
        $this->assertFalse($test_res, 'test ' . strtolower($test) . ' fallito');
    }

    public function test_string_zero() {
        $value = '0';
        $test_res = lib_numeric__is_integer_string($value);

        $test = 'stringa "0"';
        print($test);
        print("\n");
        $this->assertTrue($test_res, 'test ' . strtolower($test) . ' fallito');
    }

    public function test_numeric_string_leading_zero() {
        $value = '012';
        $test_res = lib_numeric__is_integer_string($value);

        $test = 'stringa numerica che inizia con "0"';
        print($test);
        print("\n");
        $this->assertFalse($test_res, 'test ' . strtolower($test) . ' fallito');
    }

    public function test_non_numeric_string() {
        $value = 'abc';
        $test_res = lib_numeric__is_integer_string($value);

        $test = 'stringa interamente non numerica';
        print($test);
        print("\n");
        $this->assertFalse($test_res, 'test ' . strtolower($test) . ' fallito');
    }

    public function test_non_numeric_string_leading_integers() {
        $value = '123a';
        $test_res = lib_numeric__is_integer_string($value);

        $test = 'stringa alfanumerica (mista)';
        print($test);
        print("\n");
        $this->assertFalse($test_res, 'test ' . strtolower($test) . ' fallito');
    }
}
