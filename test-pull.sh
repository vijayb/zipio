#!/bin/sh

ssh ubuntu@zipiyo.com 'cd /home/ubuntu/zipio; cp -r www www-test; cp -r notifier notifier-test; tar zcvf www-test.tar.gz www-test notifier-test; rm -rf www-test notifier-test;'
scp ubuntu@zipiyo.com:/home/ubuntu/zipio/www-test.tar.gz $HOME/zipio
ssh ubuntu@zipiyo.com 'cd /home/ubuntu/zipio; rm -f www-test.tar.gz;'
tar zxvf www-test.tar.gz;
rm -f www-test.tar.gz;