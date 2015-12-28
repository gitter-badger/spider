#!/usr/bin/env bash
### Import Database Versions
. `dirname $0`/_versions.sh

### Import Directories
. `dirname $0`/_directories.sh

### Setup Dependencies for Adding Repos
sudo apt-get update #> /dev/null
apt-get install software-properties-common python-software-properties -y #> /dev/null
echo "...installing apt-get stuff..."