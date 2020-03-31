<?php

/**
 * Plugin Name: Build Hooks
 * Description: This plugin allows you to trigger a build hook on Gatsby Cloud service.
 */

require plugin_dir_path(__FILE__) . 'vendor/autoload.php';

add_action('admin_menu', 'register_web_hooks_admin_page');

const BUILD_HOOK_OPTION = '_build_hooks_webhook';
const BUILD_HOOK_SETTINGS_OPTION = '_build_hooks_settings';
const BUILD_HOOK_TRIGGER_OPTION ='_build_hooks_trigger';

function bypass_option() {
  return in_array(
    current_user_role(),
    [
      'super_admin',
      'administrator'
    ]
  );
}

function current_user_role() {
  $current_user = wp_get_current_user();

  return $current_user->roles[0];
}

function settings_option() {
  if (bypass_option()) {
    return true;
  }

  $settings = get_option(BUILD_HOOK_SETTINGS_OPTION, []);

  return in_array(current_user_role(), $settings);
}

function trigger_option() {
  if (bypass_option()) {
    return true;
  }

  $trigger = get_option(BUILD_HOOK_TRIGGER_OPTION, []);

  return in_array(current_user_role(), $trigger);
}

function register_web_hooks_admin_page()
{
  if (trigger_option()) {
      add_menu_page(
          'Build Hooks',
          'Build Hooks',
          'edit_pages',
          'build-hooks',
          'build_hooks',
          'dashicons-cloud'
      );
    }

  if (settings_option()) {
    add_submenu_page(
      'build-hooks',
      'Settings',
      'Settings',
      'edit_pages',
      'build-hooks-settings',
      'build_hooks_settings'
    );
  }
}

if (isset($_POST['action'])) {
    if ($_POST['action'] === 'update_option_build_hooks') {
        setOptionsPantheon($_POST);
    }

    if ($_POST['action'] === 'trigger_build') {
        trigger_build();
    }
}

function setOptionsPantheon($data)
{
    $web_hook = $data[BUILD_HOOK_OPTION];
    $settings = $data[BUILD_HOOK_SETTINGS_OPTION];
    $trigger = $data[BUILD_HOOK_TRIGGER_OPTION];
    
    if ($web_hook) {
      update_option(BUILD_HOOK_OPTION, $web_hook);
    } else {
      update_option(BUILD_HOOK_OPTION, null);
    }
    if ($settings) {
      update_option(BUILD_HOOK_SETTINGS_OPTION, $settings);
    } else {
      update_option(BUILD_HOOK_SETTINGS_OPTION, null);
    }
    if ($trigger) {
      update_option(BUILD_HOOK_TRIGGER_OPTION, $trigger);
    } else {
      update_option(BUILD_HOOK_TRIGGER_OPTION, null);
    }
}

function build_hooks()
{
    $url = get_option(BUILD_HOOK_OPTION);
    $trigger = get_option(BUILD_HOOK_TRIGGER_OPTION);
    $settings = get_option(BUILD_HOOK_SETTINGS_OPTION);
    $current_user = wp_get_current_user();
    $current_role = $current_user->roles[0];

    ?>
      <div class="wrap">
        <h1>Build Hooks</h1>
        ​<hr />
        <h2>Web Hook</h2>
          <table class="form-table">
            <tbody>
              <tr>
                <th scope="row">Current Webhook</th>
                <td>
                  <fieldset>
                    <legend class="screen-reader-text">Current Webhook</legend>
                      <input type="text" class="full-input" name="<?php echo BUILD_HOOK_OPTION ?>" disabled read-only value="<?php echo $url ?>" size="96">
                  </fieldset>
                </td>
              </tr>
            </tbody>
          </table>
          <?php if(trigger_option()||settings_option()) : ?>
          <hr />
          <h2>Trigger</h2>
          <form method="post" action="/wp-admin/admin.php?page=build-hooks" novalidate="novalidate">
            <div class="submit">
              <input name="action" value="trigger_build" type="hidden">
              <input name="submit" id="submit" <?php if (!$url) { echo "disabled=disabled"; } ?> class="button button-primary" value="Trigger Build" type="submit">
            </div>
          </form>
          <?php endif; ?>
      </div>
    <?php

}

function build_hooks_settings()
{
    $url = get_option(BUILD_HOOK_OPTION);
    $settings = get_option(BUILD_HOOK_SETTINGS_OPTION);
    $trigger = get_option(BUILD_HOOK_TRIGGER_OPTION);
    $roles = get_editable_roles();

    ?>
      <div class="wrap">
        <h1>Settings</h1>
        ​<hr />
        <h2>Web Hook</h2>
        <form method="post" action="<?php $_SERVER['PHP_SELF']?>" novalidate="novalidate">
          <table class="form-table">
            <tbody>
              <tr>
                <th scope="row">Webhook</th>
                <td>
                  <fieldset>
                    <legend class="screen-reader-text">Webhook</legend>
                      <input type="text" class="full-input" name="<?php echo BUILD_HOOK_OPTION ?>" value="<?php echo $url ?>" size="96">
                  </fieldset>
                </td>
              </tr>
            </tbody>
          </table>

        ​<hr />
        <h2>Roles with settings capabilities</h2>
          <table class="form-table">
            <tbody>
              <tr>
                <th scope="row">Roles</th>
                <td>
                  <fieldset>
                    <legend class="screen-reader-text">Roles</legend>
                    <input type="hidden" name="<?php echo BUILD_HOOK_SETTINGS_OPTION ?>[]" value="administrator">
                      <?php foreach ($roles as $key => $role) {
                        ?>
                          <label for="<?php echo BUILD_HOOK_SETTINGS_OPTION.'_'.$key ?>">
                            <input type="checkbox" <?php echo $key == 'administrator'?'checked disabled':'' ?> <?php echo in_array($key, $settings) ?'checked':'' ?> name="<?php echo BUILD_HOOK_SETTINGS_OPTION ?>[]" id="<?php echo BUILD_HOOK_SETTINGS_OPTION .'_'.$key ?>" value="<?php echo $key ?>"> <?php echo $role['name'] ?>
                          </label><br />
                        <?php
                      } ?>
                  </fieldset>
                </td>
              </tr>
            </tbody>
          </table>
        ​<hr />
        <h2>Roles with trigger build capabilities</h2>
          <table class="form-table">
            <tbody>
              <tr>
                <th scope="row">Roles</th>
                <td>
                  <fieldset>
                    <legend class="screen-reader-text">Roles</legend>
                    <input type="hidden" name="<?php echo BUILD_HOOK_TRIGGER_OPTION ?>[]" value="administrator">
                      <?php foreach ($roles as $key => $role) {
                        ?>
                          <label for="<?php echo BUILD_HOOK_TRIGGER_OPTION.'_'.$key ?>">
                            <input type="checkbox" <?php echo $key == 'administrator'?'checked disabled':'' ?> <?php echo in_array($key, $trigger) ?'checked':'' ?> name="<?php echo BUILD_HOOK_TRIGGER_OPTION ?>[]" id="<?php echo $trigger_option.'_'.$key ?>" value="<?php echo $key ?>"> <?php echo $role['name'] ?>
                          </label><br />
                        <?php
                      } ?>
                  </fieldset>
                </td>
              </tr>
            </tbody>
          </table>
          <div class="submit">
              <input name="action" value="update_option_build_hooks" type="hidden">
              <input name="submit" id="submit" class="button button-primary" value="Save changes" type="submit">
          </div>
        </form>
      </div>
    <?php
}

function trigger_build()
{
    $option_name = '_build_hooks_webhook';
    $url = get_option($option_name);

    $client = new \GuzzleHttp\Client([
        'headers' => ['Content-Type' => 'application/json'],
    ]);

    $response = $client->post($url);
}
