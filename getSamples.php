<?php

echo 'Checking samples directory...'.PHP_EOL;

$samplesPath = __DIR__.'/samples/';
if(!file_exists($samplesPath)) {
    mkdir($samplesPath);
}

foreach(glob($samplesPath.'*') as $file) {
    unlink($file);
}

$samples = [
    'test-empty.jpg',
    'test-medium.jpg',
    'test-small.gif',
    'test-small.jpg',
    'test-small.png',
    'test.gif',
    'test.jpg',
    'test.png',
    'test2.jpg',
    'test3.jpg',
    'test4.jpg',
    'test5.jpg',
    'test6.jpg',
    'test7.jpg'
];

foreach($samples as $sample) {
    echo 'Downloading '.$sample.'...';
    file_put_contents($samplesPath.$sample, file_get_contents('https://colortools.weanswer.it/samples/'.$sample));
    echo ' Done'.PHP_EOL;
}

echo 'Creating symlinks'.PHP_EOL;

symlink($samplesPath.'test.jpg', $samplesPath.'test-just-a-file');
symlink($samplesPath.'test.jpg', $samplesPath.'test-wrong-ext.png');

echo 'All done.'.PHP_EOL;



