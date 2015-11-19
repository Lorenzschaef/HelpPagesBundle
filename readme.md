HelpPagesBundle
===============

This is a Symfony Bundle that provides a simple way of including end user documentation in your application. It takes markdown files stored on the server and renders them in a template, together with an automatically generated menu.

Installation
============

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require lorenzschaef/help-pages-bundle "~1"
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project.

The bundle depends on the KnpMarkdownBundle, so you have to register that as well, if you haven't already.

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Knp\Bundle\MarkdownBundle\KnpMarkdownBundle(),
            new Lorenzschaef\HelpPagesBundle\LorenzschaefHelpPagesBundle(),
        );

        // ...
    }

    // ...
}
```

Step 3: Configure the Routes
----------------------------

Add the following line to your app/config/routing.yml and change the prefix as you like.

```yml
help_pages:
    resource: "@LorenzschaefHelpPagesBundle/Controller/"
    type: annotation
    prefix: /help
```

Step 4: Add the Bundle to the Assetic Configuration
---------------------------------------------------

In your app/config/config.yml add the Bundle to the Assetic config.

```yml
assetic:
    bundles:        [AppBundle, LorenzschaefHelpPagesBundle, ...]

```

Step 4: Change the Base Directory (optional)
---------------------------------------------------------------------------

By default, the bundle looks for your Help Pages in app/Resources/HelpPages. You can change this in your config like so:


```yml
lorenzschaef_help_pages:
    basedir: your/directory
```

The directory is specified relative to your AppKernel.php, which normally resides in the app folder.



Usage
=====

Add your Documents
------------------

Put your Help Pages in the configured folder (app/Resources/HelpPages by default). The files have to end with .md and can be organized in subfolders. To define the sorting order, you may prepend a number followed by an underscore to the filename.

```
|- 01_gettingstarted.md
|- 02_basics
    |- index.md
    |- 01_accountcreation.md
    |- 02_profile.md
  ...
```

These pages can then be accessed through the following URLs:

```
http:://www.example.com/help/gettingstarted
http:://www.example.com/help/basics                     <- This displays the index.md
http:://www.example.com/help/basics/accountcreation
http:://www.example.com/help/basics/profile
```

The menu generator looks for the first level one title of each document to display it as the link text. Currently only the syntax with the hash (#) is supported. If it can't find any level 1 title in the document, it will use the string "Untitled".

Change the Appearance
---------------------

The original templates are located in the bundle folder under Resources/views.

- layout.html.twig: defines the general layout.
- toc.html.twig: defines a twig macro that renders the table of contents which is used in layout.html.twig

In order to override one of these, just put a template with the same name in app/Resources/LorenzschaefHelpPagesBundle/views.

