#!/bin/bash
SQL2="DELETE t1 FROM leads t1 INNER JOIN leads t2 WHERE t1.id < t2.id AND t1.psid = t2.psid;"
MYSQL_USER1="salesdy"
MYSQL_PASS1="salesdy@123"
MYSQL_DB1="salesdy"
echo $SQL2 | /usr/bin/mysql --user=$MYSQL_USER1 --password=$MYSQL_PASS1 $MYSQL_DB1
