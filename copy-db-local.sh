#!/bin/sh

ssh ubuntu@zipio.com 'mysqldump -u root --password=daewoo Zipio > /home/ubuntu/Zipio.out'
scp ubuntu@zipio.com:/home/ubuntu/Zipio.out Zipio.out
mysql -u root --password=daewoo Zipiyo < Zipio.out
rm -rf Zipio.out