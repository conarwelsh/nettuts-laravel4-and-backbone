<?php

interface CommentRepositoryInterface {
	public function findById($post_id, $id);
	public function findAll($post_id);
	public function store($post_id, $data);
	public function update($post_id, $id, $data);
	public function destroy($post_id, $id);
	public function validate($data);
	public function instance();
}