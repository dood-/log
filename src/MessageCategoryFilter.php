<?php

declare(strict_types=1);

namespace Yiisoft\Log;

use InvalidArgumentException;

use function gettype;
use function is_string;
use function rtrim;
use function substr_compare;
use function sprintf;
use function strpos;

/**
 * Category is a data object that stores and matches the included and excluded categories of log messages.
 *
 * @internal
 */
final class MessageCategoryFilter
{
    public const DEFAULT = 'application';

    /**
     * @var string[] The list of included log message categories.
     *
     * Defaults to empty, which means all categories are included.
     *
     * You can use an asterisk at the end of a category so that the category may be used to
     * match those categories sharing the same common prefix. For example, `Yiisoft\Db\*` will match
     * categories starting with `Yiisoft\Db\`, such as `Yiisoft\Db\Connection`.
     */
    private array $include = [];

    /**
     * @var string[] The list of excluded log message categories.
     *
     * Defaults to empty, which means there are no excluded categories.
     *
     * You can use an asterisk at the end of a category so that the category can be used to
     * match those categories sharing the same common prefix. For example, `Yiisoft\Db\*` will match
     * categories starting with `Yiisoft\Db\`, such as `Yiisoft\Db\Connection`.
     */
    private array $exclude = [];

    /**
     * Sets the log message categories to be included.
     *
     * @param string[] $categories List of log message categories to be included.
     *
     * @throws InvalidArgumentException for invalid log message categories structure.
     */
    public function include(array $categories): void
    {
        $this->checkStructure($categories);
        $this->include = $categories;
    }

    /**
     * Gets the log message categories to be included.
     *
     * @return string[] The list of log message categories to be included.
     */
    public function getIncluded(): array
    {
        return $this->include;
    }

    /**
     * Sets the log message categories to be excluded.
     *
     * @param string[] $categories The list of log message categories to be excluded.
     *
     * @throws InvalidArgumentException When log message category structure is invalid.
     */
    public function exclude(array $categories): void
    {
        $this->checkStructure($categories);
        $this->exclude = $categories;
    }

    /**
     * Gets the log message categories to be excluded.
     *
     * @return string[] The list of log message categories to be excluded.
     */
    public function getExcluded(): array
    {
        return $this->exclude;
    }

    /**
     * Checks whether the specified log message category is excluded.
     *
     * @param string $category The log message category.
     *
     * @return bool The value indicating whether the specified category is excluded.
     */
    public function isExcluded(string $category): bool
    {
        foreach ($this->exclude as $exclude) {
            $prefix = rtrim($exclude, '*');

            if ($category === $exclude || ($prefix !== $exclude && strpos($category, $prefix) === 0)) {
                return true;
            }
        }

        if (empty($this->include)) {
            return false;
        }

        foreach ($this->include as $include) {
            if (
                $category === $include
                || (
                    !empty($include)
                    && substr_compare($include, '*', -1, 1) === 0
                    && strpos($category, rtrim($include, '*')) === 0
                )
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks message categories structure.
     *
     * @param array $categories The log message categories to be checked.
     *
     * @throws InvalidArgumentException When log message category structure is invalid.
     */
    private function checkStructure(array $categories): void
    {
        foreach ($categories as $category) {
            if (!is_string($category)) {
                throw new InvalidArgumentException(sprintf(
                    'The log message category must be a string, %s received.',
                    gettype($category)
                ));
            }
        }
    }
}