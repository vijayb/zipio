#!/bin/sh

ssh ubuntu@zipio.com 'cd /home/ubuntu/zipio; cp -r www www-live; cp -r notifier notifier-live; tar zcvf www-live.tar.gz www-live notifier-live; rm -rf www-live notifier-live;'
scp ubuntu@zipio.com:/home/ubuntu/zipio/www-live.tar.gz /home/sanjay/zipio
ssh ubuntu@zipio.com 'cd /home/ubuntu/zipio; rm -f www-live.tar.gz;'
tar zxvf www-live.tar.gz;
rm -f www-live.tar.gz;