//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class hook332 extends _HOOK_CLASS_
{
    /**
     * Create
     *
     * @param	\IPS\nexus\Package\Hosting	$package	The package
     * @param	\IPS\nexus\Customer			$customer	The customer
     * @return	void
     */
    public function create( \IPS\nexus\Package\Hosting $package, \IPS\nexus\Customer $customer )
    {
        // Get settings
        $configuration = json_decode( \IPS\Settings::i()->resellerhosting_configuration, TRUE );

        // If we have a plan name
        if ( isset( $configuration[$package->id] ) AND $configuration[$package->id] != NULL )
        {
            // Create the account
            $response = $this->server->api( 'createacct', array(
                'username'		=> $this->username,
                'domain'		=> $this->domain,
                'password'		=> $this->password,
                'ip'			=> $package->ip ? 'y' : 'n',
                'cgi'			=> $package->cgi,
                'frontpage'		=> $package->frontpage,
                'hasshell'		=> $package->hasshell,
                'contactemail'	=> $customer->email,
                'plan'          => $configuration[$package->_id]
            ) );

            // If we get an error
            if ( !$response['result'][0]['status'] )
            {
                // Throw an error
                throw new \IPS\nexus\Hosting\Exception( $this->server, $response['result'][0]['statusmsg'] );
            }
        }

        // Normal account
        else
        {
            // Call parent
            return parent::create( $package, $customer );
        }
    }

    /**
     * Edit Privileges
     *
     * @param	array	$values	Values from form
     * @return	void
     */
    public function edit( $values )
    {
        // If update contains a plan name
        if ( isset( $values['p_plan'] ) )
        {
            /// Create the data array
            $data = array( 'user' => $this->username );
            $data['pkg'] = $values['p_plan'];

            // Update the plan
            $response = $this->server->api( 'changepackage', $data );

            // If we have an error
            if( !$response['result'][0]['status'] )
            {
                // Throw error
                throw new \IPS\nexus\Hosting\Exception( $this->server, $response['result'][0]['statusmsg'] );
            }
        }

        // Not a plan change
        else
        {
            // Call parent
            return parent::edit( $values );
        }
    }
}
