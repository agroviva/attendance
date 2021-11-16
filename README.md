# Zeiterfassung für EGroupware

# Clone Github Repository
```$``` ```git clone https://github.com/agroviva/attendance```
# Nginx Configuration
Add to this file: ```sudo nano /etc/egroupware-docker/egroupware-nginx.conf```
```
# Routing Templates
location /egroupware/attendance/graph {
    alias /usr/share/egroupware/attendance/graph;
    try_files $uri $uri/ @attendancegraph;

    location ~ \index.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $request_filename;
        fastcgi_pass $egroupware:9000;
    }
}

location @attendancegraph {
    rewrite /graph/(.*)$ /egroupware/attendance/graph/index.php?/$1 last;
}

# Export (PDF, Excel etc.)
location /egroupware/attendance/export {
    alias /usr/share/egroupware/attendance/export;
    try_files $uri $uri/ @export;

    location ~ \index.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $request_filename;
        fastcgi_pass $egroupware:9000;
    }
}

location @export {
    rewrite /export/(.*)$ /egroupware/attendance/export/index.php?/$1 last;
}
```
