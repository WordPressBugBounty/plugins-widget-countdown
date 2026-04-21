<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Timer Repository — ALL timer database operations in one place.
 *
 * Every file that needs timer data (admin, frontend, widget, integrations)
 * goes through this class. No more scattered $wpdb calls.
 */
class Wpda_Countdown_Timer_Repository {

	private $table;

	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'wpda_contdown_timer';
	}

	public function get_table_name() {
		return $this->table;
	}

	/**
	 * Get a single timer by ID. Returns null if the table doesn't exist yet.
	 */
	public function find( $id ) {
		if ( ! $this->table_exists() ) return null;
		global $wpdb;
		return $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$this->table} WHERE id = %d", $id )
		);
	}

	/**
	 * Get decoded timer data merged with defaults.
	 *
	 * @return array|null  Merged settings array, or null if not found.
	 */
	public function find_data( $id ) {
		$row = $this->find( $id );
		if ( ! $row ) return null;
		$data = json_decode( $row->option_value, true );
		return is_array( $data ) ? $data : array();
	}

	/**
	 * Get all timers (raw rows). Returns [] if the table doesn't exist yet.
	 */
	public function all() {
		if ( ! $this->table_exists() ) return array();
		global $wpdb;
		$rows = $wpdb->get_results( "SELECT id, name, option_value FROM {$this->table} ORDER BY id ASC" );
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Get id => name map for dropdowns.
	 *
	 * @return array  [ id => name, ... ]
	 */
	public function all_names() {
		$list = array();
		foreach ( $this->all() as $row ) {
			$list[ $row->id ] = $row->name;
		}
		return $list;
	}

	/**
	 * Insert a new timer.
	 *
	 * @param string $name  Timer display name.
	 * @param array  $data  Settings array (will be JSON-encoded).
	 * @return int  Inserted row ID, or 0 on failure.
	 */
	public function insert( $name, array $data ) {
		global $wpdb;
		$ok = $wpdb->insert(
			$this->table,
			array(
				'name'         => $name ?: 'Unnamed',
				'option_value' => wp_json_encode( $data ),
			),
			array( '%s', '%s' )
		);
		return $ok ? $wpdb->insert_id : 0;
	}

	/**
	 * Update an existing timer.
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
	 * Delete a timer by ID.
	 */
	public function delete( $id ) {
		global $wpdb;
		return (bool) $wpdb->delete( $this->table, array( 'id' => $id ), array( '%d' ) );
	}

	/**
	 * Count total timers.
	 */
	public function count() {
		global $wpdb;
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table}" );
	}

	/**
	 * Get the highest ID (used after insert to redirect to edit).
	 */
	public function max_id() {
		global $wpdb;
		return (int) $wpdb->get_var( "SELECT MAX(id) FROM {$this->table}" );
	}

	/**
	 * Check if the table exists in the database.
	 */
	public function table_exists() {
		global $wpdb;
		return $wpdb->get_var( "SHOW TABLES LIKE '{$this->table}'" ) === $this->table;
	}
}
