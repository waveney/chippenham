# Running in Docker

The site can be ran in docker for development. A self signed certificate will be automatically provided

## Requirements

[Docker and Docker Compose](https://docs.docker.com/get-docker/)

## Configuration

The config assumes the local uid and gid are both 1000. If your local ids are differnet values set environment variables `UID` and/or `GID` with the correct values

## Running

`docker compose up -d`

## Accessing

Navigate to https://localhost:8443

## PHP settings

The php.ini uses the default development settings altered to disable deprecation messages
