<?php

namespace AlexDashkin\Adwpfw\Modules;

use AlexDashkin\Adwpfw\App;
use AlexDashkin\Adwpfw\Exceptions\AdwpfwException;
use AlexDashkin\Adwpfw\Items\ProfileField;

/**
 * User Profile Custom fields.
 */
class Profile extends ModuleWithItems
{
    /**
     * @var ProfileField[]
     */
    protected $items = [];

    /**
     * @var string Fields group heading
     */
    private $heading = 'Custom fields';

    /**
     * Constructor.
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);

        add_action('show_user_profile', [$this, 'render']);
        add_action('edit_user_profile', [$this, 'render']);
        add_action('personal_options_update', [$this, 'save']);
        add_action('edit_user_profile_update', [$this, 'save']);
    }

    /**
     * Add Profile Field.
     *
     * @param array $data. Data to pass to ProfileField constructor.
     *
     * @see ProfileField::__construct();
     *
     * @throws AdwpfwException
     */
    public function add(array $data)
    {
        $this->items[] = new ProfileField($this->app, $data);
    }

    /**
     * Get profile field value.
     *
     * @param string $id Field ID.
     * @param int $userId Defaults to current user.
     * @return mixed
     */
    public function get($id, $userId = null)
    {
        if ($item = $this->searchItems(['id' => $id], true)) {
            return $item->get($userId);
        }

        return null;
    }

    /**
     * Set profile field value.
     *
     * @param string $id Field ID.
     * @param mixed $value
     * @param int $userId Defaults to current user.
     * @return bool
     */
    public function set($id, $value, $userId = null)
    {
        if ($item = $this->searchItems(['id' => $id], true)) {
            return $item->set($value, $userId);
        }

        return false;
    }

    /**
     * Save posted data.
     *
     * @param int $userId User ID.
     */
    public function save($userId)
    {
        if (!current_user_can('edit_user')) {
            return;
        }

        $prefix = $this->config['prefix'];

        if (empty($_POST[$prefix]['profile'])) {
            return;
        }

        foreach ($this->items as $item) {
            $item->save($userId, $_POST[$prefix]['profile']);
        }
    }

    /**
     * Render the Fields.
     *
     * @param \WP_User $user
     */
    public function render($user)
    {
        $fields = [];

        foreach ($this->items as $item) {
            $fields[] = $item->getArgs($user->ID);
        }

        // Group
        $groups = [];
        foreach ($fields as $field) {
            $groups[$field['group'] ?: 0][] = $field;
        }

        $args = [
            'prefix' => $this->config['prefix'],
            'groups' => $groups,
        ];

        echo $this->m('Twig')->renderFile('templates/profile', $args);
    }
}
