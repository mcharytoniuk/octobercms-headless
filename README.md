# OctoberCMS Headless

This plugin is aimed at developers but provides easy to use static content
forms through reusing OctoberCMS form behavior and the ability to expose API
for going headless.

## Requirements

This plugin requires https://github.com/alsofronie/eloquent-uuid library.
Because of OctoberCMS not clear way to provide composer dependencies with
marketplace plugins in some scenarios you might need to add
`alsofronie/eloquent-uuid` directly to your `composer.json` after installing
the plugin by typing in the project root:

```bash
$ composer require alsofronie/eloquent-uuid
```

## Setup

The setup process should take about ~5 minutes overall if you get some
practice. ;) There are actually 4 steps in this list (one of them is optional),
it's just illustrated by some examples.

1. You need to create backend forms that would allow static content editing.
To do so you need to create a local plugin in your OctoberCMS installation by
typing in your project root:

```bash
$ php artisan create:plugin Acme.ContentEditor
```

Where `Acme` is your desired name. You can read more here:
https://octobercms.com/docs/console/scaffolding#scaffold-create-plugin

2. Add backend form following this guide, but instead of using the default
[Backend.Behaviors.FormController](https://octobercms.com/docs/api) behavior
use `Newride.Headless.Behaviors.StaticContentEditor` behavior provided by
Headless:
https://octobercms.com/docs/backend/forms#introduction
`Newride.Headless.Behaviors.StaticContentEditor` extends the default
`FormController` behavior. It uses `form_config.yaml` to generate static
content API and component for you.

Inside your backend Controller you also need to provide a public property with
the page name your are going to edit (`public $staticPageName`). It will be
used by static content component in your views and API routes. Your final
controller should look somewhat like this:

```php
<?php

namespace Acme\ContentEditor\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Homepage extends Controller
{
    public $implement = [
        'Newride.Headless.Behaviors.StaticContentEditor',
    ];

    public $formConfig = 'config_form.yaml';

    public $staticPageName = 'home';

    public function update(): void
    {
        parent::update();

        BackendMenu::setContext('Acme.ContentEditor', 'contenteditor', 'homepage');
    }
}
```

In your `config_form.yaml` you can then configure your form fields while
refering to those docs: https://octobercms.com/docs/backend/forms#form-fields

If should look like this:

```yaml
name: My static content form
form: $/acme/contenteditor/controllers/homepage/fields.yaml
modelClass: Newride\Headless\Models\StaticContent
```

3. In your `Plugin.php` file register backend navigation so you will be able
to access your backend form. It should look somewhat like this:

```php
<?php

public function registerNavigation(): array
{
    return [
        'contenteditor' => [
            'label' => 'Homepage Editor',
            'url' => Backend::url('acme/contenteditor/homepage/update'),
            'icon' => 'icon-align-left',
            'permissions' => ['acme.contenteditor.*'],
            'order' => 500,
        ],
    ];
}
```

If you have more editors you can nest them in side menu like this:

```php
<?php

public function registerNavigation(): array
{
    return [
        'contenteditor' => [
            'label' => 'ContentEditor',
            'url' => Backend::url('acme/contenteditor/home/update'),
            'icon' => 'icon-align-left',
            'permissions' => ['acme.contenteditor.*'],
            'order' => 500,
            'sideMenu' => [
                'homepage' => [
                    'label' => 'Homepage',
                    'icon' => 'icon-home',
                    'url' => Backend::url('acme/contenteditor/homepage/update'),
                ],
                'team' => [
                    'label' => 'Team',
                    'icon' => 'icon-users',
                    'url' => Backend::url('acme/contenteditor/team/update'),
                ],
            ],
        ],
    ];
}
```

4. (optional) You can also expose headless API by adding this to your
`Plugin@boot` method:

```php
<?php

use Newride\Headless\Helpers\StaticContent;

class Plugin extends PluginBase
{
    // (...)

    public function boot()
    {
        StaticContent::expose('home');
    }

    // (...)
}
```

Your API will be accessible then under `http://projecturl/api/v1/content/home`.
It will be linked to your form controller. You can expose as many static
content endpoints as you need.

Response looks like this:

```json
{
    "id": "<guid>",
    "created_at": "<date>",
    "updated_at": "<date>",
    "page_name": "home",
    "data": {
    }
}
```

That's all. :)

## Usage

Headless uses your FormController's form config to generate API format. So for
example if your `/acme/contenteditor/controllers/homepage/fields.yaml` from
FormController looks like this:

```yaml
fields:

    title:
        label: Homepage title
        required: true

    planet:
        label: World you are currently living in
        type: textarea
        size: small
        required: true
```

You can use it on your page like this:

```html
title = "Home"
url = "/"
layout = "default"
is_hidden = 0

[staticContent]
page_name = "home"
strict = true
==

<p>Hello, {{ static.content.planet }}!</p> <!-- <p>Hello, World!</p> -->
```

Api response at `http://projecturl/api/v1/content/home` would look like this:

```json
{
    "id": "<guid>",
    "created_at": "<date>",
    "updated_at": "<date>",
    "page_name": "home",
    "data": {
        "title": "Hello",
        "planet": "World"
    }
}
```

## Summary

I hope this helps. If you need some help, feel free to fill an issue.
