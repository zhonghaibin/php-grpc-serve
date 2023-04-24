#FROM spiralscout/roadrunner:2023.1.1 AS roadrunner
#FROM zhonghaibin/php8-cli-roadrunner:latest
#COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr
#EXPOSE 8000
#ENTRYPOINT  ["rr", "serve", "-d", "-c", ".rr.yaml"]

FROM zhonghaibin/php8.1-cli-roadrunner:latest
COPY . /app
RUN composer ins --no-dev