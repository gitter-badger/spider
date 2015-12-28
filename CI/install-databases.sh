#!/usr/bin/env bash
#echo "...installing databases"

# Install Java JDK8
. /vagrant/CI/jdk8/install.sh

### Java Directories
export JAVA_HOME=/usr/lib/jvm/java-8-oracle
export JRE_HOME=/usr/lib/jvm/java-8-oracle

# Install Gremlin Server
#. ${CI_DIR}/gremlin-server/install.sh

# Install Neo4j
#. ${CI_DIR}/neo4j/install.sh

# Install OrientDB
#. ${CI_DIR}/orient/install.sh
