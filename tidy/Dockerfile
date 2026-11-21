# HortusFox Tidy UI: original HortusFox plus an overlay that replaces the frontend.
# BASE defaults to the official upstream image, so the backend is always unmodified HortusFox.
ARG BASE=ghcr.io/danielbrendel/hortusfox-web:latest
FROM ${BASE}

# Keep the original templates reachable as "upstream/..." so overlay views can include them.
RUN cp -r /var/www/html/app/views /tmp/upstream-views \
 && mv /tmp/upstream-views /var/www/html/app/views/upstream

COPY tidy/views/ /var/www/html/app/views/

# HortusFox releases up to 6.4 keep the layout partials next to the pages, newer ones in layout/.
# Provide the Tidy partials in whichever place the base image uses.
RUN if [ -f /var/www/html/app/views/navbar.php ]; then \
      cp /var/www/html/app/views/layout/*.php /var/www/html/app/views/; \
    fi
COPY tidy/public/css/ /var/www/html/public/css/
COPY tidy/public/js/ /var/www/html/public/js/
COPY tidy/public/fonts/ /var/www/html/public/fonts/
