Table definition format
=======================
This document describes table definition format used in library BBKK_aPDO.



Purpose
--------------
The idea is taken from markdowm formats: they are readable even without a
formatter. The reasone because I invented it is to make feasable to anyone to
read, edit and create from scratch a table abstract descrition for a generic
application.

Once the tables are described, the developed parser is able to read and convert
it in DBMS specific `CREATE TABLE` SQL language.

Even if the main purpose is to reach a generic level, it is possible to use
DBMS specific keywords and/or definitions.

[Suggestions are welcome](https://github.com/bubbakk/BBKK-dbdatalib/issues)



Format overview
---------------
A correct definition file contains 2 sections/blocks: the table header and the
table body. The header contains table related informations, the body specifies
all fields informations.

The two sections are separated by an extra carriage return such as:

    table header

    table body


### Table header ###

Table header can contain definitions for:

* table name
* table desciption
* fields compounding primary key

Only the table name is name is mandatory.

Table header is described like this:

    users
    ------
    This table contains informations about users login and little statistics
    like the last access date and number of failed login after a good one.
    [id,first_name]

that is, section by section:

1. **table name**: _mandatory_, one line only, the very first of the file.
   Use no (white)spaces and no special/fancy characters; just [a-zA-Z0-9_]
   (technically speaking, regex match is "\w")
2. **table name separator**: _mandatory_, one or more '-' charachter
   (technically speaking, regex match is "[-+]")
3. **table description**: not mandatory, single-multiline text. All lines will
   be trimmed and appended with a space characted between each one. No
   punctuation is needed
4. **table primary key**:  not mandatory,list of comma separated fields that
   compose the primary key. Is obvious that all fields must be declared in the
   table body. All fields will be whitespace-trimmed


### Table body ###

..document me...
