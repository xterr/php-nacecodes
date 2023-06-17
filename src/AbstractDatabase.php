<?php

namespace Xterr\NaceCodes;

use Closure;
use Countable;
use Iterator;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @template-implements Iterator<mixed>
 */
abstract class AbstractDatabase implements Iterator, Countable
{
    protected $data = [];

    /**
     * @var string
     */
    private $baseDirectory;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    private $class;

    private $index = [];

    public function __construct(string $baseDirectory, string $class, TranslatorInterface $translator = null)
    {
        $this->baseDirectory = $baseDirectory;
        $this->class = $class;
        $this->translator = $translator;
    }

    public function toArray(): array
    {
        return iterator_to_array($this);
    }

    public function count(): int
    {
        $this->_loadData();

        return count($this->data);
    }

    public function next(): void
    {
        next($this->data);
    }

    public function valid(): bool
    {
        return $this->key() !== null;
    }

    public function key(): ?int
    {
        return key($this->data);
    }

    public function rewind(): void
    {
        $this->_loadData();
        reset($this->data);
    }

    public function current()
    {
        return $this->_arrayToEntry(current($this->data));
    }

    protected function _arrayToEntry(array $entry)
    {
        $class = $this->class;

        $closure = Closure::bind(static function () use ($entry, $class) {
            $instance = new $class();

            foreach (array_keys($entry) as $field) {
                $instance->$field = $entry[$field];
            }

            return $instance;
        }, null, $class);

        return $closure();
    }

    protected function _find(string $indexedFieldName, $value): array
    {
        $this->_buildIndex();

        if (!isset($this->index[$indexedFieldName])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Unknown index "%s" in database "%s"',
                    $indexedFieldName,
                    static::class
                )
            );
        }

        return $this->index[$indexedFieldName][is_array($value) ? implode('_', $value) : $value] ?? [];
    }

    protected function _fileName(): string
    {
        return lcfirst(basename(str_replace('\\', '/', static::class)));
    }

    protected function _getIndexDefinition(): array
    {
        return [];
    }

    private function _buildIndex(): void
    {
        if (!empty($this->index)) {
            return;
        }

        $this->_loadData();

        $indexedFields = $this->_getIndexDefinition();

        if (empty($indexedFields)) {
            return;
        }

        foreach ($this->data as $entryArray) {
            $entry = $this->_arrayToEntry($entryArray);

            foreach ($indexedFields as $indexName => $indexDefinition) {
                if (!isset($this->index[$indexName])) {
                    $this->index[$indexName] = [];
                }

                $indexDefinition = is_array($indexDefinition) ? $indexDefinition : [$indexDefinition];

                $values = array_map(static function ($field) use ($entryArray) {
                    return $entryArray[$field];
                }, $indexDefinition);

                if (!isset($this->index[$indexName][implode('_', $values)])) {
                    $this->index[$indexName][implode('_', $values)] = [];
                }

                $this->index[$indexName][implode('_', $values)][] = $entry;
            }
        }
    }

    private function _loadData(): void
    {
        if (!empty($this->data)) {
            return;
        }

        $this->data = json_decode(
            file_get_contents(
                $this->baseDirectory . DIRECTORY_SEPARATOR . $this->_fileName() . '.json'
            ),
            true
        );
    }
}
