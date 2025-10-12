<?php
/**
 * Patami IPS Framework
 *
 * @package IPSPATAMI
 * @version 3.4
 * @link https://bitbucket.org/patami/ipspatami
 *
 * @author Florian Wiethoff <florian.wiethoff@patami.com>
 * @copyright 2017 Florian Wiethoff
 *
 * @license GPL
 */

namespace Patami\IPS\Services\Alexa\Skills\Custom;

/**
 * Abstract base class to handle intent slots and session key-value pairs in Alexa Custom Skill requests and responses.
 *
 * It offers attribute access ($data->key) and array access ($data['key']) as well as an iterator interface (eg. to
 * use foreach on the object to process all key-value pairs).
 *
 * @package IPSPATAMI
 */
abstract class RequestData implements \ArrayAccess, \Iterator
{
    /** @var array Key-value pairs with the request data. */
    protected $data = array();

    /**
     * RequestData constructor.
     * @param array $data Key-value pairs with the request data.
     */
    public function __construct(array $data = array())
    {
        $this->data = $data;
    }

    /**
     * Magic method which returns the value from the request data with the specified key.
     * @param string $name Key name.
     * @return string Value of the request data.
     */
    public function &__get($name)
    {
        return $this->data[$name];
    }

    /**
     * Magic method which updates or adds a value in the request data with the specified key.
     * @param string $name Key name.
     * @param mixed $value New value.
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * Magic method which checks whether a key in the request data array exists.
     * @param string $name Key name.
     * @return bool True if the key exists.
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * Magic method to unset (remove) a key-value pair from the request data array.
     * @param string $name Key name.
     */
    public function __unset($name)
    {
        unset($this->data[$name]);
    }

    /**
     * Sets the value at a specified index in the request data array.
     * This method is used for the Iterator interface.
     * @param int|null $offset Array index or null to add a new element to the end of the array.
     * @param string $value New value.
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    /**
     * Checks if the specified index exists in the request data array.
     * This method is used for the Iterator interface.
     * @param int $offset Array index.
     * @return bool True if the key-value pair at the specified index exists.
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * Unsets (removes) the key-value pair at the specified index in the request data array.
     * This method is used for the Iterator interface.
     * The array indices will be renumbered after removing the key-value pair.
     * @param int $offset Array index.
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset($this->data[$offset]);
        }
    }

    /**
     * Returns the value at the specified index in the request data array.
     * This method is used for the Iterator interface.
     * @param int $offset Array index.
     * @return string|null Value at the specified index or null if there is no such index.
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->data[$offset] : null;
    }

    /**
     * Sets the current index / element of the request array to the first element.
     * This method is used for the Iterator interface.
     * @return string|false Value of the first array element or false if the array is empty.
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        return reset($this->data);
    }

    /**
     * Returns the value of key-value pair at the current index of the request array.
     * This method is used for the Iterator interface.
     * @return string|false Value of the current key-value pair or false if the array is empty.
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return current($this->data);
    }

    /**
     * Returns the key of key-value pair at the current index of the request array.
     * This method is used for the Iterator interface.
     * @return string|false Key of the current key-value pair or false if the array is empty.
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return key($this->data);
    }

    /**
     * Advances the current index / element of the request data array to the next element.
     * This method is used for the Iterator interface.
     * @return string|false Value of the next key-value pair or false if the array is empty or if there are no more elements.
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        return next($this->data);
    }

    /**
     * Checks if the current key-value pair is valid (ie. the key is not null).
     * @return bool True if the current key-value pair is valid.
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        return !is_null(key($this->data));
    }

    /**
     * Returns the request data array.
     * @return array Key-value pairs.
     */
    public function Get()
    {
        return $this->data;
    }

    /**
     * Returns the request data array as JSON-encoded string.
     * @return string JSON-encoded key-value pairs.
     */
    public function GetAsJSON()
    {
        return json_encode($this->data);
    }
}
