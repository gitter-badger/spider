#!/bin/bash
echo "------------ RUNNING STARTUP COMMANDS ------------"

### Set variables
export NEO4J_VERSION="2.2.4"
export GREMLINSERVER_VERSION="3.0.2"
export ORIENT_VERSION="2.1.0"

export INSTALL_DIR="/home/vagrant"
export VAGRANT_DIR="/vagrant"
export BOOTSTRAP_DIR="$VAGRANT_DIR/vagrant"

export GREMLIN_DIR="apache-gremlin-server-$GREMLINSERVER_VERSION-incubating"

## start gremlin-server
cd $INSTALL_DIR/$GREMLIN_DIR
sudo bin/gremlin-server.sh conf/gremlin-server-spider.yaml > /dev/null 2>&1 &
cd $VAGRANT_DIR
sleep 30

## start neo4j
sudo $INSTALL_DIR/neo4j-community-$NEO4J_VERSION/bin/neo4j start
sleep 15

# changing password:
sudo curl -vX POST http://neo4j:neo4j@localhost:7474/user/neo4j/password -d"password=j4oen"

## start orient
sudo nohup $INSTALL_DIR/orientdb-community-$ORIENT_VERSION/bin/server.sh > $INSTALL_DIR/orientdb-community-$ORIENT_VERSION/log/server.out&
sleep 15

echo "------------ END: RUNNING STARTUP COMMANDS ------------"

#if [ -f /home/vagrant/gremlin-server-spider.yaml ];
#then
#   echo "yes."
#   touch /home/vagrant/still.exists.txt
#else
#   echo "no."
#   touch /home/vagrant/doesnotexist.txt
#fi
