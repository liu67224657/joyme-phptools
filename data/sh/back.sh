#!/bin/bash
a="$1"
b="$2"
c="$3"
dirpath=$(cd "$(dirname "$0")"; pwd)
CODE_ROOT_DIR=/opt/phpcode
CODE_DIR=$CODE_ROOT_DIR/"$b"package/"$c"."$a"
WWW_ROOT_DIR=/opt/www
WWW_LNK=$WWW_ROOT_DIR/$b/$a

###check input###

if [ "$a"x != "beta"x -a "$a"x != "prod"x -a "$a"x != "alpha"x -a "$a"x != "test"x ]; then
    echo "path is beta|prod|alpha|test"
    exit;
fi
if [ -z $c ]; then
        echo "path project version"
        exit;
fi

if [ "$a"x == "beta"x -o "$a"x == "prod"x ]; then
        SVN_URL=http://staffsvn.joyme.com/svn/php
else
        SVN_URL=http://svn.enjoyf.com/svn/php
fi

echo "back $a $b $c <br>"

###check version###

if [ ! -d $CODE_DIR ]; then
 echo "no this version"
 exit;
fi

###back link##
rm -f $WWW_LNK
ln -s $CODE_DIR $WWW_LNK

###back permission###
chown webops:operators -R $CODE_ROOT_DIR/"$b"package/
chown webops:operators $WWW_LNK

###rsync to other webhosts###

function rsync_link {
cd $dirpath
HOSTS_FILE=webhosts.list
for i in `cat $HOSTS_FILE`; do
    echo "begin rsync back link "$i"<br>"
    rsync -avlzqpP  $WWW_ROOT_DIR/$b  webops@$i:$WWW_ROOT_DIR"/"
    echo "`date '+%Y-%m-%d_%H:%M:%S'`_"back version ""$a"_"$b"_to"$c"_"$i""  >> /opt/servicelogs/backlog/release/release.log
done
}

echo "try rsync back<br>"
rsync_link
echo "back version done<br>"
