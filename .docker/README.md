# Docker Guide

## Install
Install [Docker](https://docs.docker.com/v17.12/install/) & [Docker Compose](https://docs.docker.com/compose/install/).
Also install [mkcert](https://github.com/FiloSottile/mkcert) for local ssl-certificates.

### Docker for mac
Since Docker For Mac has some serious performance issues regarding volume mounting
it's recommended to use Docker Toolbox. This installation guide is written specifically for
Docker Toolbox.

Download Docker Toolbox [here](https://download.docker.com/mac/stable/DockerToolbox.pkg).

Ignore the quick start for now and finish the installation.

#### Create a Docker Machine
Docker runs in a VM when you run Docker Toolbox. To create a VM, run the following command:

```bash
docker-machine create -d virtualbox --virtualbox-memory 4096 default
```

Next, start the machine:
```bash
docker-machine start default
```

To use the started machine, export the necessary shell variables with:
```bash
eval $(docker-machine env default)
```

To have faster volume mounts and to prevent permission problems we leverage the power
of `docker-machine-nfs`.

Install `docker-machine-nfs` with Homebrew:
```bash
brew install docker-machine-nfs
```

Then run:
```bash
docker-machine-nfs default --nfs-config="-alldirs -maproot=0" --mount-opts="noacl,async,nolock,vers=3,udp,noatime,actimeo=1"
```

Your VM should be ready to use now!

## TLDR;
Run this from the repository's root directory:
```sh 
make init
```

## Generate SSL certificate
To bootstrap a development environment in Docker, you need to setup a local certificate first.
```sh
make ssl
```

## Start the stack in development mode
Starting the development stack is as easy as entering a single command in the root
directory of this repository:
```sh
make server 
```

This will start a development server in the background. Make sure your local domain
name is registered in `/etc/hosts`:
```sh
# Docker for Mac
localhost  comments.tweakers.test
# Docker Toolbox
192.168.99.100  comments.tweakers.test 
```

Your development environment should now be available on `https://comments.tweakers.test`

## Known issues
At the moment, the `www-data` user in the Apache container has a hardcoded ID of `502`.
This is the default ID for Mac users. This setup is very likely not working on other OS-es.
