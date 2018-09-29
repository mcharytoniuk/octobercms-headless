<?php

namespace Newride\Headless\Helpers;

use Newride\Headless\Models\StaticContent as StaticContentModel;
use Route;

class StaticContent
{
    public static function expose(string $pageName): void
    {
        Route::get('api/v1/content/'.$pageName, function () use ($pageName): array {
            return StaticContentModel::where('page_name', $pageName)->firstOrFail()->toArray();
        });
    }
}
