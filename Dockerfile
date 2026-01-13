FROM caddy:alpine

RUN echo "http://dl-cdn.alpinelinux.org/alpine/edge/testing" >> /etc/apk/repositories && \
	apk add --no-cache php84-fpm php84-mbstring php84-pecl-imagick php84-fileinfo php84-zip php84-intl php84-pdo_sqlite php84-sqlite3 php84-session && \
	mkdir /srv/app && \
	chown 10801:10801 /var/log/php84 /srv/app && \
	echo -e "[www]\ncatch_workers_output = yes\naccess.log = /proc/self/fd/2\nphp_admin_value[error_log] = /proc/self/fd/2" >/etc/php84/php-fpm.d/peppermint.conf

COPY docker/Caddyfile /etc/caddy/Caddyfile
COPY build/index.php /srv/app/
COPY docker/run.sh /srv/
COPY docker/peppermint.json /srv/app/
RUN mkdir -p /srv/data &&\
chown 10801:10801 /srv/data &&\
chown 10801:10801 /srv/app/peppermint.json &&\
chmod 664 /srv/app/peppermint.json

# /srv/app/peppermint.json needs to be your peppermint.json file.
# This dockerfile uses a basic temporary peppermint.json, 
# to ensure the file is created with the right permissions. 
# On first run, Pepperminty Wiki will fill out the rest of the missing settings.
#
# Alternatively, you can generate a full peppermint.json, and replace the temporary file. 
# To do so, you'll have to setup a temporary instance of Pepperminty Wiki 
# (even just using e.g. php -S [::]:35623 -t build after cloning the git repository.)

# IMPORTANT: Set data_storage_dir to /srv/data!
# See also https://starbeamrainbowlabs.com/labs/peppermint/peppermint-config-info.php#config_data_storage_dir
VOLUME [ "/srv/data" ]

EXPOSE 80

# Pepperminty Wiki runs as user UID 10801 and GID 10801.
# Remember: Running any docker apps as root -- even inside the container -- is a terriible idea and leaves you liable to security issues! 
USER 10801:10801
WORKDIR /srv/app

# Start PHP-FPM and Caddy via a script
CMD ["sh", "/srv/run.sh"]
