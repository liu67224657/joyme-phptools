#!/bin/bash
a="$1"
b="$2"
dirpath=$(cd "$(dirname "$0")"; pwd)
CODE_ROOT_DIR=/opt/svndata/php
CODE_DIR=$CODE_ROOT_DIR/"$b"

###check input###

if [ "$a"x != "beta"x -a "$a"x != "prod"x -a "$a"x != "alpha"x -a "$a"x != "dev"x ]; then
    echo "path is beta|prod|alpha|dev"
    exit;
fi
if [ -z $b ]; then
        echo "path project"
        exit;
fi

echo "update $a $b <br>"

###check version###

if [ ! -d $CODE_DIR ]; then
 echo "no this project"
 exit;
fi

###rsync to other webhosts###

function rsync_code {
	HOSTS_FILE=$dirpath/new_webhosts/"$a".list
	cd $CODE_ROOT_DIR
	for i in `cat $HOSTS_FILE`; do
	    echo "rsync code "$i"<br>"
	    rsync -ogrltDRvzqP --password-file=/etc/rsync.password --exclude=".svn"  --exclude="$b/$a/cache" "$b"/"$a" codeuser@$i::code
	done
}

echo "try rsync code<br>"
rsync_code
echo "rsync code done<br>"
