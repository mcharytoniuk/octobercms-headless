<?php

namespace Newride\Headless\Behaviors;

use Backend\Behaviors\FormController;
use LogicException;
use Newride\Headless\Models\StaticContent;

class StaticContentEditor extends FormController
{
    public function formFindModelObject($recordId)
    {
        return $this->staticContentGetModel();
    }

    public function staticContentGetModel(): StaticContent
    {
        $pageName = $this->staticContentGetPageName();

        return StaticContent::findOrCreateForPage($pageName);
    }

    public function staticContentGetPageName(): string
    {
        if (isset($this->controller->staticPageName)) {
            return $this->controller->staticPageName;
        }

        if (method_exists($this->controller, 'staticContentGetPageName')) {
            return $this->controller->staticContentGetPageName();
        }

        throw new LogicException(
            'You need to implement staticContentGetPageName in your controller'
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
}
