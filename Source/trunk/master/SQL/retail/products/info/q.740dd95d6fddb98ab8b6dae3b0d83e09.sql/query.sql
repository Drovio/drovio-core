-- Insert category
INSERT INTO RTL_productInfoCategory (company_id, title)
VALUES ({cid}, '{title}');

/* Get last id */
SELECT last_insert_id() AS id;