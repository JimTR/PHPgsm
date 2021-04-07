#!/bin/bash
# Function to check if the PHP version is valid
#COLORS
# Reset
Color_Off='\033[0m'       # Text Reset

# Regular Colors
Red='\033[0;31m'          # Red
Green='\033[0;32m'        # Green
Yellow='\033[0;33m'       # Yellow
Purple='\033[0;35m'       # Purple
Cyan='\033[0;36m'         # Cyan
[ $# -eq 0 ] && { echo "Usage: $0 filename"; exit 1; }
echo $1;
# Update packages and Upgrade system
echo -e "$Cyan \n Updating System.. $Color_Off"
sudo apt-get update -y && sudo apt-get upgrade -y
echo -e
echo -e "$Purple Enabling 32 bit arch for steamcmd. $Color_Off"
sudo dpkg --add-architecture i386
echo -e "$Cyan \n Installing PHP & Requirements $Color_Off"
sudo apt-get install php-cli php-mbstring php-gmp php-mysql php-zip php-xml -y
echo -e "$Cyan \n Installing Steamcmd & Other Requirements $Color_Off"
sudo apt-get install steamcmd tmpreaper mlocate apt-show-versions libc-bin -y
echo -e "$Green If all went well we can now run the installer - ./install.php $Color_Off"
