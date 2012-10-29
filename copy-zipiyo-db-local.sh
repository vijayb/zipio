#!/bin/sh

ssh ubuntu@zipiyo.com 'mysqldump -u root --password=daewoo Zipiyo > /home/ubuntu/Zipiyo.out'
scp ubuntu@zipiyo.com:/home/ubuntu/Zipiyo.out Zipiyo.out
mysql -u root --password=daewoo Zipiyo < Zipiyo.out
rm -rf Zipiyo.out
