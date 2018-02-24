-- Add product info
INSERT INTO RTL_productInfo (category_id, company_id, title, is_bool)
VALUES ({catid}, {cid}, '{title}', {is_bool});

/* Get inserted id */
SELECT last_insert_id() AS id;