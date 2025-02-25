<?php

class EnergyClassSql extends ObjectModel
{
    /**
     * Get energy class by product id.
     */
    public static function getEnergyClass($id_product)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('class');
        $sql->from('energylabeleu');
        $sql->where('id_product = '.(int) $id_product);

        return $db->executeS($sql);
    }

    /**
     * Delete energy class by product id.
     */
    public static function deleteEnergyClass($id_product)
    {
        $db = Db::getInstance();
        $sql = 'DELETE FROM '._DB_PREFIX_.'energylabeleu WHERE id_product = '.(int) $id_product;

        return $db->execute($sql);
    }

    /**
     * Insert energy class by product id.
     */
    public static function insertEnergyClass($id_product, $class)
    {
        $db = Db::getInstance();
        $sql = 'INSERT INTO '._DB_PREFIX_.'energylabeleu (id_product, class) VALUES ('.(int) $id_product.', "'.pSQL($class).'")';

        return $db->execute($sql);
    }
}
