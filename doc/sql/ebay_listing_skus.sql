ALTER TABLE `ims`.`ebay_listing_skus` 
ADD COLUMN `num` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '��� �ڼ���sku' AFTER `listing_id`;
