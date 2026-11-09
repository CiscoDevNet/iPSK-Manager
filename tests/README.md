Run the isolated PHP regressions and syntax checks:

```sh
docker run --rm --network none -v "$PWD:/app:ro" -w /app php:8.3-cli sh tests/run.sh
```

For actual SQL execution, build the test image and start a disposable database on an internal network. No application configuration is loaded. The integration test creates a randomly named database and removes it in a finally block.

```sh
docker build -t ipsk-review-php:local -f tests/Dockerfile .
docker network create --internal ipsk-review-test
docker run -d --rm --name ipsk-review-db --network ipsk-review-test -e MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1 mariadb:11.4
# Wait until the database reports ready before running:
docker run --rm --network ipsk-review-test -v "$PWD:/app:ro" -w /app ipsk-review-php:local php tests/database-integration.php
docker stop ipsk-review-db
docker network rm ipsk-review-test
```
