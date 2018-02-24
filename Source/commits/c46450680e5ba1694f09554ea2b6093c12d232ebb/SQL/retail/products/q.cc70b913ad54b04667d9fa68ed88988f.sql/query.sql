-- Get next product id
SELECT IFNULL(MAX(product_id), 0) + 1 INTO @companyProductID
FROM RTL_Cproduct
WHERE company_id = {cid};

-- Set product id
SELECT CONCAT({cid}, '_', @companyProductID) INTO @productID;

-- Insert Company Product
INSERT INTO RTL_Cproduct (id, company_id, product_id, global_product_id, hierarchy_id, title, description)
VALUES (@productID, {cid}, @companyProductID, {gpid}, {hid}, '{title}', '{description}');

/* Get product id */
SELECT @companyProductID AS id;