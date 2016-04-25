# MDS SPA

## Installation

1. Install Apache server and PHP, eg: XAMPP.

2. Put the files into `htdocs` under XAMPP installation directory.

3. Create a folder named `files` in the root of the project and make sure
   the server software has read and write permission on it. This folder is
   used to store the generated QR codes and zip files.
   
4. Launch the Apache server and use localhost to run the application

Note: the QRCode generation process will be completed instantly, while for the
      email service, it may has some kind of delay due to the regular attachment
      check of email server.
