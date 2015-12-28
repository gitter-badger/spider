#!/bin/bash
## Import Versions, Directories, and Setup Dependencies
source `dirname $0`/_bootstrap.sh

### Install Java and the Databases
source ${CI_DIR}/install-databases.sh

### Startup all databases
source ${CI_DIR}/start-databases.sh