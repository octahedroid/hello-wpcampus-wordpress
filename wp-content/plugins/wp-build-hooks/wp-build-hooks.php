<?php

/**
 * Plugin Name: Build Hooks
 * Description: This plugin allows you to trigger a build hook on Gatsby Cloud service.
 */

require plugin_dir_path(__FILE__) . 'vendor/autoload.php';

add_action('admin_menu', 'register_web_hooks_admin_page');

function register_web_hooks_admin_page()
{
  $settings_option = '_settings_webhook';
  $trigger_option = '_trigger_webhook';
  $settings = get_option($settings_option);
  $trigger = get_option($trigger_option);
  $current_user = wp_get_current_user();
  $current_role = $current_user->roles[0];
  add_menu_page(
      'Build Hooks',
      'Build Hooks',
      'edit_pages',
      'build-hooks',
      'build_hooks',
      'dashicons-cloud'
  );
  if (in_array($current_role, $trigger)) {
    add_submenu_page('build-hooks', 'Web Hooks', 'Web Hooks', 'edit_pages', 'build-hooks', 'build_hooks');
  }
  if (in_array($current_role, $settings)) {
    add_submenu_page('build-hooks', 'Settings', 'Settings', 'edit_pages', 'build-hooks-settings', 'build_hooks_settings');
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
    $build_hook_option = '_build_hooks_webhook';
    $settings_option = '_settings_webhook';
    $trigger_option = '_trigger_webhook';
    $web_hook = $data[$build_hook_option];
    $settings = $data[$settings_option];
    $trigger = $data[$trigger_option];
    
    if ($web_hook) {
      update_option($build_hook_option, $web_hook);
    } else {
      update_option($build_hook_option, null);
    }
    if ($settings) {
      update_option($settings_option, $settings);
    } else {
      update_option($settings_option, null);
    }
    if ($trigger) {
      update_option($trigger_option, $trigger);
    } else {
      update_option($trigger_option, null);
    }
}

function build_hooks()
{
    $build_hook_option = '_build_hooks_webhook';
    $trigger_option = '_trigger_webhook';
    $settings_option = '_settings_webhook';
    $url = get_option($build_hook_option);
    $current_user = wp_get_current_user();
    $current_role = $current_user->roles[0];
    $trigger = get_option($trigger_option);
    $settings = get_option($settings_option);

    ?>
      <div class="wrap">
        <h1>Build Hooks</h1>
        ​<hr />
        <h2>Web Hooks</h2>
          <table class="form-table">
            <tbody>
              <tr>
                <th scope="row">Current Webhook</th>
                <td>
                  <fieldset>
                    <legend class="screen-reader-text">Current Webhook</legend>
                      <input type="text" class="full-input" name="<?php echo $build_hook_option ?>" disabled read-only value="<?php echo $url ?>" size="96">
                  </fieldset>
                </td>
              </tr>
            </tbody>
          </table>
      <?php if(in_array($current_role, $trigger)||in_array($current_role, $settings)) : ?>
        <hr />
        <h2>Trigger Hooks</h2>
        <form method="post" action="$site_url/wp-admin/admin.php?page=build-hooks" novalidate="novalidate">
          <div class="submit">
            <input name="action" value="trigger_build" type="hidden">
            <input name="submit" id="submit" $disabled class="button button-primary" value="Trigger Build" type="submit">
          </div>
        </form>
      <?php endif; ?>
      </div>
    <?php

}

function build_hooks_settings()
{
    $build_hook_option = '_build_hooks_webhook';
    $settings_option = '_settings_webhook';
    $trigger_option = '_trigger_webhook';
    $url = get_option($build_hook_option);
    $settings = get_option($settings_option);
    $trigger = get_option($trigger_option);
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
                      <input type="text" class="full-input" name="<?php echo $build_hook_option ?>" value="<?php echo $url ?>" size="96">
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
                    <input type="hidden" name="<?php echo $settings_option ?>[]" value="administrator">
                      <?php foreach ($roles as $key => $role) {
                        ?>
                          <label for="<?php echo $settings_option.'_'.$key ?>">
                            <input type="checkbox" <?php echo $key == 'administrator'?'checked disabled':'' ?> <?php echo in_array($key, $settings) ?'checked':'' ?> name="<?php echo $settings_option ?>[]" id="<?php echo $settings_option.'_'.$key ?>" value="<?php echo $key ?>"> <?php echo $role['name'] ?> 
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
                    <input type="hidden" name="<?php echo $trigger_option ?>[]" value="administrator">
                      <?php foreach ($roles as $key => $role) {
                        ?>
                          <label for="<?php echo $trigger_option.'_'.$key ?>">
                            <input type="checkbox" <?php echo $key == 'administrator'?'checked disabled':'' ?> <?php echo in_array($key, $trigger) ?'checked':'' ?> name="<?php echo $trigger_option ?>[]" id="<?php echo $trigger_option.'_'.$key ?>" value="<?php echo $key ?>"> <?php echo $role['name'] ?> 
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
    // @TODO store using a settings form
    $option_name = '_build_hooks_webhook';
    $url = get_option($option_name);
    // $url = 'https://webhook.gatsbyjs.com/hooks/data_source/publish/2a931035-e412-4970-ae71-0eddefcea553';

    $client = new \GuzzleHttp\Client([
        'headers' => ['Content-Type' => 'application/json'],
    ]);

    $response = $client->post($url);
}
