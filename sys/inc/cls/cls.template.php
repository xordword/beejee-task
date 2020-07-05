<?PHP
class Template {
	private $m_vars, $m_ld, $m_rd, $m_tpl_path, $m_ext;

	function __construct($path) {
		$this->m_vars     = array();
		$this->m_ld       = '{[';
		$this->m_rd       = ']}';
		$this->m_tpl_path = $path;
		$this->m_ext      = '.tpl';
	}

	public function assign($arg1, $arg2 = false) {
		if (is_array($arg1)) foreach ($arg1 as $k=>$v) $this->m_vars[$k] = $v;
		else $this->m_vars[$arg1] = $arg2;
	}

	public function parse($tpl_name) {
		$fname = $this->m_tpl_path.'/'.$tpl_name.$this->m_ext;
		$buf = file_get_contents($fname);
		$pbuf=''; $o=0;
		while (($p = strpos($buf, $this->m_ld,$o))!==false) {
			$pbuf .= substr($buf, $o, $p-$o);
			$block = substr($buf, $p=$p+strlen($this->m_ld), ($t=strpos($buf,$this->m_rd,$p))-$p);
			if ($t===false) break;
			$o = $t+strlen($this->m_rd);
			$pbuf .= $this->m_vars[$block];
		}
		$pbuf .= substr($buf, $o);

		return $pbuf;
	}
}
?>