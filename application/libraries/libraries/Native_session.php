<?php
if ( ! defined('BASEPATH') )
    exit( 'No direct script access allowed' );

class Native_session
{
    public function __construct()
    {
        session_start();
    }

    public function set( $key, $value )
    {
        $_SESSION[$key] = $value;
    }

    public function set_array( $valueArray )
    {
        foreach($valueArray AS $key=>$value)
		{
			$_SESSION[$key] = $value;
		}
    }
	
    public function get( $key )
    {
        return isset( $_SESSION[$key] ) ? $_SESSION[$key] : null;
    }

    public function regenerate_id( $delOld = false )
    {
        session_regenerate_id( $delOld );
    }

    public function delete( $key )
    {
        unset( $_SESSION[$key] );
    }
	
    public function delete_all($list=array())
    {
        # Just delete those in the list
		if(!empty($list))
		{
			foreach($list AS $key=>$value) unset( $_SESSION[$key] );
		}
		# Delete all
		else 
		{
			$_SESSION = array();
			session_destroy();
		}
    }
}