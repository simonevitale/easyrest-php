# EasyRest-Php

EasyRest-Php is a fast, modular and extensible PHP micro framework that helps you to quickly implement simple yet powerful RESTful APIs to manage your own content, oriented to the interoperability with external applications such as mobile apps.

EasyRest-Php implements XML defined modules and offers the possibility to create your own one which will be loaded dynamically.

> EasyRest-Php is still a "work in progress" incomplete work and is provided "as it is".
> Please check the TODO list in the development section to keep up to date about the work status.

### Tags
REST, Php, API, MySql, Back-end, CMS, Controller, Module, Object Oriented

### Version
0.9

### Installation

 -  Database - Once forked the repository you will need to set up the MySql database. The "sql" folder contains the SQL
script you will need to execute
 -  Extract the src in your desired folder (e.g. as default "portal") and upload it in your server. You can set this folder through the *Settings.xml* file.

##### Settings
 - settings/Database.xml - It contains your database settings: it is the first thing you want to set up. The object Database (Database.php) reads this file and implements a singleton to centralize the databsase requests.
 - *settings/Settings.xml* - "Variables" (folders, URLs and more) globally useful to not be hard coded.
 - *settings/Modules.xml* - It defines the modules to dynamically load.

### Modules

So far the following modules have been implemented:

* Language
* Category
* Country
* Author
* Location
* Article
* Event
* User - Manages users, including "sign in" / "sign out" / "sign up" operations.

### Development

Want to contribute? Great! Please don't hesitate to contact me for any requests or information.

##### Todos

 - Documentation - http://apidocjs.com/ is a good candidate.
 - Organization Module - Implement a many-to-many Organizations-User as designed in the database.

### Credits

EasyRest-Php is based on Jacwright REST Server [https://github.com/jacwright/RestServer]
