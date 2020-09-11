# Sample PHP Web Crawler
This is a sample web crawler application designed to crawl a website, and index various information as it comes across it.

# Dependencies
* Docker / Docker-Compose
* Bash (linux/mac)
* Powershell (windows)

> ### __Note on Docker__
> All builds and external libraries are generated within the Docker images. This is done for maximum portability, but at the expense of IDE support. Because of this, external PHP libraries (such as composer / phalcon) will not be contained within this build and will not be accessible to an IDE unless installed and made available somewhere else.

# Usage
Setup `.env` with your configurations (see `.env-sample` for variables required)

### Windows Powershell
Run the following to get started (assumes dependencies & `.env` installed):

        > . ./start.ps1


### Linux / Mac
Run the following to get started (assuming you have the necessary dependecies and `.env`):

        $ ./start.sh

