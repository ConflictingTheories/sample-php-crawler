#!/bin/bash

while read line do
    export $line
done < .env

docker-compose build
docker-compose up -d
