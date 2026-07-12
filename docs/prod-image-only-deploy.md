# Production Image-Only Deploy

Production should not clone or build the full source repo.

Production only needs:

- docker-compose.yml
- .env.prod
- setup/site.prod.yml
- pulled DockerHub images
- persistent Docker volumes

Images:

- vinugawade/growbig-site-platform:prod
- vinugawade/growbig-site-frontend:prod

Server ports:

- site-platform listens on 127.0.0.1:8081
- site-frontend listens on 127.0.0.1:8082
- Apache owns 80/443 and reverse proxies to those local ports

Domains:

- admin.growbigllp.com -> 127.0.0.1:8081
- api.growbigllp.com -> 127.0.0.1:8081
- growbigllp.com -> 127.0.0.1:8082
- www.growbigllp.com -> 127.0.0.1:8082
- dev.growbigllp.com -> 127.0.0.1:8082

Use docker-compose on the current Debian 10 server because Docker is old and does not support the docker compose plugin command.
