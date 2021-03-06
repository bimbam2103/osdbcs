## OpenSIM Database Cleanup Script

ATTENTION: This script does only work if you have access to ALL Region-OARs and Avatar-IARs. If you cannot access ALL OARs and IARs DO NOT RUN THIS SCRIPT !!!!

Target: This script is mainly used for gridowners to reduce the database size. This will be done by checking all OARs and IARs and remove all Assets they are not be used.
As example: You upload an entire XML-File with nearly 2000 Textures. So you have about 2000 new assets in the database. Now you upload the same file again in mistake. Now the same assets are created again. If you now delete one entire upload the assets stay in the database. This script removes these scripts because they do not have any reference in any inventory or any Region.

ANOTHER ATTENTION: MAKE BACKUPS. Before you run this script ALLWAYS MAKE BACKUPS.

### Tested environment

* OpenSIM 0.9.0.1
* PHP 7.3
* Debian Linux 9 (Stretch)
* MySQL 5.7.24

### Required minimum Environment

* OpenSim 0.9.x
* PHP 7
* MySQLi-Extension for PHP
* MySQL 5.6
* Debian Linux 7
* gzip

### Prerequisites

Edit the cleaner.php and edit the first lines to match your system and needs.

**Important:**

It is recommended that you set the optimize switch to ```true``` but you should pay attention to this part:

If you want to optimize your db you NEED enough free hard disk space to do this. The size of needed hard disk space is the size of the DB because MySQL will copy all tables to a new database and deletes the old one. In this way it removes **free bytes**.

### Usage

First you need to make the OARs and IARs in your servers. For the OARs to the following:

Go to **every** simulator and type as example:

```
use REGIONNAME
save oar REGIONNAME.oar
```

Now you have in the **bin**-Directory of your simulator every OAR you made. Just copy or move them to the location where your script is.

Next you need to create the IARs. This is a little bit harder to obtain. By default you need all passwords from every avatar to get the IARs. There are 2 possibilities:

* Let the users create an IAR by a webservice as example
* Change the passwordHash in the auth DB to create the backups and restore the passwordHash after IAR-creation

If you have the password just do the following:

```
save iar AVATARFIRST AVATARLAST / AVATARPASSWORD firstname_lastname.iar
```

Maybe you can create a system script to create these IARs to automate this process.

After you have all archives (OARs and IARs) just follow these steps:

* Shutdown every simulator
* Shutdown every server process (robust, inventory, money...)
* Execute this command: ```php cleaner.php avatarfirst_avatarlast.iar secondavatar_secondlast.iar region1.oar region2.oar region3.oar.....```

This script can take a while depending on the size of your assets and db.