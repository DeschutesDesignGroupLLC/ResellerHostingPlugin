//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class hook43 extends _HOOK_CLASS_
{
    /**
     * ACP Fields
     *
     * @param	\IPS\nexus\Package	$package	The package
     * @param	bool				$custom		If TRUE, is for a custom package
     * @param	bool				$customEdit	If TRUE, is editing a custom package
     * @return	array
     */
    static public function acpFormFields( \IPS\nexus\Package $package, $custom=false, $customEdit=false )
    {
        // Get parent form fields
        $fields = parent::acpFormFields( $package, $custom, $customEdit );

        // Get the saved configuration
        $configuration = json_decode( \IPS\Settings::i()->resellerhosting_configuration, TRUE );

        // Add our custom plan field
        $fields['package_settings']['plan'] = new \IPS\Helpers\Form\Text( 'p_plan', $package->type === 'hosting' ? $configuration[$package->id] : NULL, isset( $configuration[$package->id] ) AND $configuration[$package->id] != NULL ? TRUE : FALSE );

        // Return the fields
        return $fields;
    }

    /**
     * On Upgrade/Downgrade
     *
     * @param	\IPS\nexus\Purchase							$purchase				The purchase
     * @param	\IPS\nexus\Package							$newPackage				The package to upgrade to
     * @param	int|NULL|\IPS\nexus\Purchase\RenewalTerm	$chosenRenewalOption	The chosen renewal option
     * @return	void
     */
    public function onChange( \IPS\nexus\Purchase $purchase, \IPS\nexus\Package $newPackage, $chosenRenewalOption=NULL )
    {
        // Get settings
        $configuration = json_decode( \IPS\Settings::i()->resellerhosting_configuration, TRUE );

        // If we have a plan name with the new package
        if ( isset( $configuration[$newPackage->id] ) AND $configuration[$newPackage->id] != NULL )
        {
            // Try and load account
            try
            {
                // Load account
                $account = \IPS\nexus\Hosting\Account::load( $purchase->id );

                // If our new package plan is different than the old
                if ( $configuration[$newPackage->id] !=  $configuration[$this->id] )
                {
                    // Update the account
                    $update = array( 'p_plan' => $configuration[$newPackage->id] );
                    $account->edit( $update );
                }
            }

            // Unable to load account
            catch ( \OutOfRangeException $e ) {}
        }

        // The package we are changing to has a plan name
        else
        {
            // Call parent
            return parent::onChange( $purchase, $newPackage, $chosenRenewalOption );
        }
    }
}
