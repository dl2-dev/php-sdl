<?php declare(strict_types=1);

namespace DL2\SDL;

trait JsonSingleton
{
    private static mixed $instance = null;
    private Json $json;

    private function __construct()
    {
        $this->json = new Json();

        if (\defined('static::FILENAME')) {
            $this->json = Json::read(static::FILENAME);
        }
    }

    public function __get(string $who): mixed
    {
        return $this->json->{$who};
    }

    public static function get(string $who, bool $objectAsArray = false): mixed
    {
        /** @var mixed */
        $result = self::getInstance()->{$who};

        if ($objectAsArray) {
            return json_decode(json_encode($result), true);
        }

        return $result;
    }

    public static function getInstance(): self
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function getIterator()
    {
        yield from $this->json;
    }
}
