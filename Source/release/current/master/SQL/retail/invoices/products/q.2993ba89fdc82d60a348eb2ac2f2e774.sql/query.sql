UPDATE RTL_invoiceProduct
SET
	product_price = {price},
	tax_rate_id = {rate_id},
	tax_rate = {rate},
	discount = {discount},
	amount = {amount},
	total_price = {total_price}
WHERE invoice_id = '{iid}' AND product_id = '{pid}';