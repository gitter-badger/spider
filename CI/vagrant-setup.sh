#!/usr/bin/env bash
### Import Versions, Directories, and Setup Dependencies
. `dirname $0`/_bootstrap.sh

echo "...Now the following variables should be available"
echo "...neo: ${NEO4J_VERSION}"
echo "...build dir: ${BUILD_DIR}"
echo "...ci dir: ${CI_DIR}"
echo "...install dir: ${INSTALL_DIR}"

echo "...installing php"

# Install and Configure PHP and Tools
echo "---- Installing PHP and Extensions ----"
echo "-- Updating PHP repository--"
sudo apt-get update #> /dev/null
add-apt-repository ppa:ondrej/php5-5.6 -y #> /dev/null
apt-get update #> /dev/null

echo "-- Installing PHP --"
apt-get install php5 -y #> /dev/null

echo "-- Installing PHP extensions --"
sudo apt-get install curl php5-curl php5-gd php5-mcrypt -y #> /dev/null
sudo apt-get install php5-xdebug #> /dev/null

echo "-- Installing Composer and PHPUnit --"
curl --silent https://getcomposer.org/installer | php #> /dev/null 2>&1
mv composer.phar /usr/local/bin/composer
alias phpunit=/vagrant/vendor/bin/phpunit

### Install Java and the Databases
#. `dirname $0`/install-databases.sh

echo "...installing databases"

# Install Java JDK8
. `dirname $0`/jdk8/install.sh

### Java Directories
export JAVA_HOME=/usr/lib/jvm/java-8-oracle
export JRE_HOME=/usr/lib/jvm/java-8-oracle

# Install Gremlin Server
#. ${CI_DIR}/gremlin-server/install.sh
. `dirname $0`/gremlin-server/install.sh

# Install Neo4j
#. ${CI_DIR}/neo4j/install.sh
. `dirname $0`/neo4j/install.sh

# Install OrientDB
#. ${CI_DIR}/orient/install.sh
#. `dirname $0`/orient/install.sh

echo "...done with bootstrap"

