/**
 * sync.js
 */
var io      = require('socket.io').listen(8080);

console.log('Web Server started, waiting for connections...');

var redis   = require("redis").createClient();

redis.subscribe("/todo");

console.log('Subscribed to redis channel "todo"...');

redis.on("message", function(channel, message){

  console.log("Received a message on channel %s : %s", channel, message);

  data = JSON.parse(message);

  console.log('Emitting a message to all of the other clients.');
  
  io.sockets.emit('message', data);

});