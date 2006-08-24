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

# symfony libraries

echo ">>> freeze symfony libraries"
svn export http://svn.symfony-project.com/${SVN_PATH}/lib/ lib/symfony/

echo ">>> freeze symfony data"
svn export http://svn.symfony-project.com/${SVN_PATH}/data/ data/symfony/
mv data/symfony/web/sf web/sf

# symfony command line

echo ">>> add symfony command line"
cp data/symfony/bin/symfony.sh symfony.sh
cp data/symfony/bin/symfony.bat symfony.bat
cp data/symfony/bin/symfony.php bin/symfony.php
sed -i '' -e "s#@DATA-DIR@#data#g" symfony.sh
sed -i '' -e "s#@DATA-DIR@#data#g" symfony.bat
sed -i '' -e "s#'@PEAR-DIR@'#dirname(__FILE__).'/../lib'#g" -e "s#'@DATA-DIR@'#dirname(__FILE__).'/../data'#g" -e "s#@SYMFONY-VERSION@#0.6.0#g" -e "s#require_once 'pake.php'#require_once dirname(__FILE__).'/pake.php'#g" bin/symfony.php
sed -i '' -e "s#'@PEAR-DIR@/symfony'#dirname(__FILE__)#g" -e "s#'@DATA-DIR@#dirname(__FILE__).'/../../data#g" -e "s#@SYMFONY-VERSION@#0.6.0#g" lib/symfony/pear.php
chmod 755 symfony.sh

# default project / app

echo ">>> create a new project and a new app"
./symfony.sh init-project ${SANDBOX_NAME}
./symfony.sh init-app ${APP_NAME}

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
