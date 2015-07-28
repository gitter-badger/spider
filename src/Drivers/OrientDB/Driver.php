<?php
namespace Spider\Drivers\OrientDB;

use PhpOrient\PhpOrient;
use PhpOrient\Protocols\Binary\Data\Record;
use PhpOrient\Protocols\Binary\Data\Record as OrientRecord;
use Spider\Commands\CommandInterface;
use Spider\Drivers\AbstractDriver;
use Spider\Drivers\DriverInterface;
use Spider\Drivers\Response;
use Spider\Exceptions\FormattingException;
use Spider\Graphs\Graph;
use Spider\Graphs\Record as SpiderRecord;

/**
 * Driver for Native OrientDB (not using gremlin)
 * @package Spider\Drivers\OrientDB
 */
class Driver extends AbstractDriver implements DriverInterface
{
    /* Driver Credentials */
    /** @var  string OrientDB server hostname */
    protected $hostname;

    /** @var  int OrientDB server port */
    protected $port;

    /** @var  string OrientDB username for specified database */
    protected $username;

    /** @var  string Password for current OrientDB user */
    protected $password;

    /** @var  string Database name */
    protected $database;

    /* Internals */
    /** @var PhpOrient Language Binding */
    protected $client;

    /** @var  bool Is connection open, flag */
    protected $isOpen = false;

    protected $formatMessage = "The response from the database was incorrectly formatted for this operation";

    /**
     * Create a new instance with a client
     * @param array $properties Configuration properties
     */
    public function __construct(array $properties = [])
    {
        // Populate configuration
        parent::__construct($properties);

        // Initialize the language binding client
        $this->client = new PhpOrient();
    }

    /**
     * Connect to the database using already set, internal credentials
     * @return $this
     */
    public function open()
    {
        $config = [];
        foreach ($this as $property => $value) {
            if ($property !== 'client') {
                $config[$property] = $value;
            }
        }

        $this->client->configure($config);
        $this->client->connect();
        $this->client->dbOpen($config['database']); // What if I *want* the cluster map?

        // Flag as an open connection
        $this->isOpen = true;
    }

    /**
     * Close the database connection
     * @return $this
     */
    public function close()
    {
        if ($this->isOpen) {
            $this->client->dbClose(); // returns int
            $this->isOpen = false;
        }

        return $this;
    }

    /**
     * Opens a transaction
     * @return bool
     * @throws \Exception
     */
    public function startTransaction()
    {
        throw new \Exception(__FUNCTION__ . " is not currently supported for OrientDB driver");
    }

    /**
     * Closes a transaction
     *
     * @param bool $commit whether this is a commit (TRUE) or a rollback (FALSE)
     * @return bool
     * @throws \Exception
     */
    public function stopTransaction($commit = TRUE)
    {
        throw new \Exception(__FUNCTION__ . " is not currently supported for OrientDB driver");
    }

    /**
     * Executes a Query or read command
     *
     * @param CommandInterface $query
     * @return array|Record|Graph
     */
    public function executeReadCommand(CommandInterface $query)
    {
        return $this->executeCommand($query, 'query');
    }

    /**
     * Executes a write command
     *
     * These are the "CUD" in CRUD
     *
     * @param CommandInterface $command
     * @return Graph|Record|array|mixed mixed values for some write commands
     */
    public function executeWriteCommand(CommandInterface $command)
    {
        /* ToDo: DELETE is very sloppy */
        /* DELETE VERTEX returns an int. DELETE returns either int or before Record */
        /* Drivers expect an empty array upon successful delete */
        /* This needs to be reconciled in a better way */
        $response = $this->executeCommand($command, 'command');

        if (strpos(strtolower($command->getScript()), "delete") === 0) {
            return new Response(['_raw' => [], '_driver' => $this]);
        }

        return $response;
    }

    /**
     * Executes actual command or query
     * @param CommandInterface $command
     * @param $method
     * @return Response
     */
    protected function executeCommand(CommandInterface $command, $method)
    {
        $response = $this->client->$method($command->getScript());
        $response = $this->rawResponseToArray($response);
        return new Response(['_raw' => $response, '_driver' => $this]);
    }

    /**
     * Executes a read command without waiting for a response
     *
     * @param CommandInterface $query
     * @return $this
     */
    public function runReadCommand(CommandInterface $query)
    {
        $this->client->query($query->getScript());
        return $this;
    }

    /**
     * Executes a write command without waiting for a response
     *
     * @param CommandInterface $command
     * @return $this
     */
    public function runWriteCommand(CommandInterface $command)
    {
        $this->client->command($command->getScript());
        return $this;
    }

    /**
     * Map a raw response to a SpiderResponse
     * @param $response
     * @return SpiderRecord
     */
    protected function mapRawResponse(array $response)
    {
        // Return an empty array immediately
        if (empty($response)) {
            return $response;
        }

        // Receive array with single scalar
        if (!$response[0] instanceof Record) {
            return $response[0];
        }

        // For multiple records, map each to a Record
        array_walk($response, function (&$orientRecord) {
            $orientRecord = $this->mapOrientRecordToCollection($orientRecord);
        });

        return $response;
    }

    /**
     * Hydrate a SpiderRecord from an OrientRecord
     *
     * @param $orientRecord
     * @return SpiderRecord
     */
    protected function mapOrientRecordToCollection(OrientRecord $orientRecord)
    {
        // Or we map a single record to a Spider Record
        $collection = new \Spider\Base\Collection($orientRecord->getOData());

        $collection->add([
            'id' => $orientRecord->getRid()->jsonSerialize(),
            'label' => $orientRecord->getOClass(),

            'meta.rid' => $orientRecord->getRid(),
            'meta.version' => $orientRecord->getVersion(),
            'meta.oClass' => $orientRecord->getOClass(),
        ]);

        $collection->protect('id');
        $collection->protect('label');
        $collection->protect('meta');

        return $collection;
    }

    /**
     * Ensures that an OrientDB response is an array,
     * even if only an array of one Record
     * @param $response
     * @return array
     */
    protected function rawResponseToArray($response)
    {
        if (is_array($response)) {
            return $response;
        }

        return [$response];
    }

    /**
     * Format a raw response to a set of collections
     * This is for cases where a set of Vertices or Edges is expected in the response
     *
     * @param mixed $response the raw DB response
     * @return Response Spider consistent response
     * @throws FormattingException
     */
    public function formatAsSet($response)
    {
        $this->canFormatAsSet($response);

        $mapped = $this->mapRawResponse($response);

        if (count($mapped) === 1) {
            return $mapped[0];
        }

        return $mapped;
    }

    /**
     * Throws an exception if formatting invalid data to set
     * @param $response
     * @throws FormattingException
     */
    protected function canFormatAsSet($response)
    {
        if (is_array($response) && isset($response[0]) && !$response[0] instanceof Record) {
            throw new FormattingException();
        }
    }

    /**
     * Format a raw response to a tree of collections
     * This is for cases where a set of Vertices or Edges is expected in tree format from the response
     *
     * @param mixed $response the raw DB response
     *
     * @return Response Spider consistent response
     */
    public function formatAsTree($response)
    {
        // TODO: Implement formatAsTree() method.
    }

    /**
     * Format a raw response to a path of collections
     * This is for cases where a set of Vertices or Edges is expected in path format from the response
     *
     * @param mixed $response the raw DB response
     *
     * @return Response Spider consistent response
     */
    public function formatAsPath($response)
    {
        // TODO: Implement formatAsPath() method.
    }

    /**
     * Format a raw response to a scalar
     * This is for cases where a scalar result is expected
     *
     * @param mixed $response the raw DB response
     * @return Response Spider consistent response
     * @throws FormattingException
     */
    public function formatAsScalar($response)
    {
        // Throw exception if response does not meet the criteria for scalar formatting
        $this->canFormatAsScalar($response);

        // The response is a single record with one property
        if ($response[0] instanceof Record) {
            $scalar = [];
            foreach ($response[0]->getOData() as $key => $value) {
                array_push($scalar, $value);
            }
            return $scalar[0];
        }

        // This is an array with a single scalar value (like number of rows affected)
        return $response[0];
    }

    /**
     * Throws an exception if response cannot be formatted as a scalar
     * @param $response
     * @throws FormattingException
     */
    protected function canFormatAsScalar($response)
    {
        if (count($response) > 1) {
            throw new FormattingException();
        }

        if (!is_string($response[0])
            && !is_bool($response[0])
            && !is_int($response[0])
            && !$response[0] instanceof Record
        ) {
            throw new FormattingException();
        }

        if ($response[0] instanceof Record && count($response[0]->getOData()) !== 1) {
            throw new FormattingException();
        }
    }
}
