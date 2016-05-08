<?php

require_once '../vendor/autoload.php';

use Symfony\Component\Console\Output\OutputInterface;

$app = new Silex\Application();
$app->register(new Skel\ProjectProvider, 	[ ] );
$app->register(new Skel\ViewProvider, 		[ 'view.path' => __DIR__.'/views' ] );
$app->register(new Skel\ConsoleProvider, 	[ ] );

$app['controllers']->command('test1 -very-long-option|o', function($veryLongOption, OutputInterface $output){
	if($veryLongOption){
		$output->writeln('<info>Hello!<info>');
	}
})->description('Comand with option')->info('very-long-option', 'Option with no value');

$app['controllers']->command('test2 {argument}', function($argument, OutputInterface $output){
	$output->writeln('<info>'.$argument.'<info>');	
})
->description('test2 command')
->info('argument', 'Test argument')
->value('argument', 'default value');

$app['console.run']();