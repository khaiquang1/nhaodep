#!/bin/bash
#SQL="DELETE t1 FROM leads t1 INNER JOIN leads t2 WHERE t1.id < t2.id AND t1.lead_phone = t2.lead_phone;"
#MYSQL_USER="etrip4ucrm"
#MYSQL_PASS="etrip4ucrm@123"
#MYSQL_DB="etrip_crm"
#echo $SQL | /usr/bin/mysql --user=$MYSQL_USER --password=$MYSQL_PASS $MYSQL_DB

#SQL1="update bravo_tours set total_review=(select count(id) from bravo_review where object_model='tour' and object_id=bravo_tours.id)"
#MYSQL_USER1="etrip4u"
#MYSQL_PASS1="etrip4u@123"
#MYSQL_DB1="etrip4u"
#echo $SQL1 | /usr/bin/mysql --user=$MYSQL_USER1 --password=$MYSQL_PASS1 $MYSQL_DB1

SQL1="DELETE t1 FROM logs_call t1 INNER JOIN logs_call t2  WHERE t1.id > t2.id AND t1.lead_id = t2.lead_id and t1.start_time = t2.start_time and t1.end_time = t2.end_time"
MYSQL_USER1="salesdy"
MYSQL_PASS1="salesdy@123"
MYSQL_DB1="salesdy"
echo $SQL1 | /usr/bin/mysql --user=$MYSQL_USER1 --password=$MYSQL_PASS1 $MYSQL_DB1
