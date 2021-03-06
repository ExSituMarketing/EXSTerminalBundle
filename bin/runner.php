<?php

// THIS FILE CONTAINS THE MODIFIED CONSOLE TO WORK WITH ConsoleBundle
// MAKE SURE TO UPDATE YOUR app/console with this code
// if you don't want to setup permissions the proper way, just uncomment the following PHP line
// read http://symfony.com/doc/current/book/installation.html#configuration-and-setup for more information

set_time_limit(0);

require_once __DIR__ . '/../../../../../../app/bootstrap.php.cache';
require_once __DIR__ . '/../../../../../../app/AppKernel.php';

use EXS\TerminalBundle\Services\Output\TerminalOutput;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Debug\Debug;

$input = new ArgvInput();
$env = $input->getParameterOption(array('--env', '-e'), getenv('SYMFONY_ENV') ? : 'dev');
$debug = getenv('SYMFONY_DEBUG') !== '0' && !$input->hasParameterOption(array('--no-debug', '')) && $env !== 'prod';

if ($debug) {
    Debug::enable();
}

$kernel = new AppKernel($env, $debug);
$application = new Application($kernel);

// Added for ConsoleBundle
$inputDefinition = $application->getDefinition();

//get input definition to add --output option.
$inputDefinition->addOption(
    new InputOption(
        'output', 'o', InputOption::VALUE_OPTIONAL, 'Output type (console, db', 'console'
    )
);

//add --output option.
$outputArg = $input->getParameterOption(array('--output', '-o'));

//depend on the output option value. use the output
if ($outputArg == 'db') {
    // DB Logging
    //remove autoexit to be able to save output.
    $application->setAutoExit(false);

    //We never want interactive mode with this output.
    $input->setInteractive(false);

    //boot the kernel to be able to access service container.
    $kernel->boot();

    //Get container.
    $container = $kernel->getContainer();

    //create output object with output manager service.
    $output = new TerminalOutput($container->get('terminal.output.manager'));

    // Does the app need the buffer captured
    ob_start();
} else {
    // Normal
    $output = new ConsoleOutput();
}

// Run the App
$application->run($input, $output);

// Save the output
if ($outputArg == 'db') {
    $screen = ob_get_contents();
    ob_end_clean();
    $output->write($screen);
    $output->write('Done');
}

$output->save();
//End ConsoleBundle
