<?php
/**
 * 数据操作基础类 
 * 
 * loytor@sina.com  2015-08-11
 */
class MY_Model extends CI_Model{

	protected $table;

	public function __construct(){
		parent::__construct();
	}

	public function add($data=array(), $isRetId = false){
		if (empty($data)) {
			$data = $this;
		}
		$ret = $this->db->insert($this->table, $data);
		return  $isRetId ? $this->db->insert_id() : $ret;
	}

	public function update($where, $data = array()){
		$this->_db_where($where);
		if (empty($data)) {
			$data = $this;
		}
		return $this->db->update($this->table, $data);
	}

	public function delete($where){
		$this->_db_where($where);
		return $this->db->delete($this->table);
	}

	public function get_row($where_set, $fields = '', $order_by = 'id', $order_direction = 'desc'){
		$this->_db_where($where_set);
		if ($fields) {
			$this->db->select($fields);
		}
		$this->db->order_by($order_by, $order_direction);
		return $this->db->get($this->table, 1)->row_array();
	}

	public function get_one($where_set, $field, $order_by = 'id', $order_direction = 'desc'){
		$row = $this->get_row($where_set, $field, $order_by, $order_direction);
		if ($row AND isset($row[$field])) {
			return $row[$field];
		}
		return NULL;
	}

	public function total_rows($where_set){
		$this->_db_where($where_set);
		return $this->db->count_all_results($this->table);
	}

	public function get_all($where_set, $per_page = '', $page = '', $order_by = 'id', $order_direction = 'desc', $fields = ''){
		$this->_db_where($where_set);
		if ($fields) {
			$this->db->select($fields);
		}
		$this->db->order_by($order_by, $order_direction);
		if($per_page != '' && $page != '') {
			$this->db->limit($per_page, ($page <= 1) ? 0 : (($page - 1) * $per_page));
		}
		return $this->db->get($this->table)->result_array();
	}

	protected function _db_where($where_set){
		if (is_string($where_set) && preg_match('/^[a-z0-9]+$/i', $where_set)) {
			$this->db->where('id', $where_set);
		}
		else if (is_array($where_set) && ! empty($where_set)) {
			foreach ($where_set as $field => $value) {
				if (is_string($value) && substr($value, 0, 5) == 'like:') {
					$this->db->like($field, substr($value, 5));
				}
				else if(substr($field, 0, 3) == 'in:'){
					$this->db->where_in(substr($field,3), $value);
				}
				else if(substr($field, 0, 6) == 'notin:'){
					$this->db->where_not_in(substr($field,6), $value);
				}
				else {
					$this->db->where($field, $value);
				}
			}
		}
	}
}