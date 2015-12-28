#!/usr/bin/env bash
echo "...installing gremlin version ${GREMLINSERVER_VERSION} to ${INSTALL_DIR}"
echo "...copying file ${CI_DIR}/gremlin-server/gremlin-spider-script.groovy to ${INSTALL_DIR}/apache-gremlin-server-${GREMLINSERVER_VERSION}-incubating/scripts/"
echo "...Java Dir: ${JAVA_HOME}"

## Install gremlin-server
wget --no-check-certificate -O ${INSTALL_DIR}/apache-gremlin-server-${GREMLINSERVER_VERSION}-incubating-bin.zip https://www.apache.org/dist/incubator/tinkerpop/${GREMLINSERVER_VERSION}-incubating/apache-gremlin-server-${GREMLINSERVER_VERSION}-incubating-bin.zip
unzip ${INSTALL_DIR}/apache-gremlin-server-${GREMLINSERVER_VERSION}-incubating-bin.zip -d ${INSTALL_DIR}/

# get gremlin-server configuration files
cp ${CI_DIR}/gremlin-server/gremlin-spider-script.groovy ${INSTALL_DIR}/apache-gremlin-server-${GREMLINSERVER_VERSION}-incubating/scripts/
cp ${CI_DIR}/gremlin-server/gremlin-server-spider.yaml ${INSTALL_DIR}/apache-gremlin-server-${GREMLINSERVER_VERSION}-incubating/conf/

# get neo4j dependencies
cd ${INSTALL_DIR}/apache-gremlin-server-${GREMLINSERVER_VERSION}-incubating
bin/gremlin-server.sh -i org.apache.tinkerpop neo4j-gremlin ${GREMLINSERVER_VERSION}-incubating
sleep 30
cd ${BUILD_DIR}
