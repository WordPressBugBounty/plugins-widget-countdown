<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Theme Repository — ALL theme database operations in one place.
 */
class Wpda_Countdown_Theme_Repository {

	private $table;

	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'wpda_contdown_theme';
	}

	public function get_table_name() {
		return $this->table;
	}

	/**
	 * Get a single theme by ID. Returns null if the table doesn't exist yet.
	 */
	public function find( $id ) {
		if ( ! $this->table_exists() ) return null;
		global $wpdb;
		return $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$this->table} WHERE id = %d", $id )
		);
	}

	public function find_data( $id ) {
		$row = $this->find( $id );
		if ( ! $row ) return null;
		$data = json_decode( $row->option_value, true );
		return is_array( $data ) ? $data : array();
	}

	/**
	 * Get the default theme row. Returns null if the table doesn't exist yet.
	 */
	public function find_default() {
		if ( ! $this->table_exists() ) return null;
		global $wpdb;
		return $wpdb->get_row( "SELECT * FROM {$this->table} WHERE `default` = 1" );
	}

	public function find_or_default( $id ) {
		$theme = $this->find( $id );
		if ( ! $theme ) {
			$theme = $this->find_default();
		}
		return $theme;
	}

	/**
	 * Get all themes (raw rows). Returns [] if the table doesn't exist yet.
	 */
	public function all() {
		if ( ! $this->table_exists() ) return array();
		global $wpdb;
		$rows = $wpdb->get_results( "SELECT id, name, option_value, `default` FROM {$this->table} ORDER BY id ASC" );
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Get id => "Name (Type)" map for dropdowns.
	 *
	 * The type suffix helps users identify what design each theme uses
	 * (Standard / Vertical / Circle / Flip) without having to open each one.
	 * Stored names in the DB are unchanged — this is display-only.
	 */
	public function all_names() {
		static $type_labels = array(
			'standart' => 'Standard',
			'vertical' => 'Vertical',
			'circle'   => 'Circle',
			'flip'     => 'Flip',
		);
		$list = array();
		foreach ( $this->all() as $row ) {
			$data  = json_decode( $row->option_value, true );
			$type  = ( is_array( $data ) && isset( $data['countdown_type'] ) ) ? $data['countdown_type'] : 'standart';
			$label = isset( $type_labels[ $type ] ) ? $type_labels[ $type ] : 'Standard';
			$list[ $row->id ] = $row->name . ' (' . $label . ')';
		}
		return $list;
	}

	/**
	 * Insert a new theme.
	 *
	 * @param string $name     Theme display name.
	 * @param array  $data     Settings array (will be JSON-encoded).
	 * @param bool   $default  Whether this is the default theme.
	 * @return int  Inserted row ID, or 0 on failure.
	 */
	public function insert( $name, array $data, $default = false ) {
		global $wpdb;
		$ok = $wpdb->insert(
			$this->table,
			array(
				'name'         => $name ?: 'Unnamed',
				'option_value' => wp_json_encode( $data ),
				'default'      => $default ? 1 : 0,
			),
			array( '%s', '%s', '%d' )
		);
		return $ok ? $wpdb->insert_id : 0;
	}

	/**
	 * Update an existing theme.
	 *
	 * @return int|false  Rows affected, 0 if data unchanged, false on DB error.
	 */
	public function update( $id, $name, array $data ) {
		global $wpdb;
		return $wpdb->update(
			$this->table,
			array(
				'name'         => $name,
				'option_value' => wp_json_encode( $data ),
			),
			array( 'id' => $id ),
			array( '%s', '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Save — insert or update depending on whether $id is provided.
	 *
	 * @return int  The row ID on success, 0 on failure.
	 */
	public function save( $id, $name, array $data ) {
		if ( $id ) {
			return $this->update( $id, $name, $data ) !== false ? (int) $id : 0;
		}
		return $this->insert( $name, $data );
	}

	/**
	 * Delete a theme by ID.
	 */
	public function delete( $id ) {
		global $wpdb;
		return (bool) $wpdb->delete( $this->table, array( 'id' => $id ), array( '%d' ) );
	}

	/**
	 * Set a theme as default (clears previous default).
	 */
	public function set_default( $id ) {
		global $wpdb;
		$wpdb->update( $this->table, array( 'default' => 0 ), array( 'default' => 1 ) );
		return (bool) $wpdb->update( $this->table, array( 'default' => 1 ), array( 'id' => $id ) );
	}

	/**
	 * Check if any default theme exists. Returns its ID or false.
	 */
	public function get_default_id() {
		global $wpdb;
		$id = $wpdb->get_var( "SELECT id FROM {$this->table} WHERE `default` = 1" );
		return $id ? (int) $id : false;
	}

	/**
	 * Count total themes.
	 */
	public function count() {
		global $wpdb;
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table}" );
	}

	/**
	 * Get the highest ID.
	 */
	public function max_id() {
		global $wpdb;
		return (int) $wpdb->get_var( "SELECT MAX(id) FROM {$this->table}" );
	}

	/**
	 * Check if the table exists.
	 */
	public function table_exists() {
		global $wpdb;
		return $wpdb->get_var( "SHOW TABLES LIKE '{$this->table}'" ) === $this->table;
	}
}
