#!/bin/bash
SVN_ROOT_DIR=/opt/svndata/php
PATCH_ROOT_DIR=/opt/phppatch
CODE_ROOT_DIR=/opt/phpcode
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

echo "0:patch $a $b $c" >> $dirpath/status

PATCH_DIR=$PATCH_ROOT_DIR"/"$b"/"$c

echo "0:patch dir is " $PATCH_DIR >> $dirpath/status
#补丁只能增加/修改文件 不能删除文件

currentp=`ls -l $CODE_ROOT_DIR/"$b"package/current | awk '{print $NF}'|awk -F"/" '{print $NF}' |awk -F"." '{print $1"."$2"."$3"."$4}'`
currentp="$b"package/$currentp"."$a

CODE_DIR=$CODE_ROOT_DIR/$currentp

if [ "$a" == "beta" -o "$a" == "test" ]; then
	echo "0:add patch to svn branch" >> $dirpath/status
	SVN_DIR=$SVN_ROOT_DIR"/"$b"/"branch
else
	echo "0:add patch to svn tag" >> $dirpath/status
	SVN_DIR=$SVN_ROOT_DIR"/"$b"/"tag
fi
sudo \cp -rfp $PATCH_DIR"/"* $CODE_DIR"/"

#change permission
sudo chown root. -R $currentp

currentp=`ls -l $CODE_ROOT_DIR/"$b"package/current | awk '{print $NF}'|awk -F"/" '{print $NF}' |awk -F"." '{print $1"."$2"."$3"."$4}'`
currentp="$b"package/$currentp"."$a

HOSTS_FILE=$dirpath/new_webhosts/"$a".list
for i in `cat $HOSTS_FILE`; do
	cd $CODE_ROOT_DIR
    echo "0:begin rsync "$i >> $dirpath/status
    rsync -ogrltDRvzqP --password-file=/etc/rsync.password $currentp  codeuser@$i::code
    echo "0:"$i"rsync over" >> $dirpath/status
done

sudo \cp -rfpv $PATCH_DIR/* $SVN_DIR"/"
[ $? -eq 0 ]||{
	echo -e "1:Copy error" >> $dirpath/status
	exit;
}
TMP=/tmp/patch_tmp.$$
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
#chown webops:operators -R $SVN_ROOT_DIR
rm -f $TMP

echo '1:patch over' >> $dirpath/status
exit;


