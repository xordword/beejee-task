<?PHP

class Form {
	private $_fields;
	private $_glob;

	public $fields;
	public $chk;
	public $all_err;
	public $err;

	public function __construct(array $fields, $all_err = false, $glob = '_POST') {
		$this->_fields = $fields;
		$this->_glob   = $glob;
		$this->fields  = array();
		$this->chk     = array();
		$this->all_err = $all_err;
		$this->err     = array();
		foreach ($this->_fields as $k=>&$v) {
			if (is_array($v)) {
				$this->fields[$k] = isset($v['val'])?$v['val']:null;
				if (!isset($v['file'])) $v['file'] = false;
				if (!isset($v['empty'])) $v['empty'] = true;
				if (!isset($v['req'])) $v['req'] = true;
			} else {
				$this->fields[$k] = $v;
				$v = array('file'=>false, 'empty'=>true, 'req'=>true);
			}	
		}
	}

	public function val($data) {
		if ($this->fields) {
			foreach ($this->fields as $k=>&$v) if (!isset($v) && isset($data[$k])) $v = $data[$k];
			return true;
		}
		return false;
	}

	public function get() {
		$flag = true;
		foreach ($this->_fields as $k=>$v) {
			if ($v['file']) {
				if (isset($_FILES[$k])) {
					$f = &$_FILES[$k];
					if (is_array($f['size'])) {
						$this->fields[$k] = array();
						for ($i=0, $sz=sizeof($f['size']); $i<$sz; $i++) {
							if ($f['size'][$i]) {
								if (preg_match('/\.([^.]+)$/', $f['name'][$i], $pock)) $ext = $pock[1];
								else $ext = '';
								$this->fields[$k][] = array(
									'size'     => $f['size'][$i], 
									'name'     => $f['name'][$i],
									'tmp_name' => $f['tmp_name'][$i],
									'type'     => $f['type'][$i],
									'ext'      => $ext
								);
							}
						}
					} elseif ($f['size']) {
						if (preg_match('/\.([^.]+)$/', $f['name'], $pock)) $ext = $pock[1];
						else $ext = '';
						$this->fields[$k] = array(array(
							'size'     => $f['size'], 
							'name'     => $f['name'],
							'tmp_name' => $f['tmp_name'],
							'type'     => $f['type'],
							'ext'      => $ext
						));
					} else $this->fields[$k] = null;
				} elseif (!$v['req']) $this->fields[$k] = null;
				else {
					$flag = false;
					break;
				}
			} else {
				if (isset($GLOBALS[$this->_glob][$k])) $this->fields[$k] = $GLOBALS[$this->_glob][$k];
				elseif (!$v['req']) $this->fields[$k] = null;
				else {
					$flag = false;
					break;
				}
			}
		}
		$this->trim($this->fields);
		return $flag;
	}

	public function check() {
		foreach ($this->_fields as $k=>$v) {
			$msg = array();
			$title = isset($v['title'])?$v['title']:$k;
			$val = $this->fields[$k];

			if ($v['file']) {
				if (!$v['empty'] && !$val) $msg[] = isset($v['empty_msg'])?$v['empty_msg']:('Пожалуйста укажите '.$title.'.');

				if (($this->all_err || !$msg) && ($val || !$v['empty'])) {
					if (isset($v['ext']) && $v['ext']) {
						for ($i=0, $sz=sizeof($val); $i<$sz; $i++) {
							if (is_array($v['ext'])) {
								if (!in_array($val[$i]['ext'], $v['ext'])) {
									$msg[] = isset($v['ext_msg'])?$v['ext_msg']:('Только расширение \''.implode('\', \'', $v['ext']).'\' '.(sizeof($v['ext'])>1?'s':'').' допустимо для '.$title.'.');
									break;
								}
							} else {
								if ($val[$i]['ext']!=$v['ext']) {
									$msg[] = isset($v['ext_msg'])?$v['ext_msg']:('Только расширение \''.$v['ext'].'\' допустимо для '.$title.'.');
									break;
								}
							}	
						}
					}
				}
			} else {
				$sl = strlen(is_array($val)?serialize($val):$val);

				if (!$v['empty'] && !$sl) $msg[] = isset($v['empty_msg'])?$v['empty_msg']:('Пожалуйста укажите '.$title.'.');

				if (($this->all_err || !$msg) && ($sl || !$v['empty'])) {
					if (isset($v['len']) && is_array($v['len'])) {
						$min = $v['len'][0];
						$max = isset($v['len'][1])?$v['len'][1]:null;
						$str = 'Пожалуйста укажите '.$title;
						if (isset($min) && !isset($max) && $sl<$min)
							$msg[] = isset($v['len_msg'][0])?$v['len_msg'][0]:($str.' с длиной большей или равной '.$min.' символам.');
						elseif (isset($max) && !isset($min) && $sl>$max)
							$msg[] = isset($v['len_msg'][1])?$v['len_msg'][1]:($str.' длиной меньшей или равной '.$max.' символам.');
						elseif (isset($min, $max) && ($sl<$min || $sl>$max))
							$msg[] = isset($v['len_msg'][2])?$v['len_msg'][2]:($str.' длиной от '.$min.' до '.$max.' символов.');
					}

					if (($this->all_err || !$msg) && isset($v['nval']) && is_array($v['nval'])) {
						$min = $v['nval'][0];
						$max = isset($v['nval'][1])?$v['nval'][1]:null;
						$str = 'Пожалуйста укажите '.$title;
						if (isset($min) && !isset($max) && $val<$min)
							$msg[] = isset($v['nval_msg'][0])?$v['nval_msg'][0]:($str.' больше или равно '.$min.'.');
						elseif (isset($max) && !isset($min) && $val>$max)
							$msg[] = isset($v['nval_msg'][1])?$v['nval_msg'][1]:($str.' меньше или равно '.$max.'.');
						elseif (isset($min, $max) && ($val<$min || $val>$max))
							$msg[] = isset($v['nval_msg'][2])?$v['nval_msg'][2]:($str.' от '.$min.' до '.$max.'.');
					}

					if (
						($this->all_err || !$msg) && isset($v['format']) && method_exists($this, 'format_'.$v['format']) &&
						!call_user_func(array($this, 'format_'.$v['format']), $val)
					) $msg[] = isset($v['format_msg'])?$v['format_msg']:('Пожалуйста укажите корректно '.$title.'.');

					if (($this->all_err || !$msg) && (
						(isset($v['ink']) && !isset($v['ink'][$val])) || (isset($v['in']) && !in_array($val, $v['in']))
					)) {
						$msg[] = isset($v['in_msg'])?$v['in_msg']:('Пожалуйста выберите '.$title.' из списка.');
					}

					if (($this->all_err || !$msg) && isset($v['ina'])) {
						$flag = false;
						for ($i=0, $sz=sizeof($v['ina'][0]); $i<$sz; $i++)
							if ($val==$v['ina'][0][$i][$v['ina'][1]]) {
								$flag = true;
								break;
							}
						if (!$flag) $msg[] = isset($v['in_msg'])?$v['in_msg']:('Пожалуйста выберите '.$title.' из списка.');
						else $this->chk[$k] = array('row'=>$v['ina'][0][$i]);
					}

					if (($this->all_err || !$msg) && isset($v['dbd']) && is_array($v['dbd']) && $v['dbd']) {
						for ($i=0, $sz=sizeof($v['dbd']); $i<$sz; $i++) {
							$dbd = &$v['dbd'][$i];
							if (
								isset($dbd['db'], $dbd['table']) && 
								($row = $dbd['db']->row('`'.$dbd['table'].'`', $val, 's', '`'.(isset($dbd['field'])?$dbd['field']:$k).'`')) &&
								(!isset($dbd['id']) || (is_array($dbd['id']) && $row[$dbd['id'][0]]!=$dbd['id'][1]) || $row['id']!=$dbd['id'])
							) {
								$msg[] = isset($v['dbd_msg'])?$v['dbd_msg']:('That '.$title.' already in use.');
								$this->chk[$k][$i] = array('row'=>$row);
								break;
							} else $this->chk[$k][$i] = array('row'=>null);
						}
					}
				}
			}	
			if ($msg) $this->err[$k] = $msg;
		}

		return !$this->err;
	}

	public function trim(&$data) {
		$in = array(&$data);
		while (list($k, $v) = each($in)) {
			foreach ($v as $k1 => $v1) {
				if (!@$this->_fields[$k1]['file']) {
					if (is_array($v1)) $in[] = &$in[$k][$k1];
					elseif (isset($v1)) $in[$k][$k1] = trim($v1);
				}	
			}
		}
		unset($in);
	}

	public function format_integer($str) {
		return preg_match('/^\d+$/i', $str);
	}
	public function format_username($str) {
		return preg_match('/^[\da-z]{2,20}$/i', $str);
	}
	public function format_pass($str) {
		return preg_match('/^.{6,20}$/i', $str);
	}
	public function format_name($str) {
		return preg_match('/^[\d\w(),.-]{2,40}$/i', $str);
	}

	public function format_email($str) {
		return preg_match('/^[\w\d-\.]+@([\w\d-]+(\.[\w\-]+)+)$/i', $str);
	}

	public function format_addr($str) {
		return preg_match('/^[\w\d.,#-]{1,40}(\s+[\w\d.,#-]{1,40}){0,5}$/i', $str);
	}

	public function format_weight($str) {
		return preg_match('/^\d+(\.\d)?$/', $str);
	}

	public function format_price($str) {
		return preg_match('/^\d+(\.\d{1,2})?$/', $str);
	}

	public function format_city($str) {
		return preg_match('/^[\w()-,.\/]{2,40}(\s+[\w()-,.\/]{2,40}){0,5}$/i', $str);
	}

	public function __destruct() {}
}



?>