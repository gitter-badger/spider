#!/usr/bin/env bash
echo "...installing orient version ${ORIENT_VERSION} to ${INSTALL_DIR}"

### install orient
# Download orient
wget -O $INSTALL_DIR/orientdb-community-$ORIENT_VERSION.tar.gz wget http://www.orientechnologies.com/download.php?file=orientdb-community-$ORIENT_VERSION.tar.gz
tar -xzf $INSTALL_DIR/orientdb-community-$ORIENT_VERSION.tar.gz -C $INSTALL_DIR/

### fix to make sure the orient install is also owned by root
chown -R root:root $INSTALL_DIR/orientdb-community-$ORIENT_VERSION

# update server.sh with correct user and path
sed -i '/ORIENTDB_DIR="YOUR_ORIENTDB_INSTALLATION_PATH"/ c\ORIENTDB_DIR="'$INSTALL_DIR'/orientdb-community-'$ORIENT_VERSION'"' $INSTALL_DIR/orientdb-community-$ORIENT_VERSION/bin/orientdb.sh
sed -i '/ORIENTDB_USER="USER_YOU_WANT_ORIENTDB_RUN_WITH"/ c\ORIENTDB_USER="root"' $INSTALL_DIR/orientdb-community-$ORIENT_VERSION/bin/orientdb.sh
