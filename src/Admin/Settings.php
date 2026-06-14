<?php

declare(strict_types=1);

namespace Catalog\Admin;

use Catalog\Contract\HasHooks;
use Catalog\Service\Settings as SettingsStore;

defined('ABSPATH') || exit;

/**
 * Admin settings page registered as a WooCommerce submenu ("WooCommerce →
 * Catalog"). Stores everything in the `catalog_settings` option (array): what to
 * hide (price / add-to-cart), the scope (all / selected products / categories),
 * the role rule (everyone / guests / specific roles / except roles) and the
 * optional replacement call-to-action. All output is escaped; all input is
 * sanitised and validated on save.
 */
final class Settings implements HasHooks
{
    private const OPTION = SettingsStore::OPTION;
    private const PAGE   = 'catalog-settings';

    private const SCOPES     = ['all', 'products', 'categories'];
    private const ROLE_MODES = ['everyone', 'guests', 'roles', 'except_roles'];

    /** Incremented to give each inline-help control a unique id/anchor. */
    private int $helpSeq = 0;

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

        wp_enqueue_script(
            'catalog-admin',
            CATALOG_URL . 'assets/js/admin.js',
            [],
            \Catalog\VERSION,
            ['in_footer' => true, 'strategy' => 'defer'],
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
                        <?php esc_html_e('Hide prices and/or the add-to-cart button — store-wide, for selected products or categories, or only for certain visitors (for example, show prices to logged-in wholesale customers). Optionally replace add-to-cart with your own call-to-action button.', 'catalog'); ?>
                    </p>
                </div>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields(self::PAGE); ?>

                <div class="catalog-card">
                    <h2><?php esc_html_e('What to hide', 'catalog'); ?></h2>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <?php esc_html_e('Enable catalog mode', 'catalog'); ?>
                                    <?php $this->help(__('The master switch. When off, nothing is hidden and the catalog stylesheet is not loaded — zero front-end impact.', 'catalog')); ?>
                                </th>
                                <td>
                                    <label for="catalog_enabled">
                                        <input type="checkbox" id="catalog_enabled" name="<?php echo esc_attr(self::OPTION); ?>[enabled]" value="1" <?php checked((bool) ($settings['enabled'] ?? false), true); ?> />
                                        <?php esc_html_e('Apply catalog mode on the storefront.', 'catalog'); ?>
                                    </label>
                                </td>
                            </tr>
                            <?php
                            $this->checkboxRow('hide_price', __('Hide the price', 'catalog'), __('Remove the price from catalog products.', 'catalog'), $settings, __('Removes the price wherever WooCommerce would print it. Optionally show a notice such as "Contact us for pricing" below.', 'catalog'));
                            $this->checkboxRow('hide_add_to_cart', __('Hide add-to-cart', 'catalog'), __('Remove the add-to-cart button and block purchasing.', 'catalog'), $settings, __('Removes the add-to-cart button on product pages and listings, and prevents catalog products from being purchased server-side.', 'catalog'));
                            ?>
                            <tr>
                                <th scope="row">
                                    <label for="catalog_price_notice"><?php esc_html_e('Price notice', 'catalog'); ?></label>
                                    <?php $this->help(__('Optional text shown where the price would be, e.g. "Contact us for pricing". Leave blank to show nothing.', 'catalog')); ?>
                                </th>
                                <td>
                                    <input type="text" id="catalog_price_notice" name="<?php echo esc_attr(self::OPTION); ?>[price_notice]" value="<?php echo esc_attr((string) ($settings['price_notice'] ?? '')); ?>" class="regular-text" placeholder="<?php esc_attr_e('e.g. Contact us for pricing', 'catalog'); ?>" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="catalog-card">
                    <h2><?php esc_html_e('Where it applies', 'catalog'); ?></h2>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="catalog_scope"><?php esc_html_e('Scope', 'catalog'); ?></label>
                                    <?php $this->help(__('Choose which products are affected. Per-product and per-category overrides (in the product editor and the Products → Categories screen) always win over this setting.', 'catalog')); ?>
                                </th>
                                <td>
                                    <select id="catalog_scope" name="<?php echo esc_attr(self::OPTION); ?>[scope]">
                                        <?php
                                        $currentScope = (string) ($settings['scope'] ?? 'all');
                                        $scopeLabels  = [
                                            'all'        => __('All products', 'catalog'),
                                            'products'   => __('Only selected products', 'catalog'),
                                            'categories' => __('Only selected categories', 'catalog'),
                                        ];
                                        foreach (self::SCOPES as $scope) :
                                            ?>
                                            <option value="<?php echo esc_attr($scope); ?>" <?php selected($currentScope, $scope); ?>>
                                                <?php echo esc_html($scopeLabels[$scope] ?? $scope); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="description">
                                        <?php esc_html_e('"Selected products" uses the Catalog mode field in each product editor. "Selected categories" uses the Catalog mode field on each product category.', 'catalog'); ?>
                                    </p>
                                </td>
                            </tr>
                            <?php
                            $this->checkboxRow('apply_on_single', __('Single product pages', 'catalog'), __('Apply catalog mode on individual product pages.', 'catalog'), $settings, __('Hides the price / add-to-cart on each product\'s own page.', 'catalog'));
                            $this->checkboxRow('apply_on_loop', __('Shop and category listings', 'catalog'), __('Apply catalog mode on shop, category and tag listings.', 'catalog'), $settings, __('Hides the price / add-to-cart across shop, category and tag archive grids.', 'catalog'));
                            ?>
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
                                    <?php $this->help(__('Restrict catalog mode by who is viewing. For a wholesale store, choose "Everyone except selected roles" and tick your wholesale role so those customers still see prices and can buy.', 'catalog')); ?>
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
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <?php esc_html_e('Roles', 'catalog'); ?>
                                    <?php $this->help(__('Used by the two "selected roles" options above. Tick the roles the rule applies to.', 'catalog')); ?>
                                </th>
                                <td>
                                    <fieldset>
                                        <legend class="screen-reader-text"><?php esc_html_e('Roles', 'catalog'); ?></legend>
                                        <?php
                                        $selectedRoles = (array) ($settings['role_list'] ?? []);
                                        foreach ($this->editableRoles() as $slug => $name) :
                                            ?>
                                            <label class="catalog-role">
                                                <input type="checkbox" name="<?php echo esc_attr(self::OPTION); ?>[role_list][]" value="<?php echo esc_attr($slug); ?>" <?php checked(in_array($slug, $selectedRoles, true), true); ?> />
                                                <?php echo esc_html($name); ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </fieldset>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="catalog-card">
                    <h2><?php esc_html_e('Replacement call-to-action', 'catalog'); ?></h2>
                    <p class="description">
                        <?php esc_html_e('Optionally show a button in place of add-to-cart — for example, an enquiry or contact page.', 'catalog'); ?>
                    </p>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <?php
                            $this->checkboxRow('cta_enabled', __('Show a call-to-action button', 'catalog'), __('Render a button where add-to-cart used to be.', 'catalog'), $settings, __('Only used when add-to-cart is hidden. Without it, the button area is simply empty.', 'catalog'));
                            ?>
                            <tr>
                                <th scope="row">
                                    <label for="catalog_cta_text"><?php esc_html_e('Button text', 'catalog'); ?></label>
                                    <?php $this->help(__('Wording for the button, e.g. "Enquire now". Leave blank to use the translated default ("Read more").', 'catalog')); ?>
                                </th>
                                <td>
                                    <input type="text" id="catalog_cta_text" name="<?php echo esc_attr(self::OPTION); ?>[cta_text]" value="<?php echo esc_attr((string) ($settings['cta_text'] ?? '')); ?>" class="regular-text" placeholder="<?php esc_attr_e('e.g. Enquire now', 'catalog'); ?>" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="catalog_cta_url"><?php esc_html_e('Button link', 'catalog'); ?></label>
                                    <?php $this->help(__('Where the button goes, e.g. your contact or enquiry page URL. If empty, the button shows but is inert.', 'catalog')); ?>
                                </th>
                                <td>
                                    <input type="url" id="catalog_cta_url" name="<?php echo esc_attr(self::OPTION); ?>[cta_url]" value="<?php echo esc_attr((string) ($settings['cta_url'] ?? '')); ?>" class="regular-text code" placeholder="https://example.com/contact" />
                                </td>
                            </tr>
                            <?php
                            $this->checkboxRow('cta_new_tab', __('Open in a new tab', 'catalog'), __('Open the button link in a new browser tab.', 'catalog'), $settings, __('Adds target="_blank" with safe rel attributes.', 'catalog'));
                            ?>
                        </tbody>
                    </table>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render an accessible inline-help affordance: a "?" button that toggles a
     * popover describing the adjacent setting. Uses the native Popover API and is
     * wired via aria-describedby; the bundled script supplies a fallback.
     */
    private function help(string $text): void
    {
        $id = 'catalog-help-' . (++$this->helpSeq);
        ?>
        <button type="button" class="catalog-help" aria-label="<?php esc_attr_e('More information', 'catalog'); ?>" aria-describedby="<?php echo esc_attr($id); ?>" aria-expanded="false" popovertarget="<?php echo esc_attr($id); ?>">?</button>
        <div id="<?php echo esc_attr($id); ?>" class="catalog-tip" role="tooltip" popover hidden>
            <?php echo esc_html($text); ?>
        </div>
        <?php
    }

    /**
     * Render a single checkbox row in the form-table.
     *
     * @param array<string, mixed> $settings
     */
    private function checkboxRow(string $key, string $label, string $help, array $settings, string $tip = ''): void
    {
        $id = 'catalog_' . $key;
        ?>
        <tr>
            <th scope="row">
                <?php echo esc_html($label); ?>
                <?php
                if ('' !== $tip) {
                    $this->help($tip);
                }
                ?>
            </th>
            <td>
                <label for="<?php echo esc_attr($id); ?>">
                    <input type="checkbox" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr(self::OPTION); ?>[<?php echo esc_attr($key); ?>]" value="1" <?php checked((bool) ($settings[$key] ?? false), true); ?> />
                    <?php echo esc_html($help); ?>
                </label>
            </td>
        </tr>
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

        $scope = isset($raw['scope']) ? sanitize_key((string) $raw['scope']) : 'all';
        if (! in_array($scope, self::SCOPES, true)) {
            $scope = 'all';
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

        $sanitized = [
            'enabled'          => ! empty($raw['enabled']),
            'hide_price'       => ! empty($raw['hide_price']),
            'hide_add_to_cart' => ! empty($raw['hide_add_to_cart']),
            'price_notice'     => $this->text($raw, 'price_notice'),

            'scope'           => $scope,
            'apply_on_single' => ! empty($raw['apply_on_single']),
            'apply_on_loop'   => ! empty($raw['apply_on_loop']),

            'role_mode' => $roleMode,
            'role_list' => $roleList,

            'cta_enabled' => ! empty($raw['cta_enabled']),
            'cta_text'    => $this->text($raw, 'cta_text'),
            'cta_url'     => isset($raw['cta_url']) ? esc_url_raw(trim((string) $raw['cta_url'])) : '',
            'cta_new_tab' => ! empty($raw['cta_new_tab']),
        ];

        /**
         * Filter the sanitised settings before they are stored.
         *
         * @param array<string, mixed> $sanitized The sanitised settings.
         * @param array<string, mixed> $raw       The raw submitted input.
         */
        return (array) apply_filters('catalog/sanitize_settings', $sanitized, $raw);
    }

    /**
     * Sanitise a single text field from the raw input.
     *
     * @param array<string, mixed> $raw
     */
    private function text(array $raw, string $key): string
    {
        return isset($raw[$key]) ? sanitize_text_field((string) $raw[$key]) : '';
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
