<?php
namespace Spider\Commands;

use Spider\Commands\Languages\ProcessorInterface;

/**
 * Command Builder with sugar, no awareness of connections
 * Optional CommandProcessor
 */
class Builder extends BaseBuilder
{
    /** @var ProcessorInterface Valid, Driver-Specific Command Processor to process Command Bag */
    protected $processor;

    /** @var Command The processed command ready for the driver to execute */
    protected $script;

    /**
     * Creates a new instance of the Command Builder
     * With an optional language processor
     *
     * @param ProcessorInterface $processor
     * @param Bag|null $bag
     */
    public function __construct(
        ProcessorInterface $processor = null,
        Bag $bag = null
    ) {
        parent::__construct($bag);
        $this->processor = $processor;
    }

    /* Fluent Methods for building queries */
    /**
     * Add a `select` clause to the current Command Bag
     *
     * Alias of retrieve
     *
     * @param null $projections
     * @return Builder
     */
    public function select($projections = null)
    {
        return $this->retrieve($projections);
    }

    /**
     * Add a `select` clause to the current Command Bag
     *
     * Alias of retrieve
     *
     * @param array|null $data
     * @return Builder
     */
    public function insert(array $data = null)
    {
        //case of single entry, and case of multiple entries
        if (!is_array($data) || !isset($data[0]) || !is_array($data[0])) {
            //single entry situation.
            $data = [$data];
        }
        return parent::insert($data);
    }

    /**
     * Update only the first record
     * @param null $property
     * @param null $value
     * @return $this
     */
    public function updateFirst($property = null, $value = null)
    {
        $this->bag->command = Bag::COMMAND_UPDATE;
        $this->limit(1);

        return $this->update($property, $value);
    }

    /**
     * Delete a single record
     * @param null $record
     * @return $this|mixed
     */
    public function drop($record = null)
    {
        $this->delete(); // set the delete command

        if (is_array($record)) {
            return $this->records($record);
        }

        if (!is_null($record)) {
            return $this->record($record);
        }

        return $this;
    }

    /**
     * Alias of `data()`
     * @param $property
     * @param null $value
     * @return Builder
     */
    public function withData($property, $value = null)
    {
        return $this->data($property, $value);
    }

    /**
     * Add specific projections to the current Command Bag
     * @param $projections
     * @return Builder
     */
    public function only($projections)
    {
        $this->projections($projections);
        return $this;
    }

    /**
     * Add a record id to the current Command Bag as target
     * @param string|int $id The id of the record
     * @return Builder
     */
    public function record($id)
    {
        if (is_array($id)) {
            return $this->where(Bag::ELEMENT_ID, $id, 'IN');
        }
        return $this->where(Bag::ELEMENT_ID, $id, '=');
    }

    /**
     * Add several records as target
     * @param $ids
     * @return Builder
     */
    public function records($ids)
    {
        return $this->record($ids);
    }

    /**
     * Alias of record
     * Alias of `record()`
     *
     * @param string|int $id The id of the record
     * @return Builder
     */
    public function byId($id)
    {
        return $this->record($id);
    }

    /**
     * Set the target label in the current Command Bag
     * Alias for label
     *
     * @param $label
     * @return $this
     */
    public function from($label)
    {
        return $this->label($label);
    }

    /**
     * Set the target label in the current Command Bag
     * Alias for label
     *
     * @param $label
     * @return $this
     */
    public function label($label)
    {
        return $this->where(Bag::ELEMENT_LABEL, $label, '=');
    }

    /**
     * Alias of label, used for fluency
     * @param $label
     * @return Builder
     */
    public function into($label)
    {
        return $this->label($label);
    }

    /**
     * Add a `where` clause with an `OR` conjunction to the current Command Bag
     *
     * @param string $property Field name
     * @param mixed $value Value matched against
     * @param string $operator From the `self::$operators` array
     * @return $this
     */
    public function orWhere($property, $value = null, $operator = '=')
    {
        return $this->where($property, $value, $operator, 'OR');
    }

    /**
     * Add a `where` clause with an `AND` conjunction to the current Command Bag
     *
     * @param string $property Field name
     * @param mixed $value Value matched against
     * @param string $operator From the `self::$operators` array
     * @return $this
     */
    public function andWhere($property, $value = null, $operator = '=')
    {
        return $this->where($property, $value, $operator, 'AND');
    }

    /* Set limits */
    /**
     * Retrieve the first result by dispatching the current Command Bag.
     * Alias of `one()`
     * @return mixed Command results
     */
    public function first()
    {
        $this->bag->limit = 1;
        return $this;
    }

    /**
     * Set the CommandProcessor
     * @param ProcessorInterface $processor
     */
    public function setProcessor(ProcessorInterface $processor)
    {
        $this->processor = $processor;
    }

    /**
     * Is there a valid processor attached
     * @return bool
     */
    public function hasProcessor()
    {
        return isset($this->processor) && $this->processor instanceof ProcessorInterface;
    }

    /**
     * An an `update` clause to the current command bag
     * @param null $property
     * @param null $value
     * @return $this
     */
    public function update($property = null, $value = null)
    {
        //The is one situation in which we will want to reformat

        // Or, We're adding a single bit of data as well
        if (!is_null($value)) {
            return parent::update([$property => $value]);
        }

        return parent::update($property);
    }
}
