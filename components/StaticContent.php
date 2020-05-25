<?php

namespace Newride\Headless\Components;

use Newride\Headless\Models\StaticContent as StaticContentModel;
use Cms\Classes\ComponentBase;
use System\Models\File;

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

    public function content(): array
    {
        $pageName = $this->property('page_name');

        $content = StaticContentModel::findOrCreateForPage($pageName);
        $data = $content->toArray()['data'];

        // search for potential attachments
        $attachments = File::where('attachment_id', $content->id)->get();
        foreach ($attachments as $attachment) {
            if (isset($data[$attachment->field])) {
                if (!is_array($data[$attachment->field])) {
                    $data[$attachment->field] = [$data[$attachment->field]];
                }

                array_push($data[$attachment->field], $attachment);
            } else {
                $data[$attachment->field] = $attachment;
            }
        }

        return $data;
    }
}
