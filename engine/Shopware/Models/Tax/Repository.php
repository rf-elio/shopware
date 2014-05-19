<?php
/**
 * Shopware 4
 * Copyright © shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Models\Tax;

use Shopware\Components\Model\ModelRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query;
use Shopware\Components\Model\Query\SqlWalker;

/**
 * This class gathers all categories with there id, description, position, parent category id and the number
 * of articles assigned to that category.
 *
 * Uses the articles association to get the numbers of articles.
 *
 * Affected Models
 *  - Tax
 *  - Articles
 *
 * Affected tables
 *  - s_core_tax
 *  - s_core_tax_rules
 *
 * @category  Shopware
 * @package   Shopware\Models\Tax
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Repository extends ModelRepository
{
    /**
     * Get the correct tax-rate
     * @param integer $taxId
     * @param integer $areaId
     * @param integer $countryId
     * @param integer $stateId
     * @param integer $customerGroupId
     * @return mixed|string
     */
    public function getTaxRateByConditions($taxId, $areaId, $countryId, $stateId, $customerGroupId)
    {
        $sql = "
        SELECT id, tax FROM s_core_tax_rules WHERE
            active = 1 AND groupID = :taxId
        AND
            (areaID = :areaId OR areaID IS NULL)
        AND
            (countryID = :countryId OR countryID IS NULL)
        AND
            (stateID = :stateId OR stateID IS NULL)
        AND
            (customer_groupID = :customerGroupId OR customer_groupID = 0 OR customer_groupID IS NULL)
        ORDER BY customer_groupID DESC, areaID DESC, countryID DESC, stateID DESC
        LIMIT 1
        ";

        $parameters = array(
            'taxId' => $taxId,
            'areaId' => $areaId,
            'countryId' => $countryId,
            'stateId' => $stateId,
            'customerGroupId' => $customerGroupId
        );

        $taxRate = Shopware()->Db()->fetchRow($sql, $parameters);

        if (empty($taxRate['id'])) {
            $taxRate['tax'] = Shopware()->Db()->fetchOne("SELECT tax FROM s_core_tax WHERE id = ?", array($taxId));
        }

        return $taxRate['tax'];
    }
}