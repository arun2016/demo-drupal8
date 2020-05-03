CONTENTS OF THIS FILE
---------------------

* Introduction
* Requirements
* Installation
* Configuration
* Maintainers
* Contact With Us module details
  * Instructions
  * Captcha
  * Uninstall the module

INTRODUCTION
------------

Contact with us is a module which displays a form to site visitors
to contact site admin or send a inquiry. The inquiry can be
of any type like feedback, requesting more information or a lead.
The form simply has few common fields like name, company name, email,
phone, country, address, designation and query. Hence, this form can
be used for any purpose.

The form is available as a independent page and also as a block under admin
block configuration.

* For a full description of the module, visit the project page:
  https://www.drupal.org/project/contact-us
* To submit bug reports and feature suggestions, or to track changes:
  https://www.drupal.org/project/issues/contact-us

REQUIREMENTS
------------

This module requires no modules outside of Drupal core.

INSTALLATION
------------

* Install the Contact Us module as you would normally install a
  contributed Drupal module. Visit https://www.drupal.org/node/1897420 for
  further information.

CONFIGURATION
-------------

 1. Navigate to Administration > Extend and enable the module.
 2. Navigate to Administration > System > Contact Us Configuration for
    configuration of the email address.
 3. Navigate to Administration > Structure > Block layout > Place block > Contact
    Us to add the form as a Block to any region.
 4. Navigate to Administration > Content for all the contact us 
    form submission details.

MAINTAINERS
-----------

* Gaurav Kumar  https://www.drupal.org/u/gauravkumar

Contact Us
==========

Contact us is a module which displays a form to site visitors
to contact site admin or send a inquiry. The enquiry can be
of any type like feedback, requesting more information or a lead.
The form simply has few common fields like name, company name, email,
phone, country, address, designation and query. Hence, this form can
be used for any purpose.

Instructions
------------

Download and install this module as a regular drupal module installation. 
This module is not dependent on any other module.

The contact us form will be available at "/contact-us". 
Optionally you can provide a menu link to this form 
incase you do not want to use it as a block.

Contact us block will be available under 
Blocks page to add the form as a Block to any region.

After installation, it's optional to configure the email address to which the 
information of the contact us form is to be sent. A new menu will be added 
in the Drupal admin configuration page. 
This can be configured at "/admin/config/contact_us/adminsettings".

All the website contact us submission details are available in 
Drupal Admin under Content section.

Captcha
-------

It is highly encouraged to add captcha to this form in order to
avoid any spam submissions.

Uninstall the module
--------------------

If you intend to uninstall this module then before uninstallation make sure to delete
all the content related to contact us which is found under 
Administration > Content section.
