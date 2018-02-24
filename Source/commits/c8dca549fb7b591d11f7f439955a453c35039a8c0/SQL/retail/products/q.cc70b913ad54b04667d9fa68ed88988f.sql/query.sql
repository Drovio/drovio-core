-- Get next product id
SELECT IFNULL(MAX(product_id), 0) + 1 INTO @productID
FROM RTL_Cproduct
WHERE company_id = {cid};

-- Insert Company Product
INSERT INTO RTL_Cproduct (id, company_id, product_id, global_product_id, hierarchy_id, title, description)
VALUES (CONCAT({cid}, '_', @productID), {cid}, @productID, {gpid}, {hid}, '{title}', '{description}');

/* Get product id */
SELECT last_insert_id() AS id;