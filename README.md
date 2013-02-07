PHP UDP Tool
============

Simple utility for point to point connection quality testing.


Usage
-----

You can use the tool to test the connection quality from client to server:

    Client  --->--->---> Server

First you start the server:

    php put_server.php 10.10.10.10 8888 > /some/file/path/to/store/log
    
Note: /some/file/path/to/store/log should be replaced with an actual file path.
Note: 10.10.10.10 should be replaced with an IP address on which the server should listen
Note: 8888 should be replaced with the port on which the server should listen


Usage:

    php put_server.php *hostname/ip* *port* > *file to write the log in*


When the server is listening you can start the connection testing by starting the client from the other endpoint:

    php put_client.php 10.10.10.10 8888
    
Note: 10.10.10.10 is the ip/hostname of the server
Note: 8888 is the port the server is listening on

usage:

    php put_client.php *server hostname/ip* *port*  [packet_segmentation=20] [packet_size=1400]
    
When you have the connection running you can visualize the logs that server outputs by the put_log_vizualizer.php utility.
You point it to the log file on the server and the util will proccess it and output an html which should be saved in a file and opend in a browser to see the pretty charts.

    php put_log_vizualizer.php /some/file/path/to/store/log > /processed/log/file.html
    
Usage:

    php put_log_vizualizer.php *path to server log file* > *path to a new .html file*
    

