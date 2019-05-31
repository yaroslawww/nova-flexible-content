<?php

namespace Whitecube\NovaFlexibleContent\Concerns;

use Whitecube\NovaFlexibleContent\Layouts\Layout;
use Whitecube\NovaFlexibleContent\Layouts\Collection;
use Illuminate\Support\Collection as BaseCollection;

trait HasFlexible {

    /**
     * Parse a Flexible Content attribute
     *
     * @param string $attribute
     * @return Whitecube\NovaFlexibleContent\Layouts\Collection
     */
    public function flexible($attribute)
    {
        $flexible = $this->__get($attribute) ?: null;

        return $this->toFlexible($flexible);
    }

    /**
     * Parse a Flexible Content from value
     *
     * @param mixed $value
     * @return Whitecube\NovaFlexibleContent\Layouts\Collection
     */
    public function toFlexible($value)
    {
        $flexible = $this->getFlexibleArrayFromValue($value);

        if(is_null($flexible)) {
            return new Collection();
        }

        return new Collection(
            array_filter($this->getMappedFlexibleLayouts($flexible));
        );
    }

    /**
     * Transform incoming value into an array of usable layouts
     *
     * @param mixed $value
     * @return array|null
     */
    protected function getFlexibleArrayFromValue($value)
    {
        if(is_string($value)) {
            $value = json_decode($value);
            return is_array($value) ? $value : null;
        }

        if(is_a($value, BaseCollection::class)) {
            return $value->toArray();
        }

        if(is_array($value)) {
            return $value;
        }

        return null;
    }

    /**
     * Map array with Flexible Content Layouts
     *
     * @param array $flexible
     * @return array
     */
    protected function getMappedFlexibleLayouts(array $flexible)
    {
        return array_map(function($item) {
            return $this->getMappedLayout($item);
        }, $flexible);
    }

    /**
     * Transform given layout value into a usable Layout instance
     *
     * @param mixed $item
     * @return array
     */
    protected function getMappedLayout($item)
    {
        $name = null;
        $key = null;
        $attributes = [];

        if(is_string($item)) {
            $item = json_decode($item);
        }

        if(is_array($item)) {
            $name = $item['layout'] ?? null;
            $key = $item['key'] ?? null;
            $attributes = (array) $item['attributes'] ?? [];
        }

        if(is_a($item, \stdClass::class)) {
            $name = $item->layout ?? null;
            $key = $item->key ?? null;
            $attributes = (array) $item->attributes ?? [];
        }

        if(is_a($item, Layout::class)) {
            $name = $item->name();
            $key = $item->key();
            $attributes = $item->getAttributes();
        }

        if(is_null($name) || !$attributes) {
            return;
        }

        return new Layout($name, $name, [], $key, $attributes);
    }

}