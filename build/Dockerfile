FROM ubuntu:24.04

RUN apt-get -qq update -y && \
        apt-get -qq -y --no-install-recommends install  tzdata && \
        ln -fs /usr/share/zoneinfo/Europe/Paris /etc/localtime && \
        dpkg-reconfigure -f noninteractive tzdata && \
        apt-get -qq upgrade -y && \
        apt-get -qq -y --no-install-recommends install curl openssl ca-certificates && \
        update-ca-certificates && \
        apt-get clean && \
        rm -rf /var/lib/apt/lists/*

RUN apt-get -qq update -y && \
        apt-get -qq -y --no-install-recommends install php php-gd php-mysql php-curl php-json php-xml php-fpm php-mbstring php-intl php-zip cron git && \
        apt-get clean && \
        rm -rf /var/lib/apt/lists/*

COPY www.conf /etc/php/8.3/fpm/pool.d/www.conf
COPY php-fpm.conf /etc/php/8.3/fpm/php-fpm.conf
COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN sed -i 's/short_open_tag = Off/short_open_tag = On/g' /etc/php/8.3/apache2/php.ini &&\
        sed -i 's/short_open_tag = Off/short_open_tag = On/g' /etc/php/8.3/fpm/php.ini &&\
        sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 8M/g' /etc/php/8.3/fpm/php.ini &&\
        chmod a+x /docker-entrypoint.sh

EXPOSE 9000

CMD /docker-entrypoint.sh
