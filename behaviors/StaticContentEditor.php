<?php

namespace Newride\Headless\Behaviors;

use Backend\Behaviors\FormController;
use Backend\Classes\Controller;
use LogicException;
use Newride\Headless\Models\StaticContent;
use October\Rain\Database\Model;
use System\Models\File;

class StaticContentEditor extends FormController
{
    protected $hasCustomModel = false;
    protected $requiredConfig = ['form'];
    protected $staticPageName = null;

    /**
     * @Override
     */
    public function formFindModelObject($recordId)
    {
        return $this->staticContentGetModel();
    }

    /**
     * @Override
     */
    public function makeConfig($configFile = [], $requiredConfig = []): object
    {
        $config = parent::makeConfig($configFile, $requiredConfig);

        if (isset($config->static_page_name)) {
            $this->staticPageName = $config->static_page_name;
        }

        if (isset($config->modelClass) && $config->modelClass !== StaticContent::class) {
            $this->hasCustomModel = true;

            return $config;
        }

        $config->modelClass = StaticContent::class;

        if (!isset($config->form)) {
            return $config;
        }

        $formFields = parent::makeConfig($config->form);

        $attachMany = [];
        $attachOne = [];

        foreach ($formFields->fields as $formFieldKey => $formField) {
            if (isset($formField['type']) && 'fileupload' == $formField['type']) {
                switch ($formField['mode']) {
                    case 'file':
                    case 'file-single':
                    case 'image':
                    case 'image-single':
                        $attachOne[$formFieldKey] = File::class;
                    break;
                    case 'file-multi':
                    case 'image-multi':
                        $attachMany[$formFieldKey] = File::class;
                    break;
                    default:
                        throw new LogicException('Unknown form field mode: ' . $formField['mode']);
                }
            }
        }

        if (empty($attachMany) && empty($attachOne)) {
            return $config;
        }

        $pageName = $this->staticContentGetPageName();

        StaticContent::$attachments[$pageName] = [
            'attachMany' => $attachMany,
            'attachOne' => $attachOne,
        ];

        return $config;
    }

    public function staticContentGetPageName(): string
    {
        if (isset($this->controller->staticPageName)) {
            return $this->controller->staticPageName;
        }

        if (method_exists($this->controller, 'staticContentGetPageName')) {
            return $this->controller->staticContentGetPageName();
        }

        if (isset($this->staticPageName)) {
            return $this->staticPageName;
        }

        throw new LogicException(
            'You need to implement ::staticContentGetPageName() or'
            .' $staticPageName public property in your controller'
            .' when using StaticContentEditor behavior.'
        );
    }

    public function update($recordId = null, $context = null)
    {
        if (is_null($recordId)) {
            $recordId = $this->staticContentGetModel()->id;
        }

        return parent::update($recordId, $context);
    }

    protected function staticContentGetModel(): StaticContent
    {
        $pageName = $this->staticContentGetPageName();

        return StaticContent::findOrCreateForPage($pageName);
    }
}
