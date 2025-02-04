<?php
/**
 * Bcrypt
 * 
 * Methods to hash and validate passwords
 */
class Bcrypt {
	
	/**
	 * Hash
	 * 
	 * hash a password with salt, 12 rounds
	 * 
	 * @param string	$password	password to hash
	 * 
	 * @return string	hash of password
	 * 
	 * @access public
	 * @static
	 */
	public static function hash($password){
		return crypt($password, '$2a$12$'.self::salt());
	}
	
	/**
	 * Check
	 * 
	 * validate a password against a hash
	 * 
	 * @param string 	$password	password to validate
	 * @param string	$hash		hash to validate against
	 * 
	 * @return boolean	if password matches hash, true, else, false
	 * 
	 * @access public
	 * @static
	 */
	public static function check($password, $hash){
		return $hash == crypt($password, $hash);
	}
	
	/**
	 * Salt
	 * 
	 * generate a random salt
	 * 
	 * @return string generated salt
	 * 
	 * @access private
	 * @static
	 */
	public static function salt() {
		$ch = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789./';
		$chlen = 63;
		$s = "";
		for($i=0; $i<22; $i++)
		    $s .= $ch[mt_rand(0,$chlen)];
		return $s;
	}

}

?>