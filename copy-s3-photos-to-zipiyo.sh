#!/bin/sh

s3cmd del s3://s3.zipiyo.com/photos/ --recursive
s3cmd cp s3://s3.zipio.com/photos/ s3://s3.zipiyo.com/photos --recursive