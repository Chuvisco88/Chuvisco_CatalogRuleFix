<?php

class Chuvisco_CatalogRuleFix_Model_Action_Index_Refresh extends Mage_CatalogRule_Model_Action_Index_Refresh
{
    public function execute()
    {
        $this->_app->dispatchEvent('catalogrule_before_apply', array('resource' => $this->_resource));

        /** @var $coreDate Mage_Core_Model_Date */
        $coreDate  = $this->_factory->getModel('core/date');
        $timestamp = $coreDate->timestamp('Today'); // use local timestamp instead of gmtTimestamp

        foreach ($this->_app->getWebsites(false) as $website) {
            /** @var $website Mage_Core_Model_Website */
            if ($website->getDefaultStore()) {
                $this->_reindex($website, $timestamp);
            }
        }

        $this->_prepareGroupWebsite($timestamp);
        $this->_prepareAffectedProduct();
    }
}