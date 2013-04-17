<?php

class EloquentCommentRepository implements CommentRepositoryInterface {

	public function findById($post_id, $id)
	{
		$comment = Comment::find($id);
		if(!$comment || $comment->post_id != $post_id) throw new NotFoundException('Comment Not Found');
		return $comment;
	}

	public function findAll($post_id)
	{
		return Comment::where('post_id', $post_id)
			->orderBy('created_at', 'desc')
			->get();
	}

	public function store($post_id, $data)
	{
		$data['post_id'] = $post_id;
		$this->validate($data);
		return Comment::create($data);
	}

	public function update($post_id, $id, $data)
	{
		$comment = $this->findById($post_id, $id);
		$comment->fill($data);
		$this->validate($comment->toArray());
		$comment->save();
		return $comment;
	}

	public function destroy($post_id, $id)
	{
		$comment = $this->findById($post_id, $id);
		$comment->delete();
		return true;
	}

	public function validate($data)
	{
		$validator = Validator::make($data, Comment::$rules);
		if($validator->fails()) throw new ValidationException($validator);
		return true;
	}

	public function instance($data = array())
	{
		return new Comment($data);
	}

}