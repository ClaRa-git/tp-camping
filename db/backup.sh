#!/usr/bin/sh
mariadb-dump camping -uroot -psuperAdmin > /root/init.sql
echo "Sauvegarde terminée"