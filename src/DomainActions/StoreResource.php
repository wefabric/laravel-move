<?php

namespace Uteq\Move\DomainActions;

use Illuminate\Database\Eloquent\Model;
use Uteq\Move\DataTransferObjects\MediaCollection;
use Uteq\Move\Fields\Field;
use Uteq\Move\Resource;

class StoreResource
{
    public function __invoke(Model $model, array $data, Resource $resource)
    {
        $data = $this->handleFieldsBeforeStore($model, $data, $resource);

        $model = $resource->fill(
            // All media should be stripped from the model data
            //  because this action will store the media separate in the after store.
            ...$this->withoutMedia($model, $data, $resource),
        );

        $model->save();

        $this->afterStore($model, $data, $resource);

        return $model;
    }

    /**
     * Returns an array with data without media
     *
     * @param Model $model
     * @param array $data
     * @return array
     */
    public function withoutMedia(Model $model, array $data)
    {
        return [$this->modelWithoutMedia($model), $this->dataWithoutMedia($data)];
    }

    /**
     * Returns the model without the MediaCollection
     *
     * @param Model $model
     * @return Model
     */
    public function modelWithoutMedia(Model $model)
    {
        collect($model->getAttributes())
            ->filter(fn ($attribute) => $attribute instanceof MediaCollection)
            ->each(function ($attribute, $key) use ($model) {
                unset($model->{$key});
            });

        return $model;
    }

    /**
     * Returns the data without media
     *
     * @param array $data
     * @return array
     */
    public function dataWithoutMedia(array $data)
    {
        return collect($data)
            ->filter(fn ($attribute) => ! $attribute instanceof MediaCollection)
            ->toArray();
    }

    public function handleFieldsBeforeStore(Model $model, array $data, Resource $resource)
    {
        $beforeStoreFields = collect($resource->fields())
            ->filter(fn ($item) => isset($item->beforeStore));

        foreach ($data as $field => $value) {
            $beforeStoreFields
                ->filter(fn (Field $item) => $item->attribute === $field)
                ->each(fn (Field $item) => $data[$field] = $item->handleBeforeStore($model, $data, $value));
        }

        return $data;
    }

    public function afterStore(Model $model, array $data, Resource $resource)
    {
        $beforeSaveActions = method_exists($resource, 'afterStore') ? $resource->afterStore() : [];

        collect($beforeSaveActions)->each->__invoke($this, $model, $data);

        /** @psalm-suppress InvalidArgument */
        app()->call([$this, 'syncMedia'], ['model' => $model, 'data' => $data]);
    }

    public function syncMedia(SyncMediaAction $syncer, Model $model, array $data)
    {
        collect($data)
            ->filter(fn ($attribute) => $attribute instanceof MediaCollection)
            ->each(fn ($mediaCollection, $key) => $syncer($model, $mediaCollection, $key));
    }
}
