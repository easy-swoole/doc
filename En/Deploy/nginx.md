---
title: deploying easyswoole with nginx  
meta:
  - name: description
    content: deploying easyswoole with nginx 
  - name: keywords
    content: deploying easyswoole with nginx 
---

# Nginx deployment

[Nginx](http://nginx.org/）Is a lightweight `Web` server/reverse proxy server and e-mail `(IMAP / POP3)` proxy server. It is characterized by less memory and strong concurrency. It can be used as the front server of easysoole to realize load balancing.

## http proxy

```nginx
# At least one is required to configure the easysoole node
upstream easyswoole {
    server 127.0.0.1:9501;
    server 127.0.0.1:9502;
    server 127.0.0.1:9503;
}

server {
    # Port monitored by nginx
    listen 80; 
    # domain name
    server_name proxy.easyswoole.com;

    location / {
        # Forward the host and IP information of the client to the corresponding node 
        proxy_set_header Host $http_host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;

        # Forward cookie, set samesite
        proxy_cookie_path / "/; secure; HttpOnly; SameSite=strict";

        # Proxy access to real servers
        proxy_pass http://easyswoole;
    }
}
```

## websocket proxy

```nginx
#At least one is required to configure the easysoole node
upstream easyswoole {
    # The load balancing mode is set to IP hash. The function is that different clients will interact with the same node every time they request.
    ip_hash;
    server 127.0.0.1:9501;
    server 127.0.0.1:9502;
    server 127.0.0.1:9503;
}

server {
    listen 80;
    server_name websocket.easyswoole.com;

    location / {
        # websocket的header
        proxy_http_version 1.1;
        # Upgrade HTTP1.1 to websocket protocol
        proxy_set_header Upgrade websocket;
        proxy_set_header Connection "Upgrade";

        # Forward the host and IP information of the client to the corresponding node
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Host $http_host;

        # If there is no interaction between the client and the server within 60 seconds, the connection will be disconnected automatically.
        proxy_read_timeout 60s ;

        # Proxy access to real servers
        proxy_pass http://easyswoole;
    }
}
```
