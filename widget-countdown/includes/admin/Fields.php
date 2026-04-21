<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Admin Fields — renders form field HTML for admin settings pages.
 *
 * Every output is properly escaped. Each method is self-contained.
 * Also provides color and animation utility helpers used across the plugin.
 */
class Wpda_Countdown_Admin_Fields {

	/**
	 * Top admin-page banner with pro upgrade card (free only) + support link.
	 * Called from every admin page EXCEPT Featured Plugins and Hire an Expert.
	 */
	public static function render_pro_banner() {
		?>
		<div class="wpdevart_plugins_header div-for-clear">
			<?php if ( ! WPDA_COUNTDOWN_IS_PRO ) : ?>
				<div class="wpdevart_plugins_get_pro div-for-clear">
					<div class="wpdevart_plugins_get_pro_info">
						<h3>WpDevArt Countdown Premium</h3>
						<p>Powerful and Customizable Countdown Timer</p>
					</div>
					<a target="_blank" href="https://wpdevart.com/wordpress-countdown-plugin/" class="wpdevart_upgrade">Upgrade</a>
				</div>
			<?php endif; ?>
			<a target="_blank" href="<?php echo esc_url( wpdevart_countdown_support_url ); ?>" class="wpdevart_support">Have any Questions? Get quick support!</a>
		</div>
		<?php
		// Free-only: alert users when they pick a Pro-labeled option from a dropdown
		if ( ! WPDA_COUNTDOWN_IS_PRO ) {
			self::print_pro_lock_script();
		}
	}

	/**
	 * Free-mode interactive locks:
	 *   1. A `(Pro)` option inside a dropdown reverts with an alert.
	 *   2. Any interaction inside a fully pro-locked page (Popup, Sticky Bar)
	 *      intercepts and shows the same alert.
	 *   3. Styles the "(pro)" badge appended by description_cell().
	 */
	private static function print_pro_lock_script() {
		$alert_msg = 'This feature is available only in the Pro version. Click the Upgrade button at the top of the page to learn more.';
		?>
		<style>
			.wpda_pro_badge{display:inline-block;margin-left:6px;padding:1px 7px;font-size:11px;font-weight:600;color:#7052fb;background:rgba(112,82,251,0.08);border:1px solid rgba(112,82,251,0.25);border-radius:10px;vertical-align:middle;letter-spacing:.3px;}
			.wpda_pro_locked_page{position:relative;}
		</style>
		<script>
		(function(){
			var PRO_ALERT = <?php echo wp_json_encode( $alert_msg ); ?>;
			var lastAlertAt = 0;
			function showProAlert(){
				var now = Date.now();
				if (now - lastAlertAt < 400) return; // de-dup rapid event bursts
				lastAlertAt = now;
				alert(PRO_ALERT);
			}

			function lockProOptions(){
				document.querySelectorAll('select').forEach(function(sel){
					var hasPro = Array.prototype.some.call(sel.options, function(o){
						return o.textContent.indexOf('(Pro)') !== -1;
					});
					if (!hasPro) return;
					sel.addEventListener('focus', function(){ this._prevVal = this.value; });
					sel.addEventListener('change', function(){
						var opt = this.options[this.selectedIndex];
						if (opt && opt.textContent.indexOf('(Pro)') !== -1) {
							showProAlert();
							this.value = (typeof this._prevVal !== 'undefined') ? this._prevVal : '';
							if (!this.value) {
								for (var i = 0; i < this.options.length; i++) {
									if (this.options[i].textContent.indexOf('(Pro)') === -1) {
										this.selectedIndex = i;
										break;
									}
								}
							}
						}
					});
				});
			}

			function lockFullPage(){
				var containers = document.querySelectorAll('.wpda_pro_locked_page');
				if (!containers.length) return;
				var stop = function(e){
					// Allow collapsible section headers to open/close so users can browse settings
					if (e.target.closest && e.target.closest('.head_panel_div')) return;
					e.preventDefault();
					e.stopPropagation();
					showProAlert();
				};
				containers.forEach(function(c){
					['mousedown','click','keydown','change','input'].forEach(function(evt){
						c.addEventListener(evt, stop, true);
					});
				});
			}

			function lockProRows(){
				// Mark any row containing a pro field so CSS + event hooks can find it
				document.querySelectorAll('.wpda_pro_field').forEach(function(cell){
					var row = cell.closest('tr');
					if (row) row.classList.add('wpda_pro_locked_row');
				});
				var stop = function(e){
					e.preventDefault();
					e.stopPropagation();
					showProAlert();
				};
				document.querySelectorAll('.wpda_pro_locked_row').forEach(function(row){
					['mousedown','click','keydown','change','input'].forEach(function(evt){
						row.addEventListener(evt, stop, true);
					});
				});
			}

			function init(){
				lockProOptions();
				lockFullPage();
				lockProRows();
			}
			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', init);
			} else {
				init();
			}
		})();
		</script>
		<?php
	}

	/**
	 * Section heading row.
	 */
	public static function heading( $name, $group ) {
		?>
		<tr class="<?php echo esc_attr( $group ); ?> tr_heading">
			<th colspan="2"><?php echo esc_html( $name ); ?></th>
		</tr>
		<?php
	}

	/**
	 * Left-side description cell (shared by all field types).
	 *
	 * Full-pro field  — $args['pro'] === true. Row gets .wpda_pro_field and the
	 * JS lock blocks every interaction (e.g. popup/sticky fields, theme Pro
	 * spacing/sizing, vertical/circle/flip groups).
	 *
	 * Partial-pro field — at least one option in $args['values'] carries "(Pro)"
	 * but others are free (e.g. Timer type, Repeat, After-timer action, theme
	 * countdown-type). Only the badge shows; option-level lock (lockProOptions)
	 * blocks pro option picks while still letting the user choose free options.
	 */
	public static function description_cell( $args ) {
		$is_full_pro   = ! empty( $args['pro'] ) && $args['pro'] === true;
		$has_pro_option = false;
		if ( ! $is_full_pro && ! empty( $args['values'] ) && is_array( $args['values'] ) ) {
			foreach ( $args['values'] as $v ) {
				if ( is_string( $v ) && strpos( $v, '(Pro)' ) !== false ) { $has_pro_option = true; break; }
				if ( is_array( $v ) ) {
					foreach ( $v as $sub ) {
						if ( is_string( $sub ) && strpos( $sub, '(Pro)' ) !== false ) { $has_pro_option = true; break 2; }
					}
				}
			}
		}
		$show_badge = $is_full_pro || $has_pro_option;
		?>
		<td class="td_option_description<?php echo $is_full_pro ? ' wpda_pro_field' : ''; ?>">
			<?php if ( ! empty( $args['description'] ) ) : ?>
				<span class="wpdevart-info-container">?<span class="wpdevart-info"><?php echo wp_kses_post( $args['description'] ); ?></span></span>
			<?php endif; ?>
			<span class="wpdevart-title"><?php echo esc_html( $args['title'] ); ?></span>
			<?php if ( $show_badge ) : ?>
				<span class="wpda_pro_badge">(pro)</span>
			<?php endif; ?>
		</td>
		<?php
	}

	/**
	 * Simple text/number input.
	 */
	public static function simple_input( $args ) {
		$type  = isset( $args['type'] ) ? $args['type'] : 'text';
		$value = isset( $args['value'] ) ? $args['value'] : '';
		$small = isset( $args['small_text'] ) ? $args['small_text'] : '';
		?>
		<tr class="<?php echo esc_attr( isset( $args['heading_group'] ) ? $args['heading_group'] : '' ); ?> tr_option">
			<?php self::description_cell( $args ); ?>
			<td>
				<input type="<?php echo esc_attr( $type ); ?>"
				       value="<?php echo esc_attr( $value ); ?>"
				       id="<?php echo esc_attr( $args['name'] ); ?>"
				       name="<?php echo esc_attr( $args['name'] ); ?>">
				<?php if ( $small ) : ?>
					<small><?php echo esc_html( $small ); ?></small>
				<?php endif; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Hidden input (no visible row).
	 */
	public static function hidden_input( $args ) {
		// no visible output — value loaded/saved via the options system
	}

	/**
	 * Number input with a unit dropdown (px / %).
	 */
	public static function input_with_unit( $args ) {
		$input_name = $args['name'];
		$unit_name  = isset( $args['unit_name'] ) ? $args['unit_name'] : '';
		$input_val  = isset( $args['value'] ) ? $args['value'] : '';
		$unit_val   = isset( $args['unit_value'] ) ? $args['unit_value'] : ( isset( $args['unit_default'] ) ? $args['unit_default'] : '' );
		$units      = isset( $args['units'] ) ? $args['units'] : array();
		?>
		<tr class="<?php echo esc_attr( isset( $args['heading_group'] ) ? $args['heading_group'] : '' ); ?> tr_option">
			<?php self::description_cell( $args ); ?>
			<td>
				<div style="display:inline-flex;align-items:center;gap:4px;">
					<input type="number"
					       value="<?php echo esc_attr( $input_val ); ?>"
					       id="<?php echo esc_attr( $input_name ); ?>"
					       name="<?php echo esc_attr( $input_name ); ?>"
					       style="width:80px;">
					<select id="<?php echo esc_attr( $unit_name ); ?>"
					        name="<?php echo esc_attr( $unit_name ); ?>"
					        style="min-width:70px;">
						<?php foreach ( $units as $k => $v ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $k, $unit_val ); ?>>
								<?php echo esc_html( $v ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</td>
		</tr>
		<?php
	}

	/**
	 * Multiple small inputs in one row (e.g., days/hours/minutes).
	 */
	public static function many_inputs( $args ) {
		?>
		<tr class="<?php echo esc_attr( isset( $args['heading_group'] ) ? $args['heading_group'] : '' ); ?> tr_option">
			<?php self::description_cell( $args ); ?>
			<td>
				<?php foreach ( $args['values'] as $key => $default ) :
					$type  = isset( $args['type'][ $key ] ) ? $args['type'][ $key ] : 'text';
					$val   = isset( $args['value'][ $key ] ) ? $args['value'][ $key ] : ( isset( $args['default_value'][ $key ] ) ? $args['default_value'][ $key ] : '' );
					$small = isset( $args['small_text'][ $key ] ) ? $args['small_text'][ $key ] : '';
				?>
					<input type="<?php echo esc_attr( $type ); ?>"
					       value="<?php echo esc_attr( $val ); ?>"
					       id="<?php echo esc_attr( $args['name'] . '_' . $key . '_id' ); ?>"
					       name="<?php echo esc_attr( $args['name'] . '[' . $key . ']' ); ?>">
					<?php if ( $small ) : ?>
						<small><?php echo esc_html( $small ); ?></small>
					<?php endif; ?>
				<?php endforeach; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Color picker input.
	 */
	public static function color_input( $args ) {
		$value   = isset( $args['value'] ) ? $args['value'] : '';
		$default = isset( $args['default_value'] ) ? $args['default_value'] : $value;
		?>
		<tr class="<?php echo esc_attr( isset( $args['heading_group'] ) ? $args['heading_group'] : '' ); ?> tr_option">
			<?php self::description_cell( $args ); ?>
			<td class="wpda_color-picker">
				<input type="text" class="color"
				       value="<?php echo esc_attr( $value ); ?>"
				       data-default-color="<?php echo esc_attr( $default ); ?>"
				       id="<?php echo esc_attr( $args['name'] ); ?>"
				       name="<?php echo esc_attr( $args['name'] ); ?>">
				<script type="text/javascript">
					jQuery(document).ready(function() {
						jQuery('#<?php echo esc_js( $args['name'] ); ?>').wpColorPicker();
					});
				</script>
			</td>
		</tr>
		<?php
	}

	/**
	 * Standard <select> dropdown.
	 */
	public static function simple_select( $args ) {
		$current = isset( $args['value'] ) ? $args['value'] : '';
		?>
		<tr class="<?php echo esc_attr( isset( $args['heading_group'] ) ? $args['heading_group'] : '' ); ?> tr_option">
			<?php self::description_cell( $args ); ?>
			<td>
				<select id="<?php echo esc_attr( $args['name'] ); ?>"
				        name="<?php echo esc_attr( $args['name'] ); ?>">
					<?php foreach ( $args['values'] as $key => $value ) :
						if ( ! is_array( $value ) ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $current ); ?>>
								<?php echo esc_html( $value ); ?>
							</option>
						<?php else : ?>
							<optgroup label="<?php echo esc_attr( str_replace( '_', ' ', $key ) ); ?>">
								<?php foreach ( $value as $k1 => $v1 ) : ?>
									<option value="<?php echo esc_attr( $k1 ); ?>" <?php selected( $k1, $current ); ?>>
										<?php echo esc_html( $v1 ); ?>
									</option>
								<?php endforeach; ?>
							</optgroup>
						<?php endif;
					endforeach; ?>
				</select>
			</td>
		</tr>
		<?php
	}

	/**
	 * Checkbox group (e.g., days of week).
	 */
	public static function checkbox_group( $args ) {
		$current = is_array( $args['value'] ) ? $args['value'] : ( is_string( $args['value'] ) ? json_decode( $args['value'], true ) : array() );
		if ( ! is_array( $current ) ) $current = array();
		?>
		<tr class="<?php echo esc_attr( isset( $args['heading_group'] ) ? $args['heading_group'] : '' ); ?> tr_option">
			<?php self::description_cell( $args ); ?>
			<td>
				<div style="display:flex;flex-wrap:wrap;gap:6px 16px;">
					<?php foreach ( $args['values'] as $key => $label ) : ?>
						<label style="display:inline-flex;align-items:center;gap:4px;cursor:pointer;font-size:13px;">
							<input type="checkbox"
							       name="<?php echo esc_attr( $args['name'] ); ?>[]"
							       value="<?php echo esc_attr( $key ); ?>"
							       <?php checked( in_array( $key, $current ) ); ?>>
							<?php echo esc_html( $label ); ?>
						</label>
					<?php endforeach; ?>
				</div>
			</td>
		</tr>
		<?php
	}

	/**
	 * Multi-select with chosen.js.
	 */
	public static function multiplay_select( $args ) {
		?>
		<tr class="<?php echo esc_attr( isset( $args['heading_group'] ) ? $args['heading_group'] : '' ); ?> tr_option">
			<?php self::description_cell( $args ); ?>
			<td class="wpda_color-picker">
				<select id="<?php echo esc_attr( $args['name'] ); ?>"
				        name="<?php echo esc_attr( $args['name'] ); ?>"
				        class="chosen-select" multiple="" tabindex="-1" style="display: none;">
					<?php echo self::build_page_options( isset( $args['value'] ) ? $args['value'] : array() ); ?>
				</select>
				<script type="text/javascript">
					jQuery(document).ready(function() {
						jQuery('#<?php echo esc_js( $args['name'] ); ?>').chosen({width: '98%'});
					});
				</script>
			</td>
		</tr>
		<?php
	}

	/**
	 * Font family select.
	 */
	public static function font_select( $args ) {
		$current = isset( $args['value'] ) ? $args['value'] : '';
		?>
		<tr class="<?php echo esc_attr( isset( $args['heading_group'] ) ? $args['heading_group'] : '' ); ?> tr_option">
			<?php self::description_cell( $args ); ?>
			<td>
				<select id="<?php echo esc_attr( $args['name'] ); ?>"
				        name="<?php echo esc_attr( $args['name'] ); ?>">
					<?php foreach ( $args['values'] as $key => $label ) : ?>
						<option style="font-family:<?php echo esc_attr( $key ); ?>"
						        value="<?php echo esc_attr( $key ); ?>"
						        <?php selected( $key, $current ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<?php
	}

	/**
	 * Checkbox list (legacy format with key=>value checked array).
	 */
	public static function simple_checkbox( $args ) {
		$current = isset( $args['value'] ) ? $args['value'] : array();
		if ( ! is_array( $current ) ) $current = array();
		?>
		<tr class="<?php echo esc_attr( isset( $args['heading_group'] ) ? $args['heading_group'] : '' ); ?> tr_option checkbox_tr">
			<?php self::description_cell( $args ); ?>
			<td class="td_value">
				<?php foreach ( $args['values'] as $key => $label ) : ?>
					<div>
						<span>
							<input <?php checked( isset( $current[ $key ] ) || in_array( $key, $current ) ); ?>
							       type="checkbox"
							       name="<?php echo esc_attr( $args['name'] . '[' . $key . ']' ); ?>"
							       id="<?php echo esc_attr( $args['name'] . $key . '_id' ); ?>"
							       value="<?php echo esc_attr( $key ); ?>">
							<label for="<?php echo esc_attr( $args['name'] . $key . '_id' ); ?>">
								<?php echo esc_html( $label ); ?>
							</label>
						</span>
					</div>
				<?php endforeach; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Padding/margin four-sided input.
	 */
	public static function padding_margin_input( $args ) {
		$sides = array( 'top', 'right', 'bottom', 'left' );
		?>
		<tr class="<?php echo esc_attr( isset( $args['heading_group'] ) ? $args['heading_group'] : '' ); ?> tr_option tr_pading_margin">
			<?php self::description_cell( $args ); ?>
			<td class="td_value">
				<?php foreach ( $sides as $side ) :
					if ( ! isset( $args['values'][ $side ] ) ) continue;
					$val = isset( $args['value'][ $side ] ) ? $args['value'][ $side ] : '0';
				?>
					<span>
						<input type="text"
						       name="<?php echo esc_attr( $args['name'] . '[' . $side . ']' ); ?>"
						       id="<?php echo esc_attr( $args['name'] . '_' . $side . '_id' ); ?>"
						       value="<?php echo esc_attr( $val ); ?>">
						<label for="<?php echo esc_attr( $args['name'] . '_' . $side . '_id' ); ?>">
							<?php echo esc_html( ucfirst( $side ) . '(px)' ); ?>
						</label>
					</span>
				<?php endforeach; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Select with icon/font preview.
	 */
	public static function simple_select_pro_font_size( $args ) {
		$current = isset( $args['value'] ) ? $args['value'] : '';
		?>
		<tr class="<?php echo esc_attr( isset( $args['heading_group'] ) ? $args['heading_group'] : '' ); ?> tr_option">
			<?php self::description_cell( $args ); ?>
			<td>
				<select style="font-family: 'Material Icons','FontAwesome',Arial;"
				        id="<?php echo esc_attr( $args['name'] ); ?>"
				        name="<?php echo esc_attr( $args['name'] ); ?>">
					<?php foreach ( $args['values'] as $key => $value ) : ?>
						<option class="<?php echo esc_attr( $value[1] ); ?>"
						        value="<?php echo esc_attr( $key ); ?>"
						        <?php selected( $key, $current ); ?>>
							<?php echo esc_html( $value[0] ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<?php
	}

	/**
	 * Range slider input.
	 */
	public static function range_input( $args ) {
		$value = isset( $args['value'] ) ? $args['value'] : '';
		$min   = isset( $args['min_value'] ) ? $args['min_value'] : '';
		$max   = isset( $args['max_value'] ) ? $args['max_value'] : '';
		$small = isset( $args['small_text'] ) ? $args['small_text'] : '';
		?>
		<tr class="<?php echo esc_attr( isset( $args['heading_group'] ) ? $args['heading_group'] : '' ); ?> tr_option">
			<?php self::description_cell( $args ); ?>
			<td class="range_option_td">
				<input oninput="document.getElementById('<?php echo esc_js( $args['name'] ); ?>_conect').innerHTML=this.value"
				       type="range"
				       id="<?php echo esc_attr( $args['name'] ); ?>"
				       name="<?php echo esc_attr( $args['name'] ); ?>"
				       value="<?php echo esc_attr( $value ); ?>"
				       <?php echo $min !== '' ? 'min="' . esc_attr( $min ) . '"' : ''; ?>
				       <?php echo $max !== '' ? 'max="' . esc_attr( $max ) . '"' : ''; ?>>
				<output id="<?php echo esc_attr( $args['name'] ); ?>_conect"><?php echo esc_html( $value ); ?></output>
				<?php if ( $small ) : ?>
					<small><?php echo esc_html( $small ); ?></small>
				<?php endif; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Time input (HH:MM).
	 */
	public static function time_input( $args ) {
		$value = isset( $args['value'] ) ? $args['value'] : '';
		$min   = isset( $args['min_value'] ) ? $args['min_value'] : '';
		$max   = isset( $args['max_value'] ) ? $args['max_value'] : '';
		$small = isset( $args['small_text'] ) ? $args['small_text'] : '';
		?>
		<tr class="<?php echo esc_attr( isset( $args['heading_group'] ) ? $args['heading_group'] : '' ); ?> tr_option">
			<?php self::description_cell( $args ); ?>
			<td class="range_option_td">
				<input type="time"
				       id="<?php echo esc_attr( $args['name'] ); ?>"
				       name="<?php echo esc_attr( $args['name'] ); ?>"
				       value="<?php echo esc_attr( $value ); ?>"
				       <?php echo $min !== '' ? 'min="' . esc_attr( $min ) . '"' : ''; ?>
				       <?php echo $max !== '' ? 'max="' . esc_attr( $max ) . '"' : ''; ?>>
				<?php if ( $small ) : ?>
					<small><?php echo esc_html( $small ); ?></small>
				<?php endif; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Upload input with media library button.
	 */
	public static function upload_input( $args ) {
		$value = isset( $args['value'] ) ? $args['value'] : '';
		?>
		<tr class="<?php echo esc_attr( isset( $args['heading_group'] ) ? $args['heading_group'] : '' ); ?> tr_option">
			<?php self::description_cell( $args ); ?>
			<td class="upload_option_td">
				<input type="text" class="upload"
				       id="<?php echo esc_attr( $args['name'] ); ?>"
				       name="<?php echo esc_attr( $args['name'] ); ?>"
				       value="<?php echo esc_attr( $value ); ?>">
				<input class="upload-button button" type="button" value="Upload">
				<img src="<?php echo esc_url( $value ); ?>" class="cont_button_uploaded_img">
			</td>
		</tr>
		<?php
	}

	/**
	 * Date-time picker input.
	 */
	public static function calendar_input( $args ) {
		$value = isset( $args['value'] ) ? $args['value'] : '';
		$type  = isset( $args['type'] ) ? $args['type'] : 'text';
		?>
		<tr class="<?php echo esc_attr( isset( $args['heading_group'] ) ? $args['heading_group'] : '' ); ?> tr_option">
			<?php self::description_cell( $args ); ?>
			<td>
				<input class="wpda_datepicker_timer"
				       type="<?php echo esc_attr( $type ); ?>"
				       value="<?php echo esc_attr( $value ); ?>"
				       name="<?php echo esc_attr( $args['name'] ); ?>">
			</td>
		</tr>
		<?php
	}

	/**
	 * WordPress TinyMCE editor.
	 */
	public static function tinmce( $args ) {
		$value = isset( $args['value'] ) ? $args['value'] : '';
		?>
		<tr class="<?php echo esc_attr( isset( $args['heading_group'] ) ? $args['heading_group'] : '' ); ?> tr_option">
			<td colspan="2" style="padding: 4px;">
				<?php wp_editor( $value, $args['name'], array() ); ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Sortable ordering list.
	 */
	public static function oredering( $args ) {
		$ordering_info = json_decode( stripslashes( $args['value'] ), true );
		if ( ! is_array( $ordering_info ) ) $ordering_info = array();
		$elements = $args['values'];
		?>
		<tr class="<?php echo esc_attr( isset( $args['heading_group'] ) ? $args['heading_group'] : '' ); ?> tr_option">
			<?php self::description_cell( $args ); ?>
			<td style="padding: 4px;">
				<ul class="wpdevart_sortable" id="<?php echo esc_attr( $args['name'] ); ?>_ul">
					<?php foreach ( $ordering_info as $key => $value ) :
						$active_class = $value[0] ? ' control_active ' : ' control_deactive ';
					?>
						<li date-value="<?php echo esc_attr( $key ); ?>"
						    class="ui-state-default<?php echo esc_attr( $active_class ); ?>">
							<?php echo esc_html( isset( $elements[ $key ] ) ? $elements[ $key ] : $key ); ?>
							<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
						</li>
					<?php endforeach; ?>
				</ul>
				<input type="hidden"
				       name="<?php echo esc_attr( $args['name'] ); ?>"
				       id="<?php echo esc_attr( $args['name'] ); ?>"
				       value='<?php echo esc_attr( stripslashes( $args['value'] ) ); ?>'>
			</td>
		</tr>
		<?php
	}

	// ─── Static helpers ─────────────────────────────────────────

	/**
	 * Render a field by calling the appropriate method based on function_name.
	 */
	public static function render( $args ) {
		$method = $args['function_name'];
		if ( method_exists( __CLASS__, $method ) ) {
			self::$method( $args );
		}
	}

	/**
	 * Build the multi-select options for pages/posts/categories/taxonomies/etc.
	 */
	public static function build_page_options( $selected = array() ) {
		if ( ! is_array( $selected ) ) $selected = array();

		$groups = array(
			'pages'            => 'Pages',
			'posts'            => 'Posts',
			'categories'       => 'Categories',
			'custom_post_type' => 'Custom Post Types',
			'taxonomy'         => 'Taxonomies',
			'other'            => 'Other',
			'device'           => 'Device',
			'user'             => 'User',
		);

		$options = array(
			'pages'            => self::get_pages_list(),
			'posts'            => self::get_posts_list(),
			'categories'       => self::get_categories_list(),
			'custom_post_type' => self::get_cpt_list(),
			'taxonomy'         => self::get_taxonomy_list(),
			'other'            => self::get_other_list(),
			'device'           => array( 'mobile' => 'Mobile', 'desktop' => 'Desktop' ),
			'user'             => array( 'logged_in' => 'Logged in users', 'logged_out' => 'Logged out users' ),
		);

		$html = '';
		foreach ( $groups as $group_key => $group_label ) {
			$html .= '<optgroup label="' . esc_attr( $group_label ) . '">';
			if ( isset( $options[ $group_key ] ) ) {
				foreach ( $options[ $group_key ] as $opt_key => $opt_label ) {
					$sel  = in_array( $opt_key, $selected ) ? ' selected="selected"' : '';
					$html .= '<option label="' . esc_attr( $group_label ) . '" value="' . esc_attr( $opt_key ) . '"' . $sel . '>'
					        . esc_html( $opt_label ) . '</option>';
				}
			}
			$html .= '</optgroup>';
		}
		return $html;
	}

	private static function get_pages_list() {
		$list  = array();
		$pages = get_pages( array( 'sort_order' => 'ASC', 'sort_column' => 'post_title', 'post_status' => 'publish' ) );
		foreach ( $pages as $p ) {
			$list[ 'page_' . $p->ID ] = $p->post_title;
		}
		return $list;
	}

	private static function get_posts_list() {
		$list  = array();
		$posts = get_posts( array( 'orderby' => 'title', 'order' => 'ASC', 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => -1 ) );
		foreach ( $posts as $p ) {
			$list[ 'post_' . $p->ID ] = $p->post_title;
		}
		return $list;
	}

	private static function get_categories_list() {
		$list = array();
		$cats = get_categories( array( 'hide_empty' => false ) );
		foreach ( $cats as $c ) {
			$list[ 'category_' . $c->cat_ID ] = $c->cat_name;
		}
		return $list;
	}

	private static function get_cpt_list() {
		$list  = array();
		$types = get_post_types( array( 'public' => true ), 'objects' );
		foreach ( $types as $type ) {
			$list[ 'custom_post_type_' . $type->name ] = $type->label;
		}
		return $list;
	}

	private static function get_taxonomy_list() {
		$list  = array();
		$taxes = get_taxonomies( array( 'public' => true ), 'objects' );
		foreach ( $taxes as $tax ) {
			$list[ 'taxonomy_' . $tax->name ] = $tax->label;
		}
		return $list;
	}

	private static function get_other_list() {
		return array(
			'front_page'     => 'Front Page',
			'blog_page'      => 'Blog Page',
			'single_post'    => 'Single Posts',
			'sticky_post'    => 'Sticky Posts',
			'date_archive'   => 'Date Archive',
			'author_archive' => 'Author Archive',
			'search_page'    => 'Search Page',
			'404_page'       => '404 Page',
		);
	}

	/**
	 * Get font family choices for selects.
	 */
	public static function font_choices() {
		return array(
			'Arial,Helvetica Neue,Helvetica,sans-serif'                                      => 'Arial *',
			'Arial Black,Arial Bold,Arial,sans-serif'                                        => 'Arial Black *',
			'Arial Narrow,Arial,Helvetica Neue,Helvetica,sans-serif'                         => 'Arial Narrow *',
			'Courier,Verdana,sans-serif'                                                     => 'Courier *',
			'Georgia,Times New Roman,Times,serif'                                            => 'Georgia *',
			'Times New Roman,Times,Georgia,serif'                                            => 'Times New Roman *',
			'Trebuchet MS,Lucida Grande,Lucida Sans Unicode,Lucida Sans,Arial,sans-serif'    => 'Trebuchet MS *',
			'Verdana,sans-serif'                                                             => 'Verdana *',
			'American Typewriter,Georgia,serif'                                              => 'American Typewriter',
			'Andale Mono,Consolas,Monaco,Courier,Courier New,Verdana,sans-serif'             => 'Andale Mono',
			'Baskerville,Times New Roman,Times,serif'                                        => 'Baskerville',
			'Bookman Old Style,Georgia,Times New Roman,Times,serif'                          => 'Bookman Old Style',
			'Calibri,Helvetica Neue,Helvetica,Arial,Verdana,sans-serif'                     => 'Calibri',
			'Cambria,Georgia,Times New Roman,Times,serif'                                    => 'Cambria',
			'Candara,Verdana,sans-serif'                                                     => 'Candara',
			'Century Gothic,Apple Gothic,Verdana,sans-serif'                                 => 'Century Gothic',
			'Century Schoolbook,Georgia,Times New Roman,Times,serif'                         => 'Century Schoolbook',
			'Consolas,Andale Mono,Monaco,Courier,Courier New,Verdana,sans-serif'             => 'Consolas',
			'Constantia,Georgia,Times New Roman,Times,serif'                                 => 'Constantia',
			'Corbel,Lucida Grande,Lucida Sans Unicode,Arial,sans-serif'                      => 'Corbel',
			'Franklin Gothic Medium,Arial,sans-serif'                                        => 'Franklin Gothic Medium',
			'Garamond,Hoefler Text,Times New Roman,Times,serif'                              => 'Garamond',
			'Gill Sans MT,Gill Sans,Calibri,Trebuchet MS,sans-serif'                         => 'Gill Sans MT',
			'Helvetica Neue,Helvetica,Arial,sans-serif'                                      => 'Helvetica Neue',
			'Hoefler Text,Garamond,Times New Roman,Times,sans-serif'                         => 'Hoefler Text',
			'Lucida Bright,Cambria,Georgia,Times New Roman,Times,serif'                      => 'Lucida Bright',
			'Lucida Grande,Lucida Sans,Lucida Sans Unicode,sans-serif'                       => 'Lucida Grande',
			'Palatino Linotype,Palatino,Georgia,Times New Roman,Times,serif'                 => 'Palatino Linotype',
			'Tahoma,Geneva,Verdana,sans-serif'                                               => 'Tahoma',
			'Rockwell, Arial Black, Arial Bold, Arial, sans-serif'                           => 'Rockwell',
			'Segoe UI'                                                                       => 'Segoe UI',
		);
	}

	/**
	 * List of CSS animation names.
	 */
	public static function animation_list() {
		return array(
			'bounce', 'flash', 'pulse', 'rubberBand', 'shake', 'swing', 'tada', 'wobble',
			'bounceIn', 'bounceInDown', 'bounceInLeft', 'bounceInRight', 'bounceInUp',
			'fadeIn', 'fadeInDown', 'fadeInDownBig', 'fadeInLeft', 'fadeInLeftBig',
			'fadeInRight', 'fadeInRightBig', 'fadeInUp', 'fadeInUpBig',
			'flip', 'flipInX', 'flipInY', 'lightSpeedIn',
			'rotateIn', 'rotateInDownLeft', 'rotateInDownRight', 'rotateInUpLeft', 'rotateInUpRight',
			'rollIn', 'zoomIn', 'zoomInDown', 'zoomInLeft', 'zoomInRight', 'zoomInUp',
		);
	}

	/**
	 * Get a random animation name.
	 */
	public static function random_animation() {
		$list = self::animation_list();
		return $list[ array_rand( $list ) ];
	}

	/**
	 * Validate and sanitize a hex color. Returns '' if invalid (fallback to default).
	 */
	public static function sanitize_hex_color( $color ) {
		if ( '' === $color || ! is_string( $color ) ) return '';
		if ( preg_match( '/^#([A-Fa-f0-9]{3}){1,2}$/', $color ) ) return $color;
		return '';
	}

	/**
	 * Sanitize a single field value based on its function_name (field type).
	 * Used by all controllers on save to apply type-appropriate sanitization.
	 */
	public static function sanitize_field_value( $field, $raw ) {
		$fn = isset( $field['function_name'] ) ? $field['function_name'] : '';

		if ( is_array( $raw ) ) {
			return array_map( 'sanitize_text_field', $raw );
		}

		switch ( $fn ) {
			case 'tinmce':
				return wp_kses_post( stripslashes( $raw ) );
			case 'color_input':
				$v = self::sanitize_hex_color( trim( stripslashes( $raw ) ) );
				return $v !== '' ? $v : ( isset( $field['default_value'] ) ? $field['default_value'] : '' );
			case 'range_input':
				return (string) intval( $raw );
			default:
				return sanitize_text_field( stripslashes( $raw ) );
		}
	}

	/**
	 * Convert hex color to rgba.
	 */
	public static function hex2rgba( $color, $opacity = false ) {
		$default = 'rgb(0,0,0)';
		if ( empty( $color ) ) return $default;

		if ( $color[0] === '#' ) $color = substr( $color, 1 );

		if ( strlen( $color ) === 6 ) {
			$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
		} elseif ( strlen( $color ) === 3 ) {
			$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
		} else {
			return $default;
		}

		$rgb     = array_map( 'hexdec', $hex );
		$opacity = min( $opacity, 1 );

		return 'rgba(' . implode( ',', $rgb ) . ',' . $opacity . ')';
	}

	/**
	 * Darken a hex color by a percentage.
	 */
	public static function darken_color( $color, $percent ) {
		$color = ltrim( $color, '#' );
		if ( strlen( $color ) !== 6 ) return '#' . $color;

		$r = max( 0, (int) ( hexdec( substr( $color, 0, 2 ) ) * ( 1 - $percent / 100 ) ) );
		$g = max( 0, (int) ( hexdec( substr( $color, 2, 2 ) ) * ( 1 - $percent / 100 ) ) );
		$b = max( 0, (int) ( hexdec( substr( $color, 4, 2 ) ) * ( 1 - $percent / 100 ) ) );

		return '#' . str_pad( dechex( $r ), 2, '0', STR_PAD_LEFT )
		           . str_pad( dechex( $g ), 2, '0', STR_PAD_LEFT )
		           . str_pad( dechex( $b ), 2, '0', STR_PAD_LEFT );
	}
}
