## [Learning with Texts Files](https://sourceforge.net/projects/learning-with-texts/) Dockerized

1. Download docker-compose.yaml:
```yaml
# docker-compose.yml

version: "3"

services:
  lwt:
    image: ghcr.io/fertkir/lwt:latest
    restart: unless-stopped
    ports:
      - "8080:80"
  db:
    image: mariadb:10.11
    restart: unless-stopped
    command: --transaction-isolation=READ-COMMITTED --log-bin=binlog --binlog-format=ROW
    volumes:
      - db:/var/lib/mysql
    environment:
      - MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=yes
volumes:
  db:
```

2. `docker compose up -d`