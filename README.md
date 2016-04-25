# MSD SPA

## Installation and Running

1. Install Apache server and PHP, eg: XAMPP.

2. Put the files folder into `htdocs` under XAMPP installation directory.

3. Create a folder named `files` in the root of the project and make sure
   the server software has read and write permission on it. This folder is
   used to store the generated QR codes and zip files.

   command example in OS X terminal: $ mkdir files and $ chmod 777 files/

4. Launch the Apache server and use localhost to run the application

Note:

1) the QRCode generation process will be completed instantly, while for the
   email service, it may has some kind of delay due to the regular attachment
   check of email server.

2) Testing for windows platform may have certain issues with SSL
   certificate problem, which may require different version for guzzlehttp
   service. If not working well on windows, please test the project with OSX.
   The above testing process is conducted under OS X EI Capitan, Version 10.11.3.
