<?php
namespace Everyman\Neo4j\Command;
use Everyman\Neo4j\Command,
	Everyman\Neo4j\Client,
	Everyman\Neo4j\Exception,
	Everyman\Neo4j\PropertyContainer,
	Everyman\Neo4j\Relationship,
	Everyman\Neo4j\Node,
	Everyman\Neo4j\Index;

/**
 * Add an entity to an index
 */
class AddToIndex extends Command
{
	protected $index = null;
	protected $entity = null;
	protected $key = null;
	protected $value = null;

	/**
	 * Set the index to drive the command
	 *
	 * @param Client $client
	 * @param Index $index
	 * @param PropertyContainer $entity
	 * @param string $key
	 * @param string $value
	 */
	public function __construct(Client $client, Index $index, PropertyContainer $entity, $key, $value)
	{
		parent::__construct($client);
		$this->index = $index;
		$this->entity = $entity;
		$this->key = $key;
		$this->value = $value;
	}

	/**
	 * Return the data to pass
	 *
	 * @return mixed
	 */
	protected function getData()
	{
		if (!$this->entity || !$this->entity->hasId()) {
			throw new Exception('No entity to index specified');
		}

		$type = trim((string)$this->index->getType());
		return $this->getTransport()->getEndpoint().'/'.$type.'/'.$this->entity->getId();
	}

	/**
	 * Return the transport method to call
	 *
	 * @return string
	 */
	protected function getMethod()
	{
		return 'post';
	}

	/**
	 * Return the path to use
	 *
	 * @return string
	 */
	protected function getPath()
	{
		$type = trim((string)$this->index->getType());
		if ($type != Index::TypeNode && $type != Index::TypeRelationship) {
			throw new Exception('No type specified for index');
		} else if ($type == Index::TypeNode && !($this->entity instanceof Node)) {
			throw new Exception('Cannot add a node to a non-node index');
		} else if ($type == Index::TypeRelationship && !($this->entity instanceof Relationship)) {
			throw new Exception('Cannot add a relationship to a non-relationship index');
		}

		$name = trim((string)$this->index->getName());
		if (!$name) {
			throw new Exception('No name specified for index');
		}

		$key = trim((string)$this->key);
		if (!$key) {
			throw new Exception('No key specified to add to index');
		}

		$name = urlencode($name);
		$key = urlencode($key);
		$value = urlencode($this->value);

		return '/index/'.$type.'/'.$name.'/'.$key.'/'.$value;
	}

	/**
	 * Use the results
	 *
	 * @param integer $code
	 * @param array   $headers
	 * @param array   $data
	 * @return integer on failure
	 */
	protected function handleResult($code, $headers, $data)
	{
		if ((int)($code / 100) == 2) {
			return null;
		}
		return $code;
	}
}

