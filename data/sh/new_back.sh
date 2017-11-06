#!/bin/bash
a="$1"
b="$2"
c="$3"
dirpath=$(cd "$(dirname "$0")"; pwd)
CODE_ROOT_DIR=/opt/phpcode
CODE_DIR=$CODE_ROOT_DIR/"$b"package/"$c"."$a"
WWW_LNK=$CODE_ROOT_DIR/"$b"package/current

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
sudo chown root. -R $WWW_LNK

###rsync to other webhosts###

function rsync_link {
HOSTS_FILE=$dirpath/new_webhosts/"$a".list
cd $CODE_ROOT_DIR
for i in `cat $HOSTS_FILE`; do
    echo "begin rsync back link "$i"<br>"
    rsync -ogrltDRvzqP --password-file=/etc/rsync.password "$b"package/current  codeuser@$i::code 
done
}

echo "try rsync back<br>"
rsync_link
echo "back version done<br>"
