/**
 * This file is part of the RatchetBundle project.
 *
 * Copyright (c) 2013 Philipp Boes
 *
 * LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 */
/**
 * This is a slightly modfied version of ratchet from Philipp Boes
 * 
 * Changed by: Philipp A. Mohrenweiser <phiamo@googlemail.com> for Booksync
 */
(function() {

    'use strict';

    if (! 'WebSocket' in window) {
        throw new Error('WebSockets are not supported by your browser.');
    }

    if (! 'JSON' in window) {
        throw new Error('No JSON support available by your browser.');
    }

    /**
     * @type Object
     */
    var socketEventHandler = {};

    /**
     * @type WebSocket
     */
    var socket = false;
    
    /**
     * @type string
     */
    var token;
    var response_data_type;
    /**
     *
     * @param {object} config
     * @constructor
     */
    var Ratchet = function(config) {
        if (typeof config['response_data_type'] == 'undefined') {
            throw new Error("Please define a response_data_type!");
        }
        if (typeof config['uri'] == 'undefined') {
            throw new Error("Please define a uri!");
        }
        this.config = config;
        response_data_type = config.response_data_type;
    };
    
    /**
     * Init
     *
     * @param {string} p2_token
     */
    Ratchet.prototype.init = function(p2_token) {
        if (typeof p2_token != 'undefined') {
            token = p2_token;
        }
        var self = this;
        if (typeof socket == 'WebSocket') {
            socket.close();
        }
        socket = new WebSocket(this.config.uri);
        socket.onmessage = function(e) { onMessage.call(self, e); };
        socket.onclose = function(e) { invokeEventHandlers.call(self, 'socket.close', e); };
        socket.onerror = function(e) { invokeEventHandlers.call(self, 'socket.error', e); };
        socket.onopen = function(e) { invokeEventHandlers.call(self, 'socket.open', e); };
    };
    /**
     * Emits an event to the underlying websocket. Returns true on success, false on error.
     *
     * @param {string} event
     * @param {*} data
     *
     * @returns {boolean}
     */
    Ratchet.prototype.emit = function(event, data) {
        try {
            var encoded = JSON.stringify({ event: event, data: data });

            socket.send(encoded);

            return true;
        } catch (e) {
            if (Ratchet.debug) {
                if(typeof(console) != 'undefined') console.error(e);
            }
        }

        return false;
    };

    /**
     * Handles a websocket message event.
     *
     * @param e The websocket event
     */
    function onMessage(e) {
        var data;

        try {
            data = JSON.parse(e.data);
        } catch (e) {
            if (Ratchet.debug) {
                if(typeof(console) != 'undefined') console.error('Invalid json format: ' + e.data);
            }

            return;
        }

        if (data.event && data.data) {
            invokeEventHandlers.call(this, data.event, data.data, e);
        } else if (Ratchet.debug) {
            if(typeof(console) != 'undefined') console.error('Invalid data format. Assuming { event: "...", data: ... } structure.');
        }
    }

    /**
     * Registers a socket event handler for the given event.
     *
     * @param {string} event
     * @param {function} handler
     *
     * @throws Error On invalid input parameters
     */
    function registerEventHandler(event, handler) {
        if (! typeof event == 'string' || ! event.length) {
            throw new Error('The event name must be a string and must not be empty.');
        }

        if (! typeof handler == 'function') {
            throw new Error('An event handler must be a function.');
        }

        var eventHandlers = socketEventHandler[event];

        if (! eventHandlers) {
            eventHandlers = socketEventHandler[event] = [];
        }

        eventHandlers[eventHandlers.length] = handler;
    }

    /**
     * Invoke all registered handlers for the given event.
     *
     * @param {string} event
     */
    function invokeEventHandlers(event) {
        var handlers = socketEventHandler[event];
        if (handlers && handlers.length) {
            for (var i = 0, len = handlers.length; i < len; i++) {
                handlers[i].apply(this, Array.prototype.slice.call(arguments, 1));
            }
        }
    }

    // socket.open hook
    registerEventHandler('socket.open', function() {
        this.emit('socket.auth.request', {token: token, data_type: response_data_type});
    });
    // socket.auth.success hook
    registerEventHandler('socket.auth.success', function(client) {
        this.authenticated = true;
        this.client = client;
    });

    // expose functionality
    Ratchet.registerEventHandler = Ratchet.prototype.on = registerEventHandler;

    // public members
    Ratchet.prototype.authenticated = false;
    Ratchet.prototype.client = null;

    // debug
    Ratchet.debug = false;

    // expose
    window.Ratchet = Ratchet;

}).call(window);
