<?php

namespace Attendance;

class Collection
{
	protected $collection = [];

	public function __construct($collection = [])
	{
		if (is_array($collection)) {
			$this->add($collection);
		}
	}

	public function add($key, $value = '')
	{
		if (is_array($key)) {
			$collection = $key;
			foreach ($collection as $key => $value) {
				$this->collection[$key] = is_array($value) ? new static($value) : $value;
			}
		} else {
			$this->collection[$key] = is_array($value) ? new static($value) : $value;
		}

		return $this;
	}

	public function unset($key)
	{
		unset($this->collection[$key]);
	}

	public function delete($key)
	{
		$this->unset($key);
	}

	public function remove($key)
	{
		$this->unset($key);
	}

	public function toArray()
	{
		return $this->collection;
	}

	public function array()
	{
		return $this->toArray();
	}

	public function empty()
	{
		return empty($this->collection);
	}

	public function count()
	{
		return count($this->collection);
	}

	public function dump()
	{
		Dump($this->collection);

		return $this;
	}
}
