-- Create Global Product
INSERT INTO RTL_product (title, description, hierarchy_id)
VALUES ('{title}', '{description}', {hierarchy});

-- Get product id
SELECT last_insert_id() as id;