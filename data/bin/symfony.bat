@echo off

rem *********************************************************************
rem ** the symfony build script for Windows based systems (based on phing.bat)
rem ** $Id$
rem *********************************************************************

rem This script will do the following:
rem - check for PHP_COMMAND env, if found, use it.
rem   - if not found detect php, if found use it, otherwise err and terminate
rem - check for SYMFONY_HOME evn, if found use it
rem   - if not found error and leave
rem - check for PHP_CLASSPATH, if found use it
rem   - if not found set it using SYMFONY_HOME/lib

if "%OS%"=="Windows_NT" @setlocal

rem %~dp0 is expanded pathname of the current script under NT
set DEFAULT_SYMFONY_HOME=%~dp0..

goto init
goto cleanup

:init

if "%SYMFONY_HOME%" == "" set SYMFONY_HOME=%DEFAULT_SYMFONY_HOME%
set DEFAULT_SYMFONY_HOME=

if "%PHP_COMMAND%" == "" goto no_phpcommand
if "%PHP_CLASSPATH%" == "" goto set_classpath

goto run
goto cleanup

:run
IF EXIST "@PEAR-DIR@" (
  %PHP_COMMAND% -d html_errors=off -qC "@DATA-DIR@\bin\symfony.php" %1 %2 %3 %4 %5 %6 %7 %8 %9
) ELSE (
  %PHP_COMMAND% -d html_errors=off -qC "%SYMFONY_HOME%\data\bin\symfony.php" %1 %2 %3 %4 %5 %6 %7 %8 %9
)
goto cleanup

:no_phpcommand
REM echo ------------------------------------------------------------------------
REM echo WARNING: Set environment var PHP_COMMAND to the location of your php.exe
REM echo          executable (e.g. C:\PHP\php.exe).  (Assuming php.exe on Path)
REM echo ------------------------------------------------------------------------
set PHP_COMMAND=php.exe
goto init

:err_home
echo ERROR: Environment var SYMFONY_HOME not set. Please point this
echo variable to your local symfony installation!
goto cleanup

:set_classpath
set PHP_CLASSPATH=%SYMFONY_HOME%\lib
goto init

:cleanup
if "%OS%"=="Windows_NT" @endlocal
REM pause
