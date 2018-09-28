<?php

namespace Newride\Headless\Components;

use Newride\Headless\Models\StaticContent as StaticContentModel;
use Cms\Classes\ComponentBase;

class StaticContent extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Static Content',
            'description' => 'Displays static content.',
        ];
    }

    public function defineProperties()
    {
        return [
            'page_name' => [
                 'description' => 'Page from which content will be obtained.',
                 'required' => true,
                 'title' => 'Page name',
                 'type' => 'string',
            ],
            'strict' => [
                'default' => false,
                 'description' => 'Raise errors if content is not available.',
                 'required' => false,
                 'title' => 'Strict',
                 'type' => 'boolean',
            ],
        ];
    }

    public function content(): StaticContentModel
    {
        $pageName = $this->property('page_name');

        $content = StaticContentModel::findOrCreateForPage($pageName);
        $content->setStrict($this->property('strict'));

        return $content;
    }
}
