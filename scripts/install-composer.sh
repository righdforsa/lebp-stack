#!/bin/bash

##### Variable Decleration #####
repo_dir="/srv/project/lebp-stack"
repo_dir2="/srv/project/configs_overlay"

mydir=$(pwd)

##### Function for composer #####
install_composer()
{
    local repodir="${1}"
    echo "";
    echo "$0: Processing Directory: ${repodir}";

    if [ -d ${repodir} ]; then
        if [ "${repodir}" != "${mydir}" ]; then
            cd ${repodir}
            echo "$0:  Changing directory ${repodir}";
        fi
    else
        echo "";
        echo "$0:     ${repodir} Does Not exists...";
        echo "";
        return 1;
    fi

    composer_path=$(find ${repodir}/ -name composer.json)
    if [ -z "${composer_path}" ]; then
        echo "";
        echo "$0:  No composer.json file in ${repodir}";
        echo "";
        break;
    fi

    for path in $(echo "${composer_path}"); do
        if echo "${path}" | grep -q "vendor"; then
            continue
        fi

        composer_dir_loc=$(echo "${path}" | sed "s/composer.json//g")
        echo "";
        echo "$0:  composer dir loc is: ${composer_dir_loc}";
	if echo "${composer_dir_loc}" | grep -q "Bedrock-PHP"; then
            verb="update"
        else
            verb="install"
        fi

        cd ${composer_dir_loc}
        echo "$0:         Installing Latest php modules";
        echo "";

        curl -sS https://getcomposer.org/installer | php
        if [ $? -eq 0 ]; then
            echo "$0:     Running composer to get php libarary modules";
            echo "";

            php composer.phar $verb
            if [ $? -eq 0 ]; then
                echo "";
                echo "$0:         Installation Successfull";
            else
                echo "";
                echo "$0:         Failed to Install Latest Modules";
            fi
        else
            echo "";
            echo "$0:         Failed to Download Latest Modules";
        fi
    done
}

##### Checking if php installed #####
php -v >> /dev/null 2>&1
if [ $? -ne 0 ]; then
    echo "";
    echo "$0:     php command does not exists, Aborting execution of this script. ";
    echo "";
    exit 1;
fi

##### Checking on dir lebp-stack ##### 
install_composer "${repo_dir}"  ## Function Call
##### Checking on dir configs_overlay ##### 
install_composer "${repo_dir2}"  ## Function Call

