<?php
/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.7.0
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2025 KONKORD DIGITAL
 */

namespace Modules\Core;

class Hooks
{
    /**
     * Registered filters.
     */
    protected array $filters = [];

    /**
     * Merged filters.
     */
    protected array $mergedFilters = [];

    /**
     * Registered actions.
     */
    protected array $actions = [];

    /**
     * Current filter or action.
     */
    protected array $currentFilter = [];

    /**
     * Hooks a function or method to a specific filter action.
     *
     * @param  int  $priority  Optional. Sets the order of function execution for a specific action. Lower numbers run first, and if multiple
     *                         functions share the same priority, they're executed in the order they were added.
     */
    public function addFilter(string $tag, callable $callback, int $priority = 10, int $acceptedArgs = 1): true
    {
        $idx = $this->buildUniqueId($tag, $callback, $priority);
        $this->filters[$tag][$priority][$idx] = ['callback' => $callback, 'accepted_args' => $acceptedArgs];
        unset($this->mergedFilters[$tag]);

        return true;
    }

    /**
     * Removes a function from a specified filter hook.
     */
    public function removeFilter(string $tag, callable $callback, int $priority = 10): bool
    {
        $callback = $this->buildUniqueId($tag, $callback, $priority);

        $exists = isset($this->filters[$tag][$priority][$callback]);

        if ($exists) {
            unset($this->filters[$tag][$priority][$callback]);
            if (empty($this->filters[$tag][$priority])) {
                unset($this->filters[$tag][$priority]);
            }
            unset($this->mergedFilters[$tag]);
        }

        return $exists;
    }

    /**
     * Remove all of the hooks from a filter.
     */
    public function removeAllFilters(string $tag, int|false $priority = false): true
    {
        if (isset($this->filters[$tag])) {
            if ($priority !== false && isset($this->filters[$tag][$priority])) {
                unset($this->filters[$tag][$priority]);
            } else {
                unset($this->filters[$tag]);
            }
        }

        if (isset($this->mergedFilters[$tag])) {
            unset($this->mergedFilters[$tag]);
        }

        return true;
    }

    /**
     * Check if any filter has been registered for a hook.
     *
     * @return bool|int Returns true if any functions are registered, or the priority if checking a specific function.
     *                  Returns false if not attached. Use === when checking $callback, as the result may be 0.
     */
    public function hasFilter(string $tag, callable|false $callback = false): bool|int
    {
        $has = ! empty($this->filters[$tag]);

        if ($callback === false || $has == false) {
            return $has;
        }

        if (! $idx = $this->buildUniqueId($tag, $callback, false)) {
            return false;
        }

        foreach ((array) array_keys($this->filters[$tag]) as $priority) {
            if (isset($this->filters[$tag][$priority][$idx])) {
                return $priority;
            }
        }

        return false;
    }

    /**
     * Call the functions added to a filter hook.
     *
     * @return mixed The filtered value after all hooked functions are applied to it.
     */
    public function applyFilters(string $tag, mixed $value): mixed
    {
        $args = [];

        // Do 'all' actions first
        if (isset($this->filters['*'])) {
            $this->currentFilter[] = $tag;
            $args = func_get_args();
            $this->executeAllHooks($args);
        }

        if (! isset($this->filters[$tag])) {
            if (isset($this->filters['*'])) {
                array_pop($this->currentFilter);
            }

            return $value;
        }

        if (! isset($this->filters['*'])) {
            $this->currentFilter[] = $tag;
        }

        // Sort
        if (! isset($this->mergedFilters[$tag])) {
            ksort($this->filters[$tag]);
            $this->mergedFilters[$tag] = true;
        }

        reset($this->filters[$tag]);

        if (empty($args)) {
            $args = func_get_args();
        }

        do {
            foreach ((array) current($this->filters[$tag]) as $the_) {
                if (! is_null($the_['callback'])) {
                    $args[1] = $value;
                    $value = call_user_func_array($the_['callback'], array_slice($args, 1, (int) $the_['accepted_args']));
                }
            }

        } while (next($this->filters[$tag]) !== false);

        array_pop($this->currentFilter);

        return $value;
    }

    /**
     * Execute functions hooked on a specific filter hook, specifying arguments in an array.
     *
     * @return mixed The filtered value after all hooked functions are applied to it.
     */
    public function applyFiltersRefArray(string $tag, array $args): mixed
    {
        // Do 'all' actions first
        if (isset($this->filters['*'])) {
            $this->currentFilter[] = $tag;
            $all_args = func_get_args();
            $this->executeAllHooks($all_args);
        }

        if (! isset($this->filters[$tag])) {
            if (isset($this->filters['*'])) {
                array_pop($this->currentFilter);
            }

            return $args[0];
        }

        if (! isset($this->filters['*'])) {
            $this->currentFilter[] = $tag;
        }

        // Sort
        if (! isset($this->mergedFilters[$tag])) {
            ksort($this->filters[$tag]);
            $this->mergedFilters[$tag] = true;
        }

        reset($this->filters[$tag]);

        do {
            foreach ((array) current($this->filters[$tag]) as $the_) {
                if (! is_null($the_['callback'])) {
                    $args[0] = call_user_func_array($the_['callback'], array_slice($args, 0, (int) $the_['accepted_args']));
                }
            }

        } while (next($this->filters[$tag]) !== false);

        array_pop($this->currentFilter);

        return $args[0];
    }

    /**
     * Hooks a function on to a specific action.
     *
     * @param  int  $priority  Optional. Sets the order of function execution for a specific action. Lower numbers run first, and if multiple
     *                         functions share the same priority, they're executed in the order they were added.
     */
    public function addAction(string $tag, callable $callback, int $priority = 10, int $acceptedArgs = 1): bool
    {
        return $this->addFilter($tag, $callback, $priority, $acceptedArgs);
    }

    /**
     * Check if any action has been registered for a hook.
     *
     * @return bool|int Returns true if any functions are registered, or the priority if checking a specific function.
     *                  Returns false if not attached. Use === when checking $callback, as the result may be 0.
     */
    public function hasAction(string $tag, callable|false $callback = false): bool|int
    {
        return $this->hasFilter($tag, $callback);
    }

    /**
     * Removes a function from a specified action hook.
     */
    public function removeAction(string $tag, callable $callback, int $priority = 10): bool
    {
        return $this->removeFilter($tag, $callback, $priority);
    }

    /**
     * Remove all of the hooks from an action.
     */
    public function removeAllActions(string $tag, false|int $priority = false): bool
    {
        return $this->removeAllFilters($tag, $priority);
    }

    /**
     * Execute functions hooked on a specific action hook.
     */
    public function doAction(string $tag, mixed $arg = null): ?true
    {
        if (! isset($this->actions)) {
            $this->actions = [];
        }

        if (! isset($this->actions[$tag])) {
            $this->actions[$tag] = 1;
        } else {
            $this->actions[$tag]++;
        }

        // Do 'all' actions first
        if (isset($this->filters['*'])) {
            $this->currentFilter[] = $tag;
            $all_args = func_get_args();
            $this->executeAllHooks($all_args);
        }

        if (! isset($this->filters[$tag])) {
            if (isset($this->filters['*'])) {
                array_pop($this->currentFilter);
            }

            return null;
        }

        if (! isset($this->filters['*'])) {
            $this->currentFilter[] = $tag;
        }

        $args = [];
        if (is_array($arg) && count($arg) == 1 && isset($arg[0]) && is_object($arg[0])) { // array(&$this)
            $args[] = &$arg[0];
        } else {
            $args[] = $arg;
        }
        for ($a = 2; $a < func_num_args(); $a++) {
            $args[] = func_get_arg($a);
        }

        // Sort
        if (! isset($this->mergedFilters[$tag])) {
            ksort($this->filters[$tag]);
            $this->mergedFilters[$tag] = true;
        }

        reset($this->filters[$tag]);

        do {
            foreach ((array) current($this->filters[$tag]) as $the_) {
                if (! is_null($the_['callback'])) {
                    call_user_func_array($the_['callback'], array_slice($args, 0, (int) $the_['accepted_args']));
                }
            }

        } while (next($this->filters[$tag]) !== false);

        array_pop($this->currentFilter);

        return true;
    }

    /**
     * Execute functions hooked on a specific action hook, specifying arguments in an array.
     *
     * @return null Will return null if $tag does not exist in $filter array
     */
    public function doActionRefArray(string $tag, array $args): ?true
    {
        if (! isset($this->actions)) {
            $this->actions = [];
        }

        if (! isset($this->actions[$tag])) {
            $this->actions[$tag] = 1;
        } else {
            $this->actions[$tag]++;
        }

        // Do 'all' actions first
        if (isset($this->filters['*'])) {
            $this->currentFilter[] = $tag;
            $all_args = func_get_args();
            $this->executeAllHooks($all_args);
        }

        if (! isset($this->filters[$tag])) {
            if (isset($this->filters['*'])) {
                array_pop($this->currentFilter);
            }

            return null;
        }

        if (! isset($this->filters['*'])) {
            $this->currentFilter[] = $tag;
        }

        // Sort
        if (! isset($mergedFilters[$tag])) {
            ksort($this->filters[$tag]);
            $mergedFilters[$tag] = true;
        }

        reset($this->filters[$tag]);

        do {
            foreach ((array) current($this->filters[$tag]) as $the_) {
                if (! is_null($the_['callback'])) {
                    call_user_func_array($the_['callback'], array_slice($args, 0, (int) $the_['accepted_args']));
                }
            }

        } while (next($this->filters[$tag]) !== false);

        array_pop($this->currentFilter);

        return true;
    }

    /**
     * Get the number of times an action is fired.
     */
    public function didAction(string $tag): int
    {
        if (! isset($this->actions) || ! isset($this->actions[$tag])) {
            return 0;
        }

        return $this->actions[$tag];
    }

    /**
     * Retrieve the name of the current filter or action.
     */
    public function currentFilter(): ?string
    {
        return end($this->currentFilter) ?: null;
    }

    /**
     * Retrieve the name of the current action.
     */
    public function currentAction(): ?string
    {
        return $this->currentFilter();
    }

    /**
     * Check whether currently a filter is being executed.
     */
    public function doingFilter(?string $filter = null): bool
    {
        if ($filter === null) {
            return ! empty($this->currentFilter);
        }

        return in_array($filter, $this->currentFilter);
    }

    /**
     * Check whether currently an action is being executed.
     */
    public function doingAction(?string $action = null): bool
    {
        return $this->doingFilter($action);
    }

    /**
     * Build Unique ID for storage and retrieval.
     *
     * @param  string  $tag  Used in counting how many hooks were applied
     * @param  callable  $callback  Used for creating unique id
     * @param  int|bool  $priority  Used in counting how many hooks were applied. If === false and $callback is an object reference, we return the unique id only if it already has one, false otherwise.
     * @return string|bool Unique ID for usage as array key or false if $priority === false and $callback is an object reference, and it does not already have a unique id.
     */
    private function buildUniqueId(string $tag, callable $callback, int|false $priority)
    {
        static $filter_id_count = 0;

        if (is_string($callback)) {
            return $callback;
        }

        if (is_object($callback)) {
            // Closures are currently implemented as objects
            $callback = [$callback, ''];
        } else {
            $callback = (array) $callback;
        }

        if (is_object($callback[0])) {
            // Object Class Calling
            if (function_exists('spl_object_hash')) {
                return spl_object_hash($callback[0]).$callback[1];
            } else {
                $obj_idx = get_class($callback[0]).$callback[1];
                if (! isset($callback[0]->filter_id)) {
                    if ($priority === false) {
                        return false;
                    }
                    $obj_idx .= isset($this->filters[$tag][$priority]) ? count((array) $this->filters[$tag][$priority]) : $filter_id_count;
                    $callback[0]->filter_id = $filter_id_count;
                    $filter_id_count++;
                } else {
                    $obj_idx .= $callback[0]->filter_id;
                }

                return $obj_idx;
            }
        } elseif (is_string($callback[0])) {
            // Static Calling
            return $callback[0].$callback[1];
        }
    }

    private function executeAllHooks(array $args)
    {
        reset($this->filters['*']);

        do {
            foreach ((array) current($this->filters['*']) as $the_) {
                if (! is_null($the_['callback'])) {
                    call_user_func_array($the_['callback'], $args);
                }
            }
        } while (next($this->filters['*']) !== false);
    }
}
