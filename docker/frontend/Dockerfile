FROM node:22-alpine

WORKDIR /var/www/frontend

COPY --chmod=755 setup.sh /usr/local/bin/setup.sh

EXPOSE 5173

ENTRYPOINT ["/usr/local/bin/setup.sh"]
