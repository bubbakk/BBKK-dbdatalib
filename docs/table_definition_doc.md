Table definition format
=======================
This document describes table definition format used in library BBKK_aPDO.



Purpose
--------------
Table definition format is built to be as much human readable as possible, like
markdowm formats. I created it instead of using an existing one because there
is not one feasable to read, edit and create from scratch like this one.

Once the tables are described, a parser is able to read it and convert it in
DBMS specific `CREATE TABLE` SQL language.

Even if the main purpose is to reach a generic definition, it is possible to use
DBMS specific keywords and/or definitions.

[Suggestions are welcome](https://github.com/bubbakk/BBKK-dbdatalib/issues)



Format overview
---------------
A well formed table definition file contains 2 sections/blocks: the table header
and the table body. The header contains table-level related informations, the
body describes all necessary fields informations.

The two sections are separated by two carriage returns such as:

    table header


    table body


### Table header ###

Table header is made of one to three parts:

* table name
* table desciption
* fields compounding primary key

Only the **table name** is mandatory.

This is a table header example:

    users
    ------
    This table contains informations about users login and little statistics
    like the last access date and number of failed login after a good one.
    [id,first_name]

that is, section by section:

1. **table name**: _mandatory_, one line only, the very first of the file.
   Use no (white)spaces and no special/fancy characters; just [a-zA-Z0-9_]
   (technically speaking, regex matches "\w")
2. **table name separator**: _mandatory_, one or more '-' charachter
   (technically speaking, regex matches "[-+]")
3. **table comment**: optional, single or multiline text. Lines will be
   trimmed and appended with a space character between each one
4. **table primary key**: optional, list of comma separated fields inside square
   brackets that compose the primary key. Is obvious that all fields must be
   declared in the table body below. Each field will be whitespace-trimmed

The order is important.

### Table body ###

Each field is described as follows:

* name followed, optionally, by a field template
* text field comment
* data type and eventually following size and default value
* modifiers (such as not null, unique, ....)

This is an example:

    id < bigint_counter
    ;identificatore univoco progressivo

and this is anothor one:

    first_name
    ;nome
        .varchar (30) ['ciao']
        .not_null
        .unique

Apart from the the first row containing the field name, the order of the other
elements is free. The only rule is that the multiline is not allowed (actually
for field comment too).

Let's explain in details the syntax.

1. **field name**: the field section must begin with the field name. Like table
   name, use no (white)spaces and no special/fancy characters; just [a-zA-Z0-9_]
   (technically speaking, regex matches "\w")

    1a. **field template**: after the field name, separated by spaces/tabs (at
        least one of them) there can be the symbol '<' followed by other
        spaces/tabs (at least one) and after that, the name of a defined field
        data template. (please see below section field template)
2. **field comment**: after a ';' (semicolon) character, anything that follows
   until the end of line is considered to be the field comment. Optional
3. **field data type**: after a '.' (dot) this line contains the data type
   (similar to the ones of DBMS) and eventually the size and the default value.
   Size is an integer number greater than 0 inside round parenthesys. Default
   data is anything inside squared parenthesys.
   Allowed data types are:
    * bool
    * smallint, integer/int, bigint
    * real, double,
    * serial, bigserial (shortcut for autoincrement unique not-null counter)
    * char, varchar, text
    * date, time, timestamp, timestampz
    * binary
4. **field options**: after a '.' (dot) there can be none or all of the
   following case-insensitive values:
    * not_null (or not-null or notnull)
    * unique

Except from the field name, all the following field details can be indented as
wanted, by spaces or tabs. Valid formats are all the following.

Example 1: everything is left aligned, no white spaces between markers and
           values

    first_name < my_template
    ;nome
    .varchar (30) ['ciao']
    .not_null
    .unique

Example 2: everything is left aligned but some spaces are used to format text

    first_name < my_template
      ; nome
      . varchar (30) ['ciao']
      . not_null
      . unique

Example 3: all punctuations are aligned on the left and comment and keywords
           are right aligned

    first_name     < my_template
    ;                       nome
    .                     unique
    .                   not_null
    .      varchar (30) ['ciao']


