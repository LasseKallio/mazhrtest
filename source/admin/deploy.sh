#!/bin/bash
clear
grunt --force
OPTIONS="Testipenkki Live Exit"
select opt in $OPTIONS; do
if [ "$opt" = "Exit" ]; then
  echo Bye!
  exit
elif [ "$opt" = "Testipenkki" ]; then
  echo Copying files from dist/ to testipenkki.esmes.fi
  echo "Username (empty for current):"
  read username  
  TARGET=testipenkki.esmes.fi:/var/www/html/mzr/admin
  scp -r dist/* $username@$TARGET
  exit
elif [ "$opt" = "Live" ]; then
  echo Copying files from dist/ to mazhr.com
  echo "Username (empty for current):"
  read username  
  TARGET=mazhr.com:/var/www/html/admin
  scp -r dist/* $username@$TARGET
  exit
else
  echo Choose wisely!
fi
done
