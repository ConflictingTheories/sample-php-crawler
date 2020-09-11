#!/bin/bash

while read line do
    export $line
done < .env

export BUILD_PATH=$(pwd)

docker-compose down -v
docker-compose build
docker-compose up -d
