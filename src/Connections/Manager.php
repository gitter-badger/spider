<?php
namespace Spider\Connections;

use Michaels\Manager\Contracts\ManagesItemsInterface;
use Michaels\Manager\Exceptions\ItemNotFoundException;
use Michaels\Manager\Manager as ConfigManager;
use Spider\Base\Collection;
use Spider\Base\ConfigurableInterface;
use Spider\Base\ConfigurableTrait;
use Spider\Exceptions\ConnectionNotFoundException;
use Spider\Integrations\Events\DispatcherInterface;
use Spider\Integrations\Events\UsesEventsTrait;

/**
 * Manages and Builds Connections from a stored list
 * @package Spider\Connections
 */
class Manager extends Collection implements ManagesItemsInterface, ConfigurableInterface
{
    use ConfigurableTrait, UsesEventsTrait;

    /**
     * @inherits from Collection:
     *      init(), add(), get(), getAll(), exists(), has(), set(),
     *      remove(), clear(), toJson, isEmpty(), __toString()
     */

    /**
     * Build a new manager instance
     *
     * @param array $connections
     * @param DispatcherInterface $events
     * @param \Michaels\Manager\Manager $config
     */
    public function __construct($connections = [], $config = null, DispatcherInterface $events = null)
    {
        // Connections and credentials
        $this->initManager($connections);

        // Optional configuration (to be passed to connections)
        $this->setConfigManager($config);

        // Optional Event Dispatcher
        $this->setDispatcher($events);
    }

    /**
     * Builds, Caches, and Returns a Connection
     *
     * Hand null for default Connection
     * Hand alias for stored Connection
     * Hand array for implicit Connection
     *
     * @param string|null $alias alias | properties | default
     * @return Connection
     * @throws \Spider\Exceptions\ConnectionNotFoundException
     */
    public function make($alias = null)
    {
        $this->emit('connections.manager.before_make');

        // We were handed the properties for a Connection
        if (is_array($alias)) {
            return $this->buildConnection($alias);
        }

        // We need to build a Connection from configuration
        $alias = (string)$alias ?: $this->getDefault();

        // Verify the connection properties are set
        if (!$this->has($alias)) {
            throw new ConnectionNotFoundException("$alias has not been registered");
        }

        // Produce and cache the Connection
        $connection = $this->buildConnection($alias);
        $this->cache($alias, $connection);

        $this->emit('connections.manager.after_make');

        return $connection;
    }

    /**
     * Returns cached connection or makes a new one
     * See make() for details
     *
     * @param string|array|null $alias alias | properties | default
     * @return Connection
     * @throws ConnectionNotFoundException
     */
    public function fetch($alias = null)
    {
        // We were handed the properties for a Connection
        if (is_array($alias)) {
            return $this->buildConnection($alias);
        }

        // We need to build a Connection from configuration
        $alias = $alias ?: $this->getDefault();
        if ($this->has("cache.$alias")) {
            return $this->get("cache.$alias");
        }

        return $this->make($alias);
    }

    /**
     * Clears connection cache
     * @return $this
     */
    public function clearCache()
    {
        $this->set('cache', []);
        return $this;
    }

    /**
     * Build and returns the actual connection object
     *
     * @param $properties
     * @return Connection
     * @throws ItemNotFoundException
     */
    protected function buildConnection($properties)
    {
        // Get properties from stored, if needed
        if (is_string($properties)) {
            $properties = $this->get($properties);
        }

        // Extract the driver
        if (!isset($properties['driver'])) {
            throw new ConnectionNotFoundException("There is no driver set in the Connection parameters");
        }

        $diverClassName = $properties['driver'];
        unset($properties['driver']);

        return new Connection($diverClassName, $properties, $this->config(), $this->getDispatcher());
    }

    /**
     * Checks for and builds the connection name
     *
     * Will return the default connection name if none is supplied
     * Will throw and exception if the connection requested does not exist
     * @return string The alias
     * @throws ConnectionNotFoundException
     */
    protected function getDefault()
    {
        // Set the default connection
        try {
            $alias = $this->get('default');
        } catch (ItemNotFoundException $e) {
            throw new ConnectionNotFoundException("There is no default connection set");
        }

        return $alias;
    }

    /**
     * Caches a built Connection
     * @param string $alias
     * @param Connection $connection
     */
    protected function cache($alias, $connection)
    {
        $this->add("cache.$alias", $connection);
    }
}
