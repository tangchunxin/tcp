#!/bin/sh
#
EOS_HOME=/data/www/tcp/game_s_tcp
GREP='0.0.0.0:100'
DEL_DAY=30

#备份
LOG_BAK_HOME=$EOS_HOME/log
mkdir -p $LOG_BAK_HOME
cd $SAS_LOG_HOME
TODAY=`date +%Y%m%d`

DEL_DAY_1=`date -d "$DEL_DAY day ago" +%Y%m%d`
DEL_DAY_2=`date -d "$[$DEL_DAY+1] day ago" +%Y%m%d`
DEL_DAY_3=`date -d "$[$DEL_DAY+2] day ago" +%Y%m%d`

cp $LOG_BAK_HOME/swoole.log $LOG_BAK_HOME/$TODAY.log
true > $LOG_BAK_HOME/swoole.log

# /bin/rm -rf $LOG_BAK_HOME/$DEL_DAY_1.log $LOG_BAK_HOME/$DEL_DAY_2.log $LOG_BAK_HOME/$DEL_DAY_3.log
/bin/find $LOG_BAK_HOME -mtime +7 -name "*.log" -exec rm -rf {} \;

sleep 0.2

cd $EOS_HOME
mkdir -p $EOS_HOME/../$TODAY.back
cp -R $EOS_HOME $EOS_HOME/../$TODAY.back/

/bin/rm -rf $EOS_HOME/../$DEL_DAY_1.back $EOS_HOME/../$DEL_DAY_2.back $EOS_HOME/../$DEL_DAY_3.back

sleep 0.2

svn up $EOS_HOME --username readuser --password passreaduser --no-auth-cache
sleep 0.2

#重启
cd $EOS_HOME
kill $(netstat -lnp | grep $GREP | awk '{print $7}'| awk -F/ '{print $1}')
sleep 2
php71 server.php
