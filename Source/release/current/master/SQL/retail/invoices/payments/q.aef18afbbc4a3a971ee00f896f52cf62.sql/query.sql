UPDATE RTL_invoicePayment
SET
	payment = {payment},
	notes = '{notes}',
	reference_id = IF({ref} IS NULL, NULL, '{ref}')
WHERE id = '{id}' AND invoice_id = '{iid}';