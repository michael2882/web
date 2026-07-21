<?php
// 1. Agregar el campo de galería en el panel de cada variación
add_action( 'woocommerce_product_after_variable_attributes', 'mmc_add_variation_gallery_admin', 10, 3 );
function mmc_add_variation_gallery_admin( $loop, $variation_data, $variation ) {
    $gallery_ids = get_post_meta( $variation->ID, '_mmc_variation_gallery_ids', true );
    $attachments = $gallery_ids ? explode( ',', $gallery_ids ) : array();
    ?>
    <div class="form-row form-row-full mmc-variation-gallery-admin">
        <label>Galería de Variación (Ilimitada):</label>
        <div class="mmc-gallery-container">
            <ul class="mmc-gallery-images">
                <?php if ( $attachments ) : foreach ( $attachments as $attachment_id ) : 
                    $image = wp_get_attachment_image_src( $attachment_id, 'thumbnail' ); ?>
                    <li class="image" data-attachment_id="<?php echo $attachment_id; ?>">
                        <img src="<?php echo $image[0]; ?>" />
                        <a href="#" class="delete">×</a>
                    </li>
                <?php endforeach; endif; ?>
            </ul>
            <input type="hidden" class="mmc_variation_gallery_ids" name="mmc_variation_gallery_ids[<?php echo $loop; ?>]" value="<?php echo esc_attr( $gallery_ids ); ?>" />
            <button type="button" class="button add_mmc_variation_gallery">Añadir imágenes a la galería</button>
        </div>
    </div>
    <?php
}

// 2. Guardar los IDs de la galería al guardar el producto
add_action( 'woocommerce_save_product_variation', 'mmc_save_variation_gallery_admin', 10, 2 );
function mmc_save_variation_gallery_admin( $variation_id, $i ) {
    if ( isset( $_POST['mmc_variation_gallery_ids'][$i] ) ) {
        update_post_meta( $variation_id, '_mmc_variation_gallery_ids', sanitize_text_field( $_POST['mmc_variation_gallery_ids'][$i] ) );
    }
}

// 3. Pasar los datos de la galería al Frontend (JavaScript)
add_filter( 'woocommerce_available_variation', 'mmc_add_gallery_data_to_variation' );
function mmc_add_gallery_data_to_variation( $variation_data ) {
    $gallery_ids = get_post_meta( $variation_data['variation_id'], '_mmc_variation_gallery_ids', true );
    $gallery_urls = array();
    
    if ( $gallery_ids ) {
        $ids = explode( ',', $gallery_ids );
        foreach ( $ids as $id ) {
            $full = wp_get_attachment_image_src( $id, 'full' );
            $thumb = wp_get_attachment_image_src( $id, 'thumbnail' );
            if ( $full ) {
                $gallery_urls[] = array( 'full' => $full[0], 'thumb' => $thumb[0] );
            }
        }
    }
    $variation_data['mmc_gallery'] = $gallery_urls;
    return $variation_data;
}

// 4. Script para el Administrador (Media Uploader)
add_action( 'admin_footer', 'mmc_variation_gallery_admin_js' );
function mmc_variation_gallery_admin_js() {
    ?>
    <script>
    jQuery(document).ready(function($){
        $(document).on('click', '.add_mmc_variation_gallery', function(e){
            e.preventDefault();
            var $button = $(this);
            var $wrapper = $button.closest('.mmc-gallery-container');
            var $image_list = $wrapper.find('.mmc-gallery-images');
            var $input = $wrapper.find('.mmc_variation_gallery_ids');
            
            var frame = wp.media({ title: 'Añadir a Galería', button: { text: 'Añadir' }, multiple: true });
            frame.on('select', function() {
                var selection = frame.state().get('selection');
                var ids = $input.val() ? $input.val().split(',') : [];
                selection.map(function(attachment) {
                    attachment = attachment.toJSON();
                    if (attachment.id) {
                        ids.push(attachment.id);
                        $image_list.append('<li class="image" data-attachment_id="'+attachment.id+'"><img src="'+attachment.sizes.thumbnail.url+'" /><a href="#" class="delete">×</a></li>');
                    }
                });
                $input.val(ids.join(',')).trigger('change');
            });
            frame.open();
        });
        $(document).on('click', '.mmc-gallery-images .delete', function(e){
            e.preventDefault();
            var $li = $(this).closest('li');
            var $wrapper = $(this).closest('.mmc-gallery-container');
            var id = $li.data('attachment_id');
            var ids = $wrapper.find('.mmc_variation_gallery_ids').val().split(',');
            ids = ids.filter(function(v){ return v != id; });
            $wrapper.find('.mmc_variation_gallery_ids').val(ids.join(','));
            $li.remove();
        });
    });
    </script>
    <style>.mmc-gallery-images { display: flex; flex-wrap: wrap; gap: 10px; list-style: none; padding: 0; } .mmc-gallery-images li { position: relative; border: 1px solid #ccc; padding: 2px; } .mmc-gallery-images img { width: 60px; height: 60px; object-fit: cover; } .mmc-gallery-images .delete { position: absolute; top: -5px; right: -5px; background: red; color: #fff; border-radius: 50%; width: 15px; height: 15px; text-align: center; font-size: 10px; text-decoration: none; line-height: 14px; }</style>
    <?php
}