<?php

// Plugin Display Class
class Bigcommerce_display {

	// Handle Shortcodes
	function shortcode( $atts, $content ) {
		extract(
			shortcode_atts(
				array(
					'link' => '',
					'rel' => '',
					'target' => '',
					'nofollow' => '',
					'category' => '',
				), $atts
			)
		);

		// Handle Category Lookup
		if( $category ) {
			return Bigcommerce_parser::DisplayProductsInCategory( $category );
		}

		// Handle Link
		if( $rel ) { $rel = " rel='{$rel}'"; }
		if( $target ) { $target = " target='{$target}'"; };
		if( $nofollow ) { $nofollow = " nofollow='nofollow'"; };
		$extra = "{$rel}{$target}{$nofollow}";
		$storepath = Bigcommerce_parser::storepath();
		return "<a href='{$storepath}{$link}/'{$extra}>{$content}</a>";
	}

	// Give Thanks Footer Link
	function wp_footer() {
		$options = Bigcommerce_settings::get_options();
		if( ! empty( $options->showlink ) && $options->showlink == 'yes' ) {
			echo '
				<p style="text-align:center;">
					This site uses the
					<a href="http://wordpress.org/extend/plugins/interspire-bigcommerce/">
					Bigcommerce WordPress Plugin</a>
				</p>
			';
		}
	}

	// Products Listings Row
	function DisplayProductRow( $data ) {
		$storepath = Bigcommerce_parser::storepath();
		return "
			<div class='bigcommerce-row'>
				<h2 class='title {$data->is_featured}'>{$data->name}</h2>
				<div style='padding:10px 20px;'>
					<a href='{$data->image}' title='Click to enlarge'>
						<img src='{$data->image}'
							style='float:left;max-width:35%;max-height:200px;padding:10px;' />
					</a>
					<table style='border:0;width:55%;float:right;'>
						<tbody>
							<tr>
								<th>SKU</th>
								<td>{$data->sku}</td>
							</tr>
							<tr>
								<th>Availibility</th>
								<td>{$data->availability}</td>
							</tr>
							<tr>
								<th>Condition</th>
								<td>{$data->condition}</td>
							</tr>
							<tr>
								<th>Price</th>
								<td>{$data->price}</td>
							</tr>
							<tr>
								<th>Warranty</th>
								<td>{$data->warranty}</td>
							</tr>
							<tr>
								<th>Rating</th>
								<td>{$data->rating}</td>
							</tr>
							<tr>
								<th></th>
								<td>
									<a href='{$storepath}{$data->link}/'
										title='View the main store page'>
										More Info<br />
										Buy Now
									</a>
								</td>
							</tr>
						</tbody>
					</table>
					<div style='clear:both;'></div>
				</div>
			</div>
		";
	}
}

?>