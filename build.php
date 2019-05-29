<?php
// better set to php.ini phar.readonly = 0
ini_set("phar.readonly", 0);
$pharFile = 'ht.phar';
$shebang = "#!/usr/bin/env php";

// clean up
if (file_exists($pharFile)) unlink($pharFile);
if (file_exists($pharFile . '.gz')) unlink($pharFile . '.gz');

// create phar
$phar = new Phar($pharFile);
// creating our library using whole directory
// start buffering. Mandatory to modify stub.
$phar->startBuffering();

// Get the default stub. You can create your own if you have specific needs
$defaultStub = $phar->createDefaultStub('index.php');

// Adding files
$phar->buildFromDirectory(__DIR__, '/\.php$/');

// Create a custom stub to add the shebang
$stub = "#!/usr/bin/env php " . PHP_EOL .$defaultStub;

// Add the stub
$phar->setStub($stub);

$phar->stopBuffering();

$phar->compress(Phar::GZ);

echo "$pharFile successfully created";