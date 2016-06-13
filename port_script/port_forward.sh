#! /bin/bash
#
# Enable port forwarding
#
# Requirements:
#   your Private Internet Access user and password as arguments
#
# Usage:
#  ./port_forward.sh


port_forward_assignment( )
{
  echo 'Loading port forward assignment information..'
  if [ "$(uname)" == "Linux" ]; then
    local_ip=`ifconfig tun0|grep -oE "inet addr: *10\.[0-9]+\.[0-9]+\.[0-9]+"|tr -d "a-z :"|tee /tmp/vpn_ip`
    client_id=`head -n 100 /dev/urandom | md5sum | tr -d " -"`
  fi
  if [ "$(uname)" == "Darwin" ]; then
    local_ip=`ifconfig tun0 | grep "inet " | cut -d\  -f2|tee /tmp/vpn_ip`
    client_id=`head -n 100 /dev/urandom | md5 -r | tr -d " -"`
  fi
  json=`wget -q --post-data="user=$USER&pass=$PASSWORD&client_id=$client_id&local_ip=$local_ip" -O - 'https://www.privateinternetaccess.com/vpninfo/port_forward_assignment' | head -1`
  echo $json
}

EXITCODE=0
PROGRAM=`basename $0`
VERSION=1.0
USER='p4052761'
PASSWORD='Trg2jP5ibo'

port_forward_assignment
portnum=$json

# Get the isolated portnumber
left=${portnum%%[0-9]*}
right=${portnum##*[0-9]}
temp=${portnum#"$left"}
portnum=${temp%"$right"}

transmission-remote -tall --auth pi:ferret -p $portnum

exit 0
