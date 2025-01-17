<?php

    echo '<form method="post">'."\n";
    echo '<input type="hidden" value="'.wp_create_nonce("lh_add_media_from_url-file_url").'" name="lh_add_media_from_url-nonce" id="lh_add_media_from_url-nonce" />'."\n";
    echo '<table class="form-table">'."\n";
    echo '<tr valign="top">'."\n";
    echo '<th scope="row">'."\n";
    echo '<label for="lh_add_media_from_url-file_url">';
    echo __( 'URL', self::return_plugin_text_domain());
    echo '</label>'."\n";
    echo '</th>'."\n";
    echo '<td>'."\n";
    echo '<input type="url" name="lh_add_media_from_url-file_url" id="lh_add_media_from_url-file_url" value="';
    if (!empty($value)){
    
        echo esc_url($value, array('http','https'));
        
    }
    echo '" size="50" />'."\n";
    echo '</td>'."\n";
    echo '</tr>'."\n";
    echo '</table>'."\n";
    submit_button( 'Submit' );
    echo '</form>'."\n";
?>