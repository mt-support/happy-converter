<?php
/**
 * Main admin view for the converter.
 *
 * @var Modern_Tribe\Support_Team\Happy_Converter\Sources\Data_Source[] $converters
 */
?>
<div class="wrap tec-happy-converter">
	<h2>The Events Calendar | Happy Converter</h2>
	<div class="notice">
		<p>
			<?php
				printf(
					esc_html(
						/* Translator note: placeholders %1$s and %2$s represent opening and closing 'strong' tags, respectively. */
						__( 'This tool is provided in the hope that it will be useful, but comes with no guarantees. %1$sYou%2$s are responsible for the safety of your data. %1$sMake a backup first!%2$s ', 'tec-happy-converter' )
					),
					'<strong>',
					'</strong>'
				);
			?>
		</p>
	</div>
	<?php foreach ( $converters as $converter ): ?>
		<div
			class="converter <?php echo esc_attr( $converter->unconverted_data_exists() ? 'active' : 'inactive' ); ?>"
			id="<?php echo esc_attr( $converter->get_id() ); ?>"
		>
			<h4><?php echo esc_html( $converter->get_name() ); ?></h4>

			<div class="counts">
				<span class="total-nodes">
					<?php esc_html_e( 'Total nodes:', 'tec-happy-converter' ); ?>
					<span class="count-value">
						<?php esc_html_e( $converter->count_all_data_nodes() ); ?>
					</span>
				</span>

				<span class="converted-nodes">
					<?php esc_html_e( 'Converted:', 'tec-happy-converter' ); ?>
					<span class="count-value">
						<?php esc_html_e( $converter->count_converted_data_nodes() ); ?>
					</span>
				</span>

				<span class="unconverted-nodes">
					<?php esc_html_e( 'Unconverted:', 'tec-happy-converter' ); ?>
					<span class="count-value">
						<?php esc_html_e( $converter->count_unconverted_data_nodes() ); ?>
					</span>
				</span>
			</div>

			<div class="tools">
				<button class="run-converter button-secondary">
					<?php echo esc_html_x( 'Convert', 'Convert button', 'tec-happy-converter' ); ?>
				</button>

				<div class="progress-indicator">
					<div class="marker"></div>
				</div>
			</div>

			<?php if ( $converter->count_unconverted_data_nodes() === 0) : ?>
				<div class="flag success">Job complete!</div>
			<?php else: ?>
				<div class="flag"></div>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>

	<?php if ( empty( $converters ) ): ?>
		<p>
			<?php esc_html_e( __( 'Sorry, no converters are available.', 'tec-happy-converter' ) ) ?>
		</p>
	<?php endif; ?>
</div>