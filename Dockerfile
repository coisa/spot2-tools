FROM php:alpine

ENV PATH="/spot2/bin:$PATH"

RUN docker-php-ext-install pdo pdo_mysql

COPY ./ /spot2
WORKDIR /spot2

CMD ["spot2"]