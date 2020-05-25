<?php

declare(strict_types=1);

namespace Newride\Headless\Models;

use Alsofronie\Uuid\UuidModelTrait;
use Model;
use Newride\Headless\Exceptions\ContentNotFound;
use October\Rain\Database\Traits\Validation;

class StaticContent extends Model
{
    use UuidModelTrait;
    use Validation;

    public static $attachments = [];

    public $jsonable = [
        'data',
    ];

    public $guarded = ['id'];

    public $rules = [
        'id' => 'unique',
        'page_name' => 'string|required',
        'data' => 'array',
    ];

    public $table = 'newride_headless_staticcontent';

    public static function findOrCreateForPage(string $pageName): self
    {
        $model = static::where('page_name', $pageName)->first();

        if (!is_null($model)) {
            if (isset(StaticContent::$attachments[$pageName])) {
                $model->attachMany = static::$attachments[$pageName]['attachMany'];
                $model->attachOne = static::$attachments[$pageName]['attachOne'];
            }

            return $model;
        }

        static::create([
            'page_name' => $pageName,
            'data' => [],
        ]);

        return static::findOrCreateForPage($pageName);
    }

    /**
     * @Override
     */
    public function getAttribute($key)
    {
        if (!$this->isContentAttribute($key)) {
            return parent::getAttribute($key);
        }

        $data = $this->getAttribute('data');

        if (array_key_exists($key, $data)) {
            return $data[$key];
        }

        return '';
    }

    public function isContentAttribute(string $key): bool
    {
        if (array_key_exists($key, $this->attachMany) || array_key_exists($key, $this->attachOne)) {
            return false;
        }

        return !in_array($key, [
            self::CREATED_AT,
            self::UPDATED_AT,
            'data',
            'id',
            'page_name',
        ]);
    }

    /**
     * @Override
     */
    public function setAttribute($key, $value)
    {
        if (!$this->isContentAttribute($key)) {
            return parent::setAttribute($key, $value);
        }

        if (is_null($value)) {
            $value = '';
        }

        return parent::setAttribute('data', array_merge($this->data, [
            $key => $value,
        ]));
    }
}
