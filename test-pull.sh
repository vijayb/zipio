#!/bin/sh

ssh ubuntu@zipiyo.com 'cd /home/ubuntu/zipio; cp -r www www-test; tar zcvf www-test.tar.gz www-test; rm -rf www-test;'
scp ubuntu@zipiyo.com:/home/ubuntu/zipio/www-test.tar.gz /home/sanjay/zipio
ssh ubuntu@zipiyo.com 'cd /home/ubuntu/zipio; rm -rf www-test.tar.gz;'
tar zxvf www-test.tar.gz;
rm -rf www-test.tar.gz;