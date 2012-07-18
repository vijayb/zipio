#!/bin/sh

ssh ubuntu@zipio.com 'cd /home/ubuntu/zipio; cp -r www www-live; tar zcvf www-live.tar.gz www-live; rm -rf www-live;'
scp ubuntu@zipio.com:/home/ubuntu/zipio/www-live.tar.gz /home/sanjay/zipio
ssh ubuntu@zipio.com 'cd /home/ubuntu/zipio; rm -rf www-live.tar.gz;'
tar zxvf www-live.tar.gz;
rm -rf www-live.tar.gz;