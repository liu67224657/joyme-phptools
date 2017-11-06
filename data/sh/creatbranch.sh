#!/bin/bash
if [ -z $3 ]; then
	echo "Missing parameters";
	exit;
fi

if [ "$1"x == "beta"x -o "$1"x == "prod"x ]; then
	SVN_URL=http://staffsvn.joyme.com/svn/php
else
	SVN_URL=http://svn.enjoyf.com/svn/php
fi
svn cp -m "create branch" $SVN_URL/$2/trunk $SVN_URL/$2/branches/$3
echo $2' create ['$3'] over';
