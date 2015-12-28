#!/bin/bash
echo "------------ RUNNING STARTUP COMMANDS ------------"

#echo "...starting up databases"

### Import Database Versions
source `dirname $0`/_versions.sh

### Import Directories
source `dirname $0`/_directories.sh

#echo "...variables imported and available:"
#echo "...orient: ${ORIENT_VERSION}"
#echo "...build dir: ${BUILD_DIR}"
#echo "...ci dir: ${CI_DIR}"
#echo "...install dir: ${INSTALL_DIR}"

## start gremlin-server
cd "${INSTALL_DIR}/apache-gremlin-server-${GREMLINSERVER_VERSION}-incubating"
sudo bin/gremlin-server.sh conf/gremlin-server-spider.yaml > /dev/null 2>&1 &
cd ${BUILD_DIR}
sleep 30

## start neo4j
sudo ${INSTALL_DIR}/neo4j-community-${NEO4J_VERSION}/bin/neo4j start
sleep 15

# changing password:
sudo curl -vX POST http://neo4j:neo4j@localhost:7474/user/neo4j/password -d"password=j4oen"

## start orient to initially and properly set up the orientdb-server-config.xml file
sudo nohup ${INSTALL_DIR}/orientdb-community-${ORIENT_VERSION}/bin/orientdb.sh start

sleep 15

## stop orient
sudo nohup ${INSTALL_DIR}/orientdb-community-${ORIENT_VERSION}/bin/orientdb.sh stop

sleep 15

## set up the password for root
sed -i 's/password=".*" name="root"/password="root"  name="root"/' ${INSTALL_DIR}/orientdb-community-${ORIENT_VERSION}/config/orientdb-server-config.xml

## restart the orient server
sudo nohup ${INSTALL_DIR}/orientdb-community-${ORIENT_VERSION}/bin/orientdb.sh start

sleep 15

echo "------------ END: RUNNING STARTUP COMMANDS ------------"
