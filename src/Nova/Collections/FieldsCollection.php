<?php

namespace NovaFlexibleContent\Nova\Collections;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\FieldCollection as NovaFieldCollection;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use NovaFlexibleContent\Flexible;
use NovaFlexibleContent\Http\FlexibleAttribute;

/**
 * @extends  \Laravel\Nova\Fields\FieldCollection<int, \Laravel\Nova\Fields\Field>
 */
class FieldsCollection extends NovaFieldCollection
{
    /**
     * @inheritDoc
     */
    public function findFieldByAttribute($attribute, $default = null)
    {
        if (str_contains($attribute, FlexibleAttribute::GROUP_SEPARATOR)) {
            return $this->findFieldUsedInFlexibleByAttribute($attribute, $default);
        }

        return parent::findFieldByAttribute($attribute, $default);
    }

    public function findFieldUsedInFlexibleByAttribute($attribute, mixed $default = null)
    {
        /** @var NovaRequest $request */
        $request = resolve(NovaRequest::class);

        /** @var Resource $resource */
        $resource = $request->findResourceOrFail();

        [$groupKey, $fieldKey] = explode(FlexibleAttribute::GROUP_SEPARATOR, $attribute, 2);

        foreach ($resource->updateFields($request) as $field) {
            if ($field instanceof Flexible) {
                if ($group = $field->findGroupRecursive($groupKey)) {
                    $foundField = $group->fieldsCollection()
                                           ->first(fn (Field $groupField) => $groupField->attribute == $fieldKey);

                    if ($foundField) {
                        return $foundField;
                    }
                }
            }
        }

        return value($default);
    }
}
