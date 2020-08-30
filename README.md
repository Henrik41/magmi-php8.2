Magmi 2 for Magento 2 > 2.1.x
===========================

This is fork from official magmi Github reposiotry (https://github.com/dweeves/magmi-git).
This fork use version 0.7.23 of magmi with changes for Magento 2 imported from repositories:
- tagesjump/magmi-m2 - https://github.com/tagesjump/magmi-m2
- pushnov-i/magmi-m2 - https://github.com/pushnov-i/magmi-m2
On top of that custom compatibility fixes were added.

We're accepting pull requests.
''''''''''''''''''''''''''''''

Magento CE 2 Support
====================

Current version is in **beta** and tested only for import simple and configurable products, categories, images and simple-configurable links.

**NOTICE: If you want to create URL rewrites please enable "On the fly indexer" plugin!**

**Known working plugins:**
- On the fly category creator/importer
- On the fly indexer
- Configurable Item processor
- Image attributes processor

### Authentication

Magmi now features shared Magento authentication out of the box.

One can simply use their Magento administrative (backend) credentials to login to Magmi.


#### .ini File Warning

While the authentication protects your Magmi web interface from unauthorised logins, it doesn't protect you from a poorly configured server.

Magmi uses .ini files to store it's configuration, and some servers will serve these files as plain text files if the are requested directly.

There is never a reason to serve .ini files to end users on a Magento platform, so ensure that your server is configured not to!




## Changelog form the base (Magento 1 version)

magmi-git 0.7.24
===

Security fix , remove magmi default authentication.
Force usage of magento admin login.

magmi-git 0.7.23
===

The [official Magmi Wiki](http://wiki.magmi.org/) is still hosted at SourceForge.