//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class hook330 extends _HOOK_CLASS_
{
    /**
     * Package Types
     *
     * @return	array
     */
    static public function packageTypes()
    {
        // Get types
        $types = parent::packageTypes();

        // Add reseller option
        $types['reseller'] = 'IPS\nexus\Package\Hosting';

        // Return the types
        return $types;
    }

    /**
     * [Node] Add/Edit Form
     *
     * @param	\IPS\Helpers\Form	$form	The form
     * @return	void
     */
    public function form( &$form )
    {
        // Call the parent the form
        parent::form( $form );

        // Get product radio toggle
        $radio = $form->elements['package_settings']['p_type'];

        // Remove dedicated hosting options
        $radio->options['toggles']['reseller'] = array_diff(
                $radio->options['toggles']['reseller'],
                array( 'p_queue', 'p_quota', 'p_bwlimit', 'p_maxftp', 'p_maxsql', 'p_maxpop', 'p_maxlst', 'p_maxsub', 'p_maxpark', 'p_maxaddon', 'p_ip', 'p_cgi', 'p_frontpage', 'p_hasshell' ) );

        // Get the saved configuration
        $configuration = json_decode( \IPS\Settings::i()->resellerhosting_configuration, TRUE );

        // If we have a saved reseller configuration
        if ( isset( $configuration[$this->id] ) AND $configuration[$this->id] != NULL )
        {
            // Set the radio to reseller
            $radio->value = 'reseller';
        }
    }

    /**
     * [Node] Save Add/Edit Form
     *
     * @param	array	$values	Values from the form
     * @return	void
     */
    public function saveForm( $values )
    {
        // Save information we'll need
        $type = isset( $values['p_type'] ) ? $values['p_type'] : NULL;
        $plan = isset( $values['p_plan'] ) ? $values['p_plan'] : NULL;

        // If this is a reseller package
        if ( $type == 'reseller' )
        {
            // Set to hosting
            $values['p_type'] = 'hosting';

            // Unset
            unset( $values['p_plan'] );
        }

        // Call parent save
        $return = parent::saveForm( $values );

        // If we have a reseller plan to save
        if ( $plan != NULL )
        {
            // Get the saved configuration
            $configuration = json_decode( \IPS\Settings::i()->resellerhosting_configuration, TRUE );

            // Set new value
            $configuration[$this->id] = $plan;

            // Save the configuration
            \IPS\Settings::i()->changeValues( array( 'resellerhosting_configuration' => json_encode( $configuration ) ) );
        }

        // Return
        return $return;
    }
}