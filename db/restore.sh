#!/usr/bin/sh

mariadb camping -uroot -psuperAdmin < /root/init.sql
echo "Restauration terminée"
