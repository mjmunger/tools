# tools
Useful tools for development.

## Abstractor

This class is a code generator that abstracts database tables into a *minimalistic* class.

### Database

There is a test database structure that is derived from 

#### Installing the test database

1. Use mysql to import the `testdb.sql` file.
````
mysql < utildb.restore.sql
````
2. Grant privs to the test user:
````
GRANT ALL ON utildb.* TO 'utiltester'@'localhost' IDENTIFIED BY '5AomV7ksqXMkHjvR';
````

#### Configuring `phpunit.xml`

Make sure phpunit.xml already has this section, and if not, add it:
````
   <php>
      <var name="DB_DSN" value="mysql:dbname=util;host=localhost" />
      <var name="DB_USER" value="utiltester" />
      <var name="DB_PASSWD" value="5AomV7ksqXMkHjvR" />
      <var name="DB_DBNAME" value="utildb" />
   </php>
````