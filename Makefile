init: build ssl server
make-cert:
	brew install mkcert
build:
	docker-compose -f docker-compose.yml build
server:
	docker-compose -f docker-compose.yml up -d
ssl:
	mkdir -p .docker/.certs
	mkcert -install
	mkcert '*.tweakers.test'
	mv _wildcard.tweakers.test.pem .docker/.certs/server.crt
	mv _wildcard.tweakers.test-key.pem .docker/.certs/server.key
stop:
	docker-compose -f docker-compose.yml down

