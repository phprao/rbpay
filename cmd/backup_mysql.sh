#!/bin/bash 

/usr/local/mysql/bin/mysqldump --defaults-extra-file=/etc/my.cnf.d/mysql-clients.cnf chpay > /data/wwwroot/backup/backup_`date '+%Y-%m-%d'`.sql