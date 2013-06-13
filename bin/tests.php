<?php

use Testes\Coverage\Coverage;
use Testes\Finder\Finder;
use Testes\Autoloader;

$base = __DIR__ . '/..';

require $base . '/vendor/treshugart/testes/src/Testes/Autoloader.php';

Autoloader::register();
Autoloader::addPath($base . '/tests');

$coverage = (new Coverage)->start();
$suite    = (new Finder($base . '/tests', 'Test'))->run();
$analyzer = $coverage->stop()->addDirectory($base . '/src')->is('\.php$');

?>

Coverage: <?php echo $analyzer->getPercentTested(); ?>%

<?php if ($suite->getAssertions()->isPassed()): ?>
All tests passed!
<?php else: ?>
Tests failed:
<?php foreach ($suite->getAssertions()->getFailed() as $ass): ?>
  <?php echo $ass->getTestClass() . '::' . $ass->getTestMethod() . '(' . $ass->getTestLine() . ') - ' . $ass->getMessage(); ?>

<?php endforeach; ?>
<?php endif; ?>

<?php if ($suite->getExceptions()->count()): ?>
Exceptions:

<?php foreach ($suite->getExceptions() as $exc): ?>
<?php echo $exc->getMessage(); ?>
<?php echo $exc->getException()->getTraceAsString(); ?>

<?php endforeach; ?>

<?php endif; ?>
