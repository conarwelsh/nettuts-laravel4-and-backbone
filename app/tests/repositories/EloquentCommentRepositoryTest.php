<?php

class EloquentCommentRepositoryTest extends TestCase {
	
	public function setUp()
	{
		parent::setUp();
		$this->repo = App::make('EloquentCommentRepository');
	}

	public function testFindByIdReturnsModel()
	{
		$comment = $this->repo->findById(1,1);
		$this->assertTrue($comment instanceof Illuminate\Database\Eloquent\Model);
	}

	public function testFindAllReturnsCollection()
	{
		$comments = $this->repo->findAll(1);
		$this->assertTrue($comments instanceof Illuminate\Database\Eloquent\Collection);
	}

	public function testValidatePasses()
	{
		$reply = $this->repo->validate(array(
			'post_id'     => 1,
			'content'     => 'Lorem ipsum Fugiat consectetur laborum Ut consequat aliqua.',
			'author_name' => 'Testy McTesterson'
		));

		$this->assertTrue($reply);
	}

	public function testValidateFailsWithoutContent()
	{
		try {
			$reply = $this->repo->validate(array(
				'post_id'     => 1,
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
				'post_id'     => 1,
				'content'     => 'Lorem ipsum Fugiat consectetur laborum Ut consequat aliqua.'
			));
		}
		catch(ValidationException $expected)
		{
			return;
		}

		$this->fail('ValidationException was not raised');
	}

	public function testValidateFailsWithoutPostId()
	{
		try {
			$reply = $this->repo->validate(array(
				'author_name' => 'Testy McTesterson',
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
		$comment_data = array(
			'content'     => 'Lorem ipsum Fugiat consectetur laborum Ut consequat aliqua.',
			'author_name' => 'Testy McTesterson'
		);

		$comment = $this->repo->store(1, $comment_data);

		$this->assertTrue($comment instanceof Illuminate\Database\Eloquent\Model);
		$this->assertTrue($comment->content === $comment_data['content']);
		$this->assertTrue($comment->author_name === $comment_data['author_name']);
	}

	public function testUpdateSaves()
	{
		$comment_data = array(
			'content' => 'The Content Has Been Updated'
		);

		$comment = $this->repo->update(1, 1, $comment_data);

		$this->assertTrue($comment instanceof Illuminate\Database\Eloquent\Model);
		$this->assertTrue($comment->content === $comment_data['content']);
	}

	public function testDestroySaves()
	{
		$reply = $this->repo->destroy(1,1);
		$this->assertTrue($reply);

		try {
			$this->repo->findById(1,1);
		}
		catch(NotFoundException $expected)
		{
			return;
		}

		$this->fail('NotFoundException was not raised');
	}

	public function testInstanceReturnsModel()
	{
		$comment = $this->repo->instance();
		$this->assertTrue($comment instanceof Illuminate\Database\Eloquent\Model);
	}

	public function testInstanceReturnsModelWithData()
	{
		$comment_data = array(
			'content' => 'some updated content for the comment'
		);

		$comment = $this->repo->instance($comment_data);
		$this->assertTrue($comment instanceof Illuminate\Database\Eloquent\Model);
		$this->assertTrue($comment->content === $comment_data['content']);
	}

}