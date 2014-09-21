<?php
/**
 * The template for displaying all single posts
 *
 * @package WordPress
 * @subpackage Twenty_Thirteen
 * @since Twenty Thirteen 1.0
 */

get_header(); 
$style_width = '';
if( get_option( 'portfolio_container_width' ) ) {
	$style_width = 'style="width:'.get_option('portfolio_container_width').'px;"';
	}
?>

<div class="portfolio-container" <?php echo $style_width; ?>>

		<?php /* The loop */ ?>
		<?php while ( have_posts() ) : the_post(); ?>
		<div class="portfolio-single">	
			<?php if ( has_post_thumbnail()) { ?>
				<?php the_post_thumbnail('portfolio-img'); ?>
			<?php } else { ?>
				<div style="background:url(<?php echo plugins_url( '/showcase-portfolio/images/no-image-img.jpg' ) ?>);width:<?php echo get_option('portfolio_img_size_w', '600'); ?>px;height:<?php echo get_option('portfolio_img_size_h', '400'); ?>px" title="No Image"></div>
			<?php } ?>
			
			<h3><?php the_title(); ?></h3>
						
			<p><?php the_content(); ?></p>
			
			<!-- Showcase Portfolio Meta -->
			<?php if(get_post_meta( get_the_ID(), 'client_name', true) ) { ?>
				<p class="portfolio-text">
					<strong>Client's Name:</strong>
					<span class="portfolio-text-desc">
						<?php echo esc_html( get_post_meta( get_the_ID(), 'client_name', true ) ); ?>
					</span>
				</p>
			<?php } ?>
			
			<?php if(get_post_meta( get_the_ID(), 'my_role', true) ) { ?>
				<p class="portfolio-text">
					<strong>My Role:</strong>
					<span class="portfolio-text-desc">
						<?php echo esc_html( get_post_meta( get_the_ID(), 'my_role', true ) ); ?>
					</span>
				</p>
			<?php } ?>
			
			<?php if(get_post_meta( get_the_ID(), 'date_accomplished', true) ) { ?>
				<p class="portfolio-text">
					<strong>Date Accomplished:</strong>
					<span class="portfolio-text-desc">
						<?php echo esc_html( get_post_meta( get_the_ID(), 'date_accomplished', true ) ); ?>
					</span>
				</p>
			<?php } ?>
			
			<p class="portfolio-text">
				<strong>Category:</strong>
				<span class="portfolio-text-desc">
					<?php
						$terms_as_text = get_the_term_list( $post->ID, 'shc-portfolio-category', '', ', ', '' ) ;
						echo strip_tags($terms_as_text);
					?>
				</span>
			</p>

			<?php if(get_post_meta( get_the_ID(), 'portfolio_link', true) ) { ?>	
				<p class="portfolio-text">
					<a href="<?php echo esc_html( get_post_meta( get_the_ID(), 'portfolio_link', true ) ); ?>" class="button" target="_blank">Link to Website</a>
				</p>
			<?php } ?>
			
		</div>
		<?php endwhile; ?>

</div>

<?php get_footer(); ?>