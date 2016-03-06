#!/bin/bash

# first run sleep 55
flfilename="/tmp/ImsJobsFirst.lock"
touch $flfilename
ft=$(cat $flfilename)
if [ -z $ft ]; then
    ft=0
fi
now=$(date '+%s')
tdiff=$[$now - $ft]
if [ $tdiff -gt 66 ]; then
    date '+%s' > $flfilename
    sleep 55
fi

for (( c=1; c<=100; c++ ))
do
{
    sleep 0.234567
    # 
    if [ $c -lt 10 ]; then
        cs="00$c"
    elif [ $c -lt 100 ]; then
        cs="0$c"
    else
        cs="$c"
    fi
    #
    lfilename="/tmp/ImsJobs$cs.lock"
    touch $lfilename
    t=$(cat $lfilename)
    if [ -z $t ]; then
        t=0
    fi
    # 
    now=$(date '+%s')
    tdiff=$[$now - $t]
    # 
    if [ $c -le 40 ]; then
        jg=$[60-20]
    elif [ $c -le 50 ]; then
        jg=$[60*5-20]
    elif [ $c -le 60 ]; then
        jg=$[60*10-20]
    elif [ $c -le 70 ]; then
        jg=$[60*60-20]
    elif [ $c -le 80 ]; then
        jg=$[60*60*24-20]
    elif [ $c -le 90 ]; then
        jg=$[60*60*24*7-20]
    else
        jg=$[60*60*24*30-20]
    fi
    # 
    if [ $tdiff -gt $jg ]; then
        date '+%s' > $lfilename
        /usr/bin/php /var/wwwroot/ims/yiicmd.php crontab/Runing/ImsJobs$cs
    fi
}&
done
exit 0
