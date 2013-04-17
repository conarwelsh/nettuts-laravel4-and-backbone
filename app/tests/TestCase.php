<?php

class TestCase extends Illuminate\Foundation\Testing\TestCase {

	public function setUp()
	{
		parent::setUp();
		$this->seed();
	}

	public function tearDown()
	{
		Mockery::close();
	}

	/**
	 * Creates the application.
	 *
	 * @return Symfony\Component\HttpKernel\HttpKernelInterface
	 */
	public function createApplication()
	{
		$unitTesting = true;

		$testEnvironment = 'testing';

		return require __DIR__.'/../../bootstrap/start.php';
	}

}
