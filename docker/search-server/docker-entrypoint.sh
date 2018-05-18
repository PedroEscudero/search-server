#!/bin/bash

service haproxy restart
exec /usr/bin/supervisord --nodaemon