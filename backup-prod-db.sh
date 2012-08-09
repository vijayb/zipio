#!/bin/sh

ssh ubuntu@zipio.com 'mysqldump -u root --password=daewoo Zipio > /home/ubuntu/Zipio-`date +%s`.out'
