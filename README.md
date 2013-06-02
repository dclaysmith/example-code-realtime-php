example-code-realtime-php
=========================

Example code from my talk about adding realtime functionality to PHP applications using Node and Redis. This code assumes you have a php 5 webserver running at http://localhost, with the contents of this repository. To run the application:

Install Node and npm
--------------------

Here are some suggestions for getting Node and its package manager, npm, up and running. 

https://gist.github.com/isaacs/579814

Install Node Packages
---------------------

You will need to install two packages using npm:

    > npm install socket.io
    > npm install redis

Install Redis
-------------

http://redis.io/topics/quickstart

Start Redis
-------------

    path-to-redis/src > ./redis-server

Start Node
----------

    > node sync.js

Open the Webpage
----------------

http://localhost/Index.php