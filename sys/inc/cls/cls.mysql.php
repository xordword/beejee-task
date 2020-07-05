<?php

class MySQL
{
	private $m_link, $m_pq, $m_qc, $m_error, $m_disconnected;

	function __construct()
	{
		$this->m_link         = false;
		$this->m_qc           = 0;
		$this->m_error        = '';
		$this->m_pq           = array();
		$this->m_insert_id    = null;
		$this->m_disconnected = true;
	}
	function __destruct()
	{
		$this->close();
	}
	public function connect($host, $user, $pass, $db_name = null)
	{
		if (!$this->m_link)
		{
			if (!($this->m_link = mysqli_connect($host, $user, $pass, $db_name)))
			{
				$this->m_error = 'can\'t connect to server!';
				return false;
			}
			$this->m_disconnected = false;
		}
		return true;
	}
	public function connected()
	{
		return !$this->m_disconnected;
	}
	public function close()
	{
		if ($this->m_link)
		{
			mysqli_close($this->m_link);
			$this->m_link = false;
			$this->m_disconnected = true;
			return true;
		}
		return false;
	}
	public function q_count()
	{
		return $this->m_qc;
	}
	public function error()
	{
		return $this->m_error || @mysqli_error($this->m_link);
	}
	public function error_msg($sql = null)
	{
		$msg = @mysqli_error($this->m_link);

		return $this->m_error || $msg?'<b>class error:</b> '.$this->m_error.($msg?($this->m_error?' ':'')."<br />\r\n".'<b>MySQL error: '.$msg.'</b>':'').($sql?"<br />\r\n".'<b>request:</b> '.$sql:''):'';
	}
	public function mkq($str, $vars)
	{
		if (!is_array($vars))
		{
			$vars = array($vars);
		}
		//print_r($vars);

		$q  = '';
		$sz = sizeof($vars)-1;
		$i  = $o
		    = $np
		    = 0;
		while (($p=strpos($str, '%', $o)) !== false)
		{
			if ($str[$p+1]=='%')
			{
				$np++;
			}
			if ($i-$np > $sz)
			{
				$this->m_error = 'count of vars and count of tags not match!';
				echo $this->error_msg();
				$this->close();
				exit;
			}
			$q.=substr($str, $o, $p-$o);
			$o=$p+2;
			switch ($str[$p+1])
			{
				case 'i': $q.=intval($vars[$i-$np]);
				break;
				case 'u': $q.=abs(intval($vars[$i-$np]));
				break;
				case 'f': $q.=doubleval($vars[$i-$np]);
				break;
				case 'F': $q.=abs(doubleval($vars[$i-$np]));
				break;
				case 's': $q.='\''.mysqli_real_escape_string($this->m_link, $vars[$i-$np]).'\'';
				break;
				case 'l': $q.=addcslashes(mysqli_real_escape_string($this->m_link, $vars[$i-$np]), '%_');
				break;
				case '%': $q.='%';
				break;
				default: $q.=$vars[$i-$np];
			}
			$i++;
		}
		$q.=substr($str, $o);
		return $q;
	}
	public function query($data, $ub=false)
	{
		if (is_array($data)) //åñëè $data[1]==null - óñëîâèå íå ñðàáîòàåò
		{
			if (isset($data[1]))
			{
				$sql = $this->mkq($data[0], $data[1]);
			}
			else
			{
				$sql = $data[0];
			}
		}
		else
		{
			$sql = $data;
		}
		//echo $sql."<br />";

		$r = $ub?mysqli_real_query($this->m_link, $sql):mysqli_query($this->m_link, $sql);
		$this->m_qc++;
		if ($this->error())
		{
			if (!defined('IS_DAEMON')) echo $this->error_msg($sql);
			$this->m_disconnected = true;
			$this->close();
			exit;
		}
		return $r;
	}
	public function fetch_row($r, $free_result = true)
	{
		$f = mysqli_fetch_row($r);
		if ($free_result)
		{
			mysqli_free_result($r);
		}
		return $f;
	}
	public function fetch_row_all($r, $user_func = null, $user_func_args = null)
	{
		$rows = array();
		for ($i=0, $sz=$this->num_rows($r); $i<$sz; $i++)
		{
			$rows[$i] = mysqli_fetch_row($r);
			if ($user_func)
			{
				$ref = &$rows[$i];
				call_user_func($user_func, array('row'=>$ref, 'args'=>$user_func_args));
			}
		}
		mysqli_free_result($r);
		return $rows;
	}
	public function fetch_assoc($r, $free_result = true)
	{
		$f = mysqli_fetch_assoc($r);
		if ($free_result)
		{
			mysqli_free_result($r);
		}
		return $f;
	}
	public function fetch_assoc_all($r, $user_func = null, $user_func_args = null)
	{
		$rows = array();
		for ($i=0, $sz=$this->num_rows($r); $i<$sz; $i++)
		{
			$rows[$i] = mysqli_fetch_assoc($r);
			if ($user_func)
			{
				$ref = &$rows[$i];
				call_user_func($user_func, array('row'=>$ref, 'args'=>$user_func_args));
			}
		}
		mysqli_free_result($r);
		return $rows;
	}
	public function num_rows($r)
	{
		return ($t=@mysqli_num_rows($r))?$t:0;
	}
	public function insert_id()
	{
		return mysqli_insert_id($this->m_link);
	}
	public function rows($table, $data = array(), $user_func = null, $user_func_args = null)
	{
		$vars = $expr = $having = null;

		if (isset($data['expr']))
		{
			if (is_array($data['expr']))
			{
				$expr = $data['expr'][0];
				$vars = $data['expr'][1];
			}
			else
			{
				$expr = $data['expr'];
			}
		}

		if (isset($data['having']))
		{
			if (is_array($data['having']))
			{
				$having = $data['having'][0];
				$vars = $vars?array_merge($vars, $data['having'][1]):$data['having'][1];
			}
			else
			{
				$having = $data['having'];
			}
		}

		$q = 'SELECT '.(isset($data['fields'])?$data['fields']:'*').' '.
			 'FROM '.$table.($expr?' WHERE '.$expr:'').
			 (isset($data['gb'])?' GROUP BY '.$data['gb'].($having?' HAVING '.$having:''):'').
			 (isset($data['ob'])?' ORDER BY '.$data['ob']:'').
			 (isset($data['lim'])?' LIMIT '.$data['lim']:'');
		$r = $this->query(array($q, $vars));

		return $r?$this->fetch_assoc_all($r, $user_func, $user_func_args):false;
	}
	public function tot_pages($table, $page_size, $expr = null, $field = null)
	{
		return ceil($this->count($table, $expr, $field)/$page_size);
	}
	public function rows_by_page($table, $data, $user_func = null, $user_func_args = null)
	{
		$pid = isset($_GET[$data['page_var']])?intval($_GET[$data['page_var']]):1;
		$tp  = $this->tot_pages(
			$table,
			$data['page_size'],
			(isset($data['expr'])?$data['expr']:null),
			(isset($data['count_field'])?$data['count_field']:false)
		);
		if (!$pid || $pid>$tp)
		{
			$pid = 1;
		} elseif ($pid<0) $pid = $tp?$tp:1;
		$data['lim'] = ($pid * $data['page_size'] - $data['page_size']).', '.$data['page_size'];
		$rows        = $this->rows($table, $data, $user_func, $user_func_args);
		return $rows?
			array(
				'rows'      => $rows,
				'tot_pages' => $tp,
				'tot_rows'  => $this->count(
					$table,
					isset($data['expr'])?$data['expr']:null,
					isset($data['count_field'])?$data['count_field']:null
				)
			):
			array('rows'=>false, 'tot_pages'=>0, 'tot_rows'=>0);
	}
	public function count($table, $expr = null, $field = null)
	{
		$vars = null;
		if (is_array($expr))
		{
			$vars = $expr[1];
			$expr = $expr[0];
		}

		$q = 'SELECT COUNT('.($field?'DISTINCT('.$field.')':'*').') FROM '.$table.($expr?' WHERE '.$expr:'');
		$row = $this->fetch_row($this->query(array($q, $vars)));
		return $row[0];
	}
	public function insert($table, $data)
	{
		if (is_array($data))
		{
			$fields = $data[0];
			$values = $data[1];
		}
		else
		{
			$fields = $data;
			$values = null;
		}

		$q = 'INSERT INTO '.$table.' VALUES('.$fields.')';
		$r = $this->query(array($q, $values));
		return $this->insert_id();
	}
	public function replace($table, $data)
	{
		if (is_array($data))
		{
			$fields = $data[0];
			$values = $data[1];
		}
		else
		{
			$fields = $data;
			$values = null;
		}

		$q = 'REPLACE INTO '.$table.' VALUES('.$fields.')';
		$r = $this->query(array($q, $values));
	}
	public function update($table, $data, $expr = null)
	{
		if (is_array($data))
		{
			$fields = $data[0];
			$vars   = is_array($data[1])?$data[1]:array($data[1]);
		}
		else
		{
			$fields = $data;
			$vars   = array();
		}

		if (is_array($expr))
		{
			$vars = array_merge($vars, is_array($expr[1])?$expr[1]:array($expr[1]));
			$expr = $expr[0];
		}

		$q = 'UPDATE '.$table.' SET '.$fields.($expr?' WHERE '.$expr:'');
		sizeof($vars)?$this->query(array($q, $vars)):$this->query($q);
	}
	public function upd($table, $data, $val, $type = 'u', $name = 'id')
	{
		if (is_array($data))
		{
			$fields = $data[0];
			$vars   = is_array($data[1])?$data[1]:array($data[1]);
		}
		else
		{
			$fields = $data;
			$vars   = array();
		}

		$vars[] = $val;

		$q = 'UPDATE '.$table.' SET '.$fields.' WHERE '.$name.'=%'.$type;
		$this->query(array($q, $vars));
	}
	public function delete($table, $expr = null)
	{
		$vars = null;

		if (is_array($expr))
		{
			$vars = $expr[1];
			$expr = $expr[0];
		}

		$q = 'DELETE FROM '.$table.($expr?' WHERE '.$expr:'');
		$this->query(array($q, $vars));
	}
	public function del($table, $val, $type = 'u', $name = 'id')
	{
		$q = 'DELETE FROM '.$table.' WHERE '.$name.'=%'.$type;
		$this->query(array($q, $val));
	}
	public function row($table, $val, $type = 'u', $name = 'id', $fields = null)
	{
		$q = 'SELECT '.($fields?$fields:'*').' FROM '.$table.' WHERE '.$name.'=%'.$type;
		return ($r = $this->query(array($q, $val)))?$this->fetch_assoc($r):false;
	}
	public function frow($table, $expr = null, $fields = null)
	{
		$vars = null;

		if (is_array($expr))
		{
			$vars = $expr[1];
			$expr = $expr[0];
		}

		$q='SELECT '.($fields?$fields:'*').' FROM '.$table.($expr?' WHERE '.$expr:'');
		return ($r = $this->query(array($q, $vars)))?$this->fetch_assoc($r):false;
	}
	public function field($table, $field, $val, $type = 'u', $name = 'id')
	{
		$q = 'SELECT '.$field.' FROM '.$table.' WHERE '.$name.'=%'.$type;
		$f = $this->num_rows($r = $this->query(array($q, $val)))?$this->fetch_row($r):false;

		return $f?$f[0]:false;
	}
	public function ffield($table, $field, $expr = null)
	{
		$vars = null;

		if (is_array($expr))
		{
			$vars = $expr[1];
			$expr = $expr[0];
		}

		$q='SELECT '.$field.' FROM '.$table.($expr?' WHERE '.$expr:'');
		$f = $this->num_rows($r = $this->query(array($q, $vars)))?$this->fetch_row($r):false;

		return $f?$f[0]:false;
	}
	public function fields($table, array $fields, $val, $type = 'u', $name = 'id')
	{
		$q = 'SELECT '.implode(', ', $fields).' FROM '.$table.' WHERE '.$name.'=%'.$type;

		return $this->num_rows($r = $this->query(array($q, $val)))?$this->fetch_assoc($r):false;
	}
	public function ffields($table, array $fields, $expr = null)
	{
		$vars = null;

		if (is_array($expr))
		{
			$vars = $expr[1];
			$expr = $expr[0];
		}

		$q='SELECT '.implode(', ', $fields).' FROM '.$table.($expr?' WHERE '.$expr:'');

		return $this->num_rows($r = $this->query(array($q, $vars)))?$this->fetch_row($r):false;
	}
	public function escape($val) {
		return mysqli_real_escape_string($this->m_link, $val);
	}
}
?>