var io = require('socket.io'),
    express = require('express'),
    app = express.createServer();
 
app.configure(function () {
    app.use(express.cookieParser());
    app.use(express.session({secret: 'secret', key: 'express.sid'}));
    app.use(function (req, res) {
        res.end('<h2>Hello, your session id is ' + req.sessionID + '</h2>');
    });
});
 
app.listen(8080);
var sio = io.listen(app);
 
sio.sockets.on('connection', function (socket) {
    console.log('A socket connected!');
});

var users = [];

sio.set('authorization', function (data, accept) {
    // check if there's a cookie header
    if (data.headers.cookie) {
        // if there is, parse the cookie
        console.log(data.headers.cookie);
        //data.cookie = parseCookie(data.headers.cookie);
        // note that you will need to use the same key to grad the
        // session id, as you specified in the Express setup.
        //data.sessionID = data.cookie['express.sid'];
    } else {
       // if there isn't, turn down the connection with a message
       // and leave the function.
       return accept('No cookie transmitted.', false);
    }
    // accept the incoming connection
    accept(null, true);
});
