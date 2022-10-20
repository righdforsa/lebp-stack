#!/bin/bash
#Version=1.9

##### Variable Decleration #####
repo_dir="/srv/project/lebp-stack"
repo_dir2="/srv/project/configs_overlay"

mydir=$(pwd)

##### Function for composer #####
install_composer()
{
local repodir="${1}"
echo "";
echo "Processing Directory: ${repodir}";

if [ -d ${repodir} ]; then
	if [ "${repodir}" != "${mydir}" ]; then
		cd ${repo_dir}
		echo " Changing directory ${repodir}";
	fi
else
	echo "";
	echo "	${repo_dir} Does Not exists...";
	echo "";
	return 1;
fi

composer_path=$(find ${repodir}/ -name composer.json)
if [ -z "${composer_path}" ]; then
	echo "";
	echo " No composer.json file in ${repodir}";
	echo "";
	break;
fi

for path in $(echo "${composer_path}"); do
	echo "${path}" | grep "vendor" >> /dev/null 2>&1;
	if [ $? -eq 0 ]; then
		continue
	fi
	
	composer_dir_loc=$(echo "${path}" | sed "s/composer.json//g")
	echo "";
	echo " composer dir loc is: ${composer_dir_loc}";

	cd ${composer_dir_loc}
	echo "		Installing Latest php modules";
	echo "";

	curl -sS https://getcomposer.org/installer | php
	if [ $? -eq 0 ]; then
		echo "	Testing Latest php modules";
		echo "";

		php composer.phar install
		if [ $? -eq 0 ]; then
			echo "";
			echo "		Installation Successfull";
		else
			echo "";
			echo "		Failed to Install Latest Modules";
		fi
	else
		echo "";
		echo "		Failed to Download Latest Modules";
	fi
done
}

##### Checking if php installed #####
php -v >> /dev/null 2>&1
if [ $? -ne 0 ]; then
	echo "";
	echo "	php command does not exists, Aborting execution of this script. ";
	echo "";
	exit 1;
fi

##### Checking on dir lebp-stack ##### 
install_composer "${repo_dir}"  ## Function Call
##### Checking on dir configs_overlay ##### 
install_composer "${repo_dir2}"  ## Function Call

