#!/bin/sh

# creates a symfony sandbox for this symfony version

echo ">>> sandbox initialization"
DIR=`pwd`/`dirname $0`
SANDBOX_NAME=sf_sandbox
APP_NAME=frontend
PHP=php

rm -rf /tmp/${SANDBOX_NAME}
mkdir /tmp/${SANDBOX_NAME}
cd /tmp/${SANDBOX_NAME}

echo ">>> embed symfony"
mkdir -p lib/vendor/symfony
cp -R ${DIR}/../../* lib/vendor/symfony

echo ">>> create a new project and a new app"
${PHP} lib/vendor/symfony/data/bin/symfony generate:project ${SANDBOX_NAME}
${PHP} symfony generate:app ${APP_NAME}

echo ">>> add LICENSE"
cp ${DIR}/../../LICENSE LICENSE

echo ">>> add README"
cp ${DIR}/SANDBOX_README README

echo ">>> add symfony command line for windows users"
cp ${DIR}/symfony.bat symfony.bat

echo ">>> default to sqlite"
${PHP} symfony configure:database "sqlite:%SF_DATA_DIR%/sandbox.db"

echo ">>> fix sqlite database permissions"
touch data/sandbox.db
chmod 777 data
chmod 777 data/sandbox.db

echo ">>> add some empty files in empty directories"
touch apps/${APP_NAME}/modules/.sf apps/${APP_NAME}/i18n/.sf
touch cache/.sf doc/.sf log/.sf plugins/.sf
touch test/unit/.sf test/functional/.sf test/functional/${APP_NAME}/.sf
touch web/images/.sf web/js/.sf web/uploads/assets/.sf

echo ">>> create archives"
cd ..
tar --exclude=".svn" -zcpf ${DIR}/../../${SANDBOX_NAME}.tgz ${SANDBOX_NAME}
zip -rq ${DIR}/../../${SANDBOX_NAME}.zip ${SANDBOX_NAME} -x \*/\*.svn/\*

echo ">>> cleanup"
rm -rf ${SANDBOX_NAME}
