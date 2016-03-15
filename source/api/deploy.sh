#!/bin/bash
TARGET=/var/www/mzr_backend
echo Copying to $TARGET
echo "controllers/*"
cp app/controllers/* $TARGET/app/controllers/
echo "app/*.php"
cp app/*.php $TARGET/app/
echo "database/*"
cp -r app/database/* $TARGET/app/database/
echo "commands/*"
cp -r app/commands/* $TARGET/app/commands/
echo "config/*"
cp -r app/config/* $TARGET/app/config/
echo "models/*"
cp -r app/models/* $TARGET/app/models/
echo "lang/*"
cp -r app/lang/* $TARGET/app/lang/
echo "start/*"
cp -r app/start/* $TARGET/app/start/
echo "views/*"
cp -r app/views/* $TARGET/app/views/
echo "library/*"
cp -r app/library/* $TARGET/app/library/
echo "All done!"
