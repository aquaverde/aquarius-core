#!/bin/sh
# enable maintenance mode
# Optional argument is an IP-Address to restrict to.
set -e
cd `dirname $0`/../../cache/
date --date='+2 hours' +"%Y.%m.%d %H:%M, ${1:-*}, *" > AQUARIUS_MAINTENANCE
