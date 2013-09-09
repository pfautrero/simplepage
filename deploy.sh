#! /bin/bash

# Compile css files
cd /home/pascal/Documents/SIMPLEPAGE/page/lib/template
compass compile sass/style.scss

# Deployment
rm -rf  /var/moodle25/course/format/page
cp -R /home/pascal/Documents/SIMPLEPAGE/page /var/moodle25/course/format/
chown -R www-data:www-data /var/moodle25/course/format/page

# unit tests
cd /var/moodle25/
vendor/bin/phpunit course/format/page/tests
