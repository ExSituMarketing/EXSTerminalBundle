Symfony 2.x TerminalBundle
==========================


Terminal bundle to log all command output regardless of output buffer (echo / print_r / var_dump / ->ln()), lock commands to prevent them from running concurrently, provide command stats (pid, runtime, errors) and log any exceptions / errors in the database. Very useful for controlling commands via web interface when commands are being run via cronjob. Handles legacy code that was ported tyo your symfony2 app quick and dirty.

Bundle is installed as a service listening for all exceptions and logging them in the database. Locks use the command alias as the slug for ease of reference. When used in conjunction with a crontab enabled monitor we can then alert the dev team when there are errors, current lock hits, and review the logs via web interface instead of having to login to the particular host that had the problem.

*Options*

```
--output (-o)          Output type (console, db (default: "console")
--lock=true (-l)       Whether or not to lock the console command
```


## Installing the TerminalBundle in a new Symfony2 project
So the TerminalBundle is ready for installation, great news but how do we install it.  The installation process is actually very simple.  Set up a new Symfony2 project with Composer.

Once the new project is set up, open the composer.json file and add the exs/terminal-bundle as a dependency:
``` js
//composer.json
//...
"require": {
        //other bundles
        "exs/terminal-bundle": "dev-master"
```
Save the file and have composer update the project via the command line:
``` shell
php composer.phar update
```
Composer will now update all dependencies and you should see our bundle in the list:
``` shell
  - Installing exs/terminal-bundle (dev-master 0c644b1)
    Cloning 0c644b1315e75cd4ff521eeea0ee3243faad0ce3
```

Update the app/AppKernel.php and app/config/routing.yml to include our bundle, clear the cache and update the schema:
``` php
//app/AppKernel.php
//...
    public function registerBundles()
    {
        $bundles = array(
            //Other bundles
            new EXS\TerminalBundle\EXSTerminalBundle()
        );
```
```
#app/config/parameters.yml
#...
    exs.emails.error.from: sender@example.com
    exs.emails.error.to: receiver@example.com
    exs.emails.error.subject: Terminal Error
```
``` shell
php app/console cache:clear
php app/console doctrine:schema:update --force
```
Lastly, copy the including bash file: "runner" from the vendor/exs/terminal-bundle/EXS/TerminalBundle/runner to app/runner
That adds the hooks into the original console script without altering the original.

and now you're done.

If you want to test them try opening up the same command in two terminals simultaneously. They currently run for 3 seconds each:

``` shell
php app/runner terminal:test:lock_sleep --lock=true -o=db
php app/runner terminal:test:lock_sleep --lock=true -o=db
```

Once you're run the above command you will be able to verify everything worked by checking the database. The locks and logs will be logged in your database in the following tables: 

* terminallogs

* commandlocks

Additionally if your command exited with a non 0 status it likely logged an exception in the exception5xx table. 


#### Contributing ####
Anyone and everyone is welcome to contribute.

If you have any questions or suggestions please [let us know][1].


[1]: http://www.ex-situ.com/
