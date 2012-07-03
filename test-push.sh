#!/bin/sh

tar zcvf code.tar.gz www;
scp code.tar.gz ubuntu@ec2-23-22-14-153.compute-1.amazonaws.com:/home/ubuntu/zipio;
ssh ubuntu@ec2-23-22-14-153.compute-1.amazonaws.com 'cd /home/ubuntu/zipio; rm -rf /home/ubuntu/zipio/www; tar zxvf code.tar.gz; rm -rf code.tar.gz'
rm -rf code.tar.gz;
