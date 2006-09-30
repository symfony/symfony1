#!/bin/sh

# creates a symfony sandbox for this symfony version

echo ">>> initialization"
DIR=../`dirname $0`
SANDBOX_NAME=sf_sandbox
APP_NAME=frontend
PHP=php

echo ">>> project initialization"
rm -rf ${SANDBOX_NAME}
mkdir ${SANDBOX_NAME}
cd ${SANDBOX_NAME}

echo ">>> create a new project and a new app"
php ${DIR}/symfony.php init-project ${SANDBOX_NAME}
php ${DIR}/symfony.php init-app ${APP_NAME}

echo ">>> add symfony command line"
cp ${DIR}/symfony.sh symfony.sh
cp ${DIR}/symfony.bat symfony.bat
cp ${DIR}/symfony.php symfony.php
chmod 755 symfony.sh

echo ">>> add LICENSE"
cp ${DIR}/../../LICENSE LICENSE

echo ">>> add README"
cp ${DIR}/../../doc/SANDBOX_README README

echo ">>> freeze symfony"
php ${DIR}/symfony.php freeze

echo ">>> default to sqlite"
sed -i '' -e "s#\(propel.database *= *\)mysql#\1sqlite#" config/propel.ini
sed -i '' -e "s#\(propel.database.createUrl *= *\).*#\1sqlite://./../../../../data/sandbox.db#" config/propel.ini
sed -i '' -e "s#\(propel.database.url *= *\).*#\1sqlite://./../../../../data/sandbox.db#" config/propel.ini
sed -i '' -e "s#\( *dsn *: *\).*#\1sqlite://./../data/sandbox.db#" config/databases.yml
sed -i '' -e "s/^#//g" config/databases.yml

echo ">>> add some empty files in empty directories"
touch apps/${APP_NAME}/modules/.sf apps/${APP_NAME}/i18n/.sf test/${APP_NAME}/.sf doc/.sf web/images/.sf
touch log/.sf cache/.sf batch/.sf data/sql/.sf data/model/.sf
touch data/symfony/generator/sfPropelAdmin/default/skeleton/templates/.sf
touch data/symfony/generator/sfPropelAdmin/default/skeleton/validate/.sf
touch data/symfony/modules/default/config/.sf
touch lib/model/.sf plugins/.sf web/js/.sf

echo ">>> create archive"
cd ..
tar zcpf ${SANDBOX_NAME}.tgz ${SANDBOX_NAME}

echo ">>> cleanup"
rm -rf ${SANDBOX_NAME}
