# Chip
Chippenham/Generic Folk festival Website

This is the master system for the website, no data is loaded here (ever)

Needs php 8

Installation:

Needs to be at a webspace root, Php needs the Document root in the php include path.
php settings (Other than Default): max_execution_time=120, max_input_time=120, post_max_size=16M, upload_max_filesize=16M,
              pm.max_children=20, max_input_vars = 4000

Apache needs rewrite enabled: sudo a2enmod rewrite && sudo service apache2 restart

Needs ImageMagick installed for some features to work.

Needs libheif-examples installed to handle heic/heif image formats.

Needs Skeema see https://www.skeema.io for initialise to work and the database updates to be automated.  It has an embeded copy that will work on 64 bit Intel/ Ubuntu Linux.

Needs wkhtmltopdf to convert html to pdfs (to freeze contracts etc)
apt-get install wkhtmltopdf
ln -s /usr/bin/wkhtmltopdf /usr/local/bin/html2pdf

Then run int/Initialise.php - this will create appropriate subdirectories and populate the database 
with appropriate initial tables and values

It will (soon?) track changes to the structure of the database and automatically update as appropriate.

See int/AdminGuide.php for a lot more about the system admin

See festfiles/documentation.odt for a start at documenting the system

Does not include the data from the database, and most uploaded images under /images

