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

    protected $isStrict = false;

    public static function findOrCreateForPage(string $pageName): self
    {
        $model = static::where('page_name', $pageName)->first();

        if (!is_null($model)) {
            return $model;
        }

        static::create([
            'page_name' => $pageName,
            'data' => [],
        ]);

        return static::findOrCreateForPage($pageName);
    }

    public function getAttribute($key)
    {
        if (!$this->isContentAttribute($key)) {
            return parent::getAttribute($key);
        }

        $data = $this->getAttribute('data');

        if (array_key_exists($key, $data)) {
            return $data[$key];
        }

        if ($this->isStrict()) {
            throw new ContentNotFound($key, $data);
        }

        return '';
    }

    public function isContentAttribute(string $key): bool
    {
        return !in_array($key, [
            'created_at',
            'data',
            'id',
            'page_name',
            'updated_at',
        ]);
    }

    public function isStrict(): bool
    {
        return $this->isStrict;
    }

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

    public function setStrict(bool $isStrict): void
    {
        $this->isStrict = $isStrict;
    }
}
