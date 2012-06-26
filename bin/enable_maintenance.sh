#!/bin/sh
# enable maintenance for all hosts for two hours
date --date='+2 hours' +"%Y.%m.%d %H:%M, *, *" > AQUARIUS_MAINTENANCE
