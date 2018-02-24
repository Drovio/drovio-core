-- Get next product id
SELECT IFNULL(MAX(product_id), 0) + 1 INTO @productID;
FROM RTL_Cproduct
WHERE company_id = {cid};

-- Insert Company Product
INSERT INTO RTL_Cproduct (company_id, product_id, global_product_id, company_id, title, description)
VALUES ({cid}, @productID, {gpid}, '{title}', '{description}');

-- Get product id
SELECT last_insert_id() AS id;