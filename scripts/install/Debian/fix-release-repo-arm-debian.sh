#!/bin/bash

cat > "/etc/apt/sources.list.d/freeswitch.list" <<DELIM
deb http://repo.fusionpbx.com/freeswitch-armhf/release/debian/ wheezy main
DELIM

cat > "/etc/apt/sources.list.d/fusionpbx.list" << DELIM
deb http://repo.fusionpbx.com/fusionpbx/release/debian/ wheezy main
DELIM