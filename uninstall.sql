
SELECT CONCAT('alter table ', table_name, ' DROP FOREIGN KEY ', constraint_name, ';')
FROM information_schema.table_constraints
WHERE constraint_type = 'FOREIGN KEY' AND table_name LIKE '%subscription%';

SELECT CONCAT('alter table ', table_name, ' DROP FOREIGN KEY ', constraint_name, ';')
FROM information_schema.table_constraints
WHERE constraint_type = 'FOREIGN KEY' AND table_name LIKE '%quote_item%';



DELETE FROM `setup_module` WHERE `module` = "Toppik_Subscriptions";

DROP TABLE IF EXISTS subscriptions_subscriptions;
DROP TABLE IF EXISTS subscriptions_units;
DROP TABLE IF EXISTS subscriptions_items;
DROP TABLE IF EXISTS subscriptions_periods;
DROP TABLE IF EXISTS subscriptions_profiles;
DROP TABLE IF EXISTS subscriptions_profiles_address;
DROP TABLE IF EXISTS subscriptions_profiles_item;
DROP TABLE IF EXISTS subscriptions_profiles_backup;
DROP TABLE IF EXISTS subscriptions_profiles_cancelled;
DROP TABLE IF EXISTS subscriptions_profiles_history;
DROP TABLE IF EXISTS subscriptions_profiles_orders;
DROP TABLE IF EXISTS subscription_report_daily;
DROP TABLE IF EXISTS subscriptions_rules_add;
DROP TABLE IF EXISTS subscriptions_save;
DROP TABLE IF EXISTS subscriptions_save_points;
DROP TABLE IF EXISTS subscriptions_sku_relations;
DROP TABLE IF EXISTS subscriptions_iteration;
DROP TABLE IF EXISTS subscriptions_profiles_gift;

-- ALTER TABLE quote_item MODIFY updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL;
ALTER TABLE `quote_item` DROP `linked_item_id`;
