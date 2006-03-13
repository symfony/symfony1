#!/bin/sh

# create a quickstart package

# initialization

SANDBOX_NAME=sf_sandbox
APP_NAME=frontend
SVN_PATH=trunk

echo ">>> project initialization"
mkdir ${SANDBOX_NAME}
cd ${SANDBOX_NAME}
mkdir lib bin data web

# pake

echo ">>> add pake package"
mkdir tmp
cd tmp
curl http://www.pake-project.org/downloads/pake-current.tgz > pake.tgz
tar zxpf pake.tgz
mv pake-`awk '/<release>/ {print $1}' package.xml | sed 's#<release>##' | sed 's#</release>##' | head -n 1`/lib/pake ../lib/pake
mv pake-`awk '/<release>/ {print $1}' package.xml | sed 's#<release>##' | sed 's#</release>##' | head -n 1`/bin/pake.php ../bin/
cd ..
rm -rf tmp

# symfony libraries

echo ">>> freeze symfony libraries"
svn export http://svn.symfony-project.com/${SVN_PATH}/lib/ lib/symfony/

echo ">>> freeze symfony data"
svn export http://svn.symfony-project.com/${SVN_PATH}/data/ data/symfony/
mv data/symfony/web/sf web/sf

# symfony command line

echo ">>> add symfony command line"
mkdir tmp
svn export http://svn.symfony-project.com/${SVN_PATH}/bin/ tmp/bin/
mv tmp/bin/symfony.sh symfony.sh
mv tmp/bin/symfony.bat symfony.bat
mv tmp/bin/symfony.php bin/symfony.php
rm -rf tmp
sed -i '' -e "s#@PEAR-DIR@#bin#g" symfony.sh
sed -i '' -e "s#@PEAR-DIR@#bin#g" symfony.bat
sed -i '' -e "s#'@PEAR-DIR@'#dirname(__FILE__).'/../lib'#g" -e "s#'@DATA-DIR@'#dirname(__FILE__).'/../data'#g" -e "s#@SYMFONY-VERSION@#0.6.0#g" -e "s#require_once 'pake.php'#require_once dirname(__FILE__).'/pake.php'#g" bin/symfony.php
sed -i '' -e "s#'@PEAR-DIR@/symfony'#dirname(__FILE__)#g" -e "s#'@DATA-DIR@#dirname(__FILE__).'/../../data#g" -e "s#@SYMFONY-VERSION@#0.6.0#g" lib/symfony/pear.php
chmod 755 symfony.sh

# default project / app

echo ">>> create a new project and a new app"
./symfony.sh init-project ${SANDBOX_NAME}
./symfony.sh init-app ${APP_NAME}

# phing

echo ">>> add phing package"
mkdir tmp
cd tmp
curl http://phing.info/pear/phing-current.tgz > phing.tgz
tar zxpf phing.tgz
mv phing-`awk '/<version>/ {print $1}' package.xml | sed 's#<version>##' | sed 's#</version>##' | head -n 1` ../lib/phing
cd ..
rm -rf tmp

# schema.xml

echo ">>> rename schema.xml"
mv config/schema.xml.sample config/schema.xml

# license

echo ">>> add LICENSE"
svn export http://svn.symfony-project.com/${SVN_PATH}/LICENSE LICENSE

# readme

echo ">>> add README"
svn export http://svn.symfony-project.com/${SVN_PATH}/doc/SANDBOX_README README

# default: sqlite db

echo ">>> default to sqlite"
sed -i '' -e "s#\(propel.database *= *\)mysql#\1sqlite#" config/propel.ini
sed -i '' -e "s#\(propel.database.createUrl *= *\).*#\1sqlite://./../../../../data/sandbox.db#" config/propel.ini
sed -i '' -e "s#\(propel.database.url *= *\).*#\1sqlite://./../../../../data/sandbox.db#" config/propel.ini

sed -i '' -e "s#\( *dsn *: *\).*#\1sqlite://./../data/sandbox.db#" config/databases.yml

sed -i '' -e "s/^#//g" config/databases.yml

# add some empty files in empty directories
touch apps/${APP_NAME}/modules/.sf apps/${APP_NAME}/i18n/.sf test/${APP_NAME}/.sf doc/.sf web/images/.sf
touch log/.sf cache/.sf batch/.sf data/sql/.sf data/model/.sf data/plugins/.sf
touch data/symfony/generator/sfPropelAdmin/default/skeleton/templates/.sf
touch data/symfony/generator/sfPropelAdmin/default/skeleton/validate/.sf
touch data/symfony/modules/default/config/.sf data/symfony/skeleton/project/build/.sf
touch lib/model/.sf lib/plugins/.sf web/js/.sf

# create archive

cd ..
tar zcpf ${SANDBOX_NAME}.tgz ${SANDBOX_NAME}

# cleanup

rm -rf ${SANDBOX_NAME}
