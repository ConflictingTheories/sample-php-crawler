#!/bin/bash

export BUILD_PATH=$(pwd)

docker-compose down -v
docker-compose build
docker-compose up -d
