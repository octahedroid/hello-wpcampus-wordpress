<?php

/**
 * Plugin Name: Build Hooks
 * Description: This plugin allows you to trigger a build hook on Gatsby Cloud service.
*/

require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

add_action( 'admin_menu', 'register_admin_page' );

function register_admin_page() {
  add_menu_page(
    'Build Hooks',
    'Build Hooks', 
    'manage_options', 
    'build-hooks', 
    'build_hooks'
  );
}

if (isset($_POST['action'])) {
  if ($_POST['action'] === 'update_option_build_hooks') {
    setOptionsPantheon($_POST);
  }

  if ($_POST['action'] === 'trigger_build') {
    trigger_build();
  }
}

function setOptionsPantheon($data){
  $option_name = '_build_hooks_webhook';
  $value = $data[$option_name];
  if($value){
    if (get_option( $option_name ) !== false) {
      update_option($option_name, $value);
    } else {
      add_option($option_name, $value, null, 'no');
    }
  } else {
    update_option($option_name, null);
  }
}

function build_hooks() {
  $site_url = site_url('');
  $option_name = '_build_hooks_webhook';
  $url = get_option( $option_name );
  $disabled = !$url?"disabled=disabled":"";

  echo <<<EOF
<div class="wrap">
  <h1>Build Hooks</h1>
  ​<hr />
  <h2>Gatsby Cloud</h2>
  <form method="post" action="$site_url/wp-admin/admin.php?page=build-hooks" novalidate="novalidate">
    <table class="form-table">
      <tbody>
        <tr>
          <th scope="row">Webhook</th>
          <td> 
            <fieldset>
              <legend class="screen-reader-text">Webhook</legend>
                <input type="text" class="full-input" name="$option_name" value="$url" size="96">
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
  <form method="post" action="$site_url/wp-admin/admin.php?page=build-hooks" novalidate="novalidate">
    <div class="submit">
      <input name="action" value="trigger_build" type="hidden">
      <input name="submit" id="submit" $disabled class="button button-primary" value="Trigger Build" type="submit">
    </div>
  </form>
</div>
EOF;
}


function trigger_build() {
  // @TODO store using a settings form
  $url = 'https://webhook.gatsbyjs.com/hooks/data_source/publish/2a931035-e412-4970-ae71-0eddefcea553';
  
  $client = new \GuzzleHttp\Client([
    'headers' => [ 'Content-Type' => 'application/json' ]
  ]);

  $response = $client->post( $url );
}
