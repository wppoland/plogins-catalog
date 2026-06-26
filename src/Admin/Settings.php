<?php

declare(strict_types=1);

namespace Catalog\Admin;

use Catalog\Contract\HasHooks;
use Catalog\Service\Settings as SettingsStore;

defined('ABSPATH') || exit;

/**
 * Admin settings page registered as a WooCommerce submenu ("WooCommerce →
 * Catalog"). Stores everything in the `catalog_settings` option (array): what to
 * hide (price / add-to-cart), an optional price notice, and the role rule
 * (everyone / guests / specific roles / except roles). All output is escaped;
 * all input is sanitised and validated on save.
 */
final class Settings implements HasHooks
{
    private const OPTION = SettingsStore::OPTION;
    private const PAGE   = 'catalog-settings';

    private const ROLE_MODES = ['everyone', 'guests', 'roles', 'except_roles'];

    public function registerHooks(): void
    {
        add_action('admin_menu', [$this, 'addMenuPage']);
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
        add_filter(
            'plugin_action_links_' . plugin_basename(\Catalog\PLUGIN_FILE),
            [$this, 'actionLinks'],
        );
    }

    /**
     * Add a "Settings" link on the Plugins screen row.
     *
     * @param array<int|string, string> $links
     * @return array<int|string, string>
     */
    public function actionLinks(array $links): array
    {
        $url = admin_url('admin.php?page=' . self::PAGE);

        $settingsLink = sprintf(
            '<a href="%s">%s</a>',
            esc_url($url),
            esc_html__('Settings', 'catalog'),
        );

        array_unshift($links, $settingsLink);

        return $links;
    }

    public function enqueueAssets(string $hook): void
    {
        if ($hook !== 'woocommerce_page_' . self::PAGE) {
            return;
        }

        wp_enqueue_style(
            'catalog-admin',
            CATALOG_URL . 'assets/css/admin.css',
            [],
            \Catalog\VERSION,
        );
    }

    public function addMenuPage(): void
    {
        add_submenu_page(
            'woocommerce',
            __('Catalog Mode', 'catalog'),
            __('Catalog', 'catalog'),
            'manage_woocommerce',
            self::PAGE,
            [$this, 'renderPage'],
        );
    }

    public function registerSettings(): void
    {
        register_setting(
            self::PAGE,
            self::OPTION,
            [
                'type'              => 'array',
                'sanitize_callback' => [$this, 'sanitize'],
            ],
        );

        // The menu uses manage_woocommerce; align the options.php save capability
        // so shop managers (not just admins with manage_options) can save.
        add_filter(
            'option_page_capability_' . self::PAGE,
            static fn (): string => 'manage_woocommerce',
        );
    }

    public function renderPage(): void
    {
        if (! current_user_can('manage_woocommerce')) {
            return;
        }

        $settings = $this->settings();
        ?>
        <div class="wrap catalog-admin">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div class="catalog-intro">
                <div>
                    <h2><?php esc_html_e('Turn your store into a catalog', 'catalog'); ?></h2>
                    <p>
                        <?php esc_html_e('Hide prices and/or the add-to-cart button across your store, or only for certain visitors (for example, show prices to logged-in wholesale customers).', 'catalog'); ?>
                    </p>
                </div>
            </div>

            <form class="catalog-form" method="post" action="options.php">
                <?php settings_fields(self::PAGE); ?>

                <div class="catalog-card">
                    <h2><?php esc_html_e('What to hide', 'catalog'); ?></h2>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row"><?php esc_html_e('Enable catalog mode', 'catalog'); ?></th>
                                <td>
                                    <label for="catalog_enabled">
                                        <input type="checkbox" id="catalog_enabled" name="<?php echo esc_attr(self::OPTION); ?>[enabled]" value="1" <?php checked((bool) ($settings['enabled'] ?? false), true); ?> />
                                        <?php esc_html_e('Apply catalog mode on the storefront.', 'catalog'); ?>
                                        <?php $this->defaultHint(true); ?>
                                    </label>
                                    <p class="description"><?php esc_html_e('The master switch. When off, nothing is hidden and the catalog stylesheet is not loaded, your store sells as normal.', 'catalog'); ?></p>
                                </td>
                            </tr>
                            <?php
                            $this->checkboxRow('hide_price', __('Hide the price', 'catalog'), __('Remove the price from catalog products.', 'catalog'), $settings, __('Removes the price wherever WooCommerce would print it. Optionally show a notice such as "Contact us for pricing" below.', 'catalog'), true);
                            $this->checkboxRow('hide_add_to_cart', __('Hide add-to-cart', 'catalog'), __('Remove the add-to-cart button and block purchasing.', 'catalog'), $settings, __('Removes the add-to-cart button on product pages and listings, and prevents catalog products from being purchased server-side.', 'catalog'), true);
                            ?>
                            <tr>
                                <th scope="row">
                                    <label for="catalog_price_notice"><?php esc_html_e('Price notice', 'catalog'); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="catalog_price_notice" name="<?php echo esc_attr(self::OPTION); ?>[price_notice]" value="<?php echo esc_attr((string) ($settings['price_notice'] ?? '')); ?>" class="regular-text" placeholder="<?php esc_attr_e('e.g. Contact us for pricing', 'catalog'); ?>" />
                                    <p class="description"><?php esc_html_e('Shown in place of the hidden price, styled as a small brass placard on the storefront. Leave blank to show nothing. Only applies when "Hide the price" is on.', 'catalog'); ?></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="catalog-card">
                    <h2><?php esc_html_e('Who it applies to', 'catalog'); ?></h2>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="catalog_role_mode"><?php esc_html_e('Visitor rule', 'catalog'); ?></label>
                                </th>
                                <td>
                                    <select id="catalog_role_mode" name="<?php echo esc_attr(self::OPTION); ?>[role_mode]">
                                        <?php
                                        $currentMode = (string) ($settings['role_mode'] ?? 'everyone');
                                        $modeLabels  = [
                                            'everyone'     => __('Everyone', 'catalog'),
                                            'guests'       => __('Only logged-out visitors', 'catalog'),
                                            'roles'        => __('Only selected roles', 'catalog'),
                                            'except_roles' => __('Everyone except selected roles', 'catalog'),
                                        ];
                                        foreach (self::ROLE_MODES as $mode) :
                                            ?>
                                            <option value="<?php echo esc_attr($mode); ?>" <?php selected($currentMode, $mode); ?>>
                                                <?php echo esc_html($modeLabels[$mode] ?? $mode); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php $this->defaultHint('everyone' === ($settings['role_mode'] ?? 'everyone')); ?>
                                    <p class="description">
                                        <?php esc_html_e('Choose who catalog mode hides prices and buying from. Everyone applies it to all visitors. Only logged-out visitors lets members see prices once they sign in. The two "selected roles" rules use the role list below.', 'catalog'); ?>
                                        <br />
                                        <?php esc_html_e('Wholesale tip: pick "Everyone except selected roles" and tick your wholesale role so those customers still see prices and can buy, while everyone else gets the catalog.', 'catalog'); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr class="catalog-roles-row">
                                <th scope="row"><?php esc_html_e('Roles', 'catalog'); ?></th>
                                <td>
                                    <fieldset>
                                        <legend class="screen-reader-text"><?php esc_html_e('Roles the visitor rule applies to', 'catalog'); ?></legend>
                                        <?php
                                        $selectedRoles = (array) ($settings['role_list'] ?? []);
                                        foreach ($this->editableRoles() as $slug => $name) :
                                            ?>
                                            <label class="catalog-role">
                                                <input type="checkbox" name="<?php echo esc_attr(self::OPTION); ?>[role_list][]" value="<?php echo esc_attr($slug); ?>" <?php checked(in_array($slug, $selectedRoles, true), true); ?> />
                                                <?php echo esc_html($name); ?>
                                            </label>
                                        <?php endforeach; ?>
                                        <p class="description"><?php esc_html_e('Tick the roles the rule applies to. Used only when the visitor rule is "Only selected roles" or "Everyone except selected roles".', 'catalog'); ?></p>
                                        <p class="catalog-roles-inactive"><?php esc_html_e('Not used by the current visitor rule, choose a "selected roles" rule above to enable it.', 'catalog'); ?></p>
                                    </fieldset>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render a single checkbox row in the form-table.
     *
     * @param array<string, mixed> $settings
     */
    private function checkboxRow(string $key, string $label, string $help, array $settings, string $desc = '', bool $defaultOn = false): void
    {
        $id = 'catalog_' . $key;
        ?>
        <tr>
            <th scope="row"><?php echo esc_html($label); ?></th>
            <td>
                <label for="<?php echo esc_attr($id); ?>">
                    <input type="checkbox" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr(self::OPTION); ?>[<?php echo esc_attr($key); ?>]" value="1" <?php checked((bool) ($settings[$key] ?? false), true); ?> />
                    <?php echo esc_html($help); ?>
                    <?php $this->defaultHint($defaultOn); ?>
                </label>
                <?php if ('' !== $desc) : ?>
                    <p class="description"><?php echo esc_html($desc); ?></p>
                <?php endif; ?>
            </td>
        </tr>
        <?php
    }

    /**
     * Render a quiet "(default)" pill next to a control whose shipped default is
     * the given on-state. Presentation only — it reflects the packaged default,
     * never the saved value, so merchants can see the zero-config starting point.
     */
    private function defaultHint(bool $isDefault): void
    {
        if (! $isDefault) {
            return;
        }
        ?>
        <span class="catalog-default"><?php esc_html_e('default', 'catalog'); ?></span>
        <?php
    }

    /**
     * Editable role slug => display name map.
     *
     * @return array<string, string>
     */
    private function editableRoles(): array
    {
        if (! function_exists('get_editable_roles')) {
            require_once ABSPATH . 'wp-admin/includes/user.php';
        }

        $roles = [];

        foreach (get_editable_roles() as $slug => $details) {
            $name = isset($details['name']) ? (string) $details['name'] : (string) $slug;
            $roles[(string) $slug] = translate_user_role($name);
        }

        return $roles;
    }

    /**
     * Sanitises, validates and clamps the submitted settings before save.
     *
     * @param mixed $raw
     * @return array<string, mixed>
     */
    public function sanitize(mixed $raw): array
    {
        if (! is_array($raw)) {
            $raw = [];
        }

        $roleMode = isset($raw['role_mode']) ? sanitize_key((string) $raw['role_mode']) : 'everyone';
        if (! in_array($roleMode, self::ROLE_MODES, true)) {
            $roleMode = 'everyone';
        }

        $validRoles = array_keys($this->editableRoles());
        $roleList   = [];
        if (isset($raw['role_list']) && is_array($raw['role_list'])) {
            foreach ($raw['role_list'] as $role) {
                $role = sanitize_key((string) $role);
                if (in_array($role, $validRoles, true)) {
                    $roleList[] = $role;
                }
            }
        }
        $roleList = array_values(array_unique($roleList));

        return [
            'enabled'          => ! empty($raw['enabled']),
            'hide_price'       => ! empty($raw['hide_price']),
            'hide_add_to_cart' => ! empty($raw['hide_add_to_cart']),
            'price_notice'     => isset($raw['price_notice']) ? sanitize_text_field((string) $raw['price_notice']) : '',

            'role_mode' => $roleMode,
            'role_list' => $roleList,
        ];
    }

    /**
     * Stored settings merged over packaged defaults.
     *
     * @return array<string, mixed>
     */
    private function settings(): array
    {
        $stored = get_option(self::OPTION, []);

        if (! is_array($stored)) {
            $stored = [];
        }

        /** @var array<string, mixed> $defaults */
        $defaults = require CATALOG_DIR . 'config/defaults.php';

        return array_merge($defaults, $stored);
    }
}
