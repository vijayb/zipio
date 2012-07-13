#!/bin/sh

tar zcvf code.tar.gz www;
scp code.tar.gz ubuntu@23.21.145.240:/home/ubuntu/zipio;
ssh ubuntu@23.21.145.240 'cd /home/ubuntu/zipio; rm -rf /home/ubuntu/zipio/www; tar zxvf code.tar.gz; rm -rf code.tar.gz'
rm -rf code.tar.gz;
