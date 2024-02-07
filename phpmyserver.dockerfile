FROM ubuntu:22.04

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update -y
RUN apt-get install -y software-properties-common
RUN LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php
RUN apt-get update -y && \
    apt-get install -y git unzip wget curl build-essential \
    php8.3-cli php8.3-mbstring php8.3-dev php8.3-xml php8.3-curl
ADD ./ /pms
WORKDIR /pms
COPY php.ini /etc/php/8.3/cli/conf.d/php.ini
COPY app.php /pms/app.php
COPY phpmyserver /pms/phpmyserver
RUN chmod +x /pms/phpmyserver

EXPOSE 8080
CMD ["phpmyserver","-process-port=11000","-balancer-port=9090","-number-of-processes=5","-routing-point=index.php"]
