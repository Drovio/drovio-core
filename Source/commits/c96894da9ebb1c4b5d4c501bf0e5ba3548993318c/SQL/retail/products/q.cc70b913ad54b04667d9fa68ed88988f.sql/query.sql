-- Get next product id
SELECT IFNULL(MAX(product_id), 0) + 1 INTO @companyProductID
FROM RTL_product
WHERE owner_company_id = {cid};

-- Set product id
SELECT CONCAT({cid}, '_', @companyProductID) INTO @productID;

-- Insert Company Product
INSERT INTO RTL_product (id, owner_company_id, product_id, title)
VALUES (@productID, {cid}, @companyProductID, '{title}');

-- Get product id
SELECT @productID AS id;