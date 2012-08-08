#!/bin/sh

ssh ubuntu@zipio.com 'mysqldump -u root --password=daewoo Zipio > /home/ubuntu/Zipio.out'
scp ubuntu@zipio.com:/home/ubuntu/Zipio.out ubuntu@zipiyo.com:/home/ubuntu/Zipio.out
ssh ubuntu@zipiyo.com 'mysql -u root --password=daewoo Zipiyo < Zipio.out'