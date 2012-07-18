#!/bin/sh

tar zcvf code.tar.gz www;
scp code.tar.gz ubuntu@zipio.com:/home/ubuntu/zipio;
ssh ubuntu@zipio.com 'cd /home/ubuntu/zipio; rm -rf /home/ubuntu/zipio/www; tar zxvf code.tar.gz; rm -rf code.tar.gz'
rm -rf code.tar.gz;
