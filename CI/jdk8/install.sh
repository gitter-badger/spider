#!/usr/bin/env bash
echo "...installing jdk"

### Add Repository
sudo add-apt-repository -y ppa:webupd8team/java
sudo apt-get update

## Install oracle jdk 8
# no interaction
echo debconf shared/accepted-oracle-license-v1-1 select true | /usr/bin/debconf-set-selections
echo debconf shared/accepted-oracle-license-v1-1 seen true | /usr/bin/debconf-set-selections

# run installer
sudo apt-get install -y oracle-java8-installer
sudo update-alternatives --auto java
sudo update-alternatives --auto javac
