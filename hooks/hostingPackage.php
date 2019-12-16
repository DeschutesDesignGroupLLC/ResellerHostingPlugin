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
        $fields['package_settings']['plan'] = new \IPS\Helpers\Form\Text( 'p_plan', $package->type === 'hosting' ? $configuration[ $package->id ] : NULL, TRUE);

        // Return the fields
        return $fields;
    }

    /**
     * ACP Edit Form
     *
     * @param	\IPS\nexus\Purchase				$purchase	The purchase
     * @param	\IPS\Helpers\Form				$form	The form
     * @param	\IPS\nexus\Purchase\RenewalTerm	$renewals	The renewal term
     * @return	string
     */
    public function acpEdit( \IPS\nexus\Purchase $purchase, \IPS\Helpers\Form $form, $renewals )
    {
        // Get the saved configuration
        $configuration = json_decode( \IPS\Settings::i()->resellerhosting_configuration, TRUE );

        // If this is a reseller hosting package
        if ( \in_array( $this->id, array_keys( $configuration ) ) )
        {
            // Add our plan fields
            $form->addHeader( 'reseller_hosting_edit_form_header' );
            $form->add( new \IPS\Helpers\Form\Text( 'p_plan', $configuration[ $this->id ], TRUE ) );
        }

        // Not a reseller hosting package
        else
        {
            // Return the parent fields
            return parent::acpEdit( $purchase, $form, $renewals );
        }
    }

    /**
     * ACP Edit Save
     *
     * @param	\IPS\nexus\Purchase	$purchase	The purchase
     * @param	array				$values		Values from form
     * @return	string
     */
    public function acpEditSave( \IPS\nexus\Purchase $purchase, array $values )
    {
        // Get the saved configuration
        $configuration = json_decode( \IPS\Settings::i()->resellerhosting_configuration, TRUE );

        // If this is a reseller hosting package
        if ( \in_array( $this->id, array_keys( $configuration ) ) )
        {
            // Try and load account
            try
            {
                // Load account
                $account = \IPS\nexus\Hosting\Account::load( $purchase->id );

                // Update the account
                $update = array( 'p_plan' => $values['p_plan'] );
                $account->edit( $update );

                // Unset the value
                unset( $values['p_plan'] );
            }

            // Unable to load account
            catch ( \OutOfRangeException $e ) {}
        }

        // Not a reseller hosting package
        else
        {
            // Return the parent
            return parent::acpEditSave( $purchase, $values );
        }
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
        if ( \in_array( $newPackage->id, array_keys( $configuration ) ) )
        {
            // Try and load account
            try
            {
                // Load account
                $account = \IPS\nexus\Hosting\Account::load( $purchase->id );

                // If our new package plan is different than the old
                if ( $configuration[ $newPackage->id ] != $configuration[ $this->id ] )
                {
                    // Update the account
                    $update = array( 'p_plan' => $configuration[ $newPackage->id ] );
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