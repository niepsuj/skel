<?php

namespace Skel\Tests\Project;

use PHPUnit\Framework\TestCase;
use Silex\Application;
use Skel\Application\ProjectTrait;
use Skel\Event\GenericEvent;
use Skel\Event\CleanupEvent;
use Skel\ProjectEvents;
use Skel\ProjectProvider;

class ProjectTraitTest extends TestCase
{
    public function testTrigger()
    {
        $triggered = false;

        $app = new TestApplicationWithProjectTrait();
        $app->on('some.event', function($event) use (&$triggered){
            $this->assertTrue($event instanceof GenericEvent);
            $this->assertArrayHasKey('test', $event);
            $this->assertEquals(1, $event['test']);

            $triggered = true;
        });

        $app->trigger('some.event', ['test' => 1]);
        $this->assertTrue($triggered);
    }

    public function testCleanup()
    {
        $triggered = false;

        $app = new TestApplicationWithProjectTrait();
        $app['cleanup.scope'][] = 'test';
        $app->on(ProjectEvents::CLEANUP, function($event) use (&$triggered){
            $this->assertTrue($event instanceof CleanupEvent);
            $this->assertTrue($event->can('test'));
            $event->report('test');
            $triggered = true;
        });

        $result = $app->cleanup();
        $this->assertTrue($triggered);
        $this->assertTrue($result->success('test'));
    }
}

class TestApplicationWithProjectTrait extends Application
{
    use ProjectTrait;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->register(new ProjectProvider);
    }
}