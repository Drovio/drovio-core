INSERT INTO RTL_invoiceProduct (invoice_id, product_id, product_title, product_price, tax_rate_id, tax_rate, discount, amount, total_price)
VALUES ('{iid}', '{pid}', '{title}', {price}, {rate_id}, {rate}, {discount}, {amount}, {total_price})
ON DUPLICATE KEY UPDATE title = '{title}', discount = {discount}, amount = {amount}, total_price = {total_price};