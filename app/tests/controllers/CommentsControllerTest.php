<?php

class CommentsControllerTest extends TestCase {

	/**
	 * Basic Route Tests
	 * notice that we can use our route() helper here!
	 */
	public function testIndex()
	{
		$response = $this->call('GET', route('v1.posts.comments.index', array(1)) );
		$this->assertTrue($response->isOk());
	}

	public function testShow()
	{
		$response = $this->call('GET', route('v1.posts.comments.show', array(1,1)) );
		$this->assertTrue($response->isOk());
	}

	public function testCreate()
	{
		$response = $this->call('GET', route('v1.posts.comments.create', array(1)) );
		$this->assertTrue($response->isOk());
	}

	public function testEdit()
	{
		$response = $this->call('GET', route('v1.posts.comments.edit', array(1,1)) );
		$this->assertTrue($response->isOk());
	}

	/**
	 * Test that the controller calls repo as we expect
	 * notice we are Mocking our repository
	 * also notice that we do not really care about the data or interactions
	 * we merely care that the controller is doing what we are going to want
	 * it to do, which is reach out to our repository for more information
	 */
	public function testIndexShouldCallFindAllMethod()
	{
		$mock = Mockery::mock('CommentRepositoryInterface');
		$mock->shouldReceive('findAll')->once()->andReturn('foo');
		App::instance('CommentRepositoryInterface', $mock);

		$response = $this->call('GET', route('v1.posts.comments.index', array(1)));
		$this->assertTrue(!! $response->original);
	}

	public function testShowShouldCallFindById()
	{
		$mock = Mockery::mock('CommentRepositoryInterface');
		$mock->shouldReceive('findById')->once()->andReturn('foo');
		App::instance('CommentRepositoryInterface', $mock);

		$response = $this->call('GET', route('v1.posts.comments.show', array(1,1)));
		$this->assertTrue(!! $response->original);
	}

	public function testCreateShouldCallInstanceMethod()
	{
		$mock = Mockery::mock('CommentRepositoryInterface');
		$mock->shouldReceive('instance')->once()->andReturn(array());
		App::instance('CommentRepositoryInterface', $mock);

		$response = $this->call('GET', route('v1.posts.comments.create', array(1)));
		$this->assertViewHas('comment');
	}

	public function testEditShouldCallFindByIdMethod()
	{
		$mock = Mockery::mock('CommentRepositoryInterface');
		$mock->shouldReceive('findById')->once()->andReturn(array());
		App::instance('CommentRepositoryInterface', $mock);

		$response = $this->call('GET', route('v1.posts.comments.edit', array(1,1)));
		$this->assertViewHas('comment');
	}

	public function testStoreShouldCallStoreMethod()
	{
		$mock = Mockery::mock('CommentRepositoryInterface');
		$mock->shouldReceive('store')->once()->andReturn('foo');
		App::instance('CommentRepositoryInterface', $mock);

		$response = $this->call('POST', route('v1.posts.comments.store', array(1)));
		$this->assertTrue(!! $response->original);
	}

	public function testUpdateShouldCallUpdateMethod()
	{
		$mock = Mockery::mock('CommentRepositoryInterface');
		$mock->shouldReceive('update')->once()->andReturn('foo');
		App::instance('CommentRepositoryInterface', $mock);

		$response = $this->call('PUT', route('v1.posts.comments.update', array(1,1)));
		$this->assertTrue(!! $response->original);
	}

	public function testDestroyShouldCallDestroyMethod()
	{
		$mock = Mockery::mock('CommentRepositoryInterface');
		$mock->shouldReceive('destroy')->once()->andReturn(true);
		App::instance('CommentRepositoryInterface', $mock);

		$response = $this->call('DELETE', route('v1.posts.comments.destroy', array(1,1)));
		$this->assertTrue( empty($response->original) );
	}


}