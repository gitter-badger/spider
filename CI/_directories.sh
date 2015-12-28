#!/usr/bin/env bash
### Working and CI Directories
export BUILD_DIR=$(pwd)
export CI_DIR=`dirname $0` #fix this

### Database Directories
export INSTALL_DIR="${HOME}/spider-databases"
mkdir -p ${INSTALL_DIR}

### Vagrant Directories
#VAGRANT_DIR="/vagrant"
