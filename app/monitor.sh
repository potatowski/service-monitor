#!/bin/bash
cd /var/www/service-monitor
while true
do
    nohup php /var/www/service-monitor/app/bin/console app:check-routes
    sleep 60
done
