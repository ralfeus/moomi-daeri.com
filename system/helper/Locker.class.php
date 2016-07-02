<?php
namespace system\helper;
use ErrorException;

class Locker {
    /**
     * Try to acquire an arbitrarily-named lock by binding a Unix
     * (family AF_UNIX) datagram (type SOCK_DGRAM) socket to an
     * abstract address corresponding to the lock name. On success,
     * the bound socket resource is returned (or null, on
     * failure). You may either call unlock() with that resource to
     * release the lock explicitly, or rely on the operating system
     * to clean up the socket when the process exits.
     * @param string $name name of the lock to acquire
     * @return resource null if the lock failed, or a socket
     *                  resource on success
     */
    static function lock($name) {
        $socket = socket_create(AF_UNIX, SOCK_DGRAM, 0);
        try {
            $bound = socket_bind($socket, "\x00$name");
        } catch (ErrorException $e) {
            socket_close($socket);
            return null;
        }
        return $bound ? $socket : null;
    }

    /**
     * Explicitly release a lock acquired with lock(). This is only
     * necessary if you need the lock released before the process
     * exits. This function is unnecessary, really, but you might
     * not want to require callers to know their lock is a socket.
     * @param resource $socket
     */
    static function unlock($socket) {
        socket_close($socket);
    }
}