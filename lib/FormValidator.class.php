<?php
/**
 * FMPHP-Mini (http://www.webinno.cn)
 *
 * An open source application development framework for PHP 5.4 or newer
 *
 * @package		FMPHP-Mini
 * @author		FMPHP Dev Team （QQ官方群：330488100）
 * @copyright	copyright (c) 2015 - 2016, 互联网创新实验室(http://www.webinno.cn)
 *
 * @license		
 * @link		http://www.webinno.cn/project/FMPHP-Mini
 * @link        https://github.com/Webinno/FMPHP-Mini
 * @since		Version 0.1
 * 
 * @filesource
 */


/**
* Pork.FormValidator
* Validates arrays or properties by setting up simple arrays
* 
* @package pork
* @author SchizoDuckie
* @copyright SchizoDuckie 2009
* @version 1.0
* @access public
* Modified by Tom (fxs_2008@sina.com)
*/
class FormValidator
{
	//默认消息
	public  $_error_messages = array(
	//var_filter
	'url' =>  '不合法的URL',
	'int' =>  '不是整数',
	'float' => '不是浮点数',
	'email' => '不合法的Email',

	// regexp
	'date' => '“%s”不是正确的日期格式',
	'amount' => '“%s”不是正确的数量',
	'number' => '“%s”不是正确数字格式',
	'alfanum' => '“%s”不正确的alfa字符',
	'not_empty' => '“%s”不能为空',
	'words' => '“%s”不正确的英文单词',
	'phone' => '“%s”电话号码格式不正确',
	'zipcode' => '“%s”不是正确的邮政政编码',
	'plate' => '“%s”不是正确的日期格式',
	'price' => '“%s”不是正确的日期格式',
	'2digitopt' => '“%s”不是正确的日期格式',
	'2digitforce' => '“%s”不是正确的日期格式',
	'anything' => '“%s”长度不为空',


	//function name
	'required'			=> "“%s” 字段是必需的",
	'isset'				=> "“%s” 必须有值",
	'valid_email'		=> "“%s” 字段必须是合法的Email地址",
	'valid_emails'		=> "“%s” 字段必须包括所有有效Email地址",
	'valid_url'			=> "“%s” 字段必须是合法的URL",
	'valid_ip'			=> "“%s” 字段必须是合法的IP格式",
	'min_length'		=> "“%s” 至少需要%s个字符的长度",
	'max_length'		=> "“%s” 字符串长度不能超过%s",
	'exact_length'		=> "“%s”字段长度需等于%s",
	'alpha'				=> "“%s” 字段仅能包含alphabetical字符",
	'alpha_numeric'		=> "“%s” 字段仅能包含alpha-numeric字符",
	'alpha_dash'		=> "“%s” 字段仅能包含alpha-numeric characters, underscores, and dashes字符",
	'numeric'			=> "“%s” 字段只能包含numbers",
	'is_numeric'		=> "“%s” 字段只能包含numeric字符",
	'integer'			=> "“%s” 字段只能包含integer",
	'regex_match'		=> "“%s” 格式不合法",
	'matches'			=> "“%s” 不匹配“%s”字段值",
	'is_unique' 		=> "“%s” field must contain a unique value.",
	'is_natural'		=> "“%s” 只能包含正数",
	'is_natural_no_zero'=> "“%s” 只能包含大于0的数",
	'decimal'			=> "“%s” 只能包含decimal number.",
	'less_than'			=> "“%s” 只能包含小于“%s”的数",
	'greater_than'		=> "“%s”只能包含大与“%s”数",
	);

	public static $regexes = Array(
	'date'       => "^[0-9]{4}[-/][0-9]{1,2}[-/][0-9]{1,2}\$",
	'amount'     => "^[-]?[0-9]+\$",
	'number'     => "^[-]?[0-9,]+\$",
	'alfanum'    => "^[0-9a-zA-Z ,.-_\\s\?\!]+\$",
	'not_empty'  => "[a-z0-9A-Z]+",
	'words'      => "^[A-Za-z]+[A-Za-z \\s]*\$",
	'phone'      =>"^[0-9]{10,11}\$",
	'zipcode'    => "^[1-9][0-9]{3}[a-zA-Z]{2}\$",
	'plate'      => "^([0-9a-zA-Z]{2}[-]){2}[0-9a-zA-Z]{2}\$",
	'price'      => "^[0-9.,]*(([.,][-])|([.,][0-9]{2}))?\$",
	'2digitopt'  => "^\d+(\,\d{2})?\$",
	'2digitforce'=> "^\d+\,\d\d\$",
	'anything'   =>"^[\d\D]{1,}\$",
	'username'   => "^[\w]{3,32}\$",
	);

	private $fields = array(),  $_field_data = array(), $post = array();
	private $validations = array(),$sanatations = array(),$mandatories = array(), $equal= array();
	private  $errors = array(), $corrects = array();


	public function __construct($fm)
	{
		$this->FM = $fm;
	}

	function getErrorMessege($key) {
		if (isset($this->_error_messages[$key])) {
			return $this->_error_messages[$key];
		}
		return '';
	}

	/**
 * Validates an array of items (if needed) and returns true or false
 *
 * JP modofied this function so that it checks fields even if they are not submitted.
 * for example the original code did not check for a mandatory field if it was not submitted.
 * Also the types of non mandatory fields were not checked.
 */
	public function validate(& $post)
	{
		$this->post =  $post;

		// Do we even have any data to process?  Mm?
		if (count($post) == 0)
		{
			return FALSE;
		}
		// log_message('debug', "Unable to find validation rules");
		// Load the language file containing error messages
		//$this->CI->lang->load('form_validation');

		// Cycle through the rules for each field, match the
		// corresponding $post item and test for errors
		foreach ($this->_field_data as $field => $row)
		{
			// Fetch the data from the corresponding $post array and cache it in the _field_data array.
			// Depending on whether the field name is an array or a string will determine where we get it from.

			if ($row['is_array'] == TRUE)
			{
				$this->_field_data[$field]['postdata'] = $this->_reduce_array($post, $row['keys']);
			}
			else
			{
				if (isset($post[$field]) AND $post[$field] != "")
				{
					$this->_field_data[$field]['postdata'] = $post[$field];
				}
			}

			$this->_execute($row, explode('|', $row['rules']), $this->_field_data[$field]['postdata']);
		}

		// Did we end up with any errors?
		$total_errors = count($this->errors);

		if ($total_errors > 0)
		{
			//$this->_safe_form_data = TRUE;
		}

		// Now we need to re-set the POST data with the new, processed data
		$this->_reset_post_array($post);

		// No errors, validation passes!
		if ($total_errors == 0)
		{
			return TRUE;
		}

		// Validation fails
		return FALSE;

	}

	/**
	 * Executes the Validation routines
	 *
	 * @access	private
	 * @param	array
	 * @param	array
	 * @param	mixed
	 * @param	integer
	 * @return	mixed
	 */
	protected function _execute($row, $rules, $postdata = NULL, $cycles = 0)
	{
		$errMsg = isset($row['error']) ? $row['error'] : '';

		$errMsgs = explode('|', $errMsg);
		$errMsgs = array_map('trim', $errMsgs);
		//var_dump($errMsgs);

		$hasError = FALSE;
		// If the $_POST data is an array we will run a recursive call
		if (is_array($postdata))
		{
			//print_r($postdata);
			foreach ($postdata as $key => $val)
			{
				$this->_execute($row, $rules, $val, $cycles);
				$cycles++;
				// var_dump($cycles);
				//var_dump($cycles);
			}

			return;
		}

		// --------------------------------------------------------------------

		// If the field is blank, but NOT required, no further tests are necessary
		$callback = FALSE;
		if ( ! in_array('required', $rules) AND is_null($postdata))
		{
			// Before we bail out, does the rule contain a callback?
			if (preg_match("/(callback_\w+(\[.*?\])?)/", implode(' ', $rules), $match))
			{
				$callback = TRUE;
				$rules = (array('1' => $match[1]));
			}
			else
			{
				return;
			}
		}

		// 为什么要下边的--------------------------------------------------------------------

		// --------------------------------------------------------------------


		$err_index = 0;
		// Cycle through each rule and run it
		foreach ($rules As $rule)
		{
			$err_index++;
			$_in_array = FALSE;


			// We set the $postdata variable with the current data in our master array so that
			// each cycle of the loop is dealing with the processed data from the last cycle
			if ($row['is_array'] == TRUE AND is_array($this->_field_data[$row['field']]['postdata']))
			{
				// We shouldn't need this safety, but just in case there isn't an array index
				// associated with this cycle we'll bail out
				if ( ! isset($this->_field_data[$row['field']]['postdata'][$cycles]))
				{
					continue;
				}

				$postdata = $this->_field_data[$row['field']]['postdata'][$cycles];
				$_in_array = TRUE;
			}
			else
			{
				$postdata = $this->_field_data[$row['field']]['postdata'];
			}

			// --------------------------------------------------------------------

			// Is the rule a callback?
			$callback = FALSE;
			if (substr($rule, 0, 9) == 'callback_')
			{
				$rule = substr($rule, 9);
				$callback = TRUE;
			}

			// Strip the parameter (if exists) from the rule
			// Rules can contain a parameter: max_length[5]
			$param = FALSE;
			if (preg_match("/(.*?)\[(.*)\]/", $rule, $match))
			{
				$rule	= $match[1];
				$param	= $match[2];
			}

			// Call the function that corresponds to the rule
			if ($callback === TRUE)
			{
				if ( ! method_exists($this->FM, $rule))
				{
					continue;
				}

				// Run the function and grab the result
				$result = $this->FM->$rule($postdata, $param);

				// Re-assign the result to the master data array
				if ($_in_array == TRUE)
				{
					$this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
				}
				else
				{
					$this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
				}

				// If the field isn't required and we just processed a callback we'll move on...
				if ( ! in_array('required', $rules, TRUE) AND $result !== FALSE)
				{
					continue;
				}
			}
			else
			{
				//没有回调
				if ( ! method_exists($this, $rule))
				{
					// If our own wrapper function doesn't exist we see if a native PHP function does.
					// Users can use any native PHP function call that has one param.
					if (function_exists($rule))
					{
						$result = $rule($postdata);

						if ($_in_array == TRUE)
						{
							$this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
						}
						else
						{
							$this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
						}
					}
					else
					{
						log_message('debug', "Unable to find validation rule: ".$rule);
					}

					continue;
				}

				$result = $this->$rule($postdata, $param);    //result

				if ($_in_array == TRUE)
				{
					$this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
				}
				else
				{
					$this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
				}
			}

			// Did the rule test negatively?  If so, grab the error.
			if ($result === FALSE)
			{
				$hasError = TRUE;
				if ( ! isset($this->_error_messages[$rule]))
				{
					$line = 'Unable to access an error message corresponding to your field name.';
				}
				else
				{
					$line = $this->_error_messages[$rule];
				}

				// Is the parameter we are inserting into the error message the name
				// of another field?  If so we need to grab its "field label"
				/*
				if (isset($this->_field_data[$param]) AND isset($this->_field_data[$param]['label']))
				{
				$param = $this->_translate_fieldname($this->_field_data[$param]['label']);
				}
				*/
				if (isset($errMsgs[$err_index-1]) && $errMsgs[$err_index-1] != '') {
					$line = $errMsgs[$err_index-1];
				}

				// Build the error message
				// $message = sprintf($line, $this->_translate_fieldname($row['label']), $param);
				$message = sprintf($line, $row['label'], $param);
				// Save the error message
				//$this->_field_data[$row['field']]['error'][] = $message;
				$this->errors[] = array($row['field'], $message);
				//return;
				/*
				if ( ! isset($this->_error_array[$row['field']]))
				{
				$this->_error_array[$row['field']] = $message;
				}
				return;
				}
				*/
			}
			if ($hasError) {
				//如果设置自定义， 则仅显示自定义
				if(count($errMsgs) ==1 && $errMsgs[0] != '') {
					$this->_error_array[$row['field']] = array();
					$this->_error_array[$row['field']][] = $errMsgs[0];
				}
			}
		}
	}


	/* JP
	* Returns a JSON encoded array containing the names of fields with errors and those without.
	*/
	public function getJSON() {

		$errors = array();

		$correct = array();

		if(!empty($this->errors))
		{
			foreach($this->errors as $key=>$val) { $errors[$key] = $val; }
		}

		if(!empty($this->corrects))
		{
			foreach($this->corrects as $key=>$val) { $correct[$key] = $val; }
		}

		$output = array('errors' => $errors, 'correct' => $correct);

		return json_encode($output);
	}

	function getErrors() {
		return $this->errors;
	}


	/**
 *
 * Sanatizes an array of items according to the $this->sanatations
 * sanatations will be standard of type string, but can also be specified.
 * For ease of use, this syntax is accepted:
 * $sanatations = array('fieldname', 'otherfieldname'=>'float');
 */
	public function sanatize($post, $key_s='', $index =0)
	{

		foreach($post as $key=>$val)
		{
			if ($index==0) {
				$key_s = $key;
			} else {
				$key_s = $key_s . "[". $key . "]";
			}
			// echo $key_s . "\n";

			if (is_array($val)) {
				$this->sanatize($val, $key_s, $index+ 1);
			} else {
				if (isset($this->sanatations[$key_s])) {
					$post[$key] = self::sanatizeItem($val, $this->validations[$key]);
				}
			}
		}
		return($post);
	}


	/**
 *
 * Adds an error to the errors array.
 */ 
	private function addError($field, $type='string')
	{
		$this->errors[] = array($type, $type);
	}

	/**
 *
 * Sanatize a single var according to $type.
 * Allows for static calling to allow simple sanatization
 */
	public static function sanatizeItem($var, $type)
	{
		$flags = NULL;
		switch($type)
		{
			case 'url':
				$filter = FILTER_SANITIZE_URL;
				break;
			case 'int':
				$filter = FILTER_SANITIZE_NUMBER_INT;
				break;
			case 'float':
				$filter = FILTER_SANITIZE_NUMBER_FLOAT;
				$flags = FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND;
				break;
			case 'email':
				$var = substr($var, 0, 254);
				$filter = FILTER_SANITIZE_EMAIL;
				break;
			case 'string':
			default:
				$filter = FILTER_SANITIZE_STRING;
				$flags = FILTER_FLAG_NO_ENCODE_QUOTES;
				break;
		}
		$output = filter_var($var, $filter, $flags);
		return($output);
	}
	/**
 *
 * Sanatize a single var according to $type.
 * Allows for static calling to allow simple sanatization
 */
	public static function sanatize_item($var, $type)
	{
		$flags = NULL;
		switch($type)
		{
			case 'url':
				$filter = FILTER_SANITIZE_URL;
				break;
			case 'int':
				$filter = FILTER_SANITIZE_NUMBER_INT;
				break;
			case 'float':
				$filter = FILTER_SANITIZE_NUMBER_FLOAT;
				$flags = FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND;
				break;
			case 'email':
				$var = substr($var, 0, 254);
				$filter = FILTER_SANITIZE_EMAIL;
				break;
			case 'string':
			default:
				$filter = FILTER_SANITIZE_STRING;
				$flags = FILTER_FLAG_NO_ENCODE_QUOTES;
				break;
		}
		$output = filter_var($var, $filter, $flags);
		return($output);
	}

	/**
 *
 * Validates a single var according to $type.
 * Allows for static calling to allow simple validation.
 *
 */
	public static function validateItem($var, $type)
	{
		if(array_key_exists($type, self::$regexes))
		{
			$returnval =  filter_var($var, FILTER_VALIDATE_REGEXP, array("options"=> array("regexp"=>'!'.self::$regexes[$type].'!i'))) !== false;
			return($returnval);
		}
		$filter = false;
		switch($type)
		{
			case 'email':
				$var = substr($var, 0, 254);
				$filter = FILTER_VALIDATE_EMAIL;
				break;
			case 'int':
				$filter = FILTER_VALIDATE_INT;
				break;
			case 'boolean':
				$filter = FILTER_VALIDATE_BOOLEAN;
				break;
			case 'ip':
				$filter = FILTER_VALIDATE_IP;
				break;
			case 'url':
				$filter = FILTER_VALIDATE_URL;
				break;
		}
		return ($filter === false) ? false : filter_var($var, $filter) !== false ? true :     false;
	}

	//================== 后增函数 ====================//

	/**
	 * Traverse a multidimensional $post array index until the data is found
	 *
	 * @access	private
	 * @param	array
	 * @param	array
	 * @param	integer
	 * @return	mixed
	 */
	protected function _reduce_array($array, $keys, $i = 0)
	{
		if (is_array($array))
		{
			if (isset($keys[$i]))
			{
				if (isset($array[$keys[$i]]))
				{
					$array = $this->_reduce_array($array[$keys[$i]], $keys, ($i+1));
				}
				else
				{
					return NULL;
				}
			}
			else
			{
				return $array;
			}
		}

		return $array;
	}
	/**
	 * Re-populate the _POST array with our finalized and processed data
	 *
	 * @access	private
	 * @return	null
	 */

	protected function _reset_post_array(& $post)
	{
		foreach ($this->_field_data as $field => $row)
		{
			if ( ! is_null($row['postdata']))
			{
				if ($row['is_array'] == FALSE)
				{
					if (isset($post[$row['field']]))
					{

						//$post[$row['field']] = $this->prep_for_form($row['postdata']);
						$post[$row['field']] = $row['postdata'];
					}
				}
				else
				{
					// start with a reference
					$post_ref =& $post;

					// before we assign values, make a reference to the right POST key
					if (count($row['keys']) == 1)
					{
						$post_ref =& $post_ref[current($row['keys'])];
					}
					else
					{
						foreach ($row['keys'] as $val)
						{
							$post_ref =& $post_ref[$val];
						}
					}

					if (is_array($row['postdata']))
					{
						$array = array();
						foreach ($row['postdata'] as $k => $v)
						{
							//$array[$k] = $this->prep_for_form($v);
							$array[$k] =$v;
						}

						$post_ref = $array;
					}
					else
					{
						//$post_ref = $this->prep_for_form($row['postdata']);
						$post_ref = $row['postdata'];
					}
				}
			}
		}
	}

	/**
	 * Set Rules
	 *
	 * This function takes an array of field names and validation
	 * rules as input, validates the info, and stores it
	 *
	 * @access	public
	 * @param	mixed
	 * @param	string
	 * @return	void
	 */
	public function setRules($field, $label = '', $rules = '', $error = '', $required=0, $sanatize=0)
	{
		// No reason to set rules if we have no POST data
		if (count($_POST) == 0)
		{
			return $this;
		}

		// If an array was passed via the first parameter instead of indidual string
		// values we cycle through it and recursively call this function.
		if (is_array($field))
		{
			foreach ($field as $row)
			{
				//	print_r($row);
				// Houston, we have a problem...
				if ( ! isset($row['field']) OR ! isset($row['rules']))
				{
					continue;
				}

				// If the field label wasn't passed we use the field name
				$label = ( ! isset($row['label'])) ? $row['field'] : $row['label'];
				$required = empty($row['required']) ?  0 : 1;
				$sanatize = empty($row['sanatize']) ? 0 : 1;
				$error = empty($row['errMsg']) ? '' : $row['errMsg'];

				// Here we go!
				$this->setRules($row['field'], $label, $row['rules'], $error,  $required, $sanatize );
			}
			return $this;
		}

		// No fields? Nothing to do...
		if ( ! is_string($field) OR  ! is_string($rules) OR $field == '')
		{
			return $this;
		}

		// If the field label wasn't passed we use the field name
		$label = ($label == '') ? $field : $label;

		// Is the field name an array?  We test for the existence of a bracket "[" in
		// the field name to determine this.  If it is an array, we break it apart
		// into its components so that we can fetch the corresponding POST data later
		if (strpos($field, '[') !== FALSE AND preg_match_all('/\[(.*?)\]/', $field, $matches))
		{
			// Note: Due to a bug in current() that affects some versions
			// of PHP we can not pass function call directly into it
			$x = explode('[', $field);
			$indexes[] = current($x);

			for ($i = 0; $i < count($matches['0']); $i++)
			{
				if ($matches['1'][$i] != '')
				{
					$indexes[] = $matches['1'][$i];
				}
			}

			$is_array = TRUE;
		}
		else
		{
			$indexes	= array();
			$is_array	= FALSE;
		}


		// Build our master array
		$this->_field_data[$field] = array(
		'field'				=> $field,
		'label'				=> $label,
		'rules'				=> $rules,
		'is_array'			=> $is_array,
		'keys'				=> $indexes,
		'postdata'			=> NULL,
		'error'				=> $error,
		'required'			=> $required,
		'sanatize'				=> $sanatize
		);
		/*
		if ($required) {
		$this->mandatories[] = $field;
		}
		if ($sanatize) {
		$this->sanatations[] = $field;
		}

		if (!empty($rules)) {
		$this->validations[] = 	$field;
		}
		*/
		//添加数据
		return $this;
	}
	public function setMessage($lang, $val = '')
	{
		if ( ! is_array($lang))
		{
			$lang = array($lang => $val);
		}

		$this->_error_messages = array_merge($this->_error_messages, $lang);

		return $this;
	}


	// --------------------------------------------------------------------

	/**
	 * Required
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function required($str)
	{
		if ( ! is_array($str))
		{
			return (trim($str) == '') ? FALSE : TRUE;
		}
		else
		{
			return ( ! empty($str));
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Performs a Regular Expression match test.
	 *
	 * @access	public
	 * @param	string
	 * @param	regex
	 * @return	bool
	 */
	public function regex_match($str, $regex)
	{
		if ( ! preg_match($regex, $str))
		{
			return FALSE;
		}

		return  TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Match one field to another
	 *
	 * @access	public
	 * @param	string
	 * @param	field
	 * @return	bool
	 */
	public function matches($str, $field)
	{
		if ( ! isset($post[$field]))
		{
			return FALSE;
		}

		$field = $post[$field];

		return ($str !== $field) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Match one field to another
	 *
	 * @access	public
	 * @param	string
	 * @param	field
	 * @return	bool
	 */
	public function is_unique($str, $field)
	{
		list($table, $field)=explode('.', $field);
		$query = $this->FM->db->limit(1)->get_where($table, array($field => $str));

		return $query->num_rows() === 0;
	}

	// --------------------------------------------------------------------

	/**
	 * Minimum Length
	 *
	 * @access	public
	 * @param	string
	 * @param	value
	 * @return	bool
	 */
	public function min_length($str, $val)
	{
		if (preg_match("/[^0-9]/", $val))
		{
			return FALSE;
		}

		if (function_exists('mb_strlen'))
		{
			return (mb_strlen($str) < $val) ? FALSE : TRUE;
		}

		return (strlen($str) < $val) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Max Length
	 *
	 * @access	public
	 * @param	string
	 * @param	value
	 * @return	bool
	 */
	public function max_length($str, $val)
	{
		if (preg_match("/[^0-9]/", $val))
		{
			return FALSE;
		}

		if (function_exists('mb_strlen'))
		{
			return (mb_strlen($str) > $val) ? FALSE : TRUE;
		}

		return (strlen($str) > $val) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Exact Length
	 *
	 * @access	public
	 * @param	string
	 * @param	value
	 * @return	bool
	 */
	public function exact_length($str, $val)
	{
		if (preg_match("/[^0-9]/", $val))
		{
			return FALSE;
		}

		if (function_exists('mb_strlen'))
		{
			return (mb_strlen($str) != $val) ? FALSE : TRUE;
		}

		return (strlen($str) != $val) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Valid Email
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function valid_email($str)
	{
		return ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Valid Emails
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function valid_emails($str)
	{
		if (strpos($str, ',') === FALSE)
		{
			return $this->valid_email(trim($str));
		}

		foreach (explode(',', $str) as $email)
		{
			if (trim($email) != '' && $this->valid_email(trim($email)) === FALSE)
			{
				return FALSE;
			}
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Validate IP Address
	 *
	 * @access	public
	 * @param	string
	 * @param	string "ipv4" or "ipv6" to validate a specific ip format
	 * @return	string
	 */
	public function valid_ip($ip, $which = '')
	{
		return $this->CI->input->valid_ip($ip, $which);
	}

	// --------------------------------------------------------------------

	/**
	 * Alpha
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function alpha($str)
	{
		return ( ! preg_match("/^([a-z])+$/i", $str)) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Alpha-numeric
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function alpha_numeric($str)
	{
		return ( ! preg_match("/^([a-z0-9])+$/i", $str)) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Alpha-numeric with underscores and dashes
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function alpha_dash($str)
	{
		return ( ! preg_match("/^([-a-z0-9_-])+$/i", $str)) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Numeric
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function numeric($str)
	{
		return (bool)preg_match( '/^[\-+]?[0-9]*\.?[0-9]+$/', $str);

	}

	// --------------------------------------------------------------------

	/**
	 * Is Numeric
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function is_numeric($str)
	{
		return ( ! is_numeric($str)) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Integer
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function integer($str)
	{
		return (bool) preg_match('/^[\-+]?[0-9]+$/', $str);
	}

	// --------------------------------------------------------------------

	/**
	 * Decimal number
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function decimal($str)
	{
		return (bool) preg_match('/^[\-+]?[0-9]+\.[0-9]+$/', $str);
	}

	// --------------------------------------------------------------------

	/**
	 * Greather than
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function greater_than($str, $min)
	{
		if ( ! is_numeric($str))
		{
			return FALSE;
		}
		return $str > $min;
	}

	// --------------------------------------------------------------------

	/**
	 * Less than
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function less_than($str, $max)
	{
		if ( ! is_numeric($str))
		{
			return FALSE;
		}
		return $str < $max;
	}

	// --------------------------------------------------------------------

	/**
	 * Is a Natural number  (0,1,2,3, etc.)
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function is_natural($str)
	{
		return (bool) preg_match( '/^[0-9]+$/', $str);
	}

	// --------------------------------------------------------------------

	/**
	 * Is a Natural number, but not a zero  (1,2,3, etc.)
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function is_natural_no_zero($str)
	{
		if ( ! preg_match( '/^[0-9]+$/', $str))
		{
			return FALSE;
		}

		if ($str == 0)
		{
			return FALSE;
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Valid Base64
	 *
	 * Tests a string for characters outside of the Base64 alphabet
	 * as defined by RFC 2045 http://www.faqs.org/rfcs/rfc2045
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function valid_base64($str)
	{
		return (bool) ! preg_match('/[^a-zA-Z0-9\/\+=]/', $str);
	}

	function is_unsigned_numeric($str) {
		if (is_numeric($str)) {
			if ($str >= 0) {
				return TRUE;
			}
		}
		return FALSE;
	}

	function numberFormat($str, $des) {
		return number_format($str, $des, '.', '');
	}
}
