FROM ubuntu:24.04 as build

RUN apt-get update -y && apt-get upgrade -y && apt-get install -y wget unzip

RUN EXPECTED_SHA1="a2633e94eae5aa93756ee2801ae1cb4817cfd106"  \
    && ARCHIVE="learning_with_texts_2_0_3_complete.zip" \
    && wget "https://downloads.sourceforge.net/project/learning-with-texts/$ARCHIVE" \
    && test "$EXPECTED_SHA1" = "$(sha1sum "$ARCHIVE" | awk '{print $1}')" \
    && unzip "$ARCHIVE" \
    && rm "$ARCHIVE" \
    && unzip *.zip -d src

FROM php:8.1-apache

WORKDIR /var/www/html/
COPY --from=build src/ .
RUN mv connect_xampp.inc.php connect.inc.php \
    && sed -i -e "s/localhost/db/g" connect.inc.php
RUN docker-php-ext-install mysqli
EXPOSE 80
