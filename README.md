// ==================================================================
//
//	SimplePage
//	Course format for Moodle 2.x
//
// ==================================================================

@date : june 2013
@version : 1.0
@author : Pascal Fautrero - CRDP Versailles - France

## 1- INTRODUCTION

FlexPage for Moodle 1.9 was a great plugin implemented by moodlerooms team. 
Unfortunately, when we decided to make an upgrade of our plateform, flexpage for moodle 2 was not yet available.

So what ? DIY ?
I have decided to make a new implementation of FlexPage called "SimplePage". 
The idea is to provide a moodle2 course format which is based on the tables used by FlexPage1. In that way, migration from FlexPage in moodle 1.9 to SimplePage in moodle 2 is possible and easy to do.
We hope this SimplePage version will be useful for the community.


## 2- MIGRATION FROM MOODLE 1.9 TO MOODLE 2.2

Here we are. We assume you are a moodle 1.9 administrator. You have hundred of courses based on FlexPage. Impossible for you to make a migration to moodle2 by hand.
Now, you can do this migration thanks to SimplePage !


Pre-requisites : 
* php_curl enabled for php CLI.
* root access on the server

* Step 1 : Take a small cup of tea, smile and sit down.
* Step 2 : Just upload moodle 2 on your server and replace your old moodle 1.9 by a new fresh version of moodle 2.
* Step 3 : Replace the content of /moodle/course/format/page by SimplePage.
* Step 4 : Using a ssh connection, go in the /moodle folder and start the migration using the following command : /usr/bin/php admin/cli/upgrade.php
* Step 5 : Then, you have to make a small manual update of 1 table in your database. In 'mdl_config_plugins', look for plugin 'format_page' and update the field value to '2011120100'.
* Step 6 : Everything is ok. Your moodle is up-to-date. Have fun !

**Remark :** It is not possible to display a specific block in a specific page with SimplePage.
SimplePage does not manage blocks anymore. Look at each course to control its blocks. 
But, to help you in your migration job, SimplePage displays your blocks at their original place. 
Thus, in some pages, you will see your blocks twice : one version displayed by SimplePage and the version displayed by moodle2. 

**Remark 2 :** In moodle2, left and right columns (called side-pre and side-post) can't be controlled by course format anymore. 
Fortunately, SimplePage is a naughty guy and forces moodle2 to display activities and resources in the correct column (using javascript).

**Remark 3 :** In moodle2, a new block follows user during his navigation. 
This block is located in the left column and is called "navigation block 2.0". Of course, you can disable this block. 
If you want to use this new block and if you look at its content, you can see for each course some strange links named "Section 0", "Section 1"... 
In fact, moodle uses these sections to store activities. But, FlexPage1 did not manage these sections. 
SimplePage helps you to make a one-one relation between sections and pages. Manually for each course, just go in the "edit course" page and click on "make a one-one relation between sections and pages". To transform these sections in simple links (and not in toggle button to hide/show content section), go in the administrator block, "appearance", "navigation" and enable navlinkcoursesections.




## 3- NEW INSTALLATION

Just upload SimplePage in /moodle/course/format/page and go on your moodle plateform with your administrator account. Follow the installation process.



## ACKNOWLEDGEMENTS

Thanks to net.tutsplus.com for the drop down menu :
http://net.tutsplus.com/tutorials/html-css-techniques/how-to-build-a-kick-butt-css3-mega-drop-down-menu/

Thanks to David Bushell - http://dbushell.com/ for the drag and drop jquery plugin.

