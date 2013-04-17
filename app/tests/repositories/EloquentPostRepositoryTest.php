<?php

class EloquentPostRepositoryTest extends TestCase {
	
	public function setUp()
	{
		parent::setUp();
		$this->repo = App::make('EloquentPostRepository');
	}

	public function testFindByIdReturnsModel()
	{
		$post = $this->repo->findById(1);
		$this->assertTrue($post instanceof Illuminate\Database\Eloquent\Model);
	}

	public function testFindAllReturnsCollection()
	{
		$posts = $this->repo->findAll();
		$this->assertTrue($posts instanceof Illuminate\Database\Eloquent\Collection);
	}

	public function testValidatePasses()
	{
		$reply = $this->repo->validate(array(
			'title'       => 'This Should Pass',
			'content'     => 'Lorem ipsum Fugiat consectetur laborum Ut consequat aliqua.',
			'author_name' => 'Testy McTesterson'
		));

		$this->assertTrue($reply);
	}

	public function testValidateFailsWithoutTitle()
	{
		try {
			$reply = $this->repo->validate(array(
				'content'     => 'Lorem ipsum Fugiat consectetur laborum Ut consequat aliqua.',
				'author_name' => 'Testy McTesterson'
			));
		}
		catch(ValidationException $expected)
		{
			return;
		}

		$this->fail('ValidationException was not raised');
	}

	public function testValidateFailsWithoutAuthorName()
	{
		try {
			$reply = $this->repo->validate(array(
				'title'       => 'This Should Pass',
				'content'     => 'Lorem ipsum Fugiat consectetur laborum Ut consequat aliqua.'
			));
		}
		catch(ValidationException $expected)
		{
			return;
		}

		$this->fail('ValidationException was not raised');
	}

	public function testStoreReturnsModel()
	{
		$post_data = array(
			'title'       => 'This Should Pass',
			'content'     => 'Lorem ipsum Fugiat consectetur laborum Ut consequat aliqua.',
			'author_name' => 'Testy McTesterson'
		);

		$post = $this->repo->store($post_data);

		$this->assertTrue($post instanceof Illuminate\Database\Eloquent\Model);
		$this->assertTrue($post->title === $post_data['title']);
		$this->assertTrue($post->content === $post_data['content']);
		$this->assertTrue($post->author_name === $post_data['author_name']);
	}

	public function testUpdateSaves()
	{
		$post_data = array(
			'title' => 'The Title Has Been Updated'
		);

		$post = $this->repo->update(1, $post_data);

		$this->assertTrue($post instanceof Illuminate\Database\Eloquent\Model);
		$this->assertTrue($post->title === $post_data['title']);
	}

	public function testDestroySaves()
	{
		$reply = $this->repo->destroy(1);
		$this->assertTrue($reply);

		try {
			$this->repo->findById(1);
		}
		catch(NotFoundException $expected)
		{
			return;
		}

		$this->fail('NotFoundException was not raised');
	}

	public function testInstanceReturnsModel()
	{
		$post = $this->repo->instance();
		$this->assertTrue($post instanceof Illuminate\Database\Eloquent\Model);
	}

	public function testInstanceReturnsModelWithData()
	{
		$post_data = array(
			'title' => 'Un-validated title'
		);

		$post = $this->repo->instance($post_data);
		$this->assertTrue($post instanceof Illuminate\Database\Eloquent\Model);
		$this->assertTrue($post->title === $post_data['title']);
	}

}