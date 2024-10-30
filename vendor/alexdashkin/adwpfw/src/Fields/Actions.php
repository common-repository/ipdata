<?php

namespace AlexDashkin\Adwpfw\Fields;

use AlexDashkin\Adwpfw\App;
use AlexDashkin\Adwpfw\Exceptions\AdwpfwException;

/**
 * Actions Selector. Used on Admin Pages only.
 */
class Actions extends Field
{
    /**
     * Constructor.
     *
     * @param App $app
     * @param array $data {
     * @type string $id Required.
     * @type string $layout Parent template to extend. Required.
     * @type string $form Form ID (slug). Used to distinguish multiple forms on one page. Required.
     * @type string $tpl Template name. Default 'actions'.
     * @type string $class CSS Class(es) for the control. Default 'adwpfw-form-control'.
     * @type string $label Field Label. Default empty.
     * @type string $desc Field Description. Default empty.
     * @type array $options Actions list ['label', 'value']. Required.
     * }
     * @param array $props
     *
     * @throws AdwpfwException
     */
    public function __construct(App $app, array $data, array $props = [])
    {
        $defaults = [
            'tpl' => [
                'type' => 'string',
                'default' => 'actions',
            ],
            'class' => [
                'type' => 'string',
                'default' => 'adwpfw-form-control',
            ],
            'options' => [
                'type' => 'array',
                'required' => true,
                'def' => [
                    'value' => '',
                    'label' => 'Option',
                ],
            ],
        ];

        parent::__construct($app, $data, array_merge($defaults, $props));
    }
}
