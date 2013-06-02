example-code-realtime-php
=========================

Example code from my talk about adding realtime functionality to PHP applications using Node and Redis. This code assumes you have a php 5 webserver running at http://localhost, with the contents of this repository. To run the application:

Install Node and npm
--------------------

Install Node socket.io Package
------------------------------

    > npm install socket.io

Install Node redis Package
--------------------------

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