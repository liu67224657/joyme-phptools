#!/bin/bash
SVN_ROOT_DIR=/opt/svndata/php
PATCH_ROOT_DIR=/opt/phppatch
CODE_ROOT_DIR=/opt/phpcode
WWW_ROOT_DIR=/opt/www
dirpath=$(cd "$(dirname "$0")"; pwd)
a="$1"
b="$2"
c="$3"
####count####

echo "0:patch start" > $dirpath/status

#check input
if [ "$a"x != "beta"x -a "$a"x != "prod"x -a "$a"x != "alpha"x -a "$a"x != "test"x ]; then
    echo "1:path is beta|prod|alpha|test" >> $dirpath/status
    exit;
fi
if [ -z $c ]; then
	echo "1:path project version" >> $dirpath/status
	exit;
fi

if [ "$a"x == "beta"x -o "$a"x == "prod"x ]; then
	SVN_URL=http://staffsvn.joyme.com/svn/php/phppatch
else
	SVN_URL=http://svn.enjoyf.com/svn/php/phppatch
fi

#update phppatch
cd $PATCH_ROOT_DIR"/"$b
rm -rf $c
svn up
unzip -o "$c".zip -d "$c"
echo '0:svn up for phppatch' >> $dirpath/status

echo "0:phtch $a $b $c" >> $dirpath/status

PATCH_LOG=/opt/servicelogs/backlog/release/patch.log
PATCH_DIR=$PATCH_ROOT_DIR"/"$b"/"$c
echo "0:patch dir is " $PATCH_DIR >> $dirpath/status
#补丁只能增加/修改文件 不能删除文件
CODE_DIR=$WWW_ROOT_DIR"/"$b"/"$a"/"
TMP=/tmp/patch_tmp.$$
if [ "$a" == "beta" -o "$a" == "test" ]; then
        echo "0:add patch to svn branch" >> $dirpath/status
SVN_DIR=$SVN_ROOT_DIR"/"$b"/"branch
else
        echo "0:add patch to svn tag" >> $dirpath/status
SVN_DIR=$SVN_ROOT_DIR"/"$b"/"tag
fi
\cp -rfp $PATCH_DIR"/"* $CODE_DIR"/"
chown webops:operators -R $CODE_DIR
HOSTS_FILE=webhosts.list
cd $dirpath
for i in `cat webhosts.list`; do
    echo "0:begin rsync "$i >> $dirpath/status
    rsync -alrvzqp  $PATCH_DIR"/"  webops@$i:$CODE_DIR"/"
    echo "0:"$i"rsync over" >> $dirpath/status
    echo `date '+%Y-%m-%d_%H:%M:%S'`_"$a"_"$b"_"$c"_"$i"  >> /opt/servicelogs/backlog/release/release.log
done

echo `date '+%Y-%m-%d_%H:%M:%S'`_"$a"_"$b"_"$c"  >> $PATCH_LOG
\cp -rfpv $PATCH_DIR/* $SVN_DIR"/" >> $PATCH_LOG
[ $? -eq 0 ]||{
	echo -e "1:Copy error" >> $dirpath/status
	exit;
}
echo "-------------" >> $PATCH_LOG
cd $SVN_DIR
svn st > $TMP
while read line
do
STS=`echo $line | awk '{print $1}'`
FILENAME=`echo $line|awk '{print $2}'`
if [ "$STS" == "?" ]; then
echo "0:add svn file " $FILENAME >> $dirpath/status
svn add -q  $FILENAME
fi
done < $TMP
svn ci -m "patch $c"
chown webops:operators -R $SVN_ROOT_DIR
rm -f $TMP

echo '1:patch over' >> $dirpath/status
exit;


