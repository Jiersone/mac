<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * Override this template by copying it to yourtheme/woocommerce/content-single-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


global $post, $woocommerce, $product;

?>

<?php
	/**
	 * woocommerce_before_single_product hook
	 *
	 * @hooked woocommerce_show_messages - 10
	 */
	 do_action( 'woocommerce_before_single_product' );
?>
<div itemscope itemtype="http://schema.org/Product" id="product-<?php the_ID(); ?>" <?php post_class(); ?> class="single-product">
	<?php 
		$mymeta = get_post_meta( get_the_ID(), 'carousel' );
		echo do_shortcode($mymeta[0]);
	?>
	
	<div class="row">
		<div class="col-md-9 clsContent">
			<h1 class="clsBotTitle">
				<?php _e('Choose a ','woocommerce').the_title(); ?>
			</h1>
			<div class='row'>
					<div class="entry-summary col-md-6">
						<?php
							/**
							* woocommerce_single_product_summary hook
							*
							* @hooked woocommerce_template_single_title - 5
							* @hooked woocommerce_template_single_price - 10
							* @hooked woocommerce_template_single_excerpt - 20
							* @hooked woocommerce_template_single_add_to_cart - 30
							* @hooked woocommerce_template_single_meta - 40
							* @hooked woocommerce_template_single_sharing - 50		 
							*/
							remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
							remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
							remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
							do_action('woocommerce_single_product_summary');
						?>
					</div><!-- .summary -->	
					<div class='col-md-4'>
					<?php
						/**
						* woocommerce_show_product_images hook
						*
						* @hooked woocommerce_show_product_sale_flash - 10
						* @hooked woocommerce_show_product_images - 20
						*/
						do_action( 'woocommerce_before_single_product_summary' );
					?>
					</div> <!-- col-md-4' -->
			</div> <!-- row -->
			<?php
				/**
				* woocommerce_after_single_product_summary hook
				*
				* @hooked woocommerce_output_product_data_tabs - 10
				* @hooked woocommerce_output_related_products - 20
				*/
				//do_action( 'woocommerce_after_single_product_summary' );
			?>
			</div>
			<?php if( $post->post_excerpt ): ?>
				<div class="clsExc">
					<?php the_excerpt(); ?>
				</div>
			<?php endif; ?>
			<?php the_content(); ?>
		</div><!-- end of col-md-9 -->
		<?php
		// if($product->product_type == "variable"): 
		?>
		
		<div class="col-md-3">
			
			<div id="idSticky" class="clsSticky">
				<div class="clsSidebar prodSideBar">
					<h3><?php _e('Abstract','woocommerce'); ?></h3>
					<div class="row">  
					<?php 
						//print_r($product);
							
							$available_variations = $product->get_available_variations();
							$attributes = $product->get_variation_attributes();
							$selected_attributes = $product-> get_variation_default_attributes();
							
						if ( ! empty( $available_variations ) ) : ?>
							<form class="variations_form cart" method="post" enctype='multipart/form-data' data-product_id="<?php echo $post->ID; ?>" data-product_variations="<?php echo esc_attr( json_encode( $available_variations ) ) ?>">
									<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>
									<div class="clsDetails clsDetailsImg images">
										<img class="variation_image v-image" src=""/>
									</div>
									<?php
									$loop = 0; foreach ( $attributes as $name => $options ) : $loop++; 
											
											if ( is_array( $options ) ) {
													if ( empty( $_POST ) )
															$selected_value = ( isset( $selected_attributes[ sanitize_title( $name ) ] ) ) ? $selected_attributes[ sanitize_title( $name ) ] : '';
													else
															$selected_value = isset( $_POST[ 'attribute_' . sanitize_title( $name ) ] ) ? $_POST[ 'attribute_' . sanitize_title( $name ) ] : '';
					//echo   $selected_value;
													// Get terms if this is a taxonomy - ordered
													if ( taxonomy_exists( sanitize_title( $name ) ) ) {

															$terms = get_terms( sanitize_title($name), array('menu_order' => 'ASC') );
						
															foreach ( $terms as $term ) {
																	if ( ! in_array( $term->slug, $options ) ) continue;
																	if(strtolower ($selected_value) == strtolower ($term->slug))
																	echo '<input type="hidden" value="' . strtolower($term->slug) . '" ' . 
																	checked( strtolower ($selected_value), strtolower ($term->slug), false ) . ' 
																	id="attribute_'. esc_attr( sanitize_title($name) ) .'_SidBar"
																	name="attribute_'. sanitize_title($name).'">' . apply_filters( 'woocommerce_variation_option_name', $term->name );
															}
													} else {
															foreach ( $options as $option ){
																	if(sanitize_title( $selected_value ) == sanitize_title( $option ))
																	echo '<input type="hidden" value="' .esc_attr( sanitize_title( $option ) ) . '" 
																	id="attribute_'. esc_attr( sanitize_title($name) ) .'_SidBar" name="attribute_'. sanitize_title($name).'">' ;
																	elseif ($selected_value == ""){
																		echo '<input type="hidden" value="" 
																			id="attribute_'. esc_attr( sanitize_title($name) ) .'_SidBar" name="attribute_'. sanitize_title($name).'">' ;	
																		break;
																	}
																	
															}
													}
											}
									endforeach;?>
									<div class="clsDetails">
										<div class="clsSubDiv" id="prodtitle" title="<?php the_title(); ?>"><?php the_title(); ?></div>	
										<div class="clsSubDiv" id="sku"></div>
										<div class="clsSubDiv" id="shipping"></div>
										<div class="clsSubDiv" id="price"></div>	
									</div>

									<div class="single_variation_wrap clsDetails">
										<?php do_action( 'woocommerce_before_single_variation' ); ?>

										<div class="single_variation"></div>

										<div class="variations_button">
											<?php woocommerce_quantity_input(); ?>
											<button type="submit" class="single_add_to_cart_button button alt"><?php echo apply_filters('single_add_to_cart_text', __( 'Add to cart', 'woocommerce' ), $product->product_type); ?></button>
										</div>

										<input type="hidden" name="add-to-cart" value="<?php echo $product->id; ?>" />
										<input type="hidden" name="product_id" value="<?php echo esc_attr( $post->ID ); ?>" />
										<input type="hidden" name="variation_id" value="" />

										<?php do_action( 'woocommerce_after_single_variation' ); ?>
									</div>

									<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

								<?php else : ?>

									<p class="stock out-of-stock"><?php _e( 'This product is currently out of stock and unavailable.', 'woocommerce' ); ?></p>

								<?php endif; ?>				 
							</form>
					</div>
				</div>
				
				<div class="clsSidebar">
					<p class="clsBotDetails clsBotTitle"><?php _e('More information on how to buy your ','woocommerce').the_title(); ?></p>
					<p class="clsBotDetails" id="scroll-top"><a href="#"><?php _e('General description','woocommerce'); ?></a></p>
					<p class="clsBotDetails" id="des-top"><a href="#" ><?php _e('Technical specifications','woocommerce'); ?></a></p>
					<p class="clsBotDetails">
						<a href="#" Id="showImage"><?php _e('View Gallery','woocommerce'); ?></a>
					</p>
					<?php echo do_shortcode( "[av_sidebar widget_area='Single Product Contact']" ) ?>
				</div> <!-- clsSidebar -->
			</div> <!-- clsSticky -->
			
		</div> <!-- end of col-md-3 -->
		<?php
		// endif; // checked if its variable product
		?>
	</div> <!-- end of row -->
</div><!-- #product-<?php the_ID(); ?> -->

<?php do_action( 'woocommerce_after_single_product' ); ?>
