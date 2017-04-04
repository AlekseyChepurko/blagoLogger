# blagoLogger

## About
This is a logger collects the information adbout user's  entries on your site even if he did not submit. 

If user enters any values in any input this data sends to server via ajax and saves to the DB <b>ALL</b> data from each input field of the current page.

Also here is a <i><b>snedData.php</i> script which gets all data form "logs" table and sent a .csv file to the e-mail you want



## Using
### Including to your project
Using of this logger is pretty simple. All you need to collect all entered information is include main.js script from JS folder to the end of your body section:
    
    <script src="./js/main.js"></script>
    
And set up some parameters:
    
### DB settings
All DB settings are in the <b>/logger/DBSettings.php</b>. For continous saving entered data - set the parameters of your <b>MySQL</b> DB.

### Mailer Setting
To make this project send all collected DB records with e-mail at first you have to set parametrs up in file <i><b>/logger/MailerSettings.php</b></i>

Then, just go in your browser to 

    yoursite.com/pathToLoggerDir/sendData.php    

After this step you should get an e-mail with userDataLog.csv in it.

Also you may set your server to run sendData sript for example every hour and you will recive your users' data automaticly every hour

<u>If you use a terminal at server you need sudo.</u>

<b>!!!ATTENTION!!!

After sending data to the e-mail table "logs" drops !!! Later it creates again by sript

### .csv file

To make .csv file (in fact snapshot of your current logs table state) just run in browser

    yoursite.com/pathToLoggerDir/logger/writeLogs.php
    
It will make a file named usersDataLog.csv  in pathToLoggerDir/logs directory.

<u>If you use a terminal at server you need sudo.</u>

## Dependencies
### JS
Javascript script uses <b>jQuery</b>. Include it before using!
### PHP
Code written in native PHP v5.6 and does not have any dependencies
### DB
All queries are MySQL.