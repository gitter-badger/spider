#!/usr/bin/env bash
echo "...installing neo version ${NEO4J_VERSION} to ${INSTALL_DIR}"

# install Neo4j locally:
wget -O $INSTALL_DIR/neo4j-community-$NEO4J_VERSION-unix.tar.gz dist.neo4j.org/neo4j-community-$NEO4J_VERSION-unix.tar.gz
tar -xzf $INSTALL_DIR/neo4j-community-$NEO4J_VERSION-unix.tar.gz -C $INSTALL_DIR/

sed -i 's/#org.neo4j.server.webserver.address=0.0.0.0/org.neo4j.server.webserver.address=0.0.0.0/' $INSTALL_DIR/neo4j-community-$NEO4J_VERSION/conf/neo4j-server.properties
