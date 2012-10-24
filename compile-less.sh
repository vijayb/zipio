#!/bin/sh

rm -rf www/lib/bootstrap.css
/home/sanjay/cloudhead-less.js-f8bee84/bin/lessc www/bootstrap/less/bootstrap.less > www/lib/bootstrap.css

rm -rf www/lib/bootstrap-responsive.css
/home/sanjay/cloudhead-less.js-f8bee84/bin/lessc www/bootstrap/less/responsive.less > www/lib/bootstrap-responsive.css
