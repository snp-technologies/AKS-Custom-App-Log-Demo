FROM php:7.3-apache-stretch

COPY apache2.conf /etc/apache2
COPY entrypoint.sh /bin/

RUN chmod 775 /bin/entrypoint.sh

# install the PHP extensions we need
RUN apt-get update; \
	apt-get install -y --no-install-recommends \
        curl \
        git \
		nano \        
        rsyslog \
        sudo \
        wget \
	;

# set php.ini file
RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

# set recommended PHP.ini settings
# Include PHP recommendations from https://www.drupal.org/docs/7/system-requirements/php
RUN { \
  echo 'error_log=/var/log/apache2/php-error.log'; \
  echo 'log_errors=On'; \
  echo 'display_errors=Off'; \
  } >> /usr/local/etc/php/php.ini

# Configure custom error log
# see https://www.drupal.org/docs/8/core/modules/syslog/overview#s-2-configure-syslog-to-log-to-a-separate-file-optional
RUN echo "local0.* /var/log/apache2/myapp.log" >> /etc/rsyslog.conf

RUN \ 
  ln -sfT /dev/stderr "/var/log/apache2/myapp.log"; \
  chown -R --no-dereference "www-data:www-data" "/var/log/apache2/"

#copy application code
WORKDIR /var/www/html
COPY . .

ENTRYPOINT ["/bin/entrypoint.sh"]
