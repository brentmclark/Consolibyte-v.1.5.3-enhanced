
Here is my take on an implementation of a framework for integrating PHP 
applications with QuickBooks via the QuickBooks Web Connector and qbXML 
requests. 

Make sure you look at docs/example_server.php for an example of how to use this 
code! 

--------------------------------------------------------------------------------

 * * * 
 
 * If you want to be notified when I update this package, *send me an e-mail!* 
 
 * If you find bugs, need additional features, have a neat idea, etc, please 
 	*send me an e-mail* and let me know so I can continue to improve the 
 	package! 
 
 * I'll try to provide as much support as possible if you send me an e-mail or 
 	catch me on AOL IM if you get stuck. Failing that, *I am available for 
 	part-time contract work* if you need further help or are looking for a 
 	developer to implement a QuickBooks integration solution. 
 
 * * * 

--------------------------------------------------------------------------------

Requirements:
 - PHP v5.x or greater

You need to do this to get things integrated: 
 - Create a PHP SOAP server 
 - You write handler functions for generating valid qbXML requests
 - You write handler functions for handling qbXML responses
 - Add calls to QuickBooks_Queue::enqueue() to your application where appropriate
 - Build a .QWC file
 - Set up the QuickBooks Web Connector using the .QWC file

The idea behind my framework is this: I provide a queueing class and a 
framework for the SOAP server. You have to write helper functions which 
generate qbXML request and handle qbXML responses. You integrate your 
application in two pieces: you add calls to queue up items in your main 
application, and you write those qbXML handler functions to do the grunt work. 
The framework handles a lot of the ugly stuff. 

*** MAKE SURE YOU LOOK AT THE INCLUDED EXAMPLE FILES ***

The most-used back-end is the MySQL database server backend. Other back-ends 
are supported, you can see what back-ends are supported by browsing the 
Quickbooks/Driver/ directory.  



This has been developed, tested, and deployed on a FreeBSD machine running 
the Apache web server, v2.x and PHP v5.2.x. I developed with v2.x of the 
QuickBooks Web Connector and QuickBooks Pro 2006. I have now also tested with 
QuickBooks Simple Start 2008, Pro 2008, and QuickBooks Enterprise 8.0.
I have reports of it working with QuickBooks 2004, and a few other versions 
of QuickBooks as well. 

--------------------------------------------------------------------------------

Some of the code included is in somewhat beta form, and has only been tested on 
a very limited basis, and only been tested with the features/functionality that 
I needed when writing the code. If something explodes, please don't sue me. That 
said, if you e-mail me I'd be glad to try to help as much as I can. 

 - Questions? E-mail me. 
 - Comments? E-mail me.
 - Feature requests? E-mail me. 
 - Problems? E-mail me.
 - Want to pay me to develop an integration solution? E-mail me!  

Good luck with the integration! 

 - Keith Palmer
   keith@consolibyte.com

