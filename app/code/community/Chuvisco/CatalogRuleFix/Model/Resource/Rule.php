<?php

class Chuvisco_CatalogRuleFix_Model_Resource_Rule extends Mage_CatalogRule_Model_Resource_Rule
{
    public function insertRuleData(Mage_CatalogRule_Model_Rule $rule, array $websiteIds, array $productIds = array())
    {
        /** @var $write Varien_Db_Adapter_Interface */
        $write = $this->_getWriteAdapter();

        $customerGroupIds = $rule->getCustomerGroupIds();

        $fromTime = (int) strtotime($rule->getFromDate());
        $toTime = (int) strtotime($rule->getToDate());
        $toTime = $toTime ? ($toTime + self::SECONDS_IN_DAY - 1) : 0;

        /** @var Mage_Core_Model_Date $coreDate */
        $coreDate  = $this->_factory->getModel('core/date');
        $timestamp = $coreDate->timestamp(  'Today'); // use local timestamp instead of gmtTimestamp
        if ($fromTime > $timestamp
            || ($toTime && $toTime < $timestamp)
        ) {
            return;
        }
        $sortOrder = (int) $rule->getSortOrder();
        $actionOperator = $rule->getSimpleAction();
        $actionAmount = (float) $rule->getDiscountAmount();
        $subActionOperator = $rule->getSubIsEnable() ? $rule->getSubSimpleAction() : '';
        $subActionAmount = (float) $rule->getSubDiscountAmount();
        $actionStop = (int) $rule->getStopRulesProcessing();
        /** @var $helper Mage_Catalog_Helper_Product_Flat */
        $helper = $this->_factory->getHelper('catalog/product_flat');

        if ($helper->isEnabled() && $helper->isBuiltAllStores()) {
            /** @var $store Mage_Core_Model_Store */
            foreach ($this->_app->getStores(false) as $store) {
                if (in_array($store->getWebsiteId(), $websiteIds)) {
                    /** @var $selectByStore Varien_Db_Select */
                    $selectByStore = $rule->getProductFlatSelect($store->getId())
                        ->joinLeft(array('cg' => $this->getTable('customer/customer_group')),
                            $write->quoteInto('cg.customer_group_id IN (?)', $customerGroupIds),
                            array('cg.customer_group_id'))
                        ->reset(Varien_Db_Select::COLUMNS)
                        ->columns(array(
                            new Zend_Db_Expr($store->getWebsiteId()),
                            'cg.customer_group_id',
                            'p.entity_id',
                            new Zend_Db_Expr($rule->getId()),
                            new Zend_Db_Expr($fromTime),
                            new Zend_Db_Expr($toTime),
                            new Zend_Db_Expr("'" . $actionOperator . "'"),
                            new Zend_Db_Expr($actionAmount),
                            new Zend_Db_Expr($actionStop),
                            new Zend_Db_Expr($sortOrder),
                            new Zend_Db_Expr("'" . $subActionOperator . "'"),
                            new Zend_Db_Expr($subActionAmount),
                        ));

                    if (count($productIds) > 0) {
                        $selectByStore->where('p.entity_id IN (?)', array_keys($productIds));
                    }

                    $selects = $write->selectsByRange('entity_id', $selectByStore, self::RANGE_PRODUCT_STEP);
                    foreach ($selects as $select) {
                        $write->query(
                            $write->insertFromSelect(
                                $select, $this->getTable('catalogrule/rule_product'), array(
                                'website_id',
                                'customer_group_id',
                                'product_id',
                                'rule_id',
                                'from_time',
                                'to_time',
                                'action_operator',
                                'action_amount',
                                'action_stop',
                                'sort_order',
                                'sub_simple_action',
                                'sub_discount_amount',
                            ), Varien_Db_Adapter_Interface::INSERT_IGNORE
                            )
                        );
                    }
                }
            }
        } else {
            if (count($productIds) == 0) {
                Varien_Profiler::start('__MATCH_PRODUCTS__');
                $productIds = $rule->getMatchingProductIds();
                Varien_Profiler::stop('__MATCH_PRODUCTS__');
            }

            $rows = array();
            foreach ($productIds as $productId => $validationByWebsite) {
                foreach ($websiteIds as $websiteId) {
                    foreach ($customerGroupIds as $customerGroupId) {
                        if (empty($validationByWebsite[$websiteId])) {
                            continue;
                        }
                        $rows[] = array(
                            'rule_id'             => $rule->getId(),
                            'from_time'           => $fromTime,
                            'to_time'             => $toTime,
                            'website_id'          => $websiteId,
                            'customer_group_id'   => $customerGroupId,
                            'product_id'          => $productId,
                            'action_operator'     => $actionOperator,
                            'action_amount'       => $actionAmount,
                            'action_stop'         => $actionStop,
                            'sort_order'          => $sortOrder,
                            'sub_simple_action'   => $subActionOperator,
                            'sub_discount_amount' => $subActionAmount,
                        );

                        if (count($rows) == 1000) {
                            $write->insertMultiple($this->getTable('catalogrule/rule_product'), $rows);
                            $rows = array();
                        }
                    }
                }
            }

            if (!empty($rows)) {
                $write->insertMultiple($this->getTable('catalogrule/rule_product'), $rows);
            }
        }
    }
}