<?php

class PostsControllerTest extends TestCase {

	/**
	 * Test Basic Route Responses
	 */
	public function testIndex()
	{
		$response = $this->call('GET', route('v1.posts.index'));
		$this->assertTrue($response->isOk());
	}

	public function testShow()
	{
		$response = $this->call('GET', route('v1.posts.show', array(1)));
		$this->assertTrue($response->isOk());
	}

	public function testCreate()
	{
		$response = $this->call('GET', route('v1.posts.create'));
		$this->assertTrue($response->isOk());
	}

	public function testEdit()
	{
		$response = $this->call('GET', route('v1.posts.edit', array(1)));
		$this->assertTrue($response->isOk());
	}

	/**
	 * Test that controller calls repo as we expect
	 */
	public function testIndexShouldCallFindAllMethod()
	{
		$mock = Mockery::mock('PostRepositoryInterface');
		$mock->shouldReceive('findAll')->once()->andReturn('foo');
		App::instance('PostRepositoryInterface', $mock);

		$response = $this->call('GET', route('v1.posts.index'));
		$this->assertTrue(!! $response->original);
	}

	public function testShowShouldCallFindById()
	{
		$mock = Mockery::mock('PostRepositoryInterface');
		$mock->shouldReceive('findById')->once()->andReturn('foo');
		App::instance('PostRepositoryInterface', $mock);

		$response = $this->call('GET', route('v1.posts.show', array(1)));
		$this->assertTrue(!! $response->original);
	}

	public function testCreateShouldCallInstanceMethod()
	{
		$mock = Mockery::mock('PostRepositoryInterface');
		$mock->shouldReceive('instance')->once()->andReturn(array());
		App::instance('PostRepositoryInterface', $mock);

		$response = $this->call('GET', route('v1.posts.create'));
		$this->assertViewHas('post');
	}

	public function testEditShouldCallFindByIdMethod()
	{
		$mock = Mockery::mock('PostRepositoryInterface');
		$mock->shouldReceive('findById')->once()->andReturn(array());
		App::instance('PostRepositoryInterface', $mock);

		$response = $this->call('GET', route('v1.posts.edit', array(1)));
		$this->assertViewHas('post');
	}

	public function testStoreShouldCallStoreMethod()
	{
		$mock = Mockery::mock('PostRepositoryInterface');
		$mock->shouldReceive('store')->once()->andReturn('foo');
		App::instance('PostRepositoryInterface', $mock);

		$response = $this->call('POST', route('v1.posts.store'));
		$this->assertTrue(!! $response->original);
	}

	public function testUpdateShouldCallUpdateMethod()
	{
		$mock = Mockery::mock('PostRepositoryInterface');
		$mock->shouldReceive('update')->once()->andReturn('foo');
		App::instance('PostRepositoryInterface', $mock);

		$response = $this->call('PUT', route('v1.posts.update', array(1)));
		$this->assertTrue(!! $response->original);
	}

	public function testDestroyShouldCallDestroyMethod()
	{
		$mock = Mockery::mock('PostRepositoryInterface');
		$mock->shouldReceive('destroy')->once()->andReturn(true);
		App::instance('PostRepositoryInterface', $mock);

		$response = $this->call('DELETE', route('v1.posts.destroy', array(1)));
		$this->assertTrue( empty($response->original) );
	}

}