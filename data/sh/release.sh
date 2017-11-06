#!/bin/bash
SVN_ROOT_DIR=/opt/svndata/php
WWW_ROOT_DIR=/opt/www
CODE_ROOT_DIR=/opt/phpcode
dirpath=$(cd "$(dirname "$0")"; pwd)
a="$1"
b="$2"
c="$3"
###count###
echo '0:release start' > $dirpath/status
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
	SVN_URL=http://staffsvn.joyme.com/svn/php
else
	SVN_URL=http://svn.enjoyf.com/svn/php
fi

echo "0:release $a $b $c" >> $dirpath/status

#svn check out  & u1p
# 这里的-d 参数判断$myPath是否存在 
SVN_DIR=$SVN_ROOT_DIR/$b
WWW_LNK=$WWW_ROOT_DIR/$b/$a
CODE_DIR=$CODE_ROOT_DIR/"$b"package/"$c"."$a"
CODE_DIR_PACKAGE=$CODE_ROOT_DIR/"$b"package

if [ ! -d $CODE_DIR_PACKAGE ]; then
 mkdir -p $CODE_DIR_PACKAGE
fi

if [ ! -d $WWW_ROOT_DIR/$b ]; then
 mkdir $WWW_ROOT_DIR/$b
fi

if [ ! -d $SVN_DIR ]; then
 mkdir -p $SVN_DIR
fi

cd $SVN_DIR/
rm -rf branch
svn co -q $SVN_URL/$b/branches/$c  branch

cd $SVN_DIR/branch
SVN_V=`svn info|grep "URL" | awk '{print $2}'`
if [ "$SVN_URL/$b/branches/$c" != "$SVN_V" ] ;then
   echo "1:svn no this version" >> $dirpath/status
   exit;
fi
	
chown webops:operators -R $SVN_ROOT_DIR
cd ../
#copy code to code dir
if [ -d $CODE_DIR ]; then 
  echo "1:can not release again !try remove $CODE_DIR" >> $dirpath/status
  exit;
fi
cp -r branch $CODE_ROOT_DIR/"$b"package/"$c"."$a"
#clear svn dir
find $CODE_ROOT_DIR/"$b"package/"$c"."$a" -name "*.svn" | xargs rm -rf {}
#mk link
rm -f $WWW_LNK
ln -s $CODE_DIR $WWW_LNK
#change permission
chown webops:operators -R $CODE_ROOT_DIR/"$b"package/
chown webops:operators $WWW_LNK

#rsync to other webhosts
USER=`whoami`

function rsync_hosts {
cd $dirpath
HOSTS_FILE=webhosts.list
for i in `cat $HOSTS_FILE`; do
    echo "0:rsync start "$i >> $dirpath/status
    rsync -avlzqpP  $CODE_DIR  webops@$i:$CODE_ROOT_DIR/"$b"package
    rsync -avlzqpP  $WWW_ROOT_DIR/$b  webops@$i:$WWW_ROOT_DIR"/"
    echo "0:"$i"rsync over" >> $dirpath/status
    echo "`date '+%Y-%m-%d_%H:%M:%S'`_"$a"_"$b"_"$c"_"$i""  >> /opt/servicelogs/backlog/release/release.log
done
}

echo "0:try rsync_hosts" >> $dirpath/status
rsync_hosts

#add tags
function add_tags {
echo "0:add svn tags start" >> $dirpath/status
svn del $SVN_URL/$b/tags/$c -m 'try del'
svn cp  $SVN_URL/$b/branches/$c  $SVN_URL/$b/tags/$c -m 'new version $c'
# check tags 
SVN_TAG=$SVN_DIR/tag
if [ -d $SVN_TAG ]; then
  rm -rf $SVN_TAG
fi
cd $SVN_DIR
svn co -q $SVN_URL/$b/tags/$c  tag
cd ../
chown webops:operators -R $SVN_DIR
echo "0:add svn tags over " >> $dirpath/status
}

if [ "$a" == "prod" ]; then
#add_tags
add_tags
fi

echo '1:release over' >> $dirpath/status